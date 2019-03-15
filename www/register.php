<?php
session_start();
require_once('Connections/vbsDB.php');
include_once('vbsUtils.inc');

$confoNo = mt_rand();
$_SESSION['confoNo'] = $confoNo;

$sqlQuery = "SELECT count(*) as registered from students where registered = 'C'";
$result = mysqli_query($vbsDBi, $sqlQuery);
$registered = mysqli_fetch_assoc($result);
mysqli_free_result($result);
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
<title>VBS Registration</title>
<link href="css/boilerplate.css" rel="stylesheet" type="text/css">
<link href="css/layout.css" rel="stylesheet" type="text/css">
<!--<link href="css/textural.css" rel="stylesheet" type="text/css"> -->
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="scripts/respond.min.js"></script>
</head>
<body><!--  version 1 -->
<div id="Register" class="gridContainer">
<div><h1>Hope Lutheran Church</h1></div>
<table style="border-style: none;"><tr><td width="10%"><a href="/index.php"><input type="submit" class="button" name="submit" value="<?php echo HOME_BUTTON?>"></a></td>
	<td width="80%" align="center" class="h2">VBS-Registration</td><td width="10%">&nbsp;</td></tr></table>
<div>
	<a href="search.php"><div id="mnuBtn" class="find"><span>Find</span></div></a>
    <a href="<?php echo FAMILY_PAGE ?>"><div id="mnuBtn" class="family"><span>Family</span></div></a>
    <a href="student.php"><div id="mnuBtn" class="student"><span>Students</span></div></a>
    <a href="staff.php"><div id="mnuBtn" class="staff"><span>Volunteers</span></div></a>
</div>
<div id="counter">Over&nbsp;<?php echo $registered['registered']?>&nbsp;students&nbsp;marooned on Mars</div>
<!-- FOR TEST ENV ONLY <div id="counter">Over 20,000 Shipwrecked!</div> -->
<div id="Footer">&copy; <?php echo date("Y");?> David R Ziegler &amp; Hope Lutheran Church</div>
</div>
</body>
</html>
