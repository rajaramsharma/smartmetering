<?php
require_once 'includes/config.php';

// Set JSON header for API response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Get latest reading
    $stmt1 = $pdo->query("SELECT measured_current, relay_status, reading_time FROM readings ORDER BY id DESC LIMIT 1");
    $reading = $stmt1->fetch(PDO::FETCH_ASSOC);
    
    // Get latest command
    $stmt2 = $pdo->query("SELECT relay_command, command_time FROM commands ORDER BY id DESC LIMIT 1");
    $command = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    // Calculate power and cost
    $current = $reading['measured_current'] ?? 0;
    $power = VOLTAGE * $current;
    $instantEnergy = ($power * 5) / (1000 * 3600); // 5 seconds in kWh
    $instantCost = calculateTieredCost($instantEnergy, $GLOBALS['pricing_tiers']);
    
    // Get today's total cost - IMPROVED CALCULATION
    $today = date('Y-m-d');

    // First try to get from energy_calculations table
    $stmt3 = $pdo->prepare("
        SELECT 
            SUM(ec.cost_npr) as total_cost,
            SUM(ec.energy_kwh) as total_energy
        FROM energy_calculations ec
        WHERE DATE(ec.calculation_date) = ?
    ");
    $stmt3->execute([$today]);
    $energyData = $stmt3->fetch(PDO::FETCH_ASSOC);

    $dailyCost = $energyData['total_cost'] ?? 0;
    $dailyEnergyKwh = $energyData['total_energy'] ?? 0;

    // If no energy calculations exist, calculate from readings
    if ($dailyCost == 0) {
        $stmt4 = $pdo->prepare("
            SELECT 
                AVG(measured_current) as avg_current, 
                COUNT(*) as readings_count,
                SUM(CASE WHEN relay_status = 'ON' THEN 1 ELSE 0 END) as on_readings
            FROM readings 
            WHERE DATE(reading_time) = ?
        ");
        $stmt4->execute([$today]);
        $dailyData = $stmt4->fetch(PDO::FETCH_ASSOC);
        
        $dailyAvgCurrent = $dailyData['avg_current'] ?? 0;
        $onReadings = $dailyData['on_readings'] ?? 0;
        
        // Calculate energy consumption (assuming 5-second intervals)
        $dailyEnergyKwh = ($dailyAvgCurrent * VOLTAGE * $onReadings * 5) / (1000 * 3600);
        $dailyCost = calculateTieredCost($dailyEnergyKwh, $GLOBALS['pricing_tiers']);
    }

    // Ensure we have valid numbers
    $dailyCost = max(0, floatval($dailyCost));
    $dailyEnergyKwh = max(0, floatval($dailyEnergyKwh));
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'current' => $current,
            'relay_status' => $reading['relay_status'] ?? 'OFF',
            'last_command' => $command['relay_command'] ?? 'NONE',
            'power_watts' => round($power, 2),
            'instant_cost_npr' => round($instantCost, 4),
            'daily_cost_npr' => round($dailyCost, 2),
            'daily_energy_kwh' => round($dailyEnergyKwh, 3),
            'voltage' => VOLTAGE,
            'reading_time' => $reading['reading_time'] ?? null,
            'command_time' => $command['command_time'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
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
