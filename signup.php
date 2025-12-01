<?php 

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>THODZ | SignUp</title>
  <link rel="stylesheet" href="Styles/style.css?v=<?php echo(rand(0,9e6)); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
  <link rel="icon" href="./images/logo.png">
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
    <section class="form signup">
      <header>THODZ Social Web SignUp</header>
      <form action="#" id="signup_form" method="POST" enctype="multipart/form-data" autocomplete="on">
        <div id="msg_text" class="error-text" style="display: none;"></div>
        <div class="name-details">
          <div class="field input">
            <label>First Name</label>
            <input type="text" name="fname" placeholder="First name" required>
          </div>
          <div class="field input">
            <label>Last Name</label>
            <input type="text" name="lname" placeholder="Last name" required>
          </div>
        </div>
        <div class="field input">
          <label>Email Address</label>
          <input type="text" name="email" placeholder="Enter your email" required>
        </div>
        <div class="field input">
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter new password" required>
          <i class="fas fa-eye"></i>
        </div>
        <div class="field">
          <label>Gender</label>
          <select name="gender">
            <option value="male">Male</option>
            <option value="female">Female</option>
          </select>
        </div>
        <!-- <div class="field image">
          <label>Select Image</label>
          <input type="file" name="image" accept="image/x-png,image/gif,image/jpeg,image/jpg" required>
        </div> -->
        <div class="field button">
          <input type="submit" id="signup_button" name="submit" value="Sign Up">
        </div>
      </form>
      <div class="link">Already signed up? <a href="index.php">Login now</a></div>
    </section>
  </div>

  <script src="./Js/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="./Js/pass-show-hide.js"></script>
  <script src="./Js/script.js?v=<?php echo(rand(0,9e6)); ?>"></script>

</body>
</html>