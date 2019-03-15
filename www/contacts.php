<?php
session_start();
require_once('./Connections/vbsDB.php');
include_once('vbsUtils.inc');
$errMsgText = '';

if ($_SESSION['family_id']==0){
	if (DEBUG) print "Forwarding to search page from Phones.";
//	header("Location: search.php");
}

function validate($form){
	if (DEBUG) print "Line " . __LINE__ . "-Validate<br>";
	$error = false;
	global $errMsgText;
	$errMsg = "";		/* Reset any previous messages */

	$notBlank   = array('phone'=>'Phone', 'contact_name'=>'Contact Name');	
	
	/* Remove leading and trailing spaces */
	for ($v=0; $v<count($form); $v++){
		foreach ($form[$v] as $key => $value){
			$form[$v][$key] = trim($value);
		}
	}
	
	/* Assign trim changes back to the $_POST array */
	$_POST['phone'] = $form;

	/* Check for blank elements */
	for ($v=0; $v<count($form); $v++){
		$blanks = array_intersect_key($form[$v], $notBlank);
		foreach ($blanks as $key => $value){
			if (strlen($value)===0){
				$errMsg .= "Missing " . $notBlank[$key] . ",";
				$error = true;
			}
			else
			{
				if (($blanks=='phone') && (strlen($value)<10)){
					$error = true;
					$errMsg .= $blanks[$key] . " too short,";
				}
			}
		}
	}

	/* This assigns the error text to a variable outside the function */
	if ($error) $errMsgText = trim($errMsg, ",");
	return !$error;

}

function validatePhoneQuantity(){
    global $vbsDBi;
    $validatedOK = TRUE;
    
	/* Perform validation. Count must be > 1 */
    $sql = "SELECT count(distinct phone) from phone_numbers where family_id = " . $_SESSION['family_id'];
    $rsPhoneCount = mysqli_fetch_row(mysqli_query($vbsDBi, $sql))[0];
    
    if ($rsPhoneCount < 2){
    	writeLog("Family id " . $_SESSION['family_id'] . " has insufficient contacts.");
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
	case HOME_BUTTON :
		header("Location: " . HOME_PAGE);
		break;
	case PREVIOUS_BUTTON :
		header("Location: " . FAMILY_PAGE);	
		break;
	case NEXT_PAGE :
	    if (validatePhoneQuantity()) header("Location: " . STUDENT_PAGE);
        break;
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
					writeErr("Error deleting phone", "Switch:Delete", __LINE__, $sqlErr);
				}
			}
		}
		break;	
	case "Add" :
	case "Save" :	
		if (DEBUG) print "Line: " . __LINE__ . "-Save<br>";
		if (DEBUG) print_r($_POST['phone']);
		if (DEBUG) print "<br>";

		/* First validate the data */
		$toValidate = $_POST['phone']; 
		if (validate($toValidate)){
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
						writeLog("Inserted new phone data as " . $sqlInsert);}
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
								$errMsg = "Error inserting phone " . $newPhone[$i]['phone'] . ". Record not added.";
								break;
						}
					}
				}  /* End of phone insert loop */
			}
			else  /* Unable to delete phone number(s) */
			{
				if (DEBUG) print "Line: " . __LINE__ . "Delete before Save failed!<br>";		
				$sqlErr = mysqli_error($vbsDBi);
				writeErr("Error deleting phone records for family id " . $_SESSION['family_id'], "Contacts:Save", __LINE__, $sqlErr);
			}
		}
		else{
			/* Not validated */
			if (DEBUG) print "Line: " . __LINE__ . " Validation error<br>";
			$errMsg = "Please correct missing data.";
			writeLog("Validation failed for family id contacts (".$_SESSION['family_id'].")");
			/* Restore the POSTed data to the screen, break out of here to avoid a requery */
			$rsPhone = $_POST['phone'];
			break;
		}
		break;
	case "Display" :
	    break;
	    
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
				$rsTemp = $rsPhone;
				$rsPhone = array_merge($rsTemp, $blankPhoneArray);
			}
		}
		else {
			if (DEBUG) print "Line: " . __LINE__ . "<br>";	
			$rsPhone = $blankPhoneArray;
		}
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
        $rsTemp = $rsPhone;
        $rsPhone = array_merge($rsTemp, $blankPhoneArray);
    }
}
else {
    if (DEBUG) print "Line: " . __LINE__ . "<br>";
    $rsPhone = $blankPhoneArray;
}
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
<title>VBS-Contact Information</title>
<link href="css/boilerplate.css" rel="stylesheet" type="text/css">
<link href="css/layout.css" rel="stylesheet" type="text/css">
<!-- @@ <link href="css/textural.css" rel="stylesheet" type="text/css"> -->
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="respond.min.js"></script>
<script src="scripts/vbsUtils.js"></script>
</head>
<title>VBS-Telephone Contacts</title>
<body>
<div id="Phone" class="gridContainer">
<div><h1>VBS - Contact Info</h1></div>
<?php if (strlen($errMsgText)>0) { ?>
	<div><h2 class="error"><?php echo $errMsgText;?></h2></div>
<?php } else { ?>
	<div><h3>Provide at least two different phone numbers.</h3></div>
<?php } ?>
	<div id="dataLayout">
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])?>" method="POST" name="frmContacts" target="_self">
<table cellspacing="0">
	<tr><th>*&nbsp;Name</th><th>*&nbsp;Phone</th><th class="left">Select</th></tr>
    <?php for ($i=0; $i<count($rsPhone); $i++){ ?>
    <tr>
        <td><input type="text" name="phone[<?php echo $i;?>][contact_name]" value="<?php echo $rsPhone[$i]['contact_name']; ?>" style="width:99%" maxlength="50"></td>
        <td><input type="text" name="phone[<?php echo $i;?>][phone]" maxlength="12" value="<?php echo formatPhone($rsPhone[$i]['phone']); ?>" style="width:99%"></td>
<!--
        <td><select name="phone[<?php echo $i;?>][phone_type_code]">
    	    <?php for ($p=0; $p<count($rsPhoneTypes); $p++) { ?>
	        <option value="<?php echo $rsPhoneTypes[$p]['phone_type_code']?>"<?php if (!(strcmp($rsPhoneTypes[$p]['phone_type_code'], 
				$rsPhone[$i]['phone_type_code']))) {echo " selected=\"selected\"";} ?>><?php echo $rsPhoneTypes[$p]['phone_type_desc']?></option>
        	<?php } ?>
			</select></td>
-->
		<td><input name="phone[<?php echo $i;?>][sel]" type="checkbox" value="">
        <input type="hidden" name="phone[<?php echo $i;?>][family_id]" value="<?php echo $_SESSION['family_id']?>"></td>
	</tr>
    <?php } ?>
    <tr><td colspan="4">* required  <span class="popup" onclick="myPopUp('help')">Help available<span class="popuptext" id="help">Enter family contact information on this page.  You may enter as many names and phone numbers as you wish but you must provide at least two different phone numbers.  Each contact must have a name & phone number.<br>To delete a contact, first check the Select box(es) of the lines you want to delete then click the delete button.</span></span></td></tr>
	<tr class="center">
		<td colspan="4">
    	<input type="submit" name="submit" value="Save">&nbsp;
	    <input type="submit" name="submit" value="Add">&nbsp;
	    <input type="submit" name="submit" value="Delete">&nbsp;
		</td>
	</tr>
</table>
<div id="buttonGroup" class="center">
	<input type="submit" name="submit" class="button" value="<?php echo HOME_BUTTON?>">&nbsp;
    <input type="submit" name="submit" class="button" value="<?php echo PREVIOUS_BUTTON?>">&nbsp;
    <input type="submit" name="submit" class="button" value="<?php echo NEXT_PAGE?>">
</div>
</form>
</div>
</div>
</body>
</html>
<?php
@mysqli_free_result($rsPhone);
@mysqli_free_result($rsPhoneTypeList);
?>
