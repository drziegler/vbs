<?php
session_start();
include_once('vbsUtils.inc');

define("SEARCH",	"Search");

if (!empty($_POST['submit'])){ 
switch ($_POST['submit']) {
	case HOME_BUTTON:
		header ("Location: " . HOME_PAGE);
		break;
	case SEARCH:
		header ("Location: results.php?txtPhone=".$_POST['txtPhone']);
		break;
}
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
<title>VBS Search Page</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>VBS-Find Family</title>
<link href="css/layout.css?v2" rel="stylesheet" type="text/css">
<script src="scripts/respond.min.js"></script>
</head>
<body>
<div id="Find" class="gridContainer clearfix">
    <h1>Search</h1>
    <div id="dataLayout" class="vertical-horizontal-center">
    <div id="Search">
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post" name="frmPhone" target="_self">
        <table>
            <tr><td class="center">Enter your telephone number.</td></tr>
    		<tr><td class="center"><input name="txtPhone" id="searchPhone" type="number" pattern="[1-9]" autofocus></td></tr>
        </table>
        <div id="buttonGroup" class="center">
    		<input type="submit" name="submit" class="button" value="<?php echo SEARCH ?>">&nbsp;
    		<input type="submit" name="submit" class="button" value="<?php echo HOME_BUTTON ?>">
        </div>
    </form>
    </div>
  	</div>
</div>
</body>
</html>
