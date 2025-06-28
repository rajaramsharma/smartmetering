<?php
require_once 'includes/config.php';

// Set JSON header for API response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if (isset($_GET['relay'])) {
        $relay = $_GET['relay'] == 'ON' ? 'ON' : 'OFF';
        
        $stmt = $pdo->prepare("INSERT INTO commands (relay_command) VALUES (?)");
        $stmt->execute([$relay]);
        
        // Log the command for debugging
        error_log("Relay command set to: $relay");
        
        echo json_encode([
            'status' => 'success',
            'message' => "Command set to $relay",
            'command' => $relay,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing relay parameter',
            'required' => 'relay (ON/OFF)'
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
