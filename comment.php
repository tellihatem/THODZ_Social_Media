<?php
    if (isset($comment_user['profileimg']) && !empty($comment_user['profileimg'])){
        $comment_profile_image  = $images->get_thumb_profile($comment_user['profileimg']);
    }else{
        $comment_profile_image = "./images/user_female.jpg";
        if ($comment_user['gender'] == 'male'){
            $comment_profile_image = "./images/user_male.jpg";
        }
    }
    $comment_profile_image = "." . substr($comment_profile_image,strpos($comment_profile_image,"/"),strlen($comment_profile_image));
?>
<div class="comment" id="comment_<?php echo $COMMENT['pid']; ?>">
    <span style="position: absolute;margin: 8px 0px 0px 38%;">
        <?php
            if ($COMMENT['owner'] == $_SESSION['uid']){?>
                <a class="edit_delete_post" href="./edit_post.php?pid=<?php echo $COMMENT['pid'] ?>">Edit</a>
                <a class="edit_delete_post" href="javascript:deleteComment(<?php echo $COMMENT['pid']; ?>)">Delete</a>
        <?php } ?>
    </span>
    <a href="profile.php?uid=<?php echo $comment_user['uid'] ?>"><img class="userpic" src="<?php echo $comment_profile_image; ?>" id="not_image" /></a>
    <div class="commentdata">
        <a href="profile.php?uid=<?php echo $comment_user['uid'] ?>" style="width: fit-content;"><?php echo htmlspecialchars($comment_user['fname']) . " " .  htmlspecialchars($comment_user['lname']); ?></a>
        <span class="commentdate"><?php echo $COMMENT['date'] ?> </span>
        <?php 
            if (!empty($COMMENT['post'])){
                echo "<p>" . $COMMENT['post'] . "</p>";
            }
            if (!empty($COMMENT['postimg']) && isset($COMMENT['postimg']) && $COMMENT['has_image'])
            {
                $comment_image = "./uploads/" . $COMMENT['owner'] . "/" . $COMMENT['postimg'];
                if($COMMENT['is_profileimg']){
                    $comment_image = $images->get_thumb_profile($comment_image);
                }else{
                    $comment_image = $images->get_thumb_post($comment_image);
                }
                $comment_image = "." . substr($comment_image,strpos($comment_image,"/"),strlen($comment_image));
                echo "<img class='commentimage' src='" . $comment_image . "' alt='image'>"; 
            }
        ?>
        <div class="commentsLikesarea">
            <a href="javascript:doLike('post', <?php echo($COMMENT['pid']); ?>,<?php echo($_SESSION['uid']); ?>)">Like</a>
            <a href="javascript:whoLikes(<?php echo $POST['pid'] ?>)">
                <div class="emojis">
                    <span id="likes_count_<?php echo $COMMENT['pid'] ?>" style="margin-right: 5px;"><?php echo $COMMENT['likes'] ?></span>
                    <img src="./images/emoji_like.png" alt="like">
                </div>
            </a>
            
        </div>
    </div>
    
</div>