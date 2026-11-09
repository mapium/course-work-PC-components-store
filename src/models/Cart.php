<?php

namespace App\Models;

use PDO;

class Cart {
    private $conn;
    private $table = 'cart';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function add($user_id, $session_id, $product_id, $quantity) {
        // Для гостя UNIQUE(user_id, product_id) не срабатывает: user_id равен NULL.
        $existing = $this->findProduct($user_id, $session_id, $product_id);
        if ($existing) {
            $result = $this->update($existing['id'], $existing['quantity'] + $quantity);
            // Объединяем и старые дубликаты гостевых строк, созданные до перехода на PHP-формы.
            $stmt = $this->conn->prepare("DELETE FROM cart WHERE product_id = :product_id AND id <> :id
                AND (user_id = :user_id OR (user_id IS NULL AND session_id = :session_id))");
            $stmt->execute([':product_id' => $product_id, ':id' => $existing['id'], ':user_id' => $user_id, ':session_id' => $session_id]);
            return $result;
        }
        $query = "INSERT INTO " . $this->table . " (user_id, session_id, product_id, quantity)
                  VALUES (:user_id, :session_id, :product_id, :quantity)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getItems($user_id, $session_id) {
        $query = "SELECT c.*, p.name, p.price, p.slug, p.category_id, p.stock, p.is_active,
                    (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id
                     ORDER BY pi.is_main DESC, pi.sort_order, pi.id LIMIT 1) AS main_image
                 FROM " . $this->table . " c
                 JOIN products p ON c.product_id = p.id
                  WHERE (c.user_id = :user_id OR (c.user_id IS NULL AND c.session_id = :session_id))
                 ORDER BY c.added_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCount($user_id, $session_id) {
        $query = "SELECT SUM(quantity) as count FROM " . $this->table . "
                 WHERE (user_id = :user_id OR (user_id IS NULL AND session_id = :session_id))";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    public function remove($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function findProduct($user_id, $session_id, $product_id) {
        $stmt = $this->conn->prepare("SELECT * FROM cart WHERE product_id = :product_id
            AND (user_id = :user_id OR (user_id IS NULL AND session_id = :session_id)) ORDER BY id LIMIT 1");
        $parameters = [':product_id' => $product_id, ':user_id' => $user_id, ':session_id' => $session_id];
        $stmt->execute($parameters);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($item) {
            $stmt = $this->conn->prepare("SELECT SUM(quantity) FROM cart WHERE product_id = :product_id
                AND (user_id = :user_id OR (user_id IS NULL AND session_id = :session_id))");
            $stmt->execute($parameters);
            $item['quantity'] = (int)$stmt->fetchColumn();
        }
        return $item;
    }

    public function mergeGuestCart($user_id, $session_id) {
        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare('SELECT DISTINCT product_id FROM cart WHERE user_id IS NULL AND session_id = ?');
            $stmt->execute([$session_id]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $product_id) {
                $item = $this->findProduct($user_id, $session_id, $product_id);
                $stmt_update = $this->conn->prepare('UPDATE cart SET user_id = ?, session_id = NULL, quantity = ? WHERE id = ?');
                $stmt_delete = $this->conn->prepare('DELETE FROM cart WHERE product_id = ? AND id <> ?
                    AND (user_id = ? OR (user_id IS NULL AND session_id = ?))');
                // Сначала удаляем дубликаты, чтобы не нарушить UNIQUE(user_id, product_id).
                $stmt_delete->execute([$product_id, $item['id'], $user_id, $session_id]);
                $stmt_update->execute([$user_id, $item['quantity'], $item['id']]);
            }
            $this->conn->commit();
        } catch (\Throwable $exception) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            throw $exception;
        }
    }

    public function getOwnedItem($id, $user_id, $session_id) {
        $stmt = $this->conn->prepare("SELECT c.*, p.stock, p.is_active FROM cart c
            JOIN products p ON c.product_id = p.id WHERE c.id = :id
            AND (c.user_id = :user_id OR (c.user_id IS NULL AND c.session_id = :session_id))");
        $stmt->execute([':id' => $id, ':user_id' => $user_id, ':session_id' => $session_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $quantity) {
        $query = "UPDATE " . $this->table . " SET quantity = :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function clear($user_id, $session_id) {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = :user_id OR (user_id IS NULL AND session_id = :session_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':session_id', $session_id);
        return $stmt->execute();
    }
}
