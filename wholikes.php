<?php 
if (isset($_POST['lid'])){
        $likeid = htmlspecialchars($_POST['lid']);
        require_once(__DIR__ . '/models/user.class.php');
        $user = new User();
        $likes_array = $user->getFollowers($likeid,'post');
?>
<div class="likes_list_wrapper" state="hidden">             
    <div class="likes_list_container">
        <div class="close_likes_list"><span>X</span></div>
        <?php
            foreach ($likes_array as $users){
                $uid = intval($users['uid']);
                $user_info = $user->getData($uid);
                $user_image = "../uploads/".$user_info['uid']."/".$user_info['profileimg'];
                $full_name = html_entity_decode($user_info['fname']) . " " . html_entity_decode($user_info['lname']);
                echo "<div class='person_liked'>";
                    echo "<div class='liked_user_info'>";
                        echo "<img src='".$user_image."'/>";
                        echo "<h3>".$full_name."</h3>";
                    echo "</div>";
                    echo "<i class='far fa-thumbs-up'></i>";
                echo "</div>";
            }
        ?>
    </div>
</div>
<?php } ?>
<!-- was in the post -->
<div class="likes_list_wrapper_<?php echo $POST['pid'] ?>" state="hidden">
                     
        <div class="likes_list_container_<?php echo $POST['pid'] ?>">
            <a href="javascript:whoLikes(<?php echo $POST['pid'] ?>,'close')" class="close_likes_list"><span>X</span></a>
            <?php
                $list_likes = $user->getFollowers($POST['pid'],'post');
                if (is_array($list_likes)){
                    foreach ($list_likes as $like) {
                        $user_like = $user->getData($like['uid']);
                            if (isset($user_like['profileimg']) && !empty($user_like['profileimg'])){
                                $user_image  = $images->get_thumb_profile($user_like['profileimg']);
                            }else{
                                $user_image = "./images/user_female.jpg";
                                if ($user_like['gender'] == 'male'){
                                    $user_image = "./images/user_male.jpg";
                                }
                            }
                        $user_image = "." . substr($user_image,strpos($user_image,"/"),strlen($user_image));
                        ?>   
            <div class="person_liked">
                <div class="liked_user_info">
                    <img src="<?php echo $user_image; ?>"/>
                    <h3><?php echo html_entity_decode($user_like['fname']) . '   ' .  html_entity_decode($user_like['lname']); ?></h3>
                </div>
                <i class="far fa-thumbs-up"></i>
            </div>
            <?php
                    } 
                }
            ?>
        </div>
    </div>