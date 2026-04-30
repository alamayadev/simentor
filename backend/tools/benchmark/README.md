Benchmark tool for comparing two hosts

This small Python tool runs concurrent requests against a list of endpoints on two hosts and reports latency percentiles, throughput (req/s), error counts, and produces JSON/CSV output.

Usage (from project root):

# Create a venv and install deps (Windows PowerShell)
python -m venv .venv-bench; .\.venv-bench\Scripts\Activate.ps1; pip install -r tools\benchmark\requirements.txt

# Run the benchmark
python tools\benchmark\bench.py --hosts https://api-dev.bps3215.id https://portal-dev.bps3215.id \
  --endpoints /api/wilkerstat-dashboard /api/alokasi /api/alokasi/progress-scan /api/alokasi/progress-georef \
  --concurrency 20 --requests 200 --output tools\benchmark\results.json

Notes
- The script uses HTTP/1.1 by default via httpx. You can adjust concurrency and total requests per host.
- The script will print a short comparison report and store raw per-request samples in the JSON output for further analysis.
- Run from a machine with stable bandwidth and from the same network to make comparisons fair.
