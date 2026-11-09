<?php
/** @var \PDO $pdo */
require_once dirname(__DIR__) . '/models/Order.php';

$order_model = new \App\Models\Order($pdo);

// Получаем ID заказа из параметра
$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    header('Location: ' . APP_URL . '/');
    exit;
}

// Получаем информацию о заказе
$order = $order_model->getById($order_id);

if (!$order) {
    header('Location: ' . APP_URL . '/');
    exit;
}
if (!\App\Helpers\Auth::canManageStore() && (!\App\Helpers\Auth::isLoggedIn() || $order['user_id'] != \App\Helpers\Auth::getCurrentUserId())) {
    http_response_code(403);
    include __DIR__ . '/404.php';
    exit;
}

// Получаем товары заказа
$items = $order_model->getItems($order_id);
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
            <div class="order-success-page">
                <div class="success-animation">
                    <div class="checkmark-circle">
                        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="checkmark-circle-bg" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>
                </div>

                <h1 class="success-title">Заказ успешно оформлен!</h1>
                <p class="success-subtitle">Спасибо за покупку в PCComponents Store</p>

                <div class="order-number-card">
                    <div class="order-number-label">Номер заказа</div>
                    <div class="order-number-value"><?php echo htmlspecialchars($order['order_number']); ?></div>
                </div>

                <div class="order-details-grid">
                    <div class="order-detail-card">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/payments.svg" alt="" width="24" height="24">
                        <div>
                            <div class="detail-label">Сумма заказа</div>
                            <div class="detail-value"><?php echo number_format($order['total_amount'], 0, '', ' '); ?> ₽</div>
                        </div>
                    </div>

                    <div class="order-detail-card">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/local_shipping.svg" alt="" width="24" height="24">
                        <div>
                            <div class="detail-label">Доставка</div>
                            <div class="detail-value">
                                <?php
                                    $delivery_labels = [
                                        'pickup' => 'Самовывоз',
                                        'courier' => 'Курьером',
                                        'mail' => 'Почтой'
                                    ];
                                    echo $delivery_labels[$order['delivery_method']] ?? 'Курьером';
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="order-detail-card">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/credit_card.svg" alt="" width="24" height="24">
                        <div>
                            <div class="detail-label">Оплата</div>
                            <div class="detail-value">
                                <?php
                                    $payment_labels = [
                                        'card' => 'Картой онлайн',
                                        'cash' => 'Наличными',
                                        'online' => 'Онлайн'
                                    ];
                                    echo $payment_labels[$order['payment_method']] ?? 'Картой';
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="order-detail-card">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/schedule.svg" alt="" width="24" height="24">
                        <div>
                            <div class="detail-label">Статус</div>
                            <div class="detail-value">
                                <?php
                                    echo htmlspecialchars(\App\Models\Order::statusInfo($order['status'])['label']);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($order['delivery_address'])): ?>
                <div class="order-address-card">
                    <h3>Адрес доставки</h3>
                    <p><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                </div>
                <?php endif; ?>

                <div class="order-items-section">
                    <h3>Состав заказа (<?php echo count($items); ?>)</h3>
                    <div class="order-items-list">
                        <?php foreach ($items as $item): ?>
                            <div class="order-success-item">
                                <div class="order-success-item-info">
                                    <div class="order-success-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="order-success-item-quantity">Количество: <?php echo $item['quantity']; ?></div>
                                </div>
                                <div class="order-success-item-price">
                                    <?php echo number_format($item['total_price'], 0, '', ' '); ?> ₽
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="order-info-message">
                    <img src="<?php echo APP_URL; ?>/public/images/icons/info.svg" alt="" width="20" height="20">
                    <p>
                        Мы отправили подтверждение заказа на вашу электронную почту.
                        Наш менеджер свяжется с вами в ближайшее время для уточнения деталей доставки.
                    </p>
                </div>

                <div class="order-actions">
                    <a href="<?php echo APP_URL; ?>/" class="btn btn-lg">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/home.svg" alt="" width="20" height="20">
                        На главную
                    </a>
                    <a href="<?php echo APP_URL; ?>/catalog" class="btn btn-outline btn-lg">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/apps.svg" alt="" width="20" height="20">
                        Продолжить покупки
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include dirname(__FILE__) . '/includes/footer.php'; ?>
</body>
</html>
