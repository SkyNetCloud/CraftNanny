<?php

require_once('connection.php');

// Sanitize input values from the form
$username = htmlspecialchars($_POST['user']);
$password = htmlspecialchars($_POST['pass']);
$name = htmlspecialchars($_POST['name']);
$id = htmlspecialchars($_POST['id']);
$module_type = htmlspecialchars($_POST['module_type']);

// Call the sign-in function with sanitized inputs
signIn($username, $password, $name, $dbConn, $id, $module_type);

/**
 * Function to handle user sign-in, check credentials, and perform actions based on module type.
 */
function signIn($username, $password, $name, $dbConn, $id, $module_type) {
    // Construct a query to check user credentials
    $query = "SELECT user_id FROM users WHERE username = ? AND password = ?";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

        // If user exists, generate a token and create relevant entries based on module type
        $user_id = $row['user_id'];
        $token = createToken($dbConn, $user_id, $name, $id, $module_type);

        switch ($module_type) {
            case '4':
                createRedstoneEntry($dbConn, $token, $id);
                break;
            case '3':
                createTankEntry($dbConn, $token);
                break;
            case '2':
                createEnergyEntry($dbConn, $token, $id);
                break;
        }

        echo $token; // Output the token
    } else {
        echo 'error: User not found';
    }
}

/**
 * Function to create a token entry in the database.
 */
function createToken($dbConn, $user_id, $name, $id, $module_type) {
    $token = generateRandomToken();

    $query = "INSERT INTO tokens (token, user_id, computer_name, computer_id, module_type) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "sssss", $token, $user_id, $name, $id, $module_type);
    mysqli_stmt_execute($stmt);

    return $token;
}

/**
 * Generates a random token.
 */
function generateRandomToken() {
    return rand().rand().rand().rand();
}

/**
 * Function to create an entry in the redstone_controls table.
 */
function createRedstoneEntry($dbConn, $token, $id) {
    $query = "INSERT INTO redstone_controls (token, computer_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $token, $id);
    mysqli_stmt_execute($stmt);
}

/**
 * Function to create an entry in the tanks table.
 */
function createTankEntry($dbConn, $token) {
    $query = "INSERT INTO tanks (token) VALUES (?)";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
}

/**
 * Function to create an entry in the energy_storage table.
 */
function createEnergyEntry($dbConn, $token, $id) {
    $query = "INSERT INTO energy_storage (token, computer_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $token, $id);
    mysqli_stmt_execute($stmt);
}

?>