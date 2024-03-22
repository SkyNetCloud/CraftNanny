<?php
// // Enable error reporting to display all errors
// error_reporting(E_ALL);

// // Optionally, you can also display notices and warnings along with other error types
//  error_reporting(E_ALL | E_NOTICE | E_WARNING);

// // If you want to display errors on the web page, you can set display_errors to On in php.ini
// ini_set('display_errors', 1);

// // If you want to log errors to a file, you can set log_errors to On in php.ini
// ini_set('log_errors', 1);



// Check if $_POST['user'] is set and not empty

    // Sanitize the username to prevent XSS attacks
	$username = isset($_POST['user']) ? $_POST['user'] : '';
    $username = htmlspecialchars($username); 

    // Include the database connection file
    require_once('connection.php');
    $salt = '';
	$query = "SELECT salt FROM users WHERE username = ?";
	$stmt = mysqli_prepare($dbConn, $query);
	
	// Bind the parameter to the prepared statement
	mysqli_stmt_bind_param($stmt, "s", htmlspecialchars($_POST['user']));
	
	// Execute the prepared statement
	mysqli_stmt_execute($stmt);

// Get the result of the prepared statement
$result = mysqli_stmt_get_result($stmt);

// Check if the query was successful
if ($result) {
    // Check if any rows were returned
    if (mysqli_num_rows($result) > 0) {
        // Fetch the first row
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        $salt = $row['salt'];
        echo $salt;
    } else {
        // No rows found
        echo 'No rows found';
    }
} else {
    // Handle the error
    echo 'Error: ' . mysqli_error($dbConn);
}

function dbError(&$xmlDoc, &$xmlNode, $theMessage) {
    $errorNode = $xmlDoc->createElement('mysqlError', $theMessage);
    $xmlNode->appendChild($errorNode);
}
?>
