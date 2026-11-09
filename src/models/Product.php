<?php

namespace App\Models;

use PDO;

class Product {
    private $conn;
    private $table = 'products';

    public function __construct($db) {
        $this->conn = $db;
    }

    private function ratingColumns() {
        return "COALESCE((SELECT AVG(r.rating) FROM reviews r
                    WHERE r.product_id = p.id AND r.is_approved = TRUE
                    AND r.parent_id IS NULL AND r.rating BETWEEN 1 AND 5), 0) AS average_rating,
                (SELECT COUNT(*) FROM reviews r
                    WHERE r.product_id = p.id AND r.is_approved = TRUE
                    AND r.parent_id IS NULL AND r.rating BETWEEN 1 AND 5) AS review_count";
    }

    private function imageColumn() {
        return "(SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id
                    ORDER BY pi.is_main DESC, pi.sort_order, pi.id LIMIT 1) AS main_image,
                (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id
                    ORDER BY pi.is_main DESC, pi.sort_order, pi.id LIMIT 1 OFFSET 1) AS hover_image";
    }

    public function getAll($limit = 20, $offset = 0, $include_inactive = false) {
        if (!$include_inactive) return $this->getWithFilters([], $limit, $offset);
        $query = "SELECT p.*, " . $this->imageColumn() . ", " . $this->ratingColumns() . "
                  FROM " . $this->table . " p
                  WHERE " . ($include_inactive ? '1 = 1' : 'p.is_active = TRUE') . "
                  ORDER BY p.created_at DESC, p.id DESC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id, $include_inactive = false) {
        $query = "SELECT p.*, " . $this->imageColumn() . ", " . $this->ratingColumns() . "
                  FROM " . $this->table . " p
                  WHERE p.id = :id";
        if (!$include_inactive) {
            $query .= " AND p.is_active = TRUE";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product && !$include_inactive && !in_array((int)$product['category_id'], (new Category($this->conn))->descendantIds(), true)) return false;
        return $product;
    }

    public function getByPath($path) {
        $slug = basename($path);
        $stmt = $this->conn->prepare('SELECT id FROM products WHERE slug = ? AND is_active = TRUE');
        $stmt->execute([$slug]);
        $id = $stmt->fetchColumn();
        $product = $id ? $this->getById($id) : false;
        return $product && \App\Helpers\Url::productPath($product, $this->conn) === '/product/' . $path ? $product : false;
    }

    public function getByCategoryId($category_id, $limit = 20, $offset = 0) {
        return $this->getWithFilters(['category_id' => $category_id], $limit, $offset);
    }

    public function search($query, $limit = 20, $offset = 0) {
        return $this->getWithFilters(['search' => $query], $limit, $offset);
    }

    public function getFeatured($limit = 6) {
        return $this->getWithFilters(['featured' => true], $limit);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                 (name, slug, category_id, price, description, brand, stock, sku)
                 VALUES (:name, :slug, :category_id, :price, :description, :brand, :stock, :sku)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':slug', $data['slug']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':description', $data['description'] ?? null);
        $stmt->bindParam(':brand', $data['brand'] ?? null);
        $stmt->bindParam(':stock', $data['stock'] ?? 0);
        $stmt->bindParam(':sku', $data['sku'] ?? null);

        return $stmt->execute();
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET name = :name, price = :price, description = :description, stock = :stock WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':stock', $data['stock']);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "UPDATE " . $this->table . " SET is_active = FALSE WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getSpecifications($product_id) {
        $query = "SELECT * FROM product_specifications WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSimilarProducts($product_id, $category_id, $limit = 4) {
        $query = "SELECT p.*, " . $this->imageColumn() . ", " . $this->ratingColumns() . "
                  FROM " . $this->table . " p
                  WHERE p.category_id = :category_id
                  AND p.id != :product_id
                  AND p.is_active = TRUE
                  ORDER BY RAND()
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReviews($product_id) {
        // Получаем основные отзывы (не ответы)
        $query = "SELECT r.*, u.username, u.full_name, u.role AS user_role
                  FROM reviews r
                  LEFT JOIN users u ON r.user_id = u.id
                  WHERE r.product_id = :product_id
                  AND r.is_approved = TRUE
                  AND r.parent_id IS NULL
                  ORDER BY r.created_at DESC, r.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Для каждого отзыва получаем ответы
        foreach ($reviews as &$review) {
            $query_replies = "SELECT r.*, u.username, u.full_name, u.role AS user_role
                             FROM reviews r
                             LEFT JOIN users u ON r.user_id = u.id
                             WHERE r.parent_id = :parent_id
                             AND r.is_approved = TRUE
                             ORDER BY r.created_at ASC";
            $stmt_replies = $this->conn->prepare($query_replies);
            $stmt_replies->bindParam(':parent_id', $review['id'], PDO::PARAM_INT);
            $stmt_replies->execute();
            $review['replies'] = $stmt_replies->fetchAll(PDO::FETCH_ASSOC);
        }

        return $reviews;
    }

    public function getImages($product_id) {
        $query = "SELECT * FROM product_images WHERE product_id = :product_id ORDER BY sort_order, id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countWithFilters($filters = []) {
        return $this->getWithFilters($filters, 1, 0, true);
    }

    public function getWithFilters($filters = [], $limit = 100, $offset = 0, $count_only = false) {
        $columns = $count_only ? 'COUNT(DISTINCT p.id)' : 'DISTINCT p.*, ' . $this->imageColumn() . ', ' . $this->ratingColumns() . ', c.name AS category_name';
        $query = "SELECT $columns
                  FROM " . $this->table . " p
                  LEFT JOIN categories c ON p.category_id = c.id";

        $params = [];
        $joinIndex = 0;

        if (!empty($filters['specifications']) && is_array($filters['specifications'])) {
            foreach ($filters['specifications'] as $spec_name => $spec_values) {
                if (!empty($spec_values) && is_array($spec_values)) {
                    $alias = "ps" . $joinIndex;
                    $placeholders = [];

                    foreach ($spec_values as $idx => $value) {
                        $placeholder = ":{$alias}_val_{$idx}";
                        $placeholders[] = $placeholder;
                        $params[$placeholder] = $value;
                    }

                    $in_clause = implode(', ', $placeholders);
                    $query .= " INNER JOIN product_specifications {$alias}
                               ON p.id = {$alias}.product_id
                               AND {$alias}.spec_name = :{$alias}_name
                               AND {$alias}.spec_value IN ({$in_clause})";

                    $params[":{$alias}_name"] = $spec_name;
                    $joinIndex++;
                }
            }
        }

        $ids = (new Category($this->conn))->descendantIds(!empty($filters['category_id']) ? (int)$filters['category_id'] : null);
        $where_clauses = ["p.is_active = TRUE", 'p.category_id IN (' . implode(',', $ids ?: [0]) . ')'];
        if (!empty($filters['featured'])) $where_clauses[] = 'p.is_featured = TRUE';

        $search = is_string($filters['search'] ?? null) ? trim($filters['search']) : '';
        if ($search !== '') {
            $terms = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY);
            if ($terms === false || strlen($search) > 480) {
                return $count_only ? 0 : [];
            }

            foreach ($terms as $index => $term) {
                // Treat SQL wildcard characters as literal search text.
                $pattern = '%' . strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
                $matches = [];
                foreach (['name', 'description', 'brand', 'sku'] as $field) {
                    $placeholder = ":search_{$index}_{$field}";
                    $matches[] = "p.{$field} LIKE {$placeholder} ESCAPE '!'";
                    $params[$placeholder] = $pattern;
                }

                $matches[] = "c.name LIKE :search_{$index}_category ESCAPE '!'";
                $params[":search_{$index}_category"] = $pattern;
                $matches[] = "EXISTS (
                    SELECT 1 FROM product_specifications search_spec
                    WHERE search_spec.product_id = p.id
                    AND CONCAT(search_spec.spec_name, ' ', search_spec.spec_value)
                        LIKE :search_{$index}_specification ESCAPE '!'
                )";
                $params[":search_{$index}_specification"] = $pattern;
                $where_clauses[] = '(' . implode(' OR ', $matches) . ')';
            }
        }

        if (!empty($filters['brand'])) {
            $where_clauses[] = "p.brand = :brand";
            $params[':brand'] = $filters['brand'];
        }

        if (isset($filters['price_min']) && is_numeric($filters['price_min'])) {
            $where_clauses[] = "p.price >= :price_min";
            $params[':price_min'] = $filters['price_min'];
        }

        if (isset($filters['price_max']) && is_numeric($filters['price_max'])) {
            $where_clauses[] = "p.price <= :price_max";
            $params[':price_max'] = $filters['price_max'];
        }

        if (!empty($where_clauses)) {
            $query .= " WHERE " . implode(' AND ', $where_clauses);
        }

        $sort = $filters['sort'] ?? ($search !== '' ? 'relevance' : 'newest');
        if (!$count_only && $sort === 'relevance' && $search !== '') {
            $params[':search_exact'] = $search;
            $params[':search_prefix'] = strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $query .= " ORDER BY (p.name = :search_exact) DESC,
                       (p.name LIKE :search_prefix ESCAPE '!') DESC, p.created_at DESC, p.id DESC";
        } elseif (!$count_only) {
            $sorts = ['price_asc' => 'p.price ASC, p.id DESC', 'price_desc' => 'p.price DESC, p.id DESC',
                'rating' => 'average_rating DESC, review_count DESC, p.id DESC'];
            $query .= ' ORDER BY ' . ($sorts[$sort] ?? 'p.created_at DESC, p.id DESC');
        }
        if (!$count_only) $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if (!$count_only) {
            $stmt->bindValue(':limit', max(1, min(100, (int)$limit)), PDO::PARAM_INT);
            $stmt->bindValue(':offset', max(0, (int)$offset), PDO::PARAM_INT);
        }

        $stmt->execute();
        return $count_only ? (int)$stmt->fetchColumn() : $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableSpecifications($category_id, $brand = '') {
        $ids = (new Category($this->conn))->descendantIds($category_id);
        $query = "SELECT DISTINCT ps.spec_name, ps.spec_value
                  FROM product_specifications ps
                  INNER JOIN products p ON ps.product_id = p.id
                  WHERE p.category_id IN (" . implode(',', $ids ?: [0]) . ")
                  AND p.is_active = TRUE
                  " . ($brand !== '' ? 'AND p.brand = :brand' : '') . "
                  ORDER BY ps.spec_name, ps.spec_value";

        $stmt = $this->conn->prepare($query);
        if ($brand !== '') $stmt->bindValue(':brand', $brand);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $specifications = [];
        foreach ($results as $row) {
            $spec_name = $row['spec_name'];
            $spec_value = $row['spec_value'];

            if (!isset($specifications[$spec_name])) {
                $specifications[$spec_name] = [];
            }

            if (!in_array($spec_value, $specifications[$spec_name])) {
                $specifications[$spec_name][] = $spec_value;
            }
        }

        return $specifications;
    }

    public function getAvailableBrands($category_id = null) {
        $ids = (new Category($this->conn))->descendantIds($category_id);
        $query = "SELECT DISTINCT brand
                  FROM " . $this->table . "
                  WHERE is_active = TRUE
                  AND brand IS NOT NULL
                  AND brand != '' AND category_id IN (" . implode(',', $ids ?: [0]) . ')';

        $params = [];

        $query .= " ORDER BY brand";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }

        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_column($results, 'brand');
    }
}
