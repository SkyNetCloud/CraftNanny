<?php

require_once('main_functions.php');
require_once('connection.php');

// Toggle document type: XML or JSON (for backward compatibility, you can keep the usexml flag)
$usejson = isset($_REQUEST['usejson']) ? $_REQUEST['usejson'] : 1;  // Default to JSON

// Check if 'usejson' is passed in the request to toggle between XML and JSON
if ($usejson == 1 && isset($_SERVER['HTTP_ACCEPT'])) {
    $usejson = strpos($_SERVER['HTTP_ACCEPT'], "application/json") !== false ? 1 : 0;
}

// Set up action array
if ($_REQUEST && isset($_REQUEST['a'])) {
    $actionArray = explode(',', $_REQUEST['a']);
} else {
    $phpScript = $_SERVER['PHP_SELF'];
    $dieMessage = "No action specified. Exiting.<br><a href=\"$phpScript?a=options\">Options</a>";
    die('Die: ' . $dieMessage);
}

// Initialize the response array for JSON output
$response = array();

// Process each action in the request
foreach ($actionArray as $action) {
    switch ($action) {
        case "doesUserExist":
            $response['doesUserExist'] = doesUserExist($dbConn, $_REQUEST['id'], $_REQUEST['user_type']);
            break;
        case "deleteUser":
            $response['deleteUser'] = deleteUser($dbConn, $_REQUEST['user_id']);
            break;
        case "addNewUser":
            $response['addNewUser'] = addNewUser($dbConn, $_REQUEST['username'], $_REQUEST['password'], $_REQUEST['email']);
            break;
        case "signIn":
            $response['signIn'] = signIn($dbConn, $_REQUEST['username'], $_REQUEST['password']);
            break;
        case "getConnections":
            $response['getConnections'] = getConnections($dbConn, $_REQUEST['user_id'], $_REQUEST['module_type']);
            break;
        case "getUser":
            $response['getUser'] = getUser($dbConn, $_REQUEST['user_id']);
            break;
        case "load_redstone_controls":
            $response['load_redstone_controls'] = loadRedstoneControls($dbConn, $_REQUEST['user_id']);
            break;
        case "load_fluid_modules":
            $response['load_fluid_modules'] = getFluidLevels($dbConn, $_REQUEST['user_id']);
            break;
        case "load_energy_modules":
            $response['load_energy_modules'] = getEnergyLevels($dbConn, $_REQUEST['user_id']);
            break;
        case "remove_module":
            $response['remove_module'] = removeModule($dbConn, $_REQUEST['token']);
            break;
        case "setRedstoneOutput":
            $response['setRedstoneOutput'] = setRedstoneOutput($dbConn, $_REQUEST['token'], $_REQUEST['side'], $_REQUEST['value'], $_REQUEST['val_type']);
            break;            
        case "redstone_event_dropdowns":
            $response['redstone_event_dropdowns'] = redstoneEventDropdowns($dbConn, $_REQUEST['user_id']);
            break;
        case "get_redstone_sides":
            $response['get_redstone_sides'] = getRedstoneSides($dbConn, $_REQUEST['token']);
            break;
        case "create_redstone_event":
            $response['create_redstone_event'] = createRedstoneEvent($dbConn, $_REQUEST['storage_token'], $_REQUEST['redstone_token'], $_REQUEST['trigger_value'], $_REQUEST['side'], $_REQUEST['output_value'], $_REQUEST['event_type'], $_REQUEST['user_id']);
            break;
        case "load_redstone_events":
            $response['load_redstone_events'] = loadRedstoneEvents($dbConn, $_REQUEST['user_id']);
            break;
        case "remove_event":
            $response['remove_event'] = removeEvent($dbConn, $_REQUEST['event_id']);
            break;
        default:
            // No action specified
            break;
    }
}

/**
 * Sets the content-type header to JSON.
 */
function sendjsonheader() {
    header("Content-Type: application/json; charset=utf-8");
}

/**
 * Sends the generated JSON content to the client.
 */
function sendpage($response) {
    sendjsonheader();
    echo json_encode($response, JSON_PRETTY_PRINT);
}

// Close database connection and send the JSON response
mysqli_close($dbConn);
sendpage($response);

?>
