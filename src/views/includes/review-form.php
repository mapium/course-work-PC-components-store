<details id="review-editor" class="review-editor" <?php echo $review_form_open ? 'open' : ''; ?>>
    <summary class="btn btn-secondary add-review-btn"><?php echo htmlspecialchars($review_editor['title']); ?></summary>
    <div class="review-editor-body" aria-labelledby="reviewModalTitle">
        <div class="modal-header">
            <h3 id="reviewModalTitle"><?php echo htmlspecialchars($review_editor['title']); ?></h3>
            <button type="button" class="modal-close" data-close-review aria-label="Закрыть форму" hidden>&times;</button>
        </div>
        <form id="reviewForm" method="POST" action="<?php echo htmlspecialchars($product_url); ?>/reviews" data-ajax="review">
            <input type="hidden" name="action" value="<?php echo $review_editor['action']; ?>">
            <input type="hidden" name="parent_id" id="parentReviewId" value="<?php echo $review_editor['parent_id']; ?>">
            <input type="hidden" name="review_id" id="editReviewId" value="<?php echo $review_editor['review_id']; ?>">
            <?php echo \App\Helpers\CSRF::getTokenField(); ?>
            <?php if ($review_editor['show_rating']): ?>
                <div class="form-group" id="ratingGroup">
                    <label>Оценка *</label>
                    <div class="rating-input" role="group" aria-label="Оценка товара от 1 до 5">
                        <?php for ($star = 5; $star >= 1; $star--): ?>
                            <input type="radio" name="rating" value="<?php echo $star; ?>" id="star<?php echo $star; ?>" aria-label="<?php echo $star; ?> из 5" <?php echo $star === 5 ? 'required' : ''; ?> <?php echo (int)$review_editor['rating'] === $star ? 'checked' : ''; ?>>
                            <label for="star<?php echo $star; ?>" title="<?php echo $star; ?> из 5">★</label>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="reviewComment">Комментарий *</label>
                <textarea name="comment" id="reviewComment" rows="5" maxlength="10000" required placeholder="Напишите ваш отзыв..."><?php echo htmlspecialchars($review_editor['comment']); ?></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn"><?php echo $review_editor['action'] === 'update' ? 'Сохранить' : 'Отправить'; ?></button>
                <a data-review-link href="<?php echo htmlspecialchars($product_url); ?>?tab=reviews#reviews" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</details>
<?php if (!$review_editor['show_rating'] || $review_editor['action'] === 'update'): ?>
    <a data-review-link href="<?php echo htmlspecialchars($product_url); ?>?tab=reviews&amp;review_action=add#review-editor" class="btn btn-secondary add-review-btn">Оставить новый отзыв</a>
<?php endif; ?>
