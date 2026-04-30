import pandas as pd
import sys

def check_excel(file_path):
    print(f"Checking file: {file_path}")
    try:
        # Try finding the 'FA. Detail' sheet or use first
        xl = pd.ExcelFile(file_path)
        print(f"Sheets: {xl.sheet_names}")
        
        df = pd.read_excel(file_path, sheet_name=0, header=None)
        print(f"Shape: {df.shape}")
        
        # Look for headers
        print("First 10 rows:")
        print(df.head(10).to_string())
        
        # Check column AD (29) and AR (43)
        print("\nColumn 29 (AD) and 43 (AR) sample:")
        print(df.iloc[5:15, [29, 43]])
        
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    check_excel(sys.argv[1])
