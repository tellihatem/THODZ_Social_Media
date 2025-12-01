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
    <title>Sign Up - THODZ</title>
    <link rel="stylesheet" href="Styles/app.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="./images/logo.png">
    <style>
        .auth-card { max-width: 450px; }
        .name-row { display: flex; gap: 12px; }
        .name-row input { flex: 1; }
        .gender-row { display: flex; gap: 12px; margin-top: 8px; }
        .gender-option {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition-fast);
        }
        .gender-option:hover { background: var(--bg-hover); }
        .gender-option input { width: 18px; height: 18px; }
        .gender-label { font-size: 15px; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-wrapper">
            <div class="auth-branding">
                <h1>THODZ</h1>
                <p>Create a new account and connect with friends.</p>
            </div>
            
            <div class="auth-card">
                <h2 style="text-align: center; margin-bottom: 16px;">Create a new account</h2>
                <p style="text-align: center; color: var(--text-secondary); margin-bottom: 20px;">It's quick and easy.</p>
                
                <form id="signupForm" class="auth-form">
                    <div class="name-row">
                        <input type="text" name="fname" class="auth-input" placeholder="First name" required>
                        <input type="text" name="lname" class="auth-input" placeholder="Last name" required>
                    </div>
                    <input type="email" name="email" class="auth-input" placeholder="Email address" required>
                    <input type="password" name="password" class="auth-input" placeholder="New password" required minlength="6">
                    
                    <div class="gender-row">
                        <label class="gender-option">
                            <span class="gender-label">Female</span>
                            <input type="radio" name="gender" value="female" required>
                        </label>
                        <label class="gender-option">
                            <span class="gender-label">Male</span>
                            <input type="radio" name="gender" value="male">
                        </label>
                    </div>
                    
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 12px;">
                        By clicking Sign Up, you agree to our Terms, Privacy Policy and Cookies Policy.
                    </p>
                    
                    <button type="submit" class="auth-btn" style="background: var(--success); margin-top: 16px;">Sign Up</button>
                </form>
                
                <div id="signupMessage" class="auth-message"></div>
                
                <div class="auth-divider"></div>
                
                <div class="auth-link" onclick="location.href='./login.php'" style="font-size: 16px;">Already have an account?</div>
            </div>
        </div>
    </div>

    <script src="./Js/jquery.min.js"></script>
    <script>
        document.getElementById('signupForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const messageEl = document.getElementById('signupMessage');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating account...';
            
            try {
                const response = await fetch('/api/ajax.php?action=signup', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.error) {
                    messageEl.className = 'auth-message error';
                    messageEl.textContent = data.message;
                } else {
                    messageEl.className = 'auth-message success';
                    messageEl.textContent = 'Account created! Check your email for verification link.';
                    form.reset();
                }
            } catch (error) {
                messageEl.className = 'auth-message error';
                messageEl.textContent = 'An error occurred. Please try again.';
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign Up';
            }
        });
    </script>
</body>
</html>
