<?php

// Database configuration
$servername = "old";
$username = "old";
$password = "old";
$database = "old";

// Create connection
$dbConn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$dbConn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>