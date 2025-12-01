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

// Get followers
$followers = $user->getFollowers($currentUserId, 'user');
$followersList = [];
if (is_array($followers)) {
    foreach ($followers as $follower) {
        $followerData = $user->getData($follower['uid']);
        if ($followerData) {
            $followersList[] = $followerData;
        }
    }
}

// Get who current user is following (to show follow/unfollow status)
$following = $user->getFollowing($currentUserId, 'user');
if (!is_array($following)) {
    $following = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Followers | THODZ</title>
    <link rel="stylesheet" href="Styles/app.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="./images/logo.png">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-left">
            <a href="./home_new.php">
                <img src="./images/logo.png" alt="THODZ" class="navbar-logo">
            </a>
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Search THODZ" autocomplete="off">
                <div class="search-results" id="searchResults"></div>
            </div>
        </div>

        <div class="navbar-center">
            <a href="./home_new.php" class="nav-link">
                <i class="fas fa-home"></i>
            </a>
            <a href="./following_new.php" class="nav-link">
                <i class="fas fa-user-friends"></i>
            </a>
            <a href="./followers_new.php" class="nav-link active">
                <i class="fas fa-users"></i>
            </a>
        </div>

        <div class="navbar-right">
            <a href="./profile_new.php" class="nav-profile">
                <img src="<?php echo htmlspecialchars($currentUserImg); ?>" alt="Profile">
                <span><?php echo htmlspecialchars($currentUser['fname']); ?></span>
            </a>
            <button class="nav-icon-btn dropdown-trigger">
                <i class="fas fa-caret-down"></i>
            </button>
            <div class="dropdown-menu">
                <a href="./profile_new.php" class="dropdown-header">
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
            <h1><i class="fas fa-users"></i> Followers</h1>
            <p>People who follow you</p>
        </div>

        <div class="users-grid">
            <?php if (count($followersList) > 0): ?>
                <?php foreach ($followersList as $follower): 
                    $followerImg = $images->get_thumb_profile($follower['profileimg']);
                    $isFollowingBack = in_array($follower['uid'], $following);
                ?>
                <div class="user-card">
                    <a href="./profile_new.php?uid=<?php echo $follower['uid']; ?>">
                        <img src="<?php echo htmlspecialchars($followerImg); ?>" alt="Profile" class="user-card-avatar">
                    </a>
                    <div class="user-card-info">
                        <a href="./profile_new.php?uid=<?php echo $follower['uid']; ?>">
                            <h3><?php echo htmlspecialchars($follower['fname'] . ' ' . $follower['lname']); ?></h3>
                        </a>
                        <p><?php echo $isFollowingBack ? 'You follow each other' : 'Follows you'; ?></p>
                    </div>
                    <div class="user-card-actions">
                        <?php if ($follower['uid'] != $currentUserId): ?>
                        <button class="btn <?php echo $isFollowingBack ? 'btn-success following' : 'btn-primary'; ?> like-btn" 
                                data-type="user" data-id="<?php echo $follower['uid']; ?>">
                            <i class="fas <?php echo $isFollowingBack ? 'fa-user-check' : 'fa-user-plus'; ?>"></i>
                            <span><?php echo $isFollowingBack ? 'Following' : 'Follow'; ?></span>
                        </button>
                        <?php endif; ?>
                        <a href="./chat_new.php?uid=<?php echo $follower['uid']; ?>" class="btn btn-secondary btn-icon">
                            <i class="fas fa-comment"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="user-card" style="grid-column: 1 / -1; justify-content: center; padding: 40px;">
                    <div style="text-align: center;">
                        <i class="fas fa-user-friends" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
                        <h3 style="color: var(--text-secondary);">No Followers Yet</h3>
                        <p style="color: var(--text-muted);">When people follow you, they'll appear here.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="./Js/jquery.min.js"></script>
    <script src="./Js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
