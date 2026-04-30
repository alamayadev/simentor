import json
from collections import Counter, defaultdict

p = 'tools/benchmark/results_bypass.json'
outp = 'tools/benchmark/header_counts_bypass.json'
with open(p, 'r', encoding='utf-8') as f:
    j = json.load(f)

counts = defaultdict(Counter)
summary = {}
for s in j.get('raw', []):
    host = s.get('host')
    headers = s.get('headers') or {}
    xc = headers.get('x-cache') or headers.get('X-Cache') or '(none)'
    counts[host][xc] += 1

for h, c in counts.items():
    summary[h] = dict(c)

with open(outp, 'w', encoding='utf-8') as f:
    json.dump(summary, f, indent=2)

print('Wrote', outp)
print(json.dumps(summary, indent=2))
