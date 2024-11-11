<?php

require_once('main_functions.php');
require_once('connection.php');

// Toggle document type: XML or HTML
$usexml = isset($usexml) ? $usexml : 1;
$debugMode = false;

// Check HTTP_ACCEPT header to decide on XML usage
if ($usexml == 1 && isset($_SERVER['HTTP_ACCEPT'])) {
    $usexml = strpos($_SERVER['HTTP_ACCEPT'], "application/xhtml+xml") !== false ? 1 : 0;
}

// Set up action array
if ($_REQUEST && isset($_REQUEST['a'])) {
    $actionArray = explode(',', $_REQUEST['a']);
} else {
    $phpScript = $_SERVER['PHP_SELF'];
    $dieMessage = "No action specified. Exiting.<br><a href=\"$phpScript?a=options\">Options</a>";
    die('Die: ' . $dieMessage);
}

// Initialize XML document for output
$xmlDoc = new DOMDocument('1.0', 'UTF-8');
$xmlDoc->preserveWhiteSpace = false;
$xmlDoc->formatOutput = true;

$xmlRoot = $xmlDoc->createElement('xml_root');
$xmlDoc->appendChild($xmlRoot);

// Compose XML output if debug mode is enabled
if ($debugMode) {
    $debugInfo = $xmlDoc->createElement('debug_info');
    $xmlRoot->appendChild($debugInfo);

    $requestOptions = $xmlDoc->createElement('options');
    $debugInfo->appendChild($requestOptions);
    $requestOptions->appendChild($xmlDoc->createTextNode($_REQUEST['a']));
}

// Process each action in the request
foreach ($actionArray as $action) {
    switch ($action) {
        case "setuprecord":
            $xmlRoot->appendChild(setUpRecord($dbConn, $ldapConfig, $xmlDoc, $_REQUEST['username']));
            break;
        case "getPosts":
            $xmlRoot->appendChild(getPosts($dbConn, $xmlDoc, $_REQUEST['page'], $_REQUEST['sub'], $_REQUEST['user'], $_REQUEST['comments']));
            break;
        case "email":
            phpMailer($xmlDoc, $_REQUEST['name'], $_REQUEST['email'], $_REQUEST['subject'], $_REQUEST['msg']);
            break;
        case "doesUserExist":
            $xmlRoot->appendChild(doesUserExist($dbConn, $xmlDoc, $_REQUEST['id'], $_REQUEST['user_type']));
            break;
        case "addGoogleUser":
            $xmlRoot->appendChild(addGoogleUser($dbConn, $xmlDoc, $_REQUEST['id'], $_REQUEST['name'], $_REQUEST['email'], $_REQUEST['url']));
            break;
        case "addPost":
            $xmlRoot->appendChild(addPost($dbConn, $xmlDoc, $_REQUEST['id'], $_REQUEST['title'], $_REQUEST['text'], $_REQUEST['url'], $_REQUEST['sub']));
            break;
        case "userInfo":
            $xmlRoot->appendChild(userInfo($dbConn, $xmlDoc, $_REQUEST['id']));
            break;
        case "editUsername":
            $xmlRoot->appendChild(editUsername($dbConn, $xmlDoc, $_REQUEST['id'], $_REQUEST['username']));
            break;
        case "castVote":
            $xmlRoot->appendChild(castVote($dbConn, $xmlDoc, $_REQUEST['user_id'], $_REQUEST['post_id'], $_REQUEST['vote']));
            break;
        case "tallyVotes":
            $xmlRoot->appendChild(tallyVotes($dbConn, $xmlDoc, $_REQUEST['post_id']));
            break;
        case "checkForUserVote":
            $xmlRoot->appendChild(checkForUserVote($dbConn, $xmlDoc, $_REQUEST['post_id'], $_REQUEST['user_id']));
            break;
        case "addComment":
            $xmlRoot->appendChild(addComment($dbConn, $xmlDoc, $_REQUEST['user_id'], $_REQUEST['post_id'], $_REQUEST['comment']));
            break;
        case "getComments":
            $xmlRoot->appendChild(getComments($dbConn, $xmlDoc, $_REQUEST['post_id']));
            break;
        case "addNewUser":
            $xmlRoot->appendChild(addNewUser($dbConn, $xmlDoc, $_REQUEST['username'], $_REQUEST['password'], $_REQUEST['email']));
            break;
        case "signIn":
            $xmlRoot->appendChild(signIn($dbConn, $xmlDoc, $_REQUEST['username'], $_REQUEST['password']));
            break;
        case "getConnections":
            $xmlRoot->appendChild(getConnections($dbConn, $xmlDoc, $_REQUEST['user_id'], $_REQUEST['module_type']));
            break;
        case "logs":
            $xmlRoot->appendChild(getLogs($dbConn, $xmlDoc, $_REQUEST['user_id']));
            break;
        case "getPlayerData":
            $xmlRoot->appendChild(getPlayerData($dbConn, $xmlDoc, $_REQUEST['ign'], $_REQUEST['token']));
            break;
        case "getUser":
            $xmlRoot->appendChild(getUser($dbConn, $xmlDoc, $_REQUEST['user_id']));
            break;
        case "load_redstone_controls":
            $xmlRoot->appendChild(loadRedstoneControls($dbConn, $xmlDoc, $_REQUEST['user_id']));
            break;
        case "setRedstoneOutput":
            $xmlRoot->appendChild(setRedstoneOutput($dbConn, $xmlDoc, $_REQUEST['token'], $_REQUEST['side'], $_REQUEST['value'], $_REQUEST['val_type']));
            break;
        case "load_fluid_modules":
            $xmlRoot->appendChild(getFluidLevels($dbConn, $xmlDoc, $_REQUEST['user_id']));
            break;
        case "load_energy_modules":
            $xmlRoot->appendChild(getEnergyLevels($dbConn, $xmlDoc, $_REQUEST['user_id']));
            break;
        case "remove_module":
            $xmlRoot->appendChild(removeModule($dbConn, $xmlDoc, $_REQUEST['token']));
            break;
        case "redstone_event_dropdowns":
            $xmlRoot->appendChild(redstoneEventDropdowns($dbConn, $xmlDoc, $_REQUEST['user_id']));
            break;
        case "get_redstone_sides":
            $xmlRoot->appendChild(getRedstoneSides($dbConn, $xmlDoc, $_REQUEST['token']));
            break;
        case "create_redstone_event":
            $xmlRoot->appendChild(createRedstoneEvent($dbConn, $xmlDoc, $_REQUEST['storage_token'], $_REQUEST['redstone_token'], $_REQUEST['trigger_value'], $_REQUEST['side'], $_REQUEST['output_value'], $_REQUEST['event_type'], $_REQUEST['user_id']));
            break;
        case "load_redstone_events":
            $xmlRoot->appendChild(loadRedstoneEvents($dbConn, $xmlDoc, $_REQUEST['user_id']));
            break;
        case "remove_event":
            $xmlRoot->appendChild(removeEvent($dbConn, $xmlDoc, $_REQUEST['event_id']));
            break;
        default:
            // No action specified
            break;
    }
}

/**
 * Sets the content-type header based on XML or HTML preference.
 */
function sendxhtmlheader($usexml) {
    $contentType = $usexml == 1 ? "text/xml; charset=utf-8" : "text/html; charset=utf-8";
    header("Content-Type: $contentType");
}

/**
 * Sends the generated XML or HTML content to the client.
 */
function sendpage($page, $usexml) {
    sendxhtmlheader($usexml);
    if ($usexml == 0) {
        echo '<html><body><pre>' . htmlentities($page) . '</pre></body></html>';
    } else {
        echo trim($page);
    }
}

// Close database connection and send the XML document
mysqli_close($dbConn);
$xmlString = $xmlDoc->saveXML();
sendpage(trim($xmlString), $usexml);

?>
