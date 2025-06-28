<?php require_once 'includes/header.php'; ?>

<div class="container">
    <div class="cost-table">
        <h2><i class="fas fa-code"></i> API Testing Interface</h2>
        <p>Test the ESP32 API endpoints for development and debugging</p>
        
        <div class="dashboard" style="margin: 2rem 0;">
            <!-- Upload Current Test -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon current">
                        <i class="fas fa-upload"></i>
                    </div>
                    <div>
                        <h3 class="card-title">Upload Current</h3>
                        <small>Simulate ESP32 data upload</small>
                    </div>
                </div>
                <div style="margin: 1rem 0;">
                    <input type="number" id="testCurrent" placeholder="Current (A)" step="0.01" min="0" max="50" value="15.5" 
                           style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px; margin-right: 10px;">
                    <select id="testRelay" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px; margin-right: 10px;">
                        <option value="ON">ON</option>
                        <option value="OFF">OFF</option>
                    </select>
                    <button onclick="testUpload()" class="control-btn on-btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                        <i class="fas fa-paper-plane"></i> Test Upload
                    </button>
                </div>
                <div id="uploadResult" class="mt-2"></div>
            </div>

            <!-- Get Status Test -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon status">
                        <i class="fas fa-download"></i>
                    </div>
                    <div>
                        <h3 class="card-title">Get Status</h3>
                        <small>Fetch current system status</small>
                    </div>
                </div>
                <div style="margin: 1rem 0;">
                    <button onclick="testGetStatus()" class="control-btn on-btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                        <i class="fas fa-sync"></i> Get Status
                    </button>
                </div>
                <div id="statusResult" class="mt-2"></div>
            </div>

            <!-- Command Test -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon power">
                        <i class="fas fa-terminal"></i>
                    </div>
                    <div>
                        <h3 class="card-title">Send Command</h3>
                        <small>Test relay commands</small>
                    </div>
                </div>
                <div style="margin: 1rem 0;">
                    <button onclick="testCommand('ON')" class="control-btn on-btn" style="padding: 0.5rem 1rem; font-size: 0.9rem; margin-right: 10px;">
                        <i class="fas fa-power-off"></i> ON
                    </button>
                    <button onclick="testCommand('OFF')" class="control-btn off-btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                        <i class="fas fa-power-off"></i> OFF
                    </button>
                </div>
                <div id="commandResult" class="mt-2"></div>
            </div>
        </div>

        <!-- API Documentation -->
        <div class="mt-4">
            <h3><i class="fas fa-book"></i> API Documentation</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Endpoint</th>
                            <th>Method</th>
                            <th>Parameters</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>upload_current.php</code></td>
                            <td>GET</td>
                            <td>current (float), relay (ON/OFF)</td>
                            <td>ESP32 uploads sensor data</td>
                        </tr>
                        <tr>
                            <td><code>get_status.php</code></td>
                            <td>GET</td>
                            <td>None</td>
                            <td>Get latest readings and status</td>
                        </tr>
                        <tr>
                            <td><code>command.php</code></td>
                            <td>GET</td>
                            <td>relay (ON/OFF)</td>
                            <td>Send relay command</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ESP32 Code Example -->
        <div class="mt-4">
            <h3><i class="fas fa-microchip"></i> ESP32 Arduino Code Example</h3>
            <pre style="background: #f8f9fa; padding: 1rem; border-radius: 10px; overflow-x: auto;"><code>
// ESP32 Smart Meter Code Example
#include &lt;WiFi.h&gt;
#include &lt;HTTPClient.h&gt;

const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";
const char* serverURL = "http://yourserver.com/smart";

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");
}

void loop() {
  float current = readCurrentSensor(); // Your sensor reading function
  String relayStatus = digitalRead(RELAY_PIN) ? "ON" : "OFF";
  
  // Upload data
  HTTPClient http;
  String url = String(serverURL) + "/upload_current.php?current=" + 
               String(current) + "&relay=" + relayStatus;
  
  http.begin(url);
  int httpCode = http.GET();
  
  if (httpCode > 0) {
    String response = http.getString();
    Serial.println("Upload response: " + response);
  }
  
  // Check for commands
  http.begin(String(serverURL) + "/get_status.php");
  httpCode = http.GET();
  
  if (httpCode > 0) {
    String response = http.getString();
    // Parse JSON and control relay based on command
  }
  
  http.end();
  delay(5000); // Wait 5 seconds
}
            </code></pre>
        </div>
    </div>
</div>

<script>
function testUpload() {
    const current = document.getElementById('testCurrent').value;
    const relay = document.getElementById('testRelay').value;
    const resultEl = document.getElementById('uploadResult');
    
    if (!current) {
        resultEl.innerHTML = '<div class="text-danger">Please enter a current value</div>';
        return;
    }
    
    resultEl.innerHTML = '<div class="loading"></div> Testing upload...';
    
    fetch(`upload_current.php?current=${current}&relay=${relay}`)
        .then(response => response.json())
        .then(data => {
            resultEl.innerHTML = `
                <div class="text-${data.status === 'success' ? 'success' : 'danger'}">
                    <strong>Status:</strong> ${data.status}<br>
                    <strong>Message:</strong> ${data.message}<br>
                    ${data.data ? `<strong>Data:</strong> <pre>${JSON.stringify(data.data, null, 2)}</pre>` : ''}
                </div>
            `;
        })
        .catch(error => {
            resultEl.innerHTML = `<div class="text-danger">Error: ${error.message}</div>`;
        });
}

function testGetStatus() {
    const resultEl = document.getElementById('statusResult');
    resultEl.innerHTML = '<div class="loading"></div> Fetching status...';
    
    fetch('get_status.php')
        .then(response => response.json())
        .then(data => {
            resultEl.innerHTML = `
                <div class="text-${data.status === 'success' ? 'success' : 'danger'}">
                    <strong>Status:</strong> ${data.status}<br>
                    ${data.data ? `<strong>Data:</strong> <pre>${JSON.stringify(data.data, null, 2)}</pre>` : ''}
                    ${data.message ? `<strong>Message:</strong> ${data.message}` : ''}
                </div>
            `;
        })
        .catch(error => {
            resultEl.innerHTML = `<div class="text-danger">Error: ${error.message}</div>`;
        });
}

function testCommand(cmd) {
    const resultEl = document.getElementById('commandResult');
    resultEl.innerHTML = '<div class="loading"></div> Sending command...';
    
    fetch(`command.php?relay=${cmd}`)
        .then(response => response.json())
        .then(data => {
            resultEl.innerHTML = `
                <div class="text-${data.status === 'success' ? 'success' : 'danger'}">
                    <strong>Status:</strong> ${data.status}<br>
                    <strong>Message:</strong> ${data.message}<br>
                    <strong>Command:</strong> ${data.command || 'N/A'}<br>
                    <strong>Timestamp:</strong> ${data.timestamp || 'N/A'}
                </div>
            `;
        })
        .catch(error => {
            resultEl.innerHTML = `<div class="text-danger">Error: ${error.message}</div>`;
        });
}
</script>

<?php require_once 'includes/footer.php'; ?>
