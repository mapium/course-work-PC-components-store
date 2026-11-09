<?php
require_once dirname(__DIR__) . '/models/Cart.php';

$cart_model = new \App\Models\Cart($pdo);

// Получаем ID пользователя или сессии
$user_id = \App\Helpers\Auth::isLoggedIn() ? \App\Helpers\Auth::getCurrentUserId() : null;
$session_id = session_id();

$items = $cart_model->getItems($user_id, $session_id);

// Если корзина пустая, редиректим на страницу корзины
if (empty($items)) {
    header('Location: ' . APP_URL . '/cart');
    exit;
}

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 300;
$total = $subtotal + $shipping;

// Получаем данные пользователя если авторизован
$user_data = null;
if ($user_id) {
    $stmt = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(\PDO::FETCH_ASSOC);
}
$checkout_values = $_SESSION['checkout_old'] ?? [];
unset($_SESSION['checkout_old']);
$checkout_values += [
    'name' => $user_data['full_name'] ?? '', 'email' => $user_data['email'] ?? '',
    'phone' => $user_data['phone'] ?? '', 'city' => '', 'postal_code' => '', 'address' => '',
    'comment' => '', 'payment_method' => 'card'
];
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
                    <img src="<?php echo APP_URL; ?>/public/images/icons/check_circle.svg" alt="" width="32" height="32" class="page-icon">
                    Оформление заказа
                </h1>
            </div>

            <div class="layout-split checkout-container">
                <div class="checkout-form-section">
                    <?php if (!$user_id): ?>
                        <div class="alert alert-info">Для оформления заказа <a href="<?php echo APP_URL; ?>/auth">войдите в аккаунт</a>. Товары останутся в корзине.</div>
                    <?php endif; ?>
                    <form id="checkoutForm" class="checkout-form" method="POST" action="<?php echo APP_URL; ?>/checkout">
                        <?php echo \App\Helpers\CSRF::getTokenField(); ?>
                        <div class="form-section">
                            <h2 class="form-section-title">Контактная информация</h2>

                            <div class="form-group">
                                <label for="name" class="form-label">Имя *</label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       class="form-input"
                                       required
                                       maxlength="100" value="<?php echo htmlspecialchars($checkout_values['name']); ?>"
                                       placeholder="Иван Иванов">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email"
                                           id="email"
                                           name="email"
                                           class="form-input"
                                           required
                                           maxlength="100" value="<?php echo htmlspecialchars($checkout_values['email']); ?>"
                                           placeholder="example@mail.com">
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">Телефон *</label>
                                    <input type="tel"
                                           id="phone"
                                           name="phone"
                                           class="form-input"
                                           required
                                           maxlength="20" value="<?php echo htmlspecialchars($checkout_values['phone']); ?>"
                                           placeholder="+7 (999) 123-45-67">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2 class="form-section-title">Адрес доставки</h2>

                            <div class="form-row">
                                <div class="form-group" style="flex: 2;">
                                    <label for="city" class="form-label">Город *</label>
                                    <input type="text"
                                           id="city"
                                            name="city"
                                            maxlength="100" value="<?php echo htmlspecialchars($checkout_values['city']); ?>"
                                           class="form-input"
                                           required
                                           placeholder="Москва">
                                </div>

                                <div class="form-group" style="flex: 1;">
                                    <label for="postal_code" class="form-label">Индекс</label>
                                    <input type="text"
                                           id="postal_code"
                                            name="postal_code"
                                            maxlength="6" pattern="[0-9]{6}" value="<?php echo htmlspecialchars($checkout_values['postal_code']); ?>"
                                           class="form-input"
                                           placeholder="123456">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address" class="form-label">Улица, дом, квартира *</label>
                                <input type="text"
                                       id="address"
                                       name="address"
                                       maxlength="255" value="<?php echo htmlspecialchars($checkout_values['address']); ?>"
                                       class="form-input"
                                       required
                                       placeholder="ул. Пушкина, д. 10, кв. 25">
                            </div>

                            <div class="form-group">
                                <label for="comment" class="form-label">Комментарий к заказу</label>
                                <textarea id="comment"
                                           name="comment"
                                           maxlength="5000"
                                          class="form-input"
                                          rows="3"
                                           placeholder="Дополнительная информация для курьера"><?php echo htmlspecialchars($checkout_values['comment']); ?></textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2 class="form-section-title">Способ оплаты</h2>

                            <div class="payment-methods">
                                <label class="payment-method-card">
                                    <input type="radio" name="payment_method" value="card" <?php echo $checkout_values['payment_method'] === 'card' ? 'checked' : ''; ?>>
                                    <div class="payment-method-content">
                                        <img src="<?php echo APP_URL; ?>/public/images/icons/credit_card.svg" alt="" width="24" height="24">
                                        <div>
                                            <div class="payment-method-title">Картой онлайн</div>
                                            <div class="payment-method-description">Visa, MasterCard, Мир</div>
                                        </div>
                                    </div>
                                </label>

                                <label class="payment-method-card">
                                    <input type="radio" name="payment_method" value="cash" <?php echo $checkout_values['payment_method'] === 'cash' ? 'checked' : ''; ?>>
                                    <div class="payment-method-content">
                                        <img src="<?php echo APP_URL; ?>/public/images/icons/payments.svg" alt="" width="24" height="24">
                                        <div>
                                            <div class="payment-method-title">Наличными при получении</div>
                                            <div class="payment-method-description">Оплата курьеру</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>

                <aside class="sticky-aside checkout-summary-section">
                    <div class="checkout-summary">
                        <h2 class="summary-title">Ваш заказ</h2>

                        <div class="checkout-items">
                            <?php foreach ($items as $item): ?>
                                <div class="checkout-item">
                                    <img src="<?php echo APP_URL; ?>/public/images/products/<?php echo htmlspecialchars($item['main_image'] ?? 'placeholder.jpg'); ?>"
                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         class="checkout-item-image"
                                         loading="lazy"
                                         width="60"
                                         height="60">
                                    <div class="checkout-item-details">
                                        <div class="checkout-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="checkout-item-quantity">Количество: <?php echo $item['quantity']; ?></div>
                                    </div>
                                    <div class="checkout-item-price">
                                        <?php echo number_format($item['price'] * $item['quantity'], 0, '', ' '); ?> ₽
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="summary-divider"></div>

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

                        <button type="submit" form="checkoutForm" class="btn btn-lg btn-block" <?php echo $user_id ? '' : 'disabled'; ?>>
                            <img src="<?php echo APP_URL; ?>/public/images/icons/check_circle.svg" alt="" width="20" height="20">
                            Оформить заказ
                        </button>

                        <div class="checkout-security-note">
                            <img src="<?php echo APP_URL; ?>/public/images/icons/lock.svg" alt="" width="16" height="16">
                            <span>Безопасная оплата</span>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <?php include dirname(__FILE__) . '/includes/footer.php'; ?>
</body>
</html>
