#!/usr/bin/env python3
"""
sql_to_json.py

Parse a SQL dump file and export each table's INSERT data to a separate JSON file.
Writes JSON files into `database/initial_data/<table>.json`.

Limitations:
- Focuses on parsing INSERT INTO ... VALUES (...) statements (MySQL/MariaDB dumps).
- Supports INSERTs with or without a column list. If no column list is provided, each row is exported as
a raw array of values instead of a dict.
- Handles NULL, integers, floats and single-quoted strings with common escaping (backslash and doubled quotes).
- Does not attempt to parse COPY/pg_dump formats, complex expressions, or binary/hex literals.

Usage:
    python scripts/sql_to_json.py path/to/dump.sql

If no path is provided, it tries to use `database/backupdb/simentordb_sls_20250613.sql` in the repo.

"""

import argparse
import json
import os
import re
import sys
from collections import defaultdict


INSERT_RE = re.compile(
    r"INSERT\s+INTO\s+`?(?P<table>\w+)`?\s*(?:\((?P<cols>[^)]*)\))?\s*VALUES\s*(?P<values>.*?)\s*;",
    re.IGNORECASE | re.DOTALL,
)


def unquote_identifier(s: str) -> str:
    s = s.strip()
    if s.startswith('`') and s.endswith('`'):
        return s[1:-1]
    if s.startswith('"') and s.endswith('"'):
        return s[1:-1]
    return s


def parse_columns(cols_str: str):
    if not cols_str:
        return None
    # split on commas not inside quotes (columns shouldn't contain quotes, so simple split is OK)
    cols = [unquote_identifier(c.strip()) for c in cols_str.split(',')]
    return cols


def parse_values_list(values_str: str):
    """
    Parse the VALUES clause content which may contain multiple parenthesized tuples:
    e.g. (1,'a'),(2,'b')
    Returns a list of rows, each row is a list of raw SQL tokens.
    """
    rows = []
    i = 0
    n = len(values_str)
    while i < n:
        # skip whitespace and commas
        while i < n and values_str[i].isspace():
            i += 1
        if i < n and values_str[i] == ',':
            i += 1
            continue
        if i >= n:
            break
        if values_str[i] != '(':
            # unexpected token; try to advance until next (
            j = values_str.find('(', i)
            if j == -1:
                break
            i = j
        # parse a tuple starting at i
        assert values_str[i] == '('
        i += 1
        row = []
        cur = []
        in_string = False
        string_quote = None
        escape = False
        paren_depth = 1
        while i < n and paren_depth > 0:
            ch = values_str[i]
            if in_string:
                if escape:
                    cur.append(ch)
                    escape = False
                elif ch == '\\':
                    # backslash escape
                    escape = True
                elif ch == string_quote:
                    # may be end or doubled quote; we keep it as end and let doubling be handled by next char logic
                    in_string = False
                    cur.append(ch)
                else:
                    cur.append(ch)
                i += 1
                continue
            # not in string
            if ch in ("'", '"'):
                in_string = True
                string_quote = ch
                cur.append(ch)
                i += 1
                continue
            if ch == '(':
                paren_depth += 1
                cur.append(ch)
                i += 1
                continue
            if ch == ')':
                paren_depth -= 1
                if paren_depth == 0:
                    # finish current token
                    token = ''.join(cur).strip()
                    if token != '':
                        row.append(token)
                    cur = []
                    i += 1
                    break
                else:
                    cur.append(ch)
                i += 1
                continue
            if ch == ',':
                # field separator at top paren depth
                token = ''.join(cur).strip()
                row.append(token)
                cur = []
                i += 1
                continue
            else:
                cur.append(ch)
                i += 1
        rows.append(row)
        # after a tuple, skip whitespace and optional comma handled at loop top
    return rows


def sql_literal_to_python(token: str):
    t = token.strip()
    if t.upper() == 'NULL':
        return None
    # strings are single- or double-quoted
    if (t.startswith("'") and t.endswith("'")) or (t.startswith('"') and t.endswith('"')):
        inner = t[1:-1]
        # unescape common patterns: backslash escapes and doubled single quotes
        inner = inner.replace("\\'", "'")
        inner = inner.replace('\\"', '"')
        inner = inner.replace("''", "'")
        inner = inner.replace('\\\\', '\\')
        return inner
    # numeric?
    if re.match(r'^-?\d+$', t):
        try:
            return int(t)
        except Exception:
            pass
    if re.match(r'^-?\d*\.\d+(e[-+]?\d+)?$', t, re.IGNORECASE) or re.match(r'^-?\d+e[-+]?\d+$', t, re.IGNORECASE):
        try:
            return float(t)
        except Exception:
            pass
    # fallback: return as raw string
    return t


def merge_row_with_columns(cols, row_values):
    if cols is None:
        # return raw list
        return [sql_literal_to_python(v) for v in row_values]
    # if fewer or more values than cols, still map up to min length
    obj = {}
    for i, col in enumerate(cols):
        if i < len(row_values):
            obj[col] = sql_literal_to_python(row_values[i])
        else:
            obj[col] = None
    # extra values are ignored
    return obj


def process_sql_file(sql_path, out_dir):
    if not os.path.exists(sql_path):
        print(f"SQL file not found: {sql_path}")
        return 1
    if not os.path.exists(out_dir):
        os.makedirs(out_dir, exist_ok=True)
    content = ''
    with open(sql_path, 'r', encoding='utf-8', errors='replace') as f:
        content = f.read()
    tables = defaultdict(list)
    for m in INSERT_RE.finditer(content):
        table = m.group('table')
        cols = parse_columns(m.group('cols'))
        values_blob = m.group('values')
        rows = parse_values_list(values_blob)
        for r in rows:
            obj = merge_row_with_columns(cols, r)
            tables[table].append(obj)
    # write JSON files
    for table, rows in tables.items():
        out_path = os.path.join(out_dir, f"{table}.json")
        with open(out_path, 'w', encoding='utf-8') as jf:
            json.dump(rows, jf, ensure_ascii=False, indent=2)
        print(f"Wrote {len(rows)} rows -> {out_path}")
    if not tables:
        print("No INSERT statements found in the provided SQL file.")
    return 0


def main():
    parser = argparse.ArgumentParser(description='Convert SQL INSERTs to per-table JSON files')
    parser.add_argument('sql_file', nargs='?', default='database/backupdb/simentordb_sls_20250613.sql',
                        help='Path to SQL dump file')
    parser.add_argument('--out', '-o', default='database/initial_data', help='Output directory for JSON files')
    args = parser.parse_args()
    sql_path = args.sql_file
    out_dir = args.out
    rc = process_sql_file(sql_path, out_dir)
    sys.exit(rc)


if __name__ == '__main__':
    main()
