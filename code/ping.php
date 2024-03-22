<?php

$version = 2;

require_once('connection.php');

$token = $_POST['token'];
$id = $_POST['id'];

logPing($token, $id, $version);

function logPing($token, $id, $version) {
	$query = "UPDATE tokens SET last_seen = NOW() WHERE token = '".dbEsc($token,$dbConn)."' AND computer_id = '".dbEsc($id,$dbConn)."'";
	$result = mysqli_query($dbConn, $query);
	if ($result) {
		echo $version;
	} else {
		echo $version;
	}
}

function dbEsc($theString, $dbConn) {
    $escapedString = mysqli_real_escape_string($dbConn, $theString);
    return $escapedString;
}


function dbError(&$xmlDoc, &$xmlNode, $theMessage) {
	$errorNode = $xmlDoc->createElement('mysqlError', $theMessage);
	$xmlNode->appendChild($errorNode);
}


?>
