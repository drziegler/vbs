<?php 
session_start();
require_once('Connections/vbsDB.php');
include('vbsUtils.inc');

if (empty($_SESSION['family_id'])){
	/* Send the user to the search page if no family is selected */
	header("Location: search.php");
}

function quickSave(){
	global $vbsDBi;
	if (DEBUG) print "Quick Save<br>";
	if (empty($_POST['staff_id'])) return;
	
	/* Note:  For all Inserts and Updates ...
			  All Checkboxes must use isset to determine state and explicitly set Y | N
	          All Radiobuttons must use isset to determine state and use variable content or N
			  This is to avoid resetting the buttons' state back to that from the data base 
	*/
	$sqlUpdate = "UPDATE staff SET ";
	$sqlUpdate .= "first_name='"  . ((strlen(trim($_POST['first_name']))>0) ? mysqli_real_escape_string($vbsDBi,trim($_POST['first_name']))  : "") . "'";
	$sqlUpdate .= ",last_name='"  . ((strlen(trim($_POST['last_name']))>0) ? mysqli_real_escape_string($vbsDBi,trim($_POST['last_name']))  : "") . "'";
	$sqlUpdate .= ",teach_with='" . ((strlen(trim($_POST['teach_with']))>0) ? mysqli_real_escape_string($vbsDBi,trim($_POST['teach_with']))  : "") . "'";
	$sqlUpdate .= ",comments='"   . ((strlen(trim($_POST['comments']))>0) ? mysqli_real_escape_string($vbsDBi,trim($_POST['comments']))  : "") . "'";
	$sqlUpdate .= ",mon ='" 	  .	(isset($_POST['mon'])  ? 'Y' : 'N') .  "'";
	$sqlUpdate .= ",tue ='" 	  . (isset($_POST['tue'])  ? 'Y' : 'N') .  "'";
	$sqlUpdate .= ",wed ='" 	  . (isset($_POST['wed'])  ? 'Y' : 'N') .  "'";
	$sqlUpdate .= ",thur='" 	  . (isset($_POST['thur']) ? 'Y' : 'N') . "'";
	$sqlUpdate .= ",fri ='" 	  . (isset($_POST['fri'])  ? 'Y' : 'N') .  "'";
	$sqlUpdate .= ",classroom ='" . (isset($_POST['classroom']) ? 'Y' : 'N') ."'";
	$sqlUpdate .= ",nursery ='"   . (isset($_POST['nursery'])  ? 'Y' : 'N') ."'";
	$sqlUpdate .= ",craft ='"     . (isset($_POST['craft'])     ? 'Y' : 'N') ."'";
	$sqlUpdate .= ",kitchen ='"   . (isset($_POST['kitchen'])   ? 'Y' : 'N') ."'";
	$sqlUpdate .= ",anything ='"  . (isset($_POST['anything'])  ? 'Y' : 'N') ."'";
	$sqlUpdate .= ",picture='"    . (isset($_POST['picture']) ? $_POST['picture'] : 'N') . "'";
	$sqlUpdate .= ",registered='" . (isset($_POST['registered']) ? $_POST['registered'] : 'N') . "'";
	$sqlUpdate .= ",shirt_size='" . (isset($_POST['shirt_size']) ? $_POST['shirt_size'] : '') . "'" ;
	$sqlUpdate .= ",age_group='"  . (isset($_POST['age_group'])  ? $_POST['age_group'] : '') . "'";
	//if (isset($_POST['shirt_size']) && !$_POST['shirt_size']=="Select size") $sqlUpdate .= ",shirt_size='" . $_POST['shirt_size'] . "'";
	$sqlUpdate .= ",confo='" . $_SESSION['confoNo'] . "'";
	$sqlUpdate .= ",last_update=now() ";
	$sqlUpdate .= " WHERE staff_id = " . $_POST['staff_id'];
	mysqli_real_escape_string($vbsDBi, $sqlUpdate);

	if (mysqli_query($vbsDBi, $sqlUpdate)){
		if (DEBUG) print "Line " . __LINE__ . "-Updated Staff record ".$_POST['staff_id']."<br>";
		writeLog($sqlUpdate);
	}
	else {
		if (DEBUG) print "Line " . __LINE__ . "Update error in Quick save.  See log file.<br>";
		$sqlErr = mysqli_error($vbsDBi);
		writeErr("Error:", "Staff:QuickSave", __LINE__, $sqlErr);
		writeErr("SQL Statement:", __FUNCTION__, __LINE__, $sqlUpdate);
	}

	return;
	
}


function validate($form){
	/* 	Validate the form.
		Return true if the form passed validation and is ready to save.
		Return false if the form failed validation and cannot be saved.
	*/
	$error = false;
	global $errMsgText;
	$errMsg = "";		/* clear out any previous messages */

	/* First check if the registration flag is on.  If this person is not registering this year,
	 * don't both validating the form and frustrating the user.
	 */
	if (DEBUG) print_r($form);
	if (DEBUG) print "<br>";
	if (isset($form['registered']) && $form['registered'] == 'N') return true;
	
	/* Mandatory form elements */	
	$mustExist  = array('picture'=>'Picture','age_group'=>'Over 18','registered'=>'Helping at VBS');
	$notBlank   = array('first_name'=>'First Name', 'last_name'=>'Last Name');
	$selectedLists  = array('shirt_size'=>'Shirt size');

	/* Remove leading and trailing spaces */
	foreach ($form as $key => $value){
		$form[$key] = trim($value);
	}
	/* Assign trim changes back to the $_POST array */
	$_POST = $form;

	/* Check for blank elements */
	$blanks = array_intersect_key($form, $notBlank);
	foreach ($blanks as $key => $value){
		if (strlen($value)===0){
			$errMsg .= $notBlank[$key] . ",";
			$error = true;
		}
	}

	/* Check for missing element, i.e. check boxes, radio boxes */
	$missing = array_diff_key($mustExist, $form);		/* returns keys in mustExist but not in form */
	if (DEBUG) print "Line " . __LINE__ . "-Validate:radiobutton<br>";
	foreach ($missing as $key => $value){
		$errMsg .= $value . ",";
		$error = true;
	}
	/* Add the missing elements back into the array to avoid display errors */
	$_POST = array_merge($_POST, $missing);	
	
	/* Check for options not selected */
	$selected = array_intersect_key($form,$selectedLists);
	foreach ($selected as $key=>$value){
		if (DEBUG) print "Selected key: " . $key . "-" . $value ."<br>";
		if (contains_substr($value, "Select")){
			$error = true;
			$errMsg .= $selectedLists[$key] . ",";
		}
	}

	/* Required elements for processing, no validation. Missing, add them back into the form array
	   If the check box is missing, we default it to No
	*/
	$checkBoxes = array('mon'=>'N','tue'=>'N','wed'=>'N','thur'=>'N','fri'=>'N','classroom'=>'N','craft'=>'N','kitchen'=>'N','anything'=>'N');
	$missingCheckBoxes = array_diff_key($checkBoxes, $form);  /* Returns check boxes not in the form */
	$_POST = array_merge($_POST, $missingCheckBoxes);

	/* This assigns the error text to a variable outside the function */
	$errMsgText = trim($errMsg, ",") . " required.";
	return !$error;
}
function check4dupes($form){
	
	$sql = "Select count(*) from staff ";
	$sql .= "WHERE first_name='" . $form['first_name'] . "'";
	$sql .= " AND last_name='" . $form['last_name'] . "'";
	//$sql .= " and birthdate='" . $form['birthdate'] . "'";
	$recCount = mysqli_query($vbsDBi, $sql);
	
}
function checkStaffNursery(){
	if (DEBUG) print "vbsDBi is " . $vbsDBi;
	$sql = "Select count(*) from students WHERE class='Staff Nursery' AND family_id = " . $_SESSION['family_id'];
	//$recCount = mysqli_query($vbsDBi, $sql);
	//return ($recCount>0?"checked":"");

}

/*  MAIN */
$offset = (empty($_POST['offset'])) ? 0 : $_POST['offset'];
$validateError = false;
$yesVal = $yesChk = $noChk = '';
$errMsgText = "";
$staffNurseryExists = false;
$numStudents =(empty($_POST['numStudents'])) ? 0 : $_POST['numStudents'];

if (DEBUG){
	if (isset($_POST['nursery']) ) {
		print "Staff Nursery _POST = " . $_POST['nursery'] . "<br>";
	}
	else
	{
		print "Staff Nursery _POST is null";
	}
}

/* Turn on the button display by default */
$button['New'] = '';
$button['Home'] = '';
$button['Back'] = '';
$button['NextPage'] = '';


if (empty($_REQUEST['submit'])){	
	if (DEBUG) print "Line " . __LINE__ . "<br>";
	/* Entering from registration menu.  Perform initial population */
	$_REQUEST['submit']='';		/* Set this to blank to avoid unset errors and skip the switch statement */
	$offset = 0;	/* Display the first record of the series */
}
elseif ($_REQUEST['submit']=='Redisplay'){
	if (DEBUG) print "Line " . __LINE__ . "<br>";
	/* We really do nothing here except skip the whole switch statement section */
}
else {
switch ($_POST['submit']) {
	case NEW_BUTTON :
		if (DEBUG) print "Line " . __LINE__ . "-New<br>";
		/* Create a blank array */
		$row_rsStudent = array();
		$row_rsStudent['first_name'] = $row_rsStudent['last_name'] = $row_rsStudent['age_group'] = $row_rsStudent['classroom'] = '';
		$row_rsStudent['registered'] = $row_rsStudent['teach_with'] = $row_rsStudent['comments'] = $row_rsStudent['picture'] = '';
		$row_rsStudent['mon'] = $row_rsStudent['tue'] = $row_rsStudent['wed'] = $row_rsStudent['thur'] = $row_rsStudent['fri'] = "Y";
		$row_rsStudent['classroom'] = $row_rsStudent['craft'] = $row_rsStudent['kitchen'] = $row_rsStudent['anything'] = 'Y';
		$row_rsStudent['deleted']  = $row_rsStudent['confo'] = $row_rsStudent['nursery'] = '';
		$row_rsStudent['shirt_size'] = '';
		$row_rsStudent['family_id'] = $_SESSION['family_id'];
		$row_rsStudent['staff_id'] = 0;
		
		
		/* Disable the inappropriate buttons so we don't have to do a lot of status checking.
		   The only valid buttons in new student mode are save and cancel. */
		$button['Home'] = ' disabled';
		$button['Back'] = ' disabled';
		$button['NextPage'] = ' disabled';
		$button['New'] = ' disabled';
		/* Set the registered button variables */
		$yesVal = 'Y';
		$yesChk = ' checked ';
		$noChk  = '';

		break;
	case "Save" :
		if (validate($_POST)) {
			$errMsg = '';
			/* Save the staff nursery option to a session variable.  It is only needed for this session */
			//@@-- $_SESSION['staffNursery'] = (isset($_POST['nursery']) ? $_POST['nursery'] : '');
			/* This is a new record to insert */
			$sql = "INSERT into staff (family_id, first_name, last_name, shirt_size, picture, registered, teach_with, confo, ";
			$sql .= "classroom, nursery, craft, kitchen, anything, mon, tue, wed, thur, fri, age_group, create_date, last_update)";
			$sql .= "VALUES (%u,'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',now(),now())";
			$sqlStmt = 	sprintf($sql, 
				$_SESSION['family_id'],
				mysqli_real_escape_string($vbsDBi, $_POST['first_name']),
				mysqli_real_escape_string($vbsDBi, $_POST['last_name']),
				$_POST['shirt_size'],
				(isset($_POST['picture'])    ? $_POST['picture']    : 'N'),
				(isset($_POST['registered']) ? $_POST['registered'] : 'N'),
				mysqli_real_escape_string($vbsDBi, $_POST['teach_with']),
				$_SESSION['confoNo'],
				(isset($_POST['classroom'])?'Y':'N'),
			    (isset($_POST['nursery'])  ?'Y':'N'),
				(isset($_POST['craft'])    ?'Y':'N'),
				(isset($_POST['kitchen'])  ?'Y':'N'),
				(isset($_POST['anything']) ?'Y':'N'),
				(isset($_POST['mon'])      ?'Y':'N'),
				(isset($_POST['tue'])      ?'Y':'N'),
				(isset($_POST['wed'])      ?'Y':'N'),
				(isset($_POST['thur'])     ?'Y':'N'),
				(isset($_POST['fri'])      ?'Y':'N'),
				(isset($_POST['age_group'])?$_POST['age_group']:'')
				);
			if (mysqli_query($vbsDBi, $sqlStmt)){
				if (DEBUG) print "Line " . __LINE__ . "<br>";
				writeLog($sqlStmt);
				/* Here we must redirect back to ourself to prevent a duplicate if the user refreshes the browser */
				header("Location: staff.php?submit=Redisplay");
			}
			else {
				$sqlErr = mysqli_error($vbsDBi);
				if (DEBUG) print "Line " . __LINE__ . " " . $sqlErr . "<br>";
				writeErr("Error: ", "Staff:Save", __LINE__, $sqlErr);
			}
		}
		else{
			writeLog("Validation failed for " . $_POST['staff_id']);
			$validateError = true;
			/* Set value and checked for the registered attribute.  We do not need to account for
			   a 'C' value here because this is a new record.  It can only be Y | N */
			$yesVal = 'Y';
			if ($_POST['registered']=='Y'){
				$yesChk = ' checked ';
				$noChk  = '';
			}
			else {
				$yesChk = '';
				$noChk  = ' checked ';
			}

		}
		break;
	case HOME_BUTTON :
	case PREVIOUS_BUTTON :
	case NEXT_PAGE :
	case FIRST_RECORD :
	case PREVIOUS_RECORD :
	case NEXT_RECORD :
	case LAST_RECORD :
	case "Update" :
		if (DEBUG) print "Line " . __LINE__ . "<br>";
		/* Get outta here if this staff person is not registering */
		if (isset($_POST['registered']) && $_POST['registered']=='Y') {
		
			if (validate($_POST)){
				//@@--$_SESSION['staffNursery'] = (isset($_POST['nursery']) ? $_POST['nursery'] : '');

				$sql = "UPDATE staff SET first_name='%s', last_name='%s', shirt_size='%s', picture='%s', registered='%s',teach_with='%s', comments='%s', age_group='%s', ";
				$sql .= "classroom='%s',nursery='%s',craft='%s', kitchen='%s', anything='%s', mon='%s', tue='%s', wed='%s', thur='%s', fri='%s', last_update=now()";
				$sqlWhere = " WHERE staff_id = " . $_POST['staff_id'];
				$sqlStmt = sprintf($sql,
					mysqli_real_escape_string($vbsDBi, $_POST['first_name']),
					mysqli_real_escape_string($vbsDBi, $_POST['last_name']),
					$_POST['shirt_size'],
					(isset($_POST['picture'])    ? $_POST['picture']    : 'N'),
					(isset($_POST['registered']) ? $_POST['registered'] : 'N'),
					mysqli_real_escape_string($vbsDBi, (isset($_POST['teach_with']) ? $_POST['teach_with'] : '')),
					mysqli_real_escape_string($vbsDBi, $_POST['comments']),
					(isset($_POST['age_group'])  ? $_POST['age_group'] : ''),
					(isset($_POST['classroom'])  ? 'Y' : 'N'),
				    (isset($_POST['nursery'])   ? 'Y' : 'N'),
					(isset($_POST['craft'])      ? 'Y' : 'N'),
					(isset($_POST['kitchen'])    ? 'Y' : 'N'),
					(isset($_POST['anything'])   ? 'Y' : 'N'),
					(isset($_POST['mon'])  ? 'Y' : 'N'),
					(isset($_POST['tue'])  ? 'Y' : 'N'),
					(isset($_POST['wed'])  ? 'Y' : 'N'),
					(isset($_POST['thur']) ? 'Y' : 'N'),
					(isset($_POST['fri'])  ? 'Y' : 'N')
				);
				$sqlStmt .= $sqlWhere;
				if (DEBUG) print $sqlStmt;
				if (mysqli_query($vbsDBi, $sqlStmt)){
					if (DEBUG) print "Line " . __LINE__ . "<br>";
					writeLog($sqlStmt);
				}
				else {
					$sqlErr = mysqli_error($vbsDBi);
					writeErr("Error update staff ", "Staff:Update", __LINE__, $sqlErr);
				}
			}
			else {
				$validateError = true;
			}
		}
		/* Case wthin a case.  This sub-case controls the pagination, i.e. where we go next
		  while consolidating all the update functionality within the same piece of code */
		quickSave();
		switch ($_REQUEST['submit']) {
			case FIRST_RECORD :
				if (DEBUG) print "Line " . __LINE__ . "-First<br>";
				$offset = 0;
				break;
			case PREVIOUS_RECORD :
				if (DEBUG) print "Line " . __LINE__ . "-Previous<br>";
				$offset = $offset - 1;
				break;
			case NEXT_RECORD :		
				if (DEBUG) print "Line " . __LINE__ . "-Next<br>";
				$offset = $offset + 1;
				break;
			case LAST_RECORD :
				if (DEBUG) print "Line " . __LINE__ . "-Last<br>";
				$offset = $numStudents -1;
				break;
			case HOME_BUTTON :
				header("Location: " . HOME_PAGE);
				break;
			case NEXT_PAGE :
				header("Location: " . (isset($_POST['nursery']) ? STAFF_NURSERY_PAGE : SUMMARY_PAGE));
				break;
			case PREVIOUS_BUTTON :
				header("Location: " . STUDENT_PAGE);
				break;
		}
		break;
	}
}
/*  The above switch statement will set the offset if doing pagination.
    If entering the first time, pagination will be set to display the first student.
	If updating or registering, pagination will remain the same and the same record will display.
	We always requery the database to update the screen except when validate error is true.
*/

if ($validateError){
	if (DEBUG) print "Line " . __LINE__ . "-Validation Error<br>";
	/* Restore the submitted values to redislay for fixing */
	$row_rsStudent = $_POST;
	}
else {
	if ($_REQUEST['submit']=="New"){
		if (DEBUG) print "Line " . __LINE__ . "-New<br>";
		$numStudents = 0;}
	else {
		if (DEBUG) print "Line " . __LINE__ . "-Validation OK: Redisplay<br>";
		//$query_rsStudent = "SELECT * FROM staff WHERE family_id=".$_SESSION['family_id'];
		$query_rsStudent = "SELECT staff_id, family_id, first_name, last_name, Assignment, picture, mon, tue, wed, thur, fri, kitchen, craft, classroom, nursery, anything, shirt_size, teach_with, age_group, confo, registered, comments, create_date, last_update, deleted FROM staff WHERE family_id=".$_SESSION['family_id'];
		$all_rsStudent = mysqli_query($vbsDBi, $query_rsStudent);
		$numStudents = mysqli_num_rows($all_rsStudent);
		if ($_REQUEST['submit']=='Redisplay') $offset = $numStudents-1;  /* Go to last record */
		$query_limit_rsStudent = sprintf("%s LIMIT %d, %d", $query_rsStudent, $offset, $numStudents);
		$rsStudent = mysqli_query($vbsDBi, $query_limit_rsStudent);
		$row_rsStudent = mysqli_fetch_assoc($rsStudent);
		/*
		 * TEST DEBUG STATEMENT
		 */
		Print "* * * " . $row_rsStudent['first_name'] . " - " .$row_rsStudent['nursery'] . " * * *<br>";
		
		/*Set the registered radio button variables here */
		$yesVal = ($row_rsStudent['registered']=='C') ? 'C' : 'Y';
		$yesChk = ($row_rsStudent['registered']=='Y' or $row_rsStudent['registered']=='C') ? ' checked ' : '';
		$noChk  = ($row_rsStudent['registered']=='N') ? ' checked ' : '';

/* * * * * * *		
		if (isset($_POST['nursery']) && $_POST['nursery']=='on'){
		} 
		else {
			$sql = "Select count(*) from students WHERE class='Staff Nursery' AND (registered='Y' or registered='C') AND family_id = " . $_SESSION['family_id'];
			$recCount = mysqli_query($vbsDBi, $sql);
			if ($recCount==false) {
				$_SESSION['staffNursery']='';
				$staffNurseryExists =false;
				if (DEBUG) print "RecCount is false";
			}
			else {
				$recArray = mysqli_fetch_array($recCount);
				if ($recArray[0] > 0) {
					$staffNurseryExists = true;
					$_SESSION['staffNursery'] = 'on';
				}
				else {
					$staffNurseryExists = false;
					$_SESSION['staffNursery'] = '';
				}
				unset($recArray);
				mysqli_free_result($recCount);
			}
			
		}
* * * * */
	}
}


$query_rsClassList = "SELECT class FROM class_types WHERE staff_opt = true ORDER BY disp_order";
$rsClassList = mysqli_query($vbsDBi, $query_rsClassList);
if ($rsClassList) {
	$row_rsClassList = mysqli_fetch_assoc($rsClassList);}
else{
	$sqlErr = mysqli_error($vbsDBi);
	writeErr("Unable to get class list", "Student.php", __LINE__, $sqlErr);
}

$query_rsStudentShirtList = "SELECT shirt_size FROM list_shirts WHERE staff_opt = TRUE ORDER BY disp_order ";
$rsStudentShirtList = mysqli_query($vbsDBi, $query_rsStudentShirtList);
$row_rsStudentShirtList = mysqli_fetch_assoc($rsStudentShirtList);
$totalRows_rsStudentShirtList = mysqli_num_rows($rsStudentShirtList);

$_SESSION['staff_id'] = $row_rsStudent['staff_id'];

if ($row_rsStudent['registered']=="Y") {
	$registered = true;}
else{
	$registered = false;
}

/* Count the existing staff nursery registrants for this family
 * Logic:  If a staff nursery record exists, check the box and set the session variable 
 *         If no staff nursery record exists, examine the session variable.
 *         If no session variable exists, initialize it to blank.
 *         If a session variable does exist, then use that value to check the box
 */
/* * * * * * * *
$sql = "Select count(*) from students WHERE class='Staff Nursery' AND (registered='Y' or registered='C') AND family_id = " . $_SESSION['family_id'];
$recCount = mysqli_query($vbsDBi, $sql);
if ($recCount==false) {
	$staffNurseryExists =false;
}
else {
	$recArray = mysqli_fetch_array($recCount);
	if ($recArray[0] > 0) {
		$staffNurseryExists = true;
		$_SESSION['staffNursery'] = 'on';
	}
	else {
		if (!isset($_SESSION['staffNursery'])){
			$staffNurseryExists = false;
			$_SESSION['staffNursery']='';
		}
		else {
			$staffNurseryExists = ($_SESSION['staffNursery']=='on' ? true : false);
		}
	}
	unset($recArray);
	mysqli_free_result($recCount);
}
* * * * * * * */


$staffID = $row_rsStudent['staff_id'];

/* Set the button disabled properties */
$offset = ++$offset;
//@@$button['Back'] = '';
$button['First'] = ($numStudents > 2 and $offset > 2) ? '' : ' disabled';
$button['Previous'] = ($numStudents > 1 and $offset > 1) ? '' : ' disabled';
$button['Next'] = ($numStudents > 1 and $offset<($numStudents)) ? '' : ' disabled';
$button['Last'] = ($numStudents > 2 and $offset<($numStudents-1)) ? '' : ' disabled';
//@@$button['New'] = '';
$offset = --$offset;
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
<title>VBS Staff</title>
<link href="css/boilerplate.css" rel="stylesheet" type="text/css">
<link href="css/layout.css" rel="stylesheet" type="text/css">
<!-- <link href="css/textural.css" rel="stylesheet" type="text/css"> -->
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="scripts/vbsUtils.js"></script>
<script src="scripts/respond.min.js"></script>
</head>
<body>
<div id="Staff" class="gridContainer">
	<h1>VBS - Volunteers</h1>
	<h2>Edit information and save.</h2>
    <?php if ($validateError) echo "<h3>" . $errMsgText . "</h3>";?>
	<div id="dataLayout">
	<form method="post" name="frmStaff" target="_self" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
	<table cellspacing="0">
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hAtt')">Helping at VBS?<span class="popuptext" id="hAtt">Select yes if <?php echo (empty($row_rsStudent['first_name']) ? "you are" : $row_rsStudent['first_name'] . " is");?> helping at VBS in <?php echo date("Y");?>; otherwise select No.</span></span></td>
			<td class="value">
			<label><input type="radio" name="registered" id="reg-yes" value="<?php echo $yesVal?>" <?php echo $yesChk?>> Yes</label>
            <label><input type="radio" name="registered" id="reg-no" value="N" <?php echo $noChk?>> No</label>
			</td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hFirst')">First Name<span class="popuptext" id="hFirst">Enter the first name of the staff volunteer.</span></span></td><td class="value"><input name="first_name" type="text" id="first_name" value="<?php echo $row_rsStudent['first_name']; ?>" maxlength="20"></td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hLast')">Last Name<span class="popuptext" id="hLast">Enter the last name of the staff volunteer.</span></span></td><td class="value"><input name="last_name" type="text" value="<?php echo $row_rsStudent['last_name']; ?>" maxlength="20"></td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hAvail')">Availability<span class="popuptext" id="hAvail">Check off the days you are available to help during the week of VBS.  This check box is not for help before VBS starts.  (See family page).</span></span></td><td class="value">
            <input type="checkbox" name="mon" value="Y" <?php echo (strcasecmp($row_rsStudent['mon'],"N")==0 ? "" : "checked")?>>&nbsp;Mo
            <input type="checkbox" name="tue" value="Y" <?php echo (strcasecmp($row_rsStudent['tue'],"N")==0 ? "" : "checked")?>>&nbsp;Tu
            <input type="checkbox" name="wed" value="Y" <?php echo (strcasecmp($row_rsStudent['wed'],"N")==0 ? "" : "checked")?>>&nbsp;We
            <input type="checkbox" name="thur" value="Y" <?php echo (strcasecmp($row_rsStudent['thur'],"N")==0 ? "" : "checked")?>>&nbsp;Th
            <input type="checkbox" name="fri" value="Y"  <?php echo (strcasecmp($row_rsStudent['fri'],"N")==0 ? "" : "checked")?>>&nbsp;Fr
        </td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hPref')">Preferences<span class="popuptext" id="hPref">If you are particular about where you help, check only those boxes for the area in which you have an interest.</span></span></td><td class="value">
	        <input type="checkbox" name="classroom" value="Y"  <?php echo (strcasecmp($row_rsStudent['classroom'],"N")==0 ? "" : "checked")?>>&nbsp;Classroom
			<input type="checkbox" name="craft" value="Y" <?php echo (strcasecmp($row_rsStudent['craft'],"N")==0 ? "" : "checked")?>>&nbsp;Craft
            <input type="checkbox" id="kitchen" name="kitchen" value="Y" <?php echo (strcasecmp($row_rsStudent['kitchen'],"N")==0 ? "" : "checked")?>>&nbsp;Kitchen
            <input type="checkbox" name="anything" value="Y" <?php echo (strcasecmp($row_rsStudent['anything'],"N")==0 ? "" : "checked")?>>&nbsp;Anything
	    <tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hShirt')">Shirt Size<span class="popuptext" id="hShirt">Select the shirt size you want for this volunteer.  T-Shirt are only available for those who register before <?php echo VBS_SHIRT_DEADLINE_MMDDYYYY?></span></span></td><td class="value"><select name="shirt_size">
      <?php
do {  
?>
      <option value="<?php echo $row_rsStudentShirtList['shirt_size']?>"<?php if (!(strcmp($row_rsStudentShirtList['shirt_size'], $row_rsStudent['shirt_size']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStudentShirtList['shirt_size']?></option>
      <?php
} while ($row_rsStudentShirtList = mysqli_fetch_assoc($rsStudentShirtList));
  $rows = mysqli_num_rows($rsStudentShirtList);
  if($rows > 0) {
      mysqli_data_seek($rsStudentShirtList, 0);
	  $row_rsStudentShirtList = mysqli_fetch_assoc($rsStudentShirtList);
  }
?>
		</select>
		</td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hPic')">Picture&nbsp;?<span class="popuptext" id="hPic">May we take and post photos of you during VBS?  Those who decline will be required to wear a "No-pictures" identification sign during VBS.</span></span></td><td class="value">
            <label><input type="radio" name="picture" id="pic-yes" value="Y" <?php echo (strcasecmp($row_rsStudent['picture'],"Y")==0 ? "checked" : ""); ?>>&nbsp;Yes</label>
            <label><input type="radio" name="picture" id="pic-no" value="N" <?php echo (strcasecmp($row_rsStudent['picture'],"N")==0 ? "checked" : "");?>>&nbsp;No</label>
    	</td></tr>
        <tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('clear')">Over&nbsp;18?<span class="popuptext" id="clear">Federal and state regulations require us to have clearances for volunteers over the age of 18. Answer this question to help us identify who requires clearances.</span></span></td><td class="value">
            <label><input type="radio" name="age_group" value="Adult" <?php echo (strcasecmp($row_rsStudent['age_group'],"Adult")==0 ? "checked" : "");?>>&nbsp;Yes</label>
            <label><input type="radio" name="age_group" value="Youth" <?php echo (strcasecmp($row_rsStudent['age_group'],"Youth")==0 ? "checked" : "");?>>&nbsp;No</label>
    	</td></tr>
        <tr><td class="label"><span class="popup" onclick="myPopUp('nursery')">I need staff nursery<span class="popuptext" id="nursery">Check this box if you have a child under 3 and want to place them in the staff nursery.  You will be guided to register the child on the next page.  Leave box unchecked if you do not need these services. If a staff nursery record already exists, the text "Staff nursery registrant exists" will appear adjacent to the check box.</span></span></td><td class="value">
            <label><input type="checkbox" name="nursery" <?php echo (strcasecmp($row_rsStudent['nursery'],"Y")==0 ? "checked" : '')?>></label> <?php if ($staffNurseryExists) echo "Staff nursery registrant exists"?>
    	</td></tr>
		<tr>
          <td class="label"><span class="popup" onclick="myPopUp('hClass')">I want to help in my child's class<span class="popuptext" id="hClass">If you want to be in the same class as your child, enter the child's name in this space.</span></span></td><td class="value"><input type="text" name="teach_with" placeholder="Your child's name and grade" value="<?php echo $row_rsStudent['teach_with'];?>"></td></tr>
        <tr><td class="label"><span class="popup" onclick="myPopUp('hComment')">Comments<span class="popuptext" id="hComment">This block is for comments specifically related to this volunteer.  Comments are optional.</span></span></td><td class="value"><textarea name="comments"><?php echo $row_rsStudent['comments']; ?></textarea></td></tr>
        <tr><td>*&nbsp;required  <span class="popup" onclick="myPopUp('help')">Help available<span class="popuptext" id="help">Use this form to register volunteers for the week of VBS.  Volunteers must be in 7th grade or older.  Click the underlined labels for detailed popup help. Click again to close it.</span></span></td><td class="value">
   		<?php if ($staffID==0) { ?>
			<input type="submit" name="submit" value="Save">&nbsp;
			<input type="submit" name="submit" value="Cancel">
        <?php } else { ?>
        	<input type="submit" name="submit" value="Update">
        <?php } ?>
		</td></tr>
	</table>
    <input name="staff_id" type="hidden" value="<?php echo $row_rsStudent['staff_id']; ?>">
    <input name="family_id" type="hidden" value="<?php echo $row_rsStudent['family_id']; ?>">
    <!-- <input name="registered" type="hidden" value="<?php //echo $row_rsStudent['registered']; ?>"> -->
    <input name="deleted" type="hidden" value="<?php echo $row_rsStudent['deleted']; ?>">
    <input name="confo" type="hidden" value="<?php echo $row_rsStudent['confo']; ?>">
    <input name="offset" type="hidden" value="<?php echo $offset;?>">
    <input name="numStudents" type="hidden" value="<?php echo $numStudents;?>">
	<table style=margin-top:-0.6em><tr><td>
		
		<div id="buttonSubGroup" class="center">
    		<span>Displaying staff member <?php echo (($numStudents>0)?$offset+1:0)?> of <?php echo $numStudents ?></span><br>
			<input type="submit" name="submit" class="button" value="First"<?php echo $button['First']?>>&nbsp;
			<input type="submit" name="submit" class="button" value="Previous"<?php echo $button['Previous']?>>&nbsp;
			<input type="submit" name="submit" class="button" value="Next"<?php echo $button['Next']?>>&nbsp;
			<input type="submit" name="submit" class="button" value="Last"<?php echo $button['Last']?>>&nbsp;&nbsp;&nbsp;
			<input type="submit" name="submit" class="button" value="<?php echo NEW_BUTTON?>"<?php  echo $button['New']?>><br>
		</div>
	</table>
	<div id="buttonGroup" class="buttonGroup center">
		<input type="submit" name="submit" class="button" value="<?php echo HOME_BUTTON?>"<?php echo $button['Home']?>>&nbsp;
		<input type="submit" name="submit" class="button" value="<?php echo PREVIOUS_BUTTON?>"<?php echo $button['Back']?>>&nbsp;
		<input type="submit" name="submit" class="button" value="<?php echo NEXT_PAGE ?>"<?php  echo $button['NextPage']?>>
	</div>
  </form></div>
</div>
</body>
</html>
<?php
@mysqli_free_result($rsStudent);
@mysqli_free_result($rsClassList);
@mysqli_free_result($rsStudentShirtList);
?>
