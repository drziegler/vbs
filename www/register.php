<?php
session_start();
require_once('Connections/vbsDB.php');
include_once('vbsUtils.inc');
define('FILE_NAME',   '[REGISTER] ');

$confoNo = mt_rand();
$_SESSION['confoNo'] = $confoNo;

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
<!--  <link href="css/boilerplate.css" rel="stylesheet" type="text/css"> -->
<link href="css/layout.css?v3" rel="stylesheet" type="text/css">
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="scripts/respond.min.js"></script>
</head>
<body>
<div id="Register" class="gridContainer-footer">
<h1 class='center'>Hope Lutheran Church</h1>
<table style='border:none; padding:0px; margin:0;' width='100%'><tr><td width="10%"><a href="/index.php"><input type="submit" class="button" name="submit" value="<?php echo HOME_BUTTON?>" ></a></td>
	<td width="80%" align="center" class="h2" style='padding:0; margin:0;'>VBS-Registration</td><td width="10%">&nbsp;</td></tr></table>
<div class="center vertical-center">
	<a href="search.php"><div id="mnuBtn" class="find"><span>Find</span></div></a>
    <a href="<?php echo FAMILY_PAGE ?>"><div id="mnuBtn" class="family"><span>Family</span></div></a>
    <a href="student.php"><div id="mnuBtn" class="student"><span>Students</span></div></a>
    <a href="staff.php"><div id="mnuBtn" class="staff"><span>Volunteers</span></div></a>
</div>
</div>
<div id="Footer"><div id="counter">Over&nbsp;<?php echo registrationCount();?>&nbsp;students&nbsp;in the dungeon.</div>&copy; <?php echo date("Y");?> David R Ziegler &amp; Hope Lutheran Church</div>
</div>
</body>
</html>
