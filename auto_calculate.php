<?php
// Auto-calculation endpoint for AJAX calls
require_once 'includes/config.php';

header('Content-Type: application/json');

try {
    // Get unprocessed readings from last hour
    $stmt = $pdo->query("
        SELECT r.* FROM readings r 
        LEFT JOIN energy_calculations ec ON r.id = ec.reading_id 
        WHERE ec.reading_id IS NULL 
        AND r.reading_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ORDER BY r.reading_time ASC
        LIMIT 50
    ");
    
    $unprocessedReadings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $processed = 0;
    
    foreach ($unprocessedReadings as $reading) {
        $power = VOLTAGE * $reading['measured_current'];
        $energy = ($power * 5) / (1000 * 3600);
        $cost = calculateTieredCost($energy, $pricing_tiers);
        
        $tierApplied = 'Basic Tier (0-20 kWh)';
        if ($energy > 0.02) $tierApplied = 'Standard Tier (21-40 kWh)';
        if ($energy > 0.04) $tierApplied = 'High Usage Tier (41+ kWh)';
        
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
        
        $processed++;
    }
    
    echo json_encode([
        'status' => 'success',
        'processed' => $processed,
        'message' => "Processed $processed energy calculations"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
