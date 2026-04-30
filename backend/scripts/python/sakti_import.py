import pandas as pd
import mysql.connector
from mysql.connector import Error
import os
import sys
import argparse
import re
import hashlib
from db_utils import get_connection

def clean_description(desc):
    """
    Trims the first 8 characters as requested by the user.
    e.g. '000380. Transport' -> 'Transport'
    """
    if not desc or len(desc) <= 8:
        return desc
    return desc[8:].strip()

def generate_item_match_key(year, program, activity, output, component, sub_comp, account, cleaned_desc):
    # Normalize: strip all non-alphanumeric and lowercase
    norm_desc = re.sub(r'[^a-zA-Z0-9]', '', cleaned_desc).lower()
    
    # Matches the MD5(CONCAT_WS(...)) logic in the database view
    key_src = "|".join([
        str(year), str(program), str(activity),
        str(output), str(component), str(sub_comp),
        str(account), 
        norm_desc
    ])
    return hashlib.md5(key_src.encode()).hexdigest()

def normalize_component_code(raw_value):
    """
    Converts component markers like:
    - '51.0'   -> '051'
    - '2.0'    -> '002'
    - '051.0A' -> '051'
    """
    if raw_value is None:
        return ''

    match = re.match(r'^(\d{1,3})', str(raw_value).strip())
    if not match:
        return ''

    return match.group(1).zfill(3)

def parse_sakti_hierarchical(file_path, year, usage_date):
    print(f"Ingesting SAKTI GLP039 report (Item-Level): {file_path} for Year {year} and usage date {usage_date}...")
    
    try:
        xl = pd.ExcelFile(file_path)
        sheet_name = xl.sheet_names[0]
        df = pd.read_excel(file_path, sheet_name=sheet_name, header=None).fillna('')
    except Exception as e:
        print(f"Error reading Excel: {e}")
        return 0

    conn = get_connection()
    if not conn:
        return 0
    cursor = conn.cursor()

    try:
        # Clear old data for the same year and usage date
        cursor.execute(
            "DELETE FROM budget_sakti_realizations WHERE tahun_anggaran = %s AND usage_date = %s",
            (year, usage_date)
        )
        print(f"Cleared existing SAKTI data for year {year} and usage date {usage_date}")

        # Hierarchy state
        state = {
            'program_code': '', 'program_name': '',
            'activity_code': '', 'activity_name': '',
            'output_code': '', 'output_name': '',
            'component_code': '', 'component_name': '',
            'sub_component_code': '', 'sub_component_name': '',
            'account_code': '', 'account_name': ''
        }
        
        current_kegiatan = ''

        count = 0
        
        for index, row in df.iterrows():
            row_data = [str(x).strip() for x in row]
            
            # Segment mapping based on the current FA Detail 16-segmen layout
            col1 = row_data[1]   # Main code block, e.g. WA.2886
            col2 = row_data[2]   # Activity/output block, e.g. EBA or EBA.994
            col4 = row_data[4]   # Component block, e.g. 2.0 or 51.0
            col5 = row_data[5]   # Sub-component block, e.g. 002.0A or 051.0A
            col7 = row_data[7]   # Account block, e.g. 522111.0
            col12 = row_data[12] # Account Name
            col13 = row_data[13] # Potential Item Description
            
            # 1. Program detection
            # Example: WA.2886 -> program_code = 2886, program_name = col8
            match_keg = re.match(r'^[A-Z]{2}\.(\d{4})$', col1)
            if match_keg:
                current_kegiatan = match_keg.group(1)
                state['program_code'] = current_kegiatan
                state['program_name'] = row_data[8]
                state['activity_code'] = ''
                state['activity_name'] = ''
                state['output_code'] = ''
                state['output_name'] = ''
                state['component_code'] = ''
                state['component_name'] = ''
                state['sub_component_code'] = ''
                state['sub_component_name'] = ''
                state['account_code'] = ''
                state['account_name'] = ''
                continue

            # 2. Activity detection
            # Example: EBA -> activity_code = 2886.EBA, activity_name = col6
            match_out = re.match(r'^[A-Z]{3}$', col2)
            if match_out and current_kegiatan:
                state['activity_code'] = f"{current_kegiatan}.{match_out.group(0)}"
                state['activity_name'] = row_data[6]
                state['output_code'] = ''
                state['output_name'] = ''
                state['component_code'] = ''
                state['component_name'] = ''
                state['sub_component_code'] = ''
                state['sub_component_name'] = ''
                state['account_code'] = ''
                state['account_name'] = ''
                continue
                
            # 3. Output detection
            # Example: EBA.994 -> output_code = 2886.EBA.994, output_name = col10
            match_subout = re.match(r'^[A-Z]{3}\.(\d{3})$', col2)
            if match_subout and current_kegiatan:
                state['output_code'] = f"{current_kegiatan}.{match_subout.group(0)}"
                state['output_name'] = row_data[10]
                state['component_code'] = ''
                state['component_name'] = ''
                state['sub_component_code'] = ''
                state['sub_component_name'] = ''
                state['account_code'] = ''
                state['account_name'] = ''
                continue

            # 4. Component detection
            # Example: 2.0 -> component_code = 002, component_name = col9
            if re.match(r'^\d{1,3}\.0$', col4):
                state['component_code'] = normalize_component_code(col4)
                state['component_name'] = row_data[9]
                state['sub_component_code'] = ''
                state['sub_component_name'] = ''
                state['account_code'] = ''
                state['account_name'] = ''
                continue

            # 5. Sub-component detection
            # Example: 002.0A -> sub_component_code = A, sub_component_name = col11
            match_comp = re.match(r'^\d{1,3}\.0?([A-Z])$', col5)
            if match_comp:
                if not state['component_code']:
                    state['component_code'] = normalize_component_code(col5)
                state['sub_component_code'] = match_comp.group(1)
                state['sub_component_name'] = row_data[11]
                state['account_code'] = ''
                state['account_name'] = ''
                continue

            # 6. Account detection
            match_akun = re.match(r'^(\d{6})(\.0)?$', col7)
            if match_akun:
                state['account_code'] = match_akun.group(1)
                state['account_name'] = col12
                continue

            # 7. Item detail detection
            if state['account_code'] and col13 and re.match(r'^\d{6}\.', col13):
                raw_desc = col13
                cleaned_desc = clean_description(raw_desc)
                
                # Realization columns
                try:
                    p_lalu = float(row[22]) if not pd.isna(row[22]) and row[22] != '' else 0
                    p_ini = float(row[23]) if not pd.isna(row[23]) and row[23] != '' else 0
                    p_sd = float(row[25]) if not pd.isna(row[25]) and row[25] != '' else 0
                except:
                    p_lalu = p_ini = p_sd = 0

                if p_sd != 0 or p_ini != 0:
                    comp_key = generate_item_match_key(
                        year, state['program_code'], state['activity_code'],
                        state['output_code'], state['component_code'],
                        state['sub_component_code'], state['account_code'],
                        cleaned_desc
                    )
                    
                    try:
                        cursor.execute('''
                            INSERT INTO budget_sakti_realizations (
                                tahun_anggaran, usage_date, program_code, program_name, activity_code, activity_name,
                                output_code, output_name, component_code, component_name,
                                sub_component_code, sub_component_name, account_code, account_name,
                                description, original_description,
                                periode_lalu, periode_ini, sd_periode, total_amount,
                                composite_key
                            ) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
                        ''', (
                            year, 
                            usage_date,
                            state['program_code'], state['program_name'],
                            state['activity_code'], state['activity_name'],
                            state['output_code'], state['output_name'],
                            state['component_code'], state['component_name'],
                            state['sub_component_code'], state['sub_component_name'],
                            state['account_code'], state['account_name'],
                            cleaned_desc, raw_desc,
                            p_lalu, p_ini, p_sd, p_sd,
                            comp_key
                        ))
                        count += 1
                    except Error as e:
                        print(f"Error inserting row at index {index}: {e}")

        conn.commit()
        print(f"Successfully ingested {count} SAKTI item-level realizations.")
        return count

    except Error as e:
        print(f"Database error: {e}")
        return 0
    finally:
        cursor.close()
        conn.close()

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Import SAKTI FA Detail Excel at Item Level')
    parser.add_argument('--file', required=True, help='Path to Excel file')
    parser.add_argument('--year', required=True, type=int, help='Fiscal year')
    parser.add_argument('--usage-date', required=True, help='Usage date in YYYY-MM-DD format')
    
    args = parser.parse_args()
    parse_sakti_hierarchical(args.file, args.year, args.usage_date)
