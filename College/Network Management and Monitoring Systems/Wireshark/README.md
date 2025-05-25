# NTC1052 Mini Project: Web Traffic Analysis using Wireshark

This walkthrough documents the steps taken to monitor and analyze web traffic using Wireshark on a host machine. The objective is to capture and inspect DNS, HTTP, and HTTPS communications from a browser session.

---

## 🔧 Environment Setup

- **Wireshark** installed on host system (Windows)
- **Npcap** installed with the following options enabled:
  - ✅ Restrict Npcap driver access to administrators only
  - ✅ Support raw 802.11 traffic for wireless adapters
  - ✅ Install Npcap in WinPcap API-compatible mode

---

## 🟢 Step 1: Start Wireshark and Select Interface

1. Launch **Wireshark**.
2. Select the correct interface:
   - If using Wi-Fi: select `Wi-Fi` or equivalent.
   - If using Ethernet: select `Ethernet`.
3. Confirm that packets are appearing in real time before continuing.

---

## 🟡 Step 2: Trigger Web Traffic from Browser

While Wireshark is running, open a web browser and visit the following websites:

1. [`http://neverssl.com`](http://neverssl.com) – to trigger **HTTP** traffic.
2. [`https://example.com`](https://example.com) – to trigger **HTTPS** (simple TLS handshake).
3. [`https://google.com`](https://google.com) – to trigger **DNS + HTTPS** and more encrypted traffic.

Allow each site to fully load before continuing.

---

## 🔴 Step 3: Stop and Save the Packet Capture

1. Go back to Wireshark.
2. Click the red **Stop** button to end the capture.
3. Save the file as `web_traffic.pcapng` using:

```bash
File → Save As → Choose location
```


---

## 🔍 Step 4: Analyze Captured Traffic

### 🔎 DNS Analysis

- **Filter**: `dns`
- Look for DNS queries like:
- `Standard query A example.com`
- And their corresponding responses (with resolved IPs)
- **Screenshot**: Expand packet details of a DNS query and response.

---

### 🌐 HTTP Analysis

- **Filter**: `http`
- Look for:
- `GET / HTTP/1.1` requests to `neverssl.com`
- `HTTP/1.1 200 OK` responses
- **Screenshot**: Capture request + response details in packet pane.

---

### 🔐 HTTPS / TLS Analysis

- **Filter**: `tls`
- Look for:
- `Client Hello`
- `Server Hello`
- Certificate exchange
- **Screenshot**: Expand details of Client Hello and Server Hello packets.

---

### 📡 IP and Port Analysis

- **Filter**: None, or use `ip.addr == <your_ip>` (replace with your actual IP)
- Right-click → Follow TCP Stream / Follow UDP Stream to view full conversations.
- Observe source/destination IP addresses and port numbers.
- **Screenshot**: TCP stream window showing packet exchange.

---

## ✅ Summary

This analysis demonstrates how Wireshark can be used to inspect different types of traffic:

- DNS name resolution
- HTTP cleartext web traffic
- TLS encrypted HTTPS communication
- Source and destination IP/port tracing

Wireshark is a powerful tool to understand what happens on a network and is highly useful for network monitoring and security analysis.

---
