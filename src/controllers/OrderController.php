<?php

namespace App\Controllers;

use App\Models\Order;
use App\Models\Cart as CartModel;
use App\Helpers\Auth;
use App\Helpers\CSRF;
use App\Helpers\Request;
use App\Helpers\Response;

class OrderController {
    private $db;
    private $order_model;

    public function __construct($db) {
        $this->db = $db;
        $this->order_model = new Order($db);
    }

    public function create($redirect = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::send(['success' => false, 'message' => 'Недопустимый метод запроса'], 405, $redirect);
            return;
        }
        $data = [];
        foreach (['name', 'email', 'phone', 'city', 'postal_code', 'address', 'comment', 'payment_method'] as $field) {
            $data[$field] = Request::text($_POST, $field);
        }
        if ($redirect !== null) {
            $_SESSION['checkout_old'] = $data;
        }
        if (!Auth::isLoggedIn()) {
            Response::send(['success' => false, 'message' => 'Войдите в аккаунт для оформления заказа'], 401, $redirect);
            return;
        }
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            Response::send(['success' => false, 'message' => 'Ошибка безопасности. Обновите страницу и повторите отправку'], 403, $redirect);
            return;
        }
        foreach (['name', 'email', 'phone', 'city', 'address'] as $field) {
            if ($data[$field] === '') {
                Response::send(['success' => false, 'message' => 'Пожалуйста, заполните все обязательные поля'], 400, $redirect);
                return;
            }
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 100) {
            Response::send(['success' => false, 'message' => 'Укажите корректный email'], 400, $redirect);
            return;
        }
        if (!Request::validPhone($data['phone'])) {
            Response::send(['success' => false, 'message' => 'Укажите корректный телефон (не менее 10 цифр)'], 400, $redirect);
            return;
        }
        if (Request::length($data['name']) > 100 || Request::length($data['city']) > 100
            || Request::length($data['address']) > 255 || Request::length($data['comment']) > 5000
            || ($data['postal_code'] !== '' && preg_match('/\A\d{6}\z/', $data['postal_code']) !== 1)) {
            Response::send(['success' => false, 'message' => 'Проверьте длину контактных данных, адреса и шестизначный индекс'], 400, $redirect);
            return;
        }
        if (!in_array($data['payment_method'], ['card', 'cash'], true)) {
            Response::send(['success' => false, 'message' => 'Выберите доступный способ оплаты'], 400, $redirect);
            return;
        }

        try {
            $this->db->beginTransaction();
            $cart_model = new CartModel($this->db);
            $items = $cart_model->getItems(Auth::getCurrentUserId(), session_id());
            if (!$items) {
                $this->db->rollBack();
                Response::send(['success' => false, 'message' => 'Корзина пуста'], 400, $redirect);
                return;
            }
            $subtotal_cents = 0;
            foreach ($items as $item) {
                if (!$item['is_active'] || $item['quantity'] < 1 || $item['quantity'] > $item['stock']) {
                    $this->db->rollBack();
                    Response::send(['success' => false, 'message' => 'Проверьте наличие и количество товаров в корзине'], 400, $redirect);
                    return;
                }
                $subtotal_cents += (int)round($item['price'] * 100) * (int)$item['quantity'];
            }
            $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $address = $data['city'] . ', ' . $data['address'];
            if ($data['postal_code'] !== '') {
                $address = $data['postal_code'] . ', ' . $address;
            }
            $notes = 'Получатель: ' . $data['name'] . "\nEmail: " . $data['email'] . "\nТелефон: " . $data['phone'];
            if ($data['comment'] !== '') {
                $notes .= "\nКомментарий: " . $data['comment'];
            }
            // Цены и доставка берутся на сервере, клиентская сумма не используется.
            $this->order_model->create([
                'user_id' => Auth::getCurrentUserId(), 'order_number' => $order_number,
                'total_amount' => ($subtotal_cents + 30000) / 100, 'delivery_address' => $address,
                'delivery_method' => 'courier', 'payment_method' => $data['payment_method'], 'notes' => $notes
            ]);
            $order_id = (int)$this->db->lastInsertId();
            $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
                VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $item_total = (int)round($item['price'] * 100) * (int)$item['quantity'] / 100;
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price'], $item_total]);
            }
            $cart_model->clear(Auth::getCurrentUserId(), session_id());
            $this->db->commit();
            unset($_SESSION['checkout_old']);
            Response::send([
                'success' => true, 'message' => 'Заказ успешно оформлен',
                'order_id' => $order_id, 'order_number' => $order_number
            ], 200, $redirect === null ? null : '/order-success?id=' . $order_id);
        } catch (\PDOException $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            Response::send(['success' => false, 'message' => 'Не удалось создать заказ. Попробуйте ещё раз'], 500, $redirect);
        }
    }

    public function updateStatus($redirect = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::send(['success' => false, 'message' => 'Недопустимый метод запроса'], 405, $redirect);
            return;
        }
        if (!Auth::canManageStore()) {
            Response::send(['success' => false, 'message' => 'Менять статус может только менеджер или администратор'], 403, $redirect);
            return;
        }
        $data = Request::input();
        if (!CSRF::validateToken($data['csrf_token'] ?? '')) {
            Response::send(['success' => false, 'message' => 'Ошибка безопасности. Обновите страницу'], 403, $redirect);
            return;
        }
        $order_id = Request::integer($data, 'order_id');
        $status = Request::text($data, 'status');
        if ($order_id <= 0 || !isset(Order::STATUSES[$status])) {
            Response::send(['success' => false, 'message' => 'Укажите существующий заказ и допустимый статус'], 400, $redirect);
            return;
        }
        try {
            if (!$this->order_model->getById($order_id)) {
                Response::send(['success' => false, 'message' => 'Заказ не найден'], 404, $redirect);
                return;
            }
            $this->order_model->updateStatus($order_id, $status);
            Response::send(['success' => true, 'message' => 'Статус заказа обновлён'], 200, $redirect);
        } catch (\PDOException $exception) {
            Response::send(['success' => false, 'message' => 'Не удалось изменить статус заказа'], 500, $redirect);
        }
    }

    public function getById() {
        $id = Request::integer($_GET, 'id');
        $order = $id > 0 ? $this->order_model->getById($id) : null;
        if (!$order) {
            Response::send(['success' => false, 'message' => 'Заказ не найден'], 404);
            return;
        }
        if (!Auth::canManageStore() && (!Auth::isLoggedIn() || $order['user_id'] != Auth::getCurrentUserId())) {
            Response::send(['success' => false, 'message' => 'Недостаточно прав для просмотра заказа'], 403);
            return;
        }
        Response::send(['success' => true, 'order' => $order, 'items' => $this->order_model->getItems($id)]);
    }

    public function getUserOrders() {
        if (!Auth::isLoggedIn()) {
            Response::send(['success' => false, 'message' => 'Необходимо авторизоваться'], 401);
            return;
        }
        Response::send(['success' => true, 'orders' => $this->order_model->getByUserId(Auth::getCurrentUserId())]);
    }
}
