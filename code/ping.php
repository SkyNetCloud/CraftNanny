<?php

$version = 2;

require_once('connection.php');

$token = $_POST['token']; // No need for htmlspecialchars
$id = $_POST['id']; // No need for htmlspecialchars

logPing($token, $id, $version, $dbConn);

function logPing($token, $id, $version, $dbConn) {
    // Check if POST data is set
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

    //echo "Token: $token\n";
    //echo "ID: $id\n";

    // Execute the prepared statement
    $success = mysqli_stmt_execute($stmt);

    if ($success) {
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        // echo "Affected rows: $affected_rows\n";
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
