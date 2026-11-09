<?php
$entry_id = (int)$review_entry['id'];
$entry_is_reply = $review_is_reply;
$entry = $review_entry;
$can_edit_review = \App\Helpers\Auth::isLoggedIn() && $entry['user_id'] == \App\Helpers\Auth::getUserId();
$can_delete_review = $can_edit_review || \App\Helpers\Auth::canManageStore();
?>
<div class="review-card <?php echo $entry_is_reply ? 'review-reply' : ''; ?>" data-review-id="<?php echo $entry_id; ?>">
    <div class="review-header">
        <div class="review-author-info">
            <div class="review-author">
                <?php echo htmlspecialchars($entry['full_name'] ?: ($entry['username'] ?? 'Анонимный')); ?>
                <?php if ($entry_is_reply && in_array($entry['user_role'] ?? '', ['admin', 'manager'], true)): ?><span class="badge badge-secondary">Представитель магазина</span><?php endif; ?>
            </div>
            <?php if (!$entry_is_reply): ?>
                <div class="review-rating">
                    <span class="rating-stars" role="img" aria-label="Оценка <?php echo \App\Helpers\Rating::normalize($entry['rating']); ?> из 5">
                        <?php foreach (\App\Helpers\Rating::fills($entry['rating']) as $fill): ?>
                            <span class="rating-star" aria-hidden="true">★<span class="rating-star-fill" style="width: <?php echo $fill; ?>%;">★</span></span>
                        <?php endforeach; ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        <div class="review-actions">
            <div class="review-date">
                <?php echo date('d.m.Y', strtotime($entry['created_at'])); ?>
                <?php if (!empty($entry['updated_at']) && $entry['updated_at'] !== $entry['created_at']): ?><span class="review-edited" title="Отредактировано">(изменено)</span><?php endif; ?>
            </div>
            <?php if ($can_edit_review): ?>
                <a class="btn-icon edit-review-btn" data-review-link href="<?php echo htmlspecialchars($product_url); ?>?tab=reviews&amp;review_action=edit&amp;review_id=<?php echo $entry_id; ?>#review-editor" aria-label="<?php echo $entry_is_reply ? 'Редактировать ответ' : 'Редактировать отзыв'; ?>">
                    <img src="<?php echo APP_URL; ?>/public/images/icons/edit.svg" alt="" width="16" height="16">
                </a>
            <?php endif; ?>
            <?php if ($can_delete_review): ?>
                <form method="POST" action="<?php echo htmlspecialchars($product_url); ?>/reviews" data-ajax="review" data-confirm="Удалить <?php echo $entry_is_reply ? 'ответ' : 'отзыв вместе с ответами'; ?>?">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="review_id" value="<?php echo $entry_id; ?>">
                    <?php echo \App\Helpers\CSRF::getTokenField(); ?>
                    <button type="submit" class="btn-icon delete-review-btn" aria-label="<?php echo $entry_is_reply ? 'Удалить ответ' : 'Удалить отзыв'; ?>">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/delete.svg" alt="" width="16" height="16">
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <div class="review-text"><?php echo nl2br(htmlspecialchars($entry['comment'] ?? '')); ?></div>
    <?php if (!$entry_is_reply && \App\Helpers\Auth::isLoggedIn()): ?>
        <a class="btn-text reply-review-btn" data-review-link href="<?php echo htmlspecialchars($product_url); ?>?tab=reviews&amp;review_action=reply&amp;review_id=<?php echo $entry_id; ?>#review-editor">
            <img src="<?php echo APP_URL; ?>/public/images/icons/reply.svg" alt="" width="14" height="14">Ответить
        </a>
    <?php endif; ?>
    <?php if (!$entry_is_reply && !empty($entry['replies'])): ?>
        <div class="review-replies">
            <?php foreach ($entry['replies'] as $reply_entry): ?>
                <?php $review_entry = $reply_entry; $review_is_reply = true; include __FILE__; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
