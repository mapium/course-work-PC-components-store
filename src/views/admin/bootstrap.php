<?php
// Общая проверка прав и маршрутизация разделов. Имена файлов не берутся из запроса.
if (!\App\Helpers\Auth::canManageStore()) {
    header('Location: ' . APP_URL . '/');
    exit;
}
$product_model = new \App\Models\Product($pdo);
$order_model = new \App\Models\Order($pdo);
$user_model = new \App\Models\User($pdo);
$admin_sections = [
    'dashboard' => ['title' => 'Основное', 'icon' => 'dashboard', 'file' => 'overview.php'],
    'products' => ['title' => 'Товары', 'icon' => 'inventory', 'file' => 'products.php'],
    'categories' => ['title' => 'Категории', 'icon' => 'folder_open', 'file' => 'categories.php'],
    'orders' => ['title' => 'Заказы', 'icon' => 'shopping_bag', 'file' => 'orders.php'],
    'users' => ['title' => 'Пользователи', 'icon' => 'group', 'file' => 'users.php']
];
$current_tab = \App\Helpers\Request::text($_GET, 'tab', 'dashboard');
if (!isset($admin_sections[$current_tab])) {
    $current_tab = 'dashboard';
}
$admin_page = max(1, min(1000000, \App\Helpers\Request::integer($_GET, 'page', 1)));
$admin_page_size = 50;
$admin_offset = ($admin_page - 1) * $admin_page_size;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!\App\Helpers\CSRF::validateToken($_POST['csrf_token'] ?? '')) {
        \App\Helpers\Response::send(['success' => false, 'message' => 'Ошибка безопасности. Обновите страницу'], 403, '/admin?tab=' . $current_tab);
        exit;
    }
    $action = \App\Helpers\Request::text($_POST, 'action');
    if (in_array($action, ['create', 'update', 'delete'], true)) {
        include __DIR__ . '/actions/products.php';
    } elseif (in_array($action, ['category_create', 'category_update', 'category_delete'], true)) {
        include __DIR__ . '/actions/categories.php';
    } elseif ($action === 'update_order_status') {
        include __DIR__ . '/actions/orders.php';
    } else {
        \App\Helpers\Response::send(['success' => false, 'message' => 'Неизвестное действие'], 400, '/admin?tab=' . $current_tab);
    }
    exit;
}
