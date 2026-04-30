import argparse
import json
from collections import Counter, defaultdict

from converter import extract_rkk_items, parse_numeric
from db_utils import get_connection


def audit_rkk_import(file_path, revision_name, tahun_anggaran):
    parsed_items = extract_rkk_items(file_path, tahun_anggaran)
    parsed_keys = [item['composite_key'] for item in parsed_items]
    parsed_key_set = set(parsed_keys)
    duplicate_counter = Counter(parsed_keys)

    conn = get_connection()
    if not conn:
        raise RuntimeError('Database connection failed')

    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        '''
        SELECT composite_key, description, account_code, total_amount
        FROM budget_items
        WHERE revision_name = %s AND tahun_anggaran = %s
        ''',
        (revision_name, tahun_anggaran)
    )
    db_rows = cursor.fetchall()
    conn.close()

    db_map = {row['composite_key']: row for row in db_rows}
    db_key_set = set(db_map.keys())

    missing_keys = parsed_key_set - db_key_set
    extra_keys = db_key_set - parsed_key_set

    missing_rows = []
    for item in parsed_items:
        if item['composite_key'] in missing_keys:
            missing_rows.append({
                'row_number': item['row_number'],
                'description': item['description'],
                'account_code': item['account_code'],
                'total_amount': parse_numeric(item['total_amount']),
                'composite_key': item['composite_key'],
            })

    duplicate_rows = defaultdict(list)
    for item in parsed_items:
        if duplicate_counter[item['composite_key']] > 1:
            duplicate_rows[item['composite_key']].append({
                'row_number': item['row_number'],
                'description': item['description'],
                'account_code': item['account_code'],
                'total_amount': parse_numeric(item['total_amount']),
            })

    extra_db_rows = []
    for key in extra_keys:
        row = db_map[key]
        extra_db_rows.append({
            'composite_key': key,
            'description': row['description'],
            'account_code': row['account_code'],
            'total_amount': parse_numeric(row['total_amount']),
        })

    duplicate_details = [
        {
            'composite_key': key,
            'occurrences': len(rows),
            'rows': rows,
        }
        for key, rows in duplicate_rows.items()
    ]

    return {
        'file_path': file_path,
        'revision_name': revision_name,
        'tahun_anggaran': int(tahun_anggaran),
        'source_row_count': len(parsed_items),
        'source_unique_key_count': len(parsed_key_set),
        'database_row_count': len(db_rows),
        'missing_row_count': len(missing_rows),
        'extra_db_row_count': len(extra_db_rows),
        'duplicate_source_key_count': len(duplicate_details),
        'is_complete': len(missing_rows) == 0 and len(extra_db_rows) == 0,
        'missing_rows': sorted(missing_rows, key=lambda row: row['row_number']),
        'extra_db_rows': sorted(extra_db_rows, key=lambda row: (row['account_code'] or '', row['description'] or '')),
        'duplicate_source_rows': sorted(duplicate_details, key=lambda row: (-row['occurrences'], row['composite_key'])),
    }


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Audit RKK DIPA import integrity against budget_items.')
    parser.add_argument('--file', required=True, help='Path to Excel file')
    parser.add_argument('--revision', required=True, help='Revision name')
    parser.add_argument('--year', required=True, type=int, help='Fiscal year')

    args = parser.parse_args()
    result = audit_rkk_import(args.file, args.revision, args.year)
    print(json.dumps(result, ensure_ascii=False))
