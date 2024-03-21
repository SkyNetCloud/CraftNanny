<?php

$version = 1;

require_once('connection.php');

$token = $_POST['token'] ?? '';
$id = $_POST['id'] ?? '';

logPing($token, $id, $version);

function logPing($token, $id, $version) {
    global $dbConn; // Access the database connection inside the function

    // Update last_seen timestamp
    $query = "UPDATE tokens SET last_seen = NOW() WHERE token = ? AND computer_id = ?";
    $stmt = $dbConn->prepare($query);
    $stmt->bind_param("si", $token, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo $version;
    } else {
        echo $version;
    }
}

?>
