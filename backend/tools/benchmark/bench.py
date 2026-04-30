#!/usr/bin/env python3
"""Simple async benchmark harness comparing two hosts across endpoints.
Produces JSON and a concise text summary.
"""
import argparse
import asyncio
import json
import time
from dataclasses import dataclass, asdict
from statistics import mean
from typing import List

import httpx
from math import floor
from rich.console import Console
from rich.table import Table

console = Console()


@dataclass
class Sample:
    host: str
    endpoint: str
    status_code: int
    ok: bool
    elapsed_ms: float
    timestamp: float
    error: str | None = None
    headers: dict | None = None


async def worker(client: httpx.AsyncClient, host: str, tasks_q: asyncio.Queue, out: List[Sample]):
    """Worker that pulls an endpoint from the queue and requests it.

    The queue contains endpoint strings (e.g. '/api/alokasi').
    """
    while True:
        try:
            endpoint = tasks_q.get_nowait()
        except asyncio.QueueEmpty:
            return
        url = host.rstrip('/') + '/' + endpoint.lstrip('/')
        start = time.perf_counter()
        timestamp = time.time()
        try:
            r = await client.get(url, timeout=30.0)
            elapsed = (time.perf_counter() - start) * 1000.0
            hdrs = dict(r.headers)
            out.append(Sample(host, endpoint, r.status_code, 200 <= r.status_code < 400, elapsed, timestamp, None, hdrs))
        except Exception as e:
            elapsed = (time.perf_counter() - start) * 1000.0
            out.append(Sample(host, endpoint, 0, False, elapsed, timestamp, str(e)))
        finally:
            try:
                tasks_q.task_done()
            except Exception:
                pass


async def run_host_benchmark(host: str, endpoints: List[str], concurrency: int, requests_per_endpoint: int) -> List[Sample]:
    samples: List[Sample] = []
    async with httpx.AsyncClient(http2=False) as client:
        tasks_q = asyncio.Queue()
        for endpoint in endpoints:
            for _ in range(requests_per_endpoint):
                tasks_q.put_nowait(endpoint)
        workers = [asyncio.create_task(worker(client, host, tasks_q, samples)) for _ in range(concurrency)]
        # NOTE: worker uses the same endpoint value; create tasks differently so each consumes a queued endpoint string
        # We'll instead produce workers which read endpoints from queue and call the correct url
        # Wait until the queue is consumed
        await tasks_q.join()
        for w in workers:
            w.cancel()
    return samples


def percentile(values: List[float], p: float) -> float:
    """Compute the p-th percentile (p between 0 and 100) with nearest-rank method."""
    if not values:
        return 0.0
    sorted_vals = sorted(values)
    k = (len(sorted_vals) - 1) * (p / 100.0)
    f = floor(k)
    c = min(f + 1, len(sorted_vals) - 1)
    if f == c:
        return float(sorted_vals[int(k)])
    d0 = sorted_vals[int(f)] * (c - k)
    d1 = sorted_vals[int(c)] * (k - f)
    return float(d0 + d1)


def analyze_samples(samples: List[Sample]):
    if not samples:
        return {}
    summary = {}
    # group by host and endpoint
    groups = {}
    for s in samples:
        groups.setdefault((s.host, s.endpoint), []).append(s)

    for (host, endpoint), items in groups.items():
        latencies = [it.elapsed_ms for it in items]
        success = sum(1 for it in items if it.ok)
        errors = len(items) - success
        mean_ms = sum(latencies) / len(latencies) if latencies else None
        summary.setdefault(host, {})[endpoint] = {
            'count': int(len(items)),
            'success': int(success),
            'errors': int(errors),
            'mean_ms': float(mean_ms) if mean_ms is not None else None,
            'p50_ms': float(percentile(latencies, 50)) if latencies else None,
            'p90_ms': float(percentile(latencies, 90)) if latencies else None,
            'p99_ms': float(percentile(latencies, 99)) if latencies else None,
        }
    return summary


async def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--hosts', nargs=2, required=True, help='Two hosts to compare')
    parser.add_argument('--endpoints', nargs='+', required=True)
    parser.add_argument('--concurrency', type=int, default=20)
    parser.add_argument('--requests', type=int, default=200, help='Requests per endpoint per host')
    parser.add_argument('--output', default='tools/benchmark/results.json')
    args = parser.parse_args()

    hosts = args.hosts
    endpoints = args.endpoints
    concurrency = args.concurrency
    requests_per_endpoint = args.requests

    console.print(f"Running benchmark: hosts={hosts} endpoints={endpoints} concurrency={concurrency} requests={requests_per_endpoint}")

    all_samples = []
    for host in hosts:
        console.print(f"Benchmarking host: {host}")
        # Build queue that contains endpoint strings repeated requests_per_endpoint
        q = asyncio.Queue()
        for endpoint in endpoints:
            for _ in range(requests_per_endpoint):
                q.put_nowait(endpoint)

        samples: List[Sample] = []
        async with httpx.AsyncClient(http2=False) as client:
            workers = [asyncio.create_task(worker(client, host, q, samples)) for _ in range(concurrency)]
            # each worker will pop endpoint from queue and perform requests to that endpoint
            await q.join()
            for w in workers:
                w.cancel()

        all_samples.extend(samples)
        console.print(f"Host {host} completed; samples collected: {len(samples)}")

    # analyze
    summary = analyze_samples(all_samples)

    # save raw and summary
    out = {
        'meta': {
            'hosts': hosts,
            'endpoints': endpoints,
            'concurrency': concurrency,
            'requests_per_endpoint': requests_per_endpoint,
            'timestamp': time.time(),
        },
        'summary': summary,
        'raw': [asdict(s) for s in all_samples],
    }

    with open(args.output, 'w', encoding='utf-8') as f:
        json.dump(out, f, indent=2)

    # print a readable table
    table = Table(show_header=True, header_style='bold magenta')
    table.add_column('Host')
    table.add_column('Endpoint')
    table.add_column('Count', justify='right')
    table.add_column('Success', justify='right')
    table.add_column('Errors', justify='right')
    table.add_column('p50 ms', justify='right')
    table.add_column('p90 ms', justify='right')
    table.add_column('p99 ms', justify='right')

    for host in hosts:
        for endpoint in endpoints:
            stats = summary.get(host, {}).get(endpoint, None)
            if not stats:
                table.add_row(host, endpoint, '0', '0', '0', '-', '-', '-')
            else:
                table.add_row(
                    host,
                    endpoint,
                    str(stats['count']),
                    str(stats['success']),
                    str(stats['errors']),
                    f"{stats['p50_ms']:.2f}",
                    f"{stats['p90_ms']:.2f}",
                    f"{stats['p99_ms']:.2f}",
                )

    console.print(table)
    console.print(f"Saved results to {args.output}")


if __name__ == '__main__':
    asyncio.run(main())
