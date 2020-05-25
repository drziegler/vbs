<?php
session_start();
include_once('vbsUtils.inc');
define("USE_THIS_RECORD",	"Use this record");
define("SEARCH_AGAIN",		"Search again");
define("NEXT_FAMILY",       "Next family");
define("PREVIOUS_FAMILY",   "Previous family");
define("NEW_FAMILY",		"New family");
define('FILE_NAME',         '[RESULTS] ');
$button = Array();
$numFamilies = 0;

/* We are coming back to ourselves, process as necessary */
if ((DEBUG) and !empty($_REQUEST['submit'])) {
    print '$_REQUEST[\'submit\'] = ' . $_REQUEST['submit'] . "<br>";
}
if (!empty($_REQUEST['submit'])){
    $offset=$_REQUEST['offset'];
    $numFamilies=$_REQUEST['numFamilies'];
    $_REQUEST['txtPhone']=$_REQUEST['txtPhone'];

    switch ($_REQUEST['submit']) {
    	case USE_THIS_RECORD :
    		if (DEBUG) print "Line: " . __LINE__ . "<br>";
    		$_SESSION['family_id'] = $_REQUEST['family_id'];
    		//@@$_SESSION['family_name'] =  /* mal-formed line causing variable to be mis-populated ! */
    		$_SESSION['search_phone'] = $_REQUEST["txtPhone"];
    		insertStats($vbsDBi, $_REQUEST['family_id'], 'reused');
    		header("Location: " . FAMILY_PAGE);
    		break;
    	case SEARCH_AGAIN :
    		if (DEBUG) print "Line: " . __LINE__ . "<br>";
    		$_SESSION['family_id'] = 0;
    		header("Location: search.php");
    		break;
    	case NEW_FAMILY :
    		if (DEBUG) print "Line: " . __LINE__ . "<br>";
    		if (!empty($_REQUEST['family_id'])){
    			/* This means we found an existing record but the user doesn't want to use it.  We need to clear out the
    			   phone number used to search so that we don't create a new phone entry with the existing phone number
    			   for the new family id */
    			//--unset($_SESSION['newPhone']);
    		}
    		$_SESSION['search_phone'] = $_REQUEST["txtPhone"];
    		$_SESSION['family_id']='New';
    		header("Location: " . FAMILY_PAGE);
    		break;
    }
}
else {
    $_REQUEST['submit']='';    /* Avoid the undeclared error */
    $offset = 0;            /* Initialize the offset variable */
}

switch ($_REQUEST['submit']) {
    case PREVIOUS_RECORD :
        if (DEBUG) print "Line " . __LINE__ . "-Previous<br>";
        $offset = $offset - 1;
        break;
    case NEXT_RECORD :
        if (DEBUG) print "Line " . __LINE__ . "-Next<br>";
        $offset = $offset + 1;
        break;
}


if (isset($_REQUEST["txtPhone"])) {
	if (DEBUG) print "Line: " . __LINE__ . "<br>";
	$query_rsFamily = "SELECT FAM.*, TRIM(CONCAT(COALESCE(ZIP.city,''), ' ', COALESCE(ZIP.state,''), ' ', FAM.zipcode)) city FROM family FAM ";
	$query_rsFamily .= "JOIN phone_numbers PHONE ON FAM.family_id = PHONE.family_id LEFT JOIN zipcodes ZIP ON FAM.zipcode = ZIP.zipcode ";
	$query_rsFamily .= "WHERE PHONE.phone = " . unformatPhone($_REQUEST["txtPhone"]);
	$rsFamily = mysqli_query($vbsDBi, $query_rsFamily);
	if ($rsFamily){
		if (DEBUG) print "Line: " . __LINE__ . "<br>";
		$row_rsFamily = mysqli_fetch_assoc($rsFamily);
		$numFamilies = mysqli_num_rows($rsFamily);
		$query_limit_rsFamily = sprintf("%s LIMIT %d, %d", $query_rsFamily, $offset, $numFamilies);
		$rsStudent = mysqli_query($vbsDBi, $query_limit_rsFamily);
		$row_rsFamily = mysqli_fetch_assoc($rsStudent);

		if (DEBUG) print "Total rows: " . $numFamilies;
		if ($numFamilies ==0){
			$errMsg = "No records found matching phone number " . formatPhone($_REQUEST["txtPhone"]);}
		}
	else {
		if (DEBUG) print "Line: " . __LINE__ . "<br>";
		$_SESSION['newPhone'] = $_REQUEST["txtPhone"];  		/* This will be used to create the first reference phone record for this family id */
		$errMsg = "No records found matching phone number " . formatPhone($_REQUEST['txtPhone']);}
	}
else {
	if (DEBUG) print "Line: " . __LINE__ . "<br>";
	$errMsg = "No phone number submitted to search. Try again.";
	writeLog(FILE_NAME.__LINE__." No search phone number entered.");
}

/* Set the button disabled properties */
$offset = ++$offset;
$button['Previous'] = ($numFamilies > 1 and $offset > 1) ? '' : ' disabled';
$button['Next'] = ($numFamilies > 1 and $offset<($numFamilies)) ? '' : ' disabled';
$button['New'] = '';
$offset = --$offset;

?>
<!doctype html>
<html class="">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>VBS Search Results</title>
<link href="css/layout.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="Find" class="gridContainer-footer">
	<h1>Search Results</h1>
    <div id="dataLayout">
    <div id="Result">
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post" name="frmResults" target="_self">
	<?php if ($numFamilies == 0){ ?>
	<table>
		<tr><td class="center"><?php echo $errMsg?><input name="family_id" type="hidden" value="0"></td></tr>
	<?php } else {?>
	<table>
		<tr><td class="center"><?php echo $row_rsFamily['family_name']; ?></td></tr>
        <tr><td class="center"><?php echo $row_rsFamily['address']; ?><input type="hidden" name="address" value="<?php echo $row_rsFamily['address']; ?>"></td></tr>
        <tr><td class="center"><?php echo $row_rsFamily['city']; ?></td></tr>
        <tr><td class="center"><input name="family_id" type="hidden" value="<?php echo $row_rsFamily['family_id']; ?>"><?php echo $row_rsFamily['email']; ?></td></tr>
	<?php if ($numFamilies>1) {?>
	    <tr><td>
	    <div id="buttonSubGroup" class="center">
    		Displaying family <?php echo (($numFamilies >0)?$offset+1:0)?> of <?php echo $numFamilies ?><br>
			<input type="submit" class="button" name="submit" value="<?php echo PREVIOUS_RECORD?>" <?php echo $button['Previous'];?>>&nbsp;
			<input type="submit" class="button" name="submit" value="<?php echo NEXT_RECORD?>" <?php echo $button['Next'];?>>&nbsp;
		</div>
		</td></tr>
    <?php } } ?>
	</table>
	<div id="buttonGroup" class="center">
	<?php if ($numFamilies > 0){ ?>
		<input type="submit" name="submit" class="button" value="<?php echo USE_THIS_RECORD ?>">&nbsp;
	<?php } ?>
		<input type="submit" name="submit" class="button" value="<?php echo SEARCH_AGAIN ?>">&nbsp;
		<input type="submit" name="submit" class="button" value="<?php echo NEW_FAMILY ?>">
	</div>
    <input name="offset" type="hidden" value="<?php echo $offset;?>">
    <input name="numFamilies" type="hidden" value="<?php echo $numFamilies;?>">
    <input name="txtPhone" type="hidden" value="<?php echo $_REQUEST['txtPhone']?>">
    </form>
</div></div>
</div>
<?php
include('footer.inc');
@mysqli_free_result($rsFamily);
?>
</body>
</html>
