<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>THODZ | LogIn</title>
  <link rel="stylesheet" href="Styles/style.css?v=<?php echo(rand(0,9e6)); ?>">
  <link rel="icon" href="./images/logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>
  <div class="website_objectif">
    <b>
      <h1>
        <span style="font-size: 40px;">THODZ</span>
        <span style="font-size: 30px;color: black;">social website</span>
      </h1>
      <p style="color: #2d88ff;">“Privacy is dead, and social media holds the smoking gun.”</p>
    </b>
    <h2>Connect with friends , share posts , communicate with the world around you on <b>T</b>ec<b>h O</b>ver <b>DZ</b>.</h2>
  </div>
  <div class="wrapper">
    <section class="form login">
      <header>THODZ Social Web LogIn</header>
      <form action="#" id="login_form" method="POST" enctype="multipart/form-data" autocomplete="on">
        <div id="msg_text" class="error-text" style="display:none;"></div>
        <div class="field input">
          <label>Email Address</label>
          <input type="text" name="email" placeholder="Enter your email" required>
        </div>
        <div class="field input">
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter your password" required>
          <i class="fas fa-eye"></i>
        </div>
        <div class="field button">
          <input type="button" id="login_button" name="submit" value="Log In" onclick="./Js/script.js">
        </div>
      </form>
      <div class="link">Not yet signed up? <a href="signup.php">Signup now</a></div>
      <div class="link">Forget password? <a href="forgotpassword.php">Set new one</a></div>
    </section>
  </div>
  
  <script src="./Js/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="./Js/pass-show-hide.js"></script>
  <script src="./Js/script.js?v=<?php echo(rand(0,9e6)); ?>"></script>

</body>
</html>
