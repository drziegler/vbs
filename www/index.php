<?php
require_once('Connections/vbsDB.php');
require('vbsUtils.inc');
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
<title>VBS Home - Hope Lutheran Church</title>
<!-- 
<link rel="stylesheet" media="screen and (min-width: 320px) and (max-height: 640px)" href="./css/mobile.css?v2" >
<link rel="stylesheet" media="screen and (max-width: 640px) and (min-height: 320px)" href="./css/mobile.css?v2" >
<link rel="stylesheet" media="screen and (min-width: 768px) and (max-width: 1024px)" href="./css/tablet.css?v2" >
<link rel="stylesheet" media="screen and (min-width: 1224px)" href="./css/desktop.css?v1" >
-->
<link rel="stylesheet" type="text/css" href="css/layout.css" >
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!--   <script src="scripts/respond.min.js"></script> -->
</head>
<body>
<div class="gridContainer">
	<h1>Hope Lutheran Church</h1>
	<h2>VBS Information</h2>
	<p>Welcome to the VBS information page for Hope Lutheran Church.  VBS starts on Monday, July 8 and runs through July 12th.  
	Daily hours are 9 AM to 12 PM with a closing program on Friday evening at 7 PM.  A $15 donation is recommended for each student which includes the price of the T-shirt.  
	This is collected during vacation bible school. Volunteers can order a T-shirt for $5 also payable during VBS. 	All students and staff must register to attend VBS.  
	On-line registration is easy. Registration questions?  Email the VBS office (<a href="mailto:vbs@hopecherryville.org" class="email">vbs@hopecherryville.org</a>) or call 610-767-7203 Ext 17.  Clearance questions?  Email the <a href="mailto:clearances@hopecherryville.org">Clearance Coordinator</a>.</p>
	<div class="buttonGroup center">
   		<a href="register.php"><div id="mnuBtn" class="register">Register</div></a>
   		<div id="mnuBtn" class="schedule">Schedule</div>
   	</div>
	<div id="Footer"><div id="counter">Over&nbsp;<?php echo registrationCount();?>&nbsp;students&nbsp;marooned on Mars</div>&copy; <?php echo date("Y");?> David R Ziegler &amp; Hope Lutheran Church</div>
</div>
</body>
</html>
