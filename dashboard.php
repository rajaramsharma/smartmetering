<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Get latest reading
$stmt = $pdo->query("SELECT * FROM readings ORDER BY reading_time DESC LIMIT 1");
$latestReading = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate current power and energy
$currentPower = 0;
$currentEnergy = 0;
if ($latestReading) {
    $calculations = calculatePowerAndEnergy($latestReading['measured_current']);
    $currentPower = $calculations['power'];
    $currentEnergy = $calculations['energy'];
}

// Get daily consumption
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT AVG(measured_current) as avg_current, COUNT(*) as readings_count 
                       FROM readings 
                       WHERE DATE(reading_time) = ? AND relay_status = 'ON'");
$stmt->execute([$today]);
$dailyData = $stmt->fetch(PDO::FETCH_ASSOC);

$dailyAvgCurrent = $dailyData['avg_current'] ?? 0;
$dailyReadings = $dailyData['readings_count'] ?? 0;
$dailyEnergyKwh = ($dailyAvgCurrent * VOLTAGE * $dailyReadings * 5) / (1000 * 3600); // 5 seconds interval
$dailyCost = calculateTieredCost($dailyEnergyKwh, $pricing_tiers);

// Get weekly consumption
$weekStart = date('Y-m-d', strtotime('-7 days'));
$stmt = $pdo->prepare("SELECT AVG(measured_current) as avg_current, COUNT(*) as readings_count 
                       FROM readings 
                       WHERE DATE(reading_time) >= ? AND relay_status = 'ON'");
$stmt->execute([$weekStart]);
$weeklyData = $stmt->fetch(PDO::FETCH_ASSOC);

$weeklyAvgCurrent = $weeklyData['avg_current'] ?? 0;
$weeklyReadings = $weeklyData['readings_count'] ?? 0;
$weeklyEnergyKwh = ($weeklyAvgCurrent * VOLTAGE * $weeklyReadings * 5) / (1000 * 3600);
$weeklyCost = calculateTieredCost($weeklyEnergyKwh, $pricing_tiers);

// Get monthly consumption
$monthStart = date('Y-m-d', strtotime('-30 days'));
$stmt = $pdo->prepare("SELECT AVG(measured_current) as avg_current, COUNT(*) as readings_count 
                       FROM readings 
                       WHERE DATE(reading_time) >= ? AND relay_status = 'ON'");
$stmt->execute([$monthStart]);
$monthlyData = $stmt->fetch(PDO::FETCH_ASSOC);

$monthlyAvgCurrent = $monthlyData['avg_current'] ?? 0;
$monthlyReadings = $monthlyData['readings_count'] ?? 0;
$monthlyEnergyKwh = ($monthlyAvgCurrent * VOLTAGE * $monthlyReadings * 5) / (1000 * 3600);
$monthlyCost = calculateTieredCost($monthlyEnergyKwh, $pricing_tiers);
?>

<div class="container">
    <div class="dashboard">
        <!-- Current Status Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon current">
                    <i class="fas fa-bolt"></i>
                </div>
                <div>
                    <h3 class="card-title">Current Reading</h3>
                    <small id="current-time"></small>
                </div>
            </div>
            <div class="card-value"><?php echo number_format($latestReading['measured_current'] ?? 0, 2); ?></div>
            <div class="card-unit">Amperes (A)</div>
            <div class="mt-2">
                <span class="status-indicator <?php echo ($latestReading['relay_status'] ?? 'OFF') == 'ON' ? 'status-on' : 'status-off'; ?>">
                    <i class="fas fa-circle"></i>
                    Relay <?php echo $latestReading['relay_status'] ?? 'OFF'; ?>
                </span>
            </div>
        </div>

        <!-- Power Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon power">
                    <i class="fas fa-plug"></i>
                </div>
                <div>
                    <h3 class="card-title">Current Power</h3>
                    <small>At 220V constant</small>
                </div>
            </div>
            <div class="card-value"><?php echo number_format($currentPower, 0); ?></div>
            <div class="card-unit">Watts (W)</div>
            <div class="mt-2">
                <small class="text-info">
                    <i class="fas fa-info-circle"></i>
                    P = V × I (<?php echo VOLTAGE; ?>V × <?php echo number_format($latestReading['measured_current'] ?? 0, 2); ?>A)
                </small>
            </div>
        </div>

        <!-- Daily Cost Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon cost">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div>
                    <h3 class="card-title">Today's Cost</h3>
                    <small><?php echo date('M d, Y'); ?></small>
                </div>
            </div>
            <div class="card-value">₨<?php echo number_format($dailyCost, 2); ?></div>
            <div class="card-unit">Nepalese Rupees</div>
            <div class="mt-2">
                <small class="text-success">
                    <i class="fas fa-leaf"></i>
                    <?php echo number_format($dailyEnergyKwh, 3); ?> kWh consumed
                </small>
            </div>
        </div>

        <!-- Status Overview Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon status">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <div>
                    <h3 class="card-title">System Status</h3>
                    <small>ESP32 Connected</small>
                </div>
            </div>
            <div class="card-value text-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="card-unit">Online</div>
            <div class="mt-2">
                <small class="text-info">
                    <i class="fas fa-wifi"></i>
                    Last update: <?php echo $latestReading ? date('H:i:s', strtotime($latestReading['reading_time'])) : 'Never'; ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Cost Breakdown Table -->
    <div class="cost-table">
        <h2><i class="fas fa-chart-bar"></i> Cost Analysis & Breakdown</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-calendar"></i> Period</th>
                        <th><i class="fas fa-bolt"></i> Energy (kWh)</th>
                        <th><i class="fas fa-money-bill-wave"></i> Cost (NPR)</th>
                        <th><i class="fas fa-chart-line"></i> Avg. Current (A)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Today</strong><br><small><?php echo date('M d, Y'); ?></small></td>
                        <td><?php echo number_format($dailyEnergyKwh, 3); ?> kWh</td>
                        <td><strong>₨<?php echo number_format($dailyCost, 2); ?></strong></td>
                        <td><?php echo number_format($dailyAvgCurrent, 2); ?> A</td>
                    </tr>
                    <tr>
                        <td><strong>This Week</strong><br><small>Last 7 days</small></td>
                        <td><?php echo number_format($weeklyEnergyKwh, 3); ?> kWh</td>
                        <td><strong>₨<?php echo number_format($weeklyCost, 2); ?></strong></td>
                        <td><?php echo number_format($weeklyAvgCurrent, 2); ?> A</td>
                    </tr>
                    <tr>
                        <td><strong>This Month</strong><br><small>Last 30 days</small></td>
                        <td><?php echo number_format($monthlyEnergyKwh, 3); ?> kWh</td>
                        <td><strong>₨<?php echo number_format($monthlyCost, 2); ?></strong></td>
                        <td><?php echo number_format($monthlyAvgCurrent, 2); ?> A</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            <h3><i class="fas fa-layer-group"></i> Pricing Tiers (Nepal Electricity Authority)</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Tier</th>
                            <th>Units Range</th>
                            <th>Rate per kWh</th>
                            <th>Currency</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pricing_tiers as $tier): ?>
                        <tr>
                            <td>
                                <?php 
                                if ($tier['min'] == 0) echo "Basic Tier";
                                elseif ($tier['min'] == 21) echo "Standard Tier";
                                else echo "High Usage Tier";
                                ?>
                            </td>
                            <td>
                                <?php echo $tier['min']; ?> - <?php echo $tier['max'] == 999999 ? '∞' : $tier['max']; ?> kWh
                            </td>
                            <td><strong>₨<?php echo number_format($tier['price'], 2); ?></strong></td>
                            <td>NPR</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh function
function refreshData() {
    showToast('Refreshing data...', 'info');
    location.reload();
}

// Show connection status
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($latestReading): ?>
    showToast('ESP32 connected and receiving data', 'success');
    <?php else: ?>
    showToast('No data received from ESP32', 'error');
    <?php endif; ?>
});
</script>

<?php require_once 'includes/footer.php'; ?>
