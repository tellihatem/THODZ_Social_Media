<?php
// Redirect to new chat page
$redirect = './chat_new.php';
if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
    $redirect .= '?uid=' . intval($_GET['uid']);
}
header("location: " . $redirect);
exit;

/* OLD CODE - Redirected to chat_new.php
session_start();
 if (!(isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid']))){
    header("location: ./login.php");
    die();
 }
 require_once('./models/user.class.php');
 $uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
 $user = new User();
 $_USER_DATA = $user->getData($uid);
 require_once('./controler/image.controler.php');
 $image = new Image();

 if (isset($_GET['uid']) && is_numeric($_GET['uid'])){
   $profile_data = $user->getData($_GET['uid']);
   if (is_array($profile_data)){
     $uid_chat = $profile_data['uid'];
   }else{
    header('location: ./users_chat.php');
   }
 }else{
  header('location: ./users_chat.php');
 }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>THODZ | CHAT</title>
  <link rel="stylesheet" href="./Styles/chat.css?v=<?php echo(rand(0,9e6)); ?>">
  <link rel="icon" href="./images/logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
</head>
<body>
  
  <div class="wrapper">
    <section class="chat-area">
      <header>
        <a href="users_chat.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
        <a href="./profile.php?uid=<?php echo $profile_data['uid'] ?>">
          <img src="<?php echo $image->get_thumb_profile($profile_data['profileimg']); ?>" alt="">
        </a>
        <div class="details">
          <a href="./profile.php?uid=<?php echo $profile_data['uid'] ?>">
            <span><?php echo html_entity_decode($profile_data['fname']) . " " . html_entity_decode($profile_data['lname']); ?></span>
          </a>
          <p>Active now</p>
        </div>
      </header>
      <div class="chat-box active">
        
      </div>
      <form id="chat_form" action="#" class="typing-area">
        <input type="text" class="incoming_id" name="user_id" value="<?php echo $profile_data['uid'] ?>" hidden="">
        <input type="text" name="message" id="input_chat" class="input-field" placeholder="Type a message here..." autocomplete="off">
        <button><i class="fab fa-telegram-plane"></i></button>
      </form>
    </section>
  </div>
  <script src="./Js/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="./Js/messages.js?v=<?php echo(rand(0,9e6)); ?>"></script>
</body>
</html>