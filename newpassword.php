<?php 
  if (isset($_GET['email']) && isset($_GET['token'])){
   $email = htmlentities(trim($_GET['email']));
   $token = htmlentities(trim($_GET['token']));
  }else{
    header('location: forgotpassword.php');
    die;
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>THODZ | Update password</title>
  <link rel="stylesheet" href="Styles/style.css">
  <link rel="icon" href="./images/logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>
  <div class="wrapper">
    <section class="form login">
      <header>THODZ | Update password</header>
      <form action="#" id="newpassword_form" method="POST" enctype="multipart/form-data" autocomplete="off">
        <div id="msg_text" class="error-text" style="display: none;"></div>
        <div class="field input">
          <label>New password</label>
          <input type="password" name="password_forgot" placeholder="Enter your new password" required>
          <input type="text" name="email_forgot" value="<?php echo $email; ?>" hidden="">
          <input type="text" name="token_forgot" value="<?php echo $token; ?>" hidden="">
          <i class="fas fa-eye"></i>
        </div>
        <div class="field button">
          <input type="button" id="newpassword_button" name="submit" value="Update Password" onclick="Js/script.js">
        </div>
      </form>
    </section>
  </div>
  <script src="./Js/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="./Js/pass-show-hide.js"></script>
  <script src="./Js/script.js"></script>

</body>
</html>