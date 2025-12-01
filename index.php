<?php
session_start();
if (isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid'])) {
    header("Location: ./home.php");
} else {
    header("Location: ./login.php");
}
exit;

