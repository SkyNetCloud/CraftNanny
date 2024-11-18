<?php

require_once('connection.php');

$token = $_POST['token'];
$id = $_POST['id'];
$tank_name = $_POST['tank_name'];
$fluid_type = $_POST['fluid_type'];
$percent = $_POST['percent'];

$fluid_type = htmlspecialchars($fluid_type);
$tank_name = htmlspecialchars($tank_name);
$percent = htmlspecialchars($percent);

$query = "UPDATE tokens SET last_seen = NOW() WHERE token = ? AND computer_id = ?";
$stmt = mysqli_prepare($dbConn, $query);
mysqli_stmt_bind_param($stmt, "si", $token, $id);
mysqli_stmt_execute($stmt);


if (mysqli_stmt_affected_rows($stmt) > 0 ){

	$query2 = "UPDATE tanks SET tank_name = ?, fluid_type = ?, percen = ? WHERE token = ?";
	$stmt2 = mysqli_prepare($dbConn, $query2);
	mysqli_stmt_bind_param($stmt2, "ssss", $tank_name,$fluid_type,$percent,$token);

	if (mysqli_stmt_affected_rows($stmt2) > 0){
		echo "Success";
	} else {
		echo "error: tanks update query failed.";
	}
} else {
	echo "error: token update query failed.";
}


// Close prepared statements
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt2);

// Close database connection
mysqli_close($dbConn);


?>
