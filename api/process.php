<?php 
// Start session to access user data
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in for protected actions
$protected_actions = ['addpost', 'addcomment', 'getlikes', 'chatusers', 'chatsearch', 'chatmessages', 'thodzsearch'];
$action = !isset($_GET['action']) ? '' : htmlentities(trim($_GET['action']));

if (in_array($action, $protected_actions)) {
    if (!(isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid']))) {
        echo "Session expired. Please login again.";
        exit;
    }
}
if ($action == 'addpost'){
	if(isset($_POST['pid'])){
		$postid = intval($_POST['pid']);
		require_once(__DIR__ . '/../models/post.class.php');
		$pid = new Post();
		$POST = $pid->get_singlePost($postid);
		if (is_array($POST)){
			if ($POST['owner'] == $_SESSION['uid']){
				require_once(__DIR__ . '/../models/user.class.php');
				require_once(__DIR__ . '/../controler/image.controler.php');
				$user = new User();
				$images = new Image();
				
				$postUser = $user->getData($POST['owner']);
				$postUserImg = $images->get_thumb_profile($postUser['profileimg']);
				$postImage = '';
				if (!empty($POST['postimg'])) {
					$postImage = "../uploads/" . $POST['owner'] . "/" . $POST['postimg'];
				}
				$isOwner = true;
				$currentUserId = $_SESSION['uid'];
				$currentUserData = $user->getData($currentUserId);
				$currentUserImg = $images->get_thumb_profile($currentUserData['profileimg']);
				
				include(__DIR__ . '/../templates/post_card.php');
			}
		}
	}
}
elseif ($action == 'addcomment'){
	if(isset($_POST['pid']) && isset($_POST['cid'])){
		$postid = intval($_POST['pid']);
		$commentid = intval($_POST['cid']);
		require_once(__DIR__ . '/../models/post.class.php');
		$post = new Post();
		$COMMENT = $post->get_singleComment($postid, $commentid);
		if (is_array($COMMENT)){
			require_once(__DIR__ . '/../models/user.class.php');
			require_once(__DIR__ . '/../controler/image.controler.php');
			$user = new User();
			$images = new Image();
			
			$comment = $COMMENT;
			$commentUser = $user->getData($COMMENT['owner']);
			$commentUserImg = $images->get_thumb_profile($commentUser['profileimg']);
			$commentImage = '';
			if (!empty($COMMENT['postimg'])) {
				$commentImage = "../uploads/" . $COMMENT['owner'] . "/" . $COMMENT['postimg'];
			}
			$currentUserId = $_SESSION['uid'];
			
			include(__DIR__ . '/../templates/comment_card.php');
		}   
	}
}
elseif ($action == 'getlikes'){
	if(isset($_POST['pid'])){
		$postid = intval($_POST['pid']);
		require_once(__DIR__ . '/../controler/image.controler.php');
		require_once(__DIR__ . '/../models/user.class.php');
		$user = new User();
		$images = new Image();
		$likes = $user->getFollowers($postid,'post');
		if (is_array($likes) && count($likes) > 0){
			foreach($likes as $like){
				$user_liked = $user->getData($like['uid']);
				if (!$user_liked) continue;
				
				$profile_image = $images->get_thumb_profile($user_liked['profileimg']);
				$full_name = htmlspecialchars(html_entity_decode($user_liked['fname']) . " " . html_entity_decode($user_liked['lname']));
				
				echo '<div class="likes-item">';
				echo '<a href="./profile.php?uid=' . $user_liked['uid'] . '" class="likes-item-user">';
				echo '<img src="' . htmlspecialchars($profile_image) . '" alt="Profile" class="likes-item-avatar">';
				echo '<span class="likes-item-name">' . $full_name . '</span>';
				echo '</a>';
				echo '<div class="likes-item-icon"><i class="fas fa-thumbs-up"></i></div>';
				echo '</div>';
			}
		} else {
			echo '<div class="likes-empty">No one has liked this post yet</div>';
		}
	}
}
elseif ($action == 'chatusers'){
	require_once(__DIR__ . '/../controler/image.controler.php');
	require_once(__DIR__ . '/../models/user.class.php');
	require_once(__DIR__ . '/../models/chat.class.php');
	$chat = new Chat();
	$user = new User();
	$images = new Image();
	$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
	$users = $chat->getAllUsers($uid);
	if (is_array($users)){
		foreach($users as $user_chat){
			if ($user_chat['uid'] != $uid){
				$lastMessage = $chat->getLastMessage($user_chat['uid'],$uid);
				if (isset($user_chat['profileimg']) && !empty($user_chat['profileimg'])){
	          		$user_chat_profile_img = "../uploads/" . $user_chat['uid'] . "/" . $user_chat['profileimg'];
		      	}else{
		          	$user_chat_profile_img = "../images/user_female.jpg";
		          	if ($user_chat['gender'] == 'male'){
		              $user_chat_profile_img = "../images/user_male.jpg";
		          	}
		      	}
		      	$user_chat_profile_img = $images->get_thumb_profile($user_chat_profile_img);
      	  		$user_chat_profile_img = "." . substr($user_chat_profile_img,strpos($user_chat_profile_img,"/"),strlen($user_chat_profile_img));
				($user_chat['status'] == "offline") ? $offline = "offline" : $offline = "";
				echo "<a href='chat.php?uid=".$user_chat['uid']."'>
	          <div class='content'>
	            <img src='".$user_chat_profile_img."' alt=''>
	            <div class='details'>
	              <span>".html_entity_decode($user_chat['fname']) . ' ' .html_entity_decode($user_chat['lname'])."</span>
	              <p>".$lastMessage."</p>
	            </div>
	          </div>
	          <div class='status-dot ".$offline."'><i class='fas fa-circle'></i></div>
	        </a>";
				//get last message send
			}
		}
	}else{
			echo "No user to chat for now";
		}
}
elseif ($action == 'chatsearch'){
	if (isset($_POST['searchfor'])){
		require_once(__DIR__ . '/../controler/image.controler.php');
		require_once(__DIR__ . '/../models/user.class.php');
		require_once(__DIR__ . '/../models/chat.class.php');
		$chat = new Chat();
		$user = new User();
		$images = new Image();
		$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
		$value = !isset($_POST['searchfor']) ? '' : strtolower(htmlentities(trim($_POST['searchfor'])));
		$users = $chat->getSearchUsers($uid,$value);
		if (is_array($users)){
			foreach($users as $user_chat){
				//get info
				if (isset($user_chat['profileimg']) && !empty($user_chat['profileimg'])){
	          		$user_chat_profile_img = "../uploads/" . $user_chat['uid'] . "/" . $user_chat['profileimg'];
		      	}else{
		          	$user_chat_profile_img = "../images/user_female.jpg";
		          	if ($user_chat['gender'] == 'male'){
		              $user_chat_profile_img = "../images/user_male.jpg";
		          	}
		      }
	      	$user_chat_profile_img = $images->get_thumb_profile($user_chat_profile_img);
      	  	$user_chat_profile_img = "." . substr($user_chat_profile_img,strpos($user_chat_profile_img,"/"),strlen($user_chat_profile_img));
				$lastMessage = $chat->getLastMessage($user_chat['uid'],$uid);
				($user_chat['status'] == "offline") ? $offline = "offline" : $offline = "";
				echo "<a href='chat.php?uid=".$user_chat['uid']."'>
	          <div class='content'>
	            <img src='".$user_chat_profile_img."' alt=''>
	            <div class='details'>
	              <span>".html_entity_decode($user_chat['fname']) . ' ' .html_entity_decode($user_chat['lname'])."</span>
	              <p>".$lastMessage."</p>
	            </div>
	          </div>
	          <div class='status-dot ".$offline."'><i class='fas fa-circle'></i></div>
	        </a>";
				//get last message send
			}
		}else{
			echo "No user found related to your search term";
		}
	}else{
		echo "No user found related to your search term";
	}
}
elseif ($action == 'chatmessages'){
	if (isset($_POST['chatwith'])){
		require_once(__DIR__ . '/../controler/image.controler.php');
		require_once(__DIR__ . '/../models/user.class.php');
		require_once(__DIR__ . '/../models/chat.class.php');
		$chat = new Chat();
		$user = new User();
		$images = new Image();
		$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
		$chat_with = !isset($_POST['chatwith']) ? '' : htmlentities(trim($_POST['chatwith']));
		$chat_user = $user->getData($chat_with);
		if (is_array($chat_user)){
			$messages = $chat->getMessages($uid,$chat_with);
			if (is_array($messages)){
				foreach($messages as $message){
					// Sanitize message output to prevent XSS
					$safeMsg = htmlspecialchars($message['msg'], ENT_QUOTES, 'UTF-8');
					$safeProfileImg = htmlspecialchars($chat_user['profileimg'], ENT_QUOTES, 'UTF-8');
					if ($message['outgoing_msg_id'] == $uid){ //that mean he is the sender
						echo "<div class='chat outgoing'>
						          <div class='details'>
						              <p>".$safeMsg."</p>
						          </div>
						       </div>";
					}else{//he is the receiver
						echo "<div class='chat incoming'>
						          <img src='".$safeProfileImg."' alt=''>
						          <div class='details'>
						              <p>".$safeMsg."</p>
						          </div>
						       </div>";
					}
				}
			}else{
				echo "<span style='margin-left: 25%;'>No message available</span>";
			}
		}
	}
}
elseif ($action == 'thodzsearch'){
    if (isset($_POST['searchfor'])){
        require_once(__DIR__ . '/../controler/image.controler.php');
        require_once(__DIR__ . '/../models/user.class.php');
        $user = new User();
        $images = new Image();
        $uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
        $value = !isset($_POST['searchfor']) ? '' : strtolower(htmlentities(trim($_POST['searchfor'])));
        $users = $user->getSearchUsers($uid, $value);
        if (is_array($users) && count($users) > 0){
            foreach($users as $usersearch){
                if (isset($usersearch['profileimg']) && !empty($usersearch['profileimg'])){
                    $usersearch_profile_img = "./uploads/" . $usersearch['uid'] . "/" . $usersearch['profileimg'];
                } else {
                    $usersearch_profile_img = "./images/user_female.jpg";
                    if ($usersearch['gender'] == 'male'){
                        $usersearch_profile_img = "./images/user_male.jpg";
                    }
                }
                $usersearch_profile_img = $images->get_thumb_profile($usersearch_profile_img);
                $fullName = htmlspecialchars(html_entity_decode($usersearch['fname']) . ' ' . html_entity_decode($usersearch['lname']));
                echo "<a href='./profile.php?uid=".$usersearch['uid']."' class='search-result-item'>
                    <img src='".htmlspecialchars($usersearch_profile_img)."' alt='Profile'>
                    <span>".$fullName."</span>
                </a>";
            }
        } else {
            echo "<div class='search-no-results'>No users found</div>";
        }
    } else {
        echo "<div class='search-no-results'>Start typing to search</div>";
    }
}