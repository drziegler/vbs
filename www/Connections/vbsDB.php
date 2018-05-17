<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
#$hostname_vbsDB = "localhost";
$hostname_vbsDB = "dziegler3.dotstermysql.com";
$database_vbsDB = "vbs2";
$username_vbsDB = "phpvbsuser";
$password_vbsDB = "vbs13";
$vbsDB = mysql_pconnect($hostname_vbsDB, $username_vbsDB, $password_vbsDB) or trigger_error(mysql_error(),E_USER_ERROR);
$vbsDBi = mysqli_connect($hostname_vbsDB, $username_vbsDB, $password_vbsDB, $database_vbsDB);
?>