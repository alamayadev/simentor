import pandas as pd
import mysql.connector
from mysql.connector import Error
import re
import os
import hashlib
from collections import defaultdict
from db_utils import get_connection

def parse_revision_date(revision_name, default_year=2026):
    # Mapping for months (Indonesian and English common variants)
    month_map = {
        'JAN': '01', 'FEB': '02', 'PEB': '02', 'MAR': '03', 'APR': '04',
        'MEI': '05', 'MAY': '05', 'JUN': '06', 'JUL': '07', 'AGT': '08',
        'AUG': '08', 'SEP': '09', 'OKT': '10', 'OCT': '10', 'NOP': '11',
        'NOV': '11', 'DES': '12', 'DEC': '12'
    }
    
    # Try to find day and month pattern in name (e.g. 21JAN or 12MAR)
    match = re.search(r'(\d{1,2})([A-Z]{3})', revision_name.upper())
    if match:
        day = match.group(1).zfill(2)
        month_abbr = match.group(2)
        month = month_map.get(month_abbr, '01')
        return f"{default_year}-{month}-{day}"
    return f"{default_year}-01-01"

def parse_volume(vol_str):
    if pd.isna(vol_str):
        return 0.0, ""
    vol_str = str(vol_str).strip()
    match = re.match(r'^([\d\.,]+)\s*(.*)$', vol_str)
    if match:
        vol = match.group(1).replace(',', '')
        try:
            return float(vol), match.group(2).strip()
        except ValueError:
            return 0.0, vol_str
    return 0.0, vol_str

def parse_numeric(value, default=0.0):
    if pd.isna(value):
        return default

    if isinstance(value, (int, float)):
        return float(value)

    value_str = str(value).strip()
    if value_str == "" or value_str == "*":
        return default

    normalized = value_str.replace(',', '')

    try:
        return float(normalized)
    except ValueError:
        return default

def first_non_empty(row, indexes, default=None):
    for idx in indexes:
        if idx >= len(row):
            continue
        value = row[idx]
        if not pd.isna(value) and str(value).strip() != "":
            return value
    return default

def extract_rkk_items(file_path, tahun_anggaran=2026):
    df = pd.read_excel(file_path, header=None)

    state = {
        'program': {'code': None, 'name': None},
        'activity': {'code': None, 'name': None},
        'output': {'code': None, 'name': None},
        'component': {'code': None, 'name': None},
        'sub_component': {'code': None, 'name': None},
        'account': {'code': None, 'name': None, 'source': None}
    }

    items = []

    for index, row in df.iterrows():
        raw_val = row[0]
        code = str(raw_val).strip() if not pd.isna(raw_val) else ""
        desc_primary = str(row[3]).strip() if not pd.isna(row[3]) else ""
        desc_secondary = str(row[4]).strip() if not pd.isna(row[4]) else ""
        row_source_raw = first_non_empty(row, [13, 11])
        row_source = str(row_source_raw).strip() if row_source_raw is not None else None

        if not code and not desc_primary and not desc_secondary:
            continue

        if re.match(r'^\d{4}$', code): # Program
            state['program'] = {'code': code, 'name': desc_primary}
            for k in ['activity', 'output', 'component', 'sub_component', 'account']:
                state[k] = {'code': None, 'name': None, 'source': None} if k == 'account' else {'code': None, 'name': None}
            continue
        if re.match(r'^\d{4}\.[A-Z]{3}$', code): # Activity
            state['activity'] = {'code': code, 'name': desc_primary}
            continue
        if re.match(r'^\d{4}\.[A-Z]{3}\.\d{3}$', code): # Output
            state['output'] = {'code': code, 'name': desc_primary}
            continue
        if re.match(r'^\d{3}$', code): # Component
            state['component'] = {'code': code, 'name': desc_primary}
            continue
        if re.match(r'^[A-Z]$', code): # Sub-component
            state['sub_component'] = {'code': code, 'name': desc_primary}
            continue
        if re.match(r'^\d{6}$', code): # Account
            state['account'] = {'code': code, 'name': desc_primary, 'source': row_source}
            continue

        if desc_primary == '-' or code == '-' or (not code and not pd.isna(row[6]) and first_non_empty(row, [10, 9]) is not None):
            if not state['account']['code']:
                continue

            item_desc = desc_primary if desc_primary != '-' and desc_primary else desc_secondary
            price = parse_numeric(row[7])
            amount = parse_numeric(first_non_empty(row, [10, 9], 0))
            source = row_source if row_source else state['account']['source']
            source = source if source else ""
            vol_val, unit_val = parse_volume(row[6])

            norm_desc = re.sub(r'[^a-zA-Z0-9]', '', str(item_desc)).lower()
            key_src = "|".join([
                str(tahun_anggaran),
                str(state['program']['code']), str(state['activity']['code']),
                str(state['output']['code']), str(state['component']['code']),
                str(state['sub_component']['code']), str(state['account']['code']),
                norm_desc
            ])
            comp_key = hashlib.md5(key_src.encode()).hexdigest()

            items.append({
                'row_number': int(index) + 1,
                'program_code': state['program']['code'],
                'program_name': state['program']['name'],
                'activity_code': state['activity']['code'],
                'activity_name': state['activity']['name'],
                'output_code': state['output']['code'],
                'output_name': state['output']['name'],
                'component_code': state['component']['code'],
                'component_name': state['component']['name'],
                'sub_component_code': state['sub_component']['code'],
                'sub_component_name': state['sub_component']['name'],
                'account_code': state['account']['code'],
                'account_name': state['account']['name'],
                'description': item_desc,
                'volume': vol_val,
                'unit': unit_val,
                'unit_price': price,
                'total_amount': amount,
                'funding_source': source,
                'composite_key': comp_key,
            })

    return items

def merge_rkk_items(items):
    grouped = defaultdict(list)
    for item in items:
        grouped[item['composite_key']].append(item)

    merged_items = []
    for composite_key, group in grouped.items():
        if len(group) == 1:
            merged_items.append(group[0])
            continue

        base = dict(group[0])
        base['row_number'] = min(item['row_number'] for item in group)
        base['volume'] = sum(parse_numeric(item['volume']) for item in group)
        base['unit_price'] = (
            sum(parse_numeric(item['unit_price']) for item in group) / len(group)
            if group else 0.0
        )
        base['total_amount'] = sum(parse_numeric(item['total_amount']) for item in group)

        funding_sources = []
        seen_sources = set()
        for item in group:
            source = str(item.get('funding_source') or '').strip()
            if source and source not in seen_sources:
                funding_sources.append(source)
                seen_sources.add(source)

        base_source = ' / '.join(funding_sources) if funding_sources else ''
        base['funding_source'] = f'{base_source} (merged)'.strip() if base_source else '(merged)'
        merged_items.append(base)

    return merged_items

def convert_rkk(file_path, revision_name, tahun_anggaran=2026):
    print(f"Loading {file_path} for Year {tahun_anggaran}...")
    
    conn = get_connection()
    if not conn:
        return 0
    cursor = conn.cursor()
    
    rev_date = parse_revision_date(revision_name, tahun_anggaran)
    print(f"Assigning revision date: {rev_date}")

    items = merge_rkk_items(extract_rkk_items(file_path, tahun_anggaran))
    count = 0
    total_rm = 0
    total_all = 0

    for item in items:
        print(f"  Attempting to insert item: {item['description']} (Key: {item['composite_key'][:8]}...)")

        data = (
            revision_name, rev_date, tahun_anggaran,
            item['program_code'], item['program_name'],
            item['activity_code'], item['activity_name'],
            item['output_code'], item['output_name'],
            item['component_code'], item['component_name'],
            item['sub_component_code'], item['sub_component_name'],
            item['account_code'], item['account_name'],
            item['description'], item['volume'], item['unit'], item['unit_price'], item['total_amount'], item['funding_source'],
            item['composite_key']
        )

        try:
            cursor.execute('''
                INSERT INTO budget_items (
                    revision_name, revision_date, tahun_anggaran, program_code, program_name, activity_code, activity_name,
                    output_code, output_name, component_code, component_name,
                    sub_component_code, sub_component_name, account_code, account_name,
                    description, volume, unit, unit_price, total_amount, funding_source,
                    composite_key
                ) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
            ''', data)
            count += 1
            total_all += item['total_amount']
            if item['funding_source'] == 'RM':
                total_rm += item['total_amount']
        except mysql.connector.Error as e:
            if e.errno == 1062: # Duplicate entry
                print(f"    Duplicate skipped: {item['description']}")
            else:
                print(f"    Error inserting item: {e}")

    conn.commit()
    conn.close()
    print(f"Inference Completed. Total items inserted: {count}")
    return count

import argparse

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Import RKK DIPA Excel')
    parser.add_argument('--file', required=True, help='Path to Excel file')
    parser.add_argument('--revision', required=True, help='Revision name')
    parser.add_argument('--year', required=True, type=int, help='Fiscal year')
    
    args = parser.parse_args()
    
    count = convert_rkk(args.file, args.revision, args.year)
    print(f"Imported {count} items from {args.file}")
