<?php
session_start();
if (!(isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid']))){
    header("location: ./login.php");
    die();
}
require_once('./models/user.class.php');
$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
(new User())->offline($uid);
session_unset();
session_destroy();
header("location: index.php");