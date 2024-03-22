<?php

require_once('connection.php');

$username = htmlspecialchars($_POST['user']);
$password = htmlspecialchars($_POST['pass']);
$name = htmlspecialchars($_POST['name']);
$id = htmlspecialchars($_POST['id']);
$module_type = htmlspecialchars($_POST['module_type']);

signIn($username, $password, $name, $dbConn, $id, $module_type);

function signIn($username, $password, $name, $dbConn, $id, $module_type) {
	
	// Construct the query by interpolating $_POST values
	$query = "SELECT user_id FROM users WHERE username = '" . $username . "' AND password = '" . $password . "'";	
	$result = mysqli_query($dbConn, $query);

	// echo "Username: $username\n";
	// echo "Password: $password\n";
	// echo "Name: $name\n";
	// echo "ID: $id\n";
	// echo "Module Type: $module_type\n";

	if ($result) {
		// Fetch the row
		$row2 = mysqli_fetch_array($result, MYSQLI_ASSOC);
	
		// Check if user_id exists and is not empty
		if (!empty($row2['user_id'])) {
			// Create token
			$token = createToken($dbConn, $row2['user_id'], $name, $id, $username, $module_type);
			
			// Check module_type and perform actions accordingly
			if ($module_type == '4') {
				createRedstoneEntry($dbConn, $token, $id);
			} elseif ($module_type == '3') {
				createTankEntry($dbConn, $token, $id);
			} elseif ($module_type == '2') {
				createEnergyEntry($dbConn, $token, $id);
			} else {
				echo 'error: Invalid module type'; // Display error for invalid module type
			}
			
			echo $token;
		} else {
			echo 'error: User not found'; // Display error when user_id is empty
		}
	} else {
		echo 'error: Query failed'; // Display error when query execution fails
	}
}

function createToken($dbConn, $user_id, $name, $id, $username, $module_type) {
    $token = rand().rand().rand().rand(); // Generate a random token
    $query = "INSERT INTO tokens (token, user_id, computer_name, computer_id, module_type) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "sssss", $token, $user_id, $name, $id, $module_type);
    mysqli_stmt_execute($stmt);
    return $token;
}

function createRedstoneEntry($dbConn, $token, $id) {
    $query = "INSERT INTO redstone_controls (token, computer_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $token, $id);
    mysqli_stmt_execute($stmt);
}

function createTankEntry($dbConn, $token, $id) {
    $query = "INSERT INTO tanks (token) VALUES (?)";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
}

function createEnergyEntry($dbConn, $token, $id) {
    $query = "INSERT INTO energy_storage (token, computer_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $token, $id);
    mysqli_stmt_execute($stmt);
}