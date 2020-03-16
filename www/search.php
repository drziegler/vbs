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
<html class="">
<head>
<title>VBS Search Page</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>VBS-Find Family</title>
<link href="css/layout.css?v2" rel="stylesheet" type="text/css">
</head>
<body>
<div id="Find" class="gridContainer-footer clearfix">
    <h1>Search</h1>
    <div id="dataLayout" class="horizontal-center">
    <div id="Search">
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post" name="frmPhone" target="_self">
        <table>
            <tr><td class="center">Enter your telephone number.</td></tr>
    		<tr><td class="center"><input name="txtPhone" id="searchPhone" type="number" pattern="\d{10}" maxlength="10" autofocus></td></tr>
        </table>
        <div id="buttonGroup" class="center">
    		<input type="submit" name="submit" class="button" value="<?php echo SEARCH ?>">&nbsp;
    		<input type="submit" name="submit" class="button" value="<?php echo HOME_BUTTON ?>">
        </div>
    </form>
    </div>
  	</div>
</div>
<?php include('footer.inc')?>
</body>
</html>
