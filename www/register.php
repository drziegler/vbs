<?php
session_start();
require_once('Connections/vbsDB.php');
include_once('vbsUtils.inc');

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
<link href="css/boilerplate.css" rel="stylesheet" type="text/css">
<link href="css/layout.css" rel="stylesheet" type="text/css">
<link href="css/textural.css" rel="stylesheet" type="text/css">
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="scripts/respond.min.js"></script>
</head>
<body><!--  version 1 -->
<div id="Register" class="gridContainer">
<div><h1>Hope Lutheran Church</h1></div>
<div><h2>VBS-Registration</h2></div>
<div>
    <a href="search.php"><div id="mnuBtn" class="find"><span>Find</span></div></a>
    <a href="family.php"><div id="mnuBtn" class="family"><span>Family</span></div></a>
    <a href="student.php"><div id="mnuBtn" class="student"><span>Students</span></div></a>
    <a href="staff.php"><div id="mnuBtn" class="staff"><span>Volunteers</span></div></a>
</div>  
<div id="mnuButton"><a href="confirm.php">Validate and Confirm</a></div>
<div id="Footer">&copy; 2017 David R Ziegler &amp; Hope Lutheran Church</div>
</div>
</body>
</html>
