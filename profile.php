<?php
session_start();
if (!(isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid']))){
    header("location: ./login.php");
    die();
}

require_once('./models/user.class.php');
require_once('./models/post.class.php');
require_once('./controler/image.controler.php');

$user = new User();
$post = new Post();
$images = new Image();

$currentUserId = $_SESSION['uid'];
$currentUser = $user->getData($currentUserId);

// Get profile user (either current user or viewed profile)
$profileUserId = isset($_GET['uid']) && is_numeric($_GET['uid']) ? intval($_GET['uid']) : $currentUserId;
$profileUser = $user->getData($profileUserId);

if (!$profileUser) {
    header('location: ./profile.php');
    die();
}

$isOwnProfile = ($profileUserId == $currentUserId);

// Get posts
$posts = $post->getPosts($profileUserId);

// Get follow status
$following = $user->getFollowing($currentUserId, "user");
$isFollowing = is_array($following) && in_array($profileUserId, $following);

// Get stats
$followerCount = $profileUser['likes'] ?? 0;
$followingCount = $user->getFollowingNumber($profileUserId, 'user');

// Get profile image
$profileImg = $images->get_thumb_profile($profileUser['profileimg']);
$currentUserImg = $images->get_thumb_profile($currentUser['profileimg']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profileUser['fname'] . ' ' . $profileUser['lname']); ?> | THODZ</title>
    <link rel="stylesheet" href="Styles/app.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
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
            <a href="./following.php" class="nav-link">
                <i class="fas fa-user-friends"></i>
            </a>
            <a href="./chat.php" class="nav-link">
                <i class="fas fa-comments"></i>
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
                <a href="./profile.php?tab=settings" class="dropdown-item">
                    <div class="dropdown-item-icon"><i class="fas fa-cog"></i></div>
                    <span>Settings</span>
                </a>
                <a href="./logout.php" class="dropdown-item">
                    <div class="dropdown-item-icon"><i class="fas fa-sign-out-alt"></i></div>
                    <span>Log Out</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Profile Container -->
    <div class="profile-container">
        <!-- Cover & Header -->
        <div class="profile-header">
            <div class="profile-cover">
                <?php 
                $coverImg = '';
                if (!empty($profileUser['coverimg'])) {
                    $coverImg = "./uploads/" . $profileUserId . "/" . $profileUser['coverimg'];
                }
                if ($coverImg && file_exists($coverImg)): ?>
                <img src="<?php echo htmlspecialchars($coverImg); ?>" alt="Cover">
                <?php endif; ?>
                <?php if ($isOwnProfile): ?>
                <label class="cover-edit-btn" for="coverImageInput">
                    <i class="fas fa-camera"></i> Edit Cover Photo
                </label>
                <input type="file" id="coverImageInput" accept="image/*" style="display: none;">
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <div class="profile-avatar-wrapper">
                    <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile" class="profile-avatar">
                    <?php if ($isOwnProfile): ?>
                    <label class="profile-avatar-edit" for="profileImageInput">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="profileImageInput" accept="image/*" style="display: none;">
                    <?php endif; ?>
                </div>
                <div class="profile-details">
                    <h1 class="profile-name"><?php echo htmlspecialchars($profileUser['fname'] . ' ' . $profileUser['lname']); ?></h1>
                    <div class="profile-stats">
                        <span class="profile-stat"><strong id="followerCount"><?php echo $followerCount; ?></strong> followers</span>
                        <span>·</span>
                        <span class="profile-stat"><strong><?php echo $followingCount; ?></strong> following</span>
                    </div>
                </div>
                <div class="profile-actions">
                    <?php if ($isOwnProfile): ?>
                        <button class="btn btn-secondary" data-modal="createPost">
                            <i class="fas fa-plus"></i> Add Post
                        </button>
                        <a href="?tab=settings" class="btn btn-secondary">
                            <i class="fas fa-pen"></i> Edit Profile
                        </a>
                    <?php else: ?>
                        <button class="btn <?php echo $isFollowing ? 'btn-success following' : 'btn-primary'; ?> like-btn" 
                                data-type="user" data-id="<?php echo $profileUserId; ?>">
                            <i class="fas <?php echo $isFollowing ? 'fa-user-check' : 'fa-user-plus'; ?>"></i>
                            <span><?php echo $isFollowing ? 'Following' : 'Follow'; ?></span>
                        </button>
                        <a href="./chat.php?uid=<?php echo $profileUserId; ?>" class="btn btn-secondary">
                            <i class="fas fa-comment"></i> Message
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Profile Navigation -->
        <div class="profile-nav">
            <a href="?uid=<?php echo $profileUserId; ?>" class="profile-nav-link <?php echo !isset($_GET['tab']) ? 'active' : ''; ?>">Posts</a>
            <a href="?uid=<?php echo $profileUserId; ?>&tab=about" class="profile-nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'about') ? 'active' : ''; ?>">About</a>
            <?php if ($isOwnProfile): ?>
            <a href="?tab=settings" class="profile-nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'settings') ? 'active' : ''; ?>">Settings</a>
            <?php endif; ?>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-card">
                    <h3 class="profile-card-title">About</h3>
                    <?php if (!empty($profileUser['about'])): ?>
                    <div class="profile-about-item">
                        <i class="fas fa-info-circle"></i>
                        <span><?php echo htmlspecialchars($profileUser['about']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="profile-about-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo htmlspecialchars($profileUser['email']); ?></span>
                    </div>
                    <div class="profile-about-item">
                        <i class="fas fa-user"></i>
                        <span><?php echo ucfirst($profileUser['gender'] ?? 'Not specified'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="profile-main">
                <?php if (isset($_GET['tab']) && $_GET['tab'] == 'settings' && $isOwnProfile): ?>
                <!-- Settings Tab -->
                <div class="profile-card">
                    <h3 class="profile-card-title">Edit Profile</h3>
                    <form id="settingsForm" class="settings-form">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="fname" class="auth-input" value="<?php echo htmlspecialchars($currentUser['fname']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="lname" class="auth-input" value="<?php echo htmlspecialchars($currentUser['lname']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="auth-input" value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>About</label>
                            <textarea name="about" class="auth-input" rows="3"><?php echo htmlspecialchars($currentUser['about'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>New Password (leave blank to keep current)</label>
                            <input type="password" name="password" class="auth-input">
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="rpassword" class="auth-input">
                        </div>
                        <div class="form-group">
                            <label>Current Password (required to save changes)</label>
                            <input type="password" name="currentpassword" class="auth-input" required>
                        </div>
                        <div id="settingsMessage" class="auth-message"></div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 16px;">
                            Save Changes
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <!-- Posts Tab -->
                <?php if ($isOwnProfile): ?>
                <!-- Create Post Card -->
                <div class="card create-post">
                    <div class="create-post-header" data-modal="createPost" style="cursor: pointer;">
                        <img src="<?php echo htmlspecialchars($currentUserImg); ?>" alt="Profile">
                        <div class="create-post-input">What's on your mind, <?php echo htmlspecialchars($currentUser['fname']); ?>?</div>
                    </div>
                    <div class="create-post-divider"></div>
                    <div class="create-post-actions">
                        <button class="create-post-btn photo" data-modal="createPost">
                            <i class="fas fa-images"></i> Photo
                        </button>
                        <button class="create-post-btn feeling" data-modal="createPost">
                            <i class="fas fa-smile"></i> Feeling
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Posts Section -->
                <div id="postSection">
                    <?php if ($posts && count($posts) > 0): ?>
                        <?php foreach ($posts as $POST): 
                            $postUser = $user->getData($POST['owner']);
                            $postUserImg = $images->get_thumb_profile($postUser['profileimg']);
                            $postImage = '';
                            if (!empty($POST['postimg'])) {
                                $postImage = "./uploads/" . $POST['owner'] . "/" . $POST['postimg'];
                            }
                            $isLiked = false; // TODO: Check if current user liked this post
                            $isOwner = ($POST['owner'] == $currentUserId);
                        ?>
                        <div class="card post" data-post-id="<?php echo $POST['pid']; ?>">
                            <div class="post-header">
                                <a href="./profile.php?uid=<?php echo $POST['owner']; ?>" class="post-author">
                                    <img src="<?php echo htmlspecialchars($postUserImg); ?>" alt="Profile">
                                    <div class="post-author-info">
                                        <h4><?php echo htmlspecialchars($postUser['fname'] . ' ' . $postUser['lname']); ?></h4>
                                        <span><?php echo date('M j, Y', strtotime($POST['date'] ?? 'now')); ?></span>
                                    </div>
                                </a>
                                <?php if ($isOwner): ?>
                                <div style="position: relative;">
                                    <button class="post-menu-btn">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <div class="post-menu">
                                        <button class="post-menu-item edit-post-btn" 
                                                data-post-id="<?php echo $POST['pid']; ?>"
                                                data-post-text="<?php echo htmlspecialchars($POST['post'] ?? ''); ?>">
                                            <i class="fas fa-edit"></i> Edit Post
                                        </button>
                                        <button class="post-menu-item danger delete-post-btn" 
                                                data-post-id="<?php echo $POST['pid']; ?>">
                                            <i class="fas fa-trash"></i> Delete Post
                                        </button>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($POST['post'])): ?>
                            <div class="post-content">
                                <p class="post-text"><?php echo html_entity_decode($POST['post']); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($postImage) && file_exists($postImage)): ?>
                            <img src="<?php echo htmlspecialchars($postImage); ?>" alt="Post image" class="post-image">
                            <?php endif; ?>

                            <div class="post-stats">
                                <div class="post-likes show-likes-btn" id="likesWrapper_<?php echo $POST['pid']; ?>" data-post-id="<?php echo $POST['pid']; ?>" style="<?php echo $POST['likes'] > 0 ? '' : 'display:none;'; ?>">
                                    <div class="post-likes-icon"><i class="fas fa-thumbs-up"></i></div>
                                    <span id="likesCount_<?php echo $POST['pid']; ?>"><?php echo $POST['likes']; ?></span>
                                </div>
                                <div class="post-stats-right">
                                    <span class="post-likes-count show-likes-btn" id="likesText_<?php echo $POST['pid']; ?>" data-post-id="<?php echo $POST['pid']; ?>">
                                        <?php echo $POST['likes']; ?> likes
                                    </span>
                                    <span class="post-comments-count comment-toggle-btn" id="commentCount_<?php echo $POST['pid']; ?>" data-post-id="<?php echo $POST['pid']; ?>">
                                        <?php echo $POST['comments']; ?> comments
                                    </span>
                                </div>
                            </div>

                            <div class="post-actions-divider"></div>
                            <div class="post-actions">
                                <button class="post-action-btn like-btn <?php echo $isLiked ? 'liked' : ''; ?>" 
                                        data-type="post" data-id="<?php echo $POST['pid']; ?>">
                                    <i class="<?php echo $isLiked ? 'fas' : 'far'; ?> fa-thumbs-up"></i>
                                    <span>Like</span>
                                </button>
                                <button class="post-action-btn comment-toggle-btn" data-post-id="<?php echo $POST['pid']; ?>">
                                    <i class="far fa-comment"></i>
                                    <span>Comment</span>
                                </button>
                            </div>

                            <!-- Comments Section -->
                            <div class="post-comments" id="comments_<?php echo $POST['pid']; ?>">
                                <?php 
                                $comments = $post->getComments($POST['pid']);
                                if ($comments):
                                    foreach ($comments as $comment):
                                        $commentUser = $user->getData($comment['owner']);
                                        $commentUserImg = $images->get_thumb_profile($commentUser['profileimg']);
                                        $commentImage = '';
                                        if (!empty($comment['postimg'])) {
                                            $commentImage = "./uploads/" . $comment['owner'] . "/" . $comment['postimg'];
                                        }
                                ?>
                                <div class="comment" id="comment_<?php echo $comment['pid']; ?>">
                                    <a href="./profile.php?uid=<?php echo $comment['owner']; ?>">
                                        <img src="<?php echo htmlspecialchars($commentUserImg); ?>" alt="Profile" class="comment-avatar">
                                    </a>
                                    <div class="comment-content">
                                        <div class="comment-bubble">
                                            <a href="./profile.php?uid=<?php echo $comment['owner']; ?>" class="comment-author">
                                                <?php echo htmlspecialchars($commentUser['fname'] . ' ' . $commentUser['lname']); ?>
                                            </a>
                                            <p class="comment-text"><?php echo html_entity_decode($comment['post']); ?></p>
                                        </div>
                                        <?php if (!empty($commentImage) && file_exists($commentImage)): ?>
                                        <img src="<?php echo htmlspecialchars($commentImage); ?>" alt="Comment image" class="comment-image">
                                        <?php endif; ?>
                                        <div class="comment-actions">
                                            <span class="comment-action like-btn" data-type="comment" data-id="<?php echo $comment['pid']; ?>">Like</span>
                                            <?php if ($comment['owner'] == $currentUserId): ?>
                                            <span class="comment-action" onclick="deleteComment(<?php echo $comment['pid']; ?>)">Delete</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    endforeach;
                                endif; 
                                ?>
                            </div>

                            <!-- Comment Input (hidden by default) -->
                            <form class="comment-input-wrapper" data-post-id="<?php echo $POST['pid']; ?>" style="display: none;">
                                <img src="<?php echo htmlspecialchars($currentUserImg); ?>" alt="Profile">
                                <input type="hidden" name="postid" value="<?php echo $POST['pid']; ?>">
                                <div class="comment-input-container">
                                    <textarea class="comment-input" name="commentText" placeholder="Write a comment..." rows="1"></textarea>
                                    <div class="comment-input-actions">
                                        <div class="comment-input-icons">
                                            <label class="comment-input-icon" for="commentImage_<?php echo $POST['pid']; ?>">
                                                <i class="fas fa-camera"></i>
                                            </label>
                                            <input type="file" name="commentImage" id="commentImage_<?php echo $POST['pid']; ?>" accept="image/*" style="display: none;">
                                        </div>
                                        <span class="comment-submit">Post</span>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="card" style="padding: 40px; text-align: center;">
                            <i class="fas fa-camera" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
                            <h3 style="color: var(--text-secondary);">No Posts Yet</h3>
                            <p style="color: var(--text-muted);">
                                <?php echo $isOwnProfile ? 'Share your first post!' : 'This user hasn\'t posted anything yet.'; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create Post Modal -->
    <div class="modal-overlay" id="createPostModal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Create Post</h2>
                <button class="modal-close"><i class="fas fa-times"></i></button>
            </div>
            <form id="createPostForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="modal-author">
                        <img src="<?php echo htmlspecialchars($currentUserImg); ?>" alt="Profile">
                        <h4><?php echo htmlspecialchars($currentUser['fname'] . ' ' . $currentUser['lname']); ?></h4>
                    </div>
                    <textarea class="modal-textarea" name="textarea" placeholder="What's on your mind, <?php echo htmlspecialchars($currentUser['fname']); ?>?"></textarea>
                    <div class="image-preview-container">
                        <img src="" alt="Preview" class="image-preview" id="postImagePreview">
                        <button type="button" class="image-preview-remove"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-add-content">
                        <span>Add to your post</span>
                        <div class="modal-add-icons">
                            <label class="modal-add-icon photo" for="postImageInput">
                                <i class="fas fa-images"></i>
                            </label>
                            <input type="file" name="image" id="postImageInput" accept="image/*,.pdf,application/pdf" data-preview="postImagePreview" style="display: none;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="modal-submit">Post</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Post Modal -->
    <div class="modal-overlay" id="editPostModal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Edit Post</h2>
                <button class="modal-close"><i class="fas fa-times"></i></button>
            </div>
            <form id="editPostForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="postid_edit" id="editPostId">
                    <input type="hidden" name="remove_image" id="editRemoveImage" value="0">
                    <textarea class="modal-textarea" name="textarea" id="editPostText" placeholder="What's on your mind?"></textarea>
                    <div class="image-preview-container" id="editImagePreviewContainer" style="display: none;">
                        <img src="" alt="Preview" class="image-preview" id="editPostImagePreview">
                        <button type="button" class="image-preview-remove" id="editImageRemove" title="Remove image"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-add-content">
                        <span>Add/Change image</span>
                        <div class="modal-add-icons">
                            <label class="modal-add-icon photo" for="editPostImageInput">
                                <i class="fas fa-images"></i>
                            </label>
                            <input type="file" name="image_buffer" id="editPostImageInput" accept="image/*" style="display: none;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="modal-submit">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div class="modal-overlay" id="confirmModal">
        <div class="modal" style="max-width: 400px;">
            <div class="confirm-dialog">
                <h3>Delete Post?</h3>
                <p>Are you sure you want to delete this post? This action cannot be undone.</p>
                <div class="confirm-dialog-actions">
                    <button class="btn btn-secondary modal-cancel">Cancel</button>
                    <button class="btn btn-primary confirm-delete-btn" style="background: var(--danger);">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Likes Modal -->
    <div class="modal-overlay" id="likesModal">
        <div class="modal" style="max-width: 400px;">
            <div class="modal-header">
                <h2 class="modal-title">People who liked this</h2>
                <button class="modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                <div id="likesListContainer" class="likes-list">
                    <div class="likes-loading">Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cover Photo Crop Modal -->
    <div class="modal-overlay" id="coverCropModal">
        <div class="modal cover-crop-modal">
            <div class="modal-header">
                <h2 class="modal-title">Edit Cover Photo</h2>
                <button class="modal-close" onclick="closeCoverCropModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body cover-crop-body">
                <div class="cover-crop-container">
                    <img id="coverCropImage" src="" alt="Cover">
                </div>
                <div class="cover-crop-instructions">
                    <i class="fas fa-arrows-alt"></i>
                    <span>Drag to reposition your cover photo</span>
                </div>
            </div>
            <div class="modal-footer cover-crop-footer">
                <button class="btn btn-secondary" onclick="closeCoverCropModal()">Cancel</button>
                <button class="btn btn-primary" id="saveCoverBtn" onclick="saveCroppedCover()">
                    <i class="fas fa-check"></i> Save
                </button>
            </div>
        </div>
    </div>

    <script src="./Js/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script src="./Js/app.js?v=<?php echo time(); ?>"></script>
    <script>
        // Settings form handler
        document.getElementById('settingsForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const messageEl = document.getElementById('settingsMessage');
            
            try {
                const response = await fetch('/api/ajax.php?action=settings', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.error) {
                    messageEl.className = 'auth-message error';
                    messageEl.textContent = data.message;
                } else {
                    messageEl.className = 'auth-message success';
                    messageEl.textContent = 'Profile updated successfully!';
                    setTimeout(() => location.reload(), 1500);
                }
            } catch (error) {
                messageEl.className = 'auth-message error';
                messageEl.textContent = 'An error occurred. Please try again.';
            }
        });

        // Profile image upload
        document.getElementById('profileImageInput')?.addEventListener('change', async function(e) {
            if (this.files && this.files[0]) {
                const formData = new FormData();
                formData.append('upload_image', this.files[0]);
                
                try {
                    const response = await fetch('/api/ajax.php?action=profileimg', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    
                    if (data.updateimg) {
                        location.reload();
                    }
                } catch (error) {
                    alert('Failed to upload image');
                }
            }
        });

        // Cover image cropper
        let coverCropper = null;
        let coverImageFile = null;

        // Cover image upload - open crop modal
        document.getElementById('coverImageInput')?.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                coverImageFile = this.files[0];
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(coverImageFile.type)) {
                    alert('Please select a valid image file (JPEG, PNG, GIF, or WebP)');
                    this.value = '';
                    return;
                }
                
                // Validate file size (10MB max for cropping)
                if (coverImageFile.size > 10 * 1024 * 1024) {
                    alert('Image is too large. Maximum size is 10MB.');
                    this.value = '';
                    return;
                }
                
                // Read and display image in crop modal
                const reader = new FileReader();
                reader.onload = function(event) {
                    const cropImage = document.getElementById('coverCropImage');
                    cropImage.src = event.target.result;
                    
                    // Open modal
                    document.getElementById('coverCropModal').classList.add('active');
                    
                    // Initialize cropper after image loads
                    cropImage.onload = function() {
                        if (coverCropper) {
                            coverCropper.destroy();
                        }
                        
                        coverCropper = new Cropper(cropImage, {
                            aspectRatio: 820 / 312, // Facebook cover ratio
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 1,
                            cropBoxMovable: false,
                            cropBoxResizable: false,
                            toggleDragModeOnDblclick: false,
                            minContainerWidth: 300,
                            minContainerHeight: 200,
                            background: true,
                            responsive: true,
                            guides: false,
                            center: false,
                            highlight: false,
                            cropBoxResizable: false,
                            zoomOnWheel: true,
                            zoomOnTouch: true
                        });
                    };
                };
                reader.readAsDataURL(coverImageFile);
            }
        });

        function closeCoverCropModal() {
            document.getElementById('coverCropModal').classList.remove('active');
            if (coverCropper) {
                coverCropper.destroy();
                coverCropper = null;
            }
            document.getElementById('coverImageInput').value = '';
            coverImageFile = null;
        }

        async function saveCroppedCover() {
            if (!coverCropper) return;
            
            const saveBtn = document.getElementById('saveCoverBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            try {
                // Get cropped canvas
                const canvas = coverCropper.getCroppedCanvas({
                    width: 820,
                    height: 312,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });
                
                // Convert to blob
                canvas.toBlob(async function(blob) {
                    const formData = new FormData();
                    formData.append('cover_image', blob, 'cover.jpg');
                    
                    try {
                        const response = await fetch('/api/ajax.php?action=coverimg', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await response.json();
                        
                        if (data.success) {
                            closeCoverCropModal();
                            location.reload();
                        } else {
                            alert(data.message || 'Failed to upload cover image');
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="fas fa-check"></i> Save';
                        }
                    } catch (error) {
                        alert('Failed to upload cover image');
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fas fa-check"></i> Save';
                    }
                }, 'image/jpeg', 0.9);
            } catch (error) {
                alert('Failed to process image');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-check"></i> Save';
            }
        }

        // Close modal on overlay click
        document.getElementById('coverCropModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeCoverCropModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('coverCropModal').classList.contains('active')) {
                closeCoverCropModal();
            }
        });
    </script>
</body>
</html>
