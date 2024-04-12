<?php

// Enable error reporting to display all errors
error_reporting(E_ALL);

// Optionally, you can also display notices and warnings along with other error types
 error_reporting(E_ALL | E_NOTICE | E_WARNING);

// If you want to display errors on the web page, you can set display_errors to On in php.ini
ini_set('display_errors', 1);

// If you want to log errors to a file, you can set log_errors to On in php.ini
ini_set('log_errors', 1);

$version = 1;

require_once('connection.php');

$token = $_POST['token'];
$ign = $_POST['ign'];
$event = $_POST['event'];
$description = $_POST['description'];
$id = $_POST['id'];

$user_id = validateToken($token, $id, $dbConn);
if ($user_id) {
    enterRecord($ign, $event, $description, $user_id, $token, $dbConn);
} else {
    echo 'error';
}

function enterRecord($ign, $event, $description, $user_id, $token, $dbConn) {
    // Check if a record already exists for the player and event
    $existingRecordQuery = "SELECT COUNT(*) AS record_count FROM logs WHERE user_id = ? AND ign = ? AND event = ?";
    $stmt = mysqli_prepare($dbConn, $existingRecordQuery);
    mysqli_stmt_bind_param($stmt, 'iss', $user_id, $ign, $event);
    mysqli_stmt_execute($stmt);
    $existingRecordResult = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($existingRecordResult);
    $recordCount = $row['record_count'];

    if ($recordCount > 0) {
        echo "Error: Record already exists for player '$ign' and event '$event'";
        return;
    }

    // Prepare SQL statement with placeholders
    $query = "INSERT INTO logs (user_id, ign, event, description, timestamp, token) VALUES (?, ?, ?, ?, NOW(), ?)";
    
    // Prepare statement
    $stmt = mysqli_prepare($dbConn, $query);
    if (!$stmt) {
        echo 'Error: Failed to prepare statement: ' . mysqli_error($dbConn);
        return;
    }
    
    // Bind parameters to the statement
    mysqli_stmt_bind_param($stmt, 'ssiss', $user_id, $ign, $event, $description, $token);
    
    // Execute the statement
    $success = mysqli_stmt_execute($stmt);
    
    if ($success) {
        echo 'success';
    } else {
        echo 'Error: ' . mysqli_error($dbConn);
    }
}

function validateToken($token, $id, $dbConn) {
    // Prepare SQL statement with placeholders
    $query = "SELECT user_id FROM tokens WHERE token = ? AND computer_id = ?";
    
    // Prepare statement
    $stmt = mysqli_prepare($dbConn, $query);
    if (!$stmt) {
        echo 'Error: Failed to prepare statement: ' . mysqli_error($dbConn);
        return false;
    }
    
    // Bind parameters to the statement
    mysqli_stmt_bind_param($stmt, 'si', $token, $id);
    
    // Execute the statement
    $success = mysqli_stmt_execute($stmt);
    
    if ($success) {
        // Bind result variables
        mysqli_stmt_bind_result($stmt, $user_id);
        
        // Fetch result
        mysqli_stmt_fetch($stmt);
        
        // Close statement
        mysqli_stmt_close($stmt);
        
        return $user_id;
    } else {
        echo 'Error: ' . mysqli_error($dbConn);
        return false;
    }
}

?>