<?php
require_once dirname(dirname(__DIR__)) . '/config/Database.php';
require_once dirname(dirname(__DIR__)) . '/models/Product.php';
require_once dirname(dirname(__DIR__)) . '/models/Order.php';
require_once dirname(dirname(__DIR__)) . '/models/User.php';
require_once dirname(dirname(__DIR__)) . '/helpers/CSRF.php';

$db = new \App\Config\Database();
$pdo = $db->connect();
$product_model = new \App\Models\Product($pdo);
$order_model = new \App\Models\Order($pdo);
$user_model = new \App\Models\User($pdo);

// Обработка форм
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Проверка CSRF токена
    if (!\App\Helpers\CSRF::validateToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    if ($action === 'update' && isset($_POST['id'])) {
        // Обновление товара
        $product_id = (int)$_POST['id'];

        $data = [
            'name' => $_POST['name'] ?? '',
            'price' => (float)($_POST['price'] ?? 0),
            'old_price' => !empty($_POST['old_price']) ? (float)$_POST['old_price'] : null,
            'stock' => (int)($_POST['stock'] ?? 0),
            'brand' => $_POST['brand'] ?? null,
            'sku' => $_POST['sku'] ?? null,
            'description' => $_POST['description'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'meta_title' => $_POST['meta_title'] ?? null,
            'meta_description' => $_POST['meta_description'] ?? null,
            'meta_keywords' => $_POST['meta_keywords'] ?? null
        ];

        // Обновление в базе данных
        $stmt = $pdo->prepare("UPDATE products SET
            name = :name,
            price = :price,
            old_price = :old_price,
            stock = :stock,
            brand = :brand,
            sku = :sku,
            description = :description,
            is_active = :is_active,
            meta_title = :meta_title,
            meta_description = :meta_description,
            meta_keywords = :meta_keywords,
            updated_at = NOW()
            WHERE id = :id");

        $stmt->execute([
            ':id' => $product_id,
            ':name' => $data['name'],
            ':price' => $data['price'],
            ':old_price' => $data['old_price'],
            ':stock' => $data['stock'],
            ':brand' => $data['brand'],
            ':sku' => $data['sku'],
            ':description' => $data['description'],
            ':is_active' => $data['is_active'],
            ':meta_title' => $data['meta_title'],
            ':meta_description' => $data['meta_description'],
            ':meta_keywords' => $data['meta_keywords']
        ]);

        // Обновление главного изображения
        if (isset($_POST['main_image']) && !empty($_POST['main_image'])) {
            $main_image = $_POST['main_image'];

            // Сначала убираем флаг is_main у всех изображений товара
            $stmt = $pdo->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = :product_id");
            $stmt->execute([':product_id' => $product_id]);

            // Проверяем, существует ли уже это изображение
            $stmt = $pdo->prepare("SELECT id FROM product_images WHERE product_id = :product_id AND image_path = :image_path");
            $stmt->execute([
                ':product_id' => $product_id,
                ':image_path' => $main_image
            ]);
            $existing_image = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_image) {
                // Обновляем существующее изображение
                $stmt = $pdo->prepare("UPDATE product_images SET is_main = 1 WHERE id = :id");
                $stmt->execute([':id' => $existing_image['id']]);
            } else {
                // Добавляем новое изображение как главное
                $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_main, sort_order) VALUES (:product_id, :image_path, 1, 1)");
                $stmt->execute([
                    ':product_id' => $product_id,
                    ':image_path' => $main_image
                ]);
            }
        }

        // Обновление характеристик
        if (isset($_POST['specifications'])) {
            // Удаляем старые характеристики
            $stmt = $pdo->prepare("DELETE FROM product_specifications WHERE product_id = :product_id");
            $stmt->execute([':product_id' => $product_id]);

            // Добавляем новые характеристики
            $stmt = $pdo->prepare("INSERT INTO product_specifications (product_id, spec_name, spec_value) VALUES (:product_id, :spec_name, :spec_value)");

            foreach ($_POST['specifications'] as $spec) {
                if (!empty($spec['name']) && !empty($spec['value'])) {
                    $stmt->execute([
                        ':product_id' => $product_id,
                        ':spec_name' => trim($spec['name']),
                        ':spec_value' => trim($spec['value'])
                    ]);
                }
            }
        }

        header('Location: ?tab=products');
        exit;
    }

    if ($action === 'delete' && isset($_POST['id'])) {
        // Удаление товара
        $product_id = (int)$_POST['id'];

        // Удаляем связанные изображения
        $stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = :product_id");
        $stmt->execute([':product_id' => $product_id]);

        // Удаляем характеристики
        $stmt = $pdo->prepare("DELETE FROM product_specifications WHERE product_id = :product_id");
        $stmt->execute([':product_id' => $product_id]);

        // Удаляем товар
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $product_id]);

        header('Location: ?tab=products');
        exit;
    }

    if ($action === 'create') {
        // Создание нового товара
        $data = [
            'category_id' => (int)($_POST['category_id'] ?? 1),
            'name' => $_POST['name'] ?? '',
            'slug' => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $_POST['name'] ?? '')),
            'price' => (float)($_POST['price'] ?? 0),
            'old_price' => !empty($_POST['old_price']) ? (float)$_POST['old_price'] : null,
            'stock' => (int)($_POST['stock'] ?? 0),
            'brand' => $_POST['brand'] ?? null,
            'sku' => $_POST['sku'] ?? null,
            'description' => $_POST['description'] ?? null,
            'main_image' => $_POST['main_image'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'meta_title' => $_POST['meta_title'] ?? null,
            'meta_description' => $_POST['meta_description'] ?? null,
            'meta_keywords' => $_POST['meta_keywords'] ?? null
        ];

        $stmt = $pdo->prepare("INSERT INTO products (
            category_id, name, slug, price, old_price, stock, brand, sku,
            description, is_active, meta_title, meta_description, meta_keywords, created_at
        ) VALUES (
            :category_id, :name, :slug, :price, :old_price, :stock, :brand, :sku,
            :description, :is_active, :meta_title, :meta_description, :meta_keywords, NOW()
        )");

        $stmt->execute([
            ':category_id' => $data['category_id'],
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':price' => $data['price'],
            ':old_price' => $data['old_price'],
            ':stock' => $data['stock'],
            ':brand' => $data['brand'],
            ':sku' => $data['sku'],
            ':description' => $data['description'],
            '' => $data['main_image'],
            ':is_active' => $data['is_active'],
            ':meta_title' => $data['meta_title'],
            ':meta_description' => $data['meta_description'],
            ':meta_keywords' => $data['meta_keywords']
        ]);

        $product_id = $pdo->lastInsertId();

        // Добавление главного изображения для нового товара
        if (isset($_POST['main_image']) && !empty($_POST['main_image']) && $product_id) {
            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_main, sort_order) VALUES (:product_id, :image_path, 1, 1)");
            $stmt->execute([
                ':product_id' => $product_id,
                ':image_path' => $_POST['main_image']
            ]);
        }

        // Добавление характеристик для нового товара
        if (isset($_POST['specifications']) && $product_id) {
            $stmt = $pdo->prepare("INSERT INTO product_specifications (product_id, spec_name, spec_value) VALUES (:product_id, :spec_name, :spec_value)");

            foreach ($_POST['specifications'] as $spec) {
                if (!empty($spec['name']) && !empty($spec['value'])) {
                    $stmt->execute([
                        ':product_id' => $product_id,
                        ':spec_name' => trim($spec['name']),
                        ':spec_value' => trim($spec['value'])
                    ]);
                }
            }
        }

        header('Location: ?tab=products');
        exit;
    }

    if ($action === 'update_order_status' && isset($_POST['order_id'])) {
        // Обновление статуса заказа
        $order_id = (int)$_POST['order_id'];
        $new_status = $_POST['status'] ?? '';

        $allowed_statuses = ['pending', 'processing', 'assembled', 'shipped', 'delivered', 'cancelled'];

        if (in_array($new_status, $allowed_statuses)) {
            $stmt = $pdo->prepare("UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id");
            $stmt->execute([
                ':status' => $new_status,
                ':id' => $order_id
            ]);
        }

        header('Location: ?tab=orders');
        exit;
    }
}

// Получаем статистику
$products = $product_model->getAll(100, 0);
$total_products = count($products);

$all_orders = $order_model->getAll(100, 0);
$total_orders = count($all_orders);
$active_orders = count(array_filter($all_orders, function($o) { return $o['status'] === 'pending' || $o['status'] === 'processing'; }));

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$total_revenue = array_sum(array_column($all_orders, 'total_amount'));

// Текущая вкладка
$current_tab = $_GET['tab'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-url" content="<?php echo APP_URL; ?>">
    <title>Админ-панель - PCComponents Store</title>
    <link rel="stylesheet" href="<?php echo \App\Helpers\Asset::css('style.css'); ?>">
</head>
<body>
    <?php include dirname(dirname(__FILE__)) . '/includes/header.php'; ?>

    <main>
        <div class="container">
            <div class="page-header">
                <h1>
                    <img src="<?php echo APP_URL; ?>/public/images/icons/admin_panel_settings.svg" alt="" width="32" height="32" class="page-icon">
                    Панель администратора
                </h1>
            </div>

            <div class="admin-container">
                <aside class="sticky-aside admin-sidebar">
                    <nav class="admin-nav">
                        <a href="?tab=dashboard" class="admin-nav-link <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
                            <img src="<?php echo APP_URL; ?>/public/images/icons/dashboard.svg" alt="" width="18" height="18">
                            Основное
                        </a>
                        <a href="?tab=products" class="admin-nav-link <?php echo $current_tab === 'products' ? 'active' : ''; ?>">
                            <img src="<?php echo APP_URL; ?>/public/images/icons/inventory.svg" alt="" width="18" height="18">
                            Товары
                        </a>
                        <a href="?tab=orders" class="admin-nav-link <?php echo $current_tab === 'orders' ? 'active' : ''; ?>">
                            <img src="<?php echo APP_URL; ?>/public/images/icons/shopping_bag.svg" alt="" width="18" height="18">
                            Заказы
                        </a>
                        <a href="?tab=users" class="admin-nav-link <?php echo $current_tab === 'users' ? 'active' : ''; ?>">
                            <img src="<?php echo APP_URL; ?>/public/images/icons/group.svg" alt="" width="18" height="18">
                            Пользователи
                        </a>
                    </nav>
                </aside>

                <section class="admin-content">
                    <?php if ($current_tab === 'dashboard'): ?>
                        <h2>Статистика</h2>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <img src="<?php echo APP_URL; ?>/public/images/icons/inventory.svg" alt="" width="24" height="24">
                                </div>
                                <div class="stat-content">
                                    <div class="stat-value"><?php echo $total_products; ?></div>
                                    <div class="stat-label">Всего товаров</div>
                                </div>
                            </div>

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
                                    <img src="<?php echo APP_URL; ?>/public/images/icons/schedule.svg" alt="" width="24" height="24">
                                </div>
                                <div class="stat-content">
                                    <div class="stat-value"><?php echo $active_orders; ?></div>
                                    <div class="stat-label">Активных заказов</div>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon">
                                    <img src="<?php echo APP_URL; ?>/public/images/icons/group.svg" alt="" width="24" height="24">
                                </div>
                                <div class="stat-content">
                                    <div class="stat-value"><?php echo $total_users; ?></div>
                                    <div class="stat-label">Пользователей</div>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon">
                                    <img src="<?php echo APP_URL; ?>/public/images/icons/payments.svg" alt="" width="24" height="24">
                                </div>
                                <div class="stat-content">
                                    <div class="stat-value"><?php echo number_format($total_revenue, 0, '', ' '); ?> ₽</div>
                                    <div class="stat-label">Общая выручка</div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($current_tab === 'products'): ?>
                        <div class="admin-header">
                            <h2>Управление товарами</h2>
                            <a href="?tab=products&action=add" class="btn">
                                <img src="<?php echo APP_URL; ?>/public/images/icons/add.svg" alt="" width="18" height="18">
                                Добавить товар
                            </a>
                        </div>

                        <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
                            <!-- Форма добавления товара -->
                            <div class="product-form-card">
                                <h3>Добавление товара</h3>
                                <form method="POST" action="?tab=products" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="create">
                                    <?php echo \App\Helpers\CSRF::getTokenField(); ?>

                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label>Название *</label>
                                            <input type="text" name="name" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Категория *</label>
                                            <select name="category_id" required>
                                                <?php
                                                $categories_stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
                                                while ($cat = $categories_stmt->fetch(PDO::FETCH_ASSOC)):
                                                ?>
                                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Цена *</label>
                                            <input type="number" name="price" step="0.01" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Старая цена</label>
                                            <input type="number" name="old_price" step="0.01">
                                        </div>

                                        <div class="form-group">
                                            <label>Остаток</label>
                                            <input type="number" name="stock" value="0">
                                        </div>

                                        <div class="form-group">
                                            <label>Бренд</label>
                                            <input type="text" name="brand">
                                        </div>

                                        <div class="form-group">
                                            <label>SKU</label>
                                            <input type="text" name="sku">
                                        </div>

                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" name="is_active" value="1" checked>
                                                Активен
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Главное изображение</label>
                                        <div class="custom-select-wrapper">
                                            <input type="hidden" name="main_image" id="selected-image-add" value="">
                                            <div class="custom-select" onclick="toggleDropdown('dropdown-add')">
                                                <div class="custom-select-trigger">
                                                    <div class="selected-option" id="selected-option-add">
                                                        <span class="placeholder">Выберите изображение</span>
                                                    </div>
                                                    <span class="arrow">▼</span>
                                                </div>
                                                <div class="custom-options" id="dropdown-add">
                                                    <?php
                                                    $images_dir = dirname(dirname(dirname(__DIR__))) . '/public/images/products/';
                                                    $images = array_diff(scandir($images_dir), ['.', '..']);
                                                    foreach ($images as $image):
                                                        if (in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])):
                                                    ?>
                                                        <div class="custom-option" onclick="selectImageOption('<?php echo htmlspecialchars($image, ENT_QUOTES); ?>', 'add', event)">
                                                            <img src="<?php echo APP_URL; ?>/public/images/products/<?php echo htmlspecialchars($image); ?>" alt="">
                                                            <span><?php echo htmlspecialchars($image); ?></span>
                                                        </div>
                                                    <?php
                                                        endif;
                                                    endforeach;
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Описание</label>
                                        <textarea name="description" rows="3"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>SEO: Meta Title</label>
                                        <input type="text" name="meta_title">
                                    </div>

                                    <div class="form-group">
                                        <label>SEO: Meta Description</label>
                                        <textarea name="meta_description" rows="2"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>SEO: Keywords</label>
                                        <input type="text" name="meta_keywords">
                                    </div>

                                    <div class="form-group">
                                        <label>Характеристики товара</label>
                                        <div id="specifications-container-add">
                                        </div>
                                        <button type="button" class="btn btn-outline" onclick="addSpecificationForCreate()">
                                            <img src="<?php echo APP_URL; ?>/public/images/icons/add.svg" alt="" width="18" height="18">
                                            Добавить характеристику
                                        </button>

                                        <datalist id="spec-names-list-add">
                                            <?php
                                            // Получаем уникальные названия характеристик из всех товаров
                                            $stmt = $pdo->query("SELECT DISTINCT spec_name FROM product_specifications ORDER BY spec_name");
                                            while ($spec_name = $stmt->fetch(PDO::FETCH_ASSOC)):
                                            ?>
                                                <option value="<?php echo htmlspecialchars($spec_name['spec_name']); ?>">
                                            <?php endwhile; ?>
                                        </datalist>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn">Создать</button>
                                        <a href="?tab=products" class="btn btn-outline">Отмена</a>
                                    </div>
                                </form>
                            </div>
                        <?php elseif (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])):
                            $edit_product = $product_model->getById($_GET['id']);
                            $specifications = $product_model->getSpecifications($_GET['id']);

                            // Получаем главное изображение товара
                            $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = :product_id AND is_main = 1 LIMIT 1");
                            $stmt->execute([':product_id' => $_GET['id']]);
                            $main_image_data = $stmt->fetch(PDO::FETCH_ASSOC);
                            $edit_product['main_image'] = $main_image_data['image_path'] ?? '';
                        ?>
                            <!-- Форма редактирования товара -->
                            <div class="product-form-card">
                                <h3>Редактирование товара</h3>
                                <form method="POST" action="?tab=products" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                                    <?php echo \App\Helpers\CSRF::getTokenField(); ?>

                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label>Название *</label>
                                            <input type="text" name="name" value="<?php echo htmlspecialchars($edit_product['name']); ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Цена *</label>
                                            <input type="number" name="price" step="0.01" value="<?php echo $edit_product['price']; ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Старая цена</label>
                                            <input type="number" name="old_price" step="0.01" value="<?php echo $edit_product['old_price'] ?? ''; ?>">
                                        </div>

                                        <div class="form-group">
                                            <label>Остаток</label>
                                            <input type="number" name="stock" value="<?php echo $edit_product['stock']; ?>">
                                        </div>

                                        <div class="form-group">
                                            <label>Бренд</label>
                                            <input type="text" name="brand" value="<?php echo htmlspecialchars($edit_product['brand'] ?? ''); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label>SKU</label>
                                            <input type="text" name="sku" value="<?php echo htmlspecialchars($edit_product['sku'] ?? ''); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" name="is_active" value="1" <?php echo $edit_product['is_active'] ? 'checked' : ''; ?>>
                                                Активен
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Главное изображение</label>
                                        <div class="custom-select-wrapper">
                                            <input type="hidden" name="main_image" id="selected-image-edit" value="<?php echo htmlspecialchars($edit_product['main_image'] ?? ''); ?>">
                                            <div class="custom-select" onclick="toggleDropdown('dropdown-edit')">
                                                <div class="custom-select-trigger">
                                                    <div class="selected-option" id="selected-option-edit">
                                                        <?php if (!empty($edit_product['main_image'])): ?>
                                                            <img src="<?php echo APP_URL; ?>/public/images/products/<?php echo htmlspecialchars($edit_product['main_image']); ?>" alt="">
                                                            <span><?php echo htmlspecialchars($edit_product['main_image']); ?></span>
                                                        <?php else: ?>
                                                            <span class="placeholder">Выберите изображение</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <span class="arrow">▼</span>
                                                </div>
                                                <div class="custom-options" id="dropdown-edit">
                                                    <?php
                                                    $images_dir = dirname(dirname(dirname(__DIR__))) . '/public/images/products/';
                                                    $images = array_diff(scandir($images_dir), ['.', '..']);
                                                    foreach ($images as $image):
                                                        if (in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])):
                                                            $selected_class = ($edit_product['main_image'] ?? '') === $image ? 'selected' : '';
                                                    ?>
                                                        <div class="custom-option <?php echo $selected_class; ?>" onclick="selectImageOption('<?php echo htmlspecialchars($image, ENT_QUOTES); ?>', 'edit', event)">
                                                            <img src="<?php echo APP_URL; ?>/public/images/products/<?php echo htmlspecialchars($image); ?>" alt="">
                                                            <span><?php echo htmlspecialchars($image); ?></span>
                                                        </div>
                                                    <?php
                                                        endif;
                                                    endforeach;
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Описание</label>
                                        <textarea name="description" rows="3"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>SEO: Meta Title</label>
                                        <input type="text" name="meta_title" value="<?php echo htmlspecialchars($edit_product['meta_title'] ?? ''); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>SEO: Meta Description</label>
                                        <textarea name="meta_description" rows="2"><?php echo htmlspecialchars($edit_product['meta_description'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>SEO: Keywords</label>
                                        <input type="text" name="meta_keywords" value="<?php echo htmlspecialchars($edit_product['meta_keywords'] ?? ''); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>Характеристики товара</label>
                                        <div id="specifications-container">
                                            <?php if (!empty($specifications)): ?>
                                                <?php foreach ($specifications as $index => $spec): ?>
                                                    <div class="specification-row" data-index="<?php echo $index; ?>">
                                                        <input type="text"
                                                               name="specifications[<?php echo $index; ?>][name]"
                                                               value="<?php echo htmlspecialchars($spec['spec_name']); ?>"
                                                               placeholder="Название характеристики"
                                                               class="spec-name-input"
                                                               list="spec-names-list">
                                                        <input type="text"
                                                               name="specifications[<?php echo $index; ?>][value]"
                                                               value="<?php echo htmlspecialchars($spec['spec_value']); ?>"
                                                               placeholder="Значение">
                                                        <button type="button" class="btn btn-sm btn-destructive" onclick="removeSpecification(this)">
                                                            <img src="<?php echo APP_URL; ?>/public/images/icons/delete.svg" alt="" width="14" height="14">
                                                        </button>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <button type="button" class="btn btn-outline" onclick="addSpecification()">
                                            <img src="<?php echo APP_URL; ?>/public/images/icons/add.svg" alt="" width="18" height="18">
                                            Добавить характеристику
                                        </button>

                                        <datalist id="spec-names-list">
                                            <?php
                                            // Получаем уникальные названия характеристик из всех товаров
                                            $stmt = $pdo->query("SELECT DISTINCT spec_name FROM product_specifications ORDER BY spec_name");
                                            while ($spec_name = $stmt->fetch(PDO::FETCH_ASSOC)):
                                            ?>
                                                <option value="<?php echo htmlspecialchars($spec_name['spec_name']); ?>">
                                            <?php endwhile; ?>
                                        </datalist>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn">Сохранить</button>
                                        <a href="?tab=products" class="btn btn-outline">Отмена</a>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                        <div class="data-table-wrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Название</th>
                                        <th>Цена</th>
                                        <th>Остаток</th>
                                        <th>Бренд</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?php echo $product['id']; ?></td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/product/<?php echo $product['id']; ?>" class="table-link">
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo number_format($product['price'], 2, '.', ' '); ?> ₽</td>
                                            <td>
                                                <?php if ($product['stock'] > 10): ?>
                                                    <span class="badge badge-success"><?php echo $product['stock']; ?></span>
                                                <?php elseif ($product['stock'] > 0): ?>
                                                    <span class="badge badge-warning"><?php echo $product['stock']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-destructive">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['brand'] ?? '-'); ?></td>
                                            <td>
                                                <?php if ($product['is_active']): ?>
                                                    <span class="badge badge-success">Активен</span>
                                                <?php else: ?>
                                                    <span class="badge">Неактивен</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="?tab=products&action=edit&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline">
                                                        <img src="<?php echo APP_URL; ?>/public/images/icons/edit.svg" alt="" width="14" height="14">
                                                        Редактировать
                                                    </a>
                                                    <form method="POST" action="?tab=products" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                                        <?php echo \App\Helpers\CSRF::getTokenField(); ?>
                                                        <button type="submit" class="btn btn-sm btn-destructive" onclick="return confirm('Удалить товар?')">
                                                            <img src="<?php echo APP_URL; ?>/public/images/icons/delete.svg" alt="" width="14" height="14">
                                                            Удалить
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                    <?php elseif ($current_tab === 'orders'): ?>
                        <div class="admin-header">
                            <h2>Управление заказами</h2>
                        </div>

                        <?php if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])):
                            $order_id = (int)$_GET['id'];
                            $stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = :id");
                            $stmt->execute([':id' => $order_id]);
                            $order = $stmt->fetch(PDO::FETCH_ASSOC);

                            if ($order):
                                $stmt = $pdo->prepare("SELECT oi.*, p.name, p.main_image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :order_id");
                                $stmt->execute([':order_id' => $order_id]);
                                $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                            <div class="product-form-card">
                                <h3>Заказ <?php echo htmlspecialchars($order['order_number']); ?></h3>

                                <div class="order-details">
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label>Клиент</label>
                                            <p><?php echo htmlspecialchars($order['username'] ?? 'Не указан'); ?> (<?php echo htmlspecialchars($order['email'] ?? ''); ?>)</p>
                                        </div>

                                        <div class="form-group">
                                            <label>Дата создания</label>
                                            <p><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></p>
                                        </div>

                                        <div class="form-group">
                                            <label>Метод доставки</label>
                                            <p><?php
                                                $delivery_methods = [
                                                    'pickup' => 'Самовывоз',
                                                    'courier' => 'Курьер',
                                                    'mail' => 'Почта'
                                                ];
                                                echo $delivery_methods[$order['delivery_method']] ?? $order['delivery_method'];
                                            ?></p>
                                        </div>

                                        <div class="form-group">
                                            <label>Метод оплаты</label>
                                            <p><?php
                                                $payment_methods = [
                                                    'card' => 'Карта',
                                                    'cash' => 'Наличные',
                                                    'online' => 'Онлайн'
                                                ];
                                                echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                                            ?></p>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Адрес доставки</label>
                                        <p><?php echo htmlspecialchars($order['delivery_address'] ?? 'Не указан'); ?></p>
                                    </div>

                                    <?php if (!empty($order['notes'])): ?>
                                    <div class="form-group">
                                        <label>Примечания</label>
                                        <p><?php echo htmlspecialchars($order['notes']); ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <div class="form-group">
                                        <label>Товары в заказе</label>
                                        <div class="data-table-wrapper">
                                            <table class="data-table">
                                                <thead>
                                                    <tr>
                                                        <th>Товар</th>
                                                        <th>Количество</th>
                                                        <th>Цена</th>
                                                        <th>Итого</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($order_items as $item): ?>
                                                        <tr>
                                                            <td>
                                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                                    <?php if ($item['main_image']): ?>
                                                                        <img src="<?php echo APP_URL; ?>/public/images/products/<?php echo htmlspecialchars($item['main_image']); ?>"
                                                                             alt="" style="width: 40px; height: 40px; object-fit: cover;">
                                                                    <?php endif; ?>
                                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                                </div>
                                                            </td>
                                                            <td><?php echo $item['quantity']; ?></td>
                                                            <td><?php echo number_format($item['unit_price'], 2, '.', ' '); ?> ₽</td>
                                                            <td><?php echo number_format($item['total_price'], 2, '.', ' '); ?> ₽</td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    <tr style="font-weight: bold;">
                                                        <td colspan="3" style="text-align: right;">Общая сумма:</td>
                                                        <td><?php echo number_format($order['total_amount'], 2, '.', ' '); ?> ₽</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <form method="POST" action="?tab=orders">
                                        <input type="hidden" name="action" value="update_order_status">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <?php echo \App\Helpers\CSRF::getTokenField(); ?>

                                        <div class="form-group">
                                            <label>Изменить статус заказа</label>
                                            <select name="status" class="status-select">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>В обработке</option>
                                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Обрабатывается</option>
                                                <option value="assembled" <?php echo $order['status'] === 'assembled' ? 'selected' : ''; ?>>Собран</option>
                                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Отправлен</option>
                                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Доставлен</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Отменён</option>
                                            </select>
                                        </div>

                                        <div class="form-actions">
                                            <button type="submit" class="btn">Обновить статус</button>
                                            <a href="?tab=orders" class="btn btn-outline">Назад к списку</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php
                            endif;
                        else:
                        ?>
                        <div class="data-table-wrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Номер заказа</th>
                                        <th>Клиент</th>
                                        <th>Дата</th>
                                        <th>Сумма</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_orders as $order): ?>
                                        <tr>
                                            <td><?php echo $order['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                            <td><?php
                                                $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
                                                $stmt->execute([':id' => $order['user_id']]);
                                                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                                                echo htmlspecialchars($user['username'] ?? 'Гость');
                                            ?></td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo number_format($order['total_amount'], 2, '.', ' '); ?> ₽</td>
                                            <td>
                                                <?php
                                                $status_map = [
                                                    'pending' => ['label' => 'В обработке', 'class' => 'badge-warning'],
                                                    'processing' => ['label' => 'Обрабатывается', 'class' => 'badge-info'],
                                                    'assembled' => ['label' => 'Собран', 'class' => 'badge-secondary'],
                                                    'shipped' => ['label' => 'Отправлен', 'class' => 'badge-secondary'],
                                                    'delivered' => ['label' => 'Доставлен', 'class' => 'badge-success'],
                                                    'cancelled' => ['label' => 'Отменён', 'class' => 'badge-destructive'],
                                                ];
                                                $status = $status_map[$order['status']] ?? ['label' => $order['status'], 'class' => ''];
                                                ?>
                                                <span class="badge <?php echo $status['class']; ?>">
                                                    <?php echo $status['label']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?tab=orders&action=view&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">
                                                    <img src="<?php echo APP_URL; ?>/public/images/icons/visibility.svg" alt="" width="14" height="14">
                                                    Просмотр
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                    <?php elseif ($current_tab === 'users'): ?>
                        <div class="admin-header">
                            <h2>Управление пользователями</h2>
                        </div>

                        <div class="data-table-wrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Email</th>
                                        <th>Имя пользователя</th>
                                        <th>Роль</th>
                                        <th>Дата регистрации</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
                                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($users as $user):
                                    ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td>
                                                <?php if ($user['role'] === 'admin'): ?>
                                                    <span class="badge badge-destructive">Администратор</span>
                                                <?php elseif ($user['role'] === 'manager'): ?>
                                                    <span class="badge badge-secondary">Менеджер</span>
                                                <?php else: ?>
                                                    <span class="badge">Клиент</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>

    <?php include dirname(dirname(__FILE__)) . '/includes/footer.php'; ?>

    <script>
    let specificationIndex = <?php echo isset($specifications) ? count($specifications) : 0; ?>;
    let specificationIndexAdd = 0;

    function toggleDropdown(dropdownId) {
        const dropdown = document.getElementById(dropdownId);
        const allDropdowns = document.querySelectorAll('.custom-options');

        // Закрываем все другие дропдауны
        allDropdowns.forEach(d => {
            if (d.id !== dropdownId) {
                d.classList.remove('open');
            }
        });

        // Переключаем текущий
        dropdown.classList.toggle('open');
        event.stopPropagation();
    }

    function selectImageOption(imageName, formType, e) {
        e.stopPropagation();

        const hiddenInput = document.getElementById('selected-image-' + formType);
        const selectedOption = document.getElementById('selected-option-' + formType);
        const dropdown = document.getElementById('dropdown-' + formType);

        // Обновляем скрытое поле
        hiddenInput.value = imageName;

        // Обновляем отображение выбранного элемента
        selectedOption.innerHTML = `
            <img src="<?php echo APP_URL; ?>/public/images/products/${encodeURIComponent(imageName)}" alt="">
            <span>${imageName}</span>
        `;

        // Убираем класс selected со всех опций
        const allOptions = dropdown.querySelectorAll('.custom-option');
        allOptions.forEach(opt => opt.classList.remove('selected'));

        // Добавляем класс selected к выбранной опции
        e.currentTarget.classList.add('selected');

        // Закрываем dropdown
        dropdown.classList.remove('open');
    }

    // Закрытие dropdown при клике вне его
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.custom-select')) {
            document.querySelectorAll('.custom-options').forEach(dropdown => {
                dropdown.classList.remove('open');
            });
        }
    });

    // Функции для управления характеристиками (форма редактирования)
    function addSpecification() {
        const container = document.getElementById('specifications-container');
        const newRow = document.createElement('div');
        newRow.className = 'specification-row';
        newRow.setAttribute('data-index', specificationIndex);

        newRow.innerHTML = `
            <input type="text"
                   name="specifications[${specificationIndex}][name]"
                   placeholder="Название характеристики"
                   class="spec-name-input"
                   list="spec-names-list">
            <input type="text"
                   name="specifications[${specificationIndex}][value]"
                   placeholder="Значение">
            <button type="button" class="btn btn-sm btn-destructive" onclick="removeSpecification(this)">
                <img src="<?php echo APP_URL; ?>/public/images/icons/delete.svg" alt="" width="14" height="14">
            </button>
        `;

        container.appendChild(newRow);
        specificationIndex++;
    }

    function removeSpecification(button) {
        button.closest('.specification-row').remove();
    }

    // Функции для управления характеристиками (форма создания)
    function addSpecificationForCreate() {
        const container = document.getElementById('specifications-container-add');
        const newRow = document.createElement('div');
        newRow.className = 'specification-row';
        newRow.setAttribute('data-index', specificationIndexAdd);

        newRow.innerHTML = `
            <input type="text"
                   name="specifications[${specificationIndexAdd}][name]"
                   placeholder="Название характеристики"
                   class="spec-name-input"
                   list="spec-names-list-add">
            <input type="text"
                   name="specifications[${specificationIndexAdd}][value]"
                   placeholder="Значение">
            <button type="button" class="btn btn-sm btn-destructive" onclick="removeSpecification(this)">
                <img src="<?php echo APP_URL; ?>/public/images/icons/delete.svg" alt="" width="14" height="14">
            </button>
        `;

        container.appendChild(newRow);
        specificationIndexAdd++;
    }
    </script>
</body>
</html>
