<?php 
session_start();
require_once('./Connections/vbsDB.php');
include_once('vbsUtils.inc'); 
define("USE_THIS_RECORD",	"Use this record");
define("SEARCH_AGAIN",		"Search again");
define("NEW_FAMILY",		"New family");
$totalRows_rsFamily = 0;

/* We are coming back to ourselves, process as necessary */
if (!empty($_POST['submit'])){
switch ($_POST['submit']) {
	case USE_THIS_RECORD :
		if (DEBUG) print "Line: " . __LINE__ . "<br>";
		$_SESSION['family_id'] = $_POST['family_id'];
		$_SESSION['family_name'] = 
		insertStats($vbsDBi, $_POST['family_id'], 'reused');
		header("Location: " . FAMILY_PAGE);
		break;
	case SEARCH_AGAIN :
		if (DEBUG) print "Line: " . __LINE__ . "<br>";
		$_SESSION['family_id'] = 0;
		header("Location: search.php");
		break;
	case NEW_FAMILY :
		if (DEBUG) print "Line: " . __LINE__ . "<br>";
		if (!empty($_POST['family_id'])){
			/* This means we found an existing record but the user doesn't want to use it.  We need to clear out the
			   phone number used to search so that we don't create a new phone entry with the existing phone number
			   for the new family id */
			unset($_SESSION['newPhone']);
		}
		$_SESSION['family_id']='New';
		header("Location: " . FAMILY_PAGE);
		break;
}
}


if (isset($_REQUEST["txtPhone"])) {
	if (DEBUG) print "Line: " . __LINE__ . "<br>";
	$query_rsFamily = "SELECT FAM.*, TRIM(CONCAT(COALESCE(ZIP.city,''), ' ', COALESCE(ZIP.state,''), ' ', FAM.zipcode)) city FROM family FAM ";
	$query_rsFamily .= "JOIN phone_numbers PHONE ON FAM.family_id = PHONE.family_id LEFT JOIN zipcodes ZIP ON substr(FAM.zipcode,1,5) = ZIP.zipcode ";
	$query_rsFamily .= "WHERE PHONE.phone = " . unformatPhone($_REQUEST["txtPhone"]);
	$rsFamily = mysqli_query($vbsDBi, $query_rsFamily);
	if ($rsFamily){
		if (DEBUG) print "Line: " . __LINE__ . "<br>";
		$row_rsFamily = mysqli_fetch_assoc($rsFamily);
		$totalRows_rsFamily = mysqli_num_rows($rsFamily);
		if (DEBUG) print "Total rows: " . $totalRows_rsFamily;
		if ($totalRows_rsFamily ==0){
			$errMsg = "No records found matching phone number " . formatPhone($_REQUEST["txtPhone"]);}			
		}
	else {		
		if (DEBUG) print "Line: " . __LINE__ . "<br>";
		//@@
		print mysqli_error($vbsDBi);
		$_SESSION['newPhone'] = $_REQUEST["txtPhone"];  		/* This will be used to create the first reference phone record for this family id */
		$errMsg = "No records found matching phone number " . formatPhone($_REQUEST["txtPhone"]);}
	}
else {
	if (DEBUG) print "Line: " . __LINE__ . "<br>";
	$errMsg = "No phone number submitted to search. Try again.";
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
<title>VBS Search Results</title>
<link href="css/layout.css" rel="stylesheet" type="text/css">
<link href="css/boilerplate.css" rel="stylesheet" type="text/css">
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="scripts/respond.min.js"></script>
</head>
<body>
<div id="Find" class="gridContainer">
	<div><h1>VBS - Search Results</h1></div>
    <div id="dataLayout">
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post" name="frmResults" target="_self">
	<?php if ($totalRows_rsFamily == 0){ ?>
		<div><h2><?php echo $errMsg?><input name="family_id" type="hidden" value="0"></h2></div>
	<?php } else {?>
	<table cellspacing="0">
		<tr><td class="center"><?php echo $row_rsFamily['family_name']; ?></td></tr>
        <tr><td class="center"><?php echo $row_rsFamily['address']; ?><input type="hidden" name="address" value="<?php echo $row_rsFamily['address']; ?>"></td></tr>
        <tr><td class="center"><?php echo $row_rsFamily['city']; ?></td></tr>
        <tr><td class="center"><input name="family_id" type="hidden" value="<?php echo $row_rsFamily['family_id']; ?>"><?php echo $row_rsFamily['email']; ?></td></tr>
        <?php } ?>
	</table>
	<div id="buttonGroup" class="center">
	<?php if ($totalRows_rsFamily > 0){ ?>
		<input type="submit" name="submit" class="button" value="<?php echo USE_THIS_RECORD ?>">&nbsp;
	<?php } ?>        
		<input type="submit" name="submit" class="button" value="<?php echo SEARCH_AGAIN ?>">&nbsp;
		<input type="submit" name="submit" class="button" value="<?php echo NEW_FAMILY ?>">
	</div>
    </form>
</div></div>
</body>
</html>
<?php
@mysqli_free_result($rsFamily);
?>
