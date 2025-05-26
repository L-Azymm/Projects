document.addEventListener('DOMContentLoaded', function() {
    loadScanHistory();
});

async function loadScanHistory() {
    try {
        const response = await fetch('/history');
        const scans = await response.json();
        const historyDiv = document.getElementById('scan-history');
        
        historyDiv.innerHTML = `
            <h3>Scan History</h3>
            ${scans.map(scan => `
                <div class="scan-item">
                    <p><strong>URL:</strong> ${scan.url}</p>
                    <p><strong>Time:</strong> ${new Date(scan.scan_time).toLocaleString()}</p>
                    <p><strong>Vulnerabilities Found:</strong> ${scan.vuln_count}</p>
                </div>
            `).join('')}
        `;
    } catch (error) {
        console.error('Failed to load history:', error);
        historyDiv.innerHTML = `<div class="error">Error loading scan history</div>`;
    }
}

document.getElementById('scan-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const urlInput = document.getElementById('scan-url');
    const statusDiv = document.getElementById('scan-status');
    const resultsBody = document.getElementById('scan-results');
    const url = urlInput.value.trim();

    statusDiv.textContent = `Scanning ${url}...`;
    statusDiv.className = 'status scanning';
    resultsBody.innerHTML = '<tr><td colspan="5">Scanning... Please wait</td></tr>';

    try {
        const response = await fetch('/scan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ url })
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Scan failed');
        }

        // Clear previous results
        resultsBody.innerHTML = '';
        
        if (data.results.length === 0) {
            resultsBody.innerHTML = `
                <tr>
                    <td colspan="5" class="no-results">
                        No vulnerabilities found
                    </td>
                </tr>
            `;
        } else {
            data.results.forEach(result => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${result.param || 'N/A'}</td>
                    <td><code>${result.payload}</code></td>
                    <td>${result.type}</td>
                    <td class="severity-${result.severity?.toLowerCase() || 'low'}">
                        ${result.severity || 'Low'}
                    </td>
                    <td>${result.response ? 
                        (result.response.substring(0, 50) + 
                        (result.response.length > 50 ? '...' : '')) : 
                        'No direct evidence'}</td>
                `;
                resultsBody.appendChild(row);
            });
        }

        statusDiv.textContent = data.results.length ? 
            `Found ${data.results.length} potential vulnerabilities` : 
            'No vulnerabilities detected';
        statusDiv.className = data.results.length ? 'status warning' : 'status success';
        
        // Refresh history
        await loadScanHistory();
        
    } catch (error) {
        resultsBody.innerHTML = `
            <tr>
                <td colspan="5" class="error">
                    Error: ${error.message}
                </td>
            </tr>
        `;
        statusDiv.textContent = `Error: ${error.message}`;
        statusDiv.className = 'status error';
    } finally {
        urlInput.value = '';  // Clear input field
    }
});