<?php
error_reporting(E_ALL);

error_reporting(E_ALL | E_NOTICE | E_WARNING);

ini_set('display_errors', 1);

ini_set('log_errors', 1);

require_once('connection.php');


function doesUserExist($dbConn, $id, $type)
{
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
        // If the query is successful, check if any records were found
        $counter = mysqli_num_rows($result); // Get the count of rows

        // Set the response status and the count of records
        $response['status'] = 'success';
        $response['records'] = $counter;

        // Optionally add a message or data here if needed
        $response['message'] = $counter > 0 ? 'User exists.' : 'User not found.';
    }

    // Return the response as JSON
    return json_encode($response);
}

function addNewUser($dbConn, $username, $password, $email)
{
    // Initialize the response array inside the addNewUser object
    $response = [];

    // Sanitize inputs
    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);
    $email = htmlspecialchars($email);

    // Generate salt and hash the password
    $salt = rand() . rand() . rand() . rand();
    $hash = sha1($salt . $password);

    // Generate user_id
    $user_id = rand() . rand() . rand() . rand();

    // Prepare the SQL query with placeholders to avoid SQL injection
    $query = "INSERT INTO users (user_id, username, password, salt, email) 
              VALUES (?, ?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = mysqli_prepare($dbConn, $query)) {
        // Bind the parameters to the statement
        mysqli_stmt_bind_param($stmt, "sssss", $user_id, $username, $hash, $salt, $email);

        // Execute the query
        if (mysqli_stmt_execute($stmt)) {
            // Success: Return response with the user_id (logger_token) for login
            $response['status'] = 'success';
            $response['token'] = $user_id;
        } else {
            // Execution failed: Log the error and set message
            $response['status'] = 'error';
            $response['message'] = 'Query execution failed: ' . mysqli_stmt_error($stmt);
        }

        // Close the prepared statement
        mysqli_stmt_close($stmt);
    } else {
        // Prepare failed: Log the error and set message
        $response['status'] = 'error';
        $response['message'] = 'Query preparation failed: ' . mysqli_error($dbConn);
    }

    // Set the content type to JSON and output the response inside an addNewUser object

    return $response;
}

function signIn($dbconn, $username, $password)
{
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

    return $response;
}

function getUser($dbConn, $user_id)
{
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

function getConnections($dbConn, $user_id, $type)
{
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

function getReactorStatus($dbConn, $user_id)
{
    $response = array();

    // Query to get reactor status data using a JOIN to optimize performance
    $query = "
        SELECT t.computer_name, t.token, t.last_seen, rd.burn_rate, rd.coolant, rd.fuel_percentage, 
               rd.reactor_status, rd.max_burn_rate, rd.temperature, rd.waste, rd.coolant_percentage, 
               rd.waste_percentage, rd.fuel_capacity
        FROM tokens t
        LEFT JOIN reactor_controls rd ON t.token = rd.token
        WHERE t.user_id = ? AND t.module_type = '4'
    ";

    $stmt = mysqli_prepare($dbConn, $query);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $reactors = array();
    // Process each row in the result set
    while ($row = mysqli_fetch_assoc($result)) {
        $reactor = array(
            'name' => $row['computer_name'],
            'token' => $row['token'],
            'active' => (time() - strtotime($row['last_seen']) <= 100) ? true : false,
            'burn_rate' => $row['burn_rate'] ?? null,
            'coolant' => $row['coolant'] ?? null,
            'fuel_percentage' => $row['fuel_percentage'] ?? null,
            'status' => $row['status'] ?? null,
            'max_burn_rate' => $row['max_burn_rate'] ?? null,
            'temperature' => $row['temperature'] ?? null,
            'waste' => $row['waste'] ?? null,
            'coolant_percentage' => $row['coolant_percentage'] ?? null,
            'waste_percentage' => $row['waste_percentage'] ?? null,
            'fuel_capacity' => $row['fuel_capacity'] ?? null
        );

        $reactors[] = $reactor;
    }

    // Return the array of reactors (with reactor information)

    $response['status'] = 'success';

    return $reactors;
}

// Assuming you have included the database connection and other necessary setup

function getBurnRate($dbConn, $user_id)
{
    $response = array();

    // Query to get burn rates and associated reactor data using a JOIN
    $query = "SELECT burn_rate FROM reactor_controls WHERE token = ?";

    // Prepare the statement
    $stmt = mysqli_prepare($dbConn, $query);
    if ($stmt === false) {
        echo 'Error: Failed to prepare statement for burn rate retrieval.';
        error_log(mysqli_error($dbConn));  // Log the database error
        exit;
    }

    // Bind the user_id parameter to the statement
    mysqli_stmt_bind_param($stmt, "s", $user_id);

    // Execute the statement
    if (!mysqli_stmt_execute($stmt)) {
        echo 'Error: Query execution failed. ' . mysqli_error($dbConn);
        exit;
    }

    // Get the result
    $result = mysqli_stmt_get_result($stmt);

    $reactors = array();
    // Process each row in the result set
    while ($row = mysqli_fetch_assoc($result)) {
        $reactors[] = array(  // Populate the correct array
            'burn_rate' => $row['burn_rate']
        );
    }

    // Return the array of reactors (with burn rate information)
    return $reactors;
}

function updateBurnRate($dbConn, $user_id, $burnRate)
{
  
    // Validate inputs
    if (empty($user_id) || $burnRate === null || $burnRate < 0) {
        return [
            'success' => false,
            'error' => 'Invalid input. Token must not be empty, and burn rate must be a positive number.',
        ];
    }

    // Check database connection
    if ($dbConn === false) {
        return [
            'success' => false,
            'error' => 'Database connection failed.',
        ];
    }

    // Prepare the query to update burn rate
    $query = "UPDATE reactor_controls SET burn_rate = ? WHERE token = ?";
    $stmt = mysqli_prepare($dbConn, $query);

    if ($stmt === false) {
        error_log("Failed to prepare statement: " . mysqli_error($dbConn));
        return [
            'success' => false,
            'error' => 'Failed to prepare statement.',
        ];
    }

    // Bind parameters and execute
    mysqli_stmt_bind_param($stmt, "ds", $burnRate, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            mysqli_stmt_close($stmt);
            return [
                'success' => true,
                'message' => 'Burn rate updated successfully.',
            ];
        } else {
            mysqli_stmt_close($stmt);
            return [
                'success' => false,
                'error' => 'No reactor found with the given token.',
            ];
        }
    } else {
        error_log("Execution failed: " . mysqli_error($dbConn));
        mysqli_stmt_close($stmt);
        return [
            'success' => false,
            'error' => 'Failed to execute query.',
        ];
    }
}

function getEnergyLevels($dbConn, $user_id)
{
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

function getFluidLevels($dbConn, $user_id)
{
    $response = array();

    // Query to fetch tokens and their associated fluid tank data
    $query = "
        SELECT t.computer_name, t.token, t.last_seen, tk.tank_name, tk.fluid_type, tk.percent
        FROM tokens t
        LEFT JOIN tanks tk ON t.token = tk.token
        WHERE t.user_id = ? AND t.module_type = '2'
    ";

    $stmt = mysqli_prepare($dbConn, $query);
    if (!$stmt) {
        $response['status'] = 'error';
        $response['message'] = "Database query preparation failed: " . mysqli_error($dbConn);
        return json_encode($response);
    }

    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $modules = array();
    // Process each row in the result set
    while ($row = mysqli_fetch_assoc($result)) {
        $module = array(
            'name' => $row['computer_name'],
            'token' => $row['token'],
            'active' => (time() - strtotime($row['last_seen']) <= 200) ? 'true' : 'false',
            'tank_name' => $row['tank_name'] ?? null,
            'fluid_type' => $row['fluid_type'] ?? null,
            'percent' => $row['percent'] ?? null
        );

        $modules[] = $module;
    }

    // $response['recorddata'] = $modules;
    $response['status'] = 'success';

    return $modules;
}

function loadRedstoneControls($dbConn, $user_id)
{

    header('Content-Type: application/json');


    $user_id = dbEsc($dbConn, $user_id);


    $query = "SELECT t.computer_name, t.token, t.last_seen, r.*
              FROM tokens t
              LEFT JOIN redstone_controls r ON t.token = r.token
              WHERE t.user_id = '$user_id' AND t.module_type = '3'";


    $result = mysqli_query($dbConn, $query);

    if (!$result) {
        return json_encode(array(
            'status' => 'error',
            'message' => 'Database query failed: ' . mysqli_error($dbConn)
        ));
    }

    $controls = array();

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $control = array(
            'name' => $row['computer_name'],
            'token' => $row['token'],
            'active' => (time() - strtotime($row['last_seen']) <= 200) ? true : false,
            'top' => $row['top'],
            'bottom' => $row['bottom'],
            'front' => $row['front'],
            'back' => $row['back'],
            'left' => $row['left_side'],
            'right' => $row['right_side'],
            'top_name' => $row['top_name'],
            'bottom_name' => $row['bottom_name'],
            'front_name' => $row['front_name'],
            'back_name' => $row['back_name'],
            'left_name' => $row['left_name'],
            'right_name' => $row['right_name'],
            'top_input' => $row['top_input'],
            'bottom_input' => $row['bottom_input'],
            'front_input' => $row['front_input'],
            'back_input' => $row['back_input'],
            'left_input' => $row['left_input'],
            'right_input' => $row['right_input']
        );

        $controls[] = $control;
    }

    $response = array(
        'status' => 'success',
        'recorddata' => $controls
    );


    return json_encode($response);
}

function setRedstoneOutput($dbConn, $token, $side, $value, $type)
{
    $response = array();

    if ($type == 'string') {
        $value = htmlspecialchars($value);
        $query = "UPDATE redstone_controls SET " . dbEsc($dbConn, $side) . " = '" . dbEsc($dbConn, $value) . "' WHERE token = '" . dbEsc($dbConn, $token) . "'";
    } else {
        $query = "UPDATE redstone_controls SET " . dbEsc($dbConn, $side) . " = " . dbEsc($dbConn, $value) . " WHERE token = '" . dbEsc($dbConn, $token) . "'";
    }

    $result = mysqli_query($dbConn, $query);

    if ($result) {
        $response['status'] = 'success';
    } else {
        $response['status'] = 'error';
        $response['message'] = mysqli_error($dbConn);
    }

    return json_encode($response);

    header('Content-Type: application/json');
    echo json_encode($response);
}

function removeModule($dbConn, $token)
{
    $response = array();

    // Check if the connection is valid
    if (!$dbConn) {
        $response['status'] = 'error';
        $response['message'] = 'Database connection failed';
        return json_encode($response);
    }

    // Use prepared statements to prevent SQL injection
    $query = "DELETE FROM tokens WHERE token = ?";
    $stmt = mysqli_prepare($dbConn, $query);

    // Check if statement preparation failed
    if ($stmt === false) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to prepare query: ' . mysqli_error($dbConn);
        return json_encode($response);
    }

    // Bind parameters (s for string)
    mysqli_stmt_bind_param($stmt, 's', $token);

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        $response['status'] = 'success';
    } else {
        $response['status'] = 'error';
        $response['message'] = mysqli_stmt_error($stmt);
    }

    // Close statement and return the response
    mysqli_stmt_close($stmt);

    return json_encode($response);
}

function redstoneEventDropdowns($dbConn, $user_id)
{
    // Initialize the response array
    $response = [
        'storage_modules' => [],
        'redstone_modules' => []
    ];

    // Query for storage modules
    $query = "SELECT * FROM tokens WHERE user_id = '" . dbEsc($dbConn, $user_id) . "' AND (module_type = '1' OR module_type = '2')";
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

function getRedstoneSides($dbConn, $token)
{
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

function createRedstoneEvent($dbConn, $storageToken, $redstoneToken, $triggerValue, $side, $outputValue, $eventType, $user_id)
{
    // Initialize the response array
    $response = [];

    $query = "INSERT INTO redstone_events (redstone_token, storage_token, event_type, trigger_value, side, output, user_id) VALUES " .
        "('" . dbEsc($dbConn, $redstoneToken) . "', '" . dbEsc($dbConn, $storageToken) . "', " . dbEsc($dbConn, $eventType) . ", " . dbEsc($dbConn, $triggerValue) . ", '" . dbEsc($dbConn, $side) . "', " . dbEsc($dbConn, $outputValue) . ", '" . dbEsc($dbConn, $user_id) . "')";

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

function loadRedstoneEvents($dbConn, $user_id)
{
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
        $query2 = "SELECT computer_name, last_seen FROM tokens WHERE token = '" . dbEsc($dbConn, $row['redstone_token']) . "'";
        $result2 = mysqli_query($dbConn, $query2);
        $row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);
        $eventData['redstone_module'] = $row2['computer_name'];
        $eventData['redstone_active'] = (time() - strtotime($row2['last_seen'])) <= 200;

        // Get storage module details
        $query3 = "SELECT computer_name, last_seen FROM tokens WHERE token = '" . dbEsc($dbConn, $row['storage_token']) . "'";
        $result3 = mysqli_query($dbConn, $query3);
        $row3 = mysqli_fetch_array($result3, MYSQLI_ASSOC);
        $eventData['storage_module'] = $row3['computer_name'];
        $eventData['storage_active'] = (time() - strtotime($row3['last_seen'])) <= 200;

        $response['events'][] = $eventData;
    }

}

function removeEvent($dbConn, $event_id)
{
    $response = [];

    $query2 = "DELETE FROM redstone_events WHERE event_id = ?";
    $stmt = mysqli_prepare($dbConn, $query2);

    mysqli_stmt_bind_param($stmt, "s", $event_id);

    $result2 = mysqli_stmt_execute($stmt);

    // Check if the query was successful
    if (!$result2) {
        $response['status'] = 'error';
        $response['message'] = mysqli_error($dbConn);
    } else {
        $response['status'] = 'success';
    }


    header('Content-Type: application/json');
    echo json_encode($response);
}

function jsonResponse($status, $data)
{
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


function dbEsc($dbConn, $theString)
{
    $escapedString = mysqli_real_escape_string($dbConn, $theString);
    return $escapedString;
}

function dbError(&$xmlDoc, &$xmlNode, $theMessage)
{
    $errorNode = $xmlDoc->createElement('mysqlError', $theMessage);
    $xmlNode->appendChild($errorNode);
}
