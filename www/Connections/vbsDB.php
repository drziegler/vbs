<?php

switch(VBS_ENV){
    case VBS_ENV_PROD :
        $hostname_vbsDB = 'dziegler3.dotstermysql.com';
        $database_vbsDB = 'vbs';    
        $username_vbsDB = 'phpvbsuser';
        $password_vbsDB = 'vbs19';
        break;
    case VBS_ENV_TEST :
        $hostname_vbsDB = 'dziegler3.dotstermysql.com';
        $database_vbsDB = 'vbs2';
        $username_vbsDB = 'phpvbsuser';
        $password_vbsDB = 'Jiaz5rsxnhazwBTJ';
        break;

    case VBS_ENV_DEV :
        $hostname_vbsDB = 'localhost';
        $database_vbsDB = 'vbs2';
        $username_vbsDB = 'phpvbsuser';
        $password_vbsDB = 'vbs13';
        break;
}
$vbsDBi = mysqli_connect($hostname_vbsDB, $username_vbsDB, $password_vbsDB, $database_vbsDB);
?>