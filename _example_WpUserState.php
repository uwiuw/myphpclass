<?php
/**************************************************************************************************
 * basic example of using WpUserState class
 **************************************************************************************************/
include('D:/xampp/htdocs/myblog/personal/uwiuw/version/class/UserState.php');

$WpUserState = new WpUserState('sqlModule.php');
$userRegister = new userRegister();
$result = $userRegister::isProtectedUser(2, TRUE );
$hasildebug = print_r($result, TRUE);
echo '<pre style="font-size:14px">' . '$userRegister : ' . htmlentities2($hasildebug) . '</pre>';
