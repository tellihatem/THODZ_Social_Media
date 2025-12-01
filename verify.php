<?php
require_once('./models/database.class.php');

$message = '';
$success = false;

if (!isset($_GET['email']) || !isset($_GET['token'])) {
    $message = 'Invalid verification link.';
} else {
    $_link = (new Database())->connect();
    $email = htmlspecialchars(trim($_GET['email']));
    $token = htmlspecialchars(trim($_GET['token']));
    
    // Check if token is valid and not expired (24 hours)
    $stmt = $_link->prepare('SELECT uid, email, fname, token, created_at FROM users WHERE email = ? AND token = ? AND isemailconfirmed = 0');
    $stmt->bindParam(1, $email, PDO::PARAM_STR);
    $stmt->bindParam(2, $token, PDO::PARAM_STR);
    
    if (!$stmt->execute()) {
        $message = 'An error occurred. Please try again.';
    } else if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify the user
        $updateStmt = $_link->prepare('UPDATE users SET isemailconfirmed = 1, token = '' WHERE email = ?');
        $updateStmt->bindParam(1, $email, PDO::PARAM_STR);
        
        if ($updateStmt->execute()) {
            $success = true;
            $message = 'Your email has been verified successfully!';
        } else {
            $message = 'Failed to verify email. Please try again.';
        }
    } else {
        // Check if already verified
        $checkStmt = $_link->prepare('SELECT isemailconfirmed FROM users WHERE email = ?');
        $checkStmt->bindParam(1, $email, PDO::PARAM_STR);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if ($result['isemailconfirmed'] == 1) {
                $success = true;
                $message = 'Your email is already verified!';
            } else {
                $message = 'Invalid or expired verification link.';
            }
        } else {
            $message = 'User not found.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification | THODZ</title>
    <link rel="stylesheet" href="Styles/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="./images/logo.png">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f2f5 0%, #e4e6e9 100%);
        }
        .verify-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            padding: 48px;
            text-align: center;
            max-width: 450px;
            width: 90%;
        }
        .verify-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 36px;
        }
        .verify-icon.success {
            background: linear-gradient(135deg, #42b72a 0%, #36a420 100%);
            color: white;
        }
        .verify-icon.error {
            background: linear-gradient(135deg, #f02849 0%, #d41c3c 100%);
            color: white;
        }
        .verify-title {
            font-size: 24px;
            font-weight: 700;
            color: #1c1e21;
            margin-bottom: 12px;
        }
        .verify-message {
            font-size: 16px;
            color: #65676b;
            margin-bottom: 32px;
            line-height: 1.5;
        }
        .verify-btn {
            display: inline-block;
            padding: 12px 32px;
            background: #1877f2;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: background 0.2s;
        }
        .verify-btn:hover {
            background: #166fe5;
        }
        .verify-logo {
            font-size: 28px;
            font-weight: 700;
            color: #1877f2;
            margin-bottom: 32px;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-logo">THODZ</div>
        
        <div class="verify-icon <?php echo $success ? 'success' : 'error'; ?>">
            <i class="fas <?php echo $success ? 'fa-check' : 'fa-times'; ?>"></i>
        </div>
        
        <h1 class="verify-title">
            <?php echo $success ? 'Email Verified!' : 'Verification Failed'; ?>
        </h1>
        
        <p class="verify-message">
            <?php echo htmlspecialchars($message); ?>
        </p>
        
        <?php if ($success): ?>
            <a href="./login.php" class="verify-btn">
                <i class="fas fa-sign-in-alt"></i> Login Now
            </a>
        <?php else: ?>
            <a href="./index.php" class="verify-btn">
                <i class="fas fa-home"></i> Go to Homepage
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
