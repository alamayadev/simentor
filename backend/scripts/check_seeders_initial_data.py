#!/usr/bin/env python3
"""
check_seeders_initial_data.py

Scan database/seeders for references to data files (database_path('json_data/...'))
and verify corresponding files exist under database/initial_data. For JSON files,
try to parse and show sample keys. For CSV/TXT show header/first line.

Run from repository root:
    python scripts/check_seeders_initial_data.py

"""
import re
import os
import json
import csv

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
SEEDERS_DIR = os.path.join(ROOT, 'database', 'seeders')
INITIAL_DIR = os.path.join(ROOT, 'database', 'initial_data')
MIGRATIONS_DIR = os.path.join(ROOT, 'database', 'migrations')

PATTERN = re.compile(r"database_path\(\s*'(?:json_data|initial_data)/([^']+)'\s*\)")
CSV_PATTERN = re.compile(r"database_path\(\s*'(?:json_data|initial_data)/([^']+\.(?:csv|txt))'\s*\)")

results = []

# Parse migrations to extract table -> columns mapping (best-effort via regex)
def parse_migrations(migrations_dir):
    table_cols = {}
    create_table_re = re.compile(r"Schema::create\(\s*['\"]([^'\"]+)['\"]")
    table_block_re = re.compile(r"Schema::(?:create|table)\(\s*['\"]([^'\"]+)['\"]\s*,")
    # capture $table->type('col' ... )->nullable()?
    col_re = re.compile(r"\$table->\s*(?P<type>\w+)\(\s*['\"](?P<col>[^'\"]+)['\"]\s*\)(?P<rest>[^;\n]*)")
    for fname in os.listdir(migrations_dir):
        if not fname.endswith('.php'):
            continue
        path = os.path.join(migrations_dir, fname)
        try:
            text = open(path, 'r', encoding='utf-8').read()
        except Exception:
            continue
        # find table names referenced in this migration
        tables = set()
        for m in table_block_re.findall(text):
            tables.add(m)
        # fallback: any Schema::create
        for m in create_table_re.findall(text):
            tables.add(m)

        if not tables:
            continue

        cols = {}
        for m in col_re.finditer(text):
            col = m.group('col')
            ctype = m.group('type')
            rest = m.group('rest') or ''
            nullable = '->nullable' in rest
            # also detect timestamps() and id()
            cols[col] = {'type': ctype, 'nullable': nullable}
        # check for timestamps() and id() calls (not tied to a column name)
        if '->timestamps(' in text or '->timestamps;' in text or '->timestamps()' in text:
            cols['created_at'] = {'type': 'timestamp', 'nullable': True}
            cols['updated_at'] = {'type': 'timestamp', 'nullable': True}
        if re.search(r"\$table->id\(\)", text):
            cols['id'] = {'type': 'bigint', 'nullable': False}

        for t in tables:
            if t in table_cols:
                table_cols[t].update(cols)
            else:
                table_cols[t] = dict(cols)
    return table_cols

migration_map = parse_migrations(MIGRATIONS_DIR)

for fname in os.listdir(SEEDERS_DIR):
    if not fname.endswith('.php'):
        continue
    path = os.path.join(SEEDERS_DIR, fname)
    text = open(path, 'r', encoding='utf-8').read()
    matches = PATTERN.findall(text)
    # also check for other variants (direct file reads)
    # combine unique
    matches = list(dict.fromkeys(matches))
    if not matches:
        continue
    for m in matches:
        expected = m
        expected_path = os.path.join(INITIAL_DIR, expected)
        entry = {
            'seeder': fname,
            'expected_file': expected,
            'path': expected_path,
            'exists': os.path.exists(expected_path),
            'type': None,
            'ok': False,
            'notes': [],
            'sample_keys': None,
        }
        if entry['exists']:
            low = expected.lower()
            if low.endswith('.json'):
                entry['type'] = 'json'
                try:
                    with open(expected_path, 'r', encoding='utf-8') as f:
                        data = json.load(f)
                    if isinstance(data, list):
                        entry['ok'] = True
                        if len(data) > 0 and isinstance(data[0], dict):
                            entry['sample_keys'] = list(data[0].keys())
                        else:
                            entry['notes'].append('JSON top-level is list but items are not objects or list is empty')
                    else:
                        entry['notes'].append('JSON top-level is not a list')
                except Exception as e:
                    entry['notes'].append('JSON parse error: ' + str(e))
            elif low.endswith('.csv'):
                entry['type'] = 'csv'
                try:
                    with open(expected_path, 'r', encoding='utf-8', errors='replace') as f:
                        reader = csv.reader(f)
                        header = next(reader, None)
                        entry['sample_keys'] = header
                        entry['ok'] = True if header else False
                except Exception as e:
                    entry['notes'].append('CSV read error: ' + str(e))
            elif low.endswith('.txt'):
                entry['type'] = 'txt'
                try:
                    with open(expected_path, 'r', encoding='utf-8', errors='replace') as f:
                        lines = [next(f) for _ in range(5)]
                        entry['sample_keys'] = lines[:5]
                        entry['ok'] = True
                except StopIteration:
                    entry['notes'].append('TXT file exists but is empty')
                except Exception as e:
                    entry['notes'].append('TXT read error: ' + str(e))
            else:
                # unknown extension: try to inspect
                entry['type'] = os.path.splitext(expected)[1].lstrip('.')
                entry['notes'].append('Unknown file extension')
        else:
            entry['notes'].append('File not found in database/initial_data')
        results.append(entry)

# Also find files in initial_data that are not referenced by any seeder (extra files)
referenced = set([r['expected_file'] for r in results])
all_files = set(os.listdir(INITIAL_DIR))
unreferenced = sorted(list(all_files - referenced))

# Print report
print('\nSeeders -> initial_data file compatibility report\n')
for r in results:
    print(f"Seeder: {r['seeder']}")
    print(f"  expects: {r['expected_file']} -> exists: {r['exists']} type: {r['type']}")
    if r['sample_keys']:
        print(f"  sample keys/header (first item): {r['sample_keys']}")
    # Compare to migrations if possible
    # infer table name from expected filename by common patterns
    table_guess = None
    if r['expected_file'].lower().endswith('.json'):
        base = os.path.splitext(r['expected_file'])[0]
        # common conversions: sls_kec.json -> sls_kecs table
        candidates = [base, base + 's', base.rstrip('y') + 'ies']
        for c in candidates:
            if c in migration_map:
                table_guess = c
                break
    if table_guess:
        cols = sorted(list(migration_map.get(table_guess, [])))
        print(f"  migration table detected: {table_guess} (columns: {len(cols)})")
        if cols:
            print(f"    sample migration columns: {cols[:10]}")
        # if sample keys available, compare
        if r['sample_keys'] and isinstance(r['sample_keys'], list):
            json_keys = set(r['sample_keys'])
            mig_keys = set(cols)
            missing_in_json = sorted(list(mig_keys - json_keys))
            extra_in_json = sorted(list(json_keys - mig_keys))
            if missing_in_json:
                print(f"    MISSING IN JSON (present in migration): {missing_in_json[:10]}")
            if extra_in_json:
                print(f"    EXTRA IN JSON (not in migration): {extra_in_json[:10]}")
    if r['notes']:
        for n in r['notes']:
            print(f"  NOTE: {n}")
    print('')

print('Files in database/initial_data not referenced by any seeder:')
for u in unreferenced:
    print('  - ' + u)

# Save JSON report
out = {'results': results, 'unreferenced': unreferenced}
with open(os.path.join(ROOT, 'scripts', 'seeders_initial_data_report.json'), 'w', encoding='utf-8') as jf:
    json.dump(out, jf, ensure_ascii=False, indent=2)
print('\nWrote scripts/seeders_initial_data_report.json')
