<?php

$version = 1;

require_once('connection.php');

$token = $_POST['token'] ?? '';
$ign = $_POST['ign'] ?? '';
$event = $_POST['event'] ?? '';
$description = $_POST['description'] ?? '';
$id = $_POST['id'] ?? '';

$user_id = validateToken($token, $id);
$event = htmlspecialchars($event);
$ign = htmlspecialchars($ign);
$description = htmlspecialchars($description);

if ($user_id) {
    enterRecord($ign, $event, $description, $user_id, $token);
} else {
    echo 'error';
}

function enterRecord($ign, $event, $description, $user_id, $token) {
    global $dbConn;

    $query = "INSERT INTO logs (user_id, ign, event, description, timestamp, token) VALUES (?, ?, ?, ?, NOW(), ?)";
    $stmt = $dbConn->prepare($query);
    $stmt->bind_param("isiss", $user_id, $ign, $event, $description, $token);
    $result = $stmt->execute();

    if ($result) {
        echo 'success';
    } else {
        echo 'error';
    }
}

function validateToken($token, $id) {
    global $dbConn;

    $query = "SELECT user_id FROM tokens WHERE token = ? AND computer_id = ?";
    $stmt = $dbConn->prepare($query);
    $stmt->bind_param("si", $token, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['user_id'];
    } else {
        return false;
    }
}

?>
