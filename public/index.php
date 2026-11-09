<?php

// Запуск сессии
session_start();

// Установка кодировки
header('Content-Type: text/html; charset=utf-8');

// Определяем базовый путь
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));

// Автозагрузка классов
spl_autoload_register(function ($class) {
    $prefix = 'App\\';

    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $parts = explode('\\', substr($class, strlen($prefix)));
    $parts[0] = strtolower($parts[0]);
    $file = BASE_PATH . '/src/' . implode('/', $parts) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Подключение конфигурации
require_once BASE_PATH . '/src/config/Config.php';

// Подключение к БД
$db = new \App\Config\Database();
$pdo = $db->connect();

if (!$pdo) {
    die('Database connection failed');
}

// Проверка сессии
if (\App\Helpers\Auth::isLoggedIn()) {
    \App\Helpers\Auth::checkSessionTimeout();
}

// Определение маршрута
$request_method = $_SERVER['REQUEST_METHOD'];

// Получаем путь из url параметра (от .htaccess) или из REQUEST_URI
if (!empty($_GET['url'])) {
    $path = '/' . $_GET['url'];
} else {
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $base_path = '/course-work-PC-components-store';

    // Удаляем /public/index.php если это прямой доступ
    if (strpos($request_uri, '/public/index.php') !== false) {
        $path = str_replace('/public/index.php', '', $request_uri);
        // Также удаляем базовый путь если он есть
        if (strpos($path, $base_path) === 0) {
            $path = substr($path, strlen($base_path));
        }
    } else {
        // Удаление базовой части пути для доступа через .htaccess
        if (strpos($request_uri, $base_path) === 0) {
            $path = substr($request_uri, strlen($base_path));
        } else {
            $path = $request_uri;
        }
    }
}

$path = rtrim($path, '/') ?: '/';

if ($path === '/sitemap.xml' || $path === '/robots.txt') {
    if (!in_array($request_method, ['GET', 'HEAD'], true)) { http_response_code(405); header('Allow: GET, HEAD'); exit; }
    include BASE_PATH . '/src/views/' . ($path === '/sitemap.xml' ? 'sitemap.php' : 'robots.php');
    exit;
}

// Маршруты API
if (strpos($path, '/api/') === 0) {
    if ($path === '/api/search' && $request_method === 'GET') {
        $controller = new \App\Controllers\ProductController($pdo);
        $controller->search();
        exit;
    }

    if ($path === '/api/reviews/add' && $request_method === 'POST') {
        $controller = new \App\Controllers\ReviewController($pdo);
        $controller->addReview();
        exit;
    }

    if ($path === '/api/reviews/update' && $request_method === 'POST') {
        $controller = new \App\Controllers\ReviewController($pdo);
        $controller->updateReview();
        exit;
    }

    if ($path === '/api/reviews/delete' && $request_method === 'POST') {
        $controller = new \App\Controllers\ReviewController($pdo);
        $controller->deleteReview();
        exit;
    }

    if ($path === '/api/cart' && $request_method === 'GET') {
        $controller = new \App\Controllers\CartController($pdo);
        $controller->getItems();
        exit;
    }

    if ($path === '/api/cart/add' && $request_method === 'POST') {
        $controller = new \App\Controllers\CartController($pdo);
        $controller->add();
        exit;
    }

    if ($path === '/api/cart/count' && $request_method === 'GET') {
        $controller = new \App\Controllers\CartController($pdo);
        $controller->getCount();
        exit;
    }

    if ($path === '/api/cart/summary' && $request_method === 'GET') {
        $controller = new \App\Controllers\CartController($pdo);
        $controller->getSummary();
        exit;
    }

    if (preg_match('/^\/api\/cart\/(\d+)$/', $path, $matches)) {
        $_GET['id'] = $matches[1];
        if ($request_method === 'DELETE') {
            $controller = new \App\Controllers\CartController($pdo);
            $controller->remove();
        } elseif ($request_method === 'POST') {
            $controller = new \App\Controllers\CartController($pdo);
            $controller->update();
        }
        exit;
    }

    if ($path === '/api/products' && $request_method === 'GET') {
        $controller = new \App\Controllers\ProductController($pdo);
        $controller->getAll();
        exit;
    }

    if (preg_match('/^\/api\/products\/(\d+)$/', $path, $matches)) {
        $_GET['id'] = $matches[1];
        $controller = new \App\Controllers\ProductController($pdo);
        $controller->getById();
        exit;
    }

    if ($path === '/api/products' && $request_method === 'POST') {
        $controller = new \App\Controllers\ProductController($pdo);
        $controller->create();
        exit;
    }

    if ($path === '/api/auth/register' && $request_method === 'POST') {
        $controller = new \App\Controllers\AuthController($pdo);
        $controller->register();
        exit;
    }

    if ($path === '/api/auth/login' && $request_method === 'POST') {
        $controller = new \App\Controllers\AuthController($pdo);
        $controller->login();
        exit;
    }

    if ($path === '/api/orders/create' && $request_method === 'POST') {
        $controller = new \App\Controllers\OrderController($pdo);
        $controller->create();
        exit;
    }

    if ($path === '/api/orders/status' && $request_method === 'POST') {
        $controller = new \App\Controllers\OrderController($pdo);
        $controller->updateStatus();
        exit;
    }

    if (preg_match('/^\/api\/orders\/(\d+)$/', $path, $matches)) {
        $_GET['id'] = $matches[1];
        $controller = new \App\Controllers\OrderController($pdo);
        $controller->getById();
        exit;
    }

    if ($path === '/api/orders/user' && $request_method === 'GET') {
        $controller = new \App\Controllers\OrderController($pdo);
        $controller->getUserOrders();
        exit;
    }

    // Filtering API endpoints
    if (preg_match('/^\/api\/specifications\/(\d+)$/', $path, $matches) && $request_method === 'GET') {
        header('Content-Type: application/json');
        $category_id = (int)$matches[1];
        $productModel = new \App\Models\Product($pdo);
        $specifications = $productModel->getAvailableSpecifications($category_id);
        echo json_encode(['success' => true, 'specifications' => $specifications]);
        exit;
    }

    if (preg_match('/^\/api\/brands\/(\d+)$/', $path, $matches) && $request_method === 'GET') {
        header('Content-Type: application/json');
        $category_id = (int)$matches[1];
        $productModel = new \App\Models\Product($pdo);
        $brands = $productModel->getAvailableBrands($category_id);
        echo json_encode(['success' => true, 'brands' => $brands]);
        exit;
    }

    if ($path === '/api/products/filter' && $request_method === 'POST') {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);

        $filters = [];
        if (isset($input['search']) && is_string($input['search'])) {
            $filters['search'] = trim($input['search']);
        }
        if (!empty($input['category_id'])) {
            $filters['category_id'] = (int)$input['category_id'];
        }
        if (!empty($input['price_min'])) {
            $filters['price_min'] = (float)$input['price_min'];
        }
        if (!empty($input['price_max'])) {
            $filters['price_max'] = (float)$input['price_max'];
        }
        if (!empty($input['brand'])) {
            $filters['brand'] = $input['brand'];
        }
        if (!empty($input['specifications']) && is_array($input['specifications'])) {
            $filters['specifications'] = $input['specifications'];
        }

        $limit = !empty($input['limit']) ? (int)$input['limit'] : 100;
        $offset = !empty($input['offset']) ? (int)$input['offset'] : 0;

        $productModel = new \App\Models\Product($pdo);
        $products = $productModel->getWithFilters($filters, $limit, $offset);

        echo json_encode([
            'success' => true,
            'products' => $products,
            'count' => count($products)
        ]);
        exit;
    }

    header('HTTP/1.1 404 Not Found');
    echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
    exit;
}

// Одна серверная обработка для обычных форм и AJAX-запросов.
if ($request_method === 'POST') {
    if ($path === '/cart/add') {
        $controller = new \App\Controllers\CartController($pdo);
        $controller->add(\App\Helpers\Request::localPath($_POST['return_to'] ?? '', '/cart'));
        exit;
    }
    if (preg_match('/^\/cart\/(\d+)\/(update|delete)$/', $path, $matches)) {
        $_GET['id'] = $matches[1];
        $controller = new \App\Controllers\CartController($pdo);
        $cart_redirect = \App\Helpers\Request::localPath($_POST['return_to'] ?? '', '/cart');
        if ($matches[2] === 'delete') {
            $controller->remove($cart_redirect);
        } else {
            $controller->update($cart_redirect);
        }
        exit;
    }
    if ($path === '/checkout') {
        $controller = new \App\Controllers\OrderController($pdo);
        $controller->create('/checkout');
        exit;
    }
    if (preg_match('~^/product/([a-z0-9-]+(?:/[a-z0-9-]+)+)/reviews$~', $path, $matches)) {
        $product = (new \App\Models\Product($pdo))->getByPath($matches[1]);
        if (!$product) {
            http_response_code(404);
            include BASE_PATH . '/src/views/404.php';
            exit;
        }
        $action = \App\Helpers\Request::text($_POST, 'action');
        $product_id = (int)$product['id'];
        $_POST['product_id'] = $product_id;
        $redirect = \App\Helpers\Url::productPath($product, $pdo) . '?tab=reviews#reviews';
        $controller = new \App\Controllers\ReviewController($pdo);
        if ($action === 'add') {
            $controller->addReview($redirect);
        } elseif ($action === 'update') {
            $controller->updateReview($redirect);
        } elseif ($action === 'delete') {
            $controller->deleteReview($redirect);
        } else {
            \App\Helpers\Response::send(['success' => false, 'message' => 'Неизвестное действие с отзывом'], 400, $redirect);
        }
        exit;
    }
}

// Веб-маршруты
if ($path === '/') {
    include BASE_PATH . '/src/views/home.php';
} elseif ($path === '/catalog') {
    include BASE_PATH . '/src/views/catalog.php';
} elseif ($path === '/auth') {
    include BASE_PATH . '/src/views/auth.php';
} elseif ($path === '/cart') {
    include BASE_PATH . '/src/views/cart.php';
} elseif ($path === '/checkout') {
    include BASE_PATH . '/src/views/checkout.php';
} elseif ($path === '/order-success') {
    include BASE_PATH . '/src/views/order-success.php';
} elseif ($path === '/logout') {
    $controller = new \App\Controllers\AuthController($pdo);
    $controller->logout();
} elseif (preg_match('~^/product/([a-z0-9-]+(?:/[a-z0-9-]+)+)$~', $path, $matches)) {
    $_GET['product_path'] = $matches[1];
    include BASE_PATH . '/src/views/product.php';
} elseif ($path === '/account') {
    if (!\App\Helpers\Auth::isLoggedIn()) {
        header('Location: ' . APP_URL . '/auth');
        exit;
    }
    include BASE_PATH . '/src/views/account.php';
} elseif ($path === '/admin') {
    if (!\App\Helpers\Auth::canManageStore()) {
        if (\App\Helpers\Request::isAjax()) {
            \App\Helpers\Response::send(['success' => false, 'message' => 'Необходимо войти как менеджер или администратор'], 403);
            exit;
        }
        header('Location: ' . APP_URL . '/');
        exit;
    }
    include BASE_PATH . '/src/views/admin/dashboard.php';
} elseif (preg_match('~^/category/([a-z0-9-]+(?:/[a-z0-9-]+)*)$~', $path, $matches)) {
    $_GET['slug'] = $matches[1];
    include BASE_PATH . '/src/views/category.php';
} else {
    header('HTTP/1.1 404 Not Found');
    include BASE_PATH . '/src/views/404.php';
}
