<?php
session_start();
include_once('vbsUtils.inc');
define('FILE_NAME', '[CONTACTS]');
$errMsgText = '';

if ($_SESSION['family_id']==0){
	if (DEBUG) print 'Forwarding to search page from Phones.';
	header('Location: index.php');
}

function quickSave(){
    global $vbsDBi;
    if (DEBUG) print "Line " . __LINE__ . ' Quick Save: $_POST = ' . print_r($_POST) . '<br>';
    writeLog2(FILE_NAME, __LINE__, 'Entering quickSave()');


    $sql = "DELETE from phone_numbers WHERE family_id=" . $_SESSION['family_id'];
    if (mysqli_query($vbsDBi, $sql)){
        /* Insert the records from the screen */
        $newPhone = $_POST['phone'];
        /* Loop through the phone array and insert each one into the table */
        $sql = "INSERT into phone_numbers (phone, family_id, contact_name, last_update, create_date) ";
        $sql .= "VALUES ('%s', %u, '%s', now(), now())";
        for ($i=0; $i<count($newPhone); $i++){
            if (DEBUG) print __FILE__ . ":" . __FUNCTION__ . "-" . __LINE__ . "<br>";
            $sqlInsert = sprintf($sql,
                mysqli_real_escape_string($vbsDBi, unformatPhone($newPhone[$i]['phone'])),
                mysqli_real_escape_string($vbsDBi, $newPhone[$i]['family_id']),
                mysqli_real_escape_string($vbsDBi, trim($newPhone[$i]['contact_name'])));
            if (mysqli_query($vbsDBi, $sqlInsert)){
                if (DEBUG) print "Inserting records at line: " . __LINE__ . "<br>" . $sqlInsert . "<br>";
                writeLog2(FILE_NAME, __LINE__, "Insert new phone as $sqlInsert"); }
            else {
                /* ERROR!  ERROR!  We have an ERROR! Just log it.*/
                $sqlErrNum = mysqli_errno($vbsDBi);
                $sqlErrMsg = mysqli_error($vbsDBi);
                if (DEBUG) print "Line: " . __LINE__ . "-" . $sqlErrNum . "<br>";
                writeErr(" Insert error $sqlErrMsg", __FILE__, __LINE__, $sqlErrNu);
            }
        }  /* End of phone insert loop */
    }
}

function validate($form){
	if (DEBUG) print "Line " . __LINE__ . "-Validate<br>";
	$error = false;
	global $errMsgText;
	$errMsg = "";		/* Reset any previous messages */

	$notBlank   = array('phone'=>'Phone Number', 'contact_name'=>'Contact Name');	
	
	/* Check for blank elements */
	for ($v=0; $v<count($form); $v++){
		$blanks = array_intersect_key($form[$v], $notBlank);
		foreach ($blanks as $key => $value){
			if (strlen($value)===0){
				$errMsg .= "Missing " . $notBlank[$key] . ",";
				$error = TRUE;
			}
			else
			{
				if (($blanks=='phone') && (strlen($value)<10)){
					$error = TRUE;
					$errMsg .= $blanks[$key] . " too short,";
				}
			}
		}
	}

	if (!validatePhoneQuantity()){
	    $errMsg .= "Need a minimum of two (2) unique phone numbers.";
	    $error = TRUE;
	}
	
	/* This assigns the error text to a variable outside the function */
	if ($error) $errMsgText = trim($errMsg, ",");
	return !$error;

}

function validatePhoneQuantity(){
    global $vbsDBi;
    $validatedOK = TRUE;
    
	/* Perform validation. Count must be > 1 */
    $sql = "SELECT count(distinct phone) from phone_numbers where family_id = {$_SESSION['family_id']}";
    $rsPhoneCount = mysqli_fetch_row(mysqli_query($vbsDBi, $sql))[0];
    if (DEBUG) writelog2(FILE_NAME, __LINE__ , "Unique phone count for fam ID {$_SESSION['family_id']} is $rsPhoneCount");
    
    if ($rsPhoneCount < 2){
    	writeLog(FILE_NAME . __LINE__ . " ");
    	writeLog2(FILE_NAME, __LINE__, "Family id {$_SESSION['family_id']} has < 2 contacts.");
        $validatedOK = FALSE;
    }

    return $validatedOK;
	
}


$errMsg = "";
$blankPhoneArray = Array(Array('phone'=>'', 'contact_name'=>''));


if (empty($_POST['submit'])){
	/* We are coming from the family page, just display */
	$_POST['submit']='Display';
}
	
switch ($_POST['submit']){
	case "Delete" :
		if (DEBUG) print "Line " . __LINE__ . "-Delete<br>";
		/* Find out which POST elements have the delete button checked */
		
		$phones = $_POST['phone'];
		$sql = "DELETE FROM phone_numbers WHERE family_id=%d AND phone='%s' AND contact_name='%s'";
		foreach($phones as $value) {
			if (array_key_exists('sel', $value)){
				$sqlDelete = sprintf($sql, 				
					mysqli_real_escape_string($vbsDBi, $value['family_id']),
					mysqli_real_escape_string($vbsDBi, unformatPhone($value['phone'])),
					mysqli_real_escape_string($vbsDBi, $value['contact_name'])
				);
				if (!mysqli_query($vbsDBi, $sqlDelete)){
					$sqlErr = mysqli_error($vbsDBi);
					writeLog(FILE_NAME . __LINE__ . " Error deleting phone", "Switch:Delete", __LINE__, $sqlErr);
				}
			}
		}
		break;
    /* No validation on backwards moves */
	case HOME_BUTTON :
        quickSave();
	    header("Location: " . HOME_PAGE);
	    break;
	case PREVIOUS_BUTTON :
	    quickSave();
	    header("Location: " . FAMILY_PAGE);
	    break;
	case NEXT_PAGE :
	    quickSave();
	    $toValidate = $_POST['phone'];
	    if (validate($toValidate)===TRUE){
	        header("Location: " . STUDENT_PAGE);
	    }
	    break;
	case "Add" :
	    quickSave();
	    break;
	case "Save" :	
		if (DEBUG) print "Line: " . __LINE__ . "-Save<br>";
		if (DEBUG) print_r($_POST['phone']);
		if (DEBUG) print "<br>";

		/* We must do a quickSave because the validation is based upon what is in the database, not on the screen. 
		 * This will get the data from the screen into the database */

		quickSave();  
		
		/* Next validate the data */
		$toValidate = $_POST['phone']; 
		if (validate($toValidate)===TRUE){
			if (DEBUG) print "Line " . __LINE__ . " - Validation passed<br>";
			/* First get rid of all the existing phones contacts for the family.  We don't know what changed on the screen so we reinsert everything! */
			$sql = "DELETE from phone_numbers WHERE family_id=" . $_SESSION['family_id'];
			if (mysqli_query($vbsDBi, $sql)){
				/* Insert the records from the screen */
				$newPhone = $_POST['phone']; 
				/* Loop through the phone array and insert each one into the table */
				$sql = "INSERT into phone_numbers (phone, family_id, contact_name, last_update, create_date) ";
				$sql .= "VALUES ('%s', %u, '%s', now(), now())";
				for ($i=0; $i<count($newPhone); $i++){
					if (DEBUG) print __FILE__ . ":" . __FUNCTION__ . "-" . __LINE__ . "<br>";
					$sqlInsert = sprintf($sql, 
						mysqli_real_escape_string($vbsDBi, unformatPhone($newPhone[$i]['phone'])),
						mysqli_real_escape_string($vbsDBi, $newPhone[$i]['family_id']),
						mysqli_real_escape_string($vbsDBi, $newPhone[$i]['contact_name']));
					if (mysqli_query($vbsDBi, $sqlInsert)){
						if (DEBUG) print "Inserting records at line: " . __LINE__ . "<br>" . $sqlInsert . "<br>";		
						writeLog2(FILE_NAME, __LINE__ , "Insert new phone as $sqlInsert");
                    }
					else {

						$sqlErrNum = mysqli_errno($vbsDBi);
						$sqlErrMsg = mysqli_error($vbsDBi);
						if (DEBUG) print "Line: " . __LINE__ . "-" . $sqlErrNum . "<br>";		
						switch ($sqlErrNum) {
							case 1062:    /* This may go away because the primary key of  phonenumber has been removed 03/27/2018 */
								if (DEBUG) print __FILE__ . ":" . __FUNCTION__ . "-" . __LINE__ . " Case: 1062, " . $sqlErrMsg . "<br>";
								writeErr(__FILE__, __FUNCTION__, __LINE__, $sqlErrMsg);
								/* Do more research to determine if the number is assigned to a different family */
								$sql = "SELECT family_id, contact_name from phone_numbers where phone='".$newPhone[$i]['phone']."'";
								$rsResult = mysqli_query($vbsDBi, $sql);
								if ($rsResult) {
									$famID = mysqli_fetch_assoc($rsResult);
									if (DEBUG) {
										print "Line: " . __LINE__ ;
										print_r($famID) . "<br>";
									}
									if ($famID['family_id']==$_SESSION['family_id']){
										if (DEBUG) print "Line: " . __LINE__ . "<br>";
										$errMsgText .= "Number " . formatPhone($newPhone[$i]['phone']) . " already assigned to " . $famID['contact_name'];}
									else {
										$errMsgText .= "Number " . formatPhone($newPhone[$i]['phone']) . " already exists for a different family";
									}
								}
								break;
							case 1048:
								if (DEBUG) print __FILE__ . ":" . __FUNCTION__ . "-" . __LINE__ . " Case: 1048<br>";
								writeErr(__FILE__, __FUNCTION__, __LINE__, $sqlErrMsg);
								$errMsg .= "Both phone number and type required.";
								break;
							default:
								if (DEBUG) print __FILE__ . ":" . __FUNCTION__ . "-" . __LINE__ . " Case: default<br>";
								writeErr(__FILE__, __FUNCTION__, __LINE__, $sqlErrMsg);
								$errMsg = "Error inserting phone " . $newPhone[$i]['phone'] . " Record not added. SQLError: " . $sqlErrNum;
								break;
						}
					}
				}  /* End of phone insert loop */
				
				/* ADD PAGE NAVIGATION HERE */
				switch ($_POST['submit']){
				    case NEXT_PAGE :
				        header("Location: " . STUDENT_PAGE);
				        break;
				}
			}
			else  /* Unable to delete phone number(s) */
			{
				if (DEBUG) print "Line: " . __LINE__ . "Delete before Save failed!<br>";		
				writeLog(FILE_NAME . __LINE__ . " Error deleting phone records for family id " . $_SESSION['family_id'], "Contacts:Save", __LINE__, mysqli_error($vbsDBi));
			}
		}
		else{
			/* Not validated */
		    if (DEBUG) {
		        print "Line: " . __LINE__ . " Validation error<br>";
		        writeLog(FILE_NAME . __LINE__ . "-Validation error");
		    }
		    quickSave();   /* We need to do a quickSave here to save any new phones (validated or not) to the database for redisplay */
			//$errMsg = "Please correct missing data.";
			writeLog(FILE_NAME . __LINE__ . " Validation failed for family id contacts (".$_SESSION['family_id'].")");
			/* Restore the POSTed data to the screen, break out of here to avoid a requery */
			$rsPhone = $_POST['phone'];
			break;
		}
		break;
	case "Display" :
	    break;
}

/* This was the "display" case statement, just now moved to outside the case */
if (DEBUG) print "Line: " . __LINE__ . "-Display<br>";


$sqlPhone = "SELECT phone, contact_name FROM phone_numbers WHERE family_id=" . $_SESSION['family_id'] . " ORDER BY contact_name";
$rsResult = mysqli_query($vbsDBi, $sqlPhone);
if ($rsResult){
    $rsPhone = mysqli_fetch_all($rsResult, MYSQLI_ASSOC);
    
    if (DEBUG) {
        print_r($rsPhone);
        print "<br>";
    }
    
    if (($_POST['submit']=='Add') or (count($rsPhone)<1)){
        if (DEBUG) print "Line: " . __LINE__ . "<br>";
        /* Put a blank record on the end of the array and redisplay */
        if (DEBUG) print "Before: "; print_r($rsPhone); print "<br>";
        $rsTemp = $rsPhone;
        $rsPhone = array_merge($rsTemp, $blankPhoneArray);
        if (DEBUG) print "After: "; print_r($rsPhone); print '<br>';
    }
}
else {
    if (DEBUG) print "Line: " . __LINE__ . "<br>";
    $rsPhone = $blankPhoneArray;
    $rsPhone[0]['phone']=$_SESSION['search_phone'];
}
if (DEBUG) {
    print "Session: ";
    print_r($_SESSION);
    print "<br>";
}

?>

<!doctype html>
<html class="">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>VBS-Contact Information</title>
<link href="css/layout.css?v4" rel="stylesheet" type="text/css">
</head>
<body>
<div id="Find" class="gridContainer-footer">
<h1>Contact Info</h1>
<div id="dataLayout">
-  	<div id="Contacts">
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])?>" method="POST" name="frmContacts" target="_self">
    <table class="border-on">
        <?php if (strlen($errMsgText)>0) { ?>
        	<tr><td colspan="3" class="title nowrap error"><?php echo $errMsgText;?></td></tr>
        <?php } else { ?>
        	<tr><td colspan="3" class="center title nowrap">Provide at least two different phone numbers.</td></tr>
        <?php } ?>
    	<tr><th class="col1">*&nbsp;Name</th><th class="col2">*&nbsp;Phone</th><th class="col3">Select</th></tr>
        <?php for ($i=0; $i<count($rsPhone); $i++){ ?>
        <tr>
            <td class="center col1 border-on"><input type="text" name="phone[<?php echo $i;?>][contact_name]" value="<?php echo $rsPhone[$i]['contact_name']; ?>" maxlength="50" <?php echo ($i==0 ? " autofocus" : " ");?>></td>
            <td class="center col2 border-on"><input type="text" name="phone[<?php echo $i;?>][phone]" maxlength="12" value="<?php echo formatPhone($rsPhone[$i]['phone']); ?>" ></td>
    		<td class="center col3 border-on"><input name="phone[<?php echo $i;?>][sel]" type="checkbox" value="">
            <input type="hidden" name="phone[<?php echo $i;?>][family_id]" value="<?php echo $_SESSION['family_id']?>"></td>
    	</tr>
        <?php } ?>
        <tr><td colspan="3" class="label"><span class="float-left">*&nbsp;required</span>
        	<span class="horizontal-center">
        	<input type="submit" name="submit" value="Save">&nbsp;
    	    <input type="submit" name="submit" value="Add">&nbsp;
    	    <input type="submit" name="submit" value="Delete">&nbsp;
    		</span>
    		<span class="popup float-right" onclick="myPopUp('help')">Help available<span class="popuptext" id="help">Enter family contact information on this page.  You may enter as many names and phone numbers as you wish but you must provide at least two different phone numbers.  Each contact must have a name &amp; phone number.<br>To delete a contact, first check the Select box(es) of the lines you want to delete then click the delete button.</span></span>
    	</td></tr>	
    </table>
    <div id="buttonGroup" class="center">
    	<input type="submit" name="submit" class="button" value="<?php echo HOME_BUTTON?>">&nbsp;
        <input type="submit" name="submit" class="button" value="<?php echo PREVIOUS_BUTTON?>">&nbsp;
        <input type="submit" name="submit" class="button" value="<?php echo NEXT_PAGE?>">
    </div>
    </form>
	</div>
</div>
</div>
<script src="scripts/vbsUtils.js"></script>
<?php
include('footer.inc');
@mysqli_free_result($rsPhone);
?>
</body>
</html>
