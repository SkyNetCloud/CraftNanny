<?php

// Enable error reporting to display all errors
error_reporting(E_ALL);

// Optionally, you can also display notices and warnings along with other error types
 error_reporting(E_ALL | E_NOTICE | E_WARNING);

// If you want to display errors on the web page, you can set display_errors to On in php.ini
ini_set('display_errors', 1);

// If you want to log errors to a file, you can set log_errors to On in php.ini
ini_set('log_errors', 1);

$version = 4;

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
    // Check if a record already exists for the given event and user
    $existingRecordQuery = "SELECT * FROM logs WHERE user_id = ? AND ign = ? AND event = ?";
    $stmt = mysqli_prepare($dbConn, $existingRecordQuery);
    mysqli_stmt_bind_param($stmt, 'iss', $user_id, $ign, $event);
    mysqli_stmt_execute($stmt);
    $existingRecordResult = mysqli_stmt_get_result($stmt);

    if ($existingRecordResult && mysqli_num_rows($existingRecordResult) > 0) {
        // If a record exists, update the existing row
        $updateQuery = "UPDATE logs SET description = ?, timestamp = NOW() WHERE user_id = ? AND ign = ? AND event = ?";
        $updateStmt = mysqli_prepare($dbConn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, 'siss', $description, $user_id, $ign, $event);
        $success = mysqli_stmt_execute($updateStmt);

        if ($success) {
            echo 'success (updated)';
        } else {
            echo 'Error: ' . mysqli_error($dbConn);
        }
    } else {
        // If no record exists, insert a new record
        $insertQuery = "INSERT INTO logs (user_id, ign, event, description, timestamp) VALUES (?, ?, ?, ?, NOW())";
        $insertStmt = mysqli_prepare($dbConn, $insertQuery);
        mysqli_stmt_bind_param($insertStmt, 'iss', $user_id, $ign, $event, $description);
        $success = mysqli_stmt_execute($insertStmt);

        if ($success) {
            echo 'success (inserted)';
        } else {
            echo 'Error: ' . mysqli_error($dbConn);
        }
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