import re

SEVERITY_RULES = [
    {
        "level": "Critical",
        "patterns": [
            r'sleep\(.*\)', 
            r'benchmark\(.*\)',
            r'shutdown', 
            r'drop\s+table',
            r'xp_cmdshell', 
            r'load_file\(.*\)',
            r'/\*!sleep\(\d+\)',  # MySQL-specific
            r'procedure\s+analyse'
        ],
        "conditions": [
            lambda r: "delay" in r.lower() or 
                     "empty response" in r.lower()
        ]
    },
    {
        "level": "High",
        "patterns": [
            r'union\s+select', 
            r'information_schema',
            r'@@version', 
            r'database\(\)',
            r'extractvalue\(.*\)',
            r'updatexml\(.*\)'
        ],
        "conditions": [
            lambda r: "sql error" in r.lower() or 
                     "status code" in r.lower()
        ]
    },
    {
        "level": "Medium",
        "patterns": [
            r'or\s+1=1', 
            r'and\s+1=1',
            r'--\s*$', 
            r'/\*.*\*/',
            r'waitfor\s+delay',
            r'into\s+outfile'
        ]
    }
]

def classify_severity(vuln_data):
    payload = vuln_data.get('payload', '').lower()
    response = vuln_data.get('response', '').lower()
    
    for rule in SEVERITY_RULES:
        if any(re.search(p, payload) for p in rule["patterns"]):
            return rule["level"]
        if "conditions" in rule and any(c(response) for c in rule["conditions"]):
            return rule["level"]
    return "Low"