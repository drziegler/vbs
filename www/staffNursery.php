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
	$mustExist  = array('picture'=>'Picture opt out');
	$notBlank   = array('first_name'=>'First Name', 'last_name'=>'Last Name', 'birthdate'=>'Birthdate');
	$selectLst  = array('shirt_size'=>'Shirt size', 'class'=>'Select classroom');

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
	$_POST = array_merge($form, $missing);	
		
	/* Check for options not selected */
	$selected = array_intersect_assoc($selectLst, $form);
	foreach ($selected as $element){
		$errMsg .= $element . ",";
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

$offset = (empty($_POST['offset'])) ? 0 : $_POST['offset'];
$validateError = false;
$errMsgText = "";
$numStudents =(empty($_POST['numStudents'])) ? 0 : $_POST['numStudents'];

if (!isset($_REQUEST['submit']) or (empty($_REQUEST['submit']))){
	if (DEBUG) print "Line " . __LINE__ . "<br>";
	/* Enter from registration menu.  Perform initial population */
	$_REQUEST['submit']='';
	$offset = 0;	/* Start at the first record */
}
elseif ($_REQUEST['submit']=='Redisplay'){
	/* We are coming from SELF after a new record insert.  Get the GET offset */
	if (DEBUG) print "Line " . __LINE__ . "<br>";
	/* We really do nothing here except skip the whole switch statement section */
}
else {
switch ($_REQUEST['submit']) {
	case "Back" :
		header("Location: staff.php");
		break;
	case "Done" :
		header("Location: " . HOME_PAGE);
		break;
	case "First" :
		$offset = 0;
		break;
	case "Next"  :
		$offset = $offset + 1;
		break;
	case "Previous" :
		$offset = $offset - 1;
		break;
	case "Last"  :
		$offset = $numStudents -1;
		break;
	case "New"   :
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
	case "Save" :
		if (validate($_POST)) {
			if (DEBUG) print "Line " . __LINE__ . "<br>";
			$errMsg = '';
			/* This is a new record to insert */
			$sql = "INSERT into students (family_id, first_name, last_name, birthdate, class, ";
			$sql .= "shirt_size, picture, registered, buddy, comments, confo, create_date, last_update) ";
			$sql .= "VALUES (%u,'%s','%s','%s','%s','%s','Y','%s','%s','%s','%s', now(), now())";
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
				$row_rsStudent['student_id'] = mysqli_insert_id($vbsDBi);
				$offset = ++$offset;		/* Advance by one so the new record displays */
				writeLog("Inserted student id as " . $sqlStmt);}
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
			/* Here we must redirect back to ourself to prevent a duplicate if the user refreshes the browser 
			Redisplay just forces the code past the switch statement as there is no Redisplay option
			Offset defines which record to display.  Since we added one, the offset will be one less than the total number of students*/
//@@		header("Location: student.php?submit=Redisplay&offset=".($_POST['numStudents']));
			header("Location: " . $_SERVER['PHP_SELF'] . "?submit=Redisplay");

		/********************************************************************************** 
		   We need to redirect to a GET to prevent multipe inserts if browser is refreshed
		   1) Increase the number of students in POST by 1 since we just added one.
		   2) Set the submit valie to "Last" so the last entry we made displays 
		 ***********************************************************************************/
//@@		header("Location: " . $_SERVER['PHP_SELF'] . "?submit=Last&numStudents=" . ($numStudents + 1));
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
			writeErr("Error writing update statement", "Switch:Unregister", $failedLine, $sqlErr);
		}
		break;
	case "Register":
	case "Update" :
		if (validate($_POST)){
			if (DEBUG) print "Line " . __LINE__ . "<br>";
			$sql = "UPDATE students SET first_name='%s', last_name='%s', birthdate='%s', 
					class='%s', shirt_size='%s', picture='%s', buddy='%s', 
					comments='%s', last_update=now()";
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
				writeErr("Error writing update statement", "Switch:Update", __LINE__, $sqlErr);
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
		if (DEBUG) print "Line " . __LINE__ . "<br>";
		$numStudents = 0;
	}
	else {
		if (DEBUG) print "Line " . __LINE__ . "<br>";
		$query_rsStudent = "SELECT * FROM students WHERE class='Staff Nursery' AND family_id=".$_SESSION['family_id'];
		writeLog($query_rsStudent);
		$all_rsStudent = mysqli_query($vbsDBi, $query_rsStudent);
		if ($all_rsStudent){
			if (DEBUG) print "Line " . __LINE__ . "<br>";
			$numStudents = mysqli_num_rows($all_rsStudent);
			if ($_REQUEST['submit']=='Redisplay') $offset = $numStudents-1;  /* Go to last record */	
			$query_limit_rsStudent = sprintf("%s LIMIT %d, %d", $query_rsStudent, $offset, $numStudents);
			$rsStudent = mysqli_query($vbsDBi, $query_limit_rsStudent);
			if ($rsStudent) {
				$row_rsStudent = mysqli_fetch_assoc($rsStudent); }
			else {
			if (DEBUG) print "Line " . __LINE__ . "<br>";
				$sqlErr = mysqli_error($vbsDBi);
				writeErr("Err LIMITing students", "Switch:Update", __LINE__, $sqlErr);
				writeLog($query_limit_rsStudent);
			}
		}
		else
		{
			if (DEBUG) print "Line " . __LINE__ . "<br>";
			$sqlErr = mysqli_error($vbsDBi);
			writeErr("Err selecting students", "Switch:Update", __LINE__, $sqlErr);
		}
	}
}


$query_rsClassList = "SELECT class FROM class_types WHERE class='Staff Nursery' ORDER BY disp_order";
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
	$registered = true;}
else{
	$registered = false;
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
<link href="css/textural.css" rel="stylesheet" type="text/css">
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="scripts/respond.min.js"></script>
</head>
<body>
<div id="Student" class="gridContainer">
	<div id="header"><h1>VBS - Staff Nursery</h1></div>
	<div id="status"><h2>
	<?php if ($registered) {?>
		Edit information and click update or unregister.
    <?php } else { ?>
		Edit information and click register.	        
	<?php } ?></h2>
    <h3><?php if ($validateError) echo $errMsgText;?></h3>
	</div>
	<div id="dataLayout">
	<form method="post" name="frmStudent" target="_self" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
	<table cellspacing="0">
		<tr><td class="label">*&nbsp;First Name:</td><td class="value"><input name="first_name" type="text" id="first_name" value="<?php echo $row_rsStudent['first_name']; ?>" maxlength="20"></td></tr>
		<tr><td class="label">*&nbsp;Last Name:</td><td class="value"><input name="last_name" type="text" value="<?php echo $row_rsStudent['last_name']; ?>" maxlength="20"></td></tr>
		<tr><td class="label">*&nbsp;Birthdate:</td><td class="value"><input name="birthdate" type="date" value="<?php echo $row_rsStudent['birthdate']; ?>"></td></tr>
		<tr><td class="label">*&nbsp;Class:</td><td class="value">
        <select name="class" id="select">
		<?php do {  ?>
			<option value="<?php echo $row_rsClassList['class']?>"<?php if (!(strcmp($row_rsClassList['class'], $row_rsStudent['class']))) {echo "selected=\"selected\"";} ?>>
			<?php echo $row_rsClassList['class']?></option>
		<?php
		} while ($row_rsClassList = mysqli_fetch_assoc($rsClassList));
		$rows = mysqli_num_rows($rsClassList);
		if($rows > 0) {
			mysqli_data_seek($rsClassList, 0);
			$row_rsClassList = mysqli_fetch_assoc($rsClassList);
		}
?>
        </select></td></tr>
    <tr><td class="label">*&nbsp;Shirt Size:</td><td class="value"><select name="shirt_size">
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
		<tr><td class="label">*&nbsp;Picture:</td><td class="value">
            <label><input type="radio" name="picture" id="pic-yes" value="Y" <?php if (!(strcmp($row_rsStudent['picture'],"Y"))) {echo "checked";} ?>>Yes</label>
            <label><input type="radio" name="picture" id="pic-no" value="N" <?php if (!(strcmp($row_rsStudent['picture'],"N"))) {echo "checked";} ?>>No</label>
    	</td></tr>
<!--        <tr class="hidden"><td class="label">Buddy:</td><td class="value"><input name="buddy" type="text" value="<?php echo $row_rsStudent['buddy']; ?>" maxlength="20"></td></tr> -->
        <tr><td class="label">Comments:</td><td class="value"><textarea name="comments" cols="" rows=""><?php echo $row_rsStudent['comments']; ?></textarea></td></tr>
       	<tr><td class="label">Status:</td><td class="value"><?php echo ($registered ? "Registered (#".$row_rsStudent['confo'].")" : "Not registered"); ?></td></tr>
        <tr><td>*&nbsp;required</td><td class="value">
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
    <input name="buddy" type="hidden" value="">
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
        <input type="submit" class="button" name="submit" value="New" <?php echo $button['New'];?>>&nbsp;
        <input type="submit" class="button" name="submit" value="Done">
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
