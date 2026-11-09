<?php

namespace App\Controllers;

use App\Models\Product;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Request;
use App\Helpers\Response;

class ProductController {
    private $db;
    private $product_model;

    public function __construct($db) {
        $this->db = $db;
        $this->product_model = new Product($db);
    }

    private function validateMethod($method) {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            header('Allow: ' . $method);
            Response::send(['success' => false, 'message' => 'Недопустимый метод запроса'], 405);
            return false;
        }
        return true;
    }

    private function validateMutation($data) {
        if (!$this->validateMethod('POST')) {
            return false;
        }
        if (!Auth::canManageStore()) {
            Response::send(['success' => false, 'message' => 'Управлять товарами может только менеджер или администратор'], 403);
            return false;
        }
        if (!CSRF::validateToken($data['csrf_token'] ?? '')) {
            Response::send(['success' => false, 'message' => 'Ошибка безопасности. Обновите страницу'], 403);
            return false;
        }
        return true;
    }

    public function getAll() {
        if (!$this->validateMethod('GET')) {
            return;
        }
        $limit = Request::integer($_GET, 'limit', 20);
        $offset = Request::integer($_GET, 'offset');
        if ($limit < 1 || $limit > 100 || $offset < 0 || $offset > 1000000) {
            Response::send(['success' => false, 'message' => 'Укажите лимит от 1 до 100 и неотрицательное смещение до 1000000'], 400);
            return;
        }
        try {
            $products = $this->product_model->getAll($limit, $offset);
            Response::send(['success' => true, 'products' => $products, 'count' => count($products)]);
        } catch (\PDOException $exception) {
            Response::send(['success' => false, 'message' => 'Каталог временно недоступен'], 500);
        }
    }

    public function getById() {
        if (!$this->validateMethod('GET')) {
            return;
        }
        $id = Request::integer($_GET, 'id');
        try {
            $product = $id > 0 ? $this->product_model->getById($id) : null;
            if (!$product) {
                Response::send(['success' => false, 'message' => 'Товар не найден'], 404);
                return;
            }
            Response::send([
                'success' => true, 'product' => $product,
                'specifications' => $this->product_model->getSpecifications($id),
                'images' => $this->product_model->getImages($id),
                'reviews' => $this->product_model->getReviews($id)
            ]);
        } catch (\PDOException $exception) {
            Response::send(['success' => false, 'message' => 'Не удалось загрузить товар'], 500);
        }
    }

    public function search() {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $query = $_GET['q'] ?? '';

        if (!is_string($query) || preg_match('/\A.{0,120}\z/us', $query) !== 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Запрос должен содержать не более 120 символов'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $query = trim(preg_replace('/\s+/u', ' ', $query));
        try {
            $results = \App\Helpers\Search::results($this->db, $query);
            $products = array_map(function ($product) {
                return ['url' => \App\Helpers\Url::product($product, $this->db)] + array_intersect_key($product, array_flip([
                    'id', 'name', 'price', 'brand', 'stock', 'main_image', 'category_name'
                ]));
            }, $results['products']);
            $categories = array_map(function ($category) {
                return ['url' => \App\Helpers\Url::category($category, $this->db)] + array_intersect_key($category, array_flip(['id', 'name', 'slug']));
            }, $results['categories']);

            $pdo = $this->db;
            $header_search_query = $query;
            $header_search_results = $results;
            ob_start();
            include BASE_PATH . '/src/views/includes/search-results.php';
            $html = ob_get_clean();

            echo json_encode([
                'success' => true,
                'products' => $products,
                'categories' => $categories,
                'html' => $html
            ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\PDOException $exception) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Поиск временно недоступен'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function create() {
        $data = Request::input();
        if (!$this->validateMutation($data)) {
            return;
        }
        // API и HTML-формы используют одну серверную валидацию и транзакционное сохранение.
        $data += ['category_id' => 1, 'stock' => 0, 'is_active' => '1'];
        foreach (['price', 'old_price'] as $field) {
            if (isset($data[$field]) && (is_int($data[$field]) || is_float($data[$field]))) {
                $data[$field] = (string)$data[$field];
            }
        }
        try {
            $id = (new AdminProductController($this->db))->save($data);
            Response::send(['success' => true, 'product_id' => $id]);
        } catch (\InvalidArgumentException $exception) {
            Response::send(['success' => false, 'message' => $exception->getMessage()], 400);
        } catch (\PDOException $exception) {
            Response::send(['success' => false, 'message' => 'Не удалось создать товар'], 500);
        }
    }

    public function delete() {
        $data = Request::input();
        if (!$this->validateMutation($data)) {
            return;
        }
        try {
            $result = (new AdminProductController($this->db))->delete(Request::integer($data, 'id'));
            Response::send(['success' => $result]);
        } catch (\InvalidArgumentException $exception) {
            Response::send(['success' => false, 'message' => $exception->getMessage()], 400);
        } catch (\PDOException $exception) {
            Response::send(['success' => false, 'message' => 'Не удалось снять товар с продажи'], 500);
        }
    }
}
