import os
import mysql.connector
from mysql.connector import Error

def get_db_config():
    # Helper to parse .env manually if python-dotenv is not installed
    config = {
        'host': '127.0.0.1',
        'port': 3307,
        'database': 'simentordb',
        'user': 'root',
        'password': '@admin'
    }
    
    # Look for .env in the project root (up two levels from scripts/python/)
    env_path = os.path.normpath(os.path.join(os.path.dirname(__file__), '..', '..', '.env'))
    if os.path.exists(env_path):
        with open(env_path, 'r') as f:
            for line in f:
                if '=' in line:
                    key, value = line.strip().split('=', 1)
                    if key == 'DB_HOST': config['host'] = value
                    if key == 'DB_PORT': config['port'] = int(value)
                    if key == 'DB_DATABASE': config['database'] = value
                    if key == 'DB_USERNAME': config['user'] = value
                    if key == 'DB_PASSWORD': config['password'] = value
    return config

def get_connection():
    try:
        config = get_db_config()
        connection = mysql.connector.connect(**config)
        return connection
    except Error as e:
        print(f"Error connecting to MySQL: {e}")
        return None

def execute_script(script_path, connection):
    try:
        with open(script_path, 'r') as f:
            content = f.read()
            cursor = connection.cursor()
            
            # Simple splitter by semicolon (be careful with complex strings)
            statements = content.split(';')
            for statement in statements:
                stmt = statement.strip()
                if stmt:
                    cursor.execute(stmt)
            
            connection.commit()
            cursor.close()
    except Error as e:
        print(f"Error executing script {script_path}: {e}")
