<?php
// Defines for this file
define('FILE_NAME_UTIL',     '[VBSUTILS] '); 


define("DEBUG", 		FALSE);
define("SEND_TEXT",		TRUE);
define("SEND_ERROR_TEXT", TRUE);

/* Pagination constants */
define("HOME_PAGE",		'index.php');
define("SEARCH_PAGE",	'search.php');
define("FAMILY_PAGE",	'family.php');
define("CONTACT_PAGE",	'contacts.php');
define("STUDENT_PAGE",	'student.php');
define("STAFF_PAGE",	'staff.php');
define("STAFF_NURSERY_PAGE", 'staffNursery.php');
define("SUMMARY_PAGE",	'confirm.php');
define("CLEARANCE_PAGE",'clearances.php');
define("FINAL_PAGE",	'done.php');

/* Button Constants */
define("NEXT_PAGE",			"Next Page");
define("HOME_BUTTON",		"Home");
define("PREVIOUS_BUTTON",	"Previous Page");
define("NEW_BUTTON",		"New");
define("STAFF_NURSERY",		"Staff Nursery");

/* Record Navigation Constants */
define("FIRST_RECORD",		"First");
define("PREVIOUS_RECORD",	"Previous");
define("NEXT_RECORD", 		"Next");
define("LAST_RECORD",		"Last");

define("ADULT_VOLUNTEERS",  TRUE);
define("ALL_VOLUNTEERS",    FALSE);



//PROD
define("LOG_FILE",  	'./logs/vbs.log');
define("ERR_FILE", 	'./logs/vbs_error.log');
//DEV
//define("LOG_FILE",  	'C:/Users/David/git/vbs/www/vbs.log');
//define("ERR_FILE", 	'C:/Users/David/git/vbs/www/vbs_error.log'); 
define("START_PAGE", 	"index.php");
define("REGISTER",	"Register");
define("UNREGISTER",	"Unregister");


#Date constants
define("VBS_DATE",                          '2020-07-06');
define("VBS_DATE_MAX",                      '2016-07-01');
define("VBS_DATE_MIN",                      '2006-09-01');
define("VBS_SHIRT_DEADLINE",                '2020-06-14');
define("VBS_SHIRT_DEADLINE_MMDDYYYY",	    '06-14-2020');
define("VBS_MOM_ME_DEADLINE",               '2020-06-20');
define("VBS_MOM_ME_DEADLINE_MMDDYYYY",      '06-20-2020');

#Validation constants
define("NO_DUPES",		false);
define("WAITLIST_ONLY",	false);
//PROD
/*
define("VBS_EMAIL",		"vbs@hopecherryville.org");
define("VBS_TEXT",		"6102629198@vtext.com");
define("VBS_CLEARANCES_EMAIL","clearances@hopecherryville.org");
*/
//DEV

define("VBS_EMAIL",		"david@the-zieglers.com");
define("VBS_TEXT",		"6102629199@vtext.com");
define("VBS_CLEARANCES_EMAIL",  "david@the-zieglers.com");


function formatPhone($rawPhone){
	$delimiter = "&#45;";
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
/* This function sets all registered columns to "N" for the given family ID */
function cancelRegistration($famID){

	if (empty($famID)) return false;
		
	$sql="UPDATE STUDENTS SET REGISTERED = 'N' WHERE FAM_ID = $famID";
	if (!mysqli_query($vbsDBi, $sql)){
		writeErr("Unable to unregistered students", __FUNCTION__, __LINE__, 3001);
	}
	
	$sql="UPDATE STAFF SET REGISTERED = 'N' WHERE FAM_ID = $famID";
	if (!mysqli_query($vbsDBi, $sql)){
		writeErr("Unable to unregistered staff", __FUNCTION__, __LINE__, 3002);
	}
	
	return true;
}

function registrationCount(){
	global $vbsDBi;
	$regCount= 0;
	$sql="SELECT COUNT(*) AS c FROM students WHERE registered='C' and deleted=0";
	$rsResult=mysqli_query($vbsDBi, $sql);
	if ($rsResult){
		$rsCount = mysqli_fetch_assoc($rsResult);
		$regCount = $rsCount['c'];
	}
	else {  /* Return is FALSE.  Log the error */
	    writeLog(FILE_NAME_UTIL . __LINE__ . " rsResult is FALSE");
	    writeLog(FILE_NAME_UTIL . __LINE__ . " mySQLiErrno " . mysqli_errno($vbsDBi));
	    writeLog(FILE_NAME_UTIL . __LINE__ . " mySQLiError " . mysqli_error($vbsDBi));
	}
	return $regCount;
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
	if (DEBUG) writeLog(FILE_NAME_UTIL.__LINE__." $statInsert");
	
	/* Now execute the statement */
	if ($mysqli->query($statInsert)===false){
		writeErr($statInsert, FILE_NAME_UTIL, __LINE__, $mysqli->error);
		return false;
	}else
	{
		if (DEBUG) print "Inserted 1 stat record sucessfully.<br />";
		writeLog(FILE_NAME_UTIL.__LINE__." Inserted 1 stat record sucessfully.");
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
	$text2write = $functionName .  $lineNo . "-ErrNo: " . $errNo . " " . $text2write;
	write($text2write, ERR_FILE);	
}
/*
  This formats and writes a string to the file as 
  argument 2.  It accepts input from the wrapper functions:
  writeLog and writeErr
*/
function write($text2write, $fileName){
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