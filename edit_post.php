<?php
 session_start();
 if (!(isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid']))){
    header("location: ./login.php");
    die();
 }
 require_once('./models/user.class.php');
 $user = new User();
 $profile_data = $user->getData($_SESSION['uid']);
 $ERROR = "Access Denied You are not allowed to Edit this post";
 if (isset($_GET['pid']) && is_numeric($_GET['pid'])){
   require_once('./models/post.class.php');
   $pid = new Post();
   $POST = $pid->get_singlePost($_GET['pid'],$_SESSION['uid']);
   if (is_array($POST)){
     $ERROR = "Click on Save button to validate changes";
   }
 }
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
                <img src="<?php echo $image->get_thumb_profile($profile_data['profileimg']); ?>" alt="">
                <div>
                    <p><?php echo html_entity_decode($profile_data['fname']) . " " . html_entity_decode($profile_data['lname']); ?></p>
                   <a href="./profile.php?uid=<?php echo $profile_data['uid']; ?>">See your profile</a>
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
          <img class="navbar_logo" src="./images/logo.png" alt="logo">
        </a>
        <div class="input-icons">
          <i class="fas fa-search icon"></i>
          <a href="#">
            <input class="input-field" type="text" placeholder="Search on THODZ" id="searchInput">
          </a>
        </div>
      </div>

      <div class="navbar_center">
        <a href="./home.php" id="home_icon">
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
            <img src="<?php echo $image->get_thumb_profile($profile_data['profileimg']); ?>" alt="profile">
          </a>
          <span><?php echo html_entity_decode($profile_data['fname']); ?></span>
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
    <div class="content_center" style="margin-top: 46px;position: absolute;top: 50%; transform: translate(50%, -50%);">
        <div class="media_container">         
            <div class="share">
              <?php 
                echo"<p style='color: red;font-size: 24px;font-weight: bold;'>$ERROR</p>";
                if (is_array($POST)){  
                  if ($POST['owner'] == $_SESSION['uid']){
              ?>
              <form id="edit_post_form" action="#" method="POST" enctype="multipart/form-data">
                <input type="text" name="postid_edit" value="<?php echo $POST['pid'] ?>" style="display: none;">
                <div class="share_upSide">
                  <img id="myprofile_image3" src="<?php echo $image->get_thumb_profile($profile_data['profileimg']); ?>" alt="profile">
                  <textarea id="create_post_textarea" name="textarea" placeholder="What's on your mind, Gladius?"><?php echo html_entity_decode($POST['post']) ?></textarea>
                </div>
                <hr>
                <?php
                  $display = 'block';
                    if (!empty($POST['postimg']) && isset($POST['postimg']) && $POST['has_image'])
                    {
                        $post_image = "./uploads/" . $profile_data['uid'] . "/" . $POST['postimg'];
                        if($POST['is_profileimg']){
                            $post_image = $image->get_thumb_profile($image);
                        }else{
                            $post_image = $image->get_thumb_post($post_image);
                        }
                        $post_image = "." . substr($post_image,strpos($post_image,"/"),strlen($post_image));
                    }else{
                      $display = 'none';
                    } 
                ?>
                <div id="image_div" style="display: <?php echo $display;?>;width: 772px;height: 420px;">
                  <button style="position: absolute;right: 0;border: none;background: transparent;border-radius: 50%;font-weight: 900;font-size: 30px;color: red;display: block;" id="remove_image">X</button>
                  <img id="image_edit" src="<?php if($display == 'block') echo $post_image; ?>" style="display: table-row;width: inherit;height: inherit;">
                </div>
                <hr>
                <div class="share_downSide">
                  <div class="share_downSide_link">
                    <input id="image_buffer" type="file" name="image_buffer" onchange="loadFile(event)">
                    <button id="image_alt_edit" type="button">Select image</button>
                  </div>
                  <div class="share_downSide_link">
                    <input type="submit" name="edit_post" id="edit_post" value="Save">
                  </div>
                </div>
              </form>
                                                  <?php }
                                      }?>
            </div>
            <!-- search list container -->
        </div>
    </div>
    <div class="search_list_wrapper" state="hidden" style="display: none;">
        <div class="close_search_list"><span>X</span></div>
        <div class="search_list_container">
            
            <input type="text" placeholder="Person Name" id="SearchInputInside">
            <div id="userList_search">
                
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

      document.getElementById('image_alt_edit').addEventListener('click',function(){
          document.getElementById('image_buffer').click();
      });

      $("#remove_image").click(function(){
        $("#image_edit").attr("src","");
        $("#image_div").css("display","none");
        $("#image_buffer").val("");
      });

      var loadFile = function(event) {
        var image = document.getElementById('image_edit');
        image.src = URL.createObjectURL(event.target.files[0]);
        $("#image_div").css("display","block");
      };

    </script>
</body>
</html>