<?php
$db = new mysqli('localhost', 'root', '', 'smartmetering2');
if ($db->connect_error) exit('DB Error');

if (isset($_GET['current']) && isset($_GET['relay'])) {
    $current = floatval($_GET['current']);
    $relay = $_GET['relay'] == 'ON' ? 'ON' : 'OFF';

    $stmt = $db->prepare("INSERT INTO readings (measured_current, relay_status) VALUES (?, ?)");
    $stmt->bind_param("ds", $current, $relay);
    $stmt->execute();
    echo "OK";
} else echo "Missing data";

$db->close();
?>
