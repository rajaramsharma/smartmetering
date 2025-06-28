<?php
require_once 'includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    if (isset($_GET['device_id']) && isset($_GET['status'])) {
        $deviceId = $_GET['device_id'];
        $status = $_GET['status'];
        $uptime = isset($_GET['uptime']) ? (int)$_GET['uptime'] : 0;
        
        // Create device_status table if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS device_status (
                id INT AUTO_INCREMENT PRIMARY KEY,
                device_id VARCHAR(50) NOT NULL,
                status ENUM('ON', 'OFF') NOT NULL,
                uptime_seconds INT DEFAULT 0,
                last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                ip_address VARCHAR(45),
                signal_strength INT,
                firmware_version VARCHAR(20),
                INDEX idx_device_id (device_id),
                INDEX idx_last_seen (last_seen)
            )
        ");
        
        // Insert or update device status
        $stmt = $pdo->prepare("
            INSERT INTO device_status (device_id, status, uptime_seconds, ip_address) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            uptime_seconds = VALUES(uptime_seconds),
            last_seen = CURRENT_TIMESTAMP,
            ip_address = VALUES(ip_address)
        ");
        
        $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt->execute([$deviceId, $status, $uptime, $clientIP]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Device status updated',
            'device_id' => $deviceId,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required parameters: device_id, status'
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
