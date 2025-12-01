<?php
session_start();
if (isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid'])){
    header("location: ./home.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - THODZ</title>
    <link rel="stylesheet" href="Styles/app.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="./images/logo.png">
</head>
<body>
    <div class="auth-container">
        <div class="auth-wrapper">
            <div class="auth-branding">
                <h1>THODZ</h1>
                <p>Reset your password</p>
            </div>
            
            <div class="auth-card">
                <h2 style="margin-bottom: 16px; font-size: 20px;">Find Your Account</h2>
                <p style="color: var(--text-muted); margin-bottom: 20px;">Enter your email address and we'll send you a link to reset your password.</p>
                
                <form id="forgetForm" class="auth-form">
                    <input type="email" name="email_forgot" class="auth-input" placeholder="Email address" required>
                    <button type="submit" class="auth-btn">Send Reset Link</button>
                </form>
                
                <div id="forgetMessage" class="auth-message"></div>
                
                <div class="auth-divider"></div>
                
                <div class="auth-link" onclick="location.href='./login.php'">Back to Login</div>
            </div>
        </div>
    </div>

    <script src="./Js/jquery.min.js"></script>
    <script>
        document.getElementById('forgetForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const messageEl = document.getElementById('forgetMessage');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            
            try {
                const response = await fetch('/api/ajax.php?action=forget', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                });
                const data = await response.json();
                
                if (data.error) {
                    messageEl.className = 'auth-message error';
                    messageEl.textContent = data.message;
                } else {
                    messageEl.className = 'auth-message success';
                    messageEl.textContent = data.message || 'Reset link sent! Check your email.';
                    form.reset();
                }
            } catch (error) {
                messageEl.className = 'auth-message error';
                messageEl.textContent = 'An error occurred. Please try again.';
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Reset Link';
            }
        });
    </script>
</body>
</html>
