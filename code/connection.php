<?php

// Database configuration
$servername = "localhost";
$username = "prod";
$password = "Removed";
$database = "craftnanny_prod_db";

// Create connection
$dbConn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$dbConn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
