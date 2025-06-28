<?php
$db = new mysqli('localhost', 'root', '', 'smartmetering2');
if ($db->connect_error) exit('DB Error');

$res1 = $db->query("SELECT measured_current, relay_status FROM readings ORDER BY id DESC LIMIT 1");
$res2 = $db->query("SELECT relay_command FROM commands ORDER BY id DESC LIMIT 1");

$row1 = $res1->fetch_assoc();
$row2 = $res2->fetch_assoc();

echo json_encode([
    "current" => $row1['measured_current'],
    "status" => $row1['relay_status'],
    "command" => $row2['relay_command']
]);

$db->close();
?>
