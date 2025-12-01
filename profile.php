<?php
// Redirect to new profile page
$redirectUrl = './profile_new.php';
if (isset($_GET['uid'])) {
    $redirectUrl .= '?uid=' . intval($_GET['uid']);
}
if (isset($_GET['tab'])) {
    $redirectUrl .= (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'tab=' . htmlspecialchars($_GET['tab']);
}
header("Location: " . $redirectUrl);
exit;

 session_start();
 if (!(isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid']))){
    header("location: ./login.php");
    die();
 }
 require_once('./models/user.class.php');
 $uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
 $user = new User();
 $_USER_DATA = $user->getData($uid);
 $profile_data = $_USER_DATA;
 if (isset($_GET['uid']) && is_numeric($_GET['uid'])){
   $profile_data = $user->getData($_GET['uid']);
   if (is_array($profile_data)){
     $uid = $profile_data['uid'];
   }else{
    $profile_data = $_USER_DATA;
    header('location: ./profile.php');
   }
 }
 require_once('./models/post.class.php');
 $post = new Post();
 $posts = $post->getPosts($uid);
 $following = $user->getFollowing($_SESSION['uid'],"user");
 $value_follow = "Follow";
 if (is_array($following)){
    if(in_array($profile_data['uid'],$following))
        $value_follow = "Unfollow";
 }
 require_once('./controler/image.controler.php');
 $images = new Image();
 $following_number = $user->getFollowingNumber($profile_data['uid'],'user');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile page</title>
    <link rel="stylesheet" href="Styles/home.css?v=<?php echo(rand(0,9e6)); ?>">
    <link rel="icon" href="./images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
    <title>THODZ|Profile</title>
</head>

<body>
    <div class="setting-menu" style="display: none;">
        <div class="settings-menu-inner">
            <div class="user-profile">
                <img src="<?php echo $images->get_thumb_profile($_USER_DATA['profileimg']); ?>" alt="">
                <div>
                    <p><?php echo html_entity_decode($_USER_DATA['fname']) . " " . html_entity_decode($_USER_DATA['lname']); ?></p>
                   <a href="./profile.php?uid=<?php echo $_USER_DATA['uid']; ?>">See your profile</a>
                </div>
            </div>
            <hr>
            <div class="user-profile">
                <img src="images/feedback.png" alt="">
                <div>
                    <p>Give Feedback</p>
                   <a href="#">Help us to improve our website</a>
                </div>
            </div>
            <hr>
            <div class="setting-links">
                <img src="images/setting.png" class="settings-icon">
                <a href="./profile.php?redirect=settings">Settings <img src="images/arrow.png" width="10px"></a>
            </div>
            <div class="setting-links">
                <img src="images/help.png" class="settings-icon">
                <a href="#">Help & and Support<img src="images/arrow.png" width="10px"></a>
            </div>
            <div class="setting-links">
                <img src="images/display.png" class="settings-icon">
                <a href="#">Display & Accesibility<img src="images/arrow.png" width="10px"></a>
            </div>
            <div class="setting-links">
                <img src="images/logout.png" class="settings-icon">
                <a href="./logout.php">Logout <img src="images/arrow.png" width="10px"></a>
            </div>
        </div>
    </div>
    <div class="navbar">
      <div class="navbar_left">
        <a href="./home.php">
          <img class="navbar_logo" src="./images/logo.png" alt="logo" id="not_image">
        </a>
        <div class="input-icons">
          <i class="fas fa-search icon"></i>
          <a href="#">
            <input class="input-field" type="text" placeholder="Search on THODZ" id="searchInput">
          </a>
        </div>
      </div>

      <div class="navbar_center">
        <a href="./home.php">
          <i class="fas fa-home"></i>
        </a>
        <a href="./following.php">
          <i class="fas fa-user-friends"></i>
        </a>
        <a href="./followers.php">
          <i class="fas fa-users"></i>
        </a>
      </div>

      <div class="navbar_right">
        <div class="navbar_right_profile active">
          <a href="/profile.php">
            <img id="myprofile_image1" src="<?php echo $images->get_thumb_profile($_USER_DATA['profileimg']); ?>" alt="profile">
          </a>
          <span><?php echo html_entity_decode($_USER_DATA['fname']); ?></span>
        </div>
        <div class="navbar_right_links">
        <!-- <a href="#">
          	<i class="fa fa-bell"></i>
          </a> -->
          <a href="./users_chat.php">
          	<i class="fab fa-facebook-messenger"></i>
          </a>
          <a href="#" onclick="SettingMenuToggle()">
          	<i class="fas fa-arrow-down"></i>
      	  </a>
        </div>
      </div>
    </div>
    <?php if ($profile_data['uid'] == $_USER_DATA['uid']){ ?>
    <div class="upload_profile_image" state="hidden" style="display:none;">
        <form id="profile_image_form" method="POST" action="#" enctype="multipart/form-data">
            <h2 style="color:white;">Change Profile Image</h2>
            <input type="file" name="upload_image" id="upload_image" style="display: none;">
            <input id="upload_alt" type="button" value="Select Image"><br><br>
            <input type="submit" name="submit" id="upload_button">
        </form>        
    </div>
    <?php } ?>
    <div class="container">
        <div class="full_image_size" style="
            position: fixed;
            z-index: 99999;
            left: 50%;
            top: 50%;
            transform: translate(-24%, -50%);
            width: 600px;
            height: 500px;
            margin-top: 80px;
            display: none;
            ">
            <img src="./uploads/19/THODZ_62767d9c082299.76320943.jpg">
            <i class="fa fa-comment" style="position: absolute;top: 0;right: -100px;"></i>
        </div>
        <div class="profile-header">

            <div class="profile-img">

                <img id="myprofile_image2" src="<?php echo $images->get_thumb_profile($profile_data['profileimg']); ?>" width="230" alt="">
                <?php if ($profile_data['uid'] == $_USER_DATA['uid']){ ?>
                <div class="pitcure-hover showChangeProfile">
                    Change Picture
                </div>
                <?php } ?>
            </div>
            <div class="profile-nav-info">
                <div class="profileheader-infos">
                    <h3 class="user-name" id="fullname_profile"><?php echo html_entity_decode($profile_data['fname']) . " " .  html_entity_decode($profile_data['lname']); ?></h3>
                    <?php if ($profile_data['uid'] == $_USER_DATA['uid']){ ?>
                    <div class="address">
                        <a href="#" class="state showChangeProfile">Change Profile Picture</a>
                    </div>
                    <?php } ?>
                </div>
                <div class="profileheader-infos">
                    <span style="font-weight: bold;font-size: 25px;color: #2d88ff;font-variant: small-caps;font-family: sans-serif;">Followers</span>
                    <div style="text-align: center;">
                        <a href="<?php ($profile_data['uid'] == $_USER_DATA['uid']) ? ($link = "./followers.php") : ($link = "#"); echo $link; ?>" style="font-weight: bold;font-size: 25px;color: white;text-decoration: none;" id="follower_count"><?php echo $profile_data['likes'] ?></a>
                    </div>
                </div>
                <div class="profileheader-infos">
                    <span style="font-weight: bold;font-size: 25px;color: #2d88ff;font-variant: small-caps;font-family: sans-serif;">Following</span>
                    <div style="text-align: center;">
                        <a href="<?php ($profile_data['uid'] == $_USER_DATA['uid']) ? ($link = "./following.php") : ($link = "#"); echo $link; ?>" style="font-weight: bold;font-size: 25px;color: white;text-decoration: none;"><?php echo $following_number ?></a>
                    </div>
                </div>
                <div class="follow-buttons">
                    <?php if ($profile_data['uid'] != $_USER_DATA['uid']){ ?>
                    <a href="javascript:doLike('user', <?php echo($profile_data['uid']); ?>,<?php echo($_USER_DATA['uid']); ?>)" style="text-decoration: none;">
                        <div class="button <?php echo ($value_follow == 'Unfollow') ? 'following' : 'follow'; ?>" id="follow_button"><?php echo $value_follow; ?></div>
                    </a>
                    <?php } ?>
                </div>
                

            </div>

        </div>
        <div class="main-bd">
            <div class="left-side">
                <div class="profile-side">
                    <h3>Email</h3>
                    <p class="user-mail"><i class="fa fa envelope" id="email_profile"><?php echo $profile_data['email'] ?></i></p>
                    <div class="user-bio">
                        <h3>About</h3>
                        <p class="bio" id="about_profile"><?php echo html_entity_decode($_USER_DATA['about']); ?></p>
                    </div>
                    <?php 
                        if ($profile_data['uid'] != $_USER_DATA['uid']){
                    ?>
                    <div class="profile-btn">
                        <button class="chatbtn" onclick="location.href='/chat.php?uid=<?php echo $profile_data['uid']; ?>'">
                            <i class="fa fa-comment">Chat</i>
                        </button>
                    </div>
                <?php } ?>
                </div>
            </div>
            <div class="right-side">
                <div class="nav">
                    <ul>
                        <li index="0" class="user-post active" id="user-post-id">Posts</li>
                        <?php if ($profile_data['uid'] == $_USER_DATA['uid']){ ?>
                        <li index="1" class="profile-settings" id="profile-settings-id">Profile</li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="profile-body">
                    <div class="profile-posts tab">
                        <?php if ($profile_data['uid'] == $_USER_DATA['uid']){ ?>
                        <h1> Create Post </h1>
                            <div class="share">
                              <form id="post_form" action="#" method="POST" enctype="multipart/form-data">
                              <div class="share_upSide">
                                <img id="myprofile_image3" src="<?php echo $images->get_thumb_profile($_USER_DATA['profileimg']); ?>" alt="profile">
                                <textarea id="create_post_textarea" name="textarea" placeholder="What's on your mind, Gladius?"></textarea>
                              </div>
                              <hr>
                              <div class="share_downSide">
                                <div class="share_downSide_link">
                                  <input id="image" type="file" name="image">
                                  <button id="image_alt" type="button">Select image</button>
                                </div>
                                <div class="share_downSide_link">
                                  <input type="submit" name="submit" id="post_button" value="Post">
                                </div>
                              </div>
                              </form>
                            </div>
                        <?php } 
                            $yourposts = ($profile_data['uid'] == $_USER_DATA['uid']) ? "Your Posts" : $profile_data['fname']. " Posts" ;
                        
                        echo "<h1>$yourposts</h1>";
                        ?>
                        <div id="post_section">
                            
                            <?php
                                if ($posts){
                                    $user = new User();
                                    $current_user_image = $images->get_thumb_profile($_USER_DATA['profileimg']);
                                    foreach ($posts as $POST){
                                        $images = new Image();
                                        $ROW_USER = $user->getData($POST['owner']);
                                        if (isset($POST['postimg']) && !empty($POST['postimg'])){
                                            $image = "./uploads/" . $POST['owner'] . "/" . $POST['postimg'];
                                        }
                                        include('post.php');
                                    }
                                }  
                            ?>
                            <!-- search list container -->
                            <div class="search_list_wrapper" state="hidden" style="display: none;">
                                <div class="close_search_list"><span>X</span></div>
                                <div class="search_list_container">
                                    
                                    <input type="text" placeholder="Person Name" id="SearchInputInside">
                                    <div id="userList_search">
                                        
                                    </div>
                                </div>
                            </div>
                            <!-- who likes list container -->
                            <div class="likes_list_wrapper" state="hidden" style="display: none;">
                                <div class="likes_list_container" id="likes_list_container">
                                    <div class="close_likes_list"><span>X</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="divider"><hr></div>
                        <p>Refresh the page to load new posts</p>
                        <input type="submit" value="More..." style="width: 25%;border-radius: 10px;display: none;">
                    </div>
                    <?php
                        if ($profile_data['uid'] == $_USER_DATA['uid']){
                    ?>
                <form id="settings_form" action="#" method="POST">
                    <div class="profile-settings tab" style="display: block;">
                        <h1>Profile</h1>
                        <!-- display inline block to center it -->
                        <div id="settings_msg_error" style="background: indianred;width: 80%;text-align: center;padding: 5px;margin-top: 10px;display:none;font-weight: bold;border-radius: 30px;">Error</div>
                            <div class="settings_form">

                                <div class="form_group">
                                    <label for="fname">First Name:</label>
                                    <input type="text" id="fname" name="fname" value="<?php echo html_entity_decode($profile_data['fname']); ?>" class="settings_input">
                                </div>
                                <div class="form_group">
                                    <label for="lname">Last Name:</label>
                                    <input type="text" id="lname" name="lname" value="<?php echo html_entity_decode($profile_data['lname']); ?>" class="settings_input">
                                </div>
                                <div class="form_group">
                                    <label for="pass">New Password:</label>
                                    <input type="password" id="pass" name="password" class="settings_input">
                                </div>
                                <div class="form_group">
                                    <label for="repass">Repeat Password:</label>
                                    <input type="password" id="repass" name="rpassword" class="settings_input">
                                </div>
                                <div class="form_group">
                                    <label for="email">Email:</label>
                                    <input type="email" id="email" name="email" value="<?php echo html_entity_decode($profile_data['email'])?>" class="settings_input">
                                </div>
                                <div class="form_group">
                                    <label for="about">About</label>
                                    <textarea id="about" rows="10" name="about" class="settings_input"><?php echo $_USER_DATA['about']; ?></textarea>
                                </div>
                                <div class="form_group">
                                    <div></div>
                                    <span class="saveButton">Save</span>
                                </div>
                            
                        </div>

                    </div>
                    <div class="confirmPassWrapper">
                        <div class="confirmPassContainer">
                            <div class="confirmPassForm">
                                <input type="password" id="confirmPasswordInput" name="currentpassword" placeholder="Current password" value="">
                                <span class="confirmPassButton">Confirm Password</span>
                            </div>           
                        </div> 
                    </div>
                </form>
                <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="pictures_viewer_wrapper">
        <div class="close_preview_list" style="right: 15%;"><span>X</span></div>
        <img src="" id="imagePreview" width="800">
    </div>
    <script src="./Js/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="./Js/script.js?v=<?php echo(rand(0,9e6)); ?>"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript">

        let settingsMenu = false;
        function SettingMenuToggle () {
            if(settingsMenu) {
                document.querySelector(".setting-menu").style.display = "none";
                // $(".setting-menu").slideUp("slow");
            } else{
                document.querySelector(".setting-menu").style.display = "block";
                // $(".setting-menu").slideDown("slow");
            }
            settingsMenu = !settingsMenu;
        }
        $("img").click(function(){
            
            
            let img = $(this).attr("src");
            let id = $(this).attr("id");
            img = img.replace(".jpg_profile_thumb", "");
            img = img.replace(".jpg_post_thumb", "");

            if(img != "./images/emoji_like.png" && id != "myprofile_image1" && id != "not_image"){
                $(".pictures_viewer_wrapper").css("display", "flex");
                $("#imagePreview").attr("src" , img);
            }
            
        });

        $(".close_preview_list , .close_preview_list span").click(function(){
            $(".pictures_viewer_wrapper").css("display","none");
            $(".pictures_viewer_wrapper").attr("state" , "hidden");
        });

        $("#searchInput").click(function(){
            $(".search_list_wrapper").css("display","flex");
            $(".search_list_wrapper").attr("state" , "visible");
        });

        $(".close_search_list , .close_search_list span").click(function(){
            $("#userList_search").empty();
            document.getElementById("SearchInputInside").value = "";
            $(".search_list_wrapper").css("display","none");
            $(".search_list_wrapper").attr("state" , "hidden");
        });


        $(".saveButton").click(function(){
            $(".confirmPassWrapper").css("display" ,"flex");
        });

        $(".right-side .nav ul li").click(function () {
          $(".right-side .nav ul li").each(function () {
            $(this).removeClass("active");
          });
          $(this).addClass("active");
          let indx = $(this).attr("index");
          tabs(indx);
        });
       
        $(".addimage").click(function(){
            $("#commentImage").click();
         });
        
        $(document).on('click','.addComment',function(){
            let forp = $(this).attr("forpost");
            let div = $(".addcommentsection[forpost="+forp+"]");
            if(div.attr("st") === "hidden"){
                div.css("display" , "block");
                div.attr("st" , "visible");
            } else {
                div.css("display" , "none");
                div.attr("st" , "hidden");
            }
            
        });

        $(document).on("click" , ".showallbuttons" , function(){
            let target = $(this).attr("forpost");
            let div = $(".allcomments[forpost="+target+"]");
            if(div.attr("st") === "hidden"){
                div.css("display" , "block");
                div.attr("st" , "visible");
                $(this).find("button").text("Hide all comments");
            } else {
                div.css("display" , "none");
                div.attr("st" , "hidden");
                $(this).find("button").text("Show all comments");
            }
        });

        $(".showChangeProfile").click(function(){
            let state = $(".upload_profile_image").attr("state");
            if(state === "hidden"){
                 $(".upload_profile_image").css("display" ,"block");
                  $(".upload_profile_image").attr("state", "visible");
            }else{
                 $(".upload_profile_image").css("display","none");
                 $(".upload_profile_image").attr("state", "hidden");
            }
        });

        /*$(".show_likes_list").click(function(){
            $(".likes_list_wrapper").css("display","flex");
            $(".likes_list_wrapper").attr("state" , "visible");
        });*/

        $(".close_likes_list , .close_likes_list span").click(function(){
            $(".likes_list_wrapper").css("display","none");
            $(".likes_list_wrapper").attr("state" , "hidden");
        });

        const tab = document.querySelectorAll(".tab");

        function tabs(panelIndex) {
          tab.forEach(function (node) {
            node.style.display = "none";
          });

          tab[panelIndex].style.display = "block";
        }
        tabs(0);
        document.getElementById('image_alt').addEventListener('click',function(){
            document.getElementById('image').click();
        });
        document.getElementById('upload_alt').addEventListener('click',function(){
            document.getElementById('upload_image').click();
        });
    </script>
</body>

</html>