<?php
// Enable error reporting (you can uncomment if needed)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Include the database connection file
require_once('connection.php');

// Initialize an array to hold the response data
$response = [];

// Check if $_POST['user'] is set and not empty
$username = isset($_POST['user']) ? $_POST['user'] : '';

// Sanitize the username to prevent XSS attacks
$username = htmlspecialchars($username);

// Prepare the SQL query to fetch the salt for the user
$query = "SELECT salt FROM users WHERE username = ?";
$stmt = mysqli_prepare($dbConn, $query);

// Bind the parameter to the prepared statement
mysqli_stmt_bind_param($stmt, "s", $username);

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
        
        // Add the result to the response array
        $response['status'] = 'success';
        $response['data'] = ['salt' => $salt];
    } else {
        // No rows found
        $response['status'] = 'error';
        $response['message'] = 'No rows found';
    }
} else {
    // Handle the error
    $response['status'] = 'error';
    $response['message'] = 'Error: ' . mysqli_error($dbConn);
}

// Set the content type to JSON
header('Content-Type: application/json');

// Output the response as JSON
echo json_encode($response);

?>
