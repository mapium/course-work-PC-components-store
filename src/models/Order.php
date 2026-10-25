<?php

namespace App\Models;

use PDO;

class Order {
    private $conn;
    private $table = 'orders';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                 (user_id, order_number, total_amount, delivery_address, delivery_method, payment_method)
                 VALUES (:user_id, :order_number, :total_amount, :delivery_address, :delivery_method, :payment_method)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':order_number', $data['order_number']);
        $stmt->bindParam(':total_amount', $data['total_amount']);
        $stmt->bindParam(':delivery_address', $data['delivery_address']);
        $stmt->bindParam(':delivery_method', $data['delivery_method']);
        $stmt->bindParam(':payment_method', $data['payment_method']);

        return $stmt->execute();
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUserId($user_id, $limit = 20, $offset = 0) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserOrders($user_id) {
        return $this->getByUserId($user_id, 100, 0);
    }

    public function getAll($limit = 20, $offset = 0) {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    public function getItems($order_id) {
        $query = "SELECT oi.*, p.name, p.slug FROM order_items oi
                 JOIN products p ON oi.product_id = p.id
                 WHERE oi.order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesReport($period = 'month') {
        if ($period === 'month') {
            $date_filter = "DATE_FORMAT(created_at, '%Y-%m')";
        } else {
            $date_filter = "YEAR(created_at)";
        }

        $query = "SELECT $date_filter as period, COUNT(*) as orders_count, SUM(total_amount) as total_sales
                 FROM " . $this->table . "
                 WHERE status != 'cancelled'
                 GROUP BY $date_filter
                 ORDER BY period DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
