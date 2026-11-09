<?php

namespace App\Models;

use PDO;

class Category {
    private $conn;
    private $table = 'categories';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($include_hidden = false) {
        return array_values(array_filter($this->getTree($include_hidden), function ($row) {
            return $row['parent_id'] === null;
        }));
    }

    public function getTree($include_hidden = false) {
        $rows = $this->conn->query('SELECT * FROM categories ORDER BY sort_order, name, id')->fetchAll(PDO::FETCH_ASSOC);
        $children = [];
        foreach ($rows as $row) $children[(int)$row['parent_id']][] = $row;
        $result = [];
        $visited = [];
        $walk = function ($parent, $path, $depth, $visible) use (&$walk, &$result, &$visited, $children, $include_hidden) {
            foreach ($children[$parent] ?? [] as $row) {
                if (isset($visited[$row['id']])) continue;
                $visited[$row['id']] = true;
                $row['depth'] = $depth;
                $row['path'] = ($path === '' ? '' : $path . '/') . $row['slug'];
                $row['visible'] = $visible && (bool)$row['is_visible'];
                if ($include_hidden || $row['visible']) $result[] = $row;
                $walk((int)$row['id'], $row['path'], $depth + 1, $row['visible']);
            }
        };
        $walk(0, '', 0, true);
        return $result;
    }

    public function getByPath($path, $include_hidden = false) {
        foreach ($this->getTree($include_hidden) as $row) {
            if ($row['path'] === $path) return $row;
        }
        return false;
    }

    public function descendantIds($id = null, $include_hidden = false) {
        $tree = $this->getTree($include_hidden);
        if ($id === null) return array_map('intval', array_column($tree, 'id'));
        $path = null;
        foreach ($tree as $row) if ((int)$row['id'] === (int)$id) $path = $row['path'];
        if ($path === null) return [];
        return array_values(array_map(function ($row) { return (int)$row['id']; }, array_filter($tree, function ($row) use ($path) {
            return $row['path'] === $path || strpos($row['path'], $path . '/') === 0;
        })));
    }

    public function trail($id) {
        $rows = [];
        foreach ($this->getTree(true) as $row) $rows[$row['id']] = $row;
        $trail = [];
        $seen = [];
        while (isset($rows[$id]) && !isset($seen[$id])) {
            $seen[$id] = true;
            array_unshift($trail, $rows[$id]);
            $id = $rows[$id]['parent_id'];
        }
        return $trail;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBySlug($slug) {
        $query = "SELECT * FROM " . $this->table . " WHERE slug = :slug";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSubcategories($parent_id) {
        return array_values(array_filter($this->getTree(), function ($row) use ($parent_id) {
            return (int)$row['parent_id'] === (int)$parent_id;
        }));
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (name, slug, description, parent_id)
                 VALUES (:name, :slug, :description, :parent_id)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':slug', $data['slug']);
        $stmt->bindParam(':description', $data['description'] ?? null);
        $stmt->bindParam(':parent_id', $data['parent_id'] ?? null);

        return $stmt->execute();
    }
}
