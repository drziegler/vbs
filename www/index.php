<?php
require_once('Connections/vbsDB.php');
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
<title>VBS Home - Hope Lutheran Church</title>
<link rel="stylesheet" media="screen and (min-width: 320px) and (max-width: 480px)" href="css/mobile.css" >
<link rel="stylesheet" media="screen and (min-width: 768px) and (max-width: 1024px)" href="css/tablet.css" >
<link rel="stylesheet" media="screen and (min-width: 1224px)" href="css/desktop.css" >

<!-- <link href="css/layout.css" rel="stylesheet" type="text/css"> -->
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!--   <script src="scripts/respond.min.js"></script> -->
</head>
<body>
<div class="gridContainer">
	<h1>Hope Lutheran Church</h1>
	<h2>VBS Information</h2>
<<<<<<< Updated upstream
	<p>Welcome to the VBS information page for Hope Lutheran Church.  VBS starts on Monday, July 8 and runs through July 12th.  Daily hours are 9 AM to 12 PM with a closing program on Friday evening at 7 PM.  All students and staff must register to attend VBS.  On-line registration is easy. Registration questions?  Email the VBS office (<a href="mailto:vbs@hopecherryville.org" class="email">vbs@hopecherryville.org</a>) or call 610-767-7203 Ext 17.  Clearance questions?  Email the <a href="mailto:clearances@hopecherryville.org">Clearance Coordinator</a>.</p>
    <div id="logo"><a href="register.php"><img src="images/vbs-logo.jpg"></a></div>
	<div id="counter"><?php echo registrationCount() ?>students&nbsp;marooned on Mars</div>
	<div id="Footer">&copy;<?php echo date("Y") ?> David R. Ziegler &amp; Hope Lutheran Church</div>
=======
	<div id="Find" width="75%">
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
>>>>>>> Stashed changes
</div>
</body>
</html>
