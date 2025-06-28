<?php
$db = new mysqli('localhost', 'root', '', 'smartmetering2');
if ($db->connect_error) exit('DB Error');

if (isset($_GET['relay'])) {
    $relay = $_GET['relay'] == 'ON' ? 'ON' : 'OFF';
    $stmt = $db->prepare("INSERT INTO commands (relay_command) VALUES (?)");
    $stmt->bind_param("s", $relay);
    $stmt->execute();
    echo "Command set to $relay";
} else {
    echo "Missing parameter.";
}

$db->close();
?>
