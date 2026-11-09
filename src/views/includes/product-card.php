<?php
/** @var \PDO $pdo */
/** @var \PDO $pdo */
// $card_product и $card_return_to задаются страницей, использующей карточку.
$card_description = \App\Helpers\Seo::productDescription($card_product);
$card_excerpt = iconv_substr($card_description, 0, 100, 'UTF-8');
$card_image_name = $card_product['main_image'] ?: 'placeholder.jpg';
$card_image_dimensions = \App\Helpers\Media::dimensions($card_image_name, 'products', true);
?>
<div class="product-card">
    <a href="<?php echo htmlspecialchars(\App\Helpers\Url::product($card_product, $pdo)); ?>" class="product-card-link">
        <span class="product-card-images">
        <img src="<?php echo htmlspecialchars(\App\Helpers\Media::url($card_image_name, 'products', true)); ?>"
             alt="<?php echo htmlspecialchars($card_product['name'], ENT_QUOTES, 'UTF-8'); ?>"
              class="product-image" loading="lazy" width="<?php echo $card_image_dimensions[0]; ?>" height="<?php echo $card_image_dimensions[1]; ?>">
        <?php if (!empty($card_product['hover_image'])): ?>
            <?php $card_hover_dimensions = \App\Helpers\Media::dimensions($card_product['hover_image'], 'products', true); ?>
            <img src="<?php echo htmlspecialchars(\App\Helpers\Media::url($card_product['hover_image'], 'products', true)); ?>" alt="" class="product-image product-image-hover" loading="lazy" width="<?php echo $card_hover_dimensions[0]; ?>" height="<?php echo $card_hover_dimensions[1]; ?>">
        <?php endif; ?>
        </span>
        <div class="product-info">
            <h3 class="product-name" title="<?php echo htmlspecialchars($card_product['name'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($card_product['name']); ?></h3>
            <?php
            $rating_value = $card_product['average_rating'] ?? 0;
            $rating_count = $card_product['review_count'] ?? 0;
            include __DIR__ . '/rating.php';
            ?>
            <p class="product-price">
                <?php echo number_format($card_product['price'], 2, '.', ' '); ?> ₽
                <?php if (!empty($card_product['old_price'])): ?>
                    <del class="old-price"><?php echo number_format($card_product['old_price'], 0, '.', ' '); ?> ₽</del>
                <?php endif; ?>
            </p>
            <p class="product-description"><?php echo htmlspecialchars($card_excerpt); ?><?php echo \App\Helpers\Request::length($card_description) > 100 ? '…' : ''; ?></p>
        </div>
    </a>
    <div class="product-actions">
        <?php
        $cart_product = $card_product;
        $cart_return_to = $card_return_to ?? '/catalog';
        $cart_compact = true;
        include __DIR__ . '/product-cart.php';
        ?>
    </div>
</div>
