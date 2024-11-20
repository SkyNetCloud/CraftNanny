<?php

require_once('connection.php');

$token = isset($_POST['token']) ? $_POST['token'] : '';
$id = isset($_POST['id']) ? $_POST['id'] : '';
$top_input = isset($_POST['top_input']) ? $_POST['top_input'] : '';
$bottom_input = isset($_POST['bottom_input']) ? $_POST['bottom_input'] : '';
$front_input = isset($_POST['front_input']) ? $_POST['front_input'] : '';
$back_input = isset($_POST['back_input']) ? $_POST['back_input'] : '';
$left_input = isset($_POST['left_input']) ? $_POST['left_input'] : '';
$right_input = isset($_POST['right_input']) ? $_POST['right_input'] : '';

$top_input = htmlspecialchars($top_input);
$bottom_input = htmlspecialchars($bottom_input);
$front_input = htmlspecialchars($front_input);
$back_input = htmlspecialchars($back_input);
$left_input = htmlspecialchars($left_input);
$right_input = htmlspecialchars($right_input);

$query2 = "UPDATE redstone_controls SET top_input = ?, 
            bottom_input = ?, 
            front_input = ?, 
            back_input = ?, 
            left_input = ?, 
            right_input = ? 
            WHERE token = ?";

$stmt = mysqli_prepare($dbConn, $query2);
mysqli_stmt_bind_param($stmt, 'sssssss', $top_input, $bottom_input, $front_input, $back_input, $left_input, $right_input, $token);
mysqli_stmt_execute($stmt);

checkEvents($token, $dbConn);
getRsOutputs($token, $id, $version, $dbConn);


function getRsOutputs($token, $id, $version, $dbConn) {
    $query = "UPDATE tokens SET last_seen = NOW() WHERE token = ? AND computer_id = ?";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, 'si', $token, $id);
    $result = mysqli_stmt_execute($stmt);
    
    if ($result) {
        $query2 = "SELECT * from redstone_controls WHERE token = ?";
        $stmt2 = mysqli_prepare($dbConn, $query2);
        mysqli_stmt_bind_param($stmt2, 's', $token);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);
        $row2 = mysqli_fetch_assoc($result2);
        
        $returnString = $version.", ".$row2['top'].", ".$row2['bottom'].", ".$row2['back'].", ".$row2['front'].", ".$row2['left_side'].", ".$row2['right_side'];
        echo $returnString;
    } else {
        echo 'error: token update query failed.';
    }
}


function checkEvents($token, $dbConn) {
    $query = "SELECT * FROM redstone_events WHERE redstone_token = ?";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $query2 = "SELECT * FROM tanks WHERE token = ?";
        $stmt2 = mysqli_prepare($dbConn, $query2);
        mysqli_stmt_bind_param($stmt2, 's', $row['storage_token']);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);
        $row2 = mysqli_fetch_assoc($result2);
        
        $side = '';
        if ($row['side'] == 'top_side') {
            $side = 'top';
        }
        if ($row['side'] == 'bottom_side') {
            $side = 'bottom';
        }
        if ($row['side'] == 'front_side') {
            $side = 'front';
        }
        if ($row['side'] == 'back_side') {
            $side = 'back';
        }
        
        if ($row['event_type'] == '1') {
            if (intval($row2['percent']) > intval($row['trigger_value'])) {
                $query3 = "UPDATE redstone_controls SET ".$side." = ?";
                $stmt3 = mysqli_prepare($dbConn, $query3);
                mysqli_stmt_bind_param($stmt3, 'i', $row['output']);
                mysqli_stmt_execute($stmt3);
            }
        }
        if ($row['event_type'] == '2') {
            if (intval($row2['percent']) < intval($row['trigger_value'])) {
                $query3 = "UPDATE redstone_controls SET ".$side." = ?";
                $stmt3 = mysqli_prepare($dbConn, $query3);
                mysqli_stmt_bind_param($stmt3, 'i', $row['output']);
                mysqli_stmt_execute($stmt3);
            }
        }
    }
}

?>