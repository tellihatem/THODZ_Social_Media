<?php
/**
 * Modern Comment Card Template
 * Required variables: $comment, $commentUser, $commentUserImg, $commentImage, $currentUserId
 */
if (!isset($comment) || !is_array($comment)) return;
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
            <span class="comment-time"><?php echo date('M j', strtotime($comment['date'] ?? 'now')); ?></span>
            <?php if ($comment['owner'] == $currentUserId): ?>
            <span class="comment-action" onclick="deleteComment(<?php echo $comment['pid']; ?>)">Delete</span>
            <?php endif; ?>
        </div>
    </div>
</div>
