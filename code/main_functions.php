<?php

error_reporting(E_ALL);

  error_reporting(E_ALL | E_NOTICE | E_WARNING);

 ini_set('display_errors', 1);

ini_set('log_errors', 1);

require_once('connection.php');

function doesUserExist($dbConn, $id, $type) {
    // Initialize an array for the response
    $response = [];

    // Check the type and prepare the query
    if ($type == 'main') {
        $query = "SELECT * FROM `users` WHERE username = '" . dbEsc($dbConn, $id) . "'";
    }

    // Execute the query
    $result = mysqli_query($dbConn, $query);

    if (!$result) {
        // If there is an error, set the status to error and add the error message
        $response['status'] = 'error';
        $response['message'] = mysqli_error($dbConn);
    } else {
        // If the query is successful, set the status to success
        $response['status'] = 'success';
    }

    // Initialize the counter for the number of records found
    $counter = 0;

    // Count the number of records returned
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $counter++;
    }

    // Add the number of records to the response
    $response['records'] = $counter;

    // Return the response as JSON
    return json_encode($response);
}


function signIn($dbconn, $username, $password) {
    // Sanitize inputs to avoid XSS attacks
    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);

    // Prepare SQL to get the salt for the user
    $query = "SELECT salt FROM users WHERE username = ?";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Check if the user exists
    if (mysqli_num_rows($result) == 0) {
        return jsonResponse("error", "User not found");
    }

    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $salt = $row['salt'];

    // Hash the password with the salt
    $hash = sha1($salt . $password);

    // Prepare SQL to check if the username and password hash match
    $query2 = "SELECT user_id FROM users WHERE username = ? AND password = ?";
    $stmt2 = mysqli_prepare($dbconn, $query2);
    mysqli_stmt_bind_param($stmt2, "ss", $username, $hash);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);

    // Check if login is successful
    if (mysqli_num_rows($result2) == 0) {
        return jsonResponse("error", "Invalid username or password");
    }

    $row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);

    // Create response data
    $response = [
        "status" => "success",
        "message" => "Login successful",
        "logger_token" => $row2['user_id']
    ];

    return jsonResponse("success", $response);

}

function getUser($dbConn, $user_id) {
    $response = array();  // Initialize the response array
    
    // Escape the user ID to prevent SQL injection
    $escapedUserId = dbEsc($dbConn, $user_id);

    // Query to get the username for the given user_id
    $query2 = "SELECT username FROM users WHERE user_id = '$escapedUserId'";
    $result2 = mysqli_query($dbConn, $query2);

    // Check if the user exists
    if ($result2 && mysqli_num_rows($result2) > 0) {
        $row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);
        // Return the username if the user is found
        $response['user'] = array('username' => $row2['username']);
    } else {
        // If user not found, return an error message
        $response['error'] = 'User not found';
    }

    // Update the last_seen timestamp
    $query3 = "UPDATE users SET last_seen = NOW() WHERE user_id = '$escapedUserId'";
    mysqli_query($dbConn, $query3);

    // Return the response as a single JSON object
    echo json_encode($response);
    exit();  // Ensure no further output is sent
}

function addNewUser($dbConn, $username, $password, $email) {
    // Initialize the response array
    $response = [];

    // Sanitize inputs
    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);
    $email = htmlspecialchars($email);

    // Generate salt and hash the password
    $salt = rand().rand().rand().rand();
    $hash = sha1($salt.$password);

    // Generate user_id
    $user_id = rand().rand().rand().rand();

    // Prepare the SQL query
    $query = "INSERT INTO users (user_id, username, password, salt, email) VALUES ('".$user_id."', '" . dbEsc($dbConn, $username) ."', '" . $hash . "', '" . $salt . "', '".dbEsc($dbConn, $email)."')";

    // Execute the query
    $result = mysqli_query($dbConn, $query);

    // Check if the query was successful
    if (!$result) {
        $response['status'] = 'error';
        $response['message'] = mysqli_error($dbConn);
    } else {
        $response['status'] = 'success';
        $response['token'] = $user_id;
    }

    // Set the content type to JSON and output the response
    header('Content-Type: application/json');
    echo json_encode($response);
}

function getConnections($dbConn, $user_id, $type) {
    // Initialize the response array
    $response = [];

    // Prepare the SQL query to fetch connections using prepared statements
    $query = "SELECT * FROM tokens WHERE user_id = ? AND module_type = ?";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $user_id, $type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Check if the query was successful and if there are results
    if (!$result || mysqli_num_rows($result) == 0) {
        $response['status'] = 'error';
        $response['message'] = 'No connections found';
    } else {
        $response['status'] = 'success';
        $connections = [];

        // Process each row
        while ($row = mysqli_fetch_assoc($result)) {
            $connection = [
                'name' => $row['computer_name'],
                'token' => $row['token'],
                'active' => (time() - strtotime($row['last_seen']) > 200) ? false : true
            ];
            $connections[] = $connection;
        }

        $response['connections'] = $connections;
    }

    // Return the response for further processing (like sending to the client)
    return $response;
}

function getEnergyLevels($dbConn, $user_id) {
    $response = array();

    // Query to get tokens and energy levels using a JOIN to optimize performance
    $query = "
        SELECT t.computer_name, t.token, t.last_seen, es.bat_name, es.energy_type, es.percent
        FROM tokens t
        LEFT JOIN energy_storage es ON t.token = es.token
        WHERE t.user_id = ? AND t.module_type = '1'
    ";

    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $modules = array();
    // Process each row in the result set
    while ($row = mysqli_fetch_assoc($result)) {
        $module = array(
            'name' => $row['computer_name'],
            'token' => $row['token'],
            'active' => (time() - strtotime($row['last_seen']) <= 100) ? true : false,
            'bat_name' => $row['bat_name'] ?? null,
            'energy_type' => $row['energy_type'] ?? null,
            'percent' => $row['percent'] ?? null
        );

        $modules[] = $module;
    }

    // Return the array of modules (with energy information)
    return $modules;
}

// function getLogs($dbConn, $user_id) {
//     // Initialize the response array
//     $response = [];

//     // Prepare and bind parameters for the user_id
//     $query = "SELECT * FROM tokens WHERE user_id = ? AND module_type = '1'";
//     $stmt = mysqli_prepare($dbConn, $query);
//     mysqli_stmt_bind_param($stmt, "s", $user_id);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);

//     // Check if the query was successful
//     if (!$result) {
//         $response['status'] = 'error';
//         $response['message'] = mysqli_error($dbConn);
//     } else {
//         $response['status'] = 'success';
//         $scanners = [];

//         while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
//             $scanner = [
//                 'name' => $row['computer_name'],
//                 'token' => $row['token'],
//                 'active' => (time() - strtotime($row['last_seen']) > 200) ? false : true
//             ];

//             // Get last 10 visitors
//             $query2 = "SELECT DISTINCT(ign) AS ign FROM logs WHERE token = ? ORDER BY timestamp DESC LIMIT 10";
//             $stmt2 = mysqli_prepare($dbConn, $query2);
//             mysqli_stmt_bind_param($stmt2, "s", $row['token']);
//             mysqli_stmt_execute($stmt2);
//             $result2 = mysqli_stmt_get_result($stmt2);

//             $visitors = [];
//             while ($row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC)) {
//                 $visitor = [
//                     'ign' => $row2['ign'],
//                     'token' => $row['token']
//                 ];

//                 // Get the last seen timestamp for each visitor
//                 $query3 = "SELECT timestamp FROM logs WHERE token = ? AND ign = ? ORDER BY timestamp DESC LIMIT 1";
//                 $stmt3 = mysqli_prepare($dbConn, $query3);
//                 mysqli_stmt_bind_param($stmt3, "ss", $row['token'], $row2['ign']);
//                 mysqli_stmt_execute($stmt3);
//                 $result3 = mysqli_stmt_get_result($stmt3);
//                 $row3 = mysqli_fetch_array($result3, MYSQLI_ASSOC);

//                 $visitor['last_seen'] = $row3['timestamp'];
//                 $visitors[] = $visitor;
//             }

//             $scanner['visitors'] = $visitors;
//             $scanners[] = $scanner;
//         }

//         $response['scanners'] = $scanners;
//     }

//     // Set the content type to JSON and output the response
//     header('Content-Type: application/json');
//     echo json_encode($response);
// }

// function getPlayerData($dbConn, $ign, $token) {
//     $response = array();
    
//     $query = "SELECT * FROM logs WHERE token = '".dbEsc($dbConn, $token)."' AND ign = '".dbEsc($dbConn, $ign)."' ORDER BY timestamp DESC LIMIT 50";
//     $result = mysqli_query($dbConn, $query);
    
//     $records = array();
//     while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
//         $record = array(
//             'ign' => $row['ign'],
//             'event' => $row['event'],
//             'time' => $row['timestamp'],
//             'description' => $row['description']
//         );
//         $records[] = $record;
//     }
    
//     $response['recorddata'] = $records;
//     return json_encode($response);

// 	header('Content-Type: application/json');
//     echo json_encode($response);
// }

// function loadRedstoneControls($dbConn, $user_id) {
//     $response = array();
    
//     $query = "SELECT * FROM tokens WHERE user_id = '".dbEsc($dbConn, $user_id)."' AND module_type = '4'";
//     $result = mysqli_query($dbConn, $query);

//     $controls = array();
//     while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
//         $control = array(
//             'name' => $row['computer_name'],
//             'token' => $row['token'],
//             'active' => (time() - strtotime($row['last_seen']) <= 200) ? true : false
//         );

//         $query2 = "SELECT * FROM redstone_controls WHERE token = '".$row['token']."'";
//         $result2 = mysqli_query($dbConn, $query2);
        
//         if ($result2) {
//             $row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);
//             $control['top'] = $row2['top'];
//             $control['bottom'] = $row2['bottom'];
//             $control['front'] = $row2['front'];
//             $control['back'] = $row2['back'];
//             $control['left'] = $row2['left_side'];
//             $control['right'] = $row2['right_side'];

//             $control['top_name'] = $row2['top_name'];
//             $control['bottom_name'] = $row2['bottom_name'];
//             $control['front_name'] = $row2['front_name'];
//             $control['back_name'] = $row2['back_name'];
//             $control['left_name'] = $row2['left_name'];
//             $control['right_name'] = $row2['right_name'];

//             $control['top_input'] = $row2['top_input'];
//             $control['bottom_input'] = $row2['bottom_input'];
//             $control['front_input'] = $row2['front_input'];
//             $control['back_input'] = $row2['back_input'];
//             $control['left_input'] = $row2['left_input'];
//             $control['right_input'] = $row2['right_input'];
//         }

//         $controls[] = $control;
//     }
    
//     $response['recorddata'] = $controls;
//     $response['status'] = 'success';
//     return json_encode($response);

// 	header('Content-Type: application/json');
//     echo json_encode($response);

// }

// function setRedstoneOutput($dbConn, $token, $side, $value, $type) {
//     $response = array();
    
//     if ($type == 'string') {
//         $value = htmlspecialchars($value);
//         $query = "UPDATE redstone_controls SET ".dbEsc($dbConn, $side)." = '".dbEsc($dbConn, $value)."' WHERE token = '".dbEsc($dbConn, $token)."'";
//     } else {
//         $query = "UPDATE redstone_controls SET ".dbEsc($dbConn, $side)." = ".dbEsc($dbConn, $value)." WHERE token = '".dbEsc($dbConn, $token)."'";
//     }

//     $result = mysqli_query($dbConn, $query);
    
//     if ($result) {
//         $response['status'] = 'success';
//     } else {
//         $response['status'] = 'error';
//         $response['message'] = mysqli_error($dbConn);
//     }

//     return json_encode($response);

// 	header('Content-Type: application/json');
//     echo json_encode($response);
// }

function getFluidLevels($dbConn, $user_id) {
    $response = array();

    $query = "SELECT * FROM tokens WHERE user_id = ? AND module_type = '2'";
    $stmt = mysqli_prepare($dbConn, $query);
    
    if (!$stmt) {
        $response['status'] = 'error';
        $response['message'] = mysqli_error($dbConn);
        return json_encode($response);
    }

    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $modules = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $module = array(
            'name' => $row['computer_name'],
            'token' => $row['token'],
            'active' => (time() - strtotime($row['last_seen']) <= 200) ? 'true' : 'false'
        );

        $query2 = "SELECT * FROM tanks WHERE token = ?";
        $stmt2 = mysqli_prepare($dbConn, $query2);
        
        if (!$stmt2) {
            $response['status'] = 'error';
            $response['message'] = mysqli_error($dbConn);
            return json_encode($response);
        }

        mysqli_stmt_bind_param($stmt2, "s", $row['token']);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);

        $row2 = mysqli_fetch_assoc($result2);
        $module['tank_name'] = $row2['tank_name'];
        $module['fluid_type'] = $row2['fluid_type'];
        $module['percent'] = $row2['percent'];

        $modules[] = $module;
    }

    $response['recorddata'] = $modules;
    $response['status'] = 'success';
    return json_encode($response);

	header('Content-Type: application/json');
    echo json_encode($response);
}



//     // Constructing the response
//     $response['recorddata'] = $modules;
//     $response['status'] = 'success';

//     // Output the response as JSON
//     header('Content-Type: application/json');
//     echo json_encode($response);
// }


function removeModule($dbConn, $token) {
    $response = array();

    $query2 = "DELETE FROM tokens WHERE token = '".dbEsc($dbConn, $token)."'";
    $result2 = mysqli_query($dbConn, $query2);

    if ($result2) {
        $response['status'] = 'success';
    } else {
        $response['status'] = 'error';
        $response['message'] = mysqli_error($dbConn);
    }

    return json_encode($response);
}

// 	header('Content-Type: application/json');
//     echo json_encode($response);
// }

function redstoneEventDropdowns($dbConn, $user_id) {
    // Initialize the response array
    $response = [
        'storage_modules' => [],
        'redstone_modules' => []
    ];

    // Query for storage modules
    $query = "SELECT * FROM tokens WHERE user_id = '".dbEsc($dbConn, $user_id)."' AND (module_type = '1' OR module_type = '2')";
    $result = mysqli_query($dbConn, $query);

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $response['storage_modules'][] = [
            'name' => $row['computer_name'],
            'token' => $row['token']
        ];
    }

    // Query for redstone modules
    $query = "SELECT * FROM tokens WHERE user_id = ? AND module_type = '3'";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $response['redstone_modules'][] = [
            'name' => $row['computer_name'],
            'token' => $row['token']
        ];
    }

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

function getRedstoneSides($dbConn, $token) {
    // Initialize the response array
    $response = [
        'modules' => []
    ];

    $query = "SELECT * FROM redstone_controls WHERE token = ?";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $response['modules'][] = [
            'top_name' => $row['top_name'],
            'bottom_name' => $row['bottom_name'],
            'front_name' => $row['front_name'],
            'back_name' => $row['back_name'],
            'left_name' => $row['left_name'],
            'right_name' => $row['right_name']
        ];
    }

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

function createRedstoneEvent($dbConn, $storageToken, $redstoneToken, $triggerValue, $side, $outputValue, $eventType, $user_id) {
    // Initialize the response array
    $response = [];

    $query = "INSERT INTO redstone_events (redstone_token, storage_token, event_type, trigger_value, side, output, user_id) VALUES " .
        "('".dbEsc($dbConn, $redstoneToken)."', '".dbEsc($dbConn, $storageToken)."', ".dbEsc($dbConn, $eventType).", ".dbEsc($dbConn, $triggerValue).", '".dbEsc($dbConn, $side)."', ".dbEsc($dbConn, $outputValue).", '".dbEsc($dbConn, $user_id)."')";

    $result = mysqli_query($dbConn, $query);

    if (!$result) {
        $response['status'] = 'error';
        $response['message'] = mysqli_error($dbConn);
    } else {
        $response['status'] = 'success';
    }

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

function loadRedstoneEvents($dbConn, $user_id) {
    // Initialize the response array
    $response = [
        'events' => []
    ];

    $query = "SELECT * FROM redstone_events WHERE user_id = ?";
    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $eventData = [
            'event_id' => $row['event_id'],
            'event_type' => $row['event_type'],
            'trigger_value' => $row['trigger_value'],
            'side' => $row['side'],
            'output' => $row['output'],
            'redstone_module' => null,
            'storage_module' => null,
            'redstone_active' => false,
            'storage_active' => false
        ];

        // Get redstone module details
        $query2 = "SELECT computer_name, last_seen FROM tokens WHERE token = '".dbEsc($dbConn, $row['redstone_token'])."'";
        $result2 = mysqli_query($dbConn, $query2);
        $row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);
        $eventData['redstone_module'] = $row2['computer_name'];
        $eventData['redstone_active'] = (time() - strtotime($row2['last_seen'])) <= 200;

        // Get storage module details
        $query3 = "SELECT computer_name, last_seen FROM tokens WHERE token = '".dbEsc($dbConn, $row['storage_token'])."'";
        $result3 = mysqli_query($dbConn, $query3);
        $row3 = mysqli_fetch_array($result3, MYSQLI_ASSOC);
        $eventData['storage_module'] = $row3['computer_name'];
        $eventData['storage_active'] = (time() - strtotime($row3['last_seen'])) <= 200;

        $response['events'][] = $eventData;
    }

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

function removeEvent($dbConn, $event_id) {
    // Initialize the response array
    $response = [];

    // Prepare and bind the SQL query to delete the event
    $query2 = "DELETE FROM redstone_events WHERE event_id = ?";
    $stmt = mysqli_prepare($dbConn, $query2);

    // Bind the event_id parameter
    mysqli_stmt_bind_param($stmt, "s", $event_id);

    // Execute the statement
    $result2 = mysqli_stmt_execute($stmt);

    // Check if the query was successful
    if (!$result2) {
        $response['status'] = 'error';
        $response['message'] = mysqli_error($dbConn);
    } else {
        $response['status'] = 'success';
    }

    // Set the content type to JSON and output the response
    header('Content-Type: application/json');
    echo json_encode($response);
}

function jsonResponse($status, $data) {
    // Set the response header to application/json
    header('Content-Type: application/json');
    
    // Prepare the response structure
    $response = [
        "status" => $status,
        "data" => $data
    ];

    // Return the JSON response
    return json_encode($response);
}


function dbEsc($dbConn,$theString) {
    $escapedString = mysqli_real_escape_string($dbConn, $theString);
    return $escapedString;
}

function dbError(&$xmlDoc, &$xmlNode, $theMessage) {
	$errorNode = $xmlDoc->createElement('mysqlError', $theMessage);
	$xmlNode->appendChild($errorNode);
}



?>
