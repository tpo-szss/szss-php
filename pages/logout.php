<?php

if (!defined('IncludeAccess')) {
   die('Direkten dostop ni dovoljen!');
}

session_start();
$_SESSION = array();
session_destroy();
header("Location: login.php");
exit();

?>