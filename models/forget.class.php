<?php

require_once('database.class.php');
require_once('codeverfication.class.php');
require_once(__DIR__ . '/security.class.php');
class Forget {

	private $_link = null;
	function __construct(){
		$this->_link = (new Database())->connect();
	}

	/**
	 * Hash password using bcrypt
	 */
	private function hashPassword($password){
		return Security::hashPassword($password);
	}

	private function isEmail($email){
		// Remove all illegal characters from email
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		    return true;
		} else {
		    return false;
		}
	}

	private function isBetween($password, $min, $max){
		if (strlen($password) < $min || strlen($password) > $max)
			return false;
		else
			return true;
	}

	private function isUser($email){
		$stmt = $this->_link->prepare('SELECT email from users WHERE email = ?;');
		$stmt->bindParam(1, $email, PDO::PARAM_STR);
		if (!$stmt->execute()){
			$stmt = null;
			header("location: ../index.php?error=stmtfailed");
			die();
		}
		$stmt = null;
		return true; // Fix user enumeration vulnerability
	}

	private function UpdatePassword($email,$token,$password){
		$password = $this->hashPassword($password);
		$buffer = "";
		$stmt = $this->_link->prepare('UPDATE users SET password = ?, token = ? WHERE email = ? AND token = ? limit 1');
		$stmt->bindParam(1,$password,PDO::PARAM_STR);
		$stmt->bindParam(2,$buffer,PDO::PARAM_STR);
		$stmt->bindParam(3,$email,PDO::PARAM_STR);
		$stmt->bindParam(4,$token,PDO::PARAM_STR);
		$stmt->execute();
		return true;
	}

	public function forget($email){
		if (empty($email)){
			return 'Error: there is empty feild required';
		}
		elseif(!$this->isEmail($email)){
			return 'Error: your email is not valid';
			exit();
		}
		elseif(!$this->isUser($email)){
			return 'Error: If this email exists, a reset link has been sent'; // Don't reveal if user exists
			exit();
		}
		else{
			$one = 1;
			$token = (new CodeVerify())->SetCode();
			$stmt = $this->_link->prepare('UPDATE users SET token = ? WHERE email = ? limit 1');
			$stmt->bindParam(1,$token, PDO::PARAM_STR);
			$stmt->bindParam(2, $email, PDO::PARAM_STR);
			if (!$stmt->execute()){
				$stmt = null;
				header("location: ../index.php?error=stmtfailed");
				die();
			}
			$stmt = null;
			(new CodeVerify())->SendCodeTo($email,$token,'Dear','User','Update password','newpassword.php');
			return true;
		}
	}

	public function newPassword($email,$token,$password){
		if (empty($email) || empty($token) || empty($password)){
			return 'Error: there is empty feild required or invalid URL reopen the url from your email again';
		}
		elseif(!$this->isBetween(html_entity_decode($password), 4, 16)){
			return 'Error: password length must be between 4 and 16 charaters';
			exit();
		}
		elseif(!$this->isEmail($email)){
			return 'Error: your email is not valid reopen the url from your email again';
			exit();
		}
		elseif(!$this->isUser($email)){
			return 'Error: this user does not exist reopen the url from your email again'; // XSS
			exit();
		}else{
			$this->UpdatePassword($email,$token,$password);
			return true;
		}
	}
}