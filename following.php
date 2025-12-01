<?php
session_start();
if (!(isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid']))){
    header("location: ./login.php");
    die();
}

require_once('./models/user.class.php');
require_once('./controler/image.controler.php');

$user = new User();
$images = new Image();

$currentUserId = $_SESSION['uid'];
$currentUser = $user->getData($currentUserId);
$currentUserImg = $images->get_thumb_profile($currentUser['profileimg']);

// Get who current user is following
$following = $user->getFollowing($currentUserId, 'user');
$followingList = [];
if (is_array($following)) {
    foreach ($following as $followingId) {
        $followingData = $user->getData($followingId);
        if ($followingData) {
            $followingList[] = $followingData;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Following | THODZ</title>
    <link rel="stylesheet" href="Styles/app.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="./images/logo.png">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-left">
            <a href="./home.php">
                <img src="./images/logo.png" alt="THODZ" class="navbar-logo">
            </a>
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Search THODZ" autocomplete="off">
                <div class="search-results" id="searchResults"></div>
            </div>
        </div>

        <div class="navbar-center">
            <a href="./home.php" class="nav-link">
                <i class="fas fa-home"></i>
            </a>
            <a href="./following.php" class="nav-link active">
                <i class="fas fa-user-friends"></i>
            </a>
            <a href="./followers.php" class="nav-link">
                <i class="fas fa-users"></i>
            </a>
        </div>

        <div class="navbar-right">
            <a href="./profile.php" class="nav-profile">
                <img src="<?php echo htmlspecialchars($currentUserImg); ?>" alt="Profile">
                <span><?php echo htmlspecialchars($currentUser['fname']); ?></span>
            </a>
            <button class="nav-icon-btn dropdown-trigger">
                <i class="fas fa-caret-down"></i>
            </button>
            <div class="dropdown-menu">
                <a href="./profile.php" class="dropdown-header">
                    <img src="<?php echo htmlspecialchars($currentUserImg); ?>" alt="Profile">
                    <div class="dropdown-header-info">
                        <h4><?php echo htmlspecialchars($currentUser['fname'] . ' ' . $currentUser['lname']); ?></h4>
                        <span>See your profile</span>
                    </div>
                </a>
                <div class="dropdown-divider"></div>
                <a href="./logout.php" class="dropdown-item">
                    <div class="dropdown-item-icon"><i class="fas fa-sign-out-alt"></i></div>
                    <span>Log Out</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="users-container">
        <div class="users-header">
            <h1><i class="fas fa-user-friends"></i> Following</h1>
            <p>People you follow</p>
        </div>

        <div class="users-grid">
            <?php if (count($followingList) > 0): ?>
                <?php foreach ($followingList as $followingUser): 
                    $followingImg = $images->get_thumb_profile($followingUser['profileimg']);
                ?>
                <div class="user-card">
                    <a href="./profile.php?uid=<?php echo $followingUser['uid']; ?>">
                        <img src="<?php echo htmlspecialchars($followingImg); ?>" alt="Profile" class="user-card-avatar">
                    </a>
                    <div class="user-card-info">
                        <a href="./profile.php?uid=<?php echo $followingUser['uid']; ?>">
                            <h3><?php echo htmlspecialchars($followingUser['fname'] . ' ' . $followingUser['lname']); ?></h3>
                        </a>
                        <p><?php echo $followingUser['likes']; ?> followers</p>
                    </div>
                    <div class="user-card-actions">
                        <button class="btn btn-success following like-btn" 
                                data-type="user" data-id="<?php echo $followingUser['uid']; ?>">
                            <i class="fas fa-user-check"></i>
                            <span>Following</span>
                        </button>
                        <a href="./chat.php?uid=<?php echo $followingUser['uid']; ?>" class="btn btn-secondary btn-icon">
                            <i class="fas fa-comment"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="user-card" style="grid-column: 1 / -1; justify-content: center; padding: 40px;">
                    <div style="text-align: center;">
                        <i class="fas fa-user-plus" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
                        <h3 style="color: var(--text-secondary);">Not Following Anyone</h3>
                        <p style="color: var(--text-muted);">Find people to follow and their posts will appear in your feed.</p>
                        <a href="./home.php" class="btn btn-primary" style="margin-top: 16px;">Discover People</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="./Js/jquery.min.js"></script>
    <script src="./Js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
