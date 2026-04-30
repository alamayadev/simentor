# Setup Python Environment (Windows)

This document explains how to set up the Python environment required for the DIPA Budget Ingestion pipeline on a new Windows machine.

## 1. Install Python
Download and install Python 3.10 or higher from [python.org](https://www.python.org/downloads/windows/).

> [!IMPORTANT]
> During installation, make sure to check the box **"Add Python to PATH"**.

## 2. Install Dependencies
Open your terminal (PowerShell or Command Prompt) and run the following command to install the required libraries:

```powershell
pip install pandas mysql-connector-python openpyxl
```

- **pandas**: Used for processing the logic of Excel budgets.
- **mysql-connector-python**: Used to communicate with the MySQL/MariaDB database.
- **openpyxl**: Engine required by pandas to read `.xlsx` files.

## 3. Configure Laravel Environment
Ensure your `.env` file in the `backend/` folder points to your Python executable. 

### How to find your Python Location:
If you are unsure where Python is installed, run one of these commands in your terminal:

*   **Option A (Quickest):**
    ```powershell
    where python
    ```
*   **Option B (Most Reliable):**
    ```powershell
    python -c "import sys; print(sys.executable)"
    ```

Once you have the path, update the `PYTHON_BINARY` key in your `.env`:

```env
# Example using forward slashes (recommended for Laravel/PHP on Windows)
PYTHON_BINARY="C:/Python313/python.exe"

# If you use the Python Launcher, you can use:
# PYTHON_BINARY=py
```

## 4. Verify Setup
You can test the ingestion script manually to ensure it can connect to the database:

```powershell
cd backend
php artisan tinker
# Inside tinker, you can test the command used by the controller:
# Process::run("python scripts/python/auto_import.py --year 2026")->output();
```

## Troubleshooting
- **ModuleNotFoundError**: If you see this error, ensure you ran the `pip install` command above.
- **mysql.connector.Error**: Ensure your database is running on the correct port (default is `3307` in this project) and credentials in `.env` are correct.
- **'python' is not recognized**: Use the full absolute path in `PYTHON_BINARY` as shown in step 3.
