<?php
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/Cart.php';

$db = new \App\Config\Database();
$pdo = $db->connect();
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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-url" content="<?php echo APP_URL; ?>">
    <title>Оформление заказа - PCComponents Store</title>
    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('style.css'); ?>">
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

            <div class="checkout-container">
                <div class="checkout-form-section">
                    <form id="checkoutForm" class="checkout-form" method="POST" action="<?php echo APP_URL; ?>/api/orders/create">
                        <div class="form-section">
                            <h2 class="form-section-title">Контактная информация</h2>

                            <div class="form-group">
                                <label for="name" class="form-label">Имя *</label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       class="form-input"
                                       required
                                       value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>"
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
                                           value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>"
                                           placeholder="example@mail.com">
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">Телефон *</label>
                                    <input type="tel"
                                           id="phone"
                                           name="phone"
                                           class="form-input"
                                           required
                                           value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>"
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
                                           class="form-input"
                                           required
                                           placeholder="Москва">
                                </div>

                                <div class="form-group" style="flex: 1;">
                                    <label for="postal_code" class="form-label">Индекс</label>
                                    <input type="text"
                                           id="postal_code"
                                           name="postal_code"
                                           class="form-input"
                                           placeholder="123456">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address" class="form-label">Улица, дом, квартира *</label>
                                <input type="text"
                                       id="address"
                                       name="address"
                                       class="form-input"
                                       required
                                       placeholder="ул. Пушкина, д. 10, кв. 25">
                            </div>

                            <div class="form-group">
                                <label for="comment" class="form-label">Комментарий к заказу</label>
                                <textarea id="comment"
                                          name="comment"
                                          class="form-input"
                                          rows="3"
                                          placeholder="Дополнительная информация для курьера"></textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2 class="form-section-title">Способ оплаты</h2>

                            <div class="payment-methods">
                                <label class="payment-method-card">
                                    <input type="radio" name="payment_method" value="card" checked>
                                    <div class="payment-method-content">
                                        <img src="<?php echo APP_URL; ?>/public/images/icons/credit_card.svg" alt="" width="24" height="24">
                                        <div>
                                            <div class="payment-method-title">Картой онлайн</div>
                                            <div class="payment-method-description">Visa, MasterCard, Мир</div>
                                        </div>
                                    </div>
                                </label>

                                <label class="payment-method-card">
                                    <input type="radio" name="payment_method" value="cash">
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

                <aside class="checkout-summary-section">
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

                        <button type="submit" form="checkoutForm" class="btn btn-lg btn-block">
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
    <script src="<?php echo \App\Helpers\Asset::js('main.js'); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Checkout page loaded');

            const checkoutForm = document.getElementById('checkoutForm');
            console.log('Form found:', checkoutForm);

            if (!checkoutForm) {
                console.error('Checkout form not found!');
                return;
            }

            // Обработка формы оформления заказа
            checkoutForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Form submitted');

                const form = this;
                const formData = new FormData(form);
                const submitBtn = document.querySelector('button[form="checkoutForm"]');

                console.log('Submit button:', submitBtn);

                // Отключаем кнопку на время отправки
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Оформление...';
                }

                console.log('Sending request to:', form.action);

                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        // Редиректим на страницу успеха
                        window.location.href = '<?php echo APP_URL; ?>/order-success?id=' + data.order_id;
                    } else {
                        showNotification(data.message || 'Ошибка при оформлении заказа', 'error');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<img src="<?php echo APP_URL; ?>/public/images/icons/check_circle.svg" alt="" width="20" height="20"> Оформить заказ';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Ошибка при оформлении заказа', 'error');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<img src="<?php echo APP_URL; ?>/public/images/icons/check_circle.svg" alt="" width="20" height="20"> Оформить заказ';
                    }
                });
            });
        });
    </script>
</body>
</html>
