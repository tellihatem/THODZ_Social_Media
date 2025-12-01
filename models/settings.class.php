<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!(isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid']))){
    header("location: ../login.php");
    die();
}
require_once('database.class.php');
require_once('user.class.php');
require_once('codeverfication.class.php');
require_once(__DIR__ . '/security.class.php');

class Settings {

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

	private function isPasswordUser($currentPassword){
		$user = new User();
		$user_data = $user->getData($_SESSION['uid']);
		if (is_array($user_data)){
			$storedHash = $user_data['password'];
			// Use Security class for password verification (supports legacy MD5)
			return Security::verifyPassword($currentPassword, $storedHash);
		}
		return false;
	}

	private function isPasswordsMatch($password,$rpassword){
		if ($password == $rpassword)
			return true;
		return false;
	}

	private function UpdateRequiredField($fname,$lname,$email){
		$user = new User();
		$user_data = $user->getData($_SESSION['uid']);
		if (is_array($user_data)){
			if ($user_data['email'] == $email){
				$stmt = $this->_link->prepare('UPDATE users SET fname = ?, lname = ? WHERE uid = ?');
				$stmt->bindParam(1,$fname,PDO::PARAM_STR);
				$stmt->bindParam(2,$lname,PDO::PARAM_STR);
				$stmt->bindParam(3,$_SESSION['uid'],PDO::PARAM_INT);
				$stmt->execute();
			}else{
				$zero = 0;
				$token = (new CodeVerify())->SetCode();;
				$stmt = $this->_link->prepare('UPDATE users SET fname = ?, lname = ?, email = ?, isemailconfirmed = ? ,token = ? WHERE uid = ?');
				$stmt->bindParam(1,$fname,PDO::PARAM_STR);
				$stmt->bindParam(2,$lname,PDO::PARAM_STR);
				$stmt->bindParam(3,$email,PDO::PARAM_STR);
				$stmt->bindParam(4,$zero,PDO::PARAM_STR);
				$stmt->bindParam(5,$token,PDO::PARAM_STR);
				$stmt->bindParam(6,$_SESSION['uid'],PDO::PARAM_INT);
				$stmt->execute();
				(new CodeVerify())->SendCodeTo($email,$token,$fname,$lname,'Confirm your email','models/confirm.php');
			}
			return true;
		}
		return false;
	}

	private function UpdatePassword($password){
		$password = $this->hashPassword($password);
		$stmt = $this->_link->prepare('UPDATE users SET password = ? WHERE uid = ?');
		$stmt->bindParam(1,$password,PDO::PARAM_STR);
		$stmt->bindParam(2,$_SESSION['uid'],PDO::PARAM_INT);
		$stmt->execute();
		return true;
	}

	private function UpdateAbout($about){
		$stmt = $this->_link->prepare('UPDATE users SET about = ? WHERE uid = ?');
		$stmt->bindParam(1,$about,PDO::PARAM_STR);
		$stmt->bindParam(2,$_SESSION['uid'],PDO::PARAM_INT);
		$stmt->execute();
		return true;	
	}

	private function ValidUpdateProfile($fname,$lname,$password,$rpassword,$email,$about,$currentPassword){
		if(!$this->UpdateRequiredField($fname,$lname,$email)){
			return 'Error: Something go wrong try again or send us feedback';
			die;
		}
		if(strlen($password) > 0){
			if(!$this->UpdatePassword($password,$rpassword)){
				return 'Error: Something go wrong try again or send us feedback';
				die;
			}
		}
		$about = nl2br($about);
		if (!$this->UpdateAbout($about)){
			return 'Error: Something go wrong try again or send us feedback';
			die;
		}
		return true;
	}

	public function UpdateProfile($fname,$lname,$password,$rpassword,$email,$about,$currentPassword){
		if (empty($fname) || empty($lname) || empty($email) || empty($currentPassword)){
			return 'Error: you must fill the required field';
			die;
		}
		if (!$this->isPasswordUser($currentPassword)){
			return 'Error: wrong password';
			die;
		}
		if (!$this->isBetween(html_entity_decode($fname),1,21) || !$this->isBetween(html_entity_decode($lname),1,21)){
			return 'Error: first name and last name length must be less than 21 charcters';
			die;
		}
		if (!ctype_alnum(html_entity_decode($fname)) || !ctype_alnum(html_entity_decode($lname))){
			return 'Error: first name and last name must contains only letters and numbers';
			die;
		}
		if (strlen($password) > 0){
			if (!$this->isBetween(html_entity_decode($password),4,16)){
				return 'Error: password must be over 4 characters';
				die;
			}
			if (!$this->isPasswordsMatch($password,$rpassword)){
				return 'Error: password does not match';
				die;
			}
		}
		if (!$this->isEmail($email)){
			return 'Error: wrong email format';
			die;
		}
		/*no Error*/
		return $this->ValidUpdateProfile($fname,$lname,$password,$rpassword,$email,$about,$currentPassword);
	}
}