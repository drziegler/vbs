<?php 
session_start();
include('vbsUtils.inc');
require_once('Connections/vbsDB.php');

if (isset($_REQUEST['submit']) and $_REQUEST['submit']=="Save"){
	/* skip this redirect test, else */
}
else{
	if (!isset($_SESSION['family_id']) or empty($_SESSION['family_id'])){
		/* Send the user to the search page if no family is selected */
		header("Location: search.php");
	}
}

function validate($form){
	if (DEBUG) print "Line: " . __LINE__ . "-Validate<br>";

	$error = false;
	global $errMsgText;
	$errMsg = "";		/* clear out an previous messages */

	/* Mandatory form elements */	
	$mustExist  = array('prehelp'=>'Prehelp');
	$notBlank   = array('first_name'=>'Family Name', 'address'=>'Address', 'zipcode'=>'Zip code');
	$selectLst  = array('lsthomechurch'=>'Home Church');
	
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
	$errMsgText = "Invalid items: " . trim($errMsg, ",");
	return !$error;
}

$errMsg = "";
$totalRows_rsCityState = 0;

/* This values comes from the results page */
if ($_SESSION['family_id']=='New'){
	if (DEBUG) print "Line: " . __LINE__ . "<br>";
	$_POST['submit']='New';
	$_SESSION['family_id']='';
}
if (empty($_POST['submit'])) $_POST['submit']='Display';
if (DEBUG) print "Line: " . __LINE__ . "<br>";

switch ($_POST['submit']) {
	case 'Back' :
		if (DEBUG) print "Line: " . __LINE__ . "-Back <br>";
		header("Location: " . HOME_PAGE);
		break;
	case 'New' :
		if (DEBUG) print "Line: " . __LINE__ . "-New<br>";
		$rsFam = array('family_name'=>'','email'=>'','address'=>'','zipcode'=>'','prehelp'=>'','home_church'=>'','comments'=>'','family_id'=>'');
		$_SESSION['family_id']='0';
		break;
	case 'Next' :
		if (DEBUG) print "Line: " . __LINE__ . "-Next<br>";
	case 'Save' :
		if (validate($_POST)){
			if (DEBUG) print "Family ID: " . $_SESSION['family_id'] . "<br>";
			if ($_SESSION['family_id']==0){
				/* This is a new family to insert */
				if (DEBUG) print "Line: " . __LINE__ . "-Save:Insert<br>";
				$sqlStmt = sprintf("INSERT INTO family (family_name, email, address,
					 zipcode, home_church, prehelp, comments, confo, create_date, last_update)
					VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', now(), now())", 
				mysqli_real_escape_string($vbsDBi, $_POST['family_name']),
				mysqli_real_escape_string($vbsDBi, $_POST['email']),
				mysqli_real_escape_string($vbsDBi, $_POST['address']),
				mysqli_real_escape_string($vbsDBi, $_POST['zipcode']),
				mysqli_real_escape_string($vbsDBi, $_POST['lstHomeChurch']),
				mysqli_real_escape_string($vbsDBi, $_POST['prehelp']),
				mysqli_real_escape_string($vbsDBi, $_POST['comment']),
				$_SESSION['confoNo']
				);
					
				/* Get the new family id after insert */
				if (mysqli_query($vbsDBi, $sqlStmt)){
					if (DEBUG) print "Line " . __LINE__ . "<br>";	
					$_SESSION['family_id'] = mysqli_insert_id($vbsDBi);
					$rsFamily['family_id'] = $_SESSION['family_id'];
					insertStats($vbsDBi, $_SESSION['family_id'], 'new');
					writeLog("Inserted new family id " . $_SESSION['family_id'] . " for " . $_POST['family_name']);
					
					/* IF we have a search phone number saved, create a reference phone number against this family id for future retrieval */
					if (!empty($_SESSION['newPhone'])){
						if (DEBUG) print "Line " . __LINE__ . "<br>";	
						$sql = "INSERT INTO phone_numbers (family_id, contact_name, phone, phone_type_code, create_date, last_update) VALUES ('%s', '%s', '%s', 'H', now(), now())";
						$sqlInsert = sprintf($sql,
							mysqli_real_escape_string($vbsDBi, trim($_SESSION['family_id'])), 
							mysqli_real_escape_string($vbsDBi, trim($_POST['family_name'])),
							mysqli_real_escape_string($vbsDBi, unformatPhone($_SESSION['newPhone']))
						);
						if (!mysqli_query($vbsDBi, $sqlInsert)){
							if (DEBUG) print "Line: " . __LINE__ . " Fam:Phone:Ins<br>";					
							$sqlErr = mysqli_error($vbsDBi);
							writeErr("Error writing phone insert", " Family:Switch:Save", __LINE__, $sqlErr);
						}
						else {
							if (DEBUG) print "Line: " . __LINE__ . "-Updated:phone<br>";
							writeLog("Inserted new phone number as " . $sqlInsert);
						}
					}
				}
				else {
					if (DEBUG) print "Line " . __LINE__ . "<br>";
					$sqlErr = mysqli_error($vbsDBi);
					writeErr("Error writing insert statement", "Switch:Save", __LINE__, $sqlErr);
				}
			}
			else { /* We have an update */
				if (DEBUG) print "Line: " . __LINE__ . "-Save:Update<br>";
				$sqlStmt = sprintf("UPDATE family SET family_name='%s', email='%s', address='%s', zipcode='%s', home_church='%s', prehelp='%s', comments='%s' WHERE family_id=%s",
					mysqli_real_escape_string($vbsDBi, trim($_POST['family_name'])),
					mysqli_real_escape_string($vbsDBi, trim($_POST['email'])),
					mysqli_real_escape_string($vbsDBi, trim($_POST['address'])),
					mysqli_real_escape_string($vbsDBi, trim($_POST['zipcode'])),
					mysqli_real_escape_string($vbsDBi, trim($_POST['lstHomeChurch'])),
					mysqli_real_escape_string($vbsDBi, trim($_POST['prehelp'])),
					mysqli_real_escape_string($vbsDBi, trim($_POST['comment'])),
					mysqli_real_escape_string($vbsDBi, $_POST['family_id']));

				if (!mysqli_query($vbsDBi, $sqlStmt)){
					if (DEBUG) print "Line: " . __LINE__ . "-Update(2)<br>";					
					$sqlErr = mysqli_error($vbsDBi);
					writeErr("Error writing family update statement", "Switch:Save", __LINE__, $sqlErr);
				}
				else {
					if (DEBUG) print "Line: " . __LINE__ . "-Updated:family<br>";
					writeLog("Updated family id as " . $sqlStmt);
				}
			}
			if ($_POST['submit']=='Next'){
				header("Location: contacts.php");
			}
		}
		else {
			/* We have an error condition, display the error message */
			if (DEBUG) print "Line: " . __LINE__ . "-Failed Validation<br>";
			$errMsgText = "Please correct missing data and save again.";
			$rsFam = $_POST;
			break;
		}
	case "Display" :
		if (DEBUG) print "Line: " . __LINE__ . "-Display<br>";
		$query_rsFamily = sprintf("SELECT * FROM family  WHERE family_id = %d ", $_SESSION['family_id']);
		$result = mysqli_query($vbsDBi, $query_rsFamily);
		if ($result){
			if (DEBUG) print "Line: " . __LINE__ . "-Display(2)<br>";
			$rsFam = mysqli_fetch_assoc($result);

			$query_rsZipCode = sprintf("SELECT city, state FROM zipcodes WHERE zipcode = %s ", $rsFam['zipcode']);
			$rsResult = mysqli_query($vbsDBi, $query_rsZipCode);
			If ($rsResult===false){
				if (DEBUG) print "Line: " . __LINE__ . "-Display:no city state<br>";
				$errMsgText = "Please correct missing data and save again.";
			}
			else {
				if (DEBUG) print "Line: " . __LINE__ . "-Display:found city state<br>";
				$rsCityState = mysqli_fetch_assoc($rsResult);
				$totalRows_rsCityState = mysqli_num_rows($rsResult);
				if (intval($totalRows_rsCityState)>0){
					$city = $rsCityState['city'];
					$state = $rsCityState['state'];}
				/*else {
					if (DEBUG) print "Line: " . __LINE__ . "<br>";
					$errMsgText = "Please correct missing data and save again.";
					$rsFam = $_POST;}
				*/
			}
		}
		else {
			if (DEBUG) print "Line: " . __LINE__ . "<br>";
			$sqlErr = mysqli_error($vbsDBi);
			writeErr($query_rsFamily, "Switch:DISP", __LINE__, $sqlErr);
		}

}  /* End of switch statement */


$query_rsChurchList = "SELECT HOME_CHURCH FROM churches ORDER BY DISP_ORDER";
$rsChurchResult = mysqli_query($vbsDBi, $query_rsChurchList);
$rsChurchList = mysqli_fetch_assoc($rsChurchResult);


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
<title>VBS - Family</title>
<link href="css/boilerplate.css" rel="stylesheet" type="text/css">
<link href="css/layout.css" rel="stylesheet" type="text/css">
<link href="css/textural.css" rel="stylesheet" type="text/css">
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="scripts/vbsUtils.js"></script>
<script src="css/respond.min.js"></script>
</head>
<body>
<div id="Family" class="gridContainer">
<div><h1>VBS - Family</h1></div>
<div><h2>Edit your family information then click next.</h2></div>
<div id="dataLayout">
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])?>" method="post" name="frmFamily" target="_self">
<table cellspace="0">
	<tr><td class="label">*&nbsp;Family Name</td><td class="value"><input name="family_name" type="text" value="<?php echo $rsFam['family_name'];?>"></td></tr>
	<tr><td class="label">*&nbsp;Address</td><td class="value"><input name="address" type="text" value="<?php echo $rsFam['address'];?>"></td></tr>
	<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hZip')">Zipcode<span class="popuptext" id="hZip">Enter your 5-digit zipcode.  We'll look up the city and state.</span></span></td><td class="value"><input name="zipcode" type="number" min="0" max="99999" value="<?php echo $rsFam['zipcode'];?>" ></td></tr>
	<?php if (intval($totalRows_rsCityState)>0){ ?>
    <tr><td class="label"><span class="popup" onclick="myPopUp('hCity')">City, State<span class="popuptext" id="hCity">You can't enter anything here.  We will calculate your city and state from your zipcode. Eh?  You're from Canada?  Call us to make sure you can still enter our country!"</span></span></td><td class="value"><span><?php echo (intval($totalRows_rsCityState)>0) ? $city . ', ' . $state : ""; ?></span></td></tr>
    <?php } ?>
    <tr><td class="label">*&nbsp;Email</td><td class="value"><input name="email" type="email" value="<?php echo $rsFam['email'];?>"></td></tr>
    <tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hChurch')">Home Church<span class="popuptext" id="hChurch">Select your home church from the drop down list.  If not listed, select other and enter your home church into the comments box.</span></span></td><td class="value"><select name="lstHomeChurch" style="width:90%;">
    <option value="">Select Home Church</option>
	<?php do { ?>
    <option value="<?php echo $rsChurchList['HOME_CHURCH'];?>"<?php if (!(strcmp($rsChurchList['HOME_CHURCH'], $rsFam['home_church']))) {echo "selected=\"selected\"";} ?>><?php echo $rsChurchList['HOME_CHURCH']?></option>
    <?php } while ($rsChurchList = mysqli_fetch_assoc($rsChurchResult)); ?>
    </select></td></tr>
	<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hPrepH')">Prep help<span class="popuptext" id="hPrepH">If you are able to help prepare for VBS with tasks that can be done from home, check the yes box.  We'll contact you.  We guarantee it.</span></span></td><td class="value"><input <?php if (!(strcmp($rsFam['prehelp'],"Y"))) {echo "checked=\"checked\"";} ?> name="prehelp" type="radio" value="Y">&nbsp;Yes <input <?php if (!(strcmp($rsFam['prehelp'],"N"))) {echo "checked=\"checked\"";} ?> name="prehelp" type="radio" value="N">&nbsp;No</td></tr>
	<tr><td class="label">Comments:</td><td class="value"><textarea name="comment" cols="" rows="3" style="width:90%;"><?php echo $rsFam['comments']; ?></textarea></td></tr>
    <tr><td>* required  <span class="popup" onclick="myPopUp('help')">Help available<span class="popuptext" id="help">Use this form to update family information.  When done, click NEXT to enter phone information. Click the underlined labels for detailed popup help. Click again to close it.</span></span></td><td><input type="submit" name="submit" value="Save"></td></tr>
</table>
	<input type="hidden" name="family_id" value="<?php echo $rsFam['family_id'];?>">
    <input type="hidden" name="city" value="<?php echo $city;?>">
    <input type="hidden" name="state" value="<?php echo $state;?>">
    <div id="buttonGroup" class="center">
    	<input type="submit" name="submit" class="button" value="Back">
        <input type="submit" name="submit" class="button" value="Next">
    </div>
    </form>
</div>
</div>
</body>
</html>
<?php
@mysqli_free_result($rsFam);
@mysqli_free_result($rsCityState);
@mysqli_free_result($rsChurchList);
?>
