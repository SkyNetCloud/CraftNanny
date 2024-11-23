<?php

use GuzzleHttp\Promise\Is;

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

require_once('connection.php');

// Retrieve POST data with fallback to an empty string if not set
$token = isset($_POST['token']) ? $_POST['token'] : '';
$id = isset($_POST['id']) ? $_POST['id'] : '';

$coolant = isset($_POST['coolant']) ? floatval($_POST['coolant']) : 0.0;
$coolant_percentage = isset($_POST['coolant_percentage']) ? floatval($_POST['coolant_percentage']) : 0.0;
$coolant_capacity = isset($_POST['coolant_capacity']);
$coolant_needed = isset($_POST['coolant_needed']);

$coolant_heated = isset($_POST['coolant_heated']) ? floatval($_POST['coolant_heated']) : 0.0;
$coolant_heated_percentage = isset($_POST['coolant_heated_percentage']) ? floatval($_POST['coolant_heated_percentage']) : 0.0;
$coolant_heated_capacity = isset($_POST['coolant_heated_capacity']);
$coolant_heated_needed = isset($_POST['coolant_heated_needed']);


$fuel = isset($_POST['fuel']) ? floatval($_POST['fuel']) : 0.0;
$fuel_capacity = isset($_POST['fuel_capacity']) ? floatval($_POST['fuel_capacity']) : 0.0;
$fuel_needed = isset($_POST['fuel_needed']) ? floatval($_POST['fuel_needed']) : 0.0;
$fuel_percentage = isset($_POST['fuel_percentage']) ? floatval($_POST['fuel_percentage']) : 0.0;

$waste = isset($_POST['waste']) ? floatval($_POST['waste']) : 0.0;
$waste_capacity = isset($_POST['waste_capacity']) ? floatval($_POST['waste_capacity']) : 0.0;
$waste_needed = isset($_POST['waste_needed']) ? floatval($_POST['waste_needed']) : 0.0;
$waste_percentage = isset($_POST['waste_percentage']) ? floatval($_POST['waste_percentage']) : 0.0;


$status = isset($_POST['status']) ? htmlspecialchars($_POST['status']) : '';  // Default to empty string if not set


$max_burn_rate = isset($_POST['max_burn_rate']);
$reactor_damage_precentage = isset($_POST['reactor_damage_precentage']);
$heat_rate = isset($_POST['heat_rate']);
$environmental_loss = isset($_POST['environmental_loss']);
$temperature = isset($_POST['temperature']);
$heat_capacity = isset($_POST['heat_capacity']);

$fuel_assmblies = isset($_POST['fuel_assmblies']);
$fuel_surface_area = isset($_POST['fuel_surface_area']);

// Check if connection is valid
if ($dbConn === false) {
    echo 'Error: Database connection failed.';
    exit;
}

// Update the token's last_seen timestamp
$query = "UPDATE tokens SET last_seen = NOW() WHERE token = ? AND computer_id = ?";
$stmt = mysqli_prepare($dbConn, $query);

if ($stmt === false) {
    echo 'Error: Failed to prepare statement for token update.';
    error_log(mysqli_error($dbConn));  // Log the database error
    exit;
}

mysqli_stmt_bind_param($stmt, "si", $token, $id);
if (!mysqli_stmt_execute($stmt)) {
    echo 'Error: Token update query failed. ' . mysqli_error($dbConn);
    exit;
}

// Check if the token update was successful
if (mysqli_stmt_affected_rows($stmt) > 0) {
    // Update reactor data if token update is successful
    $query2 = "
        UPDATE reactor_controls
        SET coolant = ?,
            coolant_percentage = ?,
            coolant_capacity = ?,
            coolant_needed = ?,
            coolant_heated = ?,
            coolant_heated_percentage = ?,
            coolant_heated_capacity = ?,
            coolant_heated_needed = ?,
            fuel = ?,
            fuel_capacity = ?,
            fuel_needed = ?,
            fuel_percentage = ?,
            waste = ?,
            waste_capacity = ?,
            waste_needed = ?,
            waste_percentage = ?, 
            reactor_status = ?,
            max_burn_rate = ?,
            reactor_damage_precentage = ?,
            heat_rate = ?,
            environmental_loss = ?,
            temperature = ?,
            heat_capacity = ?,
            fuel_assmblies = ?,
            fuel_surface_area = ?,
        WHERE token = ?";
    
    $stmt2 = mysqli_prepare($dbConn, $query2);

    if ($stmt2 === false) {
        echo 'Error: Failed to prepare statement for reactor_controls update.';
        error_log(mysqli_error($dbConn));  // Log the database error
        exit;
    }

    mysqli_stmt_bind_param(
        $stmt2,
        'ccccccccffffffwwwwsbdheth',  // Adjust types as needed
        $coolant,
        $coolant_percentage,
        $coolant_capacity,
        $coolant_needed,
        $coolant_heated,
        $coolant_heated_percentage,
        $coolant_heated_capacity,
        $coolant_heated_needed,
        $fuel,
        $fuel_capacity,
        $fuel_needed,
        $fuel_percentage,
        $fuel_assmblies,
        $fuel_surface_area,
        $waste,
        $waste_capacity,
        $waste_needed,
        $waste_percentage,
        $status,
        $max_burn_rate,
        $reactor_damage_precentage,
        $heat_rate,
        $environmental_loss,
        $temperature,
        $heat_max_capacity,
        $token  // Make sure the token is last
    );

    if (!mysqli_stmt_execute($stmt2)) {
        echo 'Error: Reactor controls update failed. ' . mysqli_error($dbConn);
        exit;
    }

    // Check if the reactor data update was successful
    if (mysqli_stmt_affected_rows($stmt2) > 0) {
        echo $token; // Return the token as confirmation of success
    } else {
        echo 'Error: Reactor data update query failed.';
    }

    // Close the second statement
    mysqli_stmt_close($stmt2);
} else {
    echo 'Error: Token update query failed.';
}

// Close the first statement
mysqli_stmt_close($stmt);

// Close the database connection
mysqli_close($dbConn);
?>