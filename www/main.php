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
<link href="css/ textural.css" rel="stylesheet" type="text/css">
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="scripts/respond.min.js"></script>
</head>
<body>
<div id="Register" class="gridContainer">
<div><h1>Hope Lutheran Church</h1></div>
<div><h2>VBS-Home Page</h2></div>
<div id="Menu1">
    <a href="photos.html"><div id="mnuBtn" class="pictures"><span>Photos</span></div></a>
    <a href="stats.html"><div id="mnuBtn" class="stats"><span>Statistics</span></div></a>
</div>
<!--
<div id="Menu2">
    <a href="student.php"><div id="mnuBtn" class="student"><span>Students</span></div></a>
    <a href="staff.php"><div id="mnuBtn" class="staff"><span>Volunteers</span></div></a>
</div>  
-->
<div id="Menu3" class="mnuButton bottom"><a href="http://vbs.hopecherryville.org">Back</a></div>
<div id="Footer">&copy; 2016 David R Ziegler &amp; Hope Lutheran Church</div>
</div>
</body>
</html>
