<?php
//define('ENV',	'PROD');
define('ENV',	'DEV');

if (ENV=='PROD') {
	$hostname_vbsDB = "dziegler3.dotstermysql.com";
	$database_vbsDB = "vbs2";
	$username_vbsDB = "phpvbsuser";
	$password_vbsDB = "vbs13";
}
else {
	$hostname_vbsDB = "localhost";
	$database_vbsDB = "vbs2";
	$username_vbsDB = "phpvbsuser";
	$password_vbsDB = "vbs13";
}
$vbsDBi = mysqli_connect($hostname_vbsDB, $username_vbsDB, $password_vbsDB, $database_vbsDB);
?>