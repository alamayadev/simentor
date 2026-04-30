import os
import sys
import hashlib
import re
import argparse

# Add current directory to sys.path to help IDEs find local modules
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

import mysql.connector
from db_utils import get_connection, execute_script
from converter import convert_rkk

# Configuration
# Point to public/dipa_excel relative to this script
BASE_DIR = os.path.normpath(os.path.join(os.path.dirname(__file__), '..', '..', 'public', 'dipa_excel'))
SCHEMA_PATH = os.path.join(os.path.dirname(__file__), 'schema_mysql.sql')

def get_file_hash(file_path):
    """Calculate SHA-256 hash of a file."""
    sha256_hash = hashlib.sha256()
    with open(file_path, "rb") as f:
        for byte_block in iter(lambda: f.read(4096), b""):
            sha256_hash.update(byte_block)
    return sha256_hash.hexdigest()

def extract_metadata(filename):
    """Extract Revision Name and Date from filename."""
    rev_match = re.search(r'REVISI(?:\s*DIPA)?(?:\s*KE)?[\s_]*(\d+)', filename, re.IGNORECASE)
    rev_num = rev_match.group(1) if rev_match else "X"
    
    # Support both DD_MMM_YYYY and YYYY_MMM_DD
    date_match = re.search(r'(\d{1,2}[\s\-_]*[A-Z]{3,}[\s\-_]*20\d{2})|(20\d{2}[\s\-_]*[A-Z]{3,}[\s\-_]*\d{1,2})', filename.upper())
    date_str = date_match.group(0).replace(' ', '').replace('_', '').replace('-', '') if date_match else "UNKNOWN"
    
    rev_name = f"REVISI_{rev_num}_{date_str}"
    return rev_name

def init_db():
    conn = get_connection()
    if conn:
        execute_script(SCHEMA_PATH, conn)
        conn.close()

def run_auto_import(target_year=None):
    print(f"Starting Automated Budget Ingestion (MySQL) for Year: {target_year or 'Default'}...")
    init_db()
        
    conn = get_connection()
    if not conn:
        return
    cursor = conn.cursor()
    
    files_processed = 0
    files_skipped = 0
    
    for filename in os.listdir(BASE_DIR):
        if (filename.startswith("RINCIAN KERTAS KERJA SATKER") or filename.startswith("RKK_DIPA_REVISI")) and filename.endswith(".xlsx"):
            file_path = os.path.join(BASE_DIR, filename)
            file_hash = get_file_hash(file_path)
            
            # Check if hash already exists in DB
            cursor.execute("SELECT id, revision_name FROM imported_files WHERE file_hash = %s", (file_hash,))
            existing = cursor.fetchone()
            
            if existing:
                print(f"Skipping {filename} (Already imported as {existing[1]})")
                files_skipped += 1
                continue
            
            print(f"New file detected: {filename}")
            revision_name = extract_metadata(filename)
            print(f"Extracted Revision: {revision_name}")
            
            try:
                # Import into budget_items
                count = convert_rkk(file_path, revision_name, target_year)
                
                # Log into imported_files
                cursor.execute("""
                    INSERT INTO imported_files (file_path, file_hash, revision_name, tahun_anggaran)
                    VALUES (%s, %s, %s, %s)
                """, (filename, file_hash, revision_name, target_year))
                conn.commit()
                
                print(f"Successfully imported {count} items.")
                files_processed += 1
            except Exception as e:
                print(f"Error importing {filename}: {e}")
                conn.rollback()
                
    conn.close()
    print(f"\nScan Complete. Processed: {files_processed}, Skipped: {files_skipped}")

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Import DIPA Excel to MySQL.')
    parser.add_argument('--year', type=int, help='Fiscal year (tahun_anggaran)')
    args = parser.parse_args()
    
    run_auto_import(args.year)
