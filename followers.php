<?php
// Redirect to new followers page
header("Location: ./followers_new.php");
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
    <style type="text/css">
      @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300&display=swap");

      body {
        font-family: "Poppins", sans-serif;
      }
      .container{
        display: flex;
        flex-wrap: wrap;
        flex-direction: row;
        margin-top: 90px;
        justify-content: center;
      }
      .flip-container {
        width: 280px;
        height: 380px;
        background-color: transparent;
        border: 1px solid transparent;
        border-radius: 10px;
        perspective: 1000px;
        padding: 10px;
      }

      .flip-inner-container {
        position: relative;
        width: 100%;
        height: 100%;
        text-align: center;
        transform-style: preserve-3d;
        transition: transform 0.8s;
      }

      .flip-container:hover .flip-inner-container {
        transform: rotateY(180deg);
        cursor: pointer;
      }

      .flip-front,
      .flip-back {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        border-radius: 10px;
      }
      .flip-front {
        background: white;
        padding: 2px;
      }

      .flip-front img {
        width: 100%;
        height: 100%;
        border-radius: 10px;
      }

      .flip-back {
        width: 280px;
        height: 380px;
        background: white;
        transform: rotateY(180deg);
      }

      .flip-back .profile-image img {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        border: 4px solid #2d88ff;
        margin-top: 20px;
      }

      .flip-back p {
        font-size: 13px;
        font-weight: 500;
      }

      .flip-back ul {
        display: flex;
        margin: 20px 20px;
      }

      .flip-back ul a {
        display: block;
        height: 40px;
        width: 150px;
        color: #fff;
        text-align: center;
        margin: 0 7px;
        line-height: 38px;
        border: 2px solid transparent;
        border-radius: 70px;
        background: #2d88ff;
        text-decoration: none;
      }
      .flip-back ul a:hover {
        color: #2d88ff;
        border-color: #2d88ff;
        background: linear-gradient(375deg, transparent, transparent);
      }
    </style>
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
        
        <a href="home.php" id="home_icon">
          <i class="fas fa-home"></i>
        </a>

        <a href="following.php" id="following_icon">
          <i class="fas fa-user-friends"></i>
        </a>
        
        <a href="followers.php"  class="active_icon" id="followers_icon">
          <i class="fas fa-users"></i>
        </a>
      </div>

      <div class="navbar_right">
        <div class="navbar_right_profile">
          <a href="./profile.php">
            <img src="<?php echo $image->get_thumb_profile($_USER_DATA['profileimg']) ?>" alt="profile">
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
    <div class="container">
      <?php 
        $followers = $user->getFollowers($_SESSION['uid'],'user');
        if (is_array($followers)){
          foreach($followers as $follower){
            $follower_data = $user->getData($follower['uid']);
            if (isset($follower_data['profileimg']) && !empty($follower_data['profileimg'])){
                $follower_image = $image->get_thumb_profile($follower_data['profileimg']);
            }else{
                $follower_image = "./images/user_female.jpg";
                if ($follower_data['gender'] == 'male'){
                    $follower_image = "./images/user_male.jpg";
                }
            }
            $follower_image = "." . substr($follower_image,strpos($follower_image,"/"),strlen($follower_image));

            $follower_fullname = html_entity_decode($follower_data['fname']) ." ". html_entity_decode($follower_data['lname']);
            $follower_profile = "profile.php?uid=" . $follower_data['uid'];
            $follower_about = html_entity_decode($follower_data['about']);
      ?>
      <div class="flip-container">
        <div class="flip-inner-container">
          <div class="flip-front">
            <img src="<?php echo $follower_image; ?>" />
            <h2 style="position: absolute;bottom: 0;right: 0;width: 100%;font-weight: bold;background: white;"><?php echo $follower_fullname; ?></h2>
          </div>
          <div class="flip-back">
            <div class="profile-image">
              <img src="<?php echo $follower_image ?>" />
              <h2><?php echo $follower_fullname; ?></h2>
              <p><?php echo $follower_about; ?></p>

              <ul>
                <a href="./profile.php?uid=<?php echo $follower_data['uid']; ?>"><span>Profile</span></i></a>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <?php }
        }
      ?>
    </div>
    <!-- search list container -->
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
    </script>
  </body>
</html>