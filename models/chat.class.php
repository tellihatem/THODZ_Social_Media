<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!(isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid']))){
    header("location: ../login.php");
    die();
}
require_once('database.class.php');
require_once(__DIR__ . '/user.class.php');
require_once(__DIR__ . '/security.class.php');

class Chat {
	private $_link = null;
	private $_currentUserId = null;
	
	// Rate limiting: max messages per minute
	private const MAX_MESSAGES_PER_MINUTE = 30;
	// Max message length
	private const MAX_MESSAGE_LENGTH = 5000;
	// Allowed audio types (including application/octet-stream for browser recordings)
	private const ALLOWED_AUDIO_TYPES = ['audio/webm', 'audio/ogg', 'audio/mp3', 'audio/mpeg', 'audio/wav', 'audio/mp4', 'video/webm', 'application/octet-stream'];
	// Max file sizes
	private const MAX_IMAGE_SIZE = 5242880; // 5MB
	private const MAX_AUDIO_SIZE = 10485760; // 10MB
		
	function __construct(){
		$this->_link = (new Database())->connect();
		$this->_currentUserId = isset($_SESSION['uid']) ? intval($_SESSION['uid']) : 0;
	}
	
	/**
	 * Validate that the current user can message the target user
	 */
	private function canMessageUser($targetUserId) {
		if ($this->_currentUserId <= 0 || $targetUserId <= 0) {
			return false;
		}
		// Can't message yourself
		if ($this->_currentUserId === $targetUserId) {
			return false;
		}
		// Check if target user exists
		$user = new User();
		$targetUser = $user->getData($targetUserId);
		return $targetUser !== false;
	}
	
	/**
	 * Check rate limiting for messages
	 */
	private function checkRateLimit() {
		try {
			$stmt = $this->_link->prepare(
				'SELECT COUNT(*) as count FROM messages 
				 WHERE outgoing_msg_id = ? AND date > (NOW() - INTERVAL \'1 MINUTE\')'
			);
			$stmt->bindParam(1, $this->_currentUserId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return ($result['count'] ?? 0) < self::MAX_MESSAGES_PER_MINUTE;
		} catch (PDOException $e) {
			// If rate limit check fails, allow the message (fail open)
			return true;
		}
	}
	
	/**
	 * Sanitize message content - prevent XSS while allowing some formatting
	 */
	private function sanitizeMessage($message) {
		// Trim and limit length
		$message = trim($message);
		if (mb_strlen($message, 'UTF-8') > self::MAX_MESSAGE_LENGTH) {
			$message = mb_substr($message, 0, self::MAX_MESSAGE_LENGTH, 'UTF-8');
		}
		// Convert special characters to HTML entities
		$message = htmlspecialchars($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		return $message;
	}
	
	/**
	 * Validate and process image upload securely
	 */
	public function processImageUpload($file) {
		if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
			return ['success' => false, 'error' => 'No file uploaded'];
		}
		
		// Use Security class for validation
		$validation = Security::validateImageUpload($file, self::MAX_IMAGE_SIZE);
		if (!$validation['valid']) {
			return ['success' => false, 'error' => $validation['error']];
		}
		
		// Create upload directory if needed
		$upload_dir = __DIR__ . '/../uploads/chat/';
		if (!is_dir($upload_dir)) {
			mkdir($upload_dir, 0755, true);
		}
		
		// Generate secure filename
		$extension = 'jpg';
		switch ($validation['type']) {
			case IMAGETYPE_PNG: $extension = 'png'; break;
			case IMAGETYPE_GIF: $extension = 'gif'; break;
		}
		$filename = Security::generateSecureFilename($extension);
		$filepath = $upload_dir . $filename;
		
		// Move uploaded file
		if (!move_uploaded_file($file['tmp_name'], $filepath)) {
			return ['success' => false, 'error' => 'Failed to save file'];
		}
		
		return ['success' => true, 'filename' => $filename];
	}
	
	/**
	 * Validate and process audio upload securely
	 */
	public function processAudioUpload($file) {
		if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
			return ['success' => false, 'error' => 'No file uploaded'];
		}
		
		// Check file size
		if ($file['size'] > self::MAX_AUDIO_SIZE) {
			return ['success' => false, 'error' => 'Audio file too large (max 10MB)'];
		}
		
		// Verify MIME type
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mimeType = $finfo->file($file['tmp_name']);
		if (!in_array($mimeType, self::ALLOWED_AUDIO_TYPES)) {
			return ['success' => false, 'error' => 'Invalid audio format'];
		}
		
		// Create upload directory if needed
		$upload_dir = __DIR__ . '/../uploads/chat/';
		if (!is_dir($upload_dir)) {
			mkdir($upload_dir, 0755, true);
		}
		
		// Generate secure filename
		$filename = 'audio_' . bin2hex(random_bytes(16)) . '.webm';
		$filepath = $upload_dir . $filename;
		
		// Move uploaded file
		if (!move_uploaded_file($file['tmp_name'], $filepath)) {
			return ['success' => false, 'error' => 'Failed to save audio'];
		}
		
		return ['success' => true, 'filename' => $filename];
	}

	public function getAllUsers($id){
		//get the recent users i have chat with
		$user = new User();
		$users;
		$stmt = $this->_link->prepare('SELECT * FROM messages WHERE incoming_msg_id = ? OR outgoing_msg_id = ? ORDER BY mid DESC');
		$stmt->bindParam(1,$id,PDO::PARAM_INT);
		$stmt->bindParam(2,$id,PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() > 0){
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			//we must loop in the result and get all data of those users that is not the current user itself($id=Session)
			foreach($result as $lastuser_chatwith){
				if ($lastuser_chatwith['outgoing_msg_id'] == $id){
					$lastuser = $user->getSingleUser($lastuser_chatwith['incoming_msg_id']);
					if (isset($users) && is_array($users)){
						if (!in_array($lastuser,$users)){
							$users[] = $lastuser;
						}
					}else{
						$users[] = $lastuser;
					}
				}else{
					$lastuser = $user->getSingleUser($lastuser_chatwith['outgoing_msg_id']);
					if (isset($users) && is_array($users)){
						if (!in_array($lastuser,$users)){
							$users[] = $lastuser;
						}
					}else{
						$users[] = $lastuser;
					}
				}
			}
		}
		//get the users i follow
		$following = $user->getFollowing($id,'user');
		if (is_array($following)){
			foreach ($following as $user_i_follow){
				$data_following = $user->getSingleUser($user_i_follow);
				if (isset($users) && is_array($users)){
					if (!in_array($data_following,$users)){
							$users[] = $data_following;
					}
				}else{
					$users[] = $data_following;
				}
			}
		}
		//get the others users
		$others = $user->getAllUsers($id);
		if (is_array($others)){
			if (isset($users) && is_array($users)){
				foreach($others as $other){
					if (!in_array($other,$users)){
						$users[] = $other;
					}
				}
			}else{
				$users = $others;
			}
		}
		//order the list by the priority in the right way
		return $users;
	}

	public function getSearchUsers($id,$value){
		$user = new User();
		$search_users = $user->getSearchUsers($id,$value);
		return $search_users;
	}

	public function insertMessage($message,$user_id){
		$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
		if (is_numeric($user_id) && $uid > 0) {
			if (!empty($message)){
				$stmt = $this->_link->prepare('INSERT INTO messages (incoming_msg_id,outgoing_msg_id,msg) VALUES (?,?,?)');
				$stmt->bindParam(1,$user_id,PDO::PARAM_INT);
				$stmt->bindParam(2,$uid,PDO::PARAM_INT);
				$stmt->bindParam(3,$message,PDO::PARAM_STR);
				$stmt->execute();
				return $this->_link->lastInsertId(Database::isPostgres() ? 'messages_mid_seq' : null);
			}
		}
		return false;
	}

	public function insertMessageExtended($message, $user_id, $image = null, $audio = null){
		$uid = isset($_SESSION['uid']) ? intval($_SESSION['uid']) : 0;
		$user_id = intval($user_id);
		
		if ($user_id <= 0 || $uid <= 0) {
			return ['success' => false, 'error' => 'Invalid user'];
		}
		
		// Security: Validate target user exists and can be messaged
		if (!$this->canMessageUser($user_id)) {
			return ['success' => false, 'error' => 'Cannot message this user'];
		}
		
		// Security: Check rate limiting
		if (!$this->checkRateLimit()) {
			return ['success' => false, 'error' => 'Too many messages. Please wait a moment.'];
		}
		
		// At least one of message, image, or audio must be present
		if (empty($message) && empty($image) && empty($audio)){
			return ['success' => false, 'error' => 'Message cannot be empty'];
		}
		
		// Sanitize message
		$sanitizedMessage = $this->sanitizeMessage($message);
		
		try {
			$stmt = $this->_link->prepare('INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg, image, audio) VALUES (?, ?, ?, ?, ?)');
			$stmt->bindParam(1, $user_id, PDO::PARAM_INT);
			$stmt->bindParam(2, $uid, PDO::PARAM_INT);
			$stmt->bindParam(3, $sanitizedMessage, PDO::PARAM_STR);
			$stmt->bindParam(4, $image, PDO::PARAM_STR);
			$stmt->bindParam(5, $audio, PDO::PARAM_STR);
			$stmt->execute();
			
			return ['success' => true, 'message_id' => $this->_link->lastInsertId(Database::isPostgres() ? 'messages_mid_seq' : null)];
		} catch (PDOException $e) {
			error_log('Chat insert error: ' . $e->getMessage());
			return ['success' => false, 'error' => 'Failed to send message'];
		}
	}
	
	/**
	 * Secure method to send a message with all validations
	 */
	public function sendSecureMessage($targetUserId, $message = '', $imageFile = null, $audioFile = null) {
		$targetUserId = intval($targetUserId);
		
		// Process image if provided
		$imageName = null;
		if ($imageFile && !empty($imageFile['tmp_name'])) {
			$imageResult = $this->processImageUpload($imageFile);
			if (!$imageResult['success']) {
				return $imageResult;
			}
			$imageName = $imageResult['filename'];
		}
		
		// Process audio if provided
		$audioName = null;
		if ($audioFile && !empty($audioFile['tmp_name'])) {
			$audioResult = $this->processAudioUpload($audioFile);
			if (!$audioResult['success']) {
				return $audioResult;
			}
			$audioName = $audioResult['filename'];
		}
		
		// Insert the message
		return $this->insertMessageExtended($message, $targetUserId, $imageName, $audioName);
	}
	
	/**
	 * Get new messages since a specific message ID (for real-time updates)
	 */
	public function getNewMessages($chatWithId, $lastMessageId) {
		$uid = $this->_currentUserId;
		$chatWithId = intval($chatWithId);
		$lastMessageId = intval($lastMessageId);
		
		if ($chatWithId <= 0 || $uid <= 0) {
			return [];
		}
		
		$stmt = $this->_link->prepare(
			'SELECT * FROM messages 
			 WHERE ((incoming_msg_id = ? AND outgoing_msg_id = ?) OR (incoming_msg_id = ? AND outgoing_msg_id = ?))
			 AND mid > ?
			 ORDER BY mid ASC'
		);
		$stmt->bindParam(1, $uid, PDO::PARAM_INT);
		$stmt->bindParam(2, $chatWithId, PDO::PARAM_INT);
		$stmt->bindParam(3, $chatWithId, PDO::PARAM_INT);
		$stmt->bindParam(4, $uid, PDO::PARAM_INT);
		$stmt->bindParam(5, $lastMessageId, PDO::PARAM_INT);
		$stmt->execute();
		
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Mark messages as read
	 */
	public function markAsRead($chatWithId) {
		$uid = $this->_currentUserId;
		$chatWithId = intval($chatWithId);
		
		if ($chatWithId <= 0 || $uid <= 0) {
			return false;
		}
		
		// Mark messages from chatWith to current user as read
		$stmt = $this->_link->prepare(
			'UPDATE messages SET is_read = 1 
			 WHERE incoming_msg_id = ? AND outgoing_msg_id = ? AND is_read = 0'
		);
		$stmt->bindParam(1, $uid, PDO::PARAM_INT);
		$stmt->bindParam(2, $chatWithId, PDO::PARAM_INT);
		return $stmt->execute();
	}
	
	/**
	 * Get unread message count
	 */
	public function getUnreadCount($chatWithId = null) {
		$uid = $this->_currentUserId;
		
		if ($uid <= 0) {
			return 0;
		}
		
		if ($chatWithId) {
			$chatWithId = intval($chatWithId);
			$stmt = $this->_link->prepare(
				'SELECT COUNT(*) as count FROM messages 
				 WHERE incoming_msg_id = ? AND outgoing_msg_id = ? AND is_read = 0'
			);
			$stmt->bindParam(1, $uid, PDO::PARAM_INT);
			$stmt->bindParam(2, $chatWithId, PDO::PARAM_INT);
		} else {
			$stmt = $this->_link->prepare(
				'SELECT COUNT(*) as count FROM messages 
				 WHERE incoming_msg_id = ? AND is_read = 0'
			);
			$stmt->bindParam(1, $uid, PDO::PARAM_INT);
		}
		
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return $result['count'] ?? 0;
	}

	public function getMessages($uid,$chat_with){
		if (is_numeric($chat_with) && is_numeric($uid)) {
			$stmt = $this->_link->prepare('SELECT * FROM messages WHERE (incoming_msg_id = ? AND outgoing_msg_id = ?) OR (incoming_msg_id = ? AND outgoing_msg_id = ?)');
			$stmt->bindParam(1,$uid,PDO::PARAM_INT);
			$stmt->bindParam(2,$chat_with,PDO::PARAM_INT);
			$stmt->bindParam(3,$chat_with,PDO::PARAM_INT);
			$stmt->bindParam(4,$uid,PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0){
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		}
		return false;
	}

	public function getLastMessage($user_id,$uid){
		if (is_numeric($user_id) && is_numeric($uid)) {
			$stmt = $this->_link->prepare('SELECT * FROM messages WHERE (incoming_msg_id = ? AND outgoing_msg_id = ?) OR (incoming_msg_id = ? AND outgoing_msg_id = ?) ORDER BY mid desc limit 1');
			$stmt->bindParam(1,$uid,PDO::PARAM_INT);
			$stmt->bindParam(2,$user_id,PDO::PARAM_INT);
			$stmt->bindParam(3,$user_id,PDO::PARAM_INT);
			$stmt->bindParam(4,$uid,PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0){
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				$you = ($result['outgoing_msg_id'] == $uid) ? "You: " : "";
				$maxLen = $you ? 23 : 28;
				
				// Handle different message types
				if (!empty($result['msg'])) {
					$output = html_entity_decode($result['msg']);
					if (mb_strlen($output, 'UTF-8') > $maxLen) {
						$output = $you . mb_substr($output, 0, $maxLen, 'UTF-8') . "...";
					} else {
						$output = $you . $output;
					}
				} elseif (!empty($result['image'])) {
					$output = $you . "📷 Photo";
				} elseif (!empty($result['audio'])) {
					$output = $you . "🎤 Voice message";
				} else {
					$output = "No message";
				}
				
				return $output;
			}
		}
		return "";
	}
}