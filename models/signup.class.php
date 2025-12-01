<?php

require_once('database.class.php');
require_once('codeverfication.class.php');

class Signup {

	private $_link = null;
	
	function __construct(){
		$this->_link = (new Database())->connect();
	}

	protected function isUser($email){
		$stmt = $this->_link->prepare('SELECT email from users WHERE email = ?;');
		$stmt->bindParam(1, $email, PDO::PARAM_STR);
		if (!$stmt->execute()){
			$stmt = null;
			header("location: ../index.php?error=stmtfailed");
			die();
		}
		if ($stmt->rowCount() == 0){
			return false;
		}
		if ($stmt->rowCount() > 0){
			return true;
		}

		$stmt = null;
	}
	protected function SignUp ($fname,$lname,$email,$password,$gender,$token) {
		$stmt = $this->_link->prepare('INSERT INTO users (fname, lname, email, password, gender, isEmailConfirmed, token, likes, profileimg, about, status)
		 VALUES (?,?,?,?,?,?,?,?,?,?,?);');

		$stmt->bindParam(1, $fname, PDO::PARAM_STR);
		$stmt->bindParam(2, $lname, PDO::PARAM_STR);
		$stmt->bindParam(3, $email, PDO::PARAM_STR);
		$stmt->bindParam(4, $password, PDO::PARAM_STR);
		$stmt->bindParam(5, $gender ,PDO::PARAM_STR);
		$isEmailConfirmed = 0;
		$stmt->bindParam(6, $isEmailConfirmed ,PDO::PARAM_INT);
		$stmt->bindParam(7, $token ,PDO::PARAM_STR);
		$stmt->bindParam(8, $isEmailConfirmed ,PDO::PARAM_INT);
		$defaultImg = '';
		$stmt->bindParam(9, $defaultImg, PDO::PARAM_STR);
		$defaultAbout = '';
		$stmt->bindParam(10, $defaultAbout, PDO::PARAM_STR);
		$defaultStatus = 'offline';
		$stmt->bindParam(11, $defaultStatus, PDO::PARAM_STR);

		if (!$stmt->execute()){
			$stmt = null;
			header("location: ../index.php?error=stmtfailed");
			die();
		}
		$lastInsertId = $this->_link->lastInsertId(Database::isPostgres() ? 'users_uid_seq' : null);
		$offline = "offline";
		$stmt = $this->_link->prepare('UPDATE users SET status = ? WHERE uid = ?');
		$stmt->bindParam(1,$offline,PDO::PARAM_STR);
		$stmt->bindParam(2,$lastInsertId,PDO::PARAM_INT);
		$stmt->execute();
		(new CodeVerify())->SendCodeTo($email,$token,$fname,$lname,'Confirm your email','models/confirm.php');
		return true;
		header("location: ../login.php?error=none");
		$stmt = null;
	}
}