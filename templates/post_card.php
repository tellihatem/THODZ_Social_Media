<?php
/**
 * Modern Post Card Template
 * Required variables: $POST, $postUser, $postUserImg, $postImage, $isOwner, $currentUserId, $currentUserImg
 */
if (!isset($POST) || !is_array($POST)) return;
?>
<div class="card post" data-post-id="<?php echo $POST['pid']; ?>">
    <div class="post-header">
        <a href="./profile_new.php?uid=<?php echo $POST['owner']; ?>" class="post-author">
            <img src="<?php echo htmlspecialchars($postUserImg); ?>" alt="Profile">
            <div class="post-author-info">
                <h4><?php echo htmlspecialchars($postUser['fname'] . ' ' . $postUser['lname']); ?></h4>
                <span><?php echo date('M j, Y g:i A', strtotime($POST['date'] ?? 'now')); ?></span>
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
        <div class="post-likes" id="likesWrapper_<?php echo $POST['pid']; ?>" style="<?php echo $POST['likes'] > 0 ? '' : 'display:none;'; ?>">
            <div class="post-likes-icon"><i class="fas fa-thumbs-up"></i></div>
            <span id="likesCount_<?php echo $POST['pid']; ?>"><?php echo $POST['likes']; ?></span>
        </div>
        <div class="post-stats-right">
            <span class="post-likes-count" id="likesText_<?php echo $POST['pid']; ?>">
                <?php echo $POST['likes']; ?> likes
            </span>
            <span class="post-comments-count" id="commentCount_<?php echo $POST['pid']; ?>">
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

    <!-- Comments Section (hidden by default) -->
    <div class="post-comments" id="comments_<?php echo $POST['pid']; ?>"></div>

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
