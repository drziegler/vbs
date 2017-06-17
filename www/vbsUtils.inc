<?php
// Defines for this file

$logPath = $_SERVER['DOCUMENT_ROOT'];
define("DEBUG" , false);
define("HOME_PAGE",		'register.php');
define("LOG_FILE",  	'./logs/vbs.log');
define("ERR_FILE", 		'./logs/err.log'); 
define("START_PAGE", 	"index.php");
define("REGISTER",		"Register");
define("UNREGISTER",	"Unregister");
define("STAFF_NURSERY",	"Staff Nursery");
define("VBS_EMAIL",		"vbs@hopecherryville.org");
define("WAITLIST_ONLY",	true);
$done = array("family_id" => 0, "family" => false, "student" => false, "volunteer" => false);

function formatPhone($rawPhone){
	$delimiter = "&#8209;";
	$fmtPhone = "";

	$sanitizedPhone = unformatPhone($rawPhone); 
	if (strlen($sanitizedPhone)===10){
	  $fmtPhone = substr($sanitizedPhone,0,3) . $delimiter . substr($sanitizedPhone, 3, 3) . $delimiter . substr($sanitizedPhone, 6);
	}
	return $fmtPhone;
}

function unformatPhone($rawPhone){

	$sanitizedPhone = preg_replace('/[^0-9]/', '', $rawPhone); 
	return $sanitizedPhone;
	
}
function insertStats($mysqli, $fam_id, $type){
	$STATS_COLS = array('family_id', 'media', 'user_agent', 'ip_addr', 'create_date');

	$statInsert  = 	'INSERT INTO stats (' . implode(', ', $STATS_COLS) . ', type) VALUES ' .
					'(' . $fam_id . ', ' . 
//					'"' . $general[MEDIA_TYPE] . '\', ' .
					'"Unknown",' .
					'"' . $mysqli->real_escape_string($_SERVER['HTTP_USER_AGENT']) . '", ' .
					'"' . $_SERVER['REMOTE_ADDR'] . '", ' .
					"CURRENT_TIMESTAMP, " .
					'"' . $type . '")';


	/* Keep a backup of the insert statement in the log file for research if necessary. */
	if (DEBUG) print $statInsert . "<br />";
	if (DEBUG) writeLog($statInsert) ;
	
	/* Now execute the statement */
	if ($mysqli->query($statInsert)===false){
		writeErr($statInsert, __FUNCTION__, __LINE__, $mysqli->error);
		return false;
	}else
	{
		if (DEBUG) print "Inserted 1 stat record sucessfully.<br />";
		writeLog("Inserted 1 stat record sucessfully.");
		return true;
	}
}
/*
  These are wrapper scripts for the write function.
  It provides the Log file parameter to write to.
*/
function writeLog($text2write){
	//if (DEBUG) print "Function: " . __FUNCTION__ . " Line: " . __LINE__ . "<br>";
	write($text2write, LOG_FILE);
}
function writeErr($text2write, $functionName, $lineNo, $errNo){
	//if (DEBUG) print "Function: " . __FUNCTION__ . " Line: " . __LINE__ . "<br>";
	$text2write = $functionName . "-Line:" . $lineNo . " ErrNo: " . $errNo . " " . $text2write;
	write($text2write, ERR_FILE);	
}
/*
  This formats and writes a string to the file as 
  argument 2.  It accepts input from the wrapper functions:
  writeLog and writeErr
*/
function write($text2write, $fileName){
	//if (DEBUG) print "Function: " . __FUNCTION__ . " Line: " . __LINE__ . "<br>";
	$dateTimeStamp = date('Y-m-d H:i:s');
	$text2write = $dateTimeStamp . " " . $text2write . "\n";
	$fHandle = fopen($fileName, 'a');
	$intWritten = fwrite($fHandle, $text2write);
	fclose($fHandle);	

}
function contains_substr($mainStr, $str, $loc = false) {
    if ($loc === false) return (strpos($mainStr, $str) !== false);
    if (strlen($mainStr) < strlen($str)) return false;
    if (($loc + strlen($str)) > strlen($mainStr)) return false;
    return (strcasecmp(substr($mainStr, $loc, strlen($str)), $str) == 0);
}

?>