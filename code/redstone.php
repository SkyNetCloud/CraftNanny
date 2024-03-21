<?php

$version = 1;

require_once('connection.php');

$token = $_POST['token'] ?? '';
$id = $_POST['id'] ?? '';
$top_input = $_POST['top_input'] ?? '';
$bottom_input = $_POST['bottom_input'] ?? '';
$front_input = $_POST['front_input'] ?? '';
$back_input = $_POST['back_input'] ?? '';
$left_input = $_POST['left_input'] ?? '';
$right_input = $_POST['right_input'] ?? '';

$query = "UPDATE tokens SET last_seen = NOW() WHERE token = ? AND computer_id = ?";
$stmt = $dbConn->prepare($query);
$stmt->bind_param("si", $token, $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $query2 = "UPDATE redstone_controls SET top_input = ?, bottom_input = ?, front_input = ?, back_input = ?, left_input = ?, right_input = ? WHERE token = ?";
    $stmt2 = $dbConn->prepare($query2);
    $stmt2->bind_param("sssssss", $top_input, $bottom_input, $front_input, $back_input, $left_input, $right_input, $token);
    $stmt2->execute();

    checkEvents($token);
    getRsOutputs($token, $id, $version);
} else {
    echo 'error: token update query failed.';
}

function getRsOutputs($token, $id, $version) {
    global $dbConn;
    $query = "UPDATE tokens SET last_seen = NOW() WHERE token = ? AND computer_id = ?";
    $stmt = $dbConn->prepare($query);
    $stmt->bind_param("si", $token, $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $query2 = "SELECT * FROM redstone_controls WHERE token = ?";
        $stmt2 = $dbConn->prepare($query2);
        $stmt2->bind_param("s", $token);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $row2 = $result2->fetch_assoc();
        
        $returnString = "$version, {$row2['top']}, {$row2['bottom']}, {$row2['back']}, {$row2['front']}, {$row2['left_side']}, {$row2['right_side']}";
        echo $returnString;
    } else {
        echo 'error: token update query failed.';
    }
}

// function checkEvents($token) {
//     global $dbConn;
//     $query = "SELECT * FROM redstone_events WHERE redstone_token = ?";
//     $stmt = $dbConn->prepare($query);
//     $stmt->bind_param("s", $token);
//     $stmt->execute();
//     $result = $stmt->get_result();
    
//     while ($row = $result->fetch_assoc()) {
//         // Logic for checking events
//         // You can use prepared statements here similarly
//     }
// }

?>
