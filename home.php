<?php require_once 'includes/header.php'; ?>

<div class="container">
    <!-- Control Panel -->
    <div class="dashboard">
        <div class="card" style="grid-column: 1 / -1;">
            <div class="card-header">
                <div class="card-icon status">
                    <i class="fas fa-toggle-on"></i>
                </div>
                <div>
                    <h3 class="card-title">ESP32 Relay Control</h3>
                    <small>Remote control for connected devices</small>
                </div>
            </div>
            
            <div style="display: flex; gap: 2rem; align-items: center; justify-content: center; margin: 2rem 0;">
                <button id="onBtn" class="control-btn on-btn" onclick="sendCommand('ON')">
                    <i class="fas fa-power-off"></i>
                    <span>Turn ON</span>
                </button>
                <button id="offBtn" class="control-btn off-btn" onclick="sendCommand('OFF')">
                    <i class="fas fa-power-off"></i>
                    <span>Turn OFF</span>
                </button>
            </div>
            
            <div id="commandStatus" class="command-status">
                <i class="fas fa-info-circle"></i>
                <span>Ready to send commands</span>
            </div>
        </div>
    </div>

    <!-- Live Status Dashboard -->
    <div class="dashboard">
        <!-- Current Reading Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon current">
                    <i class="fas fa-bolt"></i>
                </div>
                <div>
                    <h3 class="card-title">Live Current</h3>
                    <small>Real-time measurement</small>
                </div>
            </div>
            <div class="card-value" id="currentValue">--</div>
            <div class="card-unit">Amperes (A)</div>
            <div class="mt-2">
                <small class="text-info" id="lastUpdate">
                    <i class="fas fa-clock"></i>
                    Waiting for data...
                </small>
            </div>
        </div>

        <!-- Power Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon power">
                    <i class="fas fa-plug"></i>
                </div>
                <div>
                    <h3 class="card-title">Live Power</h3>
                    <small>Calculated at 220V</small>
                </div>
            </div>
            <div class="card-value" id="powerValue">--</div>
            <div class="card-unit">Watts (W)</div>
            <div class="mt-2">
                <small class="text-success" id="voltageInfo">
                    <i class="fas fa-info-circle"></i>
                    Voltage: 220V
                </small>
            </div>
        </div>

        <!-- Relay Status Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon status">
                    <i class="fas fa-toggle-on"></i>
                </div>
                <div>
                    <h3 class="card-title">Relay Status</h3>
                    <small>Current device state</small>
                </div>
            </div>
            <div class="card-value" id="relayStatus">
                <i class="fas fa-question-circle"></i>
            </div>
            <div class="card-unit" id="relayText">Unknown</div>
            <div class="mt-2">
                <small class="text-info" id="lastCommand">
                    <i class="fas fa-terminal"></i>
                    Last command: --
                </small>
            </div>
        </div>

        <!-- Cost Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon cost">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <h3 class="card-title">Today's Cost</h3>
                    <small>Running total</small>
                </div>
            </div>
            <div class="card-value" id="dailyCost">₨--</div>
            <div class="card-unit">Nepalese Rupees</div>
            <div class="mt-2">
                <small class="text-success" id="dailyEnergy">
                    <i class="fas fa-leaf"></i>
                    Energy: -- kWh
                </small>
            </div>
        </div>
    </div>

    <!-- Connection Status -->
    <div class="cost-table">
        <h2><i class="fas fa-wifi"></i> ESP32 Connection Status</h2>
        <div id="connectionStatus" class="connection-status">
            <div class="status-indicator status-off">
                <i class="fas fa-circle"></i>
                <span>Checking connection...</span>
            </div>
        </div>
        
        <div class="mt-3">
            <h3><i class="fas fa-chart-line"></i> Live Data Stream</h3>
            <div id="dataLog" class="data-log">
                <p><i class="fas fa-info-circle"></i> Waiting for ESP32 data...</p>
            </div>
        </div>
    </div>
</div>

<style>
/* Control Button Styles */
.control-btn {
    padding: 1.5rem 3rem;
    font-size: 1.2rem;
    font-weight: 600;
    border: none;
    border-radius: 15px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    min-width: 150px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.control-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.control-btn:active {
    transform: translateY(0);
}

.on-btn {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: white;
}

.on-btn:hover {
    background: linear-gradient(135deg, #229954, #27ae60);
}

.off-btn {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
}

.off-btn:hover {
    background: linear-gradient(135deg, #c0392b, #a93226);
}

.control-btn i {
    font-size: 2rem;
}

.command-status {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 1rem;
    border-radius: 10px;
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
    font-weight: 500;
}

.connection-status {
    text-align: center;
    padding: 2rem;
}

.data-log {
    background: rgba(0,0,0,0.05);
    border-radius: 10px;
    padding: 1rem;
    max-height: 200px;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
}

.data-log p {
    margin: 0.5rem 0;
    padding: 0.5rem;
    border-left: 3px solid #3498db;
    background: white;
    border-radius: 5px;
}

@media (max-width: 768px) {
    .control-btn {
        min-width: 120px;
        padding: 1rem 2rem;
        font-size: 1rem;
    }
    
    .control-btn i {
        font-size: 1.5rem;
    }
}
</style>

<script>
let isConnected = false;
let lastDataTime = null;

function sendCommand(cmd) {
    const statusEl = document.getElementById('commandStatus');
    const onBtn = document.getElementById('onBtn');
    const offBtn = document.getElementById('offBtn');
    
    // Disable buttons during command
    onBtn.disabled = true;
    offBtn.disabled = true;
    
    statusEl.innerHTML = '<div class="loading"></div> Sending command...';
    statusEl.style.background = 'rgba(243, 156, 18, 0.1)';
    statusEl.style.color = '#f39c12';
    
    fetch(`command.php?relay=${cmd}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                statusEl.innerHTML = `<i class="fas fa-check-circle"></i> ${data.message}`;
                statusEl.style.background = 'rgba(46, 204, 113, 0.1)';
                statusEl.style.color = '#27ae60';
                showToast(`Relay turned ${cmd}`, 'success');
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            statusEl.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Error: ${error.message}`;
            statusEl.style.background = 'rgba(231, 76, 60, 0.1)';
            statusEl.style.color = '#e74c3c';
            showToast('Command failed', 'error');
        })
        .finally(() => {
            // Re-enable buttons
            onBtn.disabled = false;
            offBtn.disabled = false;
            
            // Reset status after 3 seconds
            setTimeout(() => {
                statusEl.innerHTML = '<i class="fas fa-info-circle"></i> Ready to send commands';
                statusEl.style.background = 'rgba(52, 152, 219, 0.1)';
                statusEl.style.color = '#3498db';
            }, 3000);
        });
}

function updateData() {
    fetch('get_status.php')
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                const data = result.data;
                
                // Update connection status
                isConnected = true;
                lastDataTime = new Date();
                updateConnectionStatus();
                
                // Update current
                document.getElementById('currentValue').textContent = data.current.toFixed(2);
                
                // Update power
                document.getElementById('powerValue').textContent = Math.round(data.power_watts);
                
                // Update relay status
                const relayStatusEl = document.getElementById('relayStatus');
                const relayTextEl = document.getElementById('relayText');
                
                if (data.relay_status === 'ON') {
                    relayStatusEl.innerHTML = '<i class="fas fa-toggle-on text-success"></i>';
                    relayTextEl.textContent = 'ON';
                    relayTextEl.className = 'card-unit text-success';
                } else {
                    relayStatusEl.innerHTML = '<i class="fas fa-toggle-off text-danger"></i>';
                    relayTextEl.textContent = 'OFF';
                    relayTextEl.className = 'card-unit text-danger';
                }
                
                // Update costs
                document.getElementById('dailyCost').textContent = `₨${data.daily_cost_npr.toFixed(2)}`;
                document.getElementById('dailyEnergy').innerHTML = 
                    `<i class="fas fa-leaf"></i> Energy: ${data.daily_energy_kwh.toFixed(3)} kWh`;
                
                // Update timestamps
                document.getElementById('lastUpdate').innerHTML = 
                    `<i class="fas fa-clock"></i> Updated: ${new Date().toLocaleTimeString()}`;
                document.getElementById('lastCommand').innerHTML = 
                    `<i class="fas fa-terminal"></i> Last command: ${data.last_command}`;
                
                // Add to data log
                addToDataLog(`Current: ${data.current}A, Power: ${Math.round(data.power_watts)}W, Status: ${data.relay_status}`);
                
            } else {
                throw new Error(result.message);
            }
        })
        .catch(error => {
            isConnected = false;
            updateConnectionStatus();
            console.error('Error fetching data:', error);
        });
}

function updateConnectionStatus() {
    const statusEl = document.getElementById('connectionStatus');
    
    if (isConnected && lastDataTime && (new Date() - lastDataTime) < 10000) {
        statusEl.innerHTML = `
            <div class="status-indicator status-on">
                <i class="fas fa-circle"></i>
                <span>ESP32 Connected - Data flowing</span>
            </div>
        `;
    } else {
        statusEl.innerHTML = `
            <div class="status-indicator status-off">
                <i class="fas fa-circle"></i>
                <span>ESP32 Disconnected - No recent data</span>
            </div>
        `;
    }
}

function addToDataLog(message) {
    const logEl = document.getElementById('dataLog');
    const timestamp = new Date().toLocaleTimeString();
    const logEntry = document.createElement('p');
    logEntry.innerHTML = `<strong>${timestamp}:</strong> ${message}`;
    
    // Add to top
    logEl.insertBefore(logEntry, logEl.firstChild);
    
    // Keep only last 10 entries
    while (logEl.children.length > 10) {
        logEl.removeChild(logEl.lastChild);
    }
}

// Update data every 3 seconds
setInterval(updateData, 3000);

// Initial data load
document.addEventListener('DOMContentLoaded', function() {
    updateData();
    showToast('ESP32 Control Panel loaded', 'info');
});

// Check connection status every 10 seconds
setInterval(updateConnectionStatus, 10000);
</script>

<?php require_once 'includes/footer.php'; ?>
