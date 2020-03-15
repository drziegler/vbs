<?php
define('ENV',	'PROD');
//define('ENV', 'TEST');
//define('ENV',	'DEV');

switch(ENV){
    case 'PROD' :
        $hostname_vbsDB = "dziegler3.dotstermysql.com";
        $database_vbsDB = "vbs";
        $username_vbsDB = "phpvbsuser";
        $password_vbsDB = "vbs19";
        break;
    case 'TEST' :
        $hostname_vbsDB = "dziegler3.dotstermysql.com";
        $database_vbsDB = "vbs2";
        $username_vbsDB = "phpvbsusertest";
        $password_vbsDB = "vbs19";
        break;
        
    case 'DEV' :
        $hostname_vbsDB = "localhost";
        $database_vbsDB = "vbs2";
        $username_vbsDB = "phpvbsuser";
        $password_vbsDB = "vbs13";
        break;
}
$vbsDBi = mysqli_connect($hostname_vbsDB, $username_vbsDB, $password_vbsDB, $database_vbsDB);
?>