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
<link rel="stylesheet" type="text/css" href="css/layout.css?v3" >
</head>
<body>
<div id="Find">
<div id="Register" class="gridContainer-footer">
	<h1>Hope Lutheran Church</h1>
	<h2>VBS Information</h2>

	<p>VBS for 2020 will start Monday, July 8th and runs daily through Friday, July 12th from 9 AM to 12 noon.  Our closing program 
	will be on Friday evening at 7 PM.  A $15 donation is suggested per student which includes the cost of a T-shirt, crafts and instructional materials.  The donation is 
	collected during the week of vacation bible school. Volunteers can order a T-shirt for $5 which is also collected during VBS. 	Volunteers are required to have all 
	clearances completed prior to the start of VBS and all students and staff must register to attend VBS.<br><br>
	Registrations can be completed by clicking the link below.  Volunteers will be provided with more clearance information at the end of the registration process.
	More information can be found on our clearances page at <a href="http://clearances.hopecherryville.org">clearances.hopecherryville.org</a> or email our Clearance Coordinator 
	(<a href="mailto:clearances@hopecherryville.org">clearances@hopecherryville.org</a>) with your questions.<br><br>
	If you have registration questions, email the VBS office (<a href="mailto:vbs@hopecherryville.org" class="email">vbs@hopecherryville.org</a>) or call 610&#8209;767&#8209;7203 Ext 17.
	</p>
	<div class="buttonGroup center">
   		<a href="search.php"><div id="mnuBtn"><span>Register</span></div></a>
   	</div>
</div>
</div>
<div id="Footer" class="center"><div id="counter">Over&nbsp;<?php echo registrationCount();?>&nbsp;students&nbsp;in the dungeon.</div>&copy; <?php echo date("Y");?> David R Ziegler &amp; Hope Lutheran Church</div>
</body>
</html>
