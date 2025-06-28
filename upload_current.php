<?php
require_once 'includes/config.php';

// Set JSON header for API response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if (isset($_GET['current']) && isset($_GET['relay'])) {
        $current = floatval($_GET['current']);
        $relay = $_GET['relay'] == 'ON' ? 'ON' : 'OFF';
        
        // Validate current value (reasonable range for household current)
        if ($current < 0 || $current > 100) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid current value. Must be between 0-100 A'
            ]);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO readings (measured_current, relay_status) VALUES (?, ?)");
        $stmt->execute([$current, $relay]);
        
        // Calculate instant values for response
        $power = VOLTAGE * $current;
        $instantEnergy = ($power * 5) / (1000 * 3600);
        $instantCost = calculateTieredCost($instantEnergy, $GLOBALS['pricing_tiers']);
        
        // Log successful upload
        error_log("ESP32 data uploaded - Current: {$current}A, Relay: {$relay}");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Data uploaded successfully',
            'data' => [
                'current' => $current,
                'relay_status' => $relay,
                'power_watts' => round($power, 2),
                'instant_cost_npr' => round($instantCost, 4),
                'voltage' => VOLTAGE,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
        
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required parameters',
            'required' => ['current (float)', 'relay (ON/OFF)'],
            'received' => [
                'current' => $_GET['current'] ?? 'missing',
                'relay' => $_GET['relay'] ?? 'missing'
            ]
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
