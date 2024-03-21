<?php

$version = 1;

require_once('connection.php');

$token = $_POST['token'] ?? '';
$id = $_POST['id'] ?? '';
$tank_name = $_POST['tank_name'] ?? '';
$fluid_type = $_POST['fluid_type'] ?? '';
$percent = $_POST['percent'] ?? '';

// Sanitize input
$fluid_type = htmlspecialchars($fluid_type);
$tank_name = htmlspecialchars($tank_name);
$percent = htmlspecialchars($percent);

// Update last_seen timestamp
$query = "UPDATE tokens SET last_seen = NOW() WHERE token = ? AND computer_id = ?";
$stmt = $dbConn->prepare($query);
$stmt->bind_param("si", $token, $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Update tank information
    $query2 = "UPDATE tanks SET tank_name = ?, fluid_type = ?, percent = ? WHERE token = ?";
    $stmt2 = $dbConn->prepare($query2);
    $stmt2->bind_param("ssds", $tank_name, $fluid_type, $percent, $token);
    $stmt2->execute();

    if ($stmt2->affected_rows > 0) {
        echo $version;
    } else {
        echo 'error: tank update query failed.';
    }
} else {
    echo 'error: token update query failed.';
}

?>
