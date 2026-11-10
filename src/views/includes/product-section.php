<?php
// $product_section задаёт заголовок, товары, обратный адрес и пустое состояние общего блока.
/** @var array $product_section */
?>
<section class="product-section" id="<?php echo htmlspecialchars($product_section['id']); ?>" data-product-section="<?php echo htmlspecialchars($product_section['id']); ?>" aria-labelledby="<?php echo htmlspecialchars($product_section['id']); ?>-title">
    <div class="product-section-heading">
        <h2 id="<?php echo htmlspecialchars($product_section['id']); ?>-title"><?php echo htmlspecialchars($product_section['title']); ?></h2>
        <p><?php echo htmlspecialchars($product_section['description']); ?></p>
    </div>
    <?php if ($product_section['products']): ?>
        <div class="products-grid">
            <?php
            $card_return_to = $product_section['return_to'];
            foreach ($product_section['products'] as $card_product):
                include __DIR__ . '/product-card.php';
            endforeach;
            ?>
        </div>
    <?php else: ?>
        <div class="product-section-empty">
            <p><?php echo htmlspecialchars($product_section['empty_message']); ?></p>
            <a href="<?php echo APP_URL; ?>/catalog" class="btn btn-outline btn-sm">Перейти в каталог</a>
        </div>
    <?php endif; ?>
</section>
