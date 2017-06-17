<?php
require_once('Connections/vbsDB.php');
$sqlQuery = "SELECT count(*) as registered from students where registered = 'Y'";
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
<link href="css/boilerplate.css" rel="stylesheet" type="text/css">
<link href="css/vbs.css" rel="stylesheet" type="text/css">
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="scripts/respond.min.js"></script>
</head>
<body style="background-color:yellow">
<div class="gridContainer clearfix">
	<div><h1>Hope Lutheran Church</h1></div>
	<div><h2>VBS Information</h2></div>
	<p>Welcome to the VBS Home Page for Hope Lutheran Church.  Check back often as new information will be posted regarding events, theme days and other important information.</p><p>Opening day is Monday, July 10 and will run through Friday, July 14.  Daily hours are 9 AM to 12 PM.  The closing program will be held Friday evening at 7 PM.</p>
    <p class="red"><b>As of Thursday, June 15, we are at maximum capacity.  Click the Hero Logo below to be placed on our waiting list. You will be advised when an opening becomes available."</b></p>
    <div id="logo" class="center"><a href="register.php"><img src="images/hero-logo.png"></a></div>
    <div id="counter" class="center"><?php echo $registered['registered']?>&nbsp;students&nbsp;registered</div>
</div>
<div id="Footer" style="font-size:10px">&copy;2017 David R. Ziegler &amp; Hope Lutheran Church</div>
</body>
</html>
