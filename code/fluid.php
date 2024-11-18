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

$query = "UPDATE tokens SET last_seen = NOW() WHERE token = '".dbEsc($token,$dbConn)."' AND computer_id = ".dbEsc($id,$dbConn);
$result = mysqli_query($dbConn, $query);

if ($result) {
	$query2 = "UPDATE tanks SET tank_name = '".dbEsc($tank_name,$dbConn)."', fluid_type = '".dbEsc($fluid_type,$dbConn)."', percent = '".dbEsc($percent,$dbConn)."' WHERE token = '".dbEsc($token,$dbConn)."'";
	$result2 = mysqli_query($dbConn, $query2);

	echo $version;
} else {
	echo 'error: token update query failed.';
}

function dbEsc($theString, $dbConn) {
    $escapedString = mysqli_real_escape_string($dbConn, $theString);
    return $escapedString;
}

?>