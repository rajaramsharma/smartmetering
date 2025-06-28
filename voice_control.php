<?php require_once 'includes/header.php'; ?>

<div class="container">
    <!-- Voice Control Panel -->
    <div class="dashboard">
        <div class="card" style="grid-column: 1 / -1;">
            <div class="card-header">
                <div class="card-icon status">
                    <i class="fas fa-microphone"></i>
                </div>
                <div>
                    <h3 class="card-title">Voice Control System</h3>
                    <small>Control your smart meter with voice commands</small>
                </div>
            </div>
            
            <div class="voice-control-panel">
                <div class="microphone-container">
                    <button id="voiceBtn" class="microphone-btn" onclick="toggleVoiceControl()">
                        <i class="fas fa-microphone" id="micIcon"></i>
                    </button>
                    <div class="voice-status" id="voiceStatus">Click to start voice control</div>
                </div>
                
                <div class="voice-commands">
                    <h4><i class="fas fa-list"></i> Available Commands:</h4>
                    <div class="command-grid">
                        <div class="command-item">
                            <i class="fas fa-power-off text-success"></i>
                            <span>"Turn on" or "Switch on"</span>
                        </div>
                        <div class="command-item">
                            <i class="fas fa-power-off text-danger"></i>
                            <span>"Turn off" or "Switch off"</span>
                        </div>
                        <div class="command-item">
                            <i class="fas fa-info-circle text-info"></i>
                            <span>"Status" or "Show status"</span>
                        </div>
                        <div class="command-item">
                            <i class="fas fa-chart-line text-warning"></i>
                            <span>"Show energy" or "Energy report"</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Status Display -->
    <div class="dashboard">
        <div class="card">
            <div class="card-header">
                <div class="card-icon current">
                    <i class="fas fa-bolt"></i>
                </div>
                <div>
                    <h3 class="card-title">Current Status</h3>
                    <small>Real-time device status</small>
                </div>
            </div>
            <div class="card-value" id="currentReading">--</div>
            <div class="card-unit">Amperes (A)</div>
            <div class="mt-2">
                <span class="status-indicator" id="relayStatusIndicator">
                    <i class="fas fa-circle"></i>
                    <span id="relayStatusText">Unknown</span>
                </span>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-icon power">
                    <i class="fas fa-plug"></i>
                </div>
                <div>
                    <h3 class="card-title">Power Consumption</h3>
                    <small>Current power usage</small>
                </div>
            </div>
            <div class="card-value" id="powerReading">--</div>
            <div class="card-unit">Watts (W)</div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-icon status">
                    <i class="fas fa-wifi"></i>
                </div>
                <div>
                    <h3 class="card-title">ESP32 Status</h3>
                    <small>Device connection</small>
                </div>
            </div>
            <div class="card-value" id="deviceStatus">
                <i class="fas fa-question-circle"></i>
            </div>
            <div class="card-unit" id="deviceStatusText">Checking...</div>
        </div>
    </div>

    <!-- Voice Command Log -->
    <div class="cost-table">
        <h2><i class="fas fa-history"></i> Voice Command History</h2>
        <div id="commandHistory" class="command-history">
            <p><i class="fas fa-info-circle"></i> Voice commands will appear here...</p>
        </div>
    </div>
</div>

<style>
.voice-control-panel {
    text-align: center;
    padding: 2rem;
}

.microphone-container {
    margin-bottom: 2rem;
}

.microphone-btn {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    font-size: 3rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    position: relative;
    overflow: hidden;
}

.microphone-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
}

.microphone-btn.listening {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    animation: pulse 1.5s infinite;
}

.microphone-btn.processing {
    background: linear-gradient(135deg, #f39c12, #e67e22);
    animation: spin 1s linear infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.voice-status {
    margin-top: 1rem;
    font-size: 1.1rem;
    font-weight: 500;
    color: #667eea;
}

.voice-commands {
    background: rgba(102, 126, 234, 0.1);
    border-radius: 15px;
    padding: 1.5rem;
    margin-top: 2rem;
}

.command-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.command-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0.8rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.command-item i {
    font-size: 1.2rem;
}

.command-history {
    max-height: 300px;
    overflow-y: auto;
    background: rgba(0,0,0,0.05);
    border-radius: 10px;
    padding: 1rem;
}

.command-entry {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0.8rem;
    margin-bottom: 0.5rem;
    background: white;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.command-entry.success {
    border-left-color: #27ae60;
}

.command-entry.error {
    border-left-color: #e74c3c;
}

@media (max-width: 768px) {
    .microphone-btn {
        width: 100px;
        height: 100px;
        font-size: 2.5rem;
    }
    
    .command-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let recognition;
let isListening = false;
let speechSynthesis = window.speechSynthesis;

// Initialize speech recognition
function initSpeechRecognition() {
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';
        
        recognition.onstart = function() {
            isListening = true;
            updateVoiceUI('listening');
            speak('Listening for command');
        };
        
        recognition.onresult = function(event) {
            const command = event.results[0][0].transcript.toLowerCase();
            processVoiceCommand(command);
        };
        
        recognition.onerror = function(event) {
            console.error('Speech recognition error:', event.error);
            updateVoiceUI('error');
            addCommandToHistory('Error: ' + event.error, 'error');
            speak('Sorry, I could not understand that');
        };
        
        recognition.onend = function() {
            isListening = false;
            updateVoiceUI('idle');
        };
        
        return true;
    } else {
        console.error('Speech recognition not supported');
        document.getElementById('voiceStatus').textContent = 'Voice control not supported in this browser';
        return false;
    }
}

function toggleVoiceControl() {
    if (!recognition) {
        if (!initSpeechRecognition()) {
            showToast('Voice control not supported', 'error');
            return;
        }
    }
    
    if (isListening) {
        recognition.stop();
    } else {
        recognition.start();
    }
}

function updateVoiceUI(state) {
    const btn = document.getElementById('voiceBtn');
    const icon = document.getElementById('micIcon');
    const status = document.getElementById('voiceStatus');
    
    btn.className = 'microphone-btn';
    
    switch(state) {
        case 'listening':
            btn.classList.add('listening');
            icon.className = 'fas fa-microphone';
            status.textContent = 'Listening... Speak your command';
            break;
        case 'processing':
            btn.classList.add('processing');
            icon.className = 'fas fa-cog';
            status.textContent = 'Processing command...';
            break;
        case 'error':
            icon.className = 'fas fa-exclamation-triangle';
            status.textContent = 'Error occurred. Click to try again';
            break;
        default:
            icon.className = 'fas fa-microphone';
            status.textContent = 'Click to start voice control';
    }
}

function processVoiceCommand(command) {
    updateVoiceUI('processing');
    addCommandToHistory('You said: "' + command + '"', 'info');
    
    // Command processing
    if (command.includes('turn on') || command.includes('switch on') || command.includes('start')) {
        executeCommand('ON', 'Turning device on');
    } else if (command.includes('turn off') || command.includes('switch off') || command.includes('stop')) {
        executeCommand('OFF', 'Turning device off');
    } else if (command.includes('status') || command.includes('show status')) {
        showStatus();
    } else if (command.includes('energy') || command.includes('power') || command.includes('consumption')) {
        showEnergyReport();
    } else if (command.includes('hello') || command.includes('hi')) {
        speak('Hello! I am your smart meter assistant. You can ask me to turn the device on or off, or check the status.');
        addCommandToHistory('Greeting acknowledged', 'success');
    } else {
        speak('Sorry, I did not understand that command. Please try again.');
        addCommandToHistory('Command not recognized', 'error');
    }
    
    updateVoiceUI('idle');
}

function executeCommand(action, message) {
    speak(message);
    
    fetch(`command.php?relay=${action}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                speak(`Device ${action.toLowerCase()} successfully`);
                addCommandToHistory(`Command executed: ${action}`, 'success');
                updateStatus(); // Refresh status display
            } else {
                speak('Command failed. Please try again.');
                addCommandToHistory(`Command failed: ${data.message}`, 'error');
            }
        })
        .catch(error => {
            speak('Network error. Please check connection.');
            addCommandToHistory('Network error: ' + error.message, 'error');
        });
}

function showStatus() {
    fetch('get_status.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const current = data.data.current;
                const status = data.data.relay_status;
                const power = data.data.power_watts;
                
                speak(`Current reading is ${current.toFixed(1)} amperes. Device is ${status}. Power consumption is ${Math.round(power)} watts.`);
                addCommandToHistory(`Status reported: ${current}A, ${status}, ${power}W`, 'success');
            } else {
                speak('Unable to get status information');
                addCommandToHistory('Status request failed', 'error');
            }
        })
        .catch(error => {
            speak('Error getting status');
            addCommandToHistory('Status error: ' + error.message, 'error');
        });
}

function showEnergyReport() {
    fetch('get_status.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const dailyEnergy = data.data.daily_energy_kwh;
                
                speak(`Today's energy consumption is ${dailyEnergy.toFixed(3)} kilowatt hours.`);
                addCommandToHistory(`Energy report: ${dailyEnergy}kWh`, 'success');
            } else {
                speak('Unable to get energy information');
                addCommandToHistory('Energy report failed', 'error');
            }
        })
        .catch(error => {
            speak('Error getting energy data');
            addCommandToHistory('Energy error: ' + error.message, 'error');
        });
}

function speak(text) {
    if (speechSynthesis) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 0.8;
        utterance.pitch = 1;
        utterance.volume = 0.8;
        speechSynthesis.speak(utterance);
    }
}

function addCommandToHistory(message, type = 'info') {
    const historyDiv = document.getElementById('commandHistory');
    const entry = document.createElement('div');
    entry.className = `command-entry ${type}`;
    
    const timestamp = new Date().toLocaleTimeString();
    entry.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <div>
            <strong>${timestamp}:</strong> ${message}
        </div>
    `;
    
    // Remove "no commands" message if it exists
    if (historyDiv.children.length === 1 && historyDiv.children[0].tagName === 'P') {
        historyDiv.innerHTML = '';
    }
    
    historyDiv.insertBefore(entry, historyDiv.firstChild);
    
    // Keep only last 10 entries
    while (historyDiv.children.length > 10) {
        historyDiv.removeChild(historyDiv.lastChild);
    }
}

function updateStatus() {
    fetch('get_status.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const info = data.data;
                
                // Update display
                document.getElementById('currentReading').textContent = info.current.toFixed(2);
                document.getElementById('powerReading').textContent = Math.round(info.power_watts);
                
                // Update relay status
                const statusIndicator = document.getElementById('relayStatusIndicator');
                const statusText = document.getElementById('relayStatusText');
                
                if (info.relay_status === 'ON') {
                    statusIndicator.className = 'status-indicator status-on';
                    statusText.textContent = 'ON';
                    document.getElementById('deviceStatus').innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                    document.getElementById('deviceStatusText').textContent = 'Online';
                } else {
                    statusIndicator.className = 'status-indicator status-off';
                    statusText.textContent = 'OFF';
                    document.getElementById('deviceStatus').innerHTML = '<i class="fas fa-power-off text-danger"></i>';
                    document.getElementById('deviceStatusText').textContent = 'Standby';
                }
            }
        })
        .catch(error => {
            console.error('Status update error:', error);
            document.getElementById('deviceStatus').innerHTML = '<i class="fas fa-exclamation-triangle text-warning"></i>';
            document.getElementById('deviceStatusText').textContent = 'Error';
        });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check for speech recognition support
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        document.getElementById('voiceStatus').innerHTML = 
            '<i class="fas fa-exclamation-triangle"></i> Voice control requires Chrome, Edge, or Safari browser';
        document.getElementById('voiceBtn').disabled = true;
    }
    
    // Initial status update
    updateStatus();
    
    // Auto-refresh status every 5 seconds
    setInterval(updateStatus, 5000);
    
    // Welcome message
    setTimeout(() => {
        if (speechSynthesis) {
            speak('Voice control system ready. Click the microphone to start.');
        }
    }, 1000);
});

// Keyboard shortcuts
document.addEventListener('keydown', function(event) {
    if (event.ctrlKey && event.key === ' ') {
        event.preventDefault();
        toggleVoiceControl();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
