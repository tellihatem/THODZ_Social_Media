<?php 

require_once('../models/signup.class.php');
require_once('../models/codeverfication.class.php');
require_once('../models/security.class.php');
require_once('image.controler.php');

class Signupcontr extends Signup {

	public $_new_img_name = null;
	
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

	private function isBetween($value, $min, $max){
		if (strlen($value) < $min || strlen($value) > $max)
			return false;
		else
			return true;
	}
	/*private function isImage($img){
		$this->_new_img_name = new Image();
		return $this->_new_img_name->isImage($img);
	}*/
	/*private function defaultImage($gender){
		$gender_img = 'user.png';
		if (empty(htmlentities(trim($gender)))){
			return false;
		}
		if ($gender == 'male'){
			$gender_img = 'profile.png';
		}
		$this->_new_img_name = $gender_img;
		return true;
	}*/
	// it not secure against CSRF attack
	public function isValidSignup ($fname,$lname,$email,$password,$gender) {
		if (empty($fname) || empty($lname) || empty($email) || empty($password)){
			return 'Error: All fields are required';
		}
		elseif (!$this->isEmail($email)){
			return 'Error: Invalid email format';
		}
		elseif (!$this->isBetween(html_entity_decode($fname),1,21) || !$this->isBetween(html_entity_decode($lname),1,21)){
			return 'Error: first name and last name length must be less than 21 charcters';
		}
		elseif (!$this->isBetween(html_entity_decode($password),4,16)){
			return 'Error: password length must be between 4 and 16 charaters';
		}
		elseif (!ctype_alnum(html_entity_decode($fname)) || !ctype_alnum(html_entity_decode($lname))){
			return 'Error: first name and last name must contains only letters and numbers';
		}
		elseif (empty(htmlentities(trim($gender)))){
			return 'Error: invalid gender input';
		}
		elseif ($this->isUser($email)) {
			return 'Error: This email address is already registered';
		}
		else {
				$password = $this->hashPassword($password);
				$token = (new CodeVerify())->SetCode();
				//$this->SignUp($fname,$lname,$email,$password,$this->_new_img_name->_new_img_name,$token);
				//$this->SignUp($fname,$lname,$email,$password,$this->_new_img_name,$token,$gender);
				$this->SignUp($fname,$lname,$email,$password,$gender,$token);
		}
	}
}