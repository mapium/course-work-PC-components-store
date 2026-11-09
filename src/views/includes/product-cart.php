<?php
// $cart_product, $cart_return_to и $cart_compact задаются страницей или карточкой.
// Корзина загружается один раз, даже если на странице несколько карточек.
if (!isset($product_cart_items)) {
    $product_cart_items = array_column((new \App\Models\Cart($pdo))->getItems(\App\Helpers\Auth::getCurrentUserId(), session_id()), null, 'product_id');
}
$product_cart_product_id = (int)$cart_product['id'];
$product_cart_item = $product_cart_items[$product_cart_product_id] ?? null;
$product_cart_id = (int)($product_cart_item['id'] ?? 0);
$product_cart_quantity = (int)($product_cart_item['quantity'] ?? 0);
$product_cart_stock = max(0, (int)$cart_product['stock']);
$product_cart_update_url = APP_URL . '/cart/' . $product_cart_id . '/update';
$product_cart_return_to = htmlspecialchars($cart_return_to, ENT_QUOTES, 'UTF-8');
?>
<div class="product-cart<?php echo $cart_compact ? ' product-cart-compact' : ''; ?>" data-product-cart="<?php echo $product_cart_product_id; ?>" data-cart-id="<?php echo $product_cart_id; ?>" data-quantity="<?php echo $product_cart_quantity; ?>" data-stock="<?php echo $product_cart_stock; ?>">
    <form method="POST" action="<?php echo APP_URL; ?>/cart/add" class="add-to-cart-form" <?php if (!$cart_compact): ?>id="add-to-cart-form"<?php endif; ?> data-ajax="cart-add" data-cart-add-form <?php echo $product_cart_quantity > 0 ? 'hidden' : ''; ?>>
        <?php if ($cart_compact): ?>
            <input type="hidden" name="quantity" value="1">
        <?php else: ?>
            <div class="quantity-selector">
                <label for="quantity">Количество:</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo max(1, $product_cart_stock); ?>" step="1" required>
            </div>
        <?php endif; ?>
        <input type="hidden" name="product_id" value="<?php echo $product_cart_product_id; ?>">
        <input type="hidden" name="return_to" value="<?php echo $product_cart_return_to; ?>">
        <?php echo \App\Helpers\CSRF::getTokenField(); ?>
        <button type="submit" class="btn<?php echo $cart_compact ? ' btn-sm' : ''; ?>" data-cart-add-button <?php echo $product_cart_stock > 0 ? '' : 'disabled'; ?>>
            <?php if ($cart_compact): ?><img src="<?php echo APP_URL; ?>/public/images/icons/shopping_cart.svg" alt="" width="16" height="16"><?php endif; ?>
            <?php echo $product_cart_stock > 0 ? ($cart_compact ? 'В корзину' : 'Добавить в корзину') : 'Нет в наличии'; ?>
        </button>
    </form>
    <form method="POST" action="<?php echo $product_cart_update_url; ?>" class="product-cart-quantity" data-ajax="cart-product" data-cart-quantity-form <?php echo $product_cart_quantity > 0 ? '' : 'hidden'; ?>>
        <input type="hidden" name="return_to" value="<?php echo $product_cart_return_to; ?>">
        <?php echo \App\Helpers\CSRF::getTokenField(); ?>
        <!-- Первый submit обновляет количество при нажатии Enter, а не уменьшает его. -->
        <button type="submit" class="btn btn-sm product-cart-apply" data-cart-apply>Обновить количество</button>
        <input id="product-cart-quantity-<?php echo $product_cart_product_id; ?>" type="number" name="quantity" value="<?php echo max(1, $product_cart_quantity); ?>" min="1" max="<?php echo max(1, $product_cart_stock); ?>" step="1" required class="quantity-display" aria-label="Количество товара в корзине">
        <button type="submit" class="btn btn-icon btn-ghost" name="quantity" value="<?php echo max(0, $product_cart_quantity - 1); ?>" formaction="<?php echo APP_URL . '/cart/' . $product_cart_id . ($product_cart_quantity <= 1 ? '/delete' : '/update'); ?>" data-cart-decrease aria-label="Уменьшить количество" title="<?php echo $product_cart_quantity <= 1 ? 'Убрать из корзины' : 'Уменьшить количество'; ?>">
            <img src="<?php echo APP_URL; ?>/public/images/icons/remove.svg" alt="" width="16" height="16">
        </button>
        <button type="submit" class="btn btn-icon btn-ghost" name="quantity" value="<?php echo $product_cart_quantity + 1; ?>" data-cart-increase aria-label="Увеличить количество" <?php echo $product_cart_quantity >= $product_cart_stock ? 'disabled' : ''; ?>>
            <img src="<?php echo APP_URL; ?>/public/images/icons/add.svg" alt="" width="16" height="16">
        </button>
    </form>
    <p class="sr-only" role="status" aria-live="polite" data-cart-status><?php echo $product_cart_quantity > 0 ? 'В корзине: ' . $product_cart_quantity . ' шт.' : ''; ?></p>
</div>
