from flask import Flask, request, jsonify, render_template
from flask_limiter import Limiter
from flask_limiter.util import get_remote_address
import mysql.connector
import logging
from sql_scanner import get_scanner
from classify_severity import classify_severity

app = Flask(__name__)
limiter = Limiter(app=app, key_func=get_remote_address, default_limits=["10 per minute"])

# MySQL Configuration
DB_CONFIG = {
    'host': 'localhost',
    'user': 'scanner_user',
    'password': 'Scanner@123',
    'database': 'injexcanner_db',
    'autocommit': True
}

def get_db():
    return mysql.connector.connect(**DB_CONFIG)

def create_tables():
    """Initialize database schema"""
    conn = get_db()
    cursor = conn.cursor()
    
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS scans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            url TEXT NOT NULL,
            scan_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
    """)
    
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS vulnerabilities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            scan_id INT NOT NULL,
            parameter TEXT NOT NULL,
            payload TEXT NOT NULL,
            type VARCHAR(50),
            severity VARCHAR(50),
            evidence TEXT,
            FOREIGN KEY (scan_id) REFERENCES scans(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
    """)
    
    conn.close()

@app.route("/")
def home():
    """Render scanner UI"""
    return render_template("index.html")

@app.route("/scan", methods=["POST"])
@limiter.limit("5 per minute")
def handle_scan():
    """Scan endpoint with proxy support"""
    try:
        data = request.get_json()
        url = data.get('url', '').strip()
        proxies = data.get('proxies', [])
        
        if not url.startswith(('http://', 'https://')):
            url = f'http://{url}'
        
        scanner = get_scanner(proxies=proxies)
        results = scanner.scan_url(url) or []
        
        conn = get_db()
        cursor = conn.cursor()
        updated_results = []

        cursor.execute("INSERT INTO scans (url) VALUES (%s)", (url,))
        scan_id = cursor.lastrowid

        for result in results:
            severity = classify_severity(result)
            cursor.execute("""
                INSERT INTO vulnerabilities 
                (scan_id, parameter, payload, type, severity, evidence)
                VALUES (%s, %s, %s, %s, %s, %s)
            """, (
                scan_id,
                result.get('param'),
                result.get('payload'),
                result.get('type'),
                severity,
                str(result.get('response'))[:500]
            ))
            
            updated_results.append({
                'param': result.get('param'),
                'payload': result.get('payload'),
                'type': result.get('type'),
                'severity': severity,
                'evidence': result.get('response')[:200] + '...' if len(str(result.get('response'))) > 200 else result.get('response')
            })

        conn.commit()
        return jsonify({
            "status": "success", 
            "scan_id": scan_id,
            "results": updated_results
        })

    except Exception as e:
        logging.error(f"Scan error: {str(e)}", exc_info=True)
        return jsonify({"error": str(e)}), 500
    finally:
        if 'conn' in locals() and conn.is_connected():
            conn.close()

@app.route("/history", methods=["GET"])
def get_history():
    """Retrieve scan history"""
    try:
        conn = get_db()
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT s.id, s.url, s.scan_time, COUNT(v.id) as vuln_count
            FROM scans s LEFT JOIN vulnerabilities v ON s.id = v.scan_id
            GROUP BY s.id ORDER BY s.scan_time DESC LIMIT 50
        """)
        
        return jsonify(cursor.fetchall())
    except Exception as e:
        return jsonify({"error": str(e)}), 500
    finally:
        if 'conn' in locals() and conn.is_connected():
            conn.close()

@app.route("/results/<int:scan_id>", methods=["GET"])
def get_scan_details(scan_id):
    """Get detailed results for specific scan"""
    try:
        conn = get_db()
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT parameter, payload, type, severity, evidence
            FROM vulnerabilities
            WHERE scan_id = %s
        """, (scan_id,))
        
        return jsonify(cursor.fetchall())
    except Exception as e:
        return jsonify({"error": str(e)}), 500
    finally:
        if 'conn' in locals() and conn.is_connected():
            conn.close()

if __name__ == "__main__":
    create_tables()
    logging.basicConfig(level=logging.DEBUG)
    app.run(host='0.0.0.0', port=5000, debug=True)