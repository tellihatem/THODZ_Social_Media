<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>THODZ | Update password</title>
  <link rel="stylesheet" href="./Styles/style.css?v=<?php echo(rand(0,9e6)); ?>">
  <link rel="icon" href="./images/logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
  <style type="text/css">
    .error-text{
      color: #721c24;
      padding: 8px 10px;
      text-align: center;
      border-radius: 5px;
      background: #f8d7da;
      border: 1px solid #f5c6cb;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <section class="form login">
      <header>THODZ | Update password</header>
        <div id="msg_text" class="error-text" style="display: none;"></div>
        <form action="#" id="forget_form" method="POST" enctype="multipart/form-data" autocomplete="off">
          <div class="field input">
            <label>Email Address</label>
            <input type="text" name="email_forgot" placeholder="Enter your email" required>
          </div>
          <div class="field button">
            <input type="button" id="forget_button" name="submit" value="Send Verfication code" onclick="Js/script.js">
          </div>
      </form>
    </section>
  </div>
  
  <script src="./Js/jquery.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="./Js/pass-show-hide.js"></script>
  <script src="./Js/script.js?v=<?php echo(rand(0,9e6)); ?>"></script>

</body>
</html>