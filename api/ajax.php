<?php 
header("Content-type: application/json; charset=utf-8");
$action = !isset($_GET['action']) ? '' : htmlentities(trim($_GET['action']));
$json = [];
if(empty($action)){
	$json = [
		'error' => true,
		'message' => 'You can not access this file directly from your browser'
	];
}
else{
	$result = null;
	if($action == 'signup'){
		require_once('../controler/signup.controler.php');
		$signup = new Signupcontr();
		$fname = !isset($_POST['fname']) ? '' : htmlentities(trim($_POST['fname']));
		$lname = !isset($_POST['lname']) ? '' : htmlentities(trim($_POST['lname']));
		$email = !isset($_POST['email']) ? '' : htmlentities(trim($_POST['email']));
		$password = !isset($_POST['password']) ? '' : htmlentities(trim($_POST['password']));
		//$img = !isset($_FILES['image']) ? '' : $_FILES['image'];
		$gender = !isset($_POST['gender']) ? '' : htmlentities(trim($_POST['gender']));
		//$result = $signup->isValidSignup($fname, $lname, $email, $password, $img);
		$result = $signup->isValidSignup($fname, $lname, $email, $password, $gender);
		if($result !== NULL){
			$json = [
				'error' => true,
				'message' => $result
			];
		}else{
			$json = [
				'error' => false,
				'message' => 'Your registration has been successfully completed. You have just been sent an email containing membership activation link.'
			];
		}
	}
	elseif($action == 'login'){
		require_once('../controler/login.controler.php');
		$login = new Logincontr();
		$email = !isset($_POST['email']) ? '' : htmlentities(trim($_POST['email']));
		$password = !isset($_POST['password']) ? '' : htmlentities(trim($_POST['password']));
		
		$result = $login->isValidLogin($email , $password);
		
		if($result !== true){
			$json = [
				'error' => true,
				'message' => $result
			];
		}
	}
	elseif($action == 'forget'){
		require_once('../models/forget.class.php');
		$forget = new Forget();
		$email = !isset($_POST['email_forgot']) ? '' : htmlentities(trim($_POST['email_forgot']));
		$result = $forget->forget($email);
		if ($result === true){
			$json = [
				'error' => false,
				'message' => 'Operation Success. Check your email please'
			];
		}else{
			$json = [
				'error' => true,
				'message' => $result
			];
		}
	}
	elseif ($action == 'newpassword') {
		require_once('../models/forget.class.php');
		$forget = new Forget();
		$email = !isset($_POST['email_forgot']) ? '' : htmlentities(trim($_POST['email_forgot']));
		$token = !isset($_POST['token_forgot']) ? '' : htmlentities(trim($_POST['token_forgot']));
		$password = !isset($_POST['password_forgot']) ? '' : htmlentities(trim($_POST['password_forgot']));
		$result = $forget->newPassword($email,$token,$password);
		if ($result === true){
			$json = [
				'error' => false,
				'message' => 'Operation Success.'
			];
		}else{
			$json = [
				'error' => true,
				'message' => $result
			];
		}
	}
	elseif($action == 'post'){
		require_once('../models/post.class.php');
		$post = new Post();
		$text = !isset($_POST['textarea']) ? '' : htmlentities(trim($_POST['textarea']));
		$img = !isset($_FILES['image']) ? '' : $_FILES['image'];
		$result = $post->createPost($text,$img,"post",0);
		if ($result !== false){
			$json = [
				'success' => true,
				'pid' => $result
			];
		}
	}
	elseif($action == 'profileimg'){
		require_once('../models/user.class.php');
		$user = new User();
		$img = !isset($_FILES['upload_image']) ? '' : $_FILES['upload_image'];
		$result = $user->UpdateProfileImg($img);
		if ($result !== false){
			list($pid,$img) = explode(',',$result);
			$json = [
				'updateimg' => true,
				'img' => $img,
				'pid' => $pid
			];
		}
	}
	elseif($action == 'coverimg'){
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		if (!isset($_SESSION['IS_LOGGED']) || !$_SESSION['IS_LOGGED'] || !isset($_SESSION['uid'])) {
			$json = ['error' => true, 'message' => 'Please login first'];
		} else {
			require_once('../models/user.class.php');
			$user = new User();
			$img = !isset($_FILES['cover_image']) ? '' : $_FILES['cover_image'];
			$result = $user->UpdateCoverImg($img);
			if ($result !== false){
				$json = [
					'success' => true,
					'img' => $result
				];
			} else {
				$json = ['error' => true, 'message' => 'Failed to upload cover image'];
			}
		}
	}
	elseif($action == 'deletepost'){
		require_once('../models/post.class.php');
		$post = new Post();
		$postid = !isset($_POST['postid']) ? 0 : intval($_POST['postid']);
		$result = $post->delete_single_post($postid);
		if ($result === true){
			$json = [
				'success' => true,
				'pid' => $postid,
			];
		}elseif($result !== false){
			list($comment_counter,$postid) = explode(',',$result);
			$json = [
				'success' => true,
				'pid' => $postid,
				'comment_counter' => $comment_counter
			];
		}
	}
	elseif($action == 'like'){
		// Start session to get current user - SECURITY FIX
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		
		// Must be logged in to like/follow
		if (!isset($_SESSION['IS_LOGGED']) || !$_SESSION['IS_LOGGED'] || !isset($_SESSION['uid'])) {
			$json = ['error' => true, 'message' => 'Please login first'];
		} else {
			require_once('../models/post.class.php');
			require_once('../models/user.class.php');
			$post = new Post();
			$type = !isset($_POST['type']) ? '' : htmlentities(trim($_POST['type']));
			$pid = !isset($_POST['pid']) ? 0 : intval($_POST['pid']);
			
			// SECURITY: Always use session user ID, never trust client-provided uid
			$uid = intval($_SESSION['uid']);
			
			// Prevent self-follow
			if ($type == 'user' && $pid == $uid) {
				$json = ['error' => true, 'message' => 'Cannot follow yourself'];
			} else {
				$result = $post->like_post($type, $pid, $uid);
				
				if ($result !== false){
					if ($type == 'user') {
						// Get updated follower count for the target user
						$user = new User();
						$followers = $user->getFollowers($pid, 'user');
						$follower_count = is_array($followers) ? count($followers) : 0;
						$json = [
							'success' => true,
							'follower_count' => $follower_count
						];
					} else {
						$json = [
							'success' => true,
							'post_count' => true,
							'counter' => $result
						];
					}
				}
			}
		}
	}
	elseif($action == 'addcomment'){
		require_once('../models/post.class.php');
		$post = new Post();
		$text = !isset($_POST['commentText']) ? '' : htmlentities(trim($_POST['commentText']));
		$postid = !isset($_POST['postid']) ? '' : htmlentities(trim($_POST['postid']));
		$img = !isset($_FILES['commentImage']) ? '' : $_FILES['commentImage'];
		$result = $post->createPost($text,$img,'comment',$postid);
		if ($result !== false){
			list($commentid,$comment_counter) = explode(',',$result);
			$json = [
				'success' => true,
				'commentid' => $commentid,
				'comment_counter' => $comment_counter,
				'postid' => $postid
			];
		}
	}
	elseif($action == 'settings'){
		$fname = !isset($_POST['fname']) ? '' : htmlentities(trim($_POST['fname']));
		$lname = !isset($_POST['lname']) ? '' : htmlentities(trim($_POST['lname']));
		$password = !isset($_POST['password']) ? '' : htmlentities(trim($_POST['password']));
		$rpassword = !isset($_POST['rpassword']) ? '' : htmlentities(trim($_POST['rpassword']));
		$email = !isset($_POST['email']) ? '' : htmlentities(trim($_POST['email']));
		$about = !isset($_POST['about']) ? '' : htmlentities(trim($_POST['about']));
		$currentPassword = !isset($_POST['currentpassword']) ? '' : htmlentities(trim($_POST['currentpassword']));
		require_once('../models/settings.class.php');
		$settings = new Settings();
		$result = $settings->UpdateProfile($fname,$lname,$password,$rpassword,$email,$about,$currentPassword);
		if ($result === true){
			$full_name = html_entity_decode($fname) . " " . html_entity_decode($lname);
			$email = html_entity_decode($email);
			//$about = html_entity_decode($about);
			$json = [
				'error' => false,
				'message' => 'Profile has been Updated successfully',
				'fullname' => $full_name,
				'email' => $email,
				'about' => $about
			];
		}else{
			$json = [
				'error' => true,
				'message' => $result
			];
		}
	}
	elseif ($action == 'editpost') {
		require_once('../models/post.class.php');
		$post = new Post();
		$text = !isset($_POST['textarea']) ? '' : htmlentities(trim($_POST['textarea']));
		$postid = !isset($_POST['postid_edit']) ? '' : intval($_POST['postid_edit']);
		$img = !isset($_FILES['image_buffer']) ? '' : $_FILES['image_buffer'];
		$removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';
		$result = $post->UpdatePost($text, $img, $postid, $removeImage);

		if ($result === true){
			$json = [
				'success' => true
			];
		} else {
			$json = [
				'error' => true,
				'message' => $result
			];
		}
	}
	elseif ($action == 'insertmessage'){
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		if (!isset($_SESSION['IS_LOGGED']) || !$_SESSION['IS_LOGGED'] || !isset($_SESSION['uid'])) {
			$json = ['error' => true, 'message' => 'Please login first'];
		} else {
			require_once('../models/chat.class.php');
			$chat = new Chat();
			
			// Get and validate inputs
			$message = isset($_POST['message']) ? trim($_POST['message']) : '';
			$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
			
			// Validate user_id
			if ($user_id <= 0) {
				$json = ['error' => true, 'message' => 'Invalid recipient'];
			} else {
				// Get files if present
				$imageFile = isset($_FILES['image']) && !empty($_FILES['image']['tmp_name']) ? $_FILES['image'] : null;
				$audioFile = isset($_FILES['audio']) && !empty($_FILES['audio']['tmp_name']) ? $_FILES['audio'] : null;
				
				// Use secure message sending method
				$result = $chat->sendSecureMessage($user_id, $message, $imageFile, $audioFile);
				
				if ($result['success']) {
					$json = [
						'success' => true,
						'message_id' => $result['message_id']
					];
				} else {
					$json = ['error' => true, 'message' => $result['error'] ?? 'Failed to send message'];
				}
			}
		}
	}
	elseif ($action == 'getnewmessages'){
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		if (!isset($_SESSION['IS_LOGGED']) || !$_SESSION['IS_LOGGED'] || !isset($_SESSION['uid'])) {
			$json = ['error' => true, 'message' => 'Please login first'];
		} else {
			require_once('../models/chat.class.php');
			$chat = new Chat();
			
			$chatWithId = isset($_POST['chat_with']) ? intval($_POST['chat_with']) : 0;
			$lastMessageId = isset($_POST['last_message_id']) ? intval($_POST['last_message_id']) : 0;
			
			if ($chatWithId > 0) {
				$messages = $chat->getNewMessages($chatWithId, $lastMessageId);
				$json = [
					'success' => true,
					'messages' => $messages
				];
			} else {
				$json = ['error' => true, 'message' => 'Invalid chat'];
			}
		}
	}
	elseif ($action == 'markasread'){
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		if (!isset($_SESSION['IS_LOGGED']) || !$_SESSION['IS_LOGGED'] || !isset($_SESSION['uid'])) {
			$json = ['error' => true, 'message' => 'Please login first'];
		} else {
			require_once('../models/chat.class.php');
			$chat = new Chat();
			
			$chatWithId = isset($_POST['chat_with']) ? intval($_POST['chat_with']) : 0;
			
			if ($chatWithId > 0) {
				$chat->markAsRead($chatWithId);
				$json = ['success' => true];
			} else {
				$json = ['error' => true, 'message' => 'Invalid chat'];
			}
		}
	}
	elseif ($action == 'getunreadcount'){
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		if (!isset($_SESSION['IS_LOGGED']) || !$_SESSION['IS_LOGGED'] || !isset($_SESSION['uid'])) {
			$json = ['error' => true, 'message' => 'Please login first'];
		} else {
			require_once('../models/chat.class.php');
			$chat = new Chat();
			
			$count = $chat->getUnreadCount();
			$json = [
				'success' => true,
				'count' => $count
			];
		}
	}
	elseif ($action == 'getonlinestatus'){
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		if (!isset($_SESSION['IS_LOGGED']) || !$_SESSION['IS_LOGGED'] || !isset($_SESSION['uid'])) {
			$json = ['error' => true, 'message' => 'Please login first'];
		} else {
			require_once('../models/user.class.php');
			$user = new User();
			
			// Update current user's last activity (heartbeat)
			if (isset($_POST['heartbeat'])) {
				$user->updateOnlineStatus($_SESSION['uid'], 'online');
			}
			
			// Get all users' online status
			$statuses = $user->getAllOnlineStatuses();
			$json = [
				'success' => true,
				'users' => $statuses
			];
		}
	}
}
echo(json_encode($json));
exit;