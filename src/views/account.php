<?php
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/Order.php';
require_once dirname(__DIR__) . '/helpers/Auth.php';

// Проверка авторизации
if (!\App\Helpers\Auth::isLoggedIn()) {
    header('Location: ' . APP_URL . '/auth');
    exit;
}

$db = new \App\Config\Database();
$pdo = $db->connect();
$order_model = new \App\Models\Order($pdo);

$user_id = \App\Helpers\Auth::getCurrentUserId();
$user = [
    'id' => $user_id,
    'username' => $_SESSION['username'] ?? 'Пользователь',
    'email' => $_SESSION['email'] ?? '',
    'role' => $_SESSION['role'] ?? 'client',
];

// Получаем заказы пользователя
$orders = $order_model->getUserOrders($user_id);
$total_orders = count($orders);
$total_spent = array_sum(array_column($orders, 'total_amount'));

// Статистика
$completed_orders = count(array_filter($orders, function($o) { return $o['status'] === 'completed'; }));
$pending_orders = count(array_filter($orders, function($o) { return $o['status'] === 'pending'; }));
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Личный кабинет - управление заказами и профилем">
    <title>Личный кабинет - PCComponents Store</title>
    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('style.css'); ?>">
</head>
<body>
    <?php include dirname(__FILE__) . '/includes/header.php'; ?>

    <main>
        <div class="container">
            <!-- Шапка профиля -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="<?php echo APP_URL; ?>/public/images/icons/account_circle.svg" alt="Avatar" width="80" height="80">
                </div>
                <div class="profile-info">
                    <h1 class="profile-name"><?php echo htmlspecialchars($user['username']); ?></h1>
                    <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="profile-badge">
                        <?php if ($user['role'] === 'admin'): ?>
                            <span class="badge badge-destructive">Администратор</span>
                        <?php elseif ($user['role'] === 'manager'): ?>
                            <span class="badge badge-secondary">Менеджер</span>
                        <?php else: ?>
                            <span class="badge">Клиент</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Статистика -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/shopping_bag.svg" alt="" width="24" height="24">
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $total_orders; ?></div>
                        <div class="stat-label">Всего заказов</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/check_circle.svg" alt="" width="24" height="24">
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $completed_orders; ?></div>
                        <div class="stat-label">Выполнено</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/schedule.svg" alt="" width="24" height="24">
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $pending_orders; ?></div>
                        <div class="stat-label">В обработке</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <img src="<?php echo APP_URL; ?>/public/images/icons/payments.svg" alt="" width="24" height="24">
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($total_spent, 0, '', ' '); ?> ₽</div>
                        <div class="stat-label">Всего потрачено</div>
                    </div>
                </div>
            </div>

            <!-- Вкладки -->
            <div class="account-section">
                <div class="tabs-container">
                    <div class="tabs-nav">
                        <button class="tab-btn active" data-tab="orders">
                            <img src="<?php echo APP_URL; ?>/public/images/icons/list_alt.svg" alt="" width="18" height="18">
                            Мои заказы
                        </button>
                        <button class="tab-btn" data-tab="profile">
                            <img src="<?php echo APP_URL; ?>/public/images/icons/person.svg" alt="" width="18" height="18">
                            Профиль
                        </button>
                        <button class="tab-btn" data-tab="settings">
                            <img src="<?php echo APP_URL; ?>/public/images/icons/settings.svg" alt="" width="18" height="18">
                            Настройки
                        </button>
                    </div>

                    <!-- Заказы -->
                    <div class="tab-content active" id="orders">
                        <div class="section-header">
                            <h2>История заказов</h2>
                        </div>

                        <?php if (empty($orders)): ?>
                            <div class="empty-state">
                                <img src="<?php echo APP_URL; ?>/public/images/icons/shopping_cart.svg" alt="" width="64" height="64" style="opacity: 0.3;">
                                <p class="empty-state-title">У вас пока нет заказов</p>
                                <p class="empty-state-description">Начните покупки в нашем каталоге</p>
                                <a href="<?php echo APP_URL; ?>/catalog" class="btn">Перейти в каталог</a>
                            </div>
                        <?php else: ?>
                            <div class="orders-list">
                                <?php foreach ($orders as $order): ?>
                                    <div class="order-card">
                                        <div class="order-header">
                                            <div class="order-number">
                                                <span class="order-label">Заказ</span>
                                                <span class="order-id">#<?php echo $order['id']; ?></span>
                                            </div>
                                            <div class="order-status">
                                                <?php
                                                $status_map = [
                                                    'pending' => ['label' => 'В обработке', 'class' => 'badge-warning'],
                                                    'processing' => ['label' => 'Обрабатывается', 'class' => 'badge-info'],
                                                    'shipped' => ['label' => 'Отправлен', 'class' => 'badge-secondary'],
                                                    'completed' => ['label' => 'Выполнен', 'class' => 'badge-success'],
                                                    'cancelled' => ['label' => 'Отменён', 'class' => 'badge-destructive'],
                                                ];
                                                $status = $status_map[$order['status']] ?? ['label' => $order['status'], 'class' => ''];
                                                ?>
                                                <span class="badge <?php echo $status['class']; ?>">
                                                    <?php echo $status['label']; ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="order-body">
                                            <div class="order-meta">
                                                <div class="order-date">
                                                    <img src="<?php echo APP_URL; ?>/public/images/icons/calendar_today.svg" alt="" width="16" height="16">
                                                    <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?>
                                                </div>
                                                <div class="order-total">
                                                    <strong><?php echo number_format($order['total_amount'], 2, '.', ' '); ?> ₽</strong>
                                                </div>
                                            </div>

                                            <?php if (!empty($order['delivery_address'])): ?>
                                                <div class="order-address">
                                                    <img src="<?php echo APP_URL; ?>/public/images/icons/location_on.svg" alt="" width="16" height="16">
                                                    <?php echo htmlspecialchars($order['delivery_address']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="order-footer">
                                            <a href="<?php echo APP_URL; ?>/order/<?php echo $order['id']; ?>" class="btn btn-outline btn-sm">
                                                Подробнее
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Профиль -->
                    <div class="tab-content" id="profile">
                        <div class="section-header">
                            <h2>Информация профиля</h2>
                        </div>

                        <div class="profile-card">
                            <div class="profile-row">
                                <span class="profile-row-label">Имя пользователя</span>
                                <span class="profile-row-value"><?php echo htmlspecialchars($user['username']); ?></span>
                            </div>
                            <div class="profile-row">
                                <span class="profile-row-label">Email</span>
                                <span class="profile-row-value"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="profile-row">
                                <span class="profile-row-label">Роль</span>
                                <span class="profile-row-value"><?php echo htmlspecialchars($user['role']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Настройки -->
                    <div class="tab-content" id="settings">
                        <div class="section-header">
                            <h2>Настройки аккаунта</h2>
                        </div>

                        <div class="settings-card">
                            <div class="setting-item">
                                <div class="setting-content">
                                    <div class="setting-title">Тема оформления</div>
                                    <div class="setting-description">Выберите светлую или тёмную тему</div>
                                </div>
                                <button id="theme-toggle-settings" class="btn btn-outline btn-sm">
                                    <img src="<?php echo APP_URL; ?>/public/images/icons/dark_mode.svg" alt="" width="16" height="16">
                                    Переключить
                                </button>
                            </div>

                            <div class="setting-item">
                                <div class="setting-content">
                                    <div class="setting-title">Уведомления</div>
                                    <div class="setting-description">Получать уведомления о заказах</div>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="switch-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include dirname(__FILE__) . '/includes/footer.php'; ?>

    <script>
        // Переключение вкладок
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');

                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                this.classList.add('active');
                document.getElementById(tabName).classList.add('active');
            });

            btn.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });

        // Переключатель темы в настройках
        document.getElementById('theme-toggle-settings')?.addEventListener('click', function() {
            document.getElementById('theme-toggle')?.click();
        });
    </script>
</body>
</html>
