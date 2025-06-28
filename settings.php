<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Handle settings update
if ($_POST) {
    try {
        // Create settings table if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS system_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT NOT NULL,
                description TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        foreach ($_POST as $key => $value) {
            if ($key !== 'submit') {
                $stmt = $pdo->prepare("
                    INSERT INTO system_settings (setting_key, setting_value) 
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ");
                $stmt->execute([$key, $value]);
            }
        }
        
        $successMessage = "Settings updated successfully!";
    } catch (Exception $e) {
        $errorMessage = "Error updating settings: " . $e->getMessage();
    }
}

// Get current settings
$settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Table might not exist yet
}

// Default values
$defaults = [
    'voltage' => 220,
    'upload_interval' => 5,
    'command_interval' => 3,
    'basic_tier_price' => 8.50,
    'standard_tier_price' => 12.00,
    'high_tier_price' => 15.50,
    'currency' => 'NPR',
    'timezone' => 'Asia/Kathmandu',
    'device_name' => 'Smart Meter ESP32',
    'alert_high_current' => 30,
    'alert_email' => '',
    'voice_enabled' => 1,
    'auto_calculations' => 1
];

foreach ($defaults as $key => $value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $value;
    }
}
?>

<div class="container">
    <?php if (isset($successMessage)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($errorMessage)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
    </div>
    <?php endif; ?>

    <form method="POST" class="settings-form">
        <!-- System Settings -->
        <div class="cost-table">
            <h2><i class="fas fa-cog"></i> System Settings</h2>
            
            <div class="settings-grid">
                <div class="setting-group">
                    <label for="voltage">System Voltage (V)</label>
                    <input type="number" id="voltage" name="voltage" value="<?php echo $settings['voltage']; ?>" min="100" max="300" step="1">
                    <small>Standard voltage for power calculations</small>
                </div>
                
                <div class="setting-group">
                    <label for="upload_interval">Upload Interval (seconds)</label>
                    <input type="number" id="upload_interval" name="upload_interval" value="<?php echo $settings['upload_interval']; ?>" min="1" max="60">
                    <small>How often ESP32 sends data</small>
                </div>
                
                <div class="setting-group">
                    <label for="command_interval">Command Check Interval (seconds)</label>
                    <input type="number" id="command_interval" name="command_interval" value="<?php echo $settings['command_interval']; ?>" min="1" max="30">
                    <small>How often ESP32 checks for commands</small>
                </div>
                
                <div class="setting-group">
                    <label for="device_name">Device Name</label>
                    <input type="text" id="device_name" name="device_name" value="<?php echo htmlspecialchars($settings['device_name']); ?>">
                    <small>Display name for your device</small>
                </div>
            </div>
        </div>

        <!-- Pricing Settings -->
        <div class="cost-table">
            <h2><i class="fas fa-money-bill-wave"></i> Pricing Tiers (Nepal)</h2>
            
            <div class="settings-grid">
                <div class="setting-group">
                    <label for="basic_tier_price">Basic Tier (0-20 kWh) - NPR per kWh</label>
                    <input type="number" id="basic_tier_price" name="basic_tier_price" value="<?php echo $settings['basic_tier_price']; ?>" step="0.01" min="0">
                    <small>Price for first 20 kWh</small>
                </div>
                
                <div class="setting-group">
                    <label for="standard_tier_price">Standard Tier (21-40 kWh) - NPR per kWh</label>
                    <input type="number" id="standard_tier_price" name="standard_tier_price" value="<?php echo $settings['standard_tier_price']; ?>" step="0.01" min="0">
                    <small>Price for 21-40 kWh</small>
                </div>
                
                <div class="setting-group">
                    <label for="high_tier_price">High Usage Tier (41+ kWh) - NPR per kWh</label>
                    <input type="number" id="high_tier_price" name="high_tier_price" value="<?php echo $settings['high_tier_price']; ?>" step="0.01" min="0">
                    <small>Price for above 40 kWh</small>
                </div>
                
                <div class="setting-group">
                    <label for="currency">Currency</label>
                    <select id="currency" name="currency">
                        <option value="NPR" <?php echo $settings['currency'] == 'NPR' ? 'selected' : ''; ?>>Nepalese Rupee (NPR)</option>
                        <option value="USD" <?php echo $settings['currency'] == 'USD' ? 'selected' : ''; ?>>US Dollar (USD)</option>
                        <option value="EUR" <?php echo $settings['currency'] == 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
                    </select>
                    <small>Display currency</small>
                </div>
            </div>
        </div>

        <!-- Alert Settings -->
        <div class="cost-table">
            <h2><i class="fas fa-bell"></i> Alert Settings</h2>
            
            <div class="settings-grid">
                <div class="setting-group">
                    <label for="alert_high_current">High Current Alert (Amperes)</label>
                    <input type="number" id="alert_high_current" name="alert_high_current" value="<?php echo $settings['alert_high_current']; ?>" min="1" max="100">
                    <small>Alert when current exceeds this value</small>
                </div>
                
                <div class="setting-group">
                    <label for="alert_email">Alert Email</label>
                    <input type="email" id="alert_email" name="alert_email" value="<?php echo htmlspecialchars($settings['alert_email']); ?>">
                    <small>Email for receiving alerts</small>
                </div>
                
                <div class="setting-group">
                    <label for="voice_enabled">Voice Control</label>
                    <select id="voice_enabled" name="voice_enabled">
                        <option value="1" <?php echo $settings['voice_enabled'] == '1' ? 'selected' : ''; ?>>Enabled</option>
                        <option value="0" <?php echo $settings['voice_enabled'] == '0' ? 'selected' : ''; ?>>Disabled</option>
                    </select>
                    <small>Enable/disable voice control feature</small>
                </div>
                
                <div class="setting-group">
                    <label for="auto_calculations">Auto Energy Calculations</label>
                    <select id="auto_calculations" name="auto_calculations">
                        <option value="1" <?php echo $settings['auto_calculations'] == '1' ? 'selected' : ''; ?>>Enabled</option>
                        <option value="0" <?php echo $settings['auto_calculations'] == '0' ? 'selected' : ''; ?>>Disabled</option>
                    </select>
                    <small>Automatically calculate energy consumption</small>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="cost-table">
            <h2><i class="fas fa-info-circle"></i> System Information</h2>
            
            <div class="info-grid">
                <div class="info-item">
                    <i class="fas fa-server"></i>
                    <div>
                        <strong>PHP Version</strong>
                        <span><?php echo phpversion(); ?></span>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-database"></i>
                    <div>
                        <strong>Database</strong>
                        <span>MySQL/MariaDB</span>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Server Time</strong>
                        <span><?php echo date('Y-m-d H:i:s'); ?></span>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-microchip"></i>
                    <div>
                        <strong>ESP32 Status</strong>
                        <span id="esp32Status">Checking...</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" name="submit" class="control-btn on-btn">
                <i class="fas fa-save"></i>
                <span>Save Settings</span>
            </button>
            
            <button type="button" onclick="resetToDefaults()" class="control-btn off-btn">
                <i class="fas fa-undo"></i>
                <span>Reset to Defaults</span>
            </button>
        </div>
    </form>
</div>

<style>
.alert {
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: rgba(46, 204, 113, 0.1);
    color: #27ae60;
    border: 1px solid rgba(46, 204, 113, 0.3);
}

.alert-error {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
    border: 1px solid rgba(231, 76, 60, 0.3);
}

.settings-form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    padding: 1rem 0;
}

.setting-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.setting-group label {
    font-weight: 600;
    color: #333;
}

.setting-group input,
.setting-group select {
    padding: 0.8rem;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.setting-group input:focus,
.setting-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.setting-group small {
    color: #666;
    font-size: 0.9rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    padding: 1rem 0;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(102, 126, 234, 0.05);
    border-radius: 10px;
}

.info-item i {
    font-size: 1.5rem;
    color: #667eea;
}

.info-item div {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.info-item strong {
    font-weight: 600;
    color: #333;
}

.info-item span {
    color: #666;
    font-size: 0.9rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    padding: 2rem 0;
}

@media (max-width: 768px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<script>
function resetToDefaults() {
    if (confirm('Are you sure you want to reset all settings to default values?')) {
        // Reset form values to defaults
        document.getElementById('voltage').value = 220;
        document.getElementById('upload_interval').value = 5;
        document.getElementById('command_interval').value = 3;
        document.getElementById('basic_tier_price').value = 8.50;
        document.getElementById('standard_tier_price').value = 12.00;
        document.getElementById('high_tier_price').value = 15.50;
        document.getElementById('currency').value = 'NPR';
        document.getElementById('device_name').value = 'Smart Meter ESP32';
        document.getElementById('alert_high_current').value = 30;
        document.getElementById('alert_email').value = '';
        document.getElementById('voice_enabled').value = 1;
        document.getElementById('auto_calculations').value = 1;
        
        showToast('Settings reset to defaults. Click Save to apply.', 'info');
    }
}

// Check ESP32 status
function checkESP32Status() {
    fetch('get_status.php')
        .then(response => response.json())
        .then(data => {
            const statusEl = document.getElementById('esp32Status');
            if (data.status === 'success') {
                statusEl.innerHTML = '<span style="color: #27ae60;">Online</span>';
            } else {
                statusEl.innerHTML = '<span style="color: #e74c3c;">Offline</span>';
            }
        })
        .catch(error => {
            document.getElementById('esp32Status').innerHTML = '<span style="color: #f39c12;">Unknown</span>';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    checkESP32Status();
    setInterval(checkESP32Status, 10000); // Check every 10 seconds
});
</script>

<?php require_once 'includes/footer.php'; ?>
