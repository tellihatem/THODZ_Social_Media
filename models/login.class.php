<?php
require_once('database.class.php');
require_once(__DIR__ . '/security.class.php');

class Login{
	
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
		if ($stmt->rowCount() > 0){
			return true;
		}
		$stmt = null;
		return false;
	}
	/**
	 * @deprecated Use LoginWithSecurePassword instead
	 */
	protected function Login ($email , $password) {
		return $this->LoginWithSecurePassword($email, $password);
	}
	
	/**
	 * Secure login with bcrypt password verification
	 * Supports migration from legacy MD5 hashes
	 */
	protected function LoginWithSecurePassword($email, $plainPassword) {
		$stmt = $this->_link->prepare('SELECT * FROM users WHERE email = ? LIMIT 1;');
		$stmt->bindParam(1, $email, PDO::PARAM_STR);
		if (!$stmt->execute()) {
			return false;
		}
		
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$user) {
			return false;
		}
		
		// Verify password using Security class (supports legacy MD5 and bcrypt)
		if (!Security::verifyPassword($plainPassword, $user['password'])) {
			return false;
		}
		
		// Check if email is confirmed
		if (!$user['isemailconfirmed']) {
			return "Error: Please verify your email. Check your inbox for the confirmation link.";
		}
		
		// Rehash password if using legacy MD5
		if (Security::needsRehash($user['password'])) {
			$newHash = Security::hashPassword($plainPassword);
			$updateStmt = $this->_link->prepare('UPDATE users SET password = ? WHERE uid = ?');
			$updateStmt->bindParam(1, $newHash, PDO::PARAM_STR);
			$updateStmt->bindParam(2, $user['uid'], PDO::PARAM_INT);
			$updateStmt->execute();
		}
		
		// Start secure session
		Security::secureSession();
		$_SESSION['IS_LOGGED'] = true;
		$_SESSION['uid'] = $user['uid'];
		
		// Update user status to online
		$online = 'online';
		$stmt = $this->_link->prepare('UPDATE users SET status = ? WHERE uid = ?');
		$stmt->bindParam(1, $online, PDO::PARAM_STR);
		$stmt->bindParam(2, $user['uid'], PDO::PARAM_INT);
		$stmt->execute();
		
		return true;
	}

	public function checkLogin($id){
		
	}
}