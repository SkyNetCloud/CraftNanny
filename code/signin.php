<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

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

    if (!$stmt) {
        echo 'error: Failed to prepare the query - ' . mysqli_error($dbConn);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "s", $username);
    $execute_result = mysqli_stmt_execute($stmt);
    
    if (!$execute_result) {
        echo 'error: Query execution failed - ' . mysqli_error($dbConn);
        exit;
    }

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
                if ($module_type == '2') {
                    createTankEntry($dbConn, $token, $id);
                } elseif ($module_type == '1') {
                    createEnergyEntry($dbConn, $token, $id);
                } elseif ($module_type == '3') {

                }

                echo $token; // Output the token if successful
            } else {
                echo 'error: Invalid password'; // Incorrect password entered by the user
            }
        } else {
            echo 'error: User not found'; // User doesn't exist in the database
        }
    } else {
        echo 'error: Query failed - ' . mysqli_error($dbConn); // Query failed to execute
    }
}






function createToken($dbConn, $user_id, $name, $id, $module_type) {
    $token = rand().rand().rand().rand(); // Generate a random token
    $query = "INSERT INTO tokens (token, user_id, computer_name, computer_id, module_type) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($dbConn, $query);

    if (!$stmt) {
        echo 'error: Failed to prepare token query - ' . mysqli_error($dbConn);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "sssss", $token, $user_id, $name, $id, $module_type);
    $execute_result = mysqli_stmt_execute($stmt);

    if (!$execute_result) {
        echo 'error: Token query execution failed - ' . mysqli_error($dbConn);
        exit;
    }

    return $token;
}

function createRedstoneEntry($dbConn, $token, $id) {
    // Escape the token and id to prevent SQL injection
    $token = dbEsc($dbConn, $token);
    $id = dbEsc($dbConn, $id);

    // Set default values for the other columns
    $defaultValue = NULL; // or you can choose specific default values

    // Prepare the SQL query with placeholders
    $query = "INSERT INTO redstone_controls (token, computer_id, top, bottom, back, front, left_side, right_side, 
                                             top_name, bottom_name, front_name, back_name, left_name, right_name, 
                                             top_input, bottom_input, front_input, back_input, left_input, right_input)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = mysqli_prepare($dbConn, $query);
    
    // Bind the parameters (using NULL for all the other fields)
    mysqli_stmt_bind_param($stmt, 'ssssssssssssssssssss', 
                           $token, $id, 
                           $defaultValue, $defaultValue, $defaultValue, $defaultValue, 
                           $defaultValue, $defaultValue, 
                           $defaultValue, $defaultValue, $defaultValue, $defaultValue, 
                           $defaultValue, $defaultValue, 
                           $defaultValue, $defaultValue, $defaultValue, $defaultValue, 
                           $defaultValue, $defaultValue);

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        // Success
        return true;
    } else {
        // Failure
        return false;
    }
}

function createTankEntry($dbConn, $token, $id) {
    // Sanitize inputs
    $token = dbEsc($dbConn, $token);
    // $id = dbEsc($dbConn, $id);

    // Prepare the SQL query to insert tank entry with placeholders for tank_name, fluid_type, and percent
    $query = "INSERT INTO tanks (token, tank_name, fluid_type, percent) 
              VALUES (?, NULL, NULL, 0)";
    $stmt = mysqli_prepare($dbConn, $query);

    if (!$stmt) {
        echo 'error: Failed to prepare tank query - ' . mysqli_error($dbConn);
        exit;
    }

    // Bind only the parameters for token
    mysqli_stmt_bind_param($stmt, "s", $token);

    // Execute the query
    if (!mysqli_stmt_execute($stmt)) {
        // Handle error if insertion fails
        error_log('Error inserting fluid entry: ' . mysqli_error($dbConn));
    }
}

function createEnergyEntry($dbConn, $token, $id) {
    // Sanitize inputs
    $token = dbEsc($dbConn, $token);
    $id = dbEsc($dbConn, $id);

    // Prepare the SQL query to insert energy storage entry with placeholders for bat_name, energy_type, and percent
    $query = "INSERT INTO energy_storage (token, computer_id, bat_name, energy_type, percent) 
              VALUES (?, ?, NULL, NULL, 0)";
    $stmt = mysqli_prepare($dbConn, $query);

    if (!$stmt) {
        echo 'error: Failed to prepare energy query - ' . mysqli_error($dbConn);
        exit;
    }

    // Bind parameters and execute the query
    mysqli_stmt_bind_param($stmt, "ss", $token, $id);
    if (!mysqli_stmt_execute($stmt)) {
        // Handle error if insertion fails
        error_log('Error inserting energy entry: ' . mysqli_error($dbConn));
    }
}


function dbEsc($dbConn,$theString) {
    $escapedString = mysqli_real_escape_string($dbConn, $theString);
    return $escapedString;
}

?>
