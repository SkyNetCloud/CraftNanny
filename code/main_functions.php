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

function dbEsc($theString) {
	$theString = mysql_real_escape_string($theString);
	return $theString;
}

function dbError(&$xmlDoc, &$xmlNode, $theMessage) {
	$errorNode = $xmlDoc->createElement('mysqlError', $theMessage);
	$xmlNode->appendChild($errorNode);
}

function doesUserExist($dbConn, $xmlDoc, $id, $type) {
    $recordDataNode = $xmlDoc->createElement('recorddata');

    if ($type == 'google') {
        // Prepare and execute the query to check existence of Google user using parameterized query
        $query = "SELECT * FROM google_users WHERE google_id = ?";
        $stmt = $dbConn->prepare($query);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
    } else if ($type == 'main') {
        // Prepare and execute the query to check existence of main user using parameterized query
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $dbConn->prepare($query);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    if (!$result) {
        // Handle database error
        $statusNode = $xmlDoc->createElement('status', 'Error executing query: ' . $query);
        dbError($xmlDoc, $recordDataNode, mysqli_error($dbConn));
    } else {
        // Query executed successfully
        $statusNode = $xmlDoc->createElement('status', 'success');

        // Count the number of records returned
        $counter = mysqli_num_rows($result);
        $recordsNode = $xmlDoc->createElement('records', $counter);
        $recordDataNode->appendChild($recordsNode);
    }

    $recordDataNode->appendChild($statusNode);

    return $recordDataNode;
}

function addGoogleUser($dbConn, $xmlDoc, $google_id, $name, $email, $image_url) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query = "INSERT INTO google_users (google_id, username, name, email, img_url) " .
				"VALUES ('".$google_id."', '" . $name ."', '" . $name . "', '" . $email . "', '" . $image_url . "')";

	$result = mysqli_query($query);

	if (!($result)) {
		$statusNode = $xmlDoc->createElement('status', $query);

		dbError($xmlDoc, $recordDataNode, mysql_error());
	} else {
		$statusNode = $xmlDoc->createElement('status', $google_id);
	}

	$recordDataNode->appendChild($statusNode);

	return $recordDataNode;
}

function signIn($dbConn, $xmlDoc, $username, $password) {
    $recordDataNode = $xmlDoc->createElement('recorddata');

    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);

    $salt = '';
    // Prepare and execute the query to retrieve salt using parameterized query
    $query = "SELECT salt FROM users WHERE username = ?";
    $stmt = $dbConn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $salt = $row['salt'];
    }

    // Generate hash with salt and password
	$hash = sha1($salt.$password);


    // Prepare and execute the query to verify username and password using parameterized query
    $query2 = "SELECT user_id FROM users WHERE username = ? AND password = ?";
    $stmt2 = $dbConn->prepare($query2);
    $stmt2->bind_param("ss", $username, $hash);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if (!$result2) {
        // Handle database error
        dbError($xmlDoc, $recordDataNode, mysqli_error($dbConn));
    } else {
        // If user is authenticated, return user_id as token
        if ($row2 = $result2->fetch_assoc()) {
            $statusNode = $xmlDoc->createElement('token', $row2['user_id']);
            $recordDataNode->appendChild($statusNode);
        } else {
            // Handle invalid credentials
            $statusNode = $xmlDoc->createElement('status', 'Invalid username or password');
            $recordDataNode->appendChild($statusNode);
        }
    }

    return $recordDataNode;
}

function addNewUser($dbConn, $xmlDoc, $username, $password, $email) {
    $recordDataNode = $xmlDoc->createElement('userdata');

    // Sanitize input
    $username = htmlspecialchars($username);
    $email = htmlspecialchars($email);

    // Generate salt and hash password
	$salt = rand().rand().rand().rand();
	$hash = sha1($salt.$password);

    // Generate a unique user ID
	$user_id = rand().rand().rand().rand();

    // Prepare and execute the query using prepared statements
    $query = "INSERT INTO users (user_id, username, password, salt, email) VALUES (?, ?, ?, ?, ?)";
    $stmt = $dbConn->prepare($query);
    $stmt->bind_param("sssss", $user_id, $username, $hash, $salt, $email);
    $result = $stmt->execute();
    $stmt->close();

    // Check if the query was successful
    if (!$result) {
        // Handle database error
        dbError($xmlDoc, $recordDataNode, "Error inserting user into the database.");
    } else {
        // User successfully added, return user ID as token
        $statusNode = $xmlDoc->createElement('token', $user_id);
        $recordDataNode->appendChild($statusNode);
    }

    return $recordDataNode;
}

function getConnections($dbConn, $xmlDoc, $user_id, $type) {
    $recordDataNode = $xmlDoc->createElement('recorddata');

    $query = "SELECT * FROM tokens WHERE user_id = ? AND module_type = ?";
    $stmt = $dbConn->prepare($query);
    $stmt->bind_param("ss", $user_id, $type);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        $statusNode = $xmlDoc->createElement('status', 'Error occurred while fetching connections');
        dbError($xmlDoc, $recordDataNode, mysqli_error($dbConn));
    } else {
        $statusNode = $xmlDoc->createElement('status', 'success');
    }

    while ($row = $result->fetch_assoc()) {
        $theChildNode = $xmlDoc->createElement('connection');
        $theChildNode->setAttribute('name', $row['computer_name']);
        $theChildNode->setAttribute('token', $row['token']);

        $datetime1 = strtotime($row['last_seen']);
        $datetime2 = time();
        $diff = $datetime2 - $datetime1;
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

function getLogs($dbConn, $xmlDoc, $user_id) {
    // Main XML element to return
    $recordDataNode = $xmlDoc->createElement('recorddata');

    // Get users tokens and scanner names
    $query = "SELECT * FROM tokens WHERE user_id = ? AND module_type = '1'";
    $stmt = $dbConn->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
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

        // For each scanner, get last 10 visitors
        $query2 = "SELECT DISTINCT(ign) AS ign FROM logs WHERE token = ? ORDER BY timestamp DESC LIMIT 10";
        $stmt2 = $dbConn->prepare($query2);
        $stmt2->bind_param("s", $row['token']);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        while ($row2 = $result2->fetch_assoc()) {
            $visitorNode = $xmlDoc->createElement('visitor');
            $visitorNode->setAttribute('ign', $row2['ign']);
            $visitorNode->setAttribute('token', $row['token']);

            $query3 = "SELECT timestamp FROM logs WHERE token = ? AND ign = ? ORDER BY timestamp DESC LIMIT 1";
            $stmt3 = $dbConn->prepare($query3);
            $stmt3->bind_param("ss", $row['token'], $row2['ign']);
            $stmt3->execute();
            $result3 = $stmt3->get_result();
            $row3 = $result3->fetch_assoc();

            $visitorNode->setAttribute('last_seen', $row3['timestamp']);
            $theScannerNode->appendChild($visitorNode);
        }
        $recordDataNode->appendChild($theScannerNode);
    }

    return $recordDataNode;
}

function getPlayerData($dbConn, $xmlDoc, $ign, $token) {
    $recordDataNode = $xmlDoc->createElement('recorddata');

    $query = "SELECT * FROM logs WHERE token = ? AND ign = ? ORDER BY timestamp DESC LIMIT 50";
    $stmt = $dbConn->prepare($query);
    $stmt->bind_param("ss", $token, $ign);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $visitorNode = $xmlDoc->createElement('record');
        $visitorNode->setAttribute('ign', $row['ign']);
        $visitorNode->setAttribute('event', $row['event']);
        $visitorNode->setAttribute('time', $row['timestamp']);
        $visitorNode->setAttribute('discription', $row['discription']);
        $recordDataNode->appendChild($visitorNode);
    }

    return $recordDataNode;
}

function getUser($dbConn, $xmlDoc, $user_id) {
    $recordDataNode = $xmlDoc->createElement('recorddata');

    $query = "SELECT username FROM users WHERE user_id = ?";
    $stmt = $dbConn->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $userNode = $xmlDoc->createElement('user');
    $userNode->setAttribute('username', $row['username']);
    $recordDataNode->appendChild($userNode);

    $query = "UPDATE users SET last_seen = NOW() WHERE user_id = ?";
    $stmt = $dbConn->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    return $recordDataNode;
}

function getFluidLevels($dbConn, $xmlDoc, $user_id) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query = "SELECT * FROM tokens WHERE user_id = '".dbEsc($user_id)."' AND module_type = '3'";
	$result = mysqli_query($query);

	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$controlNode = $xmlDoc->createElement('modules');
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

		$query2 = "SELECT * FROM tanks WHERE token = '".$row['token']."'";
		$result2 = mysqli_query($query2);

		if (!($result2)) {
			$statusNode = $xmlDoc->createElement('status', $query);

			dbError($xmlDoc, $recordDataNode, mysql_error());
		} else {
			$statusNode = $xmlDoc->createElement('status', 'success');
		}

		$row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);

		$controlNode->setAttribute('tank_name', $row2['tank_name']);
		$controlNode->setAttribute('fluid_type', $row2['fluid_type']);
		$controlNode->setAttribute('percent', $row2['percent']);

		$recordDataNode->appendChild($controlNode);

	}
	$recordDataNode->appendChild($statusNode);
	return $recordDataNode;
}

function getEnergyLevels($dbConn, $xmlDoc, $user_id) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query = "SELECT * FROM tokens WHERE user_id = '".dbEsc($user_id)."' AND module_type = '2'";
	$result = mysqli_query($query);

	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$controlNode = $xmlDoc->createElement('modules');
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

		$query2 = "SELECT * FROM energy_storage WHERE token = '".$row['token']."'";
		$result2 = mysqli_query($query2);

		if (!($result2)) {
			$statusNode = $xmlDoc->createElement('status', $query);

			dbError($xmlDoc, $recordDataNode, mysql_error());
		} else {
			$statusNode = $xmlDoc->createElement('status', 'success');
		}

		$row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);

		$controlNode->setAttribute('bat_name', $row2['bat_name']);
		$controlNode->setAttribute('energy_type', $row2['energy_type']);
		$controlNode->setAttribute('percent', $row2['percent']);

		$recordDataNode->appendChild($controlNode);

	}
	$recordDataNode->appendChild($statusNode);
	return $recordDataNode;
}

function removeModule($dbConn, $xmlDoc, $token) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query2 = "DELETE FROM tokens WHERE token = '".dbEsc($token)."'";
	$result2 = mysqli_query($query2);

	if (!($result2)) {
			$statusNode = $xmlDoc->createElement('status', $query);

			dbError($xmlDoc, $recordDataNode, mysql_error());
		} else {
			$statusNode = $xmlDoc->createElement('status', 'success');
		}

	$recordDataNode->appendChild($statusNode);

	return $recordDataNode;
}

function removeEvent($dbConn, $xmlDoc, $event_id) {
	$recordDataNode = $xmlDoc->createElement('recorddata');

	$query2 = "DELETE FROM redstone_events WHERE event_id = '".dbEsc($event_id)."'";
	$result2 = mysqli_query($query2);

	if (!($result2)) {
			$statusNode = $xmlDoc->createElement('status', $query);

			dbError($xmlDoc, $recordDataNode, mysql_error());
		} else {
			$statusNode = $xmlDoc->createElement('status', 'success');
		}

	$recordDataNode->appendChild($statusNode);

	return $recordDataNode;
}

?>
