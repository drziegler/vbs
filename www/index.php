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
<div class="gridContainer">
	<h1>Hope Lutheran Church</h1>
	<h2>VBS Information</h2>
	<div id="Find">
	<p>We are happy to announce our 2020 VBS schedule which starts Monday, July 8th and runs daily through Friday, July 12th from 9 AM to 12 noon.  Our closing program 
	will be on Friday evening at 7 PM.  A $15 donation is suggested per student which includes the cost of a T-shirt, crafts and instructional materials.  The donation is 
	collected during the week of vacation bible school. Volunteers can order a T-shirt for $5 which is also collected during VBS. 	All students and staff are required 
	to register to attend VBS.	Click the window below to register.<br><br>
	All volunteers are required to have proper documentation and clearances processed before VBS begins.  
	More information can be found on our clearances page at <a href="http://clearances.hopecherryville.org">clearances.hopecherryville.org</a> or email our Clearance Coordinator 
	(<a href="mailto:clearances@hopecherryville.org">clearances@hopecherryville.org</a>) with your questions.<br><br>
	If you have registration questions, email the VBS office (<a href="mailto:vbs@hopecherryville.org" class="email">vbs@hopecherryville.org</a>) or call 610&#8209;767&#8209;7203 Ext 17.
	</p>
	</div>
	<div class="buttonGroup center">
   		<a href="search.php"><div id="mnuBtn" class="register"><span >Register</span></div></a>
   	</div>
	<div id="Footer"><div id="counter">Over&nbsp;<?php echo registrationCount();?>&nbsp;students&nbsp;in the dungeon.</div>&copy; <?php echo date("Y");?> David R Ziegler &amp; Hope Lutheran Church</div>
</div>
</body>
</html>
