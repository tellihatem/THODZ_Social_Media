<div class="newsfeed_container">
    <div class="news_feed" id="<?php echo $POST['pid'] ?>">
    <div class="news_feed_title">
        <?php
            if (isset($ROW_USER['profileimg']) && !empty($ROW_USER['profileimg'])){
                $profile_image  = $images->get_thumb_profile($ROW_USER['profileimg']);
            }else{
                $profile_image = "./images/user_female.jpg";
                if ($ROW_USER['gender'] == 'male'){
                    $profile_image = "./images/user_male.jpg";
                }
            }
            $profile_image = "." . substr($profile_image,strpos($profile_image,"/"),strlen($profile_image));
        ?>
        <a href="profile.php?uid=<?php echo $ROW_USER['uid']; ?>" style="text-decoration: none;"><img src="<?php echo $profile_image ?>" id="not_image" alt="user"></a>
        <div class="news_feed_title_content">
            <a href="profile.php?uid=<?php echo $ROW_USER['uid']; ?>" style="text-decoration: none;"><p><?php echo html_entity_decode($ROW_USER['fname']) . " " .  html_entity_decode($ROW_USER['lname']); ?></p></a>
            <span><?php echo $POST['date'] ?>  <i class="fas fa-globe-americas"></i></span>
        </div>
        <?php 
            if($POST['is_profileimg'])
                {
                    $pronoun = "his";
                    if($ROW_USER['gender'] == "Female")
                    {
                        $pronoun = "her";
                    }
                    echo "<span style='font-weight: normal;color: #aaa;font-size: small;margin: -20px 0px 0px 10px;'>Updated $pronoun profile image</span>";
                }
        ?>
        <span style="position: absolute;margin: -20px 0px 0px calc(100% - 18%);">
            <?php
            if ($POST['owner'] == $_SESSION['uid']){
                echo"<a class='edit_delete_post' href='./edit_post.php?pid=$POST[pid]'>Edit</a>";
                echo"<a class='edit_delete_post' href='./delete_post.php?pid=$POST[pid]'>Delete</a>";
            }
            ?>
        </span>
    </div>
    <div class="news_feed_description">
        <?php 
            if (!empty($POST['post'])){
                echo "<p class='news_feed_subtitle'>" . html_entity_decode($POST['post']) . "</p>";
            }
            if (!empty($POST['postimg']) && isset($POST['postimg']) && $POST['has_image'])
            {
                $image = "./uploads/" . $POST['owner'] . "/" . $POST['postimg'];
                if($POST['is_profileimg']){
                    $image = $images->get_thumb_profile($image);
                }else{
                    $image = $images->get_thumb_post($image);
                }
                $image = "." . substr($image,strpos($image,"/"),strlen($image));
                echo "<img src='" . $image . "' alt='image'>"; 
            }
        ?>
    </div>
    <div class="likes_area">
        <a href="javascript:whoLikes(<?php echo $POST['pid'] ?>)">
            <div class="emojis">
                <img class="show_likes_list" src="./images/emoji_like.png" alt="like" id="not_image">
                <span id="likes_count_<?php echo $POST['pid'] ?>"><?php echo $POST['likes'] ?></span>
            </div>
        </a>
        <div class="comment_counts">
            <span id="comment_count_<?php echo $POST['pid'] ?>"><?php echo $POST['comments'] . " Comment" ?></span>
        </div>
    </div>

    <div class="divider"><hr></div>
    <div class="likes_buttons">
        <a href="javascript:doLike('post', <?php echo($POST['pid']); ?>,<?php echo($_SESSION['uid']); ?>)" style="width: 100%;display: flex;align-items: center;justify-content: center;text-decoration: none;">
            <div class="likes_buttons_links">
                <?php
                    $style_like = 'style="color:rgb(180, 183, 187);"';
                    $following_Like = $user->getFollowing($ROW_USER['uid'],'post'); 
                    if (is_array($following_Like)){
                        if (in_array($POST['pid'],$following_Like)){
                            $style_like = 'style="color:rgb(45, 136, 255);"';
                        }
                    }
                ?>
                <i class="far fa-thumbs-up" <?php echo $style_like; ?> id="thumbs-up-<?php echo $POST['pid']; ?>"></i>
                <span <?php echo $style_like ?> id="like-up-<?php echo $POST['pid']; ?>">Like</span>
            </div>
        </a>
            <div class="addComment"  forpost="<?php echo $POST['pid'] ?>"  style="width: 100%;display: flex;align-items: center;justify-content: center;text-decoration: none;">
                <div class="likes_buttons_links">
                    <i class="far fa-thumbs-up"></i>
                    <span>Comment</span>
                </div>
            </div>
        </div>
        <div class="addcommentsection" forpost="<?php echo $POST['pid'] ?>" st="hidden">
            <form id="add_comment_form_<?php echo $POST['pid'] ?>" method="POST" action="#" enctype="multipart/form-data">
                <div class="commentsection">
                    <img src="<?php echo isset($current_user_image) ? ($current_user_image) : ($profile_image); ?>" />
                    <input type="text" id="commentText_<?php echo $POST['pid'] ?>" name="commentText" placeholder="Write your comment.." forpost="<?php echo $POST['pid'] ?>" />
                    <button type="button" class="addimage" id="addimage"><i class="fa fa-image"></i></button> 
                    <input type="file" id="commentImage" name="commentImage" style="display:none" />
                    <input type="hidden" name="postid" value="<?php echo $POST['pid'] ?>">
                    <button class="sendcomment" onclick="javascript:addCommento(<?php echo $POST['pid'] ?>)" type="button"><i class="fa fa-caret-right"></i></button>
                </div>
            </form>
            <div class="showallbuttons" forpost="<?php echo $POST['pid'] ?>"><button> Show all comments </button> </div>
        </div>
    </div>
    <div class="allcomments" id="comment_post_<?php echo $POST['pid'] ?>" st="hidden" forpost="<?php echo $POST['pid'] ?>">
        
        <?php
            require_once(__DIR__ . '/models/post.class.php');
            $post = new Post();
            $comments = $post->getComments($POST['pid']);
            if (is_array($comments)){
                foreach($comments as $COMMENT){
                    $comment_user = $user->getData($COMMENT['owner']);
                    if (isset($COMMENT['postimg']) && !empty($COMMENT['postimg'])){
                        $comment_image = "./uploads/" . $comment_user['uid'] . "/" . $COMMENT['postimg'];
                    }
                    include('comment.php');
                }
            }

        ?>
    </div>  
</div>