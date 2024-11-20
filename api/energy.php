<?php
require_once('connection.php');

$token = $_POST['token'];
$id = $_POST['id'];
$bat_name = $_POST['name'];
$energy_type = $_POST['energy_type'];
$percent = $_POST['percent'];

// Sanitize input using htmlspecialchars to prevent XSS attacks
$energy_type = htmlspecialchars($energy_type);
$bat_name = htmlspecialchars($bat_name);
$percent = htmlspecialchars($percent);

// Prepare the first update query
$query = "UPDATE tokens SET last_seen = NOW() WHERE token = ? AND computer_id = ?";
$stmt = mysqli_prepare($dbConn, $query);
mysqli_stmt_bind_param($stmt, "si", $token, $id);
mysqli_stmt_execute($stmt);

// Check if the first query was successful
if (mysqli_stmt_affected_rows($stmt) > 0) {
    // Prepare the second update query
    $query2 = "UPDATE energy_storage SET bat_name = ?, energy_type = ?, percent = ? WHERE token = ?";
    $stmt2 = mysqli_prepare($dbConn, $query2);
    mysqli_stmt_bind_param($stmt2, "ssss", $bat_name, $energy_type, $percent, $token);
    mysqli_stmt_execute($stmt2);

    // Check if the second query was successful
    if (mysqli_stmt_affected_rows($stmt2) > 0) {
        echo $token;
    } else {
        echo 'error: energy_storage update query failed.';
    }
} else {
    echo 'error: token update query failed.';
}

// Close prepared statements
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt2);

// Close database connection
mysqli_close($dbConn);

?>
