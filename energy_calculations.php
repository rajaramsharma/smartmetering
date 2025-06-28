<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Auto-calculate energy data for recent readings
try {
    // Process any unprocessed readings from the last 24 hours
    $stmt = $pdo->query("
        SELECT r.* FROM readings r 
        LEFT JOIN energy_calculations ec ON r.id = ec.reading_id 
        WHERE ec.reading_id IS NULL 
        AND r.reading_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY r.reading_time ASC
        LIMIT 100
    ");
    
    $unprocessedReadings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process unprocessed readings
    foreach ($unprocessedReadings as $reading) {
        $power = VOLTAGE * $reading['measured_current'];
        $energy = ($power * 5) / (1000 * 3600); // 5 seconds to kWh
        $cost = calculateTieredCost($energy, $pricing_tiers);
        
        // Fix tier logic - use proper tier names based on actual energy values
        $tierApplied = 'Basic Tier (0-20 kWh)';
        if ($energy > 0.02) $tierApplied = 'Standard Tier (21-40 kWh)'; // 0.02 kWh = 20 Wh
        if ($energy > 0.04) $tierApplied = 'High Usage Tier (41+ kWh)'; // 0.04 kWh = 40 Wh
        
        $insertStmt = $pdo->prepare("
            INSERT INTO energy_calculations 
            (reading_id, current_amps, voltage, power_watts, energy_kwh, cost_npr, tier_applied, calculation_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $insertStmt->execute([
            $reading['id'],
            $reading['measured_current'],
            VOLTAGE,
            $power,
            $energy,
            $cost,
            $tierApplied,
            date('Y-m-d', strtotime($reading['reading_time']))
        ]);
    }
} catch (Exception $e) {
    // Log error for debugging but don't break the page
    error_log("Energy calculation error: " . $e->getMessage());
}

// Pagination for energy calculations
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 25;
$offset = ($page - 1) * $limit;

// Ensure values are integers
$limit = (int)$limit;
$offset = (int)$offset;

try {
    // Get total count
    $countStmt = $pdo->query("SELECT COUNT(*) FROM energy_calculations");
    $totalRecords = $countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);

    // Get energy calculations with pagination - FIX: Use bindValue with PDO::PARAM_INT
    $stmt = $pdo->prepare("
        SELECT 
            ec.*,
            r.relay_status,
            r.reading_time
        FROM energy_calculations ec
        JOIN readings r ON ec.reading_id = r.id
        ORDER BY ec.calculation_time DESC 
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $calculations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get summary statistics
    $summaryStmt = $pdo->query("
        SELECT 
            COUNT(*) as total_calculations,
            SUM(energy_kwh) as total_energy,
            SUM(cost_npr) as total_cost,
            AVG(current_amps) as avg_current,
            MAX(power_watts) as max_power,
            MIN(power_watts) as min_power
        FROM energy_calculations 
        WHERE calculation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

    // Get daily summaries - Check if table exists first
    $dailySummaries = [];
    try {
        $dailyStmt = $pdo->query("
            SELECT * FROM daily_energy_summary 
            ORDER BY summary_date DESC 
            LIMIT 10
        ");
        $dailySummaries = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Table might not exist, create a simple summary from energy_calculations
        $dailyStmt = $pdo->query("
            SELECT 
                calculation_date as summary_date,
                SUM(energy_kwh) as total_energy_kwh,
                SUM(cost_npr) as total_cost_npr,
                AVG(current_amps) as avg_current,
                MAX(current_amps) as max_current,
                COUNT(*) * 5 as on_time_seconds,
                100 as efficiency_percent
            FROM energy_calculations 
            WHERE calculation_date >= DATE_SUB(CURDATE(), INTERVAL 10 DAY)
            GROUP BY calculation_date
            ORDER BY calculation_date DESC
            LIMIT 10
        ");
        $dailySummaries = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
    $calculations = [];
    $summary = [];
    $dailySummaries = [];
    $totalRecords = 0;
    $totalPages = 0;
}
?>

<div class="container">
    <!-- Summary Cards -->
    <div class="dashboard">
        <div class="card">
            <div class="card-header">
                <div class="card-icon current">
                    <i class="fas fa-calculator"></i>
                </div>
                <div>
                    <h3 class="card-title">Total Calculations</h3>
                    <small>Last 30 days</small>
                </div>
            </div>
            <div class="card-value"><?php echo number_format($summary['total_calculations'] ?? 0); ?></div>
            <div class="card-unit">Records</div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-icon power">
                    <i class="fas fa-leaf"></i>
                </div>
                <div>
                    <h3 class="card-title">Total Energy</h3>
                    <small>Last 30 days</small>
                </div>
            </div>
            <div class="card-value"><?php echo number_format($summary['total_energy'] ?? 0, 3); ?></div>
            <div class="card-unit">kWh</div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-icon cost">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div>
                    <h3 class="card-title">Total Cost</h3>
                    <small>Last 30 days</small>
                </div>
            </div>
            <div class="card-value">₨<?php echo number_format($summary['total_cost'] ?? 0, 2); ?></div>
            <div class="card-unit">Nepalese Rupees</div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-icon status">
                    <i class="fas fa-bolt"></i>
                </div>
                <div>
                    <h3 class="card-title">Avg Current</h3>
                    <small>Last 30 days</small>
                </div>
            </div>
            <div class="card-value"><?php echo number_format($summary['avg_current'] ?? 0, 2); ?></div>
            <div class="card-unit">Amperes</div>
        </div>
    </div>

    <!-- Daily Summaries -->
    <div class="cost-table">
        <h2><i class="fas fa-chart-bar"></i> Daily Energy Summaries</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th><i class="fas fa-leaf"></i> Energy (kWh)</th>
                        <th><i class="fas fa-money-bill"></i> Cost (NPR)</th>
                        <th><i class="fas fa-bolt"></i> Avg Current</th>
                        <th><i class="fas fa-chart-line"></i> Max Current</th>
                        <th><i class="fas fa-percentage"></i> Efficiency</th>
                        <th><i class="fas fa-clock"></i> ON Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dailySummaries)): ?>
                    <tr>
                        <td colspan="7" class="text-center">
                            <i class="fas fa-info-circle"></i> No daily summaries available. 
                            <a href="?refresh=1" class="nav-link" style="display: inline;">Refresh calculations</a>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($dailySummaries as $daily): ?>
                        <tr>
                            <td><strong><?php echo date('M d, Y', strtotime($daily['summary_date'])); ?></strong></td>
                            <td><?php echo number_format($daily['total_energy_kwh'] ?? 0, 3); ?> kWh</td>
                            <td><strong>₨<?php echo number_format($daily['total_cost_npr'] ?? 0, 2); ?></strong></td>
                            <td><?php echo number_format($daily['avg_current'] ?? 0, 2); ?> A</td>
                            <td><?php echo number_format($daily['max_current'] ?? 0, 2); ?> A</td>
                            <td>
                                <span class="<?php echo ($daily['efficiency_percent'] ?? 0) > 50 ? 'text-success' : 'text-warning'; ?>">
                                    <?php echo number_format($daily['efficiency_percent'] ?? 0, 1); ?>%
                                </span>
                            </td>
                            <td><?php echo gmdate('H:i:s', $daily['on_time_seconds'] ?? 0); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Energy Calculations -->
    <div class="cost-table">
        <h2><i class="fas fa-table"></i> Detailed Energy Calculations</h2>
        <p>Individual calculations for each sensor reading</p>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><i class="fas fa-bolt"></i> Current (A)</th>
                        <th><i class="fas fa-plug"></i> Power (W)</th>
                        <th><i class="fas fa-leaf"></i> Energy (kWh)</th>
                        <th><i class="fas fa-money-bill"></i> Cost (NPR)</th>
                        <th><i class="fas fa-layer-group"></i> Tier</th>
                        <th><i class="fas fa-toggle-on"></i> Status</th>
                        <th><i class="fas fa-clock"></i> Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($calculations)): ?>
                    <tr>
                        <td colspan="8" class="text-center">
                            <div style="padding: 2rem;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #f39c12; margin-bottom: 1rem;"></i>
                                <h3>No Energy Calculations Found</h3>
                                <p>Energy calculations will be generated automatically as new sensor readings are received.</p>
                                <div style="margin-top: 1rem;">
                                    <a href="?refresh=1" class="control-btn on-btn" style="margin: 0.5rem; display: inline-flex; align-items: center; gap: 5px; padding: 0.5rem 1rem; text-decoration: none;">
                                        <i class="fas fa-sync"></i> Refresh Calculations
                                    </a>
                                    <a href="api_test.php" class="control-btn off-btn" style="margin: 0.5rem; display: inline-flex; align-items: center; gap: 5px; padding: 0.5rem 1rem; text-decoration: none;">
                                        <i class="fas fa-tools"></i> Test API
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($calculations as $calc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($calc['id']); ?></td>
                            <td><strong><?php echo number_format($calc['current_amps'], 2); ?></strong></td>
                            <td><?php echo number_format($calc['power_watts'], 0); ?> W</td>
                            <td><?php echo number_format($calc['energy_kwh'], 6); ?></td>
                            <td><strong>₨<?php echo number_format($calc['cost_npr'], 4); ?></strong></td>
                            <td>
                                <span class="status-indicator status-on" style="font-size: 0.8rem;">
                                    <?php echo htmlspecialchars($calc['tier_applied']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-indicator <?php echo $calc['relay_status'] == 'ON' ? 'status-on' : 'status-off'; ?>">
                                    <i class="fas fa-circle"></i>
                                    <?php echo htmlspecialchars($calc['relay_status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, H:i:s', strtotime($calc['reading_time'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination" style="margin-top: 2rem; text-align: center;">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="nav-link" style="display: inline-block; margin: 0 5px;">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <span style="margin: 0 1rem;">
                Page <?php echo $page; ?> of <?php echo $totalPages; ?> (<?php echo number_format($totalRecords); ?> records)
            </span>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="nav-link" style="display: inline-block; margin: 0 5px;">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Manual Calculation Trigger -->
    <div class="cost-table">
        <h2><i class="fas fa-cogs"></i> Manual Operations</h2>
        <div style="text-align: center; padding: 2rem;">
            <p>If energy calculations are not appearing automatically, you can trigger manual operations:</p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 1rem;">
                <a href="auto_calculate.php" target="_blank" class="control-btn on-btn" style="display: inline-flex; align-items: center; gap: 5px; padding: 0.8rem 1.5rem; text-decoration: none;">
                    <i class="fas fa-calculator"></i>
                    <span>Auto Calculate</span>
                </a>
                <a href="?refresh=1" class="control-btn off-btn" style="display: inline-flex; align-items: center; gap: 5px; padding: 0.8rem 1.5rem; text-decoration: none;">
                    <i class="fas fa-sync"></i>
                    <span>Refresh Page</span>
                </a>
                <a href="api_test.php" class="nav-link" style="display: inline-flex; align-items: center; gap: 5px; padding: 0.8rem 1.5rem; text-decoration: none;">
                    <i class="fas fa-tools"></i>
                    <span>API Test</span>
                </a>
                <a href="readings.php" class="nav-link" style="display: inline-flex; align-items: center; gap: 5px; padding: 0.8rem 1.5rem; text-decoration: none;">
                    <i class="fas fa-list"></i>
                    <span>View Readings</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_GET['refresh'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    showToast('Page refreshed - calculations updated', 'info');
});
</script>
<?php endif; ?>

<script>
// Auto-refresh every 30 seconds if page is visible
setInterval(function() {
    if (document.visibilityState === 'visible') {
        // Only refresh if we have some data
        const hasData = <?php echo !empty($calculations) ? 'true' : 'false'; ?>;
        if (hasData) {
            location.reload();
        }
    }
}, 30000);

// Add loading indicators for pagination links
document.addEventListener('DOMContentLoaded', function() {
    const paginationLinks = document.querySelectorAll('a[href*="page="]');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
