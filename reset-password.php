<?php
session_start();
if (isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid'])){
    header("location: ./home.php");
    die();
}

$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
$token = isset($_GET['token']) ? htmlspecialchars($_GET['token']) : '';

if (empty($email) || empty($token)) {
    header("location: ./login.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - THODZ</title>
    <link rel="stylesheet" href="Styles/app.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="./images/logo.png">
</head>
<body>
    <div class="auth-container">
        <div class="auth-wrapper">
            <div class="auth-branding">
                <h1>THODZ</h1>
                <p>Create a new password</p>
            </div>
            
            <div class="auth-card">
                <h2 style="margin-bottom: 16px; font-size: 20px;">Set New Password</h2>
                <p style="color: var(--text-muted); margin-bottom: 20px;">Enter your new password below.</p>
                
                <form id="resetForm" class="auth-form">
                    <input type="hidden" name="email_forgot" value="<?php echo $email; ?>">
                    <input type="hidden" name="token_forgot" value="<?php echo $token; ?>">
                    <input type="password" name="password_forgot" class="auth-input" placeholder="New password" required minlength="6">
                    <input type="password" name="confirm_password" class="auth-input" placeholder="Confirm new password" required minlength="6">
                    <button type="submit" class="auth-btn">Update Password</button>
                </form>
                
                <div id="resetMessage" class="auth-message"></div>
            </div>
        </div>
    </div>

    <script src="./Js/jquery.min.js"></script>
    <script>
        document.getElementById('resetForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const messageEl = document.getElementById('resetMessage');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            // Check passwords match
            const password = form.querySelector('[name="password_forgot"]').value;
            const confirmPassword = form.querySelector('[name="confirm_password"]').value;
            
            if (password !== confirmPassword) {
                messageEl.className = 'auth-message error';
                messageEl.textContent = 'Passwords do not match.';
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';
            
            try {
                const response = await fetch('/api/ajax.php?action=newpassword', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                });
                const data = await response.json();
                
                if (data.error) {
                    messageEl.className = 'auth-message error';
                    messageEl.textContent = data.message;
                } else {
                    messageEl.className = 'auth-message success';
                    messageEl.textContent = 'Password updated successfully! Redirecting to login...';
                    setTimeout(() => {
                        window.location.href = '/login.php';
                    }, 2000);
                }
            } catch (error) {
                messageEl.className = 'auth-message error';
                messageEl.textContent = 'An error occurred. Please try again.';
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Update Password';
            }
        });
    </script>
</body>
</html>
