<?php
// Redirect to new chat page
header("location: ./chat_new.php");
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>THODZ | CHAT</title>
  <link rel="stylesheet" type="text/css" href="./Styles/chat.css?v=<?php echo(rand(0,9e6)); ?>">
  <link rel="icon" href="./images/logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
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
            <img src="<?php echo $image->get_thumb_profile($_USER_DATA['profileimg']); ?>" alt="profile">
          </a>
          <span><?php echo html_entity_decode($_USER_DATA['fname']); ?></span>
        </div>
        <div class="navbar_right_links">
          <a href="#">
            <i class="fab fa-facebook-messenger" style="color: #2d88ff;background: #b8bbbf;"></i>
          </a>
          <a href="#" onclick="SettingMenuToggle()">
            <i class="fas fa-arrow-down"></i>
          </a>
        </div>
      </div>
    </div>
  <div class="wrapper">
    <section class="users">
      <header>
        <div class="content">
                    <img src="<?php echo $image->get_thumb_profile($_USER_DATA['profileimg']); ?>" alt="image">
          <div class="details">
            <span><?php echo html_entity_decode($_USER_DATA['fname']) . " " . html_entity_decode($_USER_DATA['lname']);?></span>
            <p>Active now</p>
          </div>
        </div>
        <a href="./logout.php" class="logout">Logout</a>
      </header>
      <div class="search">
        <span class="text">Select an user to start chat</span>
        <input type="text" placeholder="Enter name to search...">
        <button><i class="fas fa-search"></i></button>
      </div>
      <div class="users-list">
        
      </div>
    </section>
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
  <script src="./Js/chat.js?v=<?php echo(rand(0,9e6)); ?>"></script>
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