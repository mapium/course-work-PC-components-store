<?php

namespace App\Controllers;

use App\Helpers\Media;
use App\Helpers\Request;
use App\Helpers\Slug;
use App\Helpers\Url;
use App\Helpers\ValidationException;
use App\Models\Category;

class AdminCategoryController {
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    public function save($input, $id = null, $files = []) {
        $model = new Category($this->pdo);
        $existing = $id !== null ? $model->getById($id) : null;
        if ($id !== null && !$existing) throw new ValidationException('id', 'Категория не найдена');
        $data = [];
        foreach (['name', 'description', 'icon_path', 'meta_title', 'meta_description'] as $field) $data[$field] = Request::text($input, $field);
        foreach (['name' => 100, 'description' => 10000, 'meta_title' => 255, 'meta_description' => 255] as $field => $limit) {
            if (Request::length($data[$field]) > $limit || ($field === 'name' && $data[$field] === '')) throw new ValidationException($field, $field === 'name' ? 'Название должно содержать от 1 до 100 символов' : 'Не более ' . $limit . ' символов');
        }
        $parent = Request::integer($input, 'parent_id');
        if ($parent && (!$model->getById($parent) || ($id && in_array($parent, $model->descendantIds($id, true), true)))) {
            throw new ValidationException('parent_id', 'Выберите родительскую категорию вне этого раздела: циклическая вложенность запрещена');
        }
        $data['parent_id'] = $parent ?: null;
        $data['sort_order'] = Request::integer($input, 'sort_order', -1);
        if ($data['sort_order'] < 0 || $data['sort_order'] > 1000000) throw new ValidationException('sort_order', 'Порядок должен быть целым числом от 0 до 1000000');
        $data['is_visible'] = isset($input['is_visible']) ? 1 : 0;
        if ($data['icon_path'] !== '' && !in_array($data['icon_path'], Media::categoryIcons(), true)) throw new ValidationException('icon_path', 'Выберите изображение из библиотеки');
        $uploaded = Media::uploads($files['category_upload'] ?? [], 'categories', 'category_upload');
        if ($uploaded) $data['icon_path'] = 'categories/' . $uploaded[0];
        try {
            $this->pdo->beginTransaction();
            $data['slug'] = $existing && $existing['name'] === $data['name'] ? $existing['slug'] : Slug::unique($this->pdo, 'categories', $data['name'], $id);
            if ($id === null) {
                $fields = implode(', ', array_keys($data));
                $stmt = $this->pdo->prepare('INSERT INTO categories (' . $fields . ') VALUES (:' . implode(', :', array_keys($data)) . ')');
            } else {
                $assignments = [];
                foreach (array_keys($data) as $field) $assignments[] = "$field = :$field";
                $data['id'] = $id;
                $stmt = $this->pdo->prepare('UPDATE categories SET ' . implode(', ', $assignments) . ', updated_at = NOW() WHERE id = :id');
            }
            $stmt->execute($data);
            $id = $id ?? (int)$this->pdo->lastInsertId();
            $this->pdo->commit();
            Url::clear($this->pdo);
            return $id;
        } catch (\Throwable $error) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            Media::discard($uploaded, 'categories');
            throw $error;
        }
    }

    public function delete($id, $transfer = null) {
        $model = new Category($this->pdo);
        if (!$id || !$model->getById($id)) throw new ValidationException('id', 'Категория не найдена');
        if ($transfer && (!$model->getById($transfer) || in_array($transfer, $model->descendantIds($id, true), true))) {
            throw new ValidationException('transfer_id', 'Выберите другой раздел вне удаляемой ветки');
        }
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
            $stmt->execute([$id]);
            $count = (int)$stmt->fetchColumn();
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM categories WHERE parent_id = ?');
            $stmt->execute([$id]);
            $count += (int)$stmt->fetchColumn();
            if ($count && !$transfer) throw new ValidationException('transfer_id', 'Сначала перенесите товары и подкатегории: выберите раздел назначения');
            if ($transfer) {
                $stmt = $this->pdo->prepare('UPDATE products SET category_id = ?, updated_at = NOW() WHERE category_id = ?');
                $stmt->execute([$transfer, $id]);
                $stmt = $this->pdo->prepare('UPDATE categories SET parent_id = ?, updated_at = NOW() WHERE parent_id = ?');
                $stmt->execute([$transfer, $id]);
            }
            $stmt = $this->pdo->prepare('DELETE FROM categories WHERE id = ?');
            $stmt->execute([$id]);
            $this->pdo->commit();
            Url::clear($this->pdo);
        } catch (\Throwable $error) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $error;
        }
    }
}
