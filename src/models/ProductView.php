<?php

namespace App\Models;

use PDO;

class ProductView {
    const MAX_ITEMS = 30;

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function record($user_id, $product_id, $viewed_at = null) {
        $viewed_at = $viewed_at ?? (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s.u');
        $this->recordMany($user_id, [(int)$product_id => $viewed_at]);
    }

    public function recordMany($user_id, array $entries) {
        if ((int)$user_id <= 0 || !$entries) return;
        $ids = (new Category($this->conn))->descendantIds();
        if (!$ids) return;

        // Один товар — одна запись. Более новый просмотр не затирается при переносе гостевой истории.
        $query = 'INSERT INTO product_views (user_id, product_id, viewed_at)
                  SELECT :user_id, p.id, :viewed_at FROM products p
                  WHERE p.id = :product_id AND p.is_active = TRUE
                  AND p.category_id IN (' . implode(',', $ids) . ')';
        $query .= $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql'
            ? ' ON DUPLICATE KEY UPDATE viewed_at = GREATEST(viewed_at, VALUES(viewed_at))'
            : ' ON CONFLICT (user_id, product_id) DO UPDATE SET viewed_at = MAX(product_views.viewed_at, excluded.viewed_at)';

        $own_transaction = !$this->conn->inTransaction();
        if ($own_transaction) $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare($query);
            foreach ($entries as $product_id => $viewed_at) {
                if ((int)$product_id <= 0 || !is_string($viewed_at)) continue;
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s.u', $viewed_at, new \DateTimeZone('UTC'));
                if (!$date || $date->format('Y-m-d H:i:s.u') !== $viewed_at) continue;
                $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
                $stmt->bindValue(':product_id', (int)$product_id, PDO::PARAM_INT);
                $stmt->bindValue(':viewed_at', $viewed_at);
                $stmt->execute();
            }
            $this->trim($user_id);
            if ($own_transaction) $this->conn->commit();
        } catch (\Throwable $error) {
            if ($own_transaction && $this->conn->inTransaction()) $this->conn->rollBack();
            throw $error;
        }
    }

    public function getRecentIds($user_id, $limit = self::MAX_ITEMS) {
        if ((int)$user_id <= 0) return [];
        $ids = (new Category($this->conn))->descendantIds();
        $stmt = $this->conn->prepare('SELECT pv.product_id FROM product_views pv
            INNER JOIN products p ON p.id = pv.product_id
            WHERE pv.user_id = :user_id AND p.is_active = TRUE
            AND p.category_id IN (' . implode(',', $ids ?: [0]) . ')
            ORDER BY pv.viewed_at DESC, pv.id DESC LIMIT :limit');
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(self::MAX_ITEMS, (int)$limit)), PDO::PARAM_INT);
        $stmt->execute();
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private function trim($user_id) {
        $stmt = $this->conn->prepare('SELECT id FROM product_views WHERE user_id = :user_id
            ORDER BY viewed_at DESC, id DESC LIMIT :limit');
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', self::MAX_ITEMS, PDO::PARAM_INT);
        $stmt->execute();
        $keep = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        if (!$keep) return;
        $stmt = $this->conn->prepare('DELETE FROM product_views WHERE user_id = :user_id
            AND id NOT IN (' . implode(',', $keep) . ')');
        $stmt->bindValue(':user_id', (int)$user_id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
