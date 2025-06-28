<?php
require_once 'includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $date = $_GET['date'] ?? date('Y-m-d');
    $api = $_GET['api'] ?? false;
    
    if ($api) {
        // API mode - return JSON data
        $stmt = $pdo->prepare("
            SELECT 
                id,
                measured_current,
                relay_status,
                reading_time,
                (measured_current * 220) as power_watts
            FROM readings 
            WHERE DATE(reading_time) = ?
            ORDER BY reading_time DESC
            LIMIT 100
        ");
        $stmt->execute([$date]);
        $readings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'date' => $date,
            'count' => count($readings),
            'readings' => $readings
        ]);
    } else {
        // Redirect to main readings page
        header('Location: readings.php');
        exit;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
