<?php
/* This function formats the email to the vbs mail box. */
/* Errors in this function as in the 2000 range */
session_start();
include_once('vbsUtils.inc');
require_once('Connections/vbsDB.php');
define("SUBMIT_REGISTRATION", "Submit Registration");
define("CANCEL_REGISTRATION", "Cancel");
define("FILE_NAME", '[CONFIRM] ');

$family = array();
$studentTotal = 0;
$staffTotal = 0;

if (empty($_SESSION['family_id'])){
	header("Location: " . HOME_PAGE);
}
/* This function changes registered 'Y' values into 'C' values for confirmed
 * This is not a logical value but it was too late into the process to change
 * the existing Y processes and retest everything.
 */
function confirm($famID){
	global $vbsDBi;
	$studentsConfirmed=true;
	$staffConfirmed=true;


	$sql="update students set registered='C' where registered='Y' and family_id=$famID";
	writeLog(FILE_NAME . __LINE__ .'  '.$sql);
	if (!mysqli_query($vbsDBi, $sql)){
		writeErr("Unable to confirm students for famID $famID.", __FUNCTION__, __LINE__, 2001);
		$studentsConfirmed=false;
	}

	$sql="update staff set registered='C' where registered='Y' and family_id=$famID";
	writeLog(FILE_NAME . __LINE__ .'  '.$sql);
	if (!mysqli_query($vbsDBi, $sql)){
		writeErr("Unable to confirm staff for famID $famID.", __FUNCTION__, __LINE__, 2002);
		$staffConfirmed=false;
	}
	return ($studentsConfirmed && $staffConfirmed);
}

function sendVBSmail($famID){
	global $studentTotal, $staffTotal, $family;

	$mailbody = "<html><head>";
	$mailbody .= getStyles();
	$mailbody .= "</head><body>";
	$mailbody .= "<h1>VBS registration Summary</h1><br />";
	$mailbody .= "Registration date: " . date("F dS, Y") . "<br /><br />";

	$mailbody .= formatFamily($famID) . "\r\n";
	$mailbody .= formatConfoPhone($famID) . "\r\n";
	$mailbody .= formatStudents($famID) ."\r\n";
	$mailbody .= formatStaff($famID) . "\r\n";
	$mailbody .= "</body></html>";			/* just so we're well formed ! */

	// Append lines to $mail_header that you wish to be added to the headers of the e-mail. (SMTP Format
	// with newline char ending each line)
	$email = getEmail($famID);
	$mail_header  = "From: " . $email['family_name'] . " <" . $email['email'] . ">\r\n";
	$mail_header .= "Reply-to: " .$email['family_name']." <".$email['email'].">\r\n";
	$mail_header .= "Content-Type: text/html; charset=utf-8\r\n";

	/* Temporarily set the php.ini file to the sendmail_from value specified here */
	ini_set("sendmail_from", "vbs@hopecherryville.org");


	$mail_status = mail("david@the-zieglers.com", 'VBS Registration', $mailbody, $mail_header);
//@@TEST	$mail_status = mail(VBS_EMAIL, 'VBS Registration', $mailbody, $mail_header);
	if(!$mail_status){
         writeLog(FILE_NAME . __LINE__ . "  Mail could not be sent due to an error while trying to send the mail.  Mail status is $mail_status");
	}

	/* HLR lookup api - to get mobil carrier name */

	if (SEND_TEXT){
		writeLog(FILE_NAME . __LINE__ . "  Sending text message to " . VBS_TEXT);
		$text_headers = 'From: vbs@hopecherryville.org' . "\r\n";
		$textMsg  = "New VBS registration for " . trim($family['family_name']) . " for $studentTotal students and $staffTotal volunteers\r\n";
		$mail_status = mail(VBS_TEXT, '', $textMsg, $text_headers);
		writeLog(FILE_NAME . __LINE__ . "  Text mail status is " . $mail_status );
	}

}

/* This function generates the confirmation email back to the requester */
function sendConfo($famID){

	$mailbody = "<html><head>";
	$mailbody .= getStyles();
	$mailbody .= "</head><body>";
	$mailbody .= "<h1>VBS registration confirmation.</h1><br />";
	$mailbody .= "Registration date: " . date("F dS, Y") . "<br /><br />";

	$mailbody .="Thank you for registering for VBS " . date("Y") . ". The following information has been sent to the VBS office.";
	$mailbody .=" If any of the information is not correct, please <a href=\"mailto:vbs@hopecherryville.org\">email us</a> so we may adjust our ";
	$mailbody .="records.<br><br>";
	$mailbody .= formatFamily($famID) . "\r\n";
	$mailbody .= formatStudents($famID) ."\r\n";
	$mailbody .= formatStaff($famID) . "\r\n";
	$mailbody .= "</body></html>";			/* just so we're well formed ! */

	// Append lines to $mail_header that you wish to be added to the headers of the e-mail. (SMTP Format
	// with newline char ending each line)
	$headers = array();
	$headers[] = "MIME-Version: 1.0";
	$headers[] = "From: VBS Registration Office <" . VBS_EMAIL . ">";
	$headers[] = "Reply-to: VBS Registration Office <" . VBS_EMAIL . ">";
	$headers[] = "Content-Type: text/html; charset=utf-8";
	$headers[] = "Subject: VBS Confirmation";

	$email = getEmail($famID);

	writeLog(FILE_NAME . __LINE__ . " Sent to " . $email['family_name'] . " at " . $email['email']);
	$sendTo = $email['family_name'] . "<" . $email['email'] . ">";

	$mail_status = mail($sendTo, "VBS Confirmation", $mailbody, implode("\r\n", $headers));
	if(!$mail_status){
         writeErr(FILE_NAME . "Failed to send email to " . $sendTo . " on " . date("F dS, Y"));
	}
	else{
		writeLog(FILE_NAME . __LINE__ . " Sent confirmation email to " . $email['family_name'] . " at " . $email['email']);
	}
}

/* This function generates email to the clearance coordinator for follow up with the registrant(s) */
function sendClearanceMail($famID){
    
    $mailbody = "<html><head>";
    $mailbody .= getStyles();
    $mailbody .= "</head><body>";
    $mailbody .= "<h1>VBS Adult Volunteer Registration Confirmation.</h1><br />";
    $mailbody .= "Registration date: " . date("F dS, Y") . "<br><br>";
    
    $mailbody .="The following have registered as adult volunteers for VBS " . date("Y") . ". The following information has been sent to the VBS office.";
    $mailbody .="<br><br>";
    $mailbody .= formatFamily($famID) . "\r\n";
    $mailbody .= formatConfoPhone($famID) . "\r\n";
    $mailbody .= formatStaff($famID) . "\r\n";
    $mailbody .= "</body></html>";			/* just so we're well formed ! */
    
    // Append lines to $mail_header that you wish to be added to the headers of the e-mail. (SMTP Format
    // with newline char ending each line)
    $headers = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "From: VBS Office <" . VBS_EMAIL . ">";
    $headers[] = "Reply-to: VBS Office <" . VBS_EMAIL . ">";
    $headers[] = "Content-Type: text/html; charset=utf-8";
    $headers[] = "Subject: VBS Adult Volunteer Registration";
    
    $email = getEmail($famID);
    
    writeLog(FILE_NAME . __LINE__ . ' Sent to Clearance Coordinator at clearances@hopecherryville.org');
    $sendTo = "Clearance Coordinator <" . CLEARANCES_EMAIL . ">";
    
    $mail_status = mail($sendTo, "VBS Adult Volunteer Registration", $mailbody, implode("\r\n", $headers));
    if(!$mail_status){
        writeErr(FILE_NAME . "Failed to send email to " . $sendTo . " on " . date("F dS, Y"));
    }
    else{
        writeLog("Sent confirmation email to " . $email['family_name'] . " at " . $email['email']);
    }
}


function formatFamily($famID){
	global $vbsDBi, $family;
	$sql = $ph = $fam = "";

	$sql = "select * from family fam left join zipcodes zip on LEFT(fam.zipcode, 5)=zip.zipcode where family_id=" . $famID;
	$result = mysqli_query($vbsDBi, $sql);
	if ($result) {
		$family = mysqli_fetch_assoc($result);

		// Add the family data to the email body
		$fam = '<div id="Family">';
		$fam .= '<h2 style="margin:0">Family Information</h2>';
		$fam .= '<table cellspace="0" class="confo">';
		$fam .= '<tr><td class="label">Family Name:</td><td class="value">' . $family['family_name'] . "</td></tr>";
		$fam .= '<tr><td class="label">Address:</td><td class="value">' . $family['address'] . "</td></tr>";
		$fam .= '<tr><td class="label">City State Zip:</td><td class="value">' . $family['city'] . " " . $family['state'] . " " . $family['zipcode'] . "</td></tr>";
		$fam .= '<tr><td class="label">Email:</td><td class="value">'.$family['email']."</a></td></tr>";
		$fam .= "<tr><td class='label'>Home Church:</td><td class='value'>" . $family['home_church'] . "</td></tr>";
		if (strlen(trim($family['comments']))>0){
			$fam .= "<tr><td class='label'>Comments:</td><td>".$family["comments"]."</td></tr>";
		}
		$fam .= "</table></div><br><br>";
	}
	else {
		$sqlErr = mysqli_error($vbsDBi);
		writeErr("No family results for " . $famID, __FUNCTION__, __LINE__, $sqlErr);
	}

	//@mysqli_free_result($family);

	return $fam;
}

function formatConfoPhone($famID){
global $vbsDBi;

	$sql = "SELECT contact_name, phone FROM phone_numbers WHERE family_id=" . $famID . " ORDER BY contact_name";
	$result = mysqli_query($vbsDBi, $sql);
	if ($result===false){
		$sqlErr = mysqli_error($vbsDBi);
		writeErr(FILE_NAME . "No phone contacts for " . $famID, __FUNCTION__, __LINE__, $sqlErr);}
	else {
		$phone = mysqli_fetch_assoc($result);
		$ph = '<div id="Phone">';
		$ph .= "<h2>Phone Contacts</h2>";
		$ph .= '<table class="confo" cellspacing="0">';
		$ph .= '<tr><th>Contact Name</th><th>Telephone Number</th></tr>';
		do {
			$ph .= "<tr><td>" . $phone['contact_name'] . "</td>";
			$ph .= "<td>" . formatPhone($phone['phone']) . "</td></tr>";
		} while ($phone = mysqli_fetch_assoc($result));
		$ph .= "</table></div>";
	}
	@mysqli_free_result($phone);

	return $ph;

}

/* If includeID is false, then this is a confirmation for the requester and we suppress the ID field(s). */
function formatStudents($famID){
global $vbsDBi, $studentTotal;

	$sql = "SELECT CONCAT(first_name, ' ',last_name) as name, birthdate, class, shirt_size, picture, buddy, comments, confo, last_name, first_name
			FROM students WHERE (registered='Y' or registered='C') and family_id=" . $famID . " ORDER BY last_name, first_name";
	$result = mysqli_query($vbsDBi, $sql);

	if ($result===false){
		$sqlErr = mysqli_error($vbsDBi);
		writeErr("No student results", __FUNCTION__, __LINE__, $sqlErr);}
	else {
		$s = mysqli_fetch_assoc($result);
		$stud = '<div id="Student">';
		$stud .= "<h2>Student Information</h2>";
		$stud .= '<table cellspacing="0">';

		if (! is_null($s)){
			$stud .= "<tr><th>Name</th><th>Birthdate</th><th>Picture</th>";
			//20180402-removed confo no. $stud .= "<th>Class</th><th>T-Shirt</th><th>Friend Request</th><th>Comments</th><th>Conf #</th></tr>";
			$stud .= "<th>Class</th><th>T-Shirt</th><th>Friend Request</th><th>Comments</th></tr>";
			$stud .= "<tr>";
			do {
				$stud .= "<td class='nowrap'>" . $s['name'] . "</td>";
				$stud .= "<td class='nowrap centerText'>" . $s['birthdate'] . "</td>";
				$stud .= "<td class='centerText'>" . (($s['picture']=='Y')?"Yes":"No") . "</td>";
				$stud .= "<td class='centerText'>" . $s['class'] . "</td>";
				$stud .= "<td class='centerText'>" . $s['shirt_size'] . "</td>";
				$stud .= "<td class='centerText'>" . $s['buddy'] . "</td>";
				$stud .= "<td>" . $s['comments'] . "</td>";
				//20180402-removed confo no.$stud .= "<td>" . $s['confo'] . "</td>";
				$stud .= "</tr>";
				$studentTotal++;
			} while ($s = mysqli_fetch_assoc($result));
		}
		else{
			$stud .= "<tr><td class='center'>No students registered</td></tr>";
			$studentTotal = 0;
		}
		$stud .= "</table>";
		$stud .= "</div>";
	}

	@mysqli_free_result($s);
	return $stud;
}


function formatStaff($famID){
global $vbsDBi, $staffTotal;

	$sql = "SELECT CONCAT(first_name, ' ',last_name) as name, picture, mon, tue, wed, thur, fri, kitchen, craft, classroom, anything,
			teach_with, shirt_size, age_group, confo, comments, last_name, first_name
			FROM staff WHERE (registered='Y' or registered='C') and family_id=" . $famID . " ORDER BY last_name, first_name";
	$result = mysqli_query($vbsDBi, $sql);
	if ($result===false){
		$sqlErr = mysqli_error($vbsDBi);
		writeErr("No volunteer results", __FUNCTION__, __LINE__, $sqlErr);}
	else {
		$s = mysqli_fetch_assoc($result);

		$stf = '<div id="Staff">';
		$stf .= "<h2>Volunteer Information</h2>";
		$stf .= '<table cellspacing="0">';

		if (! is_null($s)){
			/* Add the STAFF SECTION */
			$stf .= "<tr>";
			$stf .= "<th>Name</th>";
			$stf .= "<th>M</th><th>T</th><th>W</th><th>Th</th><th>F</th>";
			$stf .= "<th>Class</th><th>Craft</th><th>Kitchen</th><th>Any</th>";
			$stf .= "<th>Picture</th>";
			$stf .= "<th>T-Shirt</th><th>Teach with</th>";
			//20180402-removed confo no. $stf .= "<th>Age Group</th><th>Conf #</th></tr>";
			$stf .= "<th>Clearance Required</th></tr>";

			do {
				$stf .= "<tr>";
				$stf .= "<td>".trim($s['name'])."</td>";
				$stf .= "<td class='centerText'>" . $s['mon'] . "</td>";
				$stf .= "<td class='centerText'>" . $s['tue'] . "</td>";
				$stf .= "<td class='centerText'>" . $s['wed'] . "</td>";
				$stf .= "<td class='centerText'>" . $s['thur'] . "</td>";
				$stf .= "<td class='centerText'>" . $s['fri'] . "</td>";
				$stf .= "<td class='centerText'>" . $s['classroom'] . "</td>";
				$stf .= "<td class='centerText'>" . $s['craft'] . "</td>";
				$stf .= "<td class='centerText'>" . $s['kitchen'] . "</td>";
				$stf .= "<td class='centerText'>" . $s['anything'] . "</td>";
				$stf .= "<td class='centerText'>" . $s['picture'] . "</td>";
				$stf .= "<td class='centerText'>" . $s['shirt_size'] . "</td>";
				$stf .= "<td class='centerText'>" . $s['teach_with'] . "</td>";
				$stf .= "<td class='centerText'>" . ($s['age_group']=='Adult'?'Y':'N') . "</td>";
				//$stf .= "<td>" . $s['confo'] . "</td>";
				$stf .= "</tr>";
				$staffTotal++;
			} while ($s = mysqli_fetch_assoc($result));
		}
		else {
			$stf .= "<tr><td class='center'>No volunteers registered</td></tr>";
			$staffTotal=0;
		}
		$stf .= "</table></div>";
	}

	return $stf;
}

function getStyles(){
	$style  = "<style> ";
	$style .= "table {border-collapse:collapse; border: .25em solid;} td{padding: .25em; border:2px solid;}";
	$style .= "#Family table{border-color:red;} ";
	$style .= "#Family td:nth-child(1) {padding-left:2em; text-align:right; font-weight:bolder;} ";
	$style .= "#Family td:nth-child(2) {padding-left:1em;} ";
	$style .= "#Phone table{border-color: rgba(0,0,255,0.25)} th{background-color: rgba(0,0,255,0.25);} ";
	$style .= "#Phone tr:nth-child(odd) {background-color:#80FFFF;} #Staff tr:nth-child(even) {background-color:#CAFFFF;} ";
	$style .= "#Student table {border-color: green;} #Student td {border-color:green;padding:1ex;} ";
	$style .= "#Student th {color:white;background-color:green;} ";
	$style .= "#Student tr:nth-child(odd) {background-color:#80FF80;} #Student tr:nth-child(even) {background-color:#CAFFCA;} ";
	$style .= "#Staff table, td{border-color:rgb(153,51,153);}";
	$style .= "#Staff th{color:white;background-color:rgb(153,51,153);} ";
	$style .= "#Staff tr:nth-child(odd) {background-color:rgb(230,179,230);} #Staff tr:nth-child(even) {background-color:rgb(230,179,230);} ";
	$style .= ".centerText{text-align:center;} ";
	$style .= "</style>";
	return $style;
}

function getEmail($famID){
	global $vbsDBi;

	$sql = "select family_name, email from family where family_id=" . $famID;
	$result = mysqli_query($vbsDBi, $sql);
	$family = mysqli_fetch_assoc($result);
	return $family;
}

function getFamilyErrors(){
	global $vbsDBi;
	$errMsg = "";
	$notEmpty = Array('family_name'=>'Family Name', 'email'=>'Email', 'address'=>'Address', 'zipcode'=>'Zipcode', 'home_church'=>'Home Church');

	/* Pull the family record */
	$sql = "SELECT family_name, email, address, zipcode, home_church from family where family_id=" . $_SESSION['family_id'];
	$rsResult = mysqli_query($vbsDBi, $sql);
	if ($rsResult){
		$rsFamily = mysqli_fetch_assoc($rsResult);
		$mandatory = array_intersect_key($notEmpty, $rsFamily);

		foreach($mandatory as $key=>$value){
			if (strlen(trim($value))===0){
				$error .= $value . ",";
			}
		}
	}
	else {
		if (DEBUG) print "Line " . __LINE__ . "-" . __FUNCTION__ . "<br>";
		$sqlErr = mysqli_error($vbsDBi);
		writeErr("Error selecting family name", __FUNCTION__, __LINE__, $sqlErr);
	}
	@mysqli_free_result($rsResult);

	$errMsg = trim($errMsg, ',');
	$errMsg = ((strlen($errMsg)>0) ? " cannot be blank.":"");
	return $errMsg;
}

function getStudentErrors(){
	global $vbsDBi;
	$errMsg = "";
	$notEmpty = Array('first_name'=>'First Name', 'last_name'=>'Last Name', 'birthdate'=>'Birthdate', 'class'=>'Class', 'shirt_size'=>'Shirt Size', 'picture'=>'Picture opt out');

	/* Pull the family record */
	$sql = "SELECT first_name, last_name, birthdate, class, shirt_size, picture from students where family_id=" . $_SESSION['family_id'];
	$rsResult = mysqli_query($vbsDBi, $sql);
	$students = mysqli_fetch_all($rsResult, MYSQLI_ASSOC);
	foreach ($students as $sKey=>$sValue){
		$mandatory = array_intersect_key($notEmpty, $students);

		foreach($mandatory as $key=>$value){
			if (strlen(trim($value))===0){
				$error .= $value . ",";
			}
		}
	}
	@mysqli_free_result($rsStudent);

	$errMsg = trim($errMsg, ',');
	$errMsg = ((strlen($errMsg)>0) ? " cannot be blank.":"");
	return $errMsg;
}

function getStaffErrors(){
	global $vbsDBi;
	$errMsg = "";
	$notEmpty = Array('first_name'=>'First Name', 'last_name'=>'Last Name', 'classroom'=>'Classroom', 'kitchen'=>'Kitchen', 'shirt_size'=>'Shirt Size', 'picture'=>'Picture opt out',
					  'craft'=>'Craft','anything'=>'Anything','mon'=>'Monday','tue'=>'Tuesday','wed'=>'Wednesday','thur'=>'Thursday','fri'=>'Friday','age_group'=>'Over 21?');

	/* Pull the family record */
	$sql = "SELECT first_name, last_name, classroom, shirt_size, picture, kitchen, anything, craft, mon, tue, wed, thur, fri,
			age_group from staff where family_id=" . $_SESSION['family_id'];
	$rsResult = mysqli_query($vbsDBi, $sql);
	$staff = mysqli_fetch_all($rsResult, MYSQLI_ASSOC);
	foreach ($staff as $sKey=>$sValue){
		$mandatory = array_intersect_key($notEmpty, $staff);

		foreach($mandatory as $key=>$value){
			if (strlen(trim($value))===0){
				$error .= $value . ",";
			}
		}
	}
	@mysqli_free_result($staff);

	$errMsg = trim($errMsg, ',');
	$errMsg = ((strlen($errMsg)>0) ? " cannot be blank.":"");
	return $errMsg;
}

/*******  PHONE RULES ************************************
1. Must have at least two different numbers

return: an unformatted string containing an error message.
**********************************************************/
function getPhoneErrors(){
	global $vbsDBi;
	$errMsg = "";

	$sqlPhoneCount = "SELECT count(distinct phone) as c from phone_numbers where family_id=" . $_SESSION['family_id'];

	$result = $vbsDBi->query($sqlPhoneCount);
	$phoneCount = $result->fetch_object()->c;
	$result->free();

	if ($phoneCount<2){
		$errMsg = "At least two phone contact numbers are required.";
	}
	
	return $errMsg;
}

/****************************************************************************
	Returns the number of registered staff members for this current family id
    NEEDING_CLEARANCES returns only count of volunteers over 18.
    ALL_VOLUNEETERS returns count of all volunteers regardles of age.	
*****************************************************************************/
function getStaffCount($volunteerType){
    global $vbsDBi;
	
    $sql = "SELECT count(*) AS count from staff where registered = 'C' and family_id = " . $_SESSION['family_id'];
    if ($volunteerType==ADULT_VOLUNTEERS) {
        $sql .= " and age_group='Adult'";
    }
    $result = mysqli_query($vbsDBi, $sql);
    $staffCount = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $staffCount['count'];
}

/***********************************************************************
	Returns the number of registered students for this current family id
************************************************************************/
function getStudentCount($famID){
	$sql = "SELECT count(*) as c from students where registered = 'Y' and family_id = " . $famID;
	$result = mysqli_query($vbsDBi, $sql);
	$registered = mysqli_fetch_assoc($result);
	mysqli_free_result($result);
	return $registered[0];
}

/**************************************************** M A I N *********************/

/*************************************************************************
 If the page was re-submitted to send an email, the REQUEST submit object
 will not be empty.  Jump to the else.
 If the SUBMIT object is empty, then display the validation screen.
 *************************************************************************/
if (isset($_POST['submit'])) {
	switch($_POST['submit']) {
		case HOME_BUTTON :
			header("Location: " . HOME_PAGE);
			break;
		case PREVIOUS_BUTTON :
			header("Location: " . STAFF_PAGE);
			break;
		case SUBMIT_REGISTRATION :
			if (confirm($_SESSION['family_id'])){
				//@@sendConfo($_SESSION['family_id']);
				//@@writeLog("Sent confirmation email to " . getEmail($_SESSION['family_id'])['family_name']);
				
			    sendVBSMail($_SESSION['family_id']);
			    writeLog(FILE_NAME.__LINE__." Sent VBS confirmation mail to VBS office.");
				if (getStaffCount(ADULT_VOLUNTEERS)>0){
				    writelog(FILE_NAME.__LINE__." Sending clearance email notification.");
				    sendClearanceMail($_SESSION['family_id']);
				    header("Location: " . CLEARANCE_PAGE);
				}
				else {
				    header("Location: " . FINAL_PAGE);
				}
				break;
			}
			else
			{
				/* Send error notice to vbs mailbox */
				writeErr("Confirm error", "confirm", __LINE__, 2003);
				header("Location: " . HOME_PAGE);
				break;
			}
	}
}
else
{
//if (empty($_REQUEST['submit'])){

	/************************************************************
	 Here we perform validations section by section.  If we find
	 an error, we stop and go no further, redirect to the page
	 with the error and wait for the user to correct and resubmit
	 ************************************************************/
	$err = getFamilyErrors();
	if (strlen(trim($err))>0) header("Location: " . FAMILY_PAGE);
	$err = "";


	$err = getStudentErrors();
	if (strlen(trim($err))>0) header("location: " . STUDENT_PAGE);
	$err = "";
}
if (DEBUG) print "Total students = $studentTotal.  Total staff = $staffTotal.<br>";

?>
    <!doctype html>
    <!--[if lt IE 7]> <html class="ie6 oldie"> <![endif]-->
    <!--[if IE 7]>    <html class="ie7 oldie"> <![endif]-->
    <!--[if IE 8]>    <html class="ie8 oldie"> <![endif]-->
    <!--[if gt IE 8]><!-->
    <html class="">
    <!--<![endif]-->
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VBS Confirmation</title>
    <link href="css/boilerplate.css" rel="stylesheet" type="text/css">
    <link href="css/layout.css" rel="stylesheet" type="text/css">
    </head>
    <body>
    <div id="Confirm" class="gridContainer">
    <div><h2>VBS-Registration Summary</h2></div>
    <?php
        echo formatFamily($_SESSION['family_id']);
        //echo formatConfoPhone($_SESSION['family_id']);
        echo formatStudents($_SESSION['family_id']);
        echo formatStaff($_SESSION['family_id']);
    ?>
    <p>&nbsp;</p>
    <div id="buttonGroup" class="center">
		<form method="post" name="frmStudent" target="_self" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" style="display:inline-block">
			<input type="submit" name="submit" class="button" value="<?php echo HOME_BUTTON?>">&nbsp;
			<input type="submit" name="submit" class="button" value="<?php echo SUBMIT_REGISTRATION;?>" <?php echo (($studentTotal+$staffTotal==0)?'disabled':'');?>>&nbsp;&nbsp;&nbsp;
			<input type="submit" name="submit" class="button" value="<?php echo PREVIOUS_BUTTON?>">&nbsp;
		</form>
    </div>
    </div>
    </body>
    </html>
