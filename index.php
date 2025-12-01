<?php 
if(!isset($_SESSION['IS_LOGGED'])){
	require_once('./login.php');
}else{
	require_once('./home.php');
}

?>

