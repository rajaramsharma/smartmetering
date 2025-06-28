<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Ensure values are integers
$limit = (int)$limit;
$offset = (int)$offset;

try {
    // Get total count
    $countStmt = $pdo->query("SELECT COUNT(*) FROM readings");
    $totalRecords = $countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);

    // Get readings with pagination - Fix: Use bindValue with PDO::PARAM_INT
    $stmt = $pdo->prepare("SELECT * FROM readings ORDER BY reading_time DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $readings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Access global pricing tiers
    global $pricing_tiers;
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
    $readings = [];
    $totalRecords = 0;
    $totalPages = 0;
}
?>

<div class="container">
    <div class="cost-table">
        <h2><i class="fas fa-list"></i> Recent Sensor Readings</h2>
        <p>Real-time data from ESP32 smart meter device</p>
        
        <?php if (empty($readings)): ?>
        <div style="text-align: center; padding: 2rem;">
            <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #f39c12; margin-bottom: 1rem;"></i>
            <h3>No Readings Found</h3>
            <p>No sensor readings available. Make sure your ESP32 is connected and sending data.</p>
            <a href="api_test.php" class="nav-link" style="display: inline-block; margin-top: 1rem;">
                <i class="fas fa-tools"></i> Test API Connection
            </a>
        </div>
        <?php else: ?>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><i class="fas fa-bolt"></i> Current (A)</th>
                        <th><i class="fas fa-plug"></i> Power (W)</th>
                        <th><i class="fas fa-toggle-on"></i> Relay Status</th>
                        <th><i class="fas fa-clock"></i> Timestamp</th>
                        <th><i class="fas fa-money-bill"></i> Instant Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($readings as $reading): 
                        $power = VOLTAGE * $reading['measured_current'];
                        $instantEnergy = ($power * 5) / (1000 * 3600); // 5 seconds in kWh
                        $instantCost = calculateTieredCost($instantEnergy, $pricing_tiers);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($reading['id']); ?></td>
                        <td><strong><?php echo number_format($reading['measured_current'], 2); ?></strong></td>
                        <td><?php echo number_format($power, 0); ?> W</td>
                        <td>
                            <span class="status-indicator <?php echo $reading['relay_status'] == 'ON' ? 'status-on' : 'status-off'; ?>">
                                <i class="fas fa-circle"></i>
                                <?php echo htmlspecialchars($reading['relay_status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y H:i:s', strtotime($reading['reading_time'])); ?></td>
                        <td>₨<?php echo number_format($instantCost, 4); ?></td>
                    </tr>
                    <?php endforeach; ?>
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
                Page <?php echo $page; ?> of <?php echo $totalPages; ?> (<?php echo number_format($totalRecords); ?> total records)
            </span>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="nav-link" style="display: inline-block; margin: 0 5px;">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div style="text-align: center; margin-top: 2rem; padding: 1rem; background: rgba(102, 126, 234, 0.1); border-radius: 10px;">
            <h4><i class="fas fa-tools"></i> Quick Actions</h4>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 1rem;">
                <a href="home.php" class="nav-link" style="display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fas fa-home"></i> Control Panel
                </a>
                <a href="api_test.php" class="nav-link" style="display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fas fa-code"></i> API Test
                </a>
                <a href="dashboard.php" class="nav-link" style="display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <button onclick="location.reload()" class="nav-link" style="background: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh every 30 seconds
setInterval(function() {
    if (document.visibilityState === 'visible') {
        location.reload();
    }
}, 30000);

// Add loading indicator when navigating
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href*="page="]');
    links.forEach(link => {
        link.addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
