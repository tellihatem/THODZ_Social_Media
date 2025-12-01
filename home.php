<?php
// Redirect to new home page
header("Location: ./home_new.php");
exit;
 $uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
 $user = new User();
 $_USER_DATA = $user->getData($uid);
 require_once('./models/post.class.php');
 //$posts = $post->getTimeLine($uid);
 $following = $user->getFollowing($uid,"user");
 require_once('./controler/image.controler.php');
 $image = new Image();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THODZ|Home</title>
    <link rel="icon" href="./images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous">
    <link rel="stylesheet" href="./Styles/home.css?v=<?php echo(rand(0,9e6)); ?>">
  </head>
  <body>
    <div class="setting-menu" style="display: none;">
        <div class="settings-menu-inner">
            <div class="user-profile">
                <img src="<?php echo $image->get_thumb_profile($_USER_DATA['profileimg']); ?>" alt="">
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
        <a class="active_icon" href="./home.php" id="home_icon">
          <i class="fas fa-home"></i>
        </a>
        <a href="./following.php" id="following_icon">
          <i class="fas fa-user-friends"></i>
        </a>
        
        
        <a href="./followers.php" id="followers_icon">
          <i class="fas fa-users"></i>
        </a>
      </div>

      <div class="navbar_right">
        <div class="navbar_right_profile">
          <a href="./profile.php">
            <img src="<?php echo $image->get_thumb_profile($_USER_DATA['profileimg']); ?>" alt="profile" id="not_image">
          </a>
          <span><?php echo html_entity_decode($_USER_DATA['fname']); ?></span>
        </div>
        <div class="navbar_right_links">
          <a href="./users_chat.php">
          	<i class="fab fa-facebook-messenger"></i>
          </a>
          <a href="#" onclick="SettingMenuToggle()">
          	<i class="fas fa-arrow-down"></i>
      	  </a>
        </div>
      </div>
    </div>
    <!-- navbar ends -->
    <!-- settings bar start -->
    
    <!-- settings bar ends -->
    <!-- content starts -->
    <div class="content">
      <div class="content_left">
        <ul>
          <li>
            <a href="./profile.php">
              <img src="<?php echo $image->get_thumb_profile($_USER_DATA['profileimg']); ?>" alt="profile" id="not_image">
              <span><?php echo html_entity_decode($_USER_DATA['fname']) . " " . html_entity_decode($_USER_DATA['lname']); ?></span>
            </a>
          </li>
          
          <li>
            <a href="./followers.php">
              <img src="./images/friends.png" alt="friends" id="not_image">
              <span>Followers</span>
            </a>
          </li>
          <li>
            <a href="./following.php">
              <img src="./images/friends.png" alt="friends" id="not_image">
              <span>Following</span>
            </a>
          </li>    
        </ul>
      </div>

      <div class="content_center">
        
        <div class="media_container">
          <form id="post_form" method="POST" enctype="multipart/form-data" action="#">
            <div class="share">
              <div class="share_upSide">
                <img src="<?php echo $image->get_thumb_profile($_USER_DATA['profileimg']); ?>" alt="profile">
                <textarea name="textarea" placeholder="What&#39;s on your mind, <?php echo html_entity_decode($_USER_DATA['fname']); ?>?"></textarea>
              </div>
              <hr>
              <div class="share_downSide">
                
                <div class="share_downSide_link">
                  <input id="image" type="file" name="image">
                  <button id="image_alt" type="button">Select image</button>
                </div>
                <div class="share_downSide_link">
                  <input type="submit" name="post_button" id="post_button" value="Post">
                </div>
              </div>
            </div>
          </form>
          <!-- news feed -->
          <?php
            if (!empty($following)){
              $post = new Post();
              $posts = $post->getHomePost($following,$_USER_DATA['uid']);
              if (is_array($posts)){
                $user = new User();
                $images = new Image();
                $current_user_image = $images->get_thumb_profile($_USER_DATA['profileimg']);
                foreach ($posts as $POST){
                  $ROW_USER = $user->getData($POST['owner']);
                  $image = "./uploads/" . $ROW_USER['uid'] . "/" . $POST['postimg'];
                  include('post.php');
                }
              }

            }else{
              echo "You must follow somebody or have own posts to show here";
            }
          ?>
          <!-- fin -->
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
        <p style="color:white">Refresh the page to load new posts</p>
      </div>
      <div class="pictures_viewer_wrapper">
        <div class="close_preview_list" style="right: 15%;"><span>X</span></div>
        <img src="" id="imagePreview" width="800">
      </div>

      <div class="content_right">
        <div class="content_right_inner">
          <div class="your_pages">
            <h3>Suggested for you</h3>
            
          </div>
          <ul>
            <?php 
              $suggested = $user->getRandomUser($_SESSION['uid']);
              $image = new Image();
              if (is_array($suggested)){
                foreach($suggested as $suggest){
                  if (isset($suggest['profileimg']) && !empty($suggest['profileimg'])){
                      $suggest_image = "./uploads/" . $suggest['uid'] . "/" . $suggest['profileimg'];
                      $suggest_image = $image->get_thumb_profile($suggest_image);
                  }else{
                      $suggest_image = "./images/user_female.jpg";
                      if ($suggest['gender'] == 'male'){
                          $suggest_image = "./images/user_male.jpg";
                      }
                  }
                  $suggest_full_name = html_entity_decode($suggest['fname']) . " " . html_entity_decode($suggest['lname']);
                  $suggest_profile = "profile.php?uid=" . $suggest['uid'];
                  echo "<li><a href='".$suggest_profile."'><img class='your_page_logo' src='".$suggest_image."' alt='codersbite' id='not_image'><span>".$suggest_full_name."</span></a></li>";
                }
              }
              else{
                echo"<li><span style='color: #e5e7eb;padding: 10px;'>no suggestion for now</span></li>";
              }
            ?>
          </ul>
          
          
          
          <div class="content_right_divider"></div>
          <div class="contacts">
            <h3>Contacts</h3>
            <div class="contact_icons">
              <i class="fas fa-search"></i>
              
            </div>
          </div>
          <ul>
            <?php
              require_once("./models/chat.class.php");
              require_once("./controler/image.controler.php");
              $image = new Image();
              $chat = new Chat();
              $users = $chat->getAllUsers($uid);
              if (is_array($users)){
                foreach($users as $user_chat){
                  if (isset($user_chat['uid']) && $user_chat['uid'] != $_SESSION['uid']){
                      if (isset($user_chat['profileimg']) && !empty($user_chat['profileimg'])){
                        $contact_image = "./uploads/" . $user_chat['uid'] . "/" . $user_chat['profileimg'];
                        $contact_image = $image->get_thumb_profile($contact_image);
                      }else{
                          $contact_image = "./images/user_female.jpg";
                          if ($user_chat['gender'] == 'male'){
                              $contact_image = "./images/user_male.jpg";
                          }
                      }
                    $contact_image = "." . substr($contact_image,strpos($contact_image,"/"),strlen($contact_image));
                    echo "<li>
                            <a href='./chat.php?uid=".$user_chat['uid']."'>
                              <img src='".$contact_image."' alt='user' id='not_image'>
                              <span>".html_entity_decode($user_chat['fname']) . ' ' . html_entity_decode($user_chat['lname'])."</span>
                            </a>
                          </li>";
                  }
                }
              }
            ?>
          </ul>
        </div>
      </div>
    </div>
    <script src="./Js/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="./Js/script.js?v=<?php echo(rand(0,9e6)); ?>"></script>
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

         $(".close_likes_list , .close_likes_list span").click(function(){
            $(".likes_list_wrapper").css("display","none");
            $(".likes_list_wrapper").attr("state" , "hidden");
        });
         
        document.getElementById('image_alt').addEventListener('click',function(){
        document.getElementById('image').click();
        });
    </script>
  </body>
</html>