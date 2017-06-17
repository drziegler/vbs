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
	$mustExist  = array('picture'=>'Picture','age_group'=>'Over 21');
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
	$errMsgText = "Check items: " . trim($errMsg, ",");
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
	case STAFF_NURSERY :
		header("Location: staffNursery.php");
		break;	
	case "Back" :
		header(sprintf("Location: %s", HOME_PAGE));
		break;
	case "First" :
		if (DEBUG) print "Line " . __LINE__ . "-First<br>";
		$offset = 0;
		break;
	case "Next"  :
		if (DEBUG) print "Line " . __LINE__ . "-Next<br>";
		$offset = $offset + 1;
		break;
	case "Previous" :
		if (DEBUG) print "Line " . __LINE__ . "-Previous<br>";
		$offset = $offset - 1;
		break;
	case "Last"  :
		if (DEBUG) print "Line " . __LINE__ . "-Last<br>";
		$offset = $numStudents -1;
		break;
	case "New"   :
		if (DEBUG) print "Line " . __LINE__ . "-New<br>";
		/* Create a blank array */
		$row_rsStudent = array();
		$row_rsStudent['first_name'] = $row_rsStudent['last_name'] = $row_rsStudent['age_group'] = $row_rsStudent['classroom'] = '';
		$row_rsStudent['registered'] = $row_rsStudent['teach_with'] = $row_rsStudent['comments'] = $row_rsStudent['picture'] = '';
		$row_rsStudent['mon'] = $row_rsStudent['tue'] = $row_rsStudent['wed'] = $row_rsStudent['thur'] = $row_rsStudent['fri'] = "Y";
		$row_rsStudent['classroom'] = $row_rsStudent['craft'] = $row_rsStudent['kitchen'] = $row_rsStudent['anything'] = 'Y';
		$row_rsStudent['deleted']  = $row_rsStudent['confo'] = '';
		$row_rsStudent['shirt_size'] = '';
		$row_rsStudent['family_id'] = $_SESSION['family_id'];
		$row_rsStudent['staff_id'] = 0;
		break;
	case "Save" :
		if (DEBUG) print "Line " . __LINE__ . "<br>";
		if (validate($_POST)) {
			if (DEBUG) print "Line " . __LINE__ . "<br>";
			$errMsg = '';
			/* This is a new record to insert */
			$sql = "INSERT into staff (family_id, first_name, last_name, shirt_size, picture, registered, teach_with, confo, ";
			$sql .= "classroom, craft, kitchen, anything, mon, tue, wed, thur, fri, age_group, create_date, last_update)";
			$sql .= "VALUES (%u,'%s','%s','%s','%s','Y','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',now(),now())";
			$sqlStmt = 	sprintf($sql, 
				$_SESSION['family_id'],
				mysqli_real_escape_string($vbsDBi, $_POST['first_name']),
				mysqli_real_escape_string($vbsDBi, $_POST['last_name']),
				mysqli_real_escape_string($vbsDBi, $_POST['shirt_size']),
				mysqli_real_escape_string($vbsDBi,$_POST['picture']),
				mysqli_real_escape_string($vbsDBi, $_POST['teach_with']),
				$_SESSION['confoNo'],
				mysqli_real_escape_string($vbsDBi, $_POST['classroom']),
				mysqli_real_escape_string($vbsDBi, $_POST['craft']),
				mysqli_real_escape_string($vbsDBi, $_POST['kitchen']),
				mysqli_real_escape_string($vbsDBi, $_POST['anything']),
				mysqli_real_escape_string($vbsDBi, $_POST['mon']),
				mysqli_real_escape_string($vbsDBi, $_POST['tue']),
				mysqli_real_escape_string($vbsDBi, $_POST['wed']),
				mysqli_real_escape_string($vbsDBi, $_POST['thur']),
				mysqli_real_escape_string($vbsDBi, $_POST['fri']),
				mysqli_real_escape_string($vbsDBi, $_POST['age_group'])
				);
			if (mysqli_query($vbsDBi, $sqlStmt)){
				if (DEBUG) print "Line " . __LINE__ . "<br>";
				//@@ Check if we really need to do this!
				//@@$row_rsStudent['staff_id'] = mysqli_insert_id($vbsDBi);
				writeLog("Inserted new staff:" . $sqlStmt);
				/* Here we must redirect back to ourself to prevent a duplicate if the user refreshes the browser */
				header("Location: staff.php?submit=Redisplay");
			}
			else {
				$sqlErr = mysqli_error($vbsDBi);
				if (DEBUG) print "Line " . __LINE__ . " " . $sqlErr . "<br>";
				writeErr("Error writing update statement", "Switch:Save", __LINE__, $sqlErr);
			}
		}
		else{
			writeLog("Validation failed for " . $_POST['staff_id']);
			$validateError = true;
		}
		break;
	case "Unregister" :
		if (DEBUG) print "Line " . __LINE__ . " Unregister <br>";
		$sql = "UPDATE staff SET registered = 'N' WHERE staff_id = " . $_POST['staff_id'];
		$failedLine = __LINE__ - 1;
		if (mysqli_query($vbsDBi, $sql)){
			writeLog("Unregistered staff as " . $sql);
		}
		else {
			$sqlErr = mysqli_error($vbsDBi);
			writeErr("Error writing update statement", "Switch:Unregister", $failedLine, $sqlErr);
		}
		/* If count of staff for family id = 0, unregister any staff nursery students */
		$sql = "SELECT count(*) AS Total_Fam_Staff FROM staff WHERE family_id=" . $_SESSION['family_id'];
		$result = mysqli_query($vbsDBi, $sql);
		if (mysqli_fetch_assoc($result)==0){
			$sql = "UPDATE students SET registered='N' WHERE class='Staff Nursery' AND family_id=" . $_SESSION['family'];			
			if (mysqli_query($vbsDBi, $sql)){
				writeLog("Unregistered all staff nursery for family id " . $_SESSION['family_id'] . ". No volunteers from same family");
			}
		}
		break;
	case "Register":
		if (DEBUG) print "Line " . __LINE__ . "<br>";
		/* Use the Update code below in conjunction with register.  The user can change values on the screen so we can't just update the
		   registered flag.  We must update all the fields. */
	case "Update" :
		if (DEBUG) print "Line " . __LINE__ . "<br>";
		if (validate($_POST)){
			$sql = "UPDATE staff SET first_name='%s', last_name='%s', shirt_size='%s', picture='%s', teach_with='%s', comments='%s', age_group='%s', ";
			$sql .= "classroom='%s', craft='%s', kitchen='%s', anything='%s', mon='%s', tue='%s', wed='%s', thur='%s', fri='%s', last_update=now()";
			$sqlWhere = " WHERE staff_id = " . $_POST['staff_id'];
			$sqlStmt = sprintf($sql,
				mysqli_real_escape_string($vbsDBi, $_POST['first_name']),
				mysqli_real_escape_string($vbsDBi, $_POST['last_name']),
				mysqli_real_escape_string($vbsDBi, $_POST['shirt_size']),
				mysqli_real_escape_string($vbsDBi, $_POST['picture']),
				mysqli_real_escape_string($vbsDBi, $_POST['teach_with']),
				mysqli_real_escape_string($vbsDBi, $_POST['comments']),
				mysqli_real_escape_string($vbsDBi, $_POST['age_group']),
				mysqli_real_escape_string($vbsDBi, $_POST['classroom']),
				mysqli_real_escape_string($vbsDBi, $_POST['craft']),
				mysqli_real_escape_string($vbsDBi, $_POST['kitchen']),
				mysqli_real_escape_string($vbsDBi, $_POST['anything']),
				mysqli_real_escape_string($vbsDBi, $_POST['mon']),
				mysqli_real_escape_string($vbsDBi, $_POST['tue']),
				mysqli_real_escape_string($vbsDBi, $_POST['wed']),
				mysqli_real_escape_string($vbsDBi, $_POST['thur']),
				mysqli_real_escape_string($vbsDBi, $_POST['fri'])
			);
			if ($_POST['submit']=="Register") {
				if (DEBUG) print "Line " . __LINE__ . "<br>";
				/* Append the registered and confo columns to the sql statement */
				$sqlStmt .= ", registered='Y', confo='" . $_SESSION['confoNo'] . "' ";
			}
			$sqlStmt .= $sqlWhere;
			if (mysqli_query($vbsDBi, $sqlStmt)){
				if (DEBUG) print "Line " . __LINE__ . "<br>";
				writeLog("Updated staff as " . $sqlStmt);
			}
			else {
				$sqlErr = mysqli_error($vbsDBi);
				writeErr("Error update staff ", "Switch:Update", __LINE__, $sqlErr);
			}
		}
		else {
			$validateError = true;
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
	print "POST: <br>";
	print_r($_POST);
	$row_rsStudent = $_POST;
	}
else {
	if ($_REQUEST['submit']=="New"){
		if (DEBUG) print "Line " . __LINE__ . "-New<br>";
		$numStudents = 0;}
	else {
		if (DEBUG) print "Line " . __LINE__ . "-Validation OK: Redisplay<br>";
		$query_rsStudent = "SELECT * FROM staff WHERE family_id=".$_SESSION['family_id'];
		$all_rsStudent = mysqli_query($vbsDBi, $query_rsStudent);
		$numStudents = mysqli_num_rows($all_rsStudent);
		if ($_REQUEST['submit']=='Redisplay') $offset = $numStudents-1;  /* Go to last record */
		$query_limit_rsStudent = sprintf("%s LIMIT %d, %d", $query_rsStudent, $offset, $numStudents);
		$rsStudent = mysqli_query($vbsDBi, $query_limit_rsStudent);
		$row_rsStudent = mysqli_fetch_assoc($rsStudent);
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

$staffID = $row_rsStudent['staff_id'];

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
<title>VBS Staff</title>
<link href="css/boilerplate.css" rel="stylesheet" type="text/css">
<link href="css/layout.css" rel="stylesheet" type="text/css">
<link href="css/textural.css" rel="stylesheet" type="text/css">
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="scripts/vbsUtils.js"></script>
<script src="scripts/respond.min.js"></script>
</head>
<body>
<div id="Staff" class="gridContainer">
	<div><h1>VBS - Volunteers</h1></div>
	<div><h2>
	<?php if ($registered) {?>
		Edit information and update.
    <?php } else { ?>
		Edit information and register.	        
	<?php } ?>  </h2>
    <div><h3><?php if ($validateError) echo $errMsgText;?></h3></div>
	</div>
	<div id="dataLayout">
	<form method="post" name="frmStaff" target="_self" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
	<table cellspacing="0">
		<tr><td class="label">*&nbsp;First Name</td><td class="value"><input name="first_name" type="text" id="first_name" value="<?php echo $row_rsStudent['first_name']; ?>" maxlength="20"></td></tr>
		<tr><td class="label">*&nbsp;Last Name</td><td class="value"><input name="last_name" type="text" value="<?php echo $row_rsStudent['last_name']; ?>" maxlength="20"></td></tr>
		<tr><td class="label">*&nbsp;Availability</td><td class="value">
            <input type="checkbox" name="mon" value="Y" <?php echo (strcasecmp($row_rsStudent['mon'],"N")==0 ? "" : "checked")?>>Mo
            <input type="checkbox" name="tue" value="Y" <?php echo (strcasecmp($row_rsStudent['tue'],"N")==0 ? "" : "checked")?>>Tu
            <input type="checkbox" name="wed" value="Y" <?php echo (strcasecmp($row_rsStudent['wed'],"N")==0 ? "" : "checked")?>>We
            <input type="checkbox" name="thur" value="Y" <?php echo (strcasecmp($row_rsStudent['thur'],"N")==0 ? "" : "checked")?>>Th
            <input type="checkbox" name="fri" value="Y"  <?php echo (strcasecmp($row_rsStudent['fri'],"N")==0 ? "" : "checked")?>>Fr
        </td></tr>
		<tr><td class="label">*&nbsp;Preferences</td><td class="value">
	        <input type="checkbox" name="classroom" value="Y"  <?php echo (strcasecmp($row_rsStudent['classroom'],"N")==0 ? "" : "checked")?>>Classroom
			<input type="checkbox" name="craft" value="Y" <?php echo (strcasecmp($row_rsStudent['craft'],"N")==0 ? "" : "checked")?>>&nbsp;Craft
            <input type="checkbox" id="kitchen" name="kitchen" value="Y" <?php echo (strcasecmp($row_rsStudent['kitchen'],"N")==0 ? "" : "checked")?>>&nbsp;Kitchen
            <input type="checkbox" name="anything" value="Y" <?php echo (strcasecmp($row_rsStudent['anything'],"N")==0 ? "" : "checked")?>>&nbsp;Anything
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
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hPic')">Picture<span class="popuptext" id="hPic">May we take and post photos of you during VBS?</span></span></td><td class="value">
            <label><input type="radio" name="picture" id="pic-yes" value="Y" <?php echo (strcasecmp($row_rsStudent['picture'],"Y")==0 ? "checked" : ""); ?>>Yes</label>
            <label><input type="radio" name="picture" id="pic-no" value="N" <?php echo (strcasecmp($row_rsStudent['picture'],"N")==0 ? "checked" : "");?>>No</label>
    	</td></tr>
        <tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('clear')">Over&nbsp;18?<span class="popuptext" id="clear">Federal and state regulations require us to have clearances for volunteers over the age of 18. Answer this question to help us identify who requires clearances.</span></span></td><td class="value">
            <label><input type="radio" name="age_group" value="Adult" <?php echo (strcasecmp($row_rsStudent['age_group'],"Adult")==0 ? "checked" : "");?>>Yes</label>
            <label><input type="radio" name="age_group" value="Youth" <?php echo (strcasecmp($row_rsStudent['age_group'],"Youth")==0 ? "checked" : "");?>>No</label>
    	</td></tr>
        <tr>
          <td class="label"><span class="popup" onclick="myPopUp('hClass')">I want to help in my child's class<span class="popuptext" id="hClass">If you want to be in the same class as your child, enter the child's name in this space; otherwise you will be assigned to a different classroom.</span></span></td><td class="value"><input type="text" name="teach_with" placeholder="Your child's name and grade" value="<?php echo $row_rsStudent['teach_with'];?>"></td></tr>
        <tr><td class="label">Comments</td><td class="value"><textarea name="comments"><?php echo $row_rsStudent['comments']; ?></textarea></td></tr>
       	<tr><td class="label"><span class="popup" onclick="myPopUp('hStatus')">Status<span class="popuptext" id="hStatus">Status indicates if this staff member is registered. If the form was completed correctly and submitted, a confirmation number will appear after the status. You may also unregister.</span></span></td><td class="value"><?php echo ($registered ? "Registered (#".$row_rsStudent['confo'].")" : "Not registered"); ?></td></tr>
        <tr><td>*&nbsp;required  <span class="popup" onclick="myPopUp('help')">Help available<span class="popuptext" id="help">Use this form to register volunteers for the week of VBS.  Volunteers must be in 7th grade or older.  Click the underlined labels for detailed popup help. Click again to close it.</span></span></td><td class="value">
   		<?php if ($registered) { ?>
			<input type="submit" name="submit" value="Update">&nbsp;&nbsp;<input type="submit" name="submit" value="Unregister">&nbsp;&nbsp;<input type="submit" name="submit" value="<?php echo STAFF_NURSERY?>">
		<?php } else { if ($staffID==0) { ?>
			<input type="submit" name="submit" value="Save">
        <?php } else { ?>
        	<input type="submit" name="submit" value="Register">
        <?php } } ?>
		</td></tr>
	</table>
    <input name="staff_id" type="hidden" value="<?php echo $row_rsStudent['staff_id']; ?>">
    <input name="family_id" type="hidden" value="<?php echo $row_rsStudent['family_id']; ?>">
    <input name="registered" type="hidden" value="<?php echo $row_rsStudent['registered']; ?>">
    <input name="deleted" type="hidden" value="<?php echo $row_rsStudent['deleted']; ?>">
    <input name="confo" type="hidden" value="<?php echo $row_rsStudent['confo']; ?>">
    <input name="offset" type="hidden" value="<?php echo $offset;?>">
    <input name="numStudents" type="hidden" value="<?php echo $numStudents;?>">
	<div id="buttonGroup" class="buttonGroup center">
    	<span>Displaying staff member <?php echo (($numStudents>0)?$offset+1:0)?> of <?php echo $numStudents ?></span><br>

		<input type="submit" name="submit" class="button" value="First"<?php echo $button['First']?>>&nbsp;
		<input type="submit" name="submit" class="button" value="Previous"<?php echo $button['Previous']?>>&nbsp;
		<input type="submit" name="submit" class="button" value="Next"<?php echo $button['Next']?>>&nbsp;
		<input type="submit" name="submit" class="button" value="Last"<?php echo $button['Last']?>><br>
		<input type="submit" name="submit" class="button" value="Back"<?php echo $button['Back']?>>&nbsp;
        <input type="submit" name="submit" class="button" value="New"<?php echo $button['New']?>>
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
