<?php 
session_start();

if (empty($_SESSION['family_id'])){
    /* Send the user to the search page if no family is selected */
    header("Location: search.php");
}

/* QuickSave does no validation and is used then registered is set to 'N' */
function quickSave(){
    global $vbsDBi;

    if (DEBUG) print "Quick Save<br>";
    if (DEBUG) print_r($_POST); print "<br>";
    if (empty($_POST['student_id'])) return;       /* New student condition */
    
    
    /* Note:  For all Inserts and Updates ...
       All Checkboxes must use isset to determine state and explicitly set Y | N
       All Radiobuttons must use isset to determine state and use variable content or N
       This is to avoid resetting the buttons' state back to that from the data base
    */
    $sql = "UPDATE students SET first_name='%s', last_name='%s', birthdate='%s',class='%s',shirt_size='%s',
           picture='%s',registered='%s',comments='%s',last_update=now()";
    $sqlWhere = " WHERE student_id = " . $_POST['student_id'];
    $sqlStmt = sprintf($sql,
        mysqli_real_escape_string($vbsDBi, $_POST['first_name']),
        mysqli_real_escape_string($vbsDBi, $_POST['last_name']),
        mysqli_real_escape_string($vbsDBi, $_POST['birthdate']),
        $_POST['class'],
        (isset($_POST['shirt_size']) ? $_POST['shirt_size'] : ''),
        (isset($_POST['picture'])    ? $_POST['picture']    : ''),
        (isset($_POST['registered']) ? $_POST['registered'] : ''),
        mysqli_real_escape_string($vbsDBi, $_POST['comments'])
    );
    $sqlUpdate = $sqlStmt . $sqlWhere;

    
    if (mysqli_query($vbsDBi, $sqlUpdate)){
        if (DEBUG) print "Line " . __LINE__ . "-Updated Staff Nursery record ".$_POST['student_id']."<br>";
        writeLog(FILE_NAME . __LINE__ . "-" . $sqlUpdate);
    }
    else {
        if (DEBUG) print "Line " . __LINE__ . "  Quick save update error.  See log file.<br>";
        $sqlErr = mysqli_error($vbsDBi);
        writeErr("Error:", "StaffNursery:QuickSave", FILE_NAME . __LINE__, $sqlErr);
        writeErr("SQL Statement:", __FUNCTION__, FILE_NAME . __LINE__, $sqlUpdate);
    }
    
    return;
    
}

function validate($form){
	/* 	Validate the form.
		Return true if the form passed validation and is ready to save.
		Return false if the form failed validation and cannot be saved.
	*/
	if (DEBUG) print 'Line '.__LINE__.' Entering validate()<br>';
    $error = FALSE;
	global $errMsgText;
	$errMsg = "";		/* clear out any previous messages */
	
	/* Mandatory form elements */	
	$mustExist  = array('picture'=>'Picture opt out missing');
	$notBlank   = array('first_name'=>'Need first name', 'last_name'=>'Need last name', 'birthdate'=>'Need birthdate');

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
	$_POST = array_merge($form, $missing);	
		

	/* This assigns the error text to a variable outside the function */
	$errMsgText = trim($errMsg, ",");
	return !$error;
}


function check4dupes($form){
    global $vbsDBi;
    
	$sql = "Select count(*) from students ";
	$sql .= "WHERE first_name='" . $form['first_name'] . "'";
	$sql .= " AND last_name='" . $form['last_name'] . "'";
	$sql .= " and birthdate='" . $form['birthdate'] . "'";
	$recCount = mysqli_query($vbsDBi, $sql);
	
}

/*******************************    MAIN  ***********************************/
require_once('Connections/vbsDB.php');
include('vbsUtils.inc');
define('FILE_NAME', '[STAFF_NURSERY] ');
$validateError = FALSE;
$yesVal = $yesChk = $noChk = $fldEnabled = $errMsgText = "";
$numStudents =(empty($_POST['numStudents'])) ? 0 : $_POST['numStudents'];

/* Turn on the button display by default */
$button['New'] = '';
$button['Home'] = '';
$button['Back'] = '';
$button['NextPage'] = '';

if (empty($_REQUEST['submit'])){
    /* Enter from another page.  Perform initial population */
    if (DEBUG) print "Line " . __LINE__ . "<br>";
    $_REQUEST['submit']='';		/* Set this to blank to prevent unset errors */
    $offset = 0;				/* Display the first record of the series */
}
elseif ($_REQUEST['submit']=='Redisplay' || $_REQUEST['submit']=='Cancel'){
    /* We really do nothing here except skip the whole switch statement section */
    if (DEBUG) print "Line " . __LINE__ . "<br>";
    $offset = 0;
}
else {
    /* We are rePOSTing from _SELF, so the 'submit' action will be populated.
     * Take the action defined by each case statment for the action in 'submit' value.
     */
    $offset = $_POST['offset'];

switch ($_REQUEST['submit']) {
    case NEW_BUTTON   :
        if (DEBUG) print 'Line ' . __LINE__ . ' New Button<br>';
        /* Create a blank array */
        $row_rsStudent = array();
        $row_rsStudent['first_name'] = $row_rsStudent['birthdate'] = $row_rsStudent['age'] = $row_rsStudent['class'] = '';
        $row_rsStudent['registered'] = $row_rsStudent['comments'] = $row_rsStudent['picture'] = '';
        $row_rsStudent['deleted'] = $row_rsStudent['last_name'] = $row_rsStudent['shirt_size'] = '';
        $row_rsStudent['family_id'] = $_SESSION['family_id'];
        $row_rsStudent['student_id'] = 0;
        $numStudents = 0;
        
        $fldEnabled = '';
        
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
        /* Only happens on a new record */

        //if (validate($_POST) && (check4dupes($_POST) === NO_DUPES)) {
        if (validate($_POST)) {
            if (DEBUG) print "Line " . __LINE__ . "SAVE passed validation<br>";
            $errMsg = '';
            /* This is a new record to insert */
            $sql = "INSERT into students (family_id, first_name, last_name, birthdate, class, ";
            $sql .= "shirt_size, picture, registered, comments, create_date, last_update) ";
            $sql .= "VALUES (%u,'%s','%s','%s','%s','%s','%s','%s','%s', now(), now())";
            $sqlStmt = 	sprintf($sql,
                $_SESSION['family_id'],
                mysqli_real_escape_string($vbsDBi, $_POST['first_name']),
                mysqli_real_escape_string($vbsDBi, $_POST['last_name']),
                mysqli_real_escape_string($vbsDBi, $_POST['birthdate']),
                mysqli_real_escape_string($vbsDBi, $_POST['class']),
                mysqli_real_escape_string($vbsDBi, $_POST['shirt_size']),
                (isset($_POST['picture'])        ? $_POST['picture']    : ''),
                (isset($_POST['registered'])     ? $_POST['registered'] : ''),
                mysqli_real_escape_string($vbsDBi, $_POST['comments'])
                );
            
            /* Get the new student id after insert */
            if (mysqli_query($vbsDBi, $sqlStmt)){
                if (DEBUG) print "Line " . __LINE__ . "<br>";
                $row_rsStudent['student_id'] = mysqli_insert_id($vbsDBi);
                writeLog(FILE_NAME . __LINE__ . "-Inserted student id as " . $sqlStmt);
                
                /* Here we must redirect back to ourself to prevent a duplicate if the user refreshes the browser
                 Redisplay just forces the code past the switch statement as there is no Redisplay option
                 */
                header("Location: " . $_SERVER['PHP_SELF'] . "?submit=Redisplay");
            }
            else {   /* ERROR inserting new student */
                if (DEBUG) print "Line " . __LINE__ . "<br>";
                $sqlErr = mysqli_error($vbsDBi);
                writeErr("Error writing insert statement", "Switch:Save", FILE_NAME . __LINE__, $sqlErr);
            }
        }
        else {
            /* This else can only branch if this is a new student who failed validation */
            if (DEBUG) print "Line " . __LINE__ . " Save failed validation<br>";
            writeLog(FILE_NAME . __LINE__ . "-Validation failed for new staff nursery " . $_POST['first_name'] . ' ' . $_POST['last_name']);
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
    default :
        /* Any other 'submit' condition requires an update of the record.
         * If the registered flag is not set or equal to 'N', then only a quickUpdate() is performed without validation.
         * If registered flag is set and not 'N', then a validate is performed before the update.
         * If the record updates sucessfully, then we redirect or rePOST as per the 'submit' action.
         */
        if (isset($_POST['registered']) && !($_POST['registered']=='N')) {
            /* Validate only records where the registered value is true.  No use validating someone who is not attending! */
            if (validate($_POST)){           /* Validation passed */
                if (DEBUG) print "Line " . __LINE__ . " Update<br>";
                
                $sql = "UPDATE students SET first_name='%s', last_name='%s', birthdate='%s', class='%s', 
                        shirt_size='%s', picture='%s', registered='%s',comments='%s', last_update=now()";
                $sqlWhere = " WHERE student_id = " . $_POST['student_id'];
                $sqlStmt = sprintf($sql,
                    mysqli_real_escape_string($vbsDBi, $_POST['first_name']),
                    mysqli_real_escape_string($vbsDBi, $_POST['last_name']),
                    mysqli_real_escape_string($vbsDBi, $_POST['birthdate']),
                    $_POST['class'],
                    $_POST['shirt_size'],
                    (isset($_POST['picture'])    ? $_POST['picture']    : ''),
                    (isset($_POST['registered']) ? $_POST['registered'] : ''),
                    mysqli_real_escape_string($vbsDBi, $_POST['comments'])
                );
                $sqlStmt .= $sqlWhere;
                if (DEBUG) print 'Line '.__LINE__." $sqlStmt";
                
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
            else {    /* Validation failed */
                if (DEBUG) print "Line " . __LINE__ . "-Failed validaton<br>";
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
        switch ($_REQUEST['submit']) {
            case FIRST_RECORD :
                $offset = 0;
                break;
            case PREVIOUS_RECORD :
                $offset = $offset - 1;
                break;
            case NEXT_RECORD :
                $offset = $offset + 1;
                break;
            case LAST_RECORD :
                $offset = $numStudents -1;
                break;
            case HOME_BUTTON :
                header("Location: " . HOME_PAGE);
                break;
            case NEXT_PAGE :
                header("Location: " . SUMMARY_PAGE);
                break;
            case PREVIOUS_BUTTON :
                header("Location: " . STAFF_PAGE);
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
    /* On validation error, restore the submitted values to the row_rsStudent array for redisplay */
    $row_rsStudent = $_POST;
    /* Need to reset the registered value variables so the proper state is maintained and displayed */
    $yesVal = ($row_rsStudent['registered']=='C') ? 'C' : 'Y';
    $yesChk = ($row_rsStudent['registered']=='Y' || $row_rsStudent['registered']=='C') ? ' checked ' : '';
    $noChk  = ($row_rsStudent['registered']=='N') ? ' checked ' : '';
}
else {  /* PASSED validation */
    if ($_REQUEST['submit']==NEW_BUTTON){
        if (DEBUG) print "Line " . __LINE__ . "-New<br>";
        $numStudents = 0;
        $fldEnabled = '';
    }
    else {
        if (DEBUG) print 'Line ' . __LINE__ . ' Passed validation<br>';
        $query_rsStudent = "SELECT * FROM students WHERE class='Staff Nursery' AND family_id=".$_SESSION['family_id'];
        writeLog(FILE_NAME . __LINE__ . '-' . $query_rsStudent);
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
        if ($numStudents==0) {
            $_REQUEST['submit']=NEW_BUTTON;
            /* If there are no students on record, disable the input fields.  User required to press new button */
            $fldEnabled=' disabled';
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

$query_rsStudentShirtList = "SELECT shirt_size FROM list_shirts WHERE shirt_size='Childs 6-8' ORDER BY disp_order ";
$rsStudentShirtList = mysqli_query($vbsDBi, $query_rsStudentShirtList);
$row_rsStudentShirtList = mysqli_fetch_assoc($rsStudentShirtList);
$totalRows_rsStudentShirtList = mysqli_num_rows($rsStudentShirtList);

$_SESSION['student_id'] = $row_rsStudent['student_id'];
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
<title>VBS Staff Nursery</title>
<!--  <link href="css/boilerplate.css" rel="stylesheet" type="text/css">  -->
<link href="css/layout.css" rel="stylesheet" type="text/css">
<script src="scripts/vbsUtils.js"></script>
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="scripts/respond.min.js"></script>
</head>
<body>
<div id="Find" class="gridContainer">
	<h1>Staff Nursery</h1>
	<div id="dataLayout">
	<div id="Student">
	<form method="post" name="frmStudent" target="_self" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
	<table>
		<tr><td colspan="2" class="center  
		<?php if ($validateError) { ?>error"><?php echo $errMsgText; }
			  else {?>">Edit information and click save or update.<?php } ?>
		</td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hAtt')">In Nursery?<span class="popuptext" id="hAtt">Select yes if <?php echo (empty($row_rsStudent['first_name']) ? "this child" : $row_rsStudent['first_name']);?> will be in the staff nursery in <?php echo date("Y");?>; otherwise select No.</span></span></td>
		<td class="value">
			<label><input type="radio" name="registered" id="reg-yes" value="<?php echo $yesVal?>" <?php echo $yesChk . $fldEnabled?> > Yes</label>
            <label><input type="radio" name="registered" id="reg-no" value="N" <?php echo $noChk . $fldEnabled?>> No</label>
		</td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hFirst')">First Name<span class="popuptext" id="hFirst">Enter your child's first name exactly as you want it to appear on name tags, project labels, etc.  This includes capitalization and any punctuation you require.</span></span></td><td class="value"><input name="first_name" type="text" id="first_name" value="<?php echo $row_rsStudent['first_name']; ?>" maxlength="20" <?php echo  $fldEnabled?>></td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hLast')">Last Name<span class="popuptext" id="hLast">Enter your child's last name exactly as you want it to appear on name tags, project labels, etc.  This includes capitalization and any punctuation you require.</span></span></td><td class="value"><input name="last_name" type="text" value="<?php echo $row_rsStudent['last_name']; ?>" maxlength="20" <?php echo  $fldEnabled?>></td></tr>
		<tr><td class="label">*&nbsp;Birthdate:</td><td class="value"><input name="birthdate" type="date" value="<?php echo $row_rsStudent['birthdate'];?>" <?php echo  $fldEnabled?>></td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hGrade')">Class<span class="popuptext" id="hGrade">Not much to select here.  Since you are signing your child up for Staff Nursery, we've already picked that for you.</span></span></td><td class="value">
        <select name="class" id="select" <?php echo  $fldEnabled?>>
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
    <tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hShirt')">Shirt Size<span class="popuptext" id="hShirt">We selected the only size appropriate for staff nursery children.  T-shirts are only available to those who register before <?php echo VBS_SHIRT_DEADLINE_MMDDYYYY ?>.</span></span></td><td class="value"><select name="shirt_size" <?php echo  $fldEnabled?>>
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
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hPic')">Picture<span class="popuptext" id="hPic">May we take and post photos of your child during VBS?</span></span></td><td class="value">
            <label><input type="radio" name="picture" id="pic-yes" value="Y" <?php if (!(strcasecmp($row_rsStudent['picture'],"Y"))) {echo "checked";} echo $fldEnabled;?>>Yes</label>
            <label><input type="radio" name="picture" id="pic-no" value="N" <?php if (!(strcasecmp($row_rsStudent['picture'],"N"))) {echo "checked";} echo $fldEnabled;?>>No</label>
    	</td></tr>
        <tr><td class="label"><span class="popup" onclick="myPopUp('sComment')">Comments<span class="popuptext" id="sComment">Enter comments here that are specifically related to this child. Allergies, medications, preferred nap times and other important information should be included.</span></span></td><td class="value"><textarea name="comments" cols="" rows="" <?php echo $fldEnabled;?>><?php echo $row_rsStudent['comments']; ?></textarea></td></tr>
        <tr><td>*&nbsp;required   <span class="popup" onclick="myPopUp('help')">Help available<span class="popuptext" id="help">Use this form to register a child in the staff nursery.  To engage the services of the nursery, an adult in the family must be a volunteer.  Click the underlined labels for detailed field level help.  Click the pop-up box to close it.</span></span></td><td class="value">
		<?php if ($fldEnabled=='') {if ($_REQUEST['submit']==NEW_BUTTON) { ?>
			<input type="submit" name="submit" value="Save">&nbsp;
			<input type="submit" name="submit" value="Cancel">
        <?php } else { ?>			
			  <input type="submit" name="submit" value="Update">
        <?php } } ?>
		</td></tr>
    	<tr><td colspan='2' class='center'>
    	    <input name="student_id" type="hidden" value="<?php echo $row_rsStudent['student_id']; ?>">
            <input name="family_id" type="hidden" value="<?php echo $row_rsStudent['family_id']; ?>">
            <input name="deleted" type="hidden" value="<?php echo $row_rsStudent['deleted']; ?>">
            <input name="offset" type="hidden" value="<?php echo $offset;?>">
            <input name="numStudents" type="hidden" value="<?php echo $numStudents;?>">
            <div id="buttonSubGroup">
		   	Displaying student <?php echo (($numStudents>0)?$offset+1:0)?> of <?php echo $numStudents ?><br>
    			<input type="submit" class="button" name="submit" value="First" <?php echo $button['First'];?>>&nbsp;
    			<input type="submit" class="button" name="submit" value="Previous" <?php echo $button['Previous'];?>>&nbsp;
    			<input type="submit" class="button" name="submit" value="Next" <?php echo $button['Next'];?>>&nbsp;
    			<input type="submit" class="button" name="submit" value="Last" <?php echo $button['Last'];?>>&nbsp;&nbsp;&nbsp;
    			<input type="submit" class="button" name="submit" value="<?php echo NEW_BUTTON?>" <?php echo $button['New'];?>>
			</div>
		</td></tr>
	</table>
	<div id="buttonSubGroup" class="center">
		<input type="submit" class="button" name="submit" value="<?php echo HOME_BUTTON?>">&nbsp;
		<input type="submit" class="button" name="submit" value="<?php echo PREVIOUS_BUTTON?>">&nbsp;
		<input type="submit" class="button" name="submit" value="<?php echo NEXT_PAGE ?>">
	</div>
  </form></div>
  </div>
</div>
</body>
</html>
<?php
@mysqli_free_result($rsStudent);
@mysqli_free_result($rsClassList);
@mysqli_free_result($rsStudentShirtList);
?>
