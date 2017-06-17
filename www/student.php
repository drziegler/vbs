<?php 
session_start();
if (empty($_SESSION['family_id'])){
	/* Send the user to the search page if no family is selected */
	header("Location: search.php");
}
function validate($form){
	/* 	Validate the form.
		Return true if the form passed validation and is ready to save.
		Return false if the form failed validation and cannot be saved.
	*/
	$error = false;
	global $errMsgText;
	$errMsg = "";		/* clear out an previous messages */
	/* Mandatory form elements */	
	$mustExist  = array('picture'=>'Picture');
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
			$error = true;
		}
	}

	/* Check for missing element, i.e. check boxes, radio boxes */
	$missing = array_diff_key($mustExist, $form);		/* returns keys in mustExist but not in form */
	foreach ($missing as $key => $value){
		$errMsg .= $value . ",";
		$error = true;
	}
	/* If the element is missing, add a blank one to the array to avoid display errors */
	$_POST = array_merge($_POST, $missing);	
		
	/* Check for options not selected */
	$selected = array_intersect_key($form,$selectLists);
	foreach ($selected as $key=>$value){
		if (contains_substr($value, "Select")){
			$error = true;
			$errMsg .= $selectLists[$key] . ",";
		}
	}

	/* This assigns the error text to a variable outside the function */
	$errMsgText = "Check: " . trim($errMsg, ",");
	return !$error;
}
function check4dupes($form){
	
	$sql = "Select count(*) from students ";
	$sql .= "WHERE first_name='" . $form['first_name'] . "'";
	$sql .= " AND last_name='" . $form['last_name'] . "'";
	$sql .= " and birthdate='" . $form['birthdate'] . "'";
	$recCount = mysqli_query($vbsDBi, $sql);
	
}
/*  MAIN */
require_once('Connections/vbsDB.php');
include('vbsUtils.inc');

$validateError = false;
$errMsgText = "";
$numStudents =(empty($_POST['numStudents'])) ? 0 : $_POST['numStudents'];


if (empty($_REQUEST['submit'])){	
	if (DEBUG) print "Line " . __LINE__ . "<br>";
	/* Entering from registration menu.  Perform initial population */
	$_REQUEST['submit']='';		/* Set this to blank to avoid unset errors */
	$offset = 0;				/* Display the first record of the series */
}
elseif ($_REQUEST['submit']=='Redisplay'){
	/* We are coming from SELF after a new record insert.  Get the GET offset */
	if (DEBUG) print "Line " . __LINE__ . "<br>";
	/* We really do nothing here except skip the whole switch statement section */
}
else {
$offset = $_POST['offset'];
if (DEBUG) print "Offset: at line " . __LINE__ . " is " . $offset . "<br>";
switch ($_POST['submit']) {
	case "Back" :
		if (DEBUG) print "Line: " . __LINE__ . "<br>";
		header(sprintf("Location: " . HOME_PAGE));
		break;
	case "First" :
		if (DEBUG) print "Line: " . __LINE__ . "<br>";	
		$offset = 0;
		print "Offset: at line " . __LINE__ . " is " . $offset . "<br>";
		break;
	case "Next"  :
		if (DEBUG) print "Line: " . __LINE__ . "<br>";
		if (DEBUG) print "Offset was " . $offset;
		$offset = $offset + 1;
		if (DEBUG) print "Offset is now " . $offset . "<br>";
		break;
	case "Previous" :
		if (DEBUG) print "Line: " . __LINE__ . "<br>";
		$offset = $offset - 1;
		if (DEBUG) print "Offset is now " . $offset . "<br>";
		break;
	case "Last"  :
		if (DEBUG) print "Line: " . __LINE__ . "<br>";
		$offset = $numStudents -1;
		if (DEBUG) print "Offset is now " . $offset . "<br>";
		break;
	case "New"   :
		if (DEBUG) print "Line: " . __LINE__ . "<br>";
		/* Create a blank array */
		$row_rsStudent = array();
		$row_rsStudent['first_name'] = $row_rsStudent['birthdate'] = $row_rsStudent['age'] = $row_rsStudent['class'] = '';
		$row_rsStudent['registered'] = $row_rsStudent['buddy'] = $row_rsStudent['comments'] = $row_rsStudent['picture'] = '';
		$row_rsStudent['deleted'] = $row_rsStudent['last_name'] = $row_rsStudent['confo'] = '';
		$row_rsStudent['shirt_size'] = '';
		$row_rsStudent['family_id'] = $_SESSION['family_id'];
		$row_rsStudent['student_id'] = 0;
		$numStudents = 0;
		break;
	case "Save" :   /* Only happens on a new record */
		if (validate($_POST)) {
			if (DEBUG) print "Line " . __LINE__ . "<br>";
			$errMsg = '';
			/* This is a new record to insert */
			$sql = "INSERT into students (family_id, first_name, last_name, birthdate, class, ";
			$sql .= "shirt_size, picture, registered, buddy, comments, confo, create_date, last_update) ";
			$sql .= "VALUES (%u,'%s','%s','%s','%s','%s','%s','Y','%s','%s','%s', now(), now())";
			$sqlStmt = 	sprintf($sql, 
				$_SESSION['family_id'],
				mysqli_real_escape_string($vbsDBi, $_POST['first_name']),
				mysqli_real_escape_string($vbsDBi, $_POST['last_name']),
				mysqli_real_escape_string($vbsDBi, $_POST['birthdate']),
				mysqli_real_escape_string($vbsDBi, $_POST['class']),
				mysqli_real_escape_string($vbsDBi, $_POST['shirt_size']),
				mysqli_real_escape_string($vbsDBi,$_POST['picture']),
				mysqli_real_escape_string($vbsDBi, $_POST['buddy']),
				mysqli_real_escape_string($vbsDBi, $_POST['comments']),
				$_SESSION['confoNo']
				);
			/* Get the new student id after insert */
			if (mysqli_query($vbsDBi, $sqlStmt)){
				if (DEBUG) print "Line " . __LINE__ . "<br>";	
				//@@ Check if we really need to do this!
				$row_rsStudent['student_id'] = mysqli_insert_id($vbsDBi);
				if (DEBUG) print "Local Offset is " . $offset . "<br>";
				if (DEBUG) print "POST Offset is " . $_POST['offset'] . "<br>";
				if (DEBUG) print "NumStudents is " . $_POST['numStudents'] . "<br>";
				writeLog("Inserted student id as " . $sqlStmt);
				/* Here we must redirect back to ourself to prevent a duplicate if the user refreshes the browser 
					Redisplay just forces the code past the switch statement as there is no Redisplay option
					Offset defines which record to display.  Since we added one, the offset will be one less than the total number of students*/
//@@				header("Location: student.php?submit=Redisplay&offset=".($_POST['numStudents']));
				header("Location: student.php?submit=Redisplay");
			}
			else {
				if (DEBUG) print "Line " . __LINE__ . "<br>";
				$sqlErr = mysqli_error($vbsDBi);
				writeErr("Error writing insert statement", "Switch:Save", __LINE__, $sqlErr);
			}
		}
		else{
			if (DEBUG) print "Line " . __LINE__ . "<br>";
			writeLog("Validation failed for " . $_POST['student_id']);
			$validateError = true;
		}
		break;
	case "Unregister" :
		if (DEBUG) print "Line " . __LINE__ . "<br>";
		$sql = "UPDATE students SET registered = 'N' WHERE student_id = " . $_POST['student_id'];
		$failedLine = __LINE__ - 1;
		if (mysqli_query($vbsDBi, $sql)){
			if (DEBUG) print "Line " . __LINE__ . "<br>";
			writeLog("Unregistered student as " . $sql);
		}
		else {
			if (DEBUG) print "Line " . __LINE__ . "<br>";
			$sqlErr = mysqli_error($vbsDBi);
			writeError("Error writing update statement", "Switch:Unregister", $failedLine, $sqlErr);
		}
		break;
	case "Register":
		if (DEBUG) print "Line " . __LINE__ . " - Register<br>";	
	case "Update" :
		if (validate($_POST)){
			if (DEBUG) print "Line " . __LINE__ . " - Update<br>";
			$sql = "UPDATE students SET first_name='%s', last_name='%s', birthdate='%s', 
					class='%s', shirt_size='%s', picture='%s', buddy='%s', comments='%s', last_update=now()";
			$sqlWhere = " WHERE student_id = " . $_POST['student_id'];
			$sqlStmt = sprintf($sql,
				mysqli_real_escape_string($vbsDBi, $_POST['first_name']),
				mysqli_real_escape_string($vbsDBi, $_POST['last_name']),
				mysqli_real_escape_string($vbsDBi, $_POST['birthdate']),
				$_POST['class'],
				$_POST['shirt_size'],
				mysqli_real_escape_string($vbsDBi, $_POST['picture']),
				mysqli_real_escape_string($vbsDBi, $_POST['buddy']),
				mysqli_real_escape_string($vbsDBi, $_POST['comments'])
				);
			if ($_POST['submit']=="Register") {
				if (DEBUG) print "Line " . __LINE__ . "<br>";
				/* Append the registered and confo columns to the sql statement */
				$sqlStmt .= ", registered='Y', confo='" . $_SESSION['confoNo'] . "' ";
			}
			$sqlStmt .= $sqlWhere;				
			if (mysqli_query($vbsDBi, $sqlStmt)){
				if (DEBUG) print "Line " . __LINE__ . "<br>";
				writeLog("Updated student as " . $sqlStmt);
			}
			else {
				if (DEBUG) print "Line " . __LINE__ . "<br>";
				$sqlErr = mysqli_error($vbsDBi);
				writeError("Error writing update statement", "Switch:Update", __LINE__, $sqlErr);
			}
		}
		else {
			if (DEBUG) print "Line " . __LINE__ . "<br>";
			writeLog("Validatition failed on Student:Update");
			$validateError = true;
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
	/* Restore the submitted values to redislay for fixing */
	if (DEBUG) print "Line " . __LINE__ . "<br>";
	$row_rsStudent = $_POST;}
else {
	if ($_REQUEST['submit']=="New"){
		if (DEBUG) print "Line " . __LINE__ . "-New<br>";
		$numStudents = 0;
	}
	else {
		if (DEBUG) print "Line " . __LINE__ . "-Validation OK: Redisplay<br>";
		$query_rsStudent = "SELECT * FROM students WHERE family_id=".$_SESSION['family_id'];
		$all_rsStudent = mysqli_query($vbsDBi, $query_rsStudent);
		$numStudents = mysqli_num_rows($all_rsStudent);	
		if ($_REQUEST['submit']=='Redisplay') $offset = $numStudents-1;  /* Go to last record */
		$query_limit_rsStudent = sprintf("%s LIMIT %d, %d", $query_rsStudent, $offset, $numStudents);
		$rsStudent = mysqli_query($vbsDBi, $query_limit_rsStudent);
		$row_rsStudent = mysqli_fetch_assoc($rsStudent);
	}
}


$query_rsClassList = "SELECT class FROM class_types WHERE student_opt = true ORDER BY disp_order";
$rsClassList = mysqli_query($vbsDBi, $query_rsClassList);
if ($rsClassList) {
	$row_rsClassList = mysqli_fetch_assoc($rsClassList);}
else{
	$sqlErr = mysqli_error($vbsDBi);
	writeErr("Unable to get class list", "Student.php", __LINE__, $sqlErr);
}

$query_rsStudentShirtList = "SELECT shirt_size FROM list_shirts WHERE student_opt = TRUE ORDER BY disp_order ";
$rsStudentShirtList = mysqli_query($vbsDBi, $query_rsStudentShirtList);
$row_rsStudentShirtList = mysqli_fetch_assoc($rsStudentShirtList);
$totalRows_rsStudentShirtList = mysqli_num_rows($rsStudentShirtList);

$_SESSION['student_id'] = $row_rsStudent['student_id'];

if ($row_rsStudent['registered']=="Y") {
	$registered = true;
	$waitlisted = false;}
else if ($row_rsStudent['registered']=="W") {
	$registered = false;
	$waitlisted = true;
}
else{
	$registered = false;
	$waitlisted = false;
}

$studentID = $row_rsStudent['student_id'];

/* Set the button disabled properties */
$offset = ++$offset;
$button['Back'] = '';
$button['First'] = ($numStudents > 2 and $offset > 2) ? '' : ' disabled';
$button['Previous'] = ($numStudents > 1 and $offset > 1) ? '' : ' disabled';
$button['Next'] = ($numStudents > 1 and $offset<($numStudents)) ? '' : ' disabled';
$button['Last'] = ($numStudents > 2 and $offset<($numStudents-1)) ? '' : ' disabled';
$button['New'] = '';
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
<title>VBS Student</title>
<link href="css/boilerplate.css" rel="stylesheet" type="text/css">
<link href="css/layout.css" rel="stylesheet" type="text/css">
<script src="scripts/vbsUtils.js"></script>
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!--<script src="scripts/respond.min.js"></script>-->
</head>
<body>
<div id="Student" class="gridContainer">
	<div><h1>VBS - Student</h1></div>
	<div id="status"><h2>
	<?php if ($registered) {?>
		Edit information and click update or unregister.
    <?php } else { ?>
		Edit information and click register.	        
	<?php } ?>    
    </h2></div>
    <?php if ($validateError) { ?><div class="error"><h3><?php echo $errMsgText;?></h3></div><?php } ?>
	<div id="dataLayout">
	<form method="post" name="frmStudent" target="_self" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
	<table>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hFirst')">First Name<span class="popuptext" id="hFirst">Enter your child's first name exactly as you want it to appear on name tags, projects labels, etc.  This includes capitalization and any punctuation you require.</span></span></td><td class="value"><input name="first_name" type="text" id="first_name" value="<?php echo $row_rsStudent['first_name']; ?>" maxlength="20"></td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hLast')">Last Name<span class="popuptext" id="hLast">Enter your child's last name exactly as you want it to appear on name tags, projects labels, etc.  This includes capitalization and any punctuation you require.</span></span></td><td class="value"><input name="last_name" type="text" value="<?php echo $row_rsStudent['last_name']; ?>" maxlength="20"></td></tr>
		<tr><td class="label">*&nbsp;Birthdate</td><td class="value"><input name="birthdate" type="date" value="<?php echo $row_rsStudent['birthdate']; ?>" min="2004-07-11" max="2013-07-11"></td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hGrade')">Grade Completed<span class="popuptext" id="hGrade">Select the grade your child is in right now or just completed.  DO NOT select the grade your child is going to in the fall.</span></span></td><td class="value">
        <select name="class">
		<?php do {  ?>
			<option value="<?php echo $row_rsClassList['class']?>"<?php if (!(strcmp($row_rsClassList['class'], $row_rsStudent['class']))) {echo "selected=\"selected\"";} ?>>
			<?php echo $row_rsClassList['class']?></option>
		<?php
		
		} while ($row_rsClassList = mysqli_fetch_assoc($rsClassList));
?>
        </select></td></tr>
    <tr><td class="label">*&nbsp;Shirt Size</td><td class="value"><select name="shirt_size">
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
            <label><input type="radio" name="picture" id="pic-yes" value="Y" <?php if (!(strcmp($row_rsStudent['picture'],"Y"))) {echo "checked";} ?>>Yes</label>
            <label><input type="radio" name="picture" id="pic-no" value="N" <?php if (!(strcmp($row_rsStudent['picture'],"N"))) {echo "checked";} ?>>No</label>
        </td></tr>
        <tr><td class="label"><span class="popup" onclick="myPopUp('hBud')">Friend<span class="popuptext" id="hBud">If your child wants to be with a specific friend, enter their name here.  Their friend must be in the same grade.  We will do our best to accommodate your request.</span></span></td><td class="value"><input name="buddy" type="text" value="<?php echo $row_rsStudent['buddy']; ?>" maxlength="20"></td></tr>
        <tr>
          <td class="label"><span class="popup" onclick="myPopUp('sComment')">&nbsp;Comments:<span class="popuptext" id="sComment">Enter comments here that are related to this child.</span></span></td><td class="value"><textarea name="comments" cols="" rows=""><?php echo $row_rsStudent['comments']; ?></textarea></td></tr>
       	<tr><td class="label"><span class="popup" onclick="myPopUp('hStatus')">Status<span class="popuptext" id="hStatus">Status indicates if this student is registered. If the form was completed correctly and submitted, a confirmation number will appear after the status. You may also unregister a student after registering.</span></span></td><td class="value"><?php echo ($registered ? "Registered (#".$row_rsStudent['confo'].")" : "Not registered"); ?></td></tr>
        <tr>
          <td>*&nbsp;required <span class="popup" onclick="myPopUp('help')">Help available<span class="popuptext" id="help">Use this form to register students for VBS.  Click the navigation buttons on the bottom to move between children in your family.  Click the underlined labels for detailed popup help. Click again to close it.</span></span></td>
          <td class="value">
   		<?php if ($registered) { ?>
			<input type="submit" name="submit" value="Update">&nbsp;&nbsp;<input type="submit" name="submit" value="Unregister">
		<?php } else { if ($studentID==0) { ?>
			<input type="submit" name="submit" value="Save">
        <?php } else { ?>
        	<input type="submit" name="submit" value="Register">
        <?php } } ?>
		</td></tr>
	</table>
    <input name="student_id" type="hidden" value="<?php echo $row_rsStudent['student_id']; ?>">
    <input name="family_id" type="hidden" value="<?php echo $row_rsStudent['family_id']; ?>">
    <input name="registered" type="hidden" value="<?php echo $row_rsStudent['registered']; ?>">
    <input name="deleted" type="hidden" value="<?php echo $row_rsStudent['deleted']; ?>">
    <input name="confo" type="hidden" value="<?php echo $row_rsStudent['confo']; ?>">
    <input name="offset" type="hidden" value="<?php echo $offset;?>">
    <input name="numStudents" type="hidden" value="<?php echo $numStudents;?>">
	<div id="buttonGroup" class="center">
    	<span>Displaying student <?php echo (($numStudents>0)?$offset+1:0)?> of <?php echo $numStudents ?> students</span><br>

		<input type="submit" class="button" name="submit" value="First" <?php echo $button['First'];?>>&nbsp;
		<input type="submit" class="button" name="submit" value="Previous" <?php echo $button['Previous'];?>>&nbsp;
		<input type="submit" class="button" name="submit" value="Next" <?php echo $button['Next'];?>>&nbsp;
		<input type="submit" class="button" name="submit" value="Last" <?php echo $button['Last'];?>><br>
		<input type="submit" class="button" name="submit" value="Back">&nbsp;
        <input type="submit" class="button" name="submit" value="New" <?php echo $button['New'];?>>
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
