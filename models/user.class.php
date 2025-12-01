<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!(isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid']))){
    header("location: ../login.php");
    die();
}
require_once('database.class.php');

class User {
	private $_link = null;
	
	function __construct(){
		$this->_link = (new Database())->connect();
	}
	public function getData($id){
		$id = addslashes($id);
		$stmt = $this->_link->prepare('SELECT * FROM users WHERE uid = ?;');
		$stmt->bindParam(1,$id,PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() > 0){
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (isset($result[0]['profileimg']) && !empty($result[0]['profileimg'])){
				$image = "./uploads/" . $id . "/" . $result[0]['profileimg'];
			}else{
				$image = "./images/user_female.jpg";
				if ($result[0]["gender"] == "male")
					$image = "./images/user_male.jpg";
			}
			$result[0]['profileimg'] = $image;
			return $result[0];
		}
		return false;
	}
	
	public function UpdateProfileImg ($img) { 
		$id = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
		if(isset($img['name']) && $img['name'] != "")
		{
			if($img['type'] == "image/jpeg")
			{
				$allowed_size = (1024 * 1024) * 7;
				if($img['size'] < $allowed_size)
				{
					//everything is fine
					$folder = "../uploads/" . $id . "/";

					//create folder
					if(!file_exists($folder))
					{

						mkdir($folder,0777,true);
					}
					require_once('../controler/image.controler.php');
					$image = new Image();

					$output_filename = uniqid('THODZ_', true);
					$output_filename = $output_filename . ".jpg";
					$filename = $folder . $output_filename;
					move_uploaded_file($img['tmp_name'], $filename);

					$image->resize_image($filename,$filename,1500,1500);

					if(file_exists($filename))
					{
						$stmt = $this->_link->prepare('UPDATE users SET profileimg = :profileimg WHERE uid = :uid ;');
						$stmt->bindParam(':profileimg',$output_filename,PDO::PARAM_STR);
						$stmt->bindParam(':uid',$id,PDO::PARAM_INT);
						$stmt->execute();
						//create a post
						require_once('post.class.php');
						$post = new Post();
						$pid = $post->createPost('',$output_filename,'profile',0);
						$output_filename = "../uploads/".$id."/".$output_filename;
						$output_filename = $image->get_thumb_profile($output_filename);
						$output_filename = "." . substr($output_filename,strpos($output_filename,"/"),strlen($output_filename));
						return $pid .  "," . $output_filename;
						 
					}


				}else
				{
					// Only images of size 3Mb or lower are allowed!
					return false;
				}

			}else
			{
				// Only images of Jpeg type are allowed!
				return false;
			}

		}else
		{
			// please add a valid image!
			return false;
		}
	}

	public function UpdateCoverImg($file){
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		if (!isset($_SESSION['uid'])) {
			return false;
		}
		$id = $_SESSION['uid'];
		
		if (!empty($file) && isset($file['name']) && !empty($file['name'])){
			$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
			$file_type = $file['type'];
			
			if (in_array($file_type, $allowed_types)){
				$max_size = 5 * 1024 * 1024; // 5MB for cover images
				if ($file['size'] <= $max_size){
					// Create upload directory if not exists
					$upload_dir = "../uploads/" . $id . "/";
					if (!is_dir($upload_dir)){
						mkdir($upload_dir, 0755, true);
					}
					
					// Generate unique filename
					$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
					$output_filename = "cover_" . uniqid() . "." . $extension;
					$output_path = $upload_dir . $output_filename;
					
					if (move_uploaded_file($file['tmp_name'], $output_path)){
						// Update database
						$stmt = $this->_link->prepare('UPDATE users SET coverimg = ? WHERE uid = ?');
						$stmt->bindParam(1, $output_filename, PDO::PARAM_STR);
						$stmt->bindParam(2, $id, PDO::PARAM_INT);
						$stmt->execute();
						
						return $output_filename;
					}
				}
			}
		}
		return false;
	}

	public function getFollowing($id,$type){
	
		$allowed[] = 'post';
		$allowed[] = 'user';
		$allowed[] = 'comment';

		if (in_array($type,$allowed)){
			if(is_numeric($id)){
				$value = '"uid":'.$id;
				$passThis = "%" . $value . "%";
				$stmt = $this->_link->prepare("SELECT * FROM likes WHERE type = ? AND likes LIKE ?");
				$stmt->bindParam(1,$type,PDO::PARAM_STR);
				$stmt->bindParam(2,$passThis,PDO::PARAM_STR);
				$stmt->execute();
				if($stmt->rowCount() > 0){
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$following_uid = array_column($result, "contentid");
					return $following_uid;
				}
			}
		}

		return false;

	}

	public function getFollowingNumber($id,$type){
		$following = (new User())->getFollowing($id,$type);
		$following_number = 0;
		if (is_array($following)){
			$following_number = sizeof($following);
		}
			/*$following_number = sizeof($following);
			if (in_array($id,$following)){
				$following_number - 1;
			}
		}*/
		return $following_number;
	}

	// public function getFollowersRow($id,$type){
	// 	$allowed[] = 'post';
	// 	$allowed[] = 'user';
	// 	$allowed[] = 'comment';

	// 	if (in_array($type,$allowed) && is_numeric($id)){
	// 		$stmt = $this->_link->prepare("SELECT * FROM likes WHERE type = ? && contentid = ?");
	// 		$stmt->bindParam(1,$type,PDO::PARAM_STR);
	// 		$stmt->bindParam(2,$id,PDO::PARAM_INT);
	// 		$stmt->execute();
	// 		if ($stmt->rowCount() > 0){
	// 			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	// 			return $result[0]['lid'];
	// 			/*$followers = json_decode($result[0]['likes'],true);
	// 			return $followers;*/
	// 		}
	// 	}
	// 	return false;
	// }

	public function getFollowers($id,$type){
		$allowed[] = 'post';
		$allowed[] = 'user';
		$allowed[] = 'comment';

		if (in_array($type,$allowed) && is_numeric($id)){
			$stmt = $this->_link->prepare("SELECT * FROM likes WHERE type = ? AND contentid = ? LIMIT 1");
			$stmt->bindParam(1,$type,PDO::PARAM_STR);
			$stmt->bindParam(2,$id,PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0){
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$followers = json_decode($result[0]['likes'],true);
				return $followers;
			}
		}
		return false;
	}

	public function getRandomUser($id){
		if (is_numeric($id)){
			$output;
			//get the users that they followed by users i'm following
			$result = $this->getFollowing($id,'user');
			if (is_array($result)){
				foreach ($result as $following){
					if ($following != $id){
						$get_relation = $this->getFollowing($following,'user');
						if (is_array($get_relation)){
							foreach ($get_relation as $relation){
								if (($relation != $following) && ($relation != $id)){
									if (!in_array($relation,$result)){
										$output[] = $this->getSingleUser($relation);
									}
								}
							}
						}
					}
				}
			}
			if (empty($output)){
				$limit = 10;
			}else{
				$limit = 10 - sizeof($output);
			}
			if ($limit > 0){
				$stmt = $this->_link->prepare("SELECT * FROM users WHERE uid != ? ORDER BY RANDOM() LIMIT ?");
				$stmt->bindParam(1,$id,PDO::PARAM_INT);
				$stmt->bindParam(2,$limit,PDO::PARAM_INT);
				$stmt->execute();
				if ($stmt->rowCount() > 0){
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$following = $this->getFollowing($id,'user');
					if (is_array($following) || (isset($output) && is_array($output))){
						foreach ($result as $single_user){
							if (is_array($following) && !in_array($single_user['uid'],$following)){
								if (isset($output) && is_array($output)){
									if (!in_array($single_user,$output)){
										$output[] = $single_user;
									}
								}else{
									$output[] = $single_user;
								}
							}
						}
					}else{
						$output = $result;
					}
				}
			}
			if (isset($output) && is_array($output)){
				return $output;
			}
		}
		return false;
	}

	//functions from here and go are related to the chat section
	public function getAllUsers($id){
		if (is_numeric($id)){
			$stmt = $this->_link->prepare("SELECT * FROM users WHERE uid != ?");
			$stmt->bindParam(1,$id,PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0){
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				return $result;
			}
		}
		return false;
	}

	public function getSearchUsers($id,$value){
		if(is_numeric($id)){
			$passThis = "%" . $value . "%";
			$stmt = $this->_link->prepare("SELECT * FROM users WHERE uid != ? AND (LOWER(fname) LIKE ? OR LOWER(lname) LIKE ?)");
			$stmt->bindParam(1,$id,PDO::PARAM_INT);
			$stmt->bindParam(2,$passThis,PDO::PARAM_STR);
			$stmt->bindParam(3,$passThis,PDO::PARAM_STR);
			$stmt->execute();
			if($stmt->rowCount() > 0){
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				return $result;
			}
		}
	}

	public function getSingleUser($id){
		if (is_numeric($id)){
			$stmt = $this->_link->prepare("SELECT * FROM users WHERE uid = ? limit 1");
			$stmt->bindParam(1,$id,PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0){
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				return $result[0];
			}
		}
		return false;
	}
	public function offline($uid){
		if (is_numeric($uid)){
			$offline = "offline";
			$stmt = $this->_link->prepare('UPDATE users SET status = ? WHERE uid = ?');
			$stmt->bindParam(1,$offline,PDO::PARAM_STR);
			$stmt->bindParam(2,$uid,PDO::PARAM_INT);
			$stmt->execute();
		}
	}

	/**
	 * Update user's online status with last activity timestamp
	 */
	public function updateOnlineStatus($uid, $status = 'online') {
		if (!is_numeric($uid)) return false;
		
		try {
			$stmt = $this->_link->prepare('UPDATE users SET status = ?, last_activity = NOW() WHERE uid = ?');
			$stmt->bindParam(1, $status, PDO::PARAM_STR);
			$stmt->bindParam(2, $uid, PDO::PARAM_INT);
			return $stmt->execute();
		} catch (PDOException $e) {
			// If last_activity column doesn't exist, just update status
			$stmt = $this->_link->prepare('UPDATE users SET status = ? WHERE uid = ?');
			$stmt->bindParam(1, $status, PDO::PARAM_STR);
			$stmt->bindParam(2, $uid, PDO::PARAM_INT);
			return $stmt->execute();
		}
	}

	/**
	 * Get online status for all users (returns array of uid => status)
	 * Users inactive for more than 2 minutes are considered offline
	 */
	public function getAllOnlineStatuses() {
		$statuses = [];
		$intervalSql = Database::isPostgres() 
			? "(NOW() - INTERVAL '2 MINUTE')" 
			: "DATE_SUB(NOW(), INTERVAL 2 MINUTE)";
		
		try {
			// Try with last_activity column first
			$stmt = $this->_link->prepare(
				"SELECT uid, status, last_activity,
				 CASE WHEN last_activity > {$intervalSql} AND status = 'online' 
				      THEN 'online' ELSE 'offline' END as real_status
				 FROM users"
			);
			$stmt->execute();
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			foreach ($results as $row) {
				$statuses[$row['uid']] = $row['real_status'];
			}
		} catch (PDOException $e) {
			// Fallback: just use status column
			$stmt = $this->_link->prepare("SELECT uid, status FROM users");
			$stmt->execute();
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			foreach ($results as $row) {
				$statuses[$row['uid']] = $row['status'];
			}
		}
		
		return $statuses;
	}
}