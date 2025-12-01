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
$currentUserImg = $images->get_thumb_profile($currentUser['profileimg']);

// Get following list for home feed
$following = $user->getFollowing($currentUserId, "user");
if (!is_array($following)) {
    $following = [];
}
$following[] = $currentUserId; // Include own posts

// Get posts from followed users
$posts = $post->getHomePost($following, $currentUserId);

// Get suggested users
$suggestedUsers = $user->getRandomUser($currentUserId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THODZ - Home</title>
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
            <a href="./home.php" class="nav-link active">
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

    <!-- Main Container -->
    <div class="main-container">
        <!-- Left Sidebar -->
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="./profile.php" class="sidebar-link">
                    <img src="<?php echo htmlspecialchars($currentUserImg); ?>" alt="Profile">
                    <span><?php echo htmlspecialchars($currentUser['fname'] . ' ' . $currentUser['lname']); ?></span>
                </a>
                <a href="./following.php" class="sidebar-link">
                    <i class="fas fa-user-friends"></i>
                    <span>Friends</span>
                </a>
                <a href="./followers.php" class="sidebar-link">
                    <i class="fas fa-users"></i>
                    <span>Followers</span>
                </a>
                <a href="./chat.php" class="sidebar-link">
                    <i class="fas fa-comments"></i>
                    <span>Messages</span>
                </a>
            </nav>
        </aside>

        <!-- Main Feed -->
        <main class="main-feed">
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
                            <button class="post-action-btn like-btn" data-type="post" data-id="<?php echo $POST['pid']; ?>">
                                <i class="far fa-thumbs-up"></i>
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
                                $commentCount = 0;
                                foreach ($comments as $comment):
                                    if ($commentCount >= 3) break; // Show only 3 comments initially
                                    $commentUser = $user->getData($comment['owner']);
                                    $commentUserImg = $images->get_thumb_profile($commentUser['profileimg']);
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
                                    <div class="comment-actions">
                                        <span class="comment-action like-btn" data-type="comment" data-id="<?php echo $comment['pid']; ?>">Like</span>
                                        <?php if ($comment['owner'] == $currentUserId): ?>
                                        <span class="comment-action" onclick="deleteComment(<?php echo $comment['pid']; ?>)">Delete</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                    $commentCount++;
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
                        <i class="fas fa-users" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
                        <h3 style="color: var(--text-secondary);">Welcome to THODZ!</h3>
                        <p style="color: var(--text-muted); margin-bottom: 16px;">
                            Follow some people to see their posts here, or create your first post!
                        </p>
                        <button class="btn btn-primary" data-modal="createPost">Create Post</button>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Right Sidebar -->
        <aside class="right-sidebar">
            <div class="sidebar-section">
                <h3 class="sidebar-section-title">Suggested for you</h3>
                <?php if ($suggestedUsers && count($suggestedUsers) > 0): ?>
                    <?php foreach (array_slice($suggestedUsers, 0, 5) as $suggested): 
                        $suggestedImg = $images->get_thumb_profile($user->getData($suggested['uid'])['profileimg']);
                    ?>
                    <a href="./profile.php?uid=<?php echo $suggested['uid']; ?>" class="sidebar-link">
                        <img src="<?php echo htmlspecialchars($suggestedImg); ?>" alt="Profile">
                        <span><?php echo htmlspecialchars($suggested['fname'] . ' ' . $suggested['lname']); ?></span>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted" style="font-size: 14px;">No suggestions available</p>
                <?php endif; ?>
            </div>
        </aside>
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
                            <input type="file" name="image" id="postImageInput" accept="image/*" data-preview="postImagePreview" style="display: none;">
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

    <script src="./Js/jquery.min.js"></script>
    <script src="./Js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
