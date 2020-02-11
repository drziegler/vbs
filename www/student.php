<?php 
session_start();

if (empty($_SESSION['family_id'])){
	/* Send the user to the search page if no family is selected */
	header("Location: search.php");
}
/* Quick save does no validation and is for when registration is set to no */
function quickSave(){
	global $vbsDBi;
	if (DEBUG) print "Line " . __LINE__ . ' Quick Save: $_POST = ';
	if (DEBUG) print_r($_POST); print "<br>";
	if (empty($_POST['student_id'])) return;       /* New student condition */
	
	$sqlUpdate = "update students set ";
	$sqlUpdate .= "first_name='" . ((strlen(trim($_POST['first_name']))>0) ? trim($_POST['first_name'])  : "") . "'";
	$sqlUpdate .= ",last_name='" . ((strlen(trim($_POST['last_name']))>0) ? trim($_POST['last_name'])  : "") . "'";
	$sqlUpdate .= ",birthdate='" . ((strlen(trim($_POST['birthdate']))>0) ? trim($_POST['birthdate'])  : "") . "'";
	$sqlUpdate .= ",buddy='" . ((strlen(trim($_POST['buddy']))>0) ? trim($_POST['buddy']) : "") . "'";
	$sqlUpdate .= ",comments='" . ((strlen(trim($_POST['comments']))>0) ? trim($_POST['comments']) : "") . "'";
	if (isset($_POST['picture'])) $sqlUpdate .= ",picture='" . $_POST['picture'] . "'";
	if (isset($_POST['registered'])) $sqlUpdate .= ",registered='" . $_POST['registered'] . "'";
	if (isset($_POST['class'])) $sqlUpdate .= ",class='" . $_POST['class'] . "'";
	if (isset($_POST['shirt_size']) && !$_POST['shirt_size']=="Select size") $sqlUpdate .= ",shirt_size='" . $_POST['shirt_size'] . "'";
	$sqlUpdate .= ",last_update=now() ";
	$sqlUpdate .= " WHERE student_id = " . $_POST['student_id'];
	mysqli_real_escape_string($vbsDBi, $sqlUpdate);

	if (mysqli_query($vbsDBi, $sqlUpdate)){
		if (DEBUG) print "Line " . __LINE__ . "-Updated Student<br>";
		writeLog(FILE_NAME . __LINE__ . " -QuickSave(): " . $sqlUpdate);
	}
	else {
		if (DEBUG) print "Line " . __LINE__ . "Update error.  See log file.<br>";
		$sqlErr = mysqli_error($vbsDBi);
		writeErr(FILE_NAME . __LINE__ . " -Error writing update statement", "QuickSave", __LINE__, $sqlErr);
	}

	return;
	
}
function validate($form){
	/* 	Validate the form.
		Return true if the form passed validation and is ready to save.
		Return false if the form failed validation and cannot be saved.
	*/
    if (DEBUG) print "Line " . __LINE__ . " Entering validate<br>";
	$error = FALSE;
	global $errMsgText;
	$errMsg = "";		/* clear out any previous messages */
	/* Mandatory form elements */	
	$mustExist  = array('picture'=>'Picture', 'registered'=>'Attending VBS?');
	$notBlank   = array('first_name'=>'First Name', 'last_name'=>'Last Name', 'birthdate'=>'Birthdate');
	$selectLists  = array('shirt_size'=>'Shirt size', 'class'=>'Class');

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
			$error = TRUE;
		}
	}

	/* Check for missing element, i.e. check boxes, radio boxes */
	$missing = array_diff_key($mustExist, $form);		/* returns keys in mustExist but not in form */
	foreach ($missing as $key => $value){
		$errMsg .= $value . ",";
		$error = TRUE;
	}
	/* If the element is missing, add a blank one to the array to avoid display errors */
	$_POST = array_merge($_POST, $missing);	
		
	/* Check for options not selected */
	$selected = array_intersect_key($form,$selectLists);
	foreach ($selected as $key=>$value){
		if (contains_substr($value, "Select")){
			$error = TRUE;
			$errMsg .= $selectLists[$key] . ",";
		}
	}

	/* This assigns the error text to a variable outside the function */
	$errMsgText = trim($errMsg, ",") . " required";
	return !$error;
}
function check4dupes($form){
	
	$dupeExists = FALSE;
	$sql = "Select count(*) from students ";
	$sql .= "WHERE first_name='" . $form['first_name'] . "'";
	$sql .= " AND last_name='" . $form['last_name'] . "'";
	$sql .= " and birthdate='" . $form['birthdate'] . "'";
	$sql .= " and family_id="  . $_SESSION['family_id'] ;
	$recCount = mysqli_query($GLOBALS['vbsDBi'], $sql);
	$rowCount = mysqli_fetch_row($recCount);
	if (DEBUG) {
		print "Line " . __LINE__ . "-Row count is " ;
		print_r($rowCount);
		print "<br>";
	}
	if ($rowCount[0] > 1) $dupeExists = TRUE;
	$recCount->close();
	
	$GLOBALS['errMsgText'] = "Duplicate student. Update existing record.";
	return $dupeExists;
	
}
/* * * * * * * * *    MAIN   * * * * * * * * * * * * * * * * * */
require_once('Connections/vbsDB.php');
include('vbsUtils.inc');
define('FILE_NAME', '[STUDENT] ');

$validateError = FALSE;
$yesVal = $yesChk = $noChk = $fldEnabled = $errMsgText = "";
$numStudents =(empty($_POST['numStudents'])) ? 0 : $_POST['numStudents'];

/* Turn on the button display by default */
$button['New'] = '';
$button['Home'] = '';
$button['Back'] = '';
$button['NextPage'] = '';


if (empty($_REQUEST['submit'])){	
    /* Entering from another page.  $_REQUEST[submit] will be empty. Perform initial population */
    if (DEBUG) print "Line " . __LINE__ . " Coming from another page.<br>";
	$_REQUEST['submit']='';		/* Set this to blank to prevent unset errors */
	$offset = 0;				/* Display the first record of the series */
}
elseif ($_REQUEST['submit']=='Redisplay' || $_REQUEST['submit']=='Cancel'){
    /* We really do nothing here except skip the whole switch statement section */
	if (DEBUG) print "Line " . __LINE__ . " Redisplay | Cancel<br>";
    $offset = 0;
}
else {
    /* We are rePOSTing from _SELF, so the 'submit' action will be populated.  
     * Take the action defined by each case statment for the action in 'submit' value.
     */
	$offset = $_POST['offset'];
switch ($_POST['submit']) {
	case NEW_BUTTON   :
		if (DEBUG) print "Line: " . __LINE__ . "-NEW STUDENT<br>";
		/* Create a blank array */
		$row_rsStudent = array();
		$row_rsStudent['first_name'] = $row_rsStudent['birthdate'] = $row_rsStudent['age'] = $row_rsStudent['class'] = '';
		$row_rsStudent['buddy'] = $row_rsStudent['comments'] = $row_rsStudent['picture'] = '';
		$row_rsStudent['deleted'] = $row_rsStudent['last_name'] = '';
		$row_rsStudent['shirt_size'] = '';
		$row_rsStudent['registered'] = 'Y';  /* Default to Yes. Parents forget to enable this! */
		$row_rsStudent['family_id'] = $_SESSION['family_id'];
		$row_rsStudent['student_id'] = 0;
		$numStudents = 0;
		
		$fieldEnable='';
		
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
	case "Save" :   /* Only happens on a new record */
	    if (validate($_POST) && (check4dupes($_POST) === NO_DUPES)) {
            if (DEBUG) print "Line " . __LINE__ . " Save - passed validation<br>";
            $errMsg = '';
            /* This is a new record to insert */
            $sql = "INSERT into students (family_id, first_name, last_name, birthdate, class, ";
            $sql .= "shirt_size, picture, registered, buddy, comments, create_date, last_update) ";
            $sql .= "VALUES (%u,'%s','%s','%s','%s','%s','%s','%s','%s','%s', now(), now())";
            if (DEBUG) print "Registered value is " . $_POST['registered'] . "<br>";
            $sqlStmt = 	sprintf($sql, 
                $_SESSION['family_id'],
                mysqli_real_escape_string($vbsDBi, $_POST['first_name']),
                mysqli_real_escape_string($vbsDBi, $_POST['last_name']),
                mysqli_real_escape_string($vbsDBi, $_POST['birthdate']),
                mysqli_real_escape_string($vbsDBi, $_POST['class']),
                mysqli_real_escape_string($vbsDBi, $_POST['shirt_size']),
                mysqli_real_escape_string($vbsDBi, $_POST['picture']),
                mysqli_real_escape_string($vbsDBi, $_POST['registered']),									
                mysqli_real_escape_string($vbsDBi, $_POST['buddy']),
                mysqli_real_escape_string($vbsDBi, $_POST['comments'])
            );
            /* Get the new student id after insert */
            writelog(FILE_NAME . __LINE__ . "-" . $sqlStmt);
            if (mysqli_query($vbsDBi, $sqlStmt)){
            $row_rsStudent['student_id'] = mysqli_insert_id($vbsDBi);
            if (DEBUG) {
                print "Line " . __LINE__ . "<br>";
                print "Local Offset is " . $offset . "<br>";
                print "POST Offset is " . $_POST['offset'] . "<br>";
                print "NumStudents is " . $_POST['numStudents'] . "<br>";
            }
            writeLog(FILE_NAME . __LINE__ . "-Student id inserted as " . $sqlStmt);

            /* Here we must redirect back to ourself to prevent a duplicate if the user refreshes the browser 
            	Redisplay just forces the code past the switch statement as there is no Redisplay option
            	Offset defines which record to display.  Since we added one, the offset will be one less than the total number of students*/
            header("Location: student.php?submit=Redisplay");
            }
            else {   /* ERROR inserting new record */
                if (DEBUG) print "Line " . __LINE__ . "<br>";
                $sqlErr = mysqli_error($vbsDBi);
                writeErr(FILE_NAME . __LINE__ . "-Error writing insert statement", "Switch:Save", __LINE__, $sqlErr);
            }
        }
		else {
            /* This else can only branch if this is a new student who failed validation */
		    if (DEBUG) print "Line " . __LINE__ . " Save - failed validation<br>";
            writeLog(FILE_NAME . __LINE__ . "-Validation failed for new student " . $_POST['first_name'] . ' ' . $_POST['last_name']);
            $button['Home'] = ' disabled';
            $button['Back'] = ' disabled';
            $button['NextPage'] = ' disabled';
            $button['New'] = ' disabled';
            $_REQUEST['submit']=NEW_BUTTON;
            $numStudents = 0;
            $validateError = TRUE;
            /* Set value and checked for the registered attribute.  We do not need to account for
			 a 'C' value here because this is a new record.  It can only be Y | N */
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
	default:
	    /* Any other 'submit' condition requires an update of the record.
	     * If the registered flag is not set or equal to 'N', then only a quickUpdate() is performed without validation.
	     * If registered flag is set and not 'N', then a validate is performed before the update.
	     * If the record updates sucessfully, then we redirect or rePOST as per the 'submit' action.
	     */
	    if (isset($_POST['registered']) && !($_POST['registered']=='N')) {
		    /* Validate only records where the registered value is true.  No use validating someone who is not attending! */
			if (validate($_POST)){           /* Validation passed */
			    if (DEBUG) print "Line " . __LINE__ . " Update - PASSED validation<br>";
				$sql = "UPDATE students SET first_name='%s', last_name='%s', birthdate='%s', 
						class='%s', shirt_size='%s', picture='%s', registered='%s', buddy='%s', comments='%s', last_update=now()";
				$sqlWhere = " WHERE student_id = " . $_POST['student_id'];
				$sqlStmt =  sprintf($sql,
            				mysqli_real_escape_string($vbsDBi, $_POST['first_name']),
            				mysqli_real_escape_string($vbsDBi, $_POST['last_name']),
            				mysqli_real_escape_string($vbsDBi, $_POST['birthdate']),
            				$_POST['class'],
            				$_POST['shirt_size'],
            				mysqli_real_escape_string($vbsDBi, $_POST['picture']),
            				mysqli_real_escape_string($vbsDBi, $_POST['registered']),
            				mysqli_real_escape_string($vbsDBi, $_POST['buddy']),
            				mysqli_real_escape_string($vbsDBi, $_POST['comments'])
                            );
				$sqlStmt .= $sqlWhere;				
				if (mysqli_query($vbsDBi, $sqlStmt)){   /* Update GOOD */
					if (DEBUG) print "Line " . __LINE__ . "-Updated Student<br>";
					writeLog(FILE_NAME . __LINE__ . "-Updated student as " . $sqlStmt);
				}
				else {                                  /* Update FAILED */
					if (DEBUG) print "Line " . __LINE__ . "Update error.  See log file.<br>";
					$sqlErr = mysqli_error($vbsDBi);
					writeErr("Error writing update statement", "Switch:Update", FILE_NAME . __LINE__, $sqlErr);
					writeErr("SQL Statement: ", "Switch:Update", FILE_NAME . __LINE__, $sqlStmt);
				}
			}
			else { /* $_POST Record Validation FAILED */
			    if (DEBUG) print "Line " . __LINE__ . " Update - FAILED validation<br>";
				writeLog(FILE_NAME . __LINE__ . "-Validatition failed on update");
				$validateError = TRUE;
				/* On error, break out of outer select case statement.
				 * This prevents moving to another page on failure of a 
				 * registered student.   All the pagination in the next
				 * case statement will be skipped. */
				break;
			}
		}
		else {
		    /* Registered flag is either 1) not set or 2) set to 'N'.  Validation was not performed.
		     * Do a quickSave to preserve the screen values.  This fixes an issue where changes 
		     * revert to the database values when registered is set to 'N' or not set at all.   Fixed 4-8-19
		     */
		    quickSave();
		}
		/* Now handle the pagination */
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
			case NEXT_PAGE :
				header("Location: " . STAFF_PAGE);
				break;
			case PREVIOUS_BUTTON :
				header("Location: " . CONTACT_PAGE);
				break;
			case HOME_BUTTON :
				header("Location: " . HOME_PAGE);
				break;
		}
        break;
	}
}
/*  The above switch statement will set the offset if doing pagination.
    If entering the first time, pagination will be set to display the first student.
	If updating or registered, pagination will remain the same and the updated record.
	We always requery the database to update the screen except when validate error is true.
*/

if ($validateError){
    if (DEBUG) print "Line " . __LINE__ . " if validate error condition<br>";
	/* On validation error, restore the submitted values to the row_rsStudent array for redisplay */
	$row_rsStudent = $_POST;
    /* Need to reset the registered value variables so the proper state is maintained and displayed */
	$yesVal = ($row_rsStudent['registered']=='C') ? 'C' : 'Y';
	$yesChk = ($row_rsStudent['registered']=='Y' || $row_rsStudent['registered']=='C') ? ' checked ' : '';
	$noChk  = ($row_rsStudent['registered']=='N') ? ' checked ' : '';
}
else {   /* Passed validation */
	if ($_REQUEST['submit']==NEW_BUTTON){
		if (DEBUG) print "Line " . __LINE__ . "-New<br>";
		$numStudents = 0;
		$fldEnabled = '';
	}
	else {
	    if (DEBUG) print "Line " . __LINE__ . "-Validation OK: Redisplay<br>";
		$query_rsStudent = 	"SELECT student_id, family_id, first_name, last_name, birthdate, class, shirt_size, picture, buddy, comments, create_date, last_update, registered FROM students WHERE family_id=" .$_SESSION['family_id'] . " AND deleted=0 AND class<>'Staff Nursery'";
		$all_rsStudent = mysqli_query($vbsDBi, $query_rsStudent);
		$numStudents = mysqli_num_rows($all_rsStudent);	
		if ($_REQUEST['submit']=='Redisplay') $offset = $numStudents-1;  /* Go to last record */
		$query_limit_rsStudent = sprintf("%s LIMIT %d, %d", $query_rsStudent, $offset, $numStudents);
		$rsStudent = mysqli_query($vbsDBi, $query_limit_rsStudent);
		$row_rsStudent = mysqli_fetch_assoc($rsStudent);
		/*Set the registered radio button variables here */
		$yesVal = ($row_rsStudent['registered']=='C') ? 'C' : 'Y';
		$yesChk = ($row_rsStudent['registered']=='Y' or $row_rsStudent['registered']=='C') ? ' checked ' : '';
		$noChk  = ($row_rsStudent['registered']=='N') ? ' checked ' : '';

		if (DEBUG) print "Line " . __LINE__ . " Number of students = " . $numStudents . '<br>';
		if ($numStudents==0) {
		    $_REQUEST['submit']=NEW_BUTTON;
		    /* If there are no students on record, disable the input fields.  User required to press new button */
		    $fldEnabled=' disabled';
		}
	}
}
if (DEBUG) print "Line " . __LINE__ . " Pagination : " . $offset . " of " . $numStudents . "<br>";

$query_rsClassList = "SELECT class FROM class_types WHERE student_opt = TRUE ";
if (iDate('z') > iDate('z', strtotime(VBS_MOM_ME_DEADLINE))){
    $query_rsClassList .= "AND class<>'Mom and Me' ";
}

$query_rsClassList .= "ORDER BY disp_order";

$rsClassList = mysqli_query($vbsDBi, $query_rsClassList);
if ($rsClassList) {
	$row_rsClassList = mysqli_fetch_assoc($rsClassList);
	if (DEBUG){
	    print "Line " . __LINE__ . " Class list: ";
	    print_r($row_rsClassList);
	    print "<br>";
	}
}
else{
    writeErr("-Unable to get class list", FILE_NAME, __LINE__, mysqli_errno($vbsDBi));
}

$query_rsStudentShirtList = "SELECT shirt_size FROM list_shirts WHERE student_opt = TRUE ORDER BY disp_order ";
$rsStudentShirtList = mysqli_query($vbsDBi, $query_rsStudentShirtList);
$row_rsStudentShirtList = mysqli_fetch_assoc($rsStudentShirtList);
$totalRows_rsStudentShirtList = mysqli_num_rows($rsStudentShirtList);

$_SESSION['student_id'] = $row_rsStudent['student_id'];
if (DEBUG) print "Line " . __LINE__ . " Session student id is" . $_SESSION['student_id'] . "<br>";

$studentID = $row_rsStudent['student_id'];

/* Set the button disabled properties */
$offset = ++$offset;
$button['First'] = ($numStudents > 2 and $offset > 2) ? '' : ' disabled';
$button['Previous'] = ($numStudents > 1 and $offset > 1) ? '' : ' disabled';
$button['Next'] = ($numStudents > 1 and $offset<($numStudents)) ? '' : ' disabled';
$button['Last'] = ($numStudents > 2 and $offset<($numStudents-1)) ? '' : ' disabled';
$offset = --$offset;

?>
<!doctype html>
<html class="">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>VBS Student</title>
<link href="css/layout.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="Find" class="gridContainer">
	<h1>Student Info</h1>
	<div id="dataLayout">
	<div id="Student">
	<form method="post" name="frmStudent" target="_self" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
	<table>
		<?php if ($validateError) { ?> 
			<tr><td colspan="2" class="error title center"> <?php echo $errMsgText; ?>
		<?php } else { ?>
			<tr><td colspan="2" class="center title">Edit information and save</td></tr> 
	 	<?php } ?>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hAtt')">Attending VBS?<span class="popuptext" id="hAtt">Select yes if <?php echo (empty($row_rsStudent['first_name']) ? "this child" : $row_rsStudent['first_name']);?> is attending VBS in <?php echo date("Y");?>; otherwise select No.</span></span></td>
		<td class="value">
			<label><input type="radio" name="registered" id="reg-yes" value="<?php echo $yesVal?>" <?php echo $yesChk . $fldEnabled?> > Yes</label>
            <label><input type="radio" name="registered" id="reg-no" value="N" <?php echo $noChk . $fldEnabled?>> No</label>
		</td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hFirst')">First Name<span class="popuptext" id="hFirst">Enter your child's first name exactly as you want it to appear on name tags, project labels, etc.  This includes capitalization and any punctuation you desire.</span></span></td><td class="value"><input name="first_name" type="text" id="first_name" value="<?php echo $row_rsStudent['first_name']?>" maxlength="20" <?php echo  $fldEnabled?> style="width:60%" autofocus></td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hLast')">Last Name<span class="popuptext" id="hLast">Enter your child's last name exactly as you want it to appear on name tags, project labels, etc.  This includes capitalization and any punctuation you require.</span></span></td><td class="value"><input name="last_name" type="text" value="<?php echo $row_rsStudent['last_name']; ?>" maxlength="20" <?php echo  $fldEnabled?> style="width:60%"></td></tr>
		<tr><td class="label">*&nbsp;<span>Birthdate</span></td><td class="value"><input name="birthdate" type="date" value="<?php echo $row_rsStudent['birthdate']; ?>" min="<?php echo VBS_DATE_MIN?>" max="<?php echo VBS_DATE_MAX?>" <?php echo  $fldEnabled?>></td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hGrade')">Grade Completed<span class="popuptext" id="hGrade">Select the grade your child is in right now or just completed.  DO NOT select the grade your child is going to in the fall.  Mom and Me students must register by <?php echo VBS_MOM_ME_DEADLINE_MMDDYYYY ?> because of the requirement for security clearances to be completed.</span></span></td><td class="value">
        <select name="class" <?php echo $fldEnabled?>>
		<?php do {  ?>
			<option value="<?php echo $row_rsClassList['class']?>"<?php if (!(strcmp($row_rsClassList['class'], $row_rsStudent['class']))) {echo "selected=\"selected\"";} ?>>
			<?php echo $row_rsClassList['class']?></option>
		<?php
		
		} while ($row_rsClassList = mysqli_fetch_assoc($rsClassList));
?>
        </select></td></tr>
    <tr><td class="label">*&nbsp;<span>Shirt Size</span></td><td class="value"><select name="shirt_size" <?php echo $fldEnabled?>>
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
		<tr>
		  <td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hPic')">Picture<span class="popuptext" id="hPic">May we take and post photos of your child during VBS?</span></span></td><td class="value">
            <label><input type="radio" name="picture" id="pic-yes" value="Y" <?php if (!(strcmp($row_rsStudent['picture'],"Y"))) {echo "checked";} echo $fldEnabled; ?> > Yes</label>
            <label><input type="radio" name="picture" id="pic-no" value="N" <?php if (!(strcmp($row_rsStudent['picture'],"N"))) {echo "checked";} echo $fldEnabled; ?>> No</label>
        </td></tr>
        <tr><td class="label"><span class="popup" onclick="myPopUp('hBud')">Friend<span class="popuptext" id="hBud">If your child wants to be with a specific friend, enter their name here.  Their friend must be in the same grade.  We will do our best to accommodate your request.</span></span></td><td class="value"><input name="buddy" type="text" value="<?php echo $row_rsStudent['buddy']; ?>" maxlength="20" <?php echo $fldEnabled;?>></td></tr>
        <tr>
          <td class="label"><span class="popup" onclick="myPopUp('sComment')">Comments:<span class="popuptext" id="sComment">Enter comments here that are related to this child.</span></span></td><td class="value"><textarea name="comments" cols="" rows="" <?php echo $fldEnabled?>><?php echo $row_rsStudent['comments']; ?></textarea></td></tr>
        <tr>
          <td class="label left"><span>*&nbsp;required</span></td>
          <td class="value">
   		<?php if ($fldEnabled=='') {if ($_REQUEST['submit']==NEW_BUTTON) { ?>
			<input type="submit" name="submit" value="Save">&nbsp;
			<input type="submit" name="submit" value="Cancel">
        <?php } else { ?>			
			  <input type="submit" name="submit" value="Update">
        <?php } }?>
        <span class="popup float-right" onclick="myPopUp('help')">Help available<span class="popuptext" id="help">Use this form to register students for VBS.  Click the first row of navigation buttons to move between children in your family.  Use the bottom row of navigation buttons to move between pages.  Click the 'new' button to start a new record.  Click the underlined labels for each item to get detailed help. Click again to close the popup..</span></span>
		</td></tr>
		<tr><td colspan='2'>
		<div id="buttonSubGroup" class="center">
	    	Displaying student <?php echo (($numStudents>0)?$offset+1:0)?> of <?php echo $numStudents ?><br>
			<input type="submit" class="button" name="submit" value="<?php echo FIRST_RECORD?>" <?php echo $button['First'];?>>&nbsp;
			<input type="submit" class="button" name="submit" value="<?php echo PREVIOUS_RECORD?>" <?php echo $button['Previous'];?>>&nbsp;
			<input type="submit" class="button" name="submit" value="<?php echo NEXT_RECORD?>" <?php echo $button['Next'];?>>&nbsp;
			<input type="submit" class="button" name="submit" value="<?php echo LAST_RECORD?>" <?php echo $button['Last'];?>>&nbsp;
        	<input type="submit" class="button" name="submit" value="<?php echo NEW_BUTTON?>" <?php echo $button['New'];?>><br>
		</div>
		</td></tr>
	</table>
    <input name="student_id" type="hidden" value="<?php echo $row_rsStudent['student_id']; ?>">
    <input name="family_id" type="hidden" value="<?php echo $row_rsStudent['family_id']; ?>">
    <input name="offset" type="hidden" value="<?php echo $offset;?>">
    <input name="numStudents" type="hidden" value="<?php echo $numStudents;?>">
	<div id="buttonGroup" class="center">
		<input type="submit" class="button" name="submit" value="<?php echo HOME_BUTTON?>"<?php echo $button['Home'];?>>&nbsp;
		<input type="submit" class="button" name="submit" value="<?php echo PREVIOUS_BUTTON?>"<?php echo $button['Back'];?>>&nbsp;
		<input type="submit" class="button" name="submit" value="<?php echo NEXT_PAGE?>"<?php echo $button['NextPage'];?>>
	</div>
  </form>
  </div>
  </div>
</div>
<script src="scripts/vbsUtils.js"></script>
</body>
</html>
<?php
@mysqli_free_result($rsStudent);
@mysqli_free_result($rsClassList);
@mysqli_free_result($rsStudentShirtList);
?>
