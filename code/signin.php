<?php

// Enable error reporting to display all errors
error_reporting(E_ALL);

// Optionally, you can also display notices and warnings along with other error types
 error_reporting(E_ALL | E_NOTICE | E_WARNING);

// If you want to display errors on the web page, you can set display_errors to On in php.ini
ini_set('display_errors', 1);

// If you want to log errors to a file, you can set log_errors to On in php.ini
ini_set('log_errors', 1);

require_once('connection.php');

$version = 1;

$username = $_POST['user'] ?? '';
$password = $_POST['pass'] ?? '';
$name = $_POST['name'] ?? '';
$id = $_POST['id'] ?? '';
$module_type = $_POST['module_type'] ?? '';

// Sanitize input
$name = htmlspecialchars($name);
$username = htmlspecialchars($username);
$module_type = htmlspecialchars($module_type);

signIn($username, $password, $name, $dbConn, $id, $module_type);

function signIn($username, $password, $name, $dbConn, $id, $module_type) {
    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);
    $name = htmlspecialchars($name);
    $id = htmlspecialchars($id);
    $module_type = htmlspecialchars($module_type);

    // Prepare and execute the query to fetch user_id
    $query2 = "SELECT user_id FROM users WHERE username = ? AND password = ?";
    $stmt2 = $dbConn->prepare($query2);
    $stmt2->bind_param("ss", $username, $password);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    // Fetch the row
    $row2 = $result2->fetch_assoc();

    // Check if user exists
    if ($row2) {
        // Create token
        $token = createToken($dbConn, $row2['user_id'], $name, $id, $username, $module_type);

        // Create module entry based on module_type
        if ($module_type == '1') {
            createModuleEntry($dbConn, $token, $id, 'tanks');
        }
        elseif ($module_type == '2') {
            createModuleEntry($dbConn, $token, $id, 'energy_storage');
        }

        echo $token; // Echo token if successful
    } else {
        echo 'error'; // Echo error if user not found
    }
}

function createToken($dbConn, $user_id, $name, $id, $username, $module_type) {
    $token = bin2hex(random_bytes(16));
    $query = "INSERT INTO tokens (token, user_id, computer_name, computer_id, module_type) VALUES (?, ?, ?, ?, ?)";
    $stmt = $dbConn->prepare($query);
    $stmt->bind_param("sissi", $token, $user_id, $name, $id, $module_type); // Bind parameters
    return $token;
}

function createModuleEntry($dbConn, $token, $id, $table) {
    $query = "INSERT INTO $table (token, computer_id) VALUES (?, ?)";
    $stmt = $dbConn->prepare($query);

}

?>
