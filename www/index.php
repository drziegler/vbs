<?php
require_once('Connections/vbsDB.php');
require('vbsUtils.inc');
?>
<!doctype html>
<html class="">
<!--<![endif]-->
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>VBS Home - Hope Lutheran Church</title>
<link rel="stylesheet" type="text/css" href="css/layout.css" >
</head>
<body>
<div class="gridContainer border-on">
	<h1>Hope Lutheran Church</h1>
	<h2>VBS Information</h2>
	<div width="75%">
	<p id="welcome" class="border-on">Welcome to the VBS information page for Hope Lutheran Church.  VBS starts on Monday, July 8 and runs through July 12th.  
	Daily hours are 9 AM to 12 PM with a closing program on Friday evening at 7 PM.  A $15 donation is recommended for each student which includes the price of the T-shirt.  
	This is collected during vacation bible school. Volunteers can order a T-shirt for $5 also payable during VBS. 	All students and staff must register to attend VBS.  
	On-line registration is easy.</br>Registration questions?  Email the VBS office (<a href="mailto:vbs@hopecherryville.org" class="email">vbs@hopecherryville.org</a>) or call 610&#8209;767&#8209;7203 Ext 17.</br>
	Clearance questions?  Email the <a href="mailto:clearances@hopecherryville.org">Clearance Coordinator</a>.</p>
	</div>
	<div class="buttonGroup center">
   		<a href="search.php"><div id="mnuBtn" class="register"><span >Register</span></div></a>
   	</div>
	<div id="Footer"><div id="counter">Over&nbsp;<?php echo registrationCount();?>&nbsp;students&nbsp;in the dungeon.</div>&copy; <?php echo date("Y");?> David R Ziegler &amp; Hope Lutheran Church</div>
</div>
</body>
</html>
