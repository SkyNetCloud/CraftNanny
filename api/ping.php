<?php

$version = 1;

require_once('connection.php');

// Use $_GET for URL parameters
$token = $_GET['token']; 
$id = $_GET['id']; 

logPing($token, $id, $version, $dbConn);

function logPing($token, $id, $version, $dbConn) {
    // Check if GET data is set
    if (!isset($token) || !isset($id)) {
        echo "Error: Token or ID not provided.";
        return;
    }

    // Prepare and execute SQL query using prepared statements
    $query = "UPDATE tokens SET last_seen = NOW() WHERE token = ? AND computer_id = ?";
    $stmt = mysqli_prepare($dbConn, $query);

    if (!$stmt) {
        echo "Error: Failed to prepare statement: " . mysqli_error($dbConn);
        return;
    }

    mysqli_stmt_bind_param($stmt, "ss", $token, $id);

    // Execute the prepared statement
    $success = mysqli_stmt_execute($stmt);

    if ($success) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        if ($affected_rows > 0) {
            echo $version;
        } else {
            echo "Error: No rows updated.";
        }
    } else {
        echo "Error: Failed to execute statement: " . mysqli_error($dbConn);
    }
}
?>