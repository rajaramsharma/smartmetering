<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Get detailed cost analysis for different periods
$periods = [
    'today' => ['label' => 'Today', 'days' => 0],
    'yesterday' => ['label' => 'Yesterday', 'days' => 1],
    'week' => ['label' => 'This Week', 'days' => 7],
    'month' => ['label' => 'This Month', 'days' => 30]
];

$costAnalysis = [];

foreach ($periods as $key => $period) {
    if ($key == 'today') {
        $dateCondition = "DATE(reading_time) = CURDATE()";
    } elseif ($key == 'yesterday') {
        $dateCondition = "DATE(reading_time) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
    } else {
        $dateCondition = "DATE(reading_time) >= DATE_SUB(CURDATE(), INTERVAL {$period['days']} DAY)";
    }
    
    $stmt = $pdo->query("SELECT 
                            AVG(measured_current) as avg_current,
                            MAX(measured_current) as max_current,
                            MIN(measured_current) as min_current,
                            COUNT(*) as total_readings,
                            SUM(CASE WHEN relay_status = 'ON' THEN 1 ELSE 0 END) as on_readings
                         FROM readings 
                         WHERE $dateCondition");
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $avgCurrent = $data['avg_current'] ?? 0;
    $onReadings = $data['on_readings'] ?? 0;
    
    // Calculate energy consumption (assuming 5-second intervals)
    $energyKwh = ($avgCurrent * VOLTAGE * $onReadings * 5) / (1000 * 3600);
    $cost = calculateTieredCost($energyKwh, $pricing_tiers);
    
    $costAnalysis[$key] = [
        'label' => $period['label'],
        'energy' => $energyKwh,
        'cost' => $cost,
        'avg_current' => $avgCurrent,
        'max_current' => $data['max_current'] ?? 0,
        'min_current' => $data['min_current'] ?? 0,
        'total_readings' => $data['total_readings'] ?? 0,
        'on_readings' => $onReadings,
        'efficiency' => $data['total_readings'] > 0 ? ($onReadings / $data['total_readings']) * 100 : 0
    ];
}
?>

<div class="container">
    <!-- Cost Overview Cards -->
    <div class="dashboard">
        <?php foreach ($costAnalysis as $analysis): ?>
        <div class="card">
            <div class="card-header">
                <div class="card-icon cost">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <h3 class="card-title"><?php echo $analysis['label']; ?></h3>
                    <small><?php echo number_format($analysis['energy'], 3); ?> kWh</small>
                </div>
            </div>
            <div class="card-value">₨<?php echo number_format($analysis['cost'], 2); ?></div>
            <div class="card-unit">Nepalese Rupees</div>
            <div class="mt-2">
                <small class="text-info">
                    <i class="fas fa-percentage"></i>
                    <?php echo number_format($analysis['efficiency'], 1); ?>% efficiency
                </small>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Detailed Cost Analysis -->
    <div class="cost-table">
        <h2><i class="fas fa-calculator"></i> Detailed Cost Analysis</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Energy (kWh)</th>
                        <th>Cost (NPR)</th>
                        <th>Avg Current</th>
                        <th>Peak Current</th>
                        <th>Efficiency</th>
                        <th>Total Readings</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($costAnalysis as $analysis): ?>
                    <tr>
                        <td><strong><?php echo $analysis['label']; ?></strong></td>
                        <td><?php echo number_format($analysis['energy'], 3); ?> kWh</td>
                        <td><strong>₨<?php echo number_format($analysis['cost'], 2); ?></strong></td>
                        <td><?php echo number_format($analysis['avg_current'], 2); ?> A</td>
                        <td><?php echo number_format($analysis['max_current'], 2); ?> A</td>
                        <td>
                            <span class="<?php echo $analysis['efficiency'] > 50 ? 'text-success' : 'text-warning'; ?>">
                                <?php echo number_format($analysis['efficiency'], 1); ?>%
                            </span>
                        </td>
                        <td><?php echo number_format($analysis['total_readings']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cost Projection -->
    <div class="cost-table">
        <h2><i class="fas fa-chart-line"></i> Cost Projections</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Projection Period</th>
                        <th>Estimated Energy</th>
                        <th>Estimated Cost</th>
                        <th>Based On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $todayCost = $costAnalysis['today']['cost'];
                    $todayEnergy = $costAnalysis['today']['energy'];
                    ?>
                    <tr>
                        <td><strong>Weekly Projection</strong></td>
                        <td><?php echo number_format($todayEnergy * 7, 3); ?> kWh</td>
                        <td><strong>₨<?php echo number_format($todayCost * 7, 2); ?></strong></td>
                        <td>Today's usage</td>
                    </tr>
                    <tr>
                        <td><strong>Monthly Projection</strong></td>
                        <td><?php echo number_format($todayEnergy * 30, 3); ?> kWh</td>
                        <td><strong>₨<?php echo number_format($todayCost * 30, 2); ?></strong></td>
                        <td>Today's usage</td>
                    </tr>
                    <tr>
                        <td><strong>Yearly Projection</strong></td>
                        <td><?php echo number_format($todayEnergy * 365, 3); ?> kWh</td>
                        <td><strong>₨<?php echo number_format($todayCost * 365, 2); ?></strong></td>
                        <td>Today's usage</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            <p><i class="fas fa-info-circle"></i> <strong>Note:</strong> Projections are based on current usage patterns and may vary with actual consumption.</p>
            <p><i class="fas fa-leaf"></i> <strong>Tip:</strong> Monitor your daily usage to optimize energy consumption and reduce costs.</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
