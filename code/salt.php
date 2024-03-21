<?php

$version = 1;

$username = $_POST['user'] ?? '';
require_once('connection.php');

// Check if username is provided
if (!empty($username)) {
    // Prepare the SQL query with a placeholder for username
    $query = "SELECT salt FROM users WHERE username = ?";
    
    // Prepare the statement
    $stmt = $dbConn->prepare($query);
    
    // Bind the parameter
    $stmt->bind_param("s", $username);
    
    // Execute the statement
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();
    
    // Check if the query was successful
    if ($result->num_rows > 0) {
        // Fetch the row
        $row = $result->fetch_assoc();
        $salt = $row['salt'];
        echo $salt;
    } else {
        echo 'error';
    }
} else {
    echo 'error: Username not provided';
}

// Close the database connection (optional)
//$dbConn->close();

?>
