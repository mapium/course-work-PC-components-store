<?php
/** @var \PDO $pdo */
require_once dirname(__DIR__) . '/models/Cart.php';

$cart_model = new \App\Models\Cart($pdo);

// Получаем ID пользователя или сессии
$user_id = \App\Helpers\Auth::isLoggedIn() ? \App\Helpers\Auth::getCurrentUserId() : null;
$session_id = session_id();

$items = $cart_model->getItems($user_id, $session_id);
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $items ? 300 : 0;
$total = $subtotal + $shipping;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>
<body>
    <?php include dirname(__FILE__) . '/includes/header.php'; ?>

    <main>
        <div class="container">
            <div class="page-header">
                <h1>
                    <img src="<?php echo APP_URL; ?>/public/images/icons/shopping_cart.svg" alt="" width="32" height="32" class="page-icon">
                    Корзина покупок
                </h1>
            </div>

            <?php if (empty($items)): ?>
                <div class="empty-state">
                    <img src="<?php echo APP_URL; ?>/public/images/icons/shopping_cart.svg" alt="" width="80" height="80" style="opacity: 0.3;">
                    <p class="empty-state-title">Ваша корзина пуста</p>
                    <p class="empty-state-description">Добавьте товары из каталога</p>
                    <a href="<?php echo APP_URL; ?>/catalog" class="btn">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/apps.svg" alt="" width="18" height="18">
                        Перейти в каталог
                    </a>
                </div>
            <?php else: ?>
                <div class="layout-split cart-container">
                    <div class="cart-items-section">
                        <div class="cart-header">
                            <h2>Товары (<?php echo count($items); ?>)</h2>
                        </div>

                        <div class="cart-items">
                            <?php foreach ($items as $item): ?>
                                <div class="cart-item-card" data-cart-item="<?php echo (int)$item['id']; ?>">
                                    <a href="<?php echo htmlspecialchars(\App\Helpers\Url::product($item, $pdo)); ?>" class="cart-item-image-link">
                                        <img src="<?php echo APP_URL; ?>/public/images/products/<?php echo htmlspecialchars($item['main_image'] ?? 'placeholder.jpg'); ?>"
                                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                                             class="cart-item-image"
                                             loading="lazy"
                                             width="120"
                                             height="120">
                                    </a>

                                    <div class="cart-item-details">
                                        <a href="<?php echo htmlspecialchars(\App\Helpers\Url::product($item, $pdo)); ?>" class="cart-item-name">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                        <div class="cart-item-price">
                                            <?php echo number_format($item['price'], 0, '', ' '); ?> ₽
                                        </div>
                                    </div>

                                    <div class="cart-item-actions">
                                        <div class="quantity-control">
                                            <form method="POST" action="<?php echo APP_URL; ?>/cart/<?php echo $item['id']; ?>/update" class="quantity-form" data-ajax="cart">
                                                <?php echo \App\Helpers\CSRF::getTokenField(); ?>
                                                <input type="hidden" name="quantity" value="<?php echo max(1, $item['quantity'] - 1); ?>">
                                                <button type="submit" class="btn btn-icon btn-outline btn-sm" aria-label="Уменьшить количество" <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>
                                                    <img src="<?php echo APP_URL; ?>/public/images/icons/remove.svg" alt="" width="16" height="16">
                                                </button>
                                            </form>

                                            <form method="POST" action="<?php echo APP_URL; ?>/cart/<?php echo $item['id']; ?>/update" class="quantity-direct-form" data-ajax="cart">
                                                <?php echo \App\Helpers\CSRF::getTokenField(); ?>
                                                <input id="cart-quantity-<?php echo (int)$item['id']; ?>" type="number" name="quantity" value="<?php echo (int)$item['quantity']; ?>" min="1" max="<?php echo max(1, (int)$item['stock']); ?>" step="1" required class="quantity-display" aria-label="Количество товара">
                                                <noscript><button type="submit" class="btn btn-sm">Обновить</button></noscript>
                                            </form>

                                            <form method="POST" action="<?php echo APP_URL; ?>/cart/<?php echo $item['id']; ?>/update" class="quantity-form" data-ajax="cart">
                                                <?php echo \App\Helpers\CSRF::getTokenField(); ?>
                                                <input type="hidden" name="quantity" value="<?php echo $item['quantity'] + 1; ?>">
                                                <button type="submit" class="btn btn-icon btn-outline btn-sm" aria-label="Увеличить количество" <?php echo !$item['is_active'] || $item['quantity'] >= $item['stock'] ? 'disabled' : ''; ?>>
                                                    <img src="<?php echo APP_URL; ?>/public/images/icons/add.svg" alt="" width="16" height="16">
                                                </button>
                                            </form>
                                        </div>

                                        <div class="cart-item-total">
                                            <?php echo number_format($item['price'] * $item['quantity'], 0, '', ' '); ?> ₽
                                        </div>

                                        <form method="POST" action="<?php echo APP_URL; ?>/cart/<?php echo $item['id']; ?>/delete" class="delete-form" data-ajax="cart">
                                            <?php echo \App\Helpers\CSRF::getTokenField(); ?>
                                            <button type="submit" class="btn btn-icon btn-outline btn-sm" aria-label="Удалить товар">
                                                <img src="<?php echo APP_URL; ?>/public/images/icons/delete.svg" alt="" width="18" height="18">
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <aside class="sticky-aside cart-summary-section">
                        <div class="cart-summary">
                            <h2 class="summary-title">Итого</h2>

                            <div class="summary-rows">
                                <div class="summary-row">
                                    <span class="summary-label">Товары (<?php echo count($items); ?>)</span>
                                    <span class="summary-value"><?php echo number_format($subtotal, 0, '', ' '); ?> ₽</span>
                                </div>

                                <div class="summary-row">
                                    <span class="summary-label">Доставка</span>
                                    <span class="summary-value"><?php echo number_format($shipping, 0, '', ' '); ?> ₽</span>
                                </div>

                                <div class="summary-divider"></div>

                                <div class="summary-row summary-total">
                                    <span class="summary-label">Итого</span>
                                    <span class="summary-value"><?php echo number_format($total, 0, '', ' '); ?> ₽</span>
                                </div>
                            </div>

                            <a href="<?php echo APP_URL; ?>/checkout" class="btn btn-lg">
                                <img src="<?php echo APP_URL; ?>/public/images/icons/check_circle.svg" alt="" width="20" height="20">
                                Оформить заказ
                            </a>

                            <a href="<?php echo APP_URL; ?>/catalog" class="btn btn-outline">
                                <img src="<?php echo APP_URL; ?>/public/images/icons/arrow_back.svg" alt="" width="18" height="18">
                                Продолжить покупки
                            </a>
                        </div>
                    </aside>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include dirname(__FILE__) . '/includes/footer.php'; ?>
</body>
</html>
