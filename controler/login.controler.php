<?php 

require_once('../models/login.class.php');
require_once('../models/security.class.php');

class Logincontr extends Login{

	const salt = 'THODZ';
	
	/**
	 * Hash password - now uses Security class for bcrypt
	 * Kept for backward compatibility during migration
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
	// it not secure against CSRF attack
	public function isValidLogin($email, $password){
		if (empty($email) || empty($password)){
			return 'Error: there is empty feild required';
		}
		elseif(!$this->isEmail($email)){
			return 'Error: your email is not valid';
			exit();
		}
		elseif(!$this->isBetween(html_entity_decode($password), 4, 16)){
			return 'Error: password length must be between 4 and 16 charaters';
			exit();
		}
		elseif(!$this->isUser($email)){
			return 'Error: Invalid email or password'; // Don't reveal if user exists
			exit();
		}
		else{
			$result = $this->loginSecure($email, $password);
			if ($result === false){
				return 'Error: Invalid email or password';
			}else{
				return $result;
			}
		}
	}
	
	/**
	 * Secure login with bcrypt password verification
	 */
	private function loginSecure($email, $password) {
		return $this->LoginWithSecurePassword($email, $password);
	}
}