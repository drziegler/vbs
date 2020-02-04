<?php 
session_start();


if (empty($_SESSION['family_id'])){
	/* Send the user to the search page if no family is selected */
	header("Location: search.php");
}

function quickSave(){
	global $vbsDBi;
	if (DEBUG) {
	    print 'Line '.__LINE__.' Quick Save. $_POST = ';
		print_r($_POST);
		print "<br>";
	}
	
	if (empty($_POST['staff_id'])) return;       /* New staff condition */
	
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
    $sqlUpdate .= ",last_update=now() ";
    $sqlUpdate .= " WHERE staff_id = " . $_POST['staff_id'];
    mysqli_real_escape_string($vbsDBi, $sqlUpdate);

	if (mysqli_query($vbsDBi, $sqlUpdate)){
		if (DEBUG) print 'Line '.__LINE__ .'  Updated Staff record '.$_POST['staff_id'].'<br>';
		writeLog(FILE_NAME . $sqlUpdate);
	}
	else {
		if (DEBUG) print 'Line '.__LINE__."  Update error in Quick save.  See log file.<br>";
		$sqlErr = mysqli_error($vbsDBi);
		writeErr(FILE_NAME . "Error:", "Staff:QuickSave", __LINE__, $sqlErr);
		writeErr(FILE_NAME . "SQL Statement:", __FUNCTION__, __LINE__, $sqlUpdate);
	}

	return;
	
}


function validate($form){
	/* 	Validate the form.
		Return TRUE if the form passed validation and is ready to save.
		Return FALSE if the form failed validation and cannot be saved.
	*/
    If (DEBUG) print 'Line '.__LINE__.'  Entering validate()<br>';
    $error = FALSE;
	global $errMsgText;
	$errMsg = "";		/* clear out any previous messages */

	/* Mandatory form elements */	
	$mustExist  = array('picture'=>'Indicate picture','age_group'=>'Indicate age group','registered'=>'Select \'Yes\' if helping.');
	$notBlank   = array('first_name'=>'Enter first name', 'last_name'=>'Enter last name');
	$selectedLists  = array('shirt_size'=>'Select shirt size');
	/* Arrays for looping through check boxes */
	$chkDays = array("mon"=>1, "tue"=>2, "wed"=>3,"thur"=>4,"fri"=>5);
	$chkAct  = array("classroom"=>1,"craft"=>2,"kitchen"=>3,"anything"=>4);
	
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
	/* Add the missing elements back into the array to avoid display errors */
	$_POST = array_merge($_POST, $missing);	
	
	/* Check for options not selected */
	$selected = array_intersect_key($form,$selectedLists);
	foreach ($selected as $key=>$value){
		if (DEBUG) print 'Line '.__LINE__ . " Selected key: " . $key . "-" . $value ."<br>";
		if (contains_substr($value, "Select")){
			$error = TRUE;
			$errMsg .= $selectedLists[$key] . ",";
		}
	}

	/* Require at least one element of the set for check boxes.  */
	if (count(array_intersect_key($chkDays, $form))==0){
	    if (DEBUG) print "No days selected";
	    $error = TRUE;
	    $errMsg .= " Select availability,";
	}
	if (count(array_intersect_key($chkAct, $form))==0){
	    if (DEBUG) print "No areas selected";
	    $error = TRUE;
	    $errMsg .= " Select a preference";
	}
	
	/* This assigns the error text to a variable outside the function */
	$errMsgText = trim($errMsg, ",");
	return !$error;
}
function check4dupes($form){
	
    if (DEBUG) print 'LINE '.__LINE__.' Entering check4dupes()<br';
    if (DEBUG) print 'Line '.__LINE__.' $row_rsStudent[ ] is_array = ' . (is_array($row_rsStudent)?'TRUE':'FALSE') . '<br>';
	$sql = "Select count(*) from staff ";
	$sql .= "WHERE first_name='" . $form['first_name'] . "'";
	$sql .= " AND last_name='" . $form['last_name'] . "'";
	//$sql .= " and birthdate='" . $form['birthdate'] . "'";
	$recCount = mysqli_query($vbsDBi, $sql);
	
}

/*
 * This function returns true if we should display the staff nursery registration page.
 * There are two (2) conditions where this should display.
 * 1. Existing staff nursery entries exist for the current family_id or
 * 2. One of the registered staff for this family_id has the staff nursery box checked.
 * If either condition is true, then the function returns true; otherwise false.
 */
function gotoStaffNursery(){
    print 'Line '.__LINE__.' Entering gotoStaffNursery()<br>';
    global $vbsDBi;
    $displayStaffNurseryPage = FALSE;
    
    /* Check for existing Staff Nursery records that may required update or edit */
    $sql = "Select count(*) from students WHERE class='Staff Nursery' AND family_id = " . $_SESSION['family_id'];
    $result = mysqli_query($vbsDBi, $sql);
    $recCount = mysqli_fetch_row($result);
    writelog(FILE_NAME . __LINE__ . "-Staff Nursery Record count (existing): " . $recCount[0]);
    if ($recCount[0] == 0){
        /* Now count staff where need staff nursery is checked */
        $sql = "Select count(*) from staff WHERE nursery='Y' AND family_id = " . $_SESSION['family_id'];
        $result = mysqli_query($vbsDBi, $sql);
        $recCount = mysqli_fetch_row($result);
        writelog(FILE_NAME . __LINE__ . "-Staff Nursery Record count (by flag): " . $recCount[0]);
        if (!$recCount[0]==0){
            $displayStaffNurseryPage=TRUE;
        }
    }
    else {
        $displayStaffNurseryPage = TRUE;        
    }
    
	return $displayStaffNurseryPage;

}

/* * * * * * * * * * * * * * * * * *  MAIN * * * * * * * * * * * * * * * * * * * * * * * */
require_once('Connections/vbsDB.php');
include('vbsUtils.inc');
define("FILE_NAME", '[STAFF] ');

$validateError = FALSE;
$staffNurseryExists = FALSE;
$yesVal = $yesChk = $noChk = $fldEnabled = $errMsgText = "";
$numStudents =(empty($_POST['numStudents'])) ? 0 : $_POST['numStudents'];

/* Turn on the button display by default */
$button['New'] = '';
$button['Home'] = '';
$button['Back'] = '';
$button['NextPage'] = '';


if (empty($_REQUEST['submit'])){
    /* Entering from another page.  $_REQUEST[submit] will be empty. Perform initial population */
    if (DEBUG) print 'Line '.__LINE__ . ' Coming from another page<br>';
    $_REQUEST['submit']='';		/* Set this to blank to prevent unset errors */
    $offset = 0;				/* Display the first record of the series */
}
elseif ($_REQUEST['submit']=='Redisplay' || $_REQUEST['submit']=='Cancel'){
    /* We do nothing here except skip the whole case section */
    if (DEBUG) print "Line " . __LINE__ . " Redisplay | Cancel<br>";
	$offset=0;
}
else {
    /* We are rePOSTing from _SELF, so the 'submit' action will be populated.
     * Take the action defined by each case statment for the action in 'submit' value.
     */
    $offset = $_POST['offset'];
    if (DEBUG) print "Offset: at line " . __LINE__ . " is " . $offset . "<br>";
switch ($_POST['submit']) {
	case NEW_BUTTON :
	    if (DEBUG) print 'Line: ' . __LINE__ . '-NEW Staff<br>';
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
	case "Save" :                  /* ONLY HAPPENS ON A NEW RECORD */
		if (validate($_POST)) {
		    if (DEBUG) print "Line " . __LINE__ . " Save - passed validation<br>";
		    $errMsg = '';
			/* This is a new record to insert */
			$sql = "INSERT into staff (family_id, first_name, last_name, shirt_size, picture, registered, teach_with, confo, ";
			$sql .= "classroom, nursery, craft, kitchen, anything, mon, tue, wed, thur, fri, age_group, create_date, last_update)";
			$sql .= "VALUES (%u,'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',now(),now())";
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
			/* Get the new staff id after insert */
			writelog(FILE_NAME . __LINE__ . "-" . $sqlStmt);
			if (mysqli_query($vbsDBi, $sqlStmt)){
			    if (DEBUG) print "Line " . __LINE__ . "<br>";
			    $row_rsStudent['staff_id'] = mysqli_insert_id($vbsDBi);
			    if (DEBUG) print "Local Offset is " . $offset . "<br>";
			    if (DEBUG) print "POST Offset is " . $_POST['offset'] . "<br>";
			    if (DEBUG) print "NumStaff is " . $_POST['numStudents'] . "<br>";
			    writeLog(FILE_NAME . __LINE__ . "-Student id inserted as " . $sqlStmt);
			    
			    /* Here we must redirect back to ourself to prevent a duplicate if the user refreshes the browser
			     * Redisplay just forces the code past the switch statement as there is no Redisplay option
			     */
			    header("Location: staff.php?submit=Redisplay");
			}
			else {
			    if (DEBUG) print "Line " . __LINE__ . "<br>";
			    $sqlErr = mysqli_error($vbsDBi);
			    writeErr(FILE_NAME . __LINE__ . "-Error writing insert statement", "Save", __LINE__, $sqlErr);
			}
		}
		else{     /* Code can branch here only if new staff member is not validated */
		    if (DEBUG) print "Line " . __LINE__ . " Save - failed validation<br>";
		    writeLog(FILE_NAME . __LINE__ . "-Validation failed for " . $_POST['first_name'] . ' ' . $_POST['last_name']);
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
                $sql = "UPDATE staff SET first_name='%s', last_name='%s', shirt_size='%s', picture='%s', registered='%s',teach_with='%s', comments='%s', age_group='%s', ";
                $sql .= "classroom='%s',nursery='%s',craft='%s', kitchen='%s', anything='%s', mon='%s', tue='%s', wed='%s', thur='%s', fri='%s', ";
                $sql .= "last_update=now()";
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
                        (isset($_POST['nursery'])    ? 'Y' : 'N'),
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
				if (mysqli_query($vbsDBi, $sqlStmt)){   /* Updated GOOD */
				    if (DEBUG) print "Line " . __LINE__ . "-Updated Staff<br>";
				    writeLog(FILE_NAME . __LINE__ . "-Updated staff as " . $sqlStmt);
				}
				else {                                  /* Updated FAILED */
				    if (DEBUG) print "Line " . __LINE__ . "Update error.  See log file.<br>";
				    $sqlErr = mysqli_error($vbsDBi);
				    writeErr("Error writing update statement", "Switch:Update", FILE_NAME . __LINE__, $sqlErr);
				    writeErr("SQL Statement: ", "Switch:Update", FILE_NAME . __LINE__, $sqlStmt);
				}
			}
			else {  /* $_POST Record Validation FAILED */
			    if (DEBUG) print "Line " . __LINE__ . "-Failed validaton<br>";
			    writeLog(FILE_NAME . __LINE__ . "-Validatition failed on update");
			    $validateError = TRUE;
			    /* On error, break out of outer select case statement.
			     * This prevents moving to another page on failure of a
			     * registered staff.   All the pagination in the next
			     * case statement will be skipped. */
			    break;
			}
		}
		else {
            /* Registered flag is either 1) not set or 2) set to 'N'.  Validation was not performed.
             * Do a quickSave to preserve the screen values.  This fixes an issue where changes
             * revert to the database values when registered is set to 'N' or not set at all.   Fixed 4-8-19
             */
            /* Do not move QuickSave() here as it will execute upon entry from another form
             The switch statement below ensures it runs only when called from itself.
             4-7-19 : Restesting this logic. Do we need to quickSave because we just updated above?  */
            quickSave();
		}
		/* Now handle the pagination */
		switch ($_REQUEST['submit']) {
			case FIRST_RECORD :
				if (DEBUG) print __LINE__ . "-First<br>";
				$offset = 0;
				break;
			case PREVIOUS_RECORD :
				if (DEBUG) print __LINE__ . "-Previous<br>";
				$offset = $offset - 1;
				break;
			case NEXT_RECORD :		
				if (DEBUG) print __LINE__ . "-Next";
				$offset = $offset + 1;
				break;
			case LAST_RECORD :
				if (DEBUG) print __LINE__ . "-Last";
				$offset = $numStudents -1;
				break;
			case HOME_BUTTON :
				header("Location: " . HOME_PAGE);
				break;
			case NEXT_PAGE :
			    writeLog(FILE_NAME . __LINE__ .' NEXT_PAGE case processed where countStaffNursery = ') . gotoStaffNursery();
				header("Location: " . (gotoStaffNursery() ? STAFF_NURSERY_PAGE : SUMMARY_PAGE));
				break;
			case PREVIOUS_BUTTON :
			    quickSave();
				header("Location: " . STUDENT_PAGE);
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

if ($validateError){        /* FAILED validation */
	/* Restore the submitted values to redislay for fixing */
	$row_rsStudent = $_POST;
	/* Need to reset the registered value variables so the proper state is maintained and displayed */
	$yesVal = ($row_rsStudent['registered']=='C') ? 'C' : 'Y';
	$yesChk = ($row_rsStudent['registered']=='Y' || $row_rsStudent['registered']=='C') ? ' checked ' : '';
	$noChk  = ($row_rsStudent['registered']=='N') ? ' checked ' : '';
	
	}
else {                      /* PASSED validation */
	if ($_REQUEST['submit']==NEW_BUTTON){
		if (DEBUG) print 'Line '.__LINE__ . " New record<br>";
		$numStudents = 0;
		$fldEnabled = '';
	}
	else {
		if (DEBUG) print 'Line '.__LINE__ . " Validation Passed. Selecting record from database for family_id " . $_SESSION['family_id'] . "<br>";
		$query_rsStudent = "SELECT staff_id, family_id, first_name, last_name, Assignment, picture, mon, tue, wed, thur, fri, kitchen, craft, classroom, nursery, anything, shirt_size, teach_with, age_group, confo, registered, comments, create_date, last_update, deleted FROM staff WHERE family_id=".$_SESSION['family_id'];
		if (DEBUG) writeLog(FILE_NAME . __LINE__ . "-" . $query_rsStudent);
		$all_rsStudent = mysqli_query($vbsDBi, $query_rsStudent);
		if (DEBUG and $all_rsStudent===FALSE) writeLog(FILE_NAME . __LINE__ . "-SQL Query failed.");
		$numStudents = mysqli_num_rows($all_rsStudent);
		if (DEBUG) print'Line '. __LINE__ . " Number of staff rows selected: $numStudents <br>";

		if ($_REQUEST['submit']=='Redisplay') $offset = $numStudents-1;  /* Go to current record */
		$query_limit_rsStudent = sprintf("%s LIMIT %d, %d", $query_rsStudent, $offset, $numStudents);
		$rsStudent = mysqli_query($vbsDBi, $query_limit_rsStudent);
		$row_rsStudent = mysqli_fetch_assoc($rsStudent);
		
		/*Set the registered radio button variables here */
		$yesVal = ($row_rsStudent['registered']=='C') ? 'C' : 'Y';
		$yesChk = ($row_rsStudent['registered']=='Y' or $row_rsStudent['registered']=='C') ? ' checked ' : '';
		$noChk  = ($row_rsStudent['registered']=='N') ? ' checked ' : '';

		if (DEBUG) print 'Line '.__LINE__." Number of staff: $numStudents <br>";
		if ($numStudents==0) {
		    $_REQUEST['submit']=NEW_BUTTON;
		    /* If there are no students on record, disable the input fields.  User required to press new button */
		    $fldEnabled=' disabled';
		}
	}
}
if (DEBUG) print 'Line '.__LINE__." Pagination: $offset of $numStudents <br>";

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

$staffID = $row_rsStudent['staff_id'];
/* Set the button disabled properties */
$offset = ++$offset;
$button['First'] = ($numStudents > 2 and $offset > 2) ? '' : ' disabled';
$button['Previous'] = ($numStudents > 1 and $offset > 1) ? '' : ' disabled';
$button['Next'] = ($numStudents > 1 and $offset<($numStudents)) ? '' : ' disabled';
$button['Last'] = ($numStudents > 2 and $offset<($numStudents-1)) ? '' : ' disabled';
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
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="scripts/vbsUtils.js"></script>
</head>
<body>
<div id="Find" class="gridContainer">
	<h1>VBS - Volunteers</h1>
	<div id="dataLayout center">
	<form method="post" name="frmStaff" target="_self" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
	<table cellspacing="0">
		<?php if ($validateError) { ?> 
			<tr><td colspan="2" class="error center"> <?php echo $errMsgText; ?>
		<?php } else { ?>
			<tr><td colspan="2" class="center">Edit information and save</td></tr> 
	 	<?php } ?>
	
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hAtt')">Helping at VBS?<span class="popuptext" id="hAtt">Select yes if <?php echo (empty($row_rsStudent['first_name']) ? "you are" : $row_rsStudent['first_name'] . " is");?> helping at VBS in <?php echo date("Y");?>; otherwise select No.</span></span></td>
			<td class="value">
			<label><input type="radio" name="registered" id="reg-yes" value="<?php echo $yesVal?>" <?php echo $yesChk . $fldEnabled?>> Yes</label>
            <label><input type="radio" name="registered" id="reg-no" value="N" <?php echo $noChk . $fldEnabled?>> No</label>
			</td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hFirst')">First Name<span class="popuptext" id="hFirst">Enter the first name of the staff volunteer.</span></span></td><td class="value"><input name="first_name" type="text" id="first_name" value="<?php echo $row_rsStudent['first_name']; ?>" maxlength="20" <?php echo $fldEnabled ?> style="width:60%"></td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hLast')">Last Name<span class="popuptext" id="hLast">Enter the last name of the staff volunteer.</span></span></td><td class="value"><input name="last_name" type="text" value="<?php echo $row_rsStudent['last_name']; ?>" maxlength="20" <?php echo $fldEnabled ?> style="width:60%"></td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hAvail')">Availability<span class="popuptext" id="hAvail">Select the days you are available to help during the week of VBS.  You must select at least one day.</span></span></td><td class="value">
            <input type="checkbox" name="mon" value="Y" <?php echo (empty($row_rsStudent['mon'])||$row_rsStudent['mon']=='N' ?'':'checked ') . $fldEnabled;?>>&nbsp;Mo
            <input type="checkbox" name="tue" value="Y" <?php echo (empty($row_rsStudent['tue'])||$row_rsStudent['tue']=='N' ?'':'checked ') . $fldEnabled;?>>&nbsp;Tu
            <input type="checkbox" name="wed" value="Y" <?php echo (empty($row_rsStudent['wed'])||$row_rsStudent['wed']=='N' ?'':'checked ') . $fldEnabled;?>>&nbsp;We
            <input type="checkbox" name="thur" value="Y" <?php echo (empty($row_rsStudent['thur'])||$row_rsStudent['thur']=='N' ?'':'checked ') . $fldEnabled;?>>&nbsp;Th
            <input type="checkbox" name="fri" value="Y" <?php echo (empty($row_rsStudent['fri'])||$row_rsStudent['fri']=='N' ?'':'checked ') . $fldEnabled;?>>&nbsp;Fr
        </td></tr>
		<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hPref')">Preferences<span class="popuptext" id="hPref">If you are particular about where you help, check only those boxes for the area in which you have an interest.  You must select at least one.</span></span></td><td class="value">
	        <input type="checkbox" name="classroom" value="Y" <?php echo (empty($row_rsStudent['classroom'])||$row_rsStudent['classroom']=='N' ?'':'checked ') . $fldEnabled;?>>&nbsp;Classroom
			<input type="checkbox" name="craft" value="Y" <?php echo (empty($row_rsStudent['craft'])||$row_rsStudent['craft']=='N' ?'':'checked ') . $fldEnabled;?>>&nbsp;Craft
            <input type="checkbox" id="kitchen" name="kitchen" value="Y" <?php echo (empty($row_rsStudent['kitchen'])||$row_rsStudent['kitchen']=='N' ?'':'checked ') . $fldEnabled;?>>&nbsp;Kitchen
            <input type="checkbox" name="anything" value="Y" <?php echo (empty($row_rsStudent['anything'])||$row_rsStudent['anything']=='N' ?'':'checked ') . $fldEnabled;?>>&nbsp;Anything
	    <tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hShirt')">Shirt Size<span class="popuptext" id="hShirt">Select the shirt size you want for this volunteer.  T-Shirt are only available for those who register before <?php echo VBS_SHIRT_DEADLINE_MMDDYYYY?></span></span></td><td class="value"><select name="shirt_size" <?php echo $fldEnabled?>>
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
            <label><input type="radio" name="picture" id="pic-yes" value="Y" <?php echo (strcasecmp($row_rsStudent['picture'],"Y")==0 ? "checked " : "") . $fldEnabled; ?>>&nbsp;Yes</label>
            <label><input type="radio" name="picture" id="pic-no" value="N" <?php echo (strcasecmp($row_rsStudent['picture'],"N")==0 ? "checked " : "") . $fldEnabled;?>>&nbsp;No</label>
    	</td></tr>
        <tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('clear')">Over&nbsp;17?<span class="popuptext" id="clear">Federal and state regulations require us to have clearances for volunteers age 18 and older. Answer this question to help us identify who requires clearances.</span></span></td><td class="value">
            <label><input type="radio" name="age_group" value="Adult" <?php echo (strcasecmp($row_rsStudent['age_group'],"Adult")==0 ? "checked " : "") . $fldEnabled;?>>&nbsp;Yes</label>
            <label><input type="radio" name="age_group" value="Youth" <?php echo (strcasecmp($row_rsStudent['age_group'],"Youth")==0 ? "checked " : "") . $fldEnabled;?>>&nbsp;No</label>
    	</td></tr>
        <tr><td class="label"><span class="popup" onclick="myPopUp('nursery')">I need staff nursery<span class="popuptext" id="nursery">Check this box if you have a child under 3 and want to place them in the staff nursery.  You will be guided to register the child on the next page.  Leave box unchecked if you do not need these services. If a staff nursery record already exists, the text "Staff nursery registrant exists" will appear adjacent to the check box.</span></span></td><td class="value">
            <label><input type="checkbox" name="nursery" <?php echo (empty($row_rsStudent['nursery']) || $row_rsStudent['nursery']=='N'?'':'checked ') . $fldEnabled;?>></label> <?php if ($staffNurseryExists) echo "Staff nursery registrant exists"?>
    	</td></tr>
		<tr>
          <td class="label"><span class="popup" onclick="myPopUp('hClass')">I want to help in my child's class<span class="popuptext" id="hClass">If you want to be in the same class as your child, enter the child's name in this space.</span></span></td><td class="value"><input type="text" name="teach_with" placeholder="Your child's name and grade" value="<?php echo $row_rsStudent['teach_with'];?>" <?php echo $fldEnabled;?> style="width:60%"></td></tr>
        <tr><td class="label"><span class="popup" onclick="myPopUp('hComment')">Comments<span class="popuptext" id="hComment">This block is for comments specifically related to this volunteer.  Comments are optional.</span></span></td><td class="value"><textarea name="comments"<?php echo $fldEnabled?> ><?php echo $row_rsStudent['comments']; ?></textarea></td></tr>
        <tr><td>*&nbsp;required  <span class="popup" onclick="myPopUp('help')">Help available<span class="popuptext" id="help">Use this form to register volunteers for the week of VBS.  Volunteers must be in 7th grade or older.  Click the underlined labels for detailed popup help. Click again to close it.</span></span></td><td class="value">
   		<?php if ($staffID==0) { ?>
			<input type="submit" name="submit" value="Save">&nbsp;
			<input type="submit" name="submit" value="Cancel">
        <?php } else { ?>
        	<input type="submit" name="submit" value="Update">
        <?php } ?>
		</td></tr>
        <input name="staff_id" type="hidden" value="<?php echo $row_rsStudent['staff_id']; ?>">
        <input name="family_id" type="hidden" value="<?php echo $row_rsStudent['family_id']; ?>">
        <input name="deleted" type="hidden" value="<?php echo $row_rsStudent['deleted']; ?>">
        <input name="confo" type="hidden" value="<?php echo $row_rsStudent['confo']; ?>">
        <input name="offset" type="hidden" value="<?php echo $offset;?>">
        <input name="numStudents" type="hidden" value="<?php echo $numStudents;?>">
		<tr><td colspan='2' class='narrow'><hr></td></tr>
		<tr><td colspan='2' style="margin-top:0;padding-top:0;">
		<div id="buttonSubGroup" class="center" style="padding-top:0;margin-top:0;">
    		Displaying staff member <?php echo (($numStudents>0)?$offset+1:0)?> of <?php echo $numStudents ?><br>
			<input type="submit" name="submit" class="button" value="First"<?php echo $button['First']?>>&nbsp;
			<input type="submit" name="submit" class="button" value="Previous"<?php echo $button['Previous']?>>&nbsp;
			<input type="submit" name="submit" class="button" value="Next"<?php echo $button['Next']?>>&nbsp;
			<input type="submit" name="submit" class="button" value="Last"<?php echo $button['Last']?>>&nbsp;&nbsp;&nbsp;
			<input type="submit" name="submit" class="button" value="<?php echo NEW_BUTTON?>"<?php  echo $button['New']?>><br>
		</div>
		</td></tr>
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
