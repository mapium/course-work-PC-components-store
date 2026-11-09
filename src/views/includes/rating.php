<?php
$display_rating = \App\Helpers\Rating::normalize($rating_value ?? 0);
$display_review_count = max(0, (int)($rating_count ?? 0));
$rating_description = $display_review_count > 0 ? 'Рейтинг ' . number_format($display_rating, 1, ',', '') . ' из 5' : 'Нет оценок';
?>
<div class="product-rating">
    <span class="rating-stars" role="img" aria-label="<?php echo $rating_description; ?>">
        <?php foreach (\App\Helpers\Rating::fills($display_rating) as $fill): ?>
            <span class="rating-star" aria-hidden="true">★<span class="rating-star-fill" style="width: <?php echo $fill; ?>%;">★</span></span>
        <?php endforeach; ?>
    </span>
    <span class="rating-value"><?php echo $display_review_count > 0 ? number_format($display_rating, 1, ',', '') : 'Нет оценок'; ?></span>
    <span class="rating-count">(<?php echo \App\Helpers\Rating::reviewLabel($display_review_count); ?>)</span>
</div>
