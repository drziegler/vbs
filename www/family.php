<?php 
session_start();
include('vbsUtils.inc');
require_once('Connections/vbsDB.php');
define("FILE_NAME", "[FAMILY] ");
/* Initialize here in case we don't find a zip code match */
$city = $state = '';
if (DEBUG){
    print "Session variables: ";
    print_r($_SESSION);
    print "<br>";
}

$errMsgText="";

if (DEBUG) print "_SESSION[familyID] = " . $_SESSION['family_id'] . "<br>";

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
	if (DEBUG) {
		print "Line: " . __LINE__ . "-Validate<br>";
		print "Line " . __LINE__ . "- _POST "; 
		print_r($_POST);
		print "<br>";
	}
	

	$error = false;
	global $errMsgText;
	$errMsg = "";		/* clear out an previous messages */

	/* Mandatory form elements */	
	/*$mustExist  = array('prehelp'=>'Prehelp'); */
	$notBlank   = array('family_name'=>'Family Name', 'address'=>'Address', 'zipcode'=>'Zip code', 'email'=>'Email', 'lsthomechurch'=>'Home Church');

	
	/* Remove leading and trailing spaces */
	foreach ($form as $key => $value){
		$form[$key] = trim($value);
	}
	/* Assign trim changes back to the $_POST array */
	$_POST = $form;
	if (DEBUG) {
		print "Line " . __LINE__ . "- _POST "; 
		print_r($_POST);
		print "<br>";
	}
	
	/* Check for blank elements */
	$blanks = array_intersect_key($form, $notBlank);
	foreach ($blanks as $key => $value){
		if (strlen(trim($value))===0){
			$errMsg .= $notBlank[$key] . ",";
			$error = true;
		}
	}

	/* Check for options not selected */
	$option = (isset($form['lstHomeChurch'])) ? $form['lstHomeChurch'] : false;
	if (!$option) {
		if (DEBUG) print "Line " . __LINE__ . "-Home church missing<br>";
		$errMsg .= "Home Church,";
		$error = true;
	}


	/* This assigns the error text to a variable outside the function */
	$errMsgText = (strlen($errMsg)>0 ? "Need: " . trim($errMsg, ",") : "");
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
	case HOME_BUTTON :
		header("Location: " . HOME_PAGE);
		break;
	case PREVIOUS_BUTTON :
		header("Location: " . SEARCH_PAGE);
		break;
	case 'New' :
		if (DEBUG) print "Line: " . __LINE__ . "-New<br>";
		$rsFam = array('family_name'=>'','email'=>'','address'=>'','zipcode'=>'','home_church'=>'','comments'=>'','family_id'=>'',								'city'=>'','state'=>'');
		$city = $state = '';
		$_SESSION['family_id']='0';
		$_SESSION['family_name'] = '';
		break;
	case NEXT_PAGE :
		if (DEBUG) print "Line: " . __LINE__ . "-Next<br>";
	case 'Save' :
		if (validate($_POST)){
			if (DEBUG) print "Family ID: " . $_SESSION['family_id'] . "<br>";
			if ($_SESSION['family_id']==0){
				/* This is a new family to insert */
				if (DEBUG) print "Line: " . __LINE__ . "-Save:Insert<br>";
				$sqlStmt = sprintf("INSERT INTO family (family_name, email, address,
					 zipcode, home_church, comments, confo, create_date, last_update)
					VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', now(), now())", 
				mysqli_real_escape_string($vbsDBi, $_POST['family_name']),
				mysqli_real_escape_string($vbsDBi, $_POST['email']),
				mysqli_real_escape_string($vbsDBi, $_POST['address']),
				mysqli_real_escape_string($vbsDBi, $_POST['zipcode']),
				mysqli_real_escape_string($vbsDBi, $_POST['lstHomeChurch']),
				mysqli_real_escape_string($vbsDBi, $_POST['comments']),
				$_SESSION['confoNo']
				);
					
				/* Get the new family id after insert */
				if (mysqli_query($vbsDBi, $sqlStmt)){
					if (DEBUG) print "Line " . __LINE__ . "<br>";	
					$_SESSION['family_id'] = mysqli_insert_id($vbsDBi);
					$rsFamily['family_id'] = $_SESSION['family_id'];
					$_SESSION['family_name'] = $_POST['family_name'];
					insertStats($vbsDBi, $_SESSION['family_id'], 'new');
					writeLog(FILE_NAME . __LINE__ . " Inserted new family id " . $_SESSION['family_id'] . " for " . $_POST['family_name']);
					
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
							writeLog(FILE_NAME . __LINE__ . " Error writing phone insert", " Family:Switch:Save", __LINE__, $sqlErr);
						}
						else {
							if (DEBUG) print "Line: " . __LINE__ . "-Updated:phone<br>";
							writeLog(FILE_NAME . __LINE__ . " Inserted new phone number as " . $sqlInsert);
						}
					}
				}
				else {
					if (DEBUG) print "Line " . __LINE__ . "<br>";
					$sqlErr = mysqli_error($vbsDBi);
					writeLog(FILE_NAME . __LINE__ . " Error writing insert statement", "Switch:Save", __LINE__, $sqlErr);
				}
			}
			else { /* We have an update */
				if (DEBUG) print "Line: " . __LINE__ . "-Save:Update<br>";
				$sqlStmt = sprintf("UPDATE family SET family_name='%s', email='%s', address='%s', zipcode='%s', home_church='%s', comments='%s' WHERE family_id=%s",
					mysqli_real_escape_string($vbsDBi, trim($_POST['family_name'])),
					mysqli_real_escape_string($vbsDBi, trim($_POST['email'])),
					mysqli_real_escape_string($vbsDBi, trim($_POST['address'])),
					mysqli_real_escape_string($vbsDBi, trim($_POST['zipcode'])),
					mysqli_real_escape_string($vbsDBi, trim($_POST['lstHomeChurch'])),
					mysqli_real_escape_string($vbsDBi, trim($_POST['comments'])),
					mysqli_real_escape_string($vbsDBi, $_POST['family_id']));

				if (!mysqli_query($vbsDBi, $sqlStmt)){
					if (DEBUG) print "Line: " . __LINE__ . "-Update(2)<br>";					
					$sqlErr = mysqli_error($vbsDBi);
					writeLog(FILE_NAME . __LINE__ . " Error writing family update statement", "Switch:Save", __LINE__, $sqlErr);
				}
				else {
					if (DEBUG) print "Line: " . __LINE__ . "-Updated:family<br>";
					writeLog(FILE_NAME . __LINE__ ." Updated family id as " . $sqlStmt);
					$_SESSION['family_name'] = $_POST['family_name'];
				}
			}
			if ($_POST['submit']==NEXT_PAGE){
				header("Location: " . CONTACT_PAGE);
			}
		}
		else {
			/* Validation failed. Display the error message */
			$rsFam = $_POST;
			$rsFam['home_church'] = $rsFam['lstHomeChurch'];
			$city = $_POST['city'];
			$state = $_POST['state'];

			if (DEBUG) {
				print "Line: " . __LINE__ . "-Failed Validation<br>" . "Line " . __LINE__ . "- _POST "; 
				print_r($_POST);
				print "<br>" . "Line " . __LINE__ . "- rsFam "; 
				if (isset($rsFam)) print_r($rsFam);
				print "<br>";
			}

			
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
			}
		}
		else {
			if (DEBUG) print "Line: " . __LINE__ . "<br>";
			$sqlErr = mysqli_error($vbsDBi);
			writeErr($query_rsFamily, "Switch:DISP", __LINE__, $sqlErr);
		}

}  /* End of switch statement */


$query_rsChurchList = "SELECT HOME_CHURCH FROM churches ORDER BY DISP_ORDER, HOME_CHURCH";
$rsChurchResult = mysqli_query($vbsDBi, $query_rsChurchList);
$rsChurchList = mysqli_fetch_assoc($rsChurchResult);
if (DEBUG) print (mysqli_num_rows($rsChurchResult) . " Church rows fetched.<br>");
if (DEBUG) print_r($_SESSION);

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
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="scripts/vbsUtils.js"></script>
<script src="css/respond.min.js"></script>
</head>
<body>
<div id="Find" class="gridContainer">
	<h1>Family Info</h1>
<div id="dataLayout center">
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])?>" method="post" name="frmFamily" target="_self">
<table id="Family">
	<?php if (strlen($errMsgText)) { ?> 
		<tr><td colspan="2" class="error center"> <?php echo $errMsgText; ?>
	<?php } else { ?>
		<tr><td colspan="2" class="center">Edit family information</td></tr> 
 	<?php } ?>
	<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hFamName')">Family Name<span class="popuptext" id="hFamName">Enter your family name in the format you want it to appear on correspondence to you, e.g. Mr &amp; Mrs John Doe.</span></span></td><td class="value"><input name="family_name" type="text" value="<?php echo $rsFam['family_name']; ?>" maxlength='40'></td></tr>
	<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hAddress')">Address<span class="popuptext" id="hAddress">Enter your street address or mailing address.</span></span></td><td class="value"><input name="address" type="text" value="<?php echo $rsFam['address'];?>" maxlength='64'></td></tr>
	<tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hZip')">Zipcode<span class="popuptext" id="hZip">Enter your 5-digit zipcode.  We'll look up the city and state.</span></span></td><td class="value"><input name="zipcode" type="number" min="0" max="99999" value="<?php echo $rsFam['zipcode'];?>" maxlength='5'></td></tr>
    <tr><td class="label"><span class="popup" onclick="myPopUp('hCity')">City, State<span class="popuptext" id="hCity">You can't enter anything here.  We will calculate your city and state from your zipcode. Eh?  You're from Canada?  Call us to make sure you can still enter our country!</span></span></td><td class="value"><span><?php echo (strlen($city)>0 || strlen($state)>0) ? $city . ', ' . $state : ""; ?></span></td></tr>
    <tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hEmail')">Email<span class="popuptext" id="hEmail">Enter the email address to use for vbs correspondence.</span></span></td><td class="value"><input name="email" type="email" value="<?php echo $rsFam['email'];?>" maxlength='75'></td></tr>
    <tr><td class="label">*&nbsp;<span class="popup" onclick="myPopUp('hChurch')">Home Church<span class="popuptext" id="hChurch">Select your home church from the drop down list.  If not listed, select other and enter your home church into the comments box.</span></span></td><td class="value"><select name="lstHomeChurch" style="width:90%;">
    <option value="">Select Home Church</option>
	<?php do { ?>
	<option value="<?php echo $rsChurchList['HOME_CHURCH'];?>"<?php if (!(strcmp($rsChurchList['HOME_CHURCH'], $rsFam['home_church']))) {echo "selected=\"selected\"";} ?>><?php echo $rsChurchList['HOME_CHURCH']?></option>
    <?php } while ($rsChurchList = mysqli_fetch_assoc($rsChurchResult)); ?>
    </select></td></tr>
	<tr><td class="label"><span>Family Comments:</span></td><td class="value"><textarea name="comments" cols="" rows="3" style="width:90%;"><?php echo $rsFam['comments']; ?></textarea></td></tr>
    <tr><td class="label left"><span>*&nbsp;required</span></td><td class="value"><input type="submit" name="submit" value="Save"><span class="popup" style="margin-left:25%" onclick="myPopUp('help')">Help available<span class="popuptext" id="help">Use this form to update family information.  When done, click 'Next' to continue. Click the underlined labels for detailed popup help. Click again to close it.</span></span></td></tr>
</table>

	<input type="hidden" name="family_id" value="<?php echo $rsFam['family_id'];?>">
    <input type="hidden" name="city" value="<?php echo $city;?>">
    <input type="hidden" name="state" value="<?php echo $state;?>">
    <div id="buttonGroup" class="center">
		<input type="submit" name="submit" class="button" value="<?php echo HOME_BUTTON?>">
    	<input type="submit" name="submit" class="button" value="<?php echo PREVIOUS_BUTTON?>">
        <input type="submit" name="submit" class="button" value="<?php echo NEXT_PAGE?>">
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
