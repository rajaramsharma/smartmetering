
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Home Control Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 1s ease-out;
        }

        .header h1 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            color: rgba(255,255,255,0.9);
            font-size: 1.1rem;
        }

        .status-bar {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            animation: slideInUp 1s ease-out;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            text-align: center;
        }

        .status-item {
            padding: 15px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            animation: pulse 2s infinite;
        }

        .status-item h3 {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .status-item .value {
            font-size: 1.8rem;
            font-weight: bold;
        }

        .devices-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .device-card {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            animation: fadeInUp 1s ease-out;
            position: relative;
            overflow: hidden;
        }

        .device-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .device-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s;
        }

        .device-card:hover::before {
            left: 100%;
        }

        .device-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .device-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.8rem;
            color: white;
            animation: rotate 3s linear infinite;
        }

        .device-icon.bulb {
            background: linear-gradient(135deg, #ffeaa7, #fdcb6e);
        }

        .device-icon.fan {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
        }

        .device-icon.pump {
            background: linear-gradient(135deg, #00cec9, #00b894);
        }

        .device-icon.ac {
            background: linear-gradient(135deg, #a29bfe, #6c5ce7);
        }

        .device-info h3 {
            font-size: 1.3rem;
            margin-bottom: 5px;
            color: #2d3436;
        }

        .device-info p {
            color: #636e72;
            font-size: 0.9rem;
        }

        .device-status {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 20px 0;
            padding: 15px;
            background: rgba(0,0,0,0.05);
            border-radius: 10px;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            animation: blink 2s infinite;
        }

        .status-dot.on {
            background: #00b894;
            box-shadow: 0 0 10px #00b894;
        }

        .status-dot.off {
            background: #e17055;
            box-shadow: 0 0 10px #e17055;
        }

        .status-dot.disabled {
            background: #b2bec3;
        }

        .control-buttons {
            display: flex;
            gap: 10px;
        }

        .control-btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .control-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .control-btn.on-btn {
            background: linear-gradient(135deg, #00b894, #00cec9);
            color: white;
        }

        .control-btn.on-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #00a085, #00b7a8);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,184,148,0.4);
        }

        .control-btn.off-btn {
            background: linear-gradient(135deg, #e17055, #d63031);
            color: white;
        }

        .control-btn.off-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #d4634a, #c92a2a);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(225,112,85,0.4);
        }

        .control-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.3s, height 0.3s;
        }

        .control-btn:active::before {
            width: 300px;
            height: 300px;
        }

        .power-consumption {
            margin-top: 15px;
            padding: 10px;
            background: rgba(116,185,255,0.1);
            border-radius: 8px;
            text-align: center;
            font-size: 0.9rem;
            color: #0984e3;
        }

        .disabled-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            font-weight: bold;
            color: #636e72;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .device-card.disabled .disabled-overlay {
            opacity: 1;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background: linear-gradient(135deg, #00b894, #00cec9);
        }

        .notification.error {
            background: linear-gradient(135deg, #e17055, #d63031);
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #0984e3;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes blink {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .devices-grid {
                grid-template-columns: 1fr;
            }
            
            .status-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-home"></i> Smart Home Control Panel</h1>
            <p>Monitor and control your connected devices</p>
        </div>

        <div class="status-bar">
            <div class="status-grid">
                <div class="status-item">
                    <h3><i class="fas fa-bolt"></i> Current</h3>
                    <div class="value" id="currentValue">0.00A</div>
                </div>
                <div class="status-item">
                    <h3><i class="fas fa-plug"></i> Power</h3>
                    <div class="value" id="powerValue">0W</div>
                </div>
                <div class="status-item">
                    <h3><i class="fas fa-wifi"></i> Status</h3>
                    <div class="value" id="connectionStatus">Connected</div>
                </div>
                <div class="status-item">
                    <h3><i class="fas fa-clock"></i> Last Update</h3>
                    <div class="value" id="lastUpdate">--:--</div>
                </div>
            </div>
        </div>

        <div class="devices-grid">
            <!-- Smart Bulb (Functional) -->
            <div class="device-card" id="bulbCard">
                <div class="device-header">
                    <div class="device-icon bulb">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="device-info">
                        <h3>Smart Bulb</h3>
                        <p>Living Room Light</p>
                    </div>
                </div>
                <div class="device-status">
                    <div class="status-indicator">
                        <div class="status-dot off" id="bulbStatusDot"></div>
                        <span id="bulbStatusText">OFF</span>
                    </div>
                    <div class="power-consumption" id="bulbPower">0W</div>
                </div>
                <div class="control-buttons">
                    <button class="control-btn on-btn" onclick="sendCommand('ON', 'bulb')">
                        <i class="fas fa-power-off"></i> Turn ON
                    </button>
                    <button class="control-btn off-btn" onclick="sendCommand('OFF', 'bulb')">
                        <i class="fas fa-power-off"></i> Turn OFF
                    </button>
                </div>
            </div>

            <!-- Ceiling Fan (Display Only) -->
            <div class="device-card disabled" id="fanCard">
                <div class="device-header">
                    <div class="device-icon fan">
                        <i class="fas fa-fan"></i>
                    </div>
                    <div class="device-info">
                        <h3>Ceiling Fan</h3>
                        <p>Bedroom Fan</p>
                    </div>
                </div>
                <div class="device-status">
                    <div class="status-indicator">
                        <div class="status-dot disabled"></div>
                        <span>Not Connected</span>
                    </div>
                    <div class="power-consumption">--W</div>
                </div>
                <div class="control-buttons">
                    <button class="control-btn on-btn" disabled>
                        <i class="fas fa-power-off"></i> Turn ON
                    </button>
                    <button class="control-btn off-btn" disabled>
                        <i class="fas fa-power-off"></i> Turn OFF
                    </button>
                </div>
                <div class="disabled-overlay">
                    <i class="fas fa-exclamation-triangle"></i> Device Not Available
                </div>
            </div>

            <!-- Water Pump (Display Only) -->
            <div class="device-card disabled" id="pumpCard">
                <div class="device-header">
                    <div class="device-icon pump">
                        <i class="fas fa-tint"></i>
                    </div>
                    <div class="device-info">
                        <h3>Water Pump</h3>
                        <p>Garden Irrigation</p>
                    </div>
                </div>
                <div class="device-status">
                    <div class="status-indicator">
                        <div class="status-dot disabled"></div>
                        <span>Not Connected</span>
                    </div>
                    <div class="power-consumption">--W</div>
                </div>
                <div class="control-buttons">
                    <button class="control-btn on-btn" disabled>
                        <i class="fas fa-play"></i> Start
                    </button>
                    <button class="control-btn off-btn" disabled>
                        <i class="fas fa-stop"></i> Stop
                    </button>
                </div>
                <div class="disabled-overlay">
                    <i class="fas fa-exclamation-triangle"></i> Device Not Available
                </div>
            </div>

            <!-- Air Conditioner (Display Only) -->
            <div class="device-card disabled" id="acCard">
                <div class="device-header">
                    <div class="device-icon ac">
                        <i class="fas fa-snowflake"></i>
                    </div>
                    <div class="device-info">
                        <h3>Air Conditioner</h3>
                        <p>Master Bedroom AC</p>
                    </div>
                </div>
                <div class="device-status">
                    <div class="status-indicator">
                        <div class="status-dot disabled"></div>
                        <span>Not Connected</span>
                    </div>
                    <div class="power-consumption">--W</div>
                </div>
                <div class="control-buttons">
                    <button class="control-btn on-btn" disabled>
                        <i class="fas fa-power-off"></i> Turn ON
                    </button>
                    <button class="control-btn off-btn" disabled>
                        <i class="fas fa-power-off"></i> Turn OFF
                    </button>
                </div>
                <div class="disabled-overlay">
                    <i class="fas fa-exclamation-triangle"></i> Device Not Available
                </div>
            </div>
        </div>
    </div>

    <div id="notification" class="notification"></div>

    <script>
        let isUpdating = false;

        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        function sendCommand(cmd, device) {
            if (device !== 'bulb') {
                showNotification('This device is not connected!', 'error');
                return;
            }

            const buttons = document.querySelectorAll('#bulbCard .control-btn');
            buttons.forEach(btn => btn.disabled = true);

            // Add loading animation
            const loadingBtn = event.target;
            const originalText = loadingBtn.innerHTML;
            loadingBtn.innerHTML = '<div class="loading"></div> Sending...';

            fetch(`command.php?relay=${cmd}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(data => {
                    showNotification(`Bulb turned ${cmd}!`, 'success');
                    updateBulbStatus(cmd);
                    setTimeout(updateData, 1000); // Update data after command
                })
                .catch(error => {
                    console.error('Command error:', error);
                    showNotification(`Failed to turn ${cmd} bulb: ${error.message}`, 'error');
                })
                .finally(() => {
                    buttons.forEach(btn => btn.disabled = false);
                    loadingBtn.innerHTML = originalText;
                });
        }

        function updateBulbStatus(status) {
            const statusDot = document.getElementById('bulbStatusDot');
            const statusText = document.getElementById('bulbStatusText');
            
            if (status === 'ON') {
                statusDot.className = 'status-dot on';
                statusText.textContent = 'ON';
            } else {
                statusDot.className = 'status-dot off';
                statusText.textContent = 'OFF';
            }
        }

        function updateData() {
            if (isUpdating) return;
            isUpdating = true;

            fetch("get_status.php")
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Update status bar
                    document.getElementById("currentValue").textContent = 
                        (parseFloat(data.current) || 0).toFixed(2) + "A";
                    
                    const power = Math.round(parseFloat(data.current) * 220) || 0;
                    document.getElementById("powerValue").textContent = power + "W";
                    document.getElementById("bulbPower").textContent = power + "W";
                    
                    document.getElementById("connectionStatus").textContent = "Connected";
                    document.getElementById("lastUpdate").textContent = 
                        new Date().toLocaleTimeString();

                    // Update bulb status
                    updateBulbStatus(data.status || 'OFF');
                })
                .catch(error => {
                    console.error('Update error:', error);
                    document.getElementById("connectionStatus").textContent = "Disconnected";
                    document.getElementById("currentValue").textContent = "0.00A";
                    document.getElementById("powerValue").textContent = "0W";
                    document.getElementById("bulbPower").textContent = "0W";
                })
                .finally(() => {
                    isUpdating = false;
                });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateData();
            setInterval(updateData, 3000);
            showNotification('Smart Home Control Panel Loaded!', 'success');
        });

        // Add some interactive effects
        document.querySelectorAll('.device-card:not(.disabled)').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>
