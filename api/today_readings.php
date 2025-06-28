<?php
require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $today = date('Y-m-d');
    
    // Get today's readings
    $stmt = $pdo->prepare("
        SELECT 
            measured_current,
            relay_status,
            reading_time,
            (measured_current * 220) as power_watts
        FROM readings 
        WHERE DATE(reading_time) = ?
        ORDER BY reading_time DESC
    ");
    $stmt->execute([$today]);
    $readings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $totalEnergy = 0;
    $totalCost = 0;
    $onTime = 0;
    
    foreach ($readings as $reading) {
        if ($reading['relay_status'] === 'ON') {
            $power = $reading['power_watts'];
            $energy = ($power * 5) / (1000 * 3600); // 5 seconds to kWh
            
            // Calculate cost using tiers
            $cost = calculateTieredCost($energy, $pricing_tiers);
            
            $totalEnergy += $energy;
            $totalCost += $cost;
            $onTime += 5; // 5 seconds per reading
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'date' => $today,
        'readings' => $readings,
        'summary' => [
            'total_readings' => count($readings),
            'total_energy_kwh' => round($totalEnergy, 6),
            'total_cost_npr' => round($totalCost, 2),
            'on_time_seconds' => $onTime,
            'on_time_hours' => round($onTime / 3600, 2)
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
