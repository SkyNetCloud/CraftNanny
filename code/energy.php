<?php

$version = 1;

require_once('connection.php');

$token = $_POST['token'] ?? '';
$id = $_POST['id'] ?? '';
$bat_name = $_POST['bat_name'] ?? '';
$energy_type = $_POST['energy_type'] ?? '';
$percent = $_POST['percent'] ?? '';

// Sanitize input
$energy_type = htmlspecialchars($energy_type);
$bat_name = htmlspecialchars($bat_name);
$percent = htmlspecialchars($percent);

// Update last_seen timestamp
$query = "UPDATE tokens SET last_seen = NOW() WHERE token = ? AND computer_id = ?";
$stmt = $dbConn->prepare($query);
$stmt->bind_param("si", $token, $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Update energy storage information
    $query2 = "UPDATE energy_storage SET bat_name = ?, energy_type = ?, percent = ? WHERE token = ?";
    $stmt2 = $dbConn->prepare($query2);
    $stmt2->bind_param("ssds", $bat_name, $energy_type, $percent, $token);
    $stmt2->execute();

    if ($stmt2->affected_rows > 0) {
        echo $version;
    } else {
        echo 'error: energy storage update query failed.';
    }
} else {
    echo 'error: token update query failed.';
}

?>
