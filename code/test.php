<?php
 // Include the database connection file
require_once('connection.php');

// Query to count the number of users
$query = "SELECT COUNT(*) AS user_count FROM users";

// Execute the query
$result = mysqli_query($dbConn, $query);

// Check if the query was successful
if ($result) {
    // Fetch the row
    $row = mysqli_fetch_assoc($result);

    // Extract the user count from the result
    $userCount = $row['user_count'];

    echo "Number of users: " . $userCount;
} else {
    // Handle the error
    echo 'Error: ' . mysqli_error($dbConn);
}

// Close the database connection
mysqli_close($dbConn);
?>