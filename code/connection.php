<?php

// Database configuration
$servername = "localhost";
$username = "craftnanny";
$password = "ShadowCow6f22#";
$database = "craftnanny";

// Create connection
$dbConn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$dbConn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>