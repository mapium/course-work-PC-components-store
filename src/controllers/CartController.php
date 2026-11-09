<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Cart as CartModel;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Request;
use App\Helpers\Response;

class CartController {
    private $db;
    private $cart_model;

    public function __construct($db) {
        $this->db = $db;
        $this->cart_model = new CartModel($db);
    }

    private function cartState($item) {
        return [
            'cart_count' => (int)$this->cart_model->getCount(Auth::getCurrentUserId(), session_id()),
            'cart_item' => [
                'id' => (int)$item['id'], 'product_id' => (int)$item['product_id'],
                'quantity' => (int)$item['quantity'], 'stock' => (int)$item['stock']
            ]
        ];
    }

    private function validateRequest($data, $redirect, $methods = ['POST']) {
        if (!in_array($_SERVER['REQUEST_METHOD'], $methods, true)) {
            Response::send(['success' => false, 'message' => 'Недопустимый метод запроса'], 405, $redirect);
            return false;
        }
        if (!CSRF::validateToken($data['csrf_token'] ?? '')) {
            Response::send(['success' => false, 'message' => 'Ошибка безопасности. Обновите страницу и повторите действие'], 403, $redirect);
            return false;
        }
        return true;
    }

    public function add($redirect = null) {
        $data = Request::input();
        if (!$this->validateRequest($data, $redirect)) {
            return;
        }
        $product_id = Request::integer($data, 'product_id');
        $quantity = array_key_exists('quantity', $data) ? Request::integer($data, 'quantity') : 1;
        if ($product_id <= 0 || $quantity < 1) {
            Response::send(['success' => false, 'message' => 'Укажите товар и положительное целое количество'], 400, $redirect);
            return;
        }

        try {
            $product = (new Product($this->db))->getById($product_id);
            if (!$product) {
                Response::send(['success' => false, 'message' => 'Товар не найден'], 404, $redirect);
                return;
            }
            $existing = $this->cart_model->findProduct(Auth::getCurrentUserId(), session_id(), $product_id);
            if ($quantity > (int)$product['stock'] - (int)($existing['quantity'] ?? 0)) {
                Response::send(['success' => false, 'message' => 'Недостаточно товара на складе с учётом количества в корзине'], 400, $redirect);
                return;
            }
            $this->cart_model->add(Auth::getCurrentUserId(), session_id(), $product_id, $quantity);
            $item = $this->cart_model->findProduct(Auth::getCurrentUserId(), session_id(), $product_id);
            $item['stock'] = $product['stock'];
            Response::send(['success' => true, 'message' => 'Товар добавлен в корзину'] + $this->cartState($item), 200, $redirect);
        } catch (\PDOException $exception) {
            Response::send(['success' => false, 'message' => 'Не удалось добавить товар в корзину'], 500, $redirect);
        }
    }

    public function update($redirect = null) {
        $data = Request::input();
        if (!$this->validateRequest($data, $redirect)) {
            return;
        }
        $cart_id = Request::integer($_GET, 'id');
        $quantity = Request::integer($data, 'quantity');
        if ($cart_id <= 0 || $quantity < 1) {
            Response::send(['success' => false, 'message' => 'Количество должно быть положительным целым числом'], 400, $redirect);
            return;
        }
        try {
            $item = $this->cart_model->getOwnedItem($cart_id, Auth::getCurrentUserId(), session_id());
            if (!$item) {
                Response::send(['success' => false, 'message' => 'Товар в вашей корзине не найден'], 404, $redirect);
                return;
            }
            if (!$item['is_active'] || $quantity > $item['stock']) {
                Response::send(['success' => false, 'message' => 'Недостаточно товара на складе'], 400, $redirect);
                return;
            }
            $this->cart_model->update($cart_id, $quantity);
            $item['quantity'] = $quantity;
            Response::send(['success' => true, 'message' => 'Количество обновлено'] + $this->cartState($item), 200, $redirect);
        } catch (\PDOException $exception) {
            Response::send(['success' => false, 'message' => 'Не удалось обновить корзину'], 500, $redirect);
        }
    }

    public function remove($redirect = null) {
        $data = Request::input();
        if (!$this->validateRequest($data, $redirect, ['POST', 'DELETE'])) {
            return;
        }
        $cart_id = Request::integer($_GET, 'id');
        try {
            $item = $cart_id > 0 ? $this->cart_model->getOwnedItem($cart_id, Auth::getCurrentUserId(), session_id()) : false;
            if (!$item) {
                Response::send(['success' => false, 'message' => 'Товар в вашей корзине не найден'], 404, $redirect);
                return;
            }
            $this->cart_model->remove($cart_id);
            $item['id'] = 0;
            $item['quantity'] = 0;
            Response::send(['success' => true, 'message' => 'Товар удалён из корзины'] + $this->cartState($item), 200, $redirect);
        } catch (\PDOException $exception) {
            Response::send(['success' => false, 'message' => 'Не удалось удалить товар из корзины'], 500, $redirect);
        }
    }

    public function getItems() {
        Response::send(['success' => true, 'items' => $this->cart_model->getItems(Auth::getCurrentUserId(), session_id())]);
    }

    public function getCount() {
        Response::send(['success' => true, 'count' => (int)$this->cart_model->getCount(Auth::getCurrentUserId(), session_id())]);
    }

    public function getSummary() {
        $items = $this->cart_model->getItems(Auth::getCurrentUserId(), session_id());
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $shipping = $items ? 300 : 0;
        Response::send(['success' => true, 'subtotal' => $subtotal, 'shipping' => $shipping, 'total' => $subtotal + $shipping]);
    }
}
