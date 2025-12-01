<?php
session_start();
if (isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid'])){
    header("location: ./home_new.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THODZ - Log In or Sign Up</title>
    <link rel="stylesheet" href="Styles/app.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="./images/logo.png">
</head>
<body>
    <div class="auth-container">
        <div class="auth-wrapper">
            <div class="auth-branding">
                <h1>THODZ</h1>
                <p>Connect with friends and the world around you on THODZ.</p>
            </div>
            
            <div class="auth-card">
                <form id="loginForm" class="auth-form">
                    <input type="email" name="email" class="auth-input" placeholder="Email address" required>
                    <input type="password" name="password" class="auth-input" placeholder="Password" required>
                    <button type="submit" class="auth-btn">Log In</button>
                </form>
                
                <div id="loginMessage" class="auth-message"></div>
                
                <div class="auth-link" onclick="location.href='./forget.php'">Forgotten password?</div>
                
                <div class="auth-divider"></div>
                
                <button class="auth-signup-btn" onclick="location.href='./signup_new.php'">Create New Account</button>
            </div>
        </div>
    </div>

    <script src="./Js/jquery.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const messageEl = document.getElementById('loginMessage');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging in...';
            
            try {
                const response = await fetch('/api/ajax.php?action=login', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                });
                const data = await response.json();
                
                if (data.error) {
                    messageEl.className = 'auth-message error';
                    messageEl.textContent = data.message;
                } else {
                    messageEl.className = 'auth-message success';
                    messageEl.textContent = 'Login successful! Redirecting...';
                    setTimeout(() => {
                        window.location.href = '/home_new.php';
                    }, 500);
                }
            } catch (error) {
                messageEl.className = 'auth-message error';
                messageEl.textContent = 'An error occurred. Please try again.';
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Log In';
            }
        });
    </script>
</body>
</html>
