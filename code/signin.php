<?php

// ini_set('display_errors', 1);
// error_reporting(E_ALL);

require_once('connection.php');

// Get and sanitize the input data
$username = htmlspecialchars($_GET['user']);  // Change to $_GET instead of $_POST
$password = htmlspecialchars($_GET['pass']);  // Change to $_GET instead of $_POST
$name = htmlspecialchars($_GET['name']);
$id = htmlspecialchars($_GET['id']);
$module_type = htmlspecialchars($_GET['module_type']);

// Call the sign-in function
signIn($username, $password, $name, $dbConn, $id, $module_type);

function signIn($username, $password, $name, $dbConn, $id, $module_type) {
    // Prepare the query to fetch the user by username
    $query = "SELECT user_id, password, salt FROM users WHERE username = ?";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

        if ($row) {
            // Retrieve the stored salt and hashed password
            $storedSalt = $row['salt'];
            $storedHashedPassword = $row['password'];

            // Hash the entered password with the stored salt
            $hashedEnteredPassword = sha1($storedSalt . $password);

            // Compare the hashed entered password with the stored hashed password
            if ($hashedEnteredPassword === $storedHashedPassword) {
                $user_id = $row['user_id'];

                // Create token
                $token = createToken($dbConn, $user_id, $name, $id, $module_type);

                // Handle module_type logic
                if ($module_type == '3') {
                    createTankEntry($dbConn, $token, $id);
                } elseif ($module_type == '2') {
                    createEnergyEntry($dbConn, $token, $id);
                }

                echo $token; // Output the token if successful
            } else {
                echo 'error: Invalid password'; // Incorrect password entered by the user
            }
        } else {
            echo 'error: User not found'; // User doesn't exist in the database
        }
    } else {
        echo 'error: Query failed'; // Query failed to execute
    }
}

function createToken($dbConn, $user_id, $name, $id, $module_type) {
    $token = rand().rand().rand().rand(); // Generate a random token
    $query = "INSERT INTO tokens (token, user_id, computer_name, computer_id, module_type) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "sssss", $token, $user_id, $name, $id, $module_type);
    mysqli_stmt_execute($stmt);
    return $token;
}

function createTankEntry($dbConn, $token, $id) {
    $query = "INSERT INTO tanks (token) VALUES (?)";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
}

function createEnergyEntry($dbConn, $token, $id) {
    $query = "INSERT INTO energy_storage (token, computer_id) VALUES ($token, $id)";
    $stmt = mysqli_prepare($dbConn, $query);
   // mysqli_stmt_bind_param($stmt, "ss", $token, $id);
    mysqli_stmt_execute($stmt);
}

?>
