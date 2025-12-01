<?php
require_once('database.class.php');

	if (!isset($_GET['email']) || !isset($_GET['token'])) {
		header("location: ../index.php?error=wrong");
		die();
	} else {
		$_link = (new Database())->connect();
		$email = $_GET['email'];
		$token = $_GET['token'];
		$stmt = $_link->prepare('SELECT email from users WHERE email = ? AND token = ? AND isEmailConfirmed = ?;');
		$stmt->bindParam(1, $email, PDO::PARAM_STR);
		$stmt->bindParam(2, $token, PDO::PARAM_STR);
		$isEmailConfirmed = 0;
		$stmt->bindParam(3, $isEmailConfirmed, PDO::PARAM_INT);
		if (!$stmt->execute()){
			$stmt = null;
			header("location: ../index.php?error=stmtfailed");
			die();
		}
		if ($stmt->rowCount() > 0){
			$stmt = $_link->prepare('UPDATE users SET isEmailConfirmed = :isEmailConfirmed , token = :token WHERE email = :email;');
			$isEmailConfirmed = 1;
			$stmt->bindParam(':isEmailConfirmed', $isEmailConfirmed, PDO::PARAM_INT);
			$token = '';
			$stmt->bindParam(':token', $token, PDO::PARAM_STR);
			$stmt->bindParam(':email', $email, PDO::PARAM_INT);
			if (!$stmt->execute()){
				$stmt = null;
				header("location: ../index.php?error=stmtfailed");
				die();
			}
			$stmt = null;
			header("location: ../home.php?error=none");
			die();
		}
		header("location: ../index.php?error=wrong");
		die();
	}