<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// check user login
if (!(isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid']))){
 	header("location: ./login.php");
 	die();
}
require_once('database.class.php');
require_once(__DIR__ . '/../controler/image.controler.php');
require_once(__DIR__ . '/security.class.php');
class Post {

	private $_link = null;
	private $_id = null;
	function __construct(){
		$this->_link = (new Database())->connect();
		$this->_id = $_SESSION['uid'];
	}
	public function createPost($text,$img,$type,$parent){
		if (!empty($text) || !empty($img)){
			$myimg = "";
			$hasimage = 0;
			$is_profile_img = 0;
			if ($type == 'profile'){
				$hasimage = 1;
				$is_profile_img = 1;
				$myimg = $img;
			}else{
				$id = $_SESSION['uid'];
				if(isset($img['name']) && $img['name'] != "")
				{
					// Validate file upload (supports images and PDFs)
					$validation = Security::validateFileUpload($img);
					if($validation['valid'])
					{
						$folder = "../uploads/" . $id . "/";

						// Create folder with secure permissions
						if(!file_exists($folder))
						{
							mkdir($folder, 0755, true);
						}
						
						if ($validation['filetype'] === 'pdf') {
							// Handle PDF upload
							$output_filename = Security::generateSecureFilename('pdf');
							$filename = $folder . $output_filename;
							move_uploaded_file($img['tmp_name'], $filename);
							$myimg = $output_filename;
							$hasimage = 2; // 2 = PDF file
						} else {
							// Handle image upload
							require_once('../controler/image.controler.php');
							$image = new Image();
							$output_filename = Security::generateSecureFilename('jpg');
							$filename = $folder . $output_filename;
							move_uploaded_file($img['tmp_name'], $filename);
							$image->resize_image($filename,$filename,1500,1500);
							$myimg = $output_filename;
							$hasimage = 1; // 1 = image
						}
					}
				}
			}

			$post = "";
			if (!empty($text)){
				$post = htmlentities($text);
				$post = nl2br($post);
			}
			if ($parent == 0){
				$stmt = $this->_link->prepare('INSERT INTO posts (post,postimg,owner,likes,comments,has_image,parent,is_profileimg) VALUES (?,?,?,?,?,?,?,?)');
				$stmt->bindParam(1,$post,PDO::PARAM_STR);
				$stmt->bindParam(2,$myimg,PDO::PARAM_STR);
				$stmt->bindParam(3,$this->_id,PDO::PARAM_INT);
				$stmt->bindParam(4,$parent,PDO::PARAM_INT);
				$stmt->bindParam(5,$parent,PDO::PARAM_INT);
				$stmt->bindParam(6,$hasimage,PDO::PARAM_INT);
				$stmt->bindParam(7,$parent,PDO::PARAM_INT);
				$stmt->bindParam(8,$is_profile_img,PDO::PARAM_INT);
				$stmt->execute();
				return $this->_link->lastInsertId(Database::isPostgres() ? 'posts_pid_seq' : null);
			}
			elseif($type == 'comment' && $parent > 0){
				$zero = 0;
				$stmt = $this->_link->prepare('UPDATE posts SET comments = comments + 1 WHERE pid = ?;');
				$stmt->bindParam(1,$parent,PDO::PARAM_INT);
				$stmt->execute();
				//get the number of comment for that post
				$stmt = $this->_link->prepare('SELECT comments FROM posts WHERE pid = ? limit 1;');
				$stmt->bindParam(1,$parent,PDO::PARAM_INT);
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$comment_counter = $result[0]['comments'];
				$stmt = $this->_link->prepare('INSERT INTO posts (post,postimg,owner,likes,comments,has_image,parent,is_profileimg) VALUES (?,?,?,?,?,?,?,?)');
				$stmt->bindParam(1,$post,PDO::PARAM_STR);
				$stmt->bindParam(2,$myimg,PDO::PARAM_STR);
				$stmt->bindParam(3,$this->_id,PDO::PARAM_INT);
				$stmt->bindParam(4,$zero,PDO::PARAM_INT);
				$stmt->bindParam(5,$zero,PDO::PARAM_INT);
				$stmt->bindParam(6,$hasimage,PDO::PARAM_INT);
				$stmt->bindParam(7,$parent,PDO::PARAM_INT);
				$stmt->bindParam(8,$zero,PDO::PARAM_INT);
				$stmt->execute();
				$lastInsertId = $this->_link->lastInsertId(Database::isPostgres() ? 'posts_pid_seq' : null);
				return $lastInsertId . "," . $comment_counter;
			}
		}
		return false;
	}

	public function UpdatePost($text,$img,$postid,$removeImage = false){
		$id = $_SESSION['uid'];
		$postid = intval($postid);
		
		// First verify the post belongs to the current user
		$stmt = $this->_link->prepare('SELECT * FROM posts WHERE pid = ? AND owner = ? limit 1');
		$stmt->bindParam(1, $postid, PDO::PARAM_INT);
		$stmt->bindParam(2, $id, PDO::PARAM_INT);
		$stmt->execute();
		$existingPost = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if (!$existingPost) {
			return false; // Post doesn't exist or doesn't belong to user
		}
		
		// Keep existing image if no new image uploaded and not removing
		$myimg = $existingPost['postimg'] ?? '';
		$hasimage = $existingPost['has_image'] ?? 0;
		$updateImage = false;
		
		// Handle image removal
		if ($removeImage && empty($img['name'])) {
			// Delete old image file if exists
			if (!empty($myimg) && !$existingPost['is_profileimg']) {
				$oldImagePath = "../uploads/" . $id . "/" . $myimg;
				if (file_exists($oldImagePath)) {
					unlink($oldImagePath);
				}
				// Also remove thumbnail if exists
				$thumbPath = $oldImagePath . "_post_thumb.jpg";
				if (file_exists($thumbPath)) {
					unlink($thumbPath);
				}
			}
			$myimg = '';
			$hasimage = 0;
			$updateImage = true;
		}
		
		// Only process new image if one was uploaded
		if(isset($img['name']) && !empty($img['name']) && isset($img['tmp_name']) && !empty($img['tmp_name']))
		{
			// Validate image upload securely
			$validation = Security::validateImageUpload($img);
			if($validation['valid'])
			{
				$folder = "../uploads/" . $id . "/";

				if(!file_exists($folder))
				{
					mkdir($folder, 0755, true);
				}
				require_once('../controler/image.controler.php');
				$image = new Image();

				$output_filename = Security::generateSecureFilename('jpg');
				$filename = $folder . $output_filename;
				move_uploaded_file($img['tmp_name'], $filename);

				$image->resize_image($filename,$filename,1500,1500);
				$myimg = $output_filename;
				$hasimage = 1;
				$updateImage = true;
			}
		}
		
		$post = htmlentities($text);
		$post = nl2br($post);

		// Update text
		$stmt = $this->_link->prepare('UPDATE posts SET post = ? WHERE pid = ? AND owner = ?');
		$stmt->bindParam(1, $post, PDO::PARAM_STR);
		$stmt->bindParam(2, $postid, PDO::PARAM_INT);
		$stmt->bindParam(3, $id, PDO::PARAM_INT);
		$stmt->execute();
		
		// Update image if changed
		if ($updateImage) {
			$stmt = $this->_link->prepare('UPDATE posts SET postimg = ?, has_image = ? WHERE pid = ? AND owner = ?');
			$stmt->bindParam(1, $myimg, PDO::PARAM_STR);
			$stmt->bindParam(2, $hasimage, PDO::PARAM_INT);
			$stmt->bindParam(3, $postid, PDO::PARAM_INT);
			$stmt->bindParam(4, $id, PDO::PARAM_INT);
			$stmt->execute();
		}
		
		return true;
	}

	/*Get All Post For Specify User*/
	public function getPosts($id){
		$id = htmlspecialchars($id);
		$zero = 0;
		$stmt = $this->_link->prepare('SELECT * FROM posts WHERE owner = ? AND parent = ? ORDER BY pid desc;');
		$stmt->bindParam(1,$id,PDO::PARAM_INT);
		$stmt->bindParam(2,$zero,PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	/*get comment for specify post*/
	public function getComments($pid){
		$pid = htmlspecialchars($pid);
		$stmt = $this->_link->prepare('SELECT * FROM posts WHERE parent = ? ORDER BY pid desc;');
		$stmt->bindParam(1,$pid,PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	public function get_singlePost($pid, $uid = null){
		$pid = intval($pid);
		$stmt = $this->_link->prepare('SELECT * FROM posts WHERE pid = ? limit 1;');
		$stmt->bindParam(1, $pid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		if (empty($result)) {
			return false;
		}
		
		// If uid provided, check ownership (for edit/delete operations)
		// If no uid provided, return post for display
		if ($uid !== null && $result[0]['owner'] != $uid) {
			return false;
		}
		
		return $result[0];
	}

	public function get_singleComment($pid, $cid){
		$cid = intval($cid);
		$pid = intval($pid);
		$stmt = $this->_link->prepare('SELECT * FROM posts WHERE pid = ? limit 1;');
		$stmt->bindParam(1, $cid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		if (empty($result)) {
			return false;
		}
		
		if ($result[0]['parent'] == $pid) {
			return $result[0];
		}
		return false;
	}

	public function delete_single_post($id){
		$stmt = $this->_link->prepare('SELECT * FROM posts WHERE pid = ? limit 1;');
		$stmt->bindParam(1,$id,PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (is_array($result)){
			//user can delete any post but we must keep the image if it's a profile image so that we can show it in photos
			if(isset($result[0]['is_profileimg']) && $result[0]['is_profileimg']){
				//do nothing
			}else{
				if (isset($result[0]['has_image']) && $result[0]['has_image']){
					$image = "../uploads/" . $result[0]['owner'] . "/" . $result[0]['postimg'];
					$image_post = $image . "_post_thumb.jpg";
					if (file_exists($image)){
						unlink($image);
						if (file_exists($image_post)){
							unlink($image_post);
						}
					}
				}
			}
			$parent = isset($result[0]['parent']) ? ($result[0]['parent']) : 0;
		}
		$type = 'post';
		$stmt = $this->_link->prepare('SELECT * FROM posts WHERE parent = ?');
		$stmt->bindParam(1,$id,PDO::PARAM_INT);
		$stmt->execute();
		$child_post = $stmt->fetchAll(PDO::FETCH_ASSOC);
		try {
			$this->_link->beginTransaction();
			$stmt = $this->_link->prepare('DELETE FROM posts WHERE pid = ?;');
			$stmt->bindParam(1,$id,PDO::PARAM_INT);
			$stmt->execute();
			$stmt = $this->_link->prepare('DELETE FROM likes WHERE contentid = ? AND type = ?;');
			$stmt->bindParam(1,$id,PDO::PARAM_INT);
			$stmt->bindParam(2,$type,PDO::PARAM_STR);
			$stmt->execute();
			$stmt = $this->_link->prepare('DELETE FROM posts WHERE parent = ?');
			$stmt->bindParam(1,$id,PDO::PARAM_INT);
			$stmt->execute();
			foreach ($child_post as $child){
				$stmt = $this->_link->prepare('DELETE FROM likes WHERE contentid = ? AND type = ?');
				$stmt->bindParam(1,$child['pid'],PDO::PARAM_INT);
				$stmt->bindParam(2,$type,PDO::PARAM_STR);
				$stmt->execute();
			}
			$this->_link->commit();
		}catch(PDOException $e) {
	        if(stripos($e->getMessage(), 'DATABASE IS LOCKED') !== false) {
	            // This should be specific to SQLite, sleep for 0.25 seconds
	            // and try again.  We do have to commit the open transaction first though
	            $conn->commit();
	            usleep(250000);
	        } else {
	            $conn->rollBack();
	            throw $e;
	        }
    	}
		/*$stmt = $this->_link->prepare('DELETE FROM posts WHERE pid = ?;');
		$stmt->bindParam(1,$id,PDO::PARAM_INT);
		$stmt->execute();*/


		/*//delete from likes
		$stmt = $this->_link->prepare('DELETE FROM likes WHERE contentid = ? AND type = ?;');
		$stmt->bindParam(1,$id,PDO::PARAM_INT);
		$stmt->bindParam(2,$type,PDO::PARAM_STR);
		$stmt->execute();*/
		//delete child
		/*$stmt = $this->_link->prepare('DELETE FROM posts WHERE parent = ?');
		$stmt->bindParam(1,$id,PDO::PARAM_INT);
		$stmt->execute();*/
		if ($parent > 0){
			$stmt = $this->_link->prepare('UPDATE posts SET comments = comments - 1 WHERE pid = ?');
			$stmt->bindParam(1,$parent,PDO::PARAM_INT);
			$stmt->execute();
			//get the number of comment for that post
			$stmt = $this->_link->prepare('SELECT * FROM posts WHERE pid = ? limit 1;');
			$stmt->bindParam(1,$parent,PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$comment_counter = $result[0]['comments'];
			$comment_counter = $comment_counter . "," . $parent;
			return $comment_counter;
		}
		return true;
	}

	public function like_post($type,$pid,$uid){
		if (is_numeric($pid) && is_numeric($uid)){
			
			$allowed[] = 'post';
			$allowed[] = 'user';
			$allowed[] = 'comment';

			if (in_array($type,$allowed)){
				/*Our Work Start from here*/
				$stmt = $this->_link->prepare('SELECT likes from likes WHERE type = ? AND contentid = ? LIMIT 1;');
				$stmt->bindParam(1,$type,PDO::PARAM_STR);
				$stmt->bindParam(2,$pid,PDO::PARAM_INT);
				$stmt->execute();
				
				/*if empty that mean we must insert else update*/
				if ($stmt->rowCount() > 0){
					/*we have already this post so we must update the likes*/
					$result = $stmt->Fetchall(PDO::FETCH_ASSOC);
					$likes = json_decode($result[0]['likes'],true);

					$users_id = array_column($likes, "uid");

					if (!in_array($uid,$users_id)){
						/*that is mean the user is first time doing the action*/
						$arr["uid"] = $uid;
						$arr["date"] = date("Y-m-d H:i:s");

						$likes[] = $arr; // add the new record

						$likes_string = json_encode($likes);
						$stmt = $this->_link->prepare('UPDATE likes SET likes = ? WHERE type = ? AND contentid = ?;');
						$stmt->bindParam(1,$likes_string,PDO::PARAM_STR);
						$stmt->bindParam(2,$type,PDO::PARAM_STR);
						$stmt->bindParam(3,$pid,PDO::PARAM_INT);
						$stmt->execute();

						// increment the number of likes (using whitelisted table/column names)
						$table = $type . 's'; // posts, users, comments
						$typeid = $type[0] . 'id'; // pid, uid, cid
						// Safe because $type is validated against $allowed whitelist
						$stmt = $this->_link->prepare('UPDATE ' . $table . ' SET likes = likes + 1 WHERE ' . $typeid . ' = ?;');
						$stmt->bindParam(1,$pid,PDO::PARAM_INT);
						$stmt->execute();
						// we can add notification here
						/*if (type != 'user'){

						}*/
					}else{
						/*that is mean the user already do this action before and he want to cancel it*/
						$key = array_search($uid,$users_id);
						unset($likes[$key]); //delete that action

						$likes_string = json_encode($likes);
						
						$stmt = $this->_link->prepare('UPDATE likes SET likes = ? WHERE type = ? AND contentid = ?;');
						$stmt->bindParam(1,$likes_string,PDO::PARAM_STR);
						$stmt->bindParam(2,$type,PDO::PARAM_STR);
						$stmt->bindParam(3,$pid,PDO::PARAM_INT);
						$stmt->execute();
						
						// decrement the number of likes (using whitelisted table/column names)
						$table = $type . 's';
						$typeid = $type[0] . 'id';
						$stmt = $this->_link->prepare('UPDATE ' . $table . ' SET likes = likes - 1 WHERE ' . $typeid . ' = ?;');
						$stmt->bindParam(1,$pid,PDO::PARAM_INT);
						$stmt->execute();
					}
				}else{
					/*this is first action have been doing to that type so we must insert into the likes*/
					$arr["uid"] = $uid;
					$arr["date"] = date("Y-m-d H:i:s");

					$likes[] = $arr;

					$likes_string = json_encode($likes);
					$stmt = $this->_link->prepare('INSERT INTO likes (type,contentid,likes) VALUES (?,?,?);');
					$stmt->bindParam(1,$type,PDO::PARAM_STR);
					$stmt->bindParam(2,$pid,PDO::PARAM_INT);
					$stmt->bindParam(3,$likes_string,PDO::PARAM_STR);
					$stmt->execute();

					// increment the number of likes (using whitelisted table/column names)
					$table = $type . 's'; // posts, users, comments
					$typeid = $type[0] . 'id'; // pid, uid, cid
					// Safe because $type is validated against $allowed whitelist
					$stmt = $this->_link->prepare('UPDATE ' . $table . ' SET likes = likes + 1 WHERE ' . $typeid . ' = ?;');
					$stmt->bindParam(1,$pid,PDO::PARAM_INT);
					$stmt->execute();
					// we can add notification here
	 				/*if($type != "user"){
		 				
					}*/
				}
				if ($type != "user"){
					$stmt = $this->_link->prepare('SELECT likes FROM ' . $type . 's WHERE ' . $type[0] . 'id = ? limit 1;');
					$stmt->bindParam(1,$pid,PDO::PARAM_INT);
					$stmt->execute();
					if ($stmt->rowCount() > 0) {
						$result = $stmt->FetchAll(PDO::FETCH_ASSOC);
						return $result[0]['likes'];
					}
					return 0;
				}else{
					return true;
				}
			}
			return false;
		}
	}

	public function getHomePost($list_uid,$user){
		if (!empty($list_uid)){
			// Sanitize all UIDs to prevent SQL injection
			$list_uid = array_filter($list_uid, 'is_numeric');
			$list_uid = array_map('intval', $list_uid);
			
			if (empty($list_uid)) {
				return [];
			}
			
			// Create placeholders for prepared statement
			$placeholders = str_repeat('?,', count($list_uid) - 1) . '?';
			$parent = 0;
			$user = intval($user);
			
			$stmt = $this->_link->prepare("SELECT * FROM posts WHERE parent = ? AND (owner = ? OR owner IN (" . $placeholders . ")) ORDER BY pid DESC");
			
			// Bind parameters
			$params = array_merge([$parent, $user], $list_uid);
			$stmt->execute($params);
			
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		}
		return [];
	}
}