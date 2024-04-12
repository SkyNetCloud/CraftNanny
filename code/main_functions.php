<?php
// Enable error reporting to display all errors
error_reporting(E_ALL);

// Optionally, you can also display notices and warnings along with other error types
 error_reporting(E_ALL | E_NOTICE | E_WARNING);

// If you want to display errors on the web page, you can set display_errors to On in php.ini
ini_set('display_errors', 1);

// If you want to log errors to a file, you can set log_errors to On in php.ini
ini_set('log_errors', 1);

require_once('connection.php');

function doesUserExist($dbConn, $xmlDoc, $id, $type) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	if($type == 'google') {
		$query = "select * from google_users where google_id = " . $id;
	} else if ($type == 'main') {
		$query = "select * from users where username = '" .dbEsc($dbConn, $id). "'";
	}


	$result = mysqli_query($dbConn, $query);

	if (!($result)) {
		$statusNode = $xmlDoc->createElement('status', $query);

		dbError($xmlDoc, $recordDataNode, mysqli_error());
	} else {
		$statusNode = $xmlDoc->createElement('status', 'success');
	}

	$counter = 0;
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC )) {
		$counter = $counter + 1;
	}
	$statusNode = $xmlDoc->createElement('records', $counter);


	$recordDataNode->appendChild($statusNode);

	return $recordDataNode;
}

function addGoogleUser($dbConn, $xmlDoc, $google_id, $name, $email, $image_url) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query = "INSERT INTO google_users (google_id, username, name, email, img_url) " .
				"VALUES ('".$google_id."', '" . $name ."', '" . $name . "', '" . $email . "', '" . $image_url . "')";

	$result = mysqli_query($dbConn, $query);

	if (!($result)) {
		$statusNode = $xmlDoc->createElement('status', $query);

		dbError($xmlDoc, $recordDataNode, mysqli_error());
	} else {
		$statusNode = $xmlDoc->createElement('status', $google_id);
	}

	$recordDataNode->appendChild($statusNode);

	return $recordDataNode;
}

function signIn($dbConn, $xmlDoc, $username, $password) {
    $recordDataNode = $xmlDoc->createElement('recorddata');

    // Sanitize input
    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);

    // Retrieve salt for the user
    $query = "SELECT salt FROM users WHERE username = '".$username."'";
    $stmt = mysqli_prepare($dbConn, $query);
    // mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result || mysqli_num_rows($result) == 0) {
        dbError($xmlDoc, $recordDataNode, "User not found");
        return $recordDataNode;
    }

    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $salt = $row['salt'];

    // Hash the password with the retrieved salt
    $hash = sha1($salt . $password);

    // Check if the username and hashed password match
    $query2 = "SELECT user_id FROM users WHERE username = ? AND password = ?";
    $stmt2 = mysqli_prepare($dbConn, $query2);
    mysqli_stmt_bind_param($stmt2, "ss", $username, $hash);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);

    if (!$result2 || mysqli_num_rows($result2) == 0) {
        dbError($xmlDoc, $recordDataNode, "Invalid username or password");
        return $recordDataNode;
    }

    // Fetch the user_id and create a token
    $row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);
    $token = $row2['user_id'];

    // Create XML nodes
    $statusNode = $xmlDoc->createElement('token', $token);
    $recordDataNode->appendChild($statusNode);

    return $recordDataNode;
}

function addNewUser($dbConn, $xmlDoc, $username, $password, $email) {
	$recordDataNode = $xmlDoc->createElement('userdata');

	$username = htmlspecialchars($username);
	$password = htmlspecialchars($password);
	$email = htmlspecialchars($email);

	$salt = rand().rand().rand().rand();
	$hash = sha1($salt.$password);

	$user_id = rand().rand().rand().rand();

	$query = "INSERT INTO users (user_id, username, password, salt, email) " .
				"VALUES ('".$user_id."', '" . dbEsc($dbConn, $username) ."', '" . $hash . "', '" . $salt . "', '".dbEsc($dbConn, $email)."')";

	$result = mysqli_query($dbConn, $query);
	

	if (!($result)) {
		$statusNode = $xmlDoc->createElement('status', $query);

		dbError($xmlDoc, $recordDataNode, mysqli_error());
	} else {
		$statusNode = $xmlDoc->createElement('token', $user_id);
	}

	$recordDataNode->appendChild($statusNode);

	return $recordDataNode;
}

function getConnections($dbConn, $xmlDoc, $user_id, $type) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

  $query = "SELECT * FROM tokens WHERE user_id = '".dbEsc($dbConn, $user_id)."' AND module_type = '".dbEsc($dbConn, $type)."'";

	$result = mysqli_query($dbConn, $query);

	if (!($result)) {
		$statusNode = $xmlDoc->createElement('status', $query);

		dbError($xmlDoc, $recordDataNode, mysqli_error());
	} else {
		$statusNode = $xmlDoc->createElement('status', 'success');
	}


  while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC )) {
	$theChildNode = $xmlDoc->createElement('connection');
	$theChildNode->setAttribute('name', $row['computer_name']);
	$theChildNode->setAttribute('token', $row['token']);

	$datetime1 = strtotime($row['last_seen']);
	$datetime2 = time();
	$diff = $datetime2-$datetime1;
	if ($diff > 200) {
		$theChildNode->setAttribute('active', false);
	} else {
		$theChildNode->setAttribute('active', true);
	}

	$recordDataNode->appendChild($theChildNode);
  }
  	$recordDataNode->appendChild($statusNode);
	return $recordDataNode;
}

function getLogs($dbConn, $xmlDoc, $user_id, $lastUpdateTime) {
    // main XML element to return
    $recordDataNode = $xmlDoc->createElement('recorddata');

    // get users tokens and scanner names
    $query = "SELECT * from tokens where user_id = '".dbEsc($dbConn, $user_id)."' AND module_type = '1'";
    $result = mysqli_query($dbConn, $query);
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC )) {
        // Check if the scanner's last update time is after the lastUpdateTime
        $lastSeenTime = strtotime($row['last_seen']);
        if ($lastSeenTime > $lastUpdateTime) {
            $theScannerNode = $xmlDoc->createElement('scanner');
            $nameNode = $xmlDoc->createElement('name');
            $nameNode->setAttribute('name', $row['computer_name']);
            $nameNode->setAttribute('token', $row['token']);
            $datetime1 = strtotime($row['last_seen']);
            $datetime2 = time();
            $diff = $datetime2 - $datetime1;
            if ($diff > 200) {
                $nameNode->setAttribute('active', false);
            } else {
                $nameNode->setAttribute('active', true);
            }
            $theScannerNode->appendChild($nameNode);
            // for each scanner, get last 10 visitors
            $query2 = "SELECT DISTINCT(ign) AS ign from logs where token = '".dbEsc($dbConn, $row['token'])."' ORDER BY timestamp DESC LIMIT 10";
            $result2 = mysqli_query($dbConn, $query2);
            while ($row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC )) {
                $VistorNode = $xmlDoc->createElement('visitor');
                $VistorNode->setAttribute('ign', $row2['ign']);
                $VistorNode->setAttribute('token', $row['token']);

                $query3 = "SELECT timestamp FROM logs WHERE token = '".$row['token']."' AND ign = '".$row2['ign']."' ORDER BY timestamp DESC LIMIT 1";
                $result3 = mysqli_query($dbConn, $query3);
                $row3 = mysqli_fetch_array($result3, MYSQLI_ASSOC );
                $VistorNode->setAttribute('last_seen', $row3['timestamp']);
                $theScannerNode->appendChild($VistorNode);
            }
            $recordDataNode->appendChild($theScannerNode);
        }
    }

    return $recordDataNode;
}

function getPlayerData($dbConn, $xmlDoc, $ign, $token) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query = "SELECT * from logs where token = '".dbEsc($dbConn, $token)."' AND ign = '".dbEsc($dbConn, $ign)."' ORDER BY timestamp DESC LIMIT 50";
	$result = mysqli_query($dbConn, $query);
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC )) {
		$VistorNode = $xmlDoc->createElement('record');
		$VistorNode->setAttribute('ign', $row['ign']);
		$VistorNode->setAttribute('event', $row['event']);
		$VistorNode->setAttribute('time', $row['timestamp']);
		$VistorNode->setAttribute('description', $row['description']);
		$recordDataNode->appendChild($VistorNode);
	}
	return $recordDataNode;
}

function getUser($dbConn, $xmlDoc, $user_id) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query2 = "SELECT username from users where user_id = '".dbEsc($dbConn, $user_id)."'";
	$result2 = mysqli_query($dbConn, $query2);
	$row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC );
	$userNode = $xmlDoc->createElement('user');
	$userNode->setAttribute('username', $row2['username']);

	$recordDataNode->appendChild($userNode);

	$query3 = "UPDATE users SET last_seen = NOW() WHERE user_id = '".dbEsc($dbConn, $user_id)."'";
	$result3 = mysqli_query($dbConn, $query3);

	return $recordDataNode;
}

function loadRedstoneControls($dbConn, $xmlDoc, $user_id) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query = "SELECT * from tokens where user_id = '".dbEsc($dbConn, $user_id)."' AND module_type = '4'";
	$result = mysqli_query($dbConn, $query);

	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC )) {
		$controlNode = $xmlDoc->createElement('controls');
		$controlNode->setAttribute('name', $row['computer_name']);
		$controlNode->setAttribute('token', $row['token']);

		$datetime1 = strtotime($row['last_seen']);
		$datetime2 = time();
		$diff = $datetime2-$datetime1;
		if ($diff > 200) {
			$controlNode->setAttribute('active', false);
		} else {
			$controlNode->setAttribute('active', true);
		}

		$query2 = "SELECT * from redstone_controls where token = '".$row['token']."'";
		$result2 = mysqli_query($dbConn, $query2);

		if (!($result2)) {
			$statusNode = $xmlDoc->createElement('status', $query);

			dbError($xmlDoc, $recordDataNode, mysqli_error());
		} else {
			$statusNode = $xmlDoc->createElement('status', 'success');
		}

		$row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC );

		$controlNode->setAttribute('top', $row2['top']);
		$controlNode->setAttribute('bottom', $row2['bottom']);
		$controlNode->setAttribute('front', $row2['front']);
		$controlNode->setAttribute('back', $row2['back']);
		$controlNode->setAttribute('left', $row2['left_side']);
		$controlNode->setAttribute('right', $row2['right_side']);

		$controlNode->setAttribute('top_name', $row2['top_name']);
		$controlNode->setAttribute('bottom_name', $row2['bottom_name']);
		$controlNode->setAttribute('front_name', $row2['front_name']);
		$controlNode->setAttribute('back_name', $row2['back_name']);
		$controlNode->setAttribute('left_name', $row2['left_name']);
		$controlNode->setAttribute('right_name', $row2['right_name']);

		$controlNode->setAttribute('top_input', $row2['top_input']);
		$controlNode->setAttribute('bottom_input', $row2['bottom_input']);
		$controlNode->setAttribute('front_input', $row2['front_input']);
		$controlNode->setAttribute('back_input', $row2['back_input']);
		$controlNode->setAttribute('left_input', $row2['left_input']);
		$controlNode->setAttribute('right_input', $row2['right_input']);

		$recordDataNode->appendChild($controlNode);

	}
	$recordDataNode->appendChild($statusNode);
	return $recordDataNode;
}

function setRedstoneOutput($dbConn, $xmlDoc, $token, $side, $value, $type) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	if ($type == 'string') {
		$value = htmlspecialchars($value);
		$query = "UPDATE redstone_controls SET ".dbEsc($dbConn, $side)." = '".dbEsc($dbConn, $value)."' WHERE token = '".dbEsc($dbConn, $token)."'";
	} else {
		$query = "UPDATE redstone_controls SET ".dbEsc($dbConn, $side)." = ".dbEsc($dbConn, $value)." WHERE token = '".dbEsc($dbConn, $token)."'";
	}

	$result = mysqli_query($dbConn, $query);

	if (!($result)) {
		$statusNode = $xmlDoc->createElement('status', $query);

		dbError($xmlDoc, $recordDataNode, mysqli_error());
	} else {
		$statusNode = $xmlDoc->createElement('status', 'success');
	}
	$recordDataNode->appendChild($statusNode);
	return $recordDataNode;
}

function getFluidLevels($dbConn, $xmlDoc, $user_id) {
    $recordDataNode = $xmlDoc->createElement('recorddata');
    $statusNode = $xmlDoc->createElement('status'); // Initialize status node outside the loop

    $query = "SELECT * FROM tokens WHERE user_id = ? AND module_type = '3'";
    $stmt = mysqli_prepare($dbConn, $query);

    // Check if the statement preparation failed
    if (!$stmt) {
        dbError($xmlDoc, $recordDataNode, mysqli_error($dbConn));
        return $recordDataNode;
    }

    // Bind the user_id parameter
    mysqli_stmt_bind_param($stmt, "s", $user_id);

    // Execute the statement
    mysqli_stmt_execute($stmt);

    // Get the result set
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $controlNode = $xmlDoc->createElement('modules');
        $controlNode->setAttribute('name', $row['computer_name']);
        $controlNode->setAttribute('token', $row['token']);

        $datetime1 = strtotime($row['last_seen']);
        $datetime2 = time();
        $diff = $datetime2 - $datetime1;
        $controlNode->setAttribute('active', ($diff <= 200) ? 'true' : 'false');

        $query2 = "SELECT * from tanks where token = ?";
        $stmt2 = mysqli_prepare($dbConn, $query2);

        // Check if the statement preparation failed
        if (!$stmt2) {
            dbError($xmlDoc, $recordDataNode, mysqli_error($dbConn));
            return $recordDataNode;
        }

        mysqli_stmt_bind_param($stmt2, "s", $row['token']);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);

        if (!$result2) {
            dbError($xmlDoc, $recordDataNode, mysqli_error($dbConn));
            return $recordDataNode;
        }

        $row2 = mysqli_fetch_assoc($result2);
        $controlNode->setAttribute('tank_name', $row2['tank_name']);
        $controlNode->setAttribute('fluid_type', $row2['fluid_type']);
        $controlNode->setAttribute('percent', $row2['percent']);

        $recordDataNode->appendChild($controlNode);
    }

    $statusNode->textContent = 'success'; // Set the content of the status node
    $recordDataNode->appendChild($statusNode);
    return $recordDataNode;
}


function getEnergyLevels($dbConn, $xmlDoc, $user_id) {
    $recordDataNode = $xmlDoc->createElement('recorddata');

    $query = "SELECT * FROM tokens WHERE user_id = ? AND module_type = '2'";
    $stmt = mysqli_prepare($dbConn, $query);

    // Bind the user_id parameter
    mysqli_stmt_bind_param($stmt, "s", $user_id);

    // Execute the statement
    mysqli_stmt_execute($stmt);

    // Get the result set
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $controlNode = $xmlDoc->createElement('modules');
        $controlNode->setAttribute('name', $row['computer_name']);
        $controlNode->setAttribute('token', $row['token']);

        $datetime1 = strtotime($row['last_seen']);
        $datetime2 = time();
        $diff = $datetime2 - $datetime1;
        if ($diff > 200) {
            $controlNode->setAttribute('active', false);
        } else {
            $controlNode->setAttribute('active', true);
        }

        $query2 = "SELECT * FROM energy_storage WHERE token = ?";
        $stmt2 = mysqli_prepare($dbConn, $query2);

        // Bind the token parameter
        mysqli_stmt_bind_param($stmt2, "s", $row['token']);

        // Execute the statement
        mysqli_stmt_execute($stmt2);

        // Get the result set
        $result2 = mysqli_stmt_get_result($stmt2);

        if (!$result2) {
            $statusNode = $xmlDoc->createElement('status', 'error');
            dbError($xmlDoc, $recordDataNode, mysqli_error($dbConn)); // Assuming $dbConn is the database connection object
        } else {
            $statusNode = $xmlDoc->createElement('status', 'success');
            $row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);

            $controlNode->setAttribute('bat_name', $row2['bat_name']);
            $controlNode->setAttribute('energy_type', $row2['energy_type']);
            $controlNode->setAttribute('percent', $row2['percent']);
        }

        $recordDataNode->appendChild($controlNode);
    }
    $recordDataNode->appendChild($statusNode);
    return $recordDataNode;
}

function removeModule($dbConn, $xmlDoc, $token) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query2 = "DELETE FROM tokens WHERE token = '".dbEsc($dbConn, $token)."'";
	$result2 = mysqli_query($dbConn, $query2);

	if (!($result2)) {
			$statusNode = $xmlDoc->createElement('status', $query);

			dbError($xmlDoc, $recordDataNode, mysqli_error());
		} else {
			$statusNode = $xmlDoc->createElement('status', 'success');
		}

	$recordDataNode->appendChild($statusNode);

	return $recordDataNode;
}

function redstoneEventDropdowns($dbConn, $xmlDoc, $user_id) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query = "SELECT * from tokens where user_id = '".dbEsc($dbConn, $user_id)."' AND (module_type = '2' OR module_type = '3')";
	$result = mysqli_query($dbConn, $query);

	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC )) {
		$controlNode = $xmlDoc->createElement('storage_modules');
		$controlNode->setAttribute('name', $row['computer_name']);
		$controlNode->setAttribute('token', $row['token']);
		$recordDataNode->appendChild($controlNode);
	}

	$query = "SELECT * FROM tokens WHERE user_id = ? AND module_type = '4'";
	$stmt = mysqli_prepare($dbConn, $query);
	
	// Bind the user_id parameter
	mysqli_stmt_bind_param($stmt, "s", $user_id);
	
	// Execute the statement
	mysqli_stmt_execute($stmt);
	
	// Get the result set
	$result = mysqli_stmt_get_result($stmt);

	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC )) {
		$controlNode = $xmlDoc->createElement('redstone_modules');
		$controlNode->setAttribute('name', $row['computer_name']);
		$controlNode->setAttribute('token', $row['token']);
		$recordDataNode->appendChild($controlNode);
	}

	return $recordDataNode;
}

function getRedstoneSides($dbConn, $xmlDoc, $token) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query = "SELECT * FROM redstone_controls WHERE token = ?";
	$stmt = mysqli_prepare($dbConn, $query);
	
	// Bind the token parameter
	mysqli_stmt_bind_param($stmt, "s", $token);
	
	// Execute the statement
	mysqli_stmt_execute($stmt);
	
	// Get the result set
	$result = mysqli_stmt_get_result($stmt);

	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC )) {
		$controlNode = $xmlDoc->createElement('modules');
		$controlNode->setAttribute('top_name', $row['top_name']);
		$controlNode->setAttribute('bottom_name', $row['bottom_name']);
		$controlNode->setAttribute('front_name', $row['front_name']);
		$controlNode->setAttribute('back_name', $row['back_name']);
		$controlNode->setAttribute('left_name', $row['left_name']);
		$controlNode->setAttribute('right_name', $row['right_name']);
		$recordDataNode->appendChild($controlNode);
	}

	return $recordDataNode;
}

function createRedstoneEvent($dbConn, $xmlDoc, $storageToken, $redstoneToken, $triggerValue, $side, $outputValue, $eventType, $user_id) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query = "INSERT INTO redstone_events (redstone_token, storage_token, event_type, trigger_value, side, output, user_id) VALUES " .
				"('".dbEsc($dbConn, $redstoneToken)."', '".dbEsc($dbConn, $storageToken)."', ".dbEsc($dbConn, $eventType).", ".dbEsc($dbConn, $triggerValue).", '".dbEsc($dbConn, $side)."', ".dbEsc($dbConn, $outputValue).", '".dbEsc($dbConn, $user_id)."')";
	$result = mysqli_query($dbConn, $query);

	if (!($result)) {
		$statusNode = $xmlDoc->createElement('status', $query);

		dbError($xmlDoc, $recordDataNode, mysqli_error());
	} else {
		$statusNode = $xmlDoc->createElement('status', 'success');
	}

	$recordDataNode->appendChild($statusNode);

	return $recordDataNode;
}

function loadRedstoneEvents($dbConn, $xmlDoc, $user_id) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query = "SELECT * FROM redstone_events WHERE user_id = ?";
	$stmt = mysqli_prepare($dbConn, $query);
	
	// Bind the user_id parameter
	mysqli_stmt_bind_param($stmt, "s", $user_id);
	
	// Execute the statement
	mysqli_stmt_execute($stmt);
	
	// Get the result set
	$result = mysqli_stmt_get_result($stmt);

	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC )) {
		$controlNode = $xmlDoc->createElement('events');

		$query2 = "SELECT computer_name, last_seen FROM tokens WHERE token = '".dbEsc($dbConn, $row['redstone_token'])."'";
		$result2 = mysqli_query($dbConn, $query2);
		$row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC );
		$controlNode->setAttribute('redstone_module', $row2['computer_name']);
		$datetime1 = strtotime($row2['last_seen']);
		$datetime2 = time();
		$diff = $datetime2-$datetime1;
		if ($diff > 200) {
			$controlNode->setAttribute('redstone_active', false);
		} else {
			$controlNode->setAttribute('redstone_active', true);
		}

		$query3 = "SELECT computer_name, last_seen FROM tokens WHERE token = '".dbEsc($dbConn, $row['storage_token'])."'";
		$result3 = mysqli_query($dbConn, $query3);
		$row3 = mysqli_fetch_array($result3, MYSQLI_ASSOC );
		$controlNode->setAttribute('storage_module', $row3['computer_name']);
		$datetime1 = strtotime($row3['last_seen']);
		$datetime2 = time();
		$diff = $datetime2-$datetime1;
		if ($diff > 200) {
			$controlNode->setAttribute('storage_active', false);
		} else {
			$controlNode->setAttribute('storage_active', true);
		}

		$controlNode->setAttribute('event_type', $row['event_type']);
		$controlNode->setAttribute('trigger_value', $row['trigger_value']);
		$controlNode->setAttribute('side', $row['side']);
		$controlNode->setAttribute('output', $row['output']);
		$controlNode->setAttribute('event_id', $row['event_id']);
		$recordDataNode->appendChild($controlNode);
	}

	return $recordDataNode;
}

function removeEvent($dbConn, $xmlDoc, $event_id) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query2 = "DELETE FROM redstone_events WHERE event_id = ?";
	$stmt = mysqli_prepare($dbConn, $query2);
	
	// Bind the event_id parameter
	mysqli_stmt_bind_param($stmt, "s", $event_id);
	
	// Execute the statement
	$result2 = mysqli_stmt_execute($stmt);

	if (!($result2)) {
			$statusNode = $xmlDoc->createElement('status', $query);

			dbError($xmlDoc, $recordDataNode, mysqli_error());
		} else {
			$statusNode = $xmlDoc->createElement('status', 'success');
		}

	$recordDataNode->appendChild($statusNode);

	return $recordDataNode;
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
