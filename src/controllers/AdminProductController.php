<?php

namespace App\Controllers;

use App\Helpers\Request;
use App\Helpers\Media;
use App\Helpers\Slug;
use App\Helpers\ValidationException;
use App\Models\Product;

class AdminProductController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function imageNames() {
        return Media::names();
    }

    private function validate($input) {
        $data = [];
        foreach (['name', 'brand', 'sku', 'description', 'detailed_description', 'meta_title', 'meta_description', 'meta_keywords', 'main_image'] as $field) {
            $data[$field] = Request::text($input, $field);
        }
        $data['category_id'] = Request::integer($input, 'category_id');
        $data['stock'] = Request::integer($input, 'stock', -1);
        $data['is_active'] = isset($input['is_active']) ? 1 : 0;
        $price = Request::text($input, 'price');
        $old_price = Request::text($input, 'old_price');
        if ($data['name'] === '' || Request::length($data['name']) > 255) {
            throw new ValidationException('name', 'Название должно содержать от 1 до 255 символов');
        }
        if (!is_numeric($price) || (float)$price <= 0 || (float)$price > 99999999.99) throw new ValidationException('price', 'Укажите положительную цену до 99999999,99');
        if ($old_price !== '' && (!is_numeric($old_price) || (float)$old_price < 0 || (float)$old_price > 99999999.99)) throw new ValidationException('old_price', 'Укажите корректную старую цену');
        if ($data['stock'] < 0 || $data['stock'] > 2147483647) throw new ValidationException('stock', 'Остаток должен быть неотрицательным целым числом');
        $data['price'] = round((float)$price, 2);
        $data['old_price'] = $old_price === '' ? null : round((float)$old_price, 2);
        foreach (['brand' => 100, 'sku' => 50, 'meta_title' => 255, 'meta_description' => 255, 'meta_keywords' => 255] as $field => $limit) {
            if (Request::length($data[$field]) > $limit) {
                throw new ValidationException($field, 'Не более ' . $limit . ' символов');
            }
        }
        $data['sku'] = $data['sku'] === '' ? null : $data['sku'];
        $stmt = $this->pdo->prepare('SELECT id FROM categories WHERE id = ?');
        $stmt->execute([$data['category_id']]);
        if (!$stmt->fetchColumn()) {
            throw new ValidationException('category_id', 'Выберите существующую категорию');
        }
        $library = $this->imageNames();
        if ($data['main_image'] !== '' && !in_array($data['main_image'], $library, true) && preg_match('/\Aupload:\d+\z/', $data['main_image']) !== 1) {
            throw new ValidationException('main_image', 'Выберите изображение из библиотеки или загрузите файл');
        }
        $data['gallery'] = null;
        if (isset($input['gallery_present'])) {
            $gallery = $input['gallery'] ?? [];
            if (!is_array($gallery) || count($gallery) > Media::MAX_IMAGES) throw new ValidationException('gallery_uploads[]', 'Допускается не более 12 изображений');
            foreach ($gallery as $name) {
                if (!is_string($name) || (!in_array($name, $library, true) && preg_match('/\Aupload:\d+\z/', $name) !== 1)) throw new ValidationException('gallery_uploads[]', 'В галерее указано недопустимое изображение');
            }
            $data['gallery'] = array_values(array_unique($gallery));
        }
        $data['specifications'] = [];
        $specifications = $input['specifications'] ?? [];
        if (!is_array($specifications) || count($specifications) > 100) {
            throw new ValidationException('specifications[0][name]', 'Допускается не более 100 характеристик');
        }
        foreach ($specifications as $index => $spec) {
            if (!is_array($spec) || !empty($spec['remove'])) {
                continue;
            }
            $name = Request::text($spec, 'name');
            $value = Request::text($spec, 'value');
            if ($name === '' && $value === '') {
                continue;
            }
            if ($name === '' || $value === '' || Request::length($name) > 100 || Request::length($value) > 255) {
                throw new ValidationException('specifications[' . $index . '][' . ($name === '' ? 'name' : 'value') . ']', 'Заполните название и значение каждой характеристики (до 100 и 255 символов)');
            }
            $data['specifications'][] = ['name' => $name, 'value' => $value];
        }
        return $data;
    }

    public function save($input, $id = null, $files = []) {
        $data = $this->validate($input);
        $existing = $id !== null ? (new Product($this->pdo))->getById($id, true) : null;
        if ($id !== null && !$existing) {
            throw new \InvalidArgumentException('Товар не найден');
        }
        if ($data['sku'] !== null) {
            $stmt = $this->pdo->prepare('SELECT id FROM products WHERE sku = ? AND id <> ?');
            $stmt->execute([$data['sku'], $id ?? 0]);
            if ($stmt->fetchColumn()) throw new ValidationException('sku', 'Товар с таким SKU уже существует');
        }
        $uploads = [];
        try {
            $uploads = Media::uploads($files['gallery_uploads'] ?? [], 'products', 'gallery_uploads[]');
            $main_upload = Media::uploads($files['main_upload'] ?? [], 'products', 'main_upload');
            $all_uploads = array_merge($uploads, $main_upload);
            $gallery = $data['gallery'] ?? ($id ? array_column((new Product($this->pdo))->getImages($id), 'image_path') : []);
            $resolve = function ($name) use ($uploads) {
                if (strpos($name, 'upload:') !== 0) return $name;
                $index = (int)substr($name, 7);
                if (!isset($uploads[$index])) throw new ValidationException('gallery_uploads[]', 'Выберите файлы галереи повторно');
                return $uploads[$index];
            };
            $gallery = array_map($resolve, $gallery);
            $main_image = $main_upload[0] ?? $resolve($data['main_image']);
            $gallery = array_values(array_unique(array_filter(array_merge($gallery, $uploads, $main_upload, [$main_image]))));
            if (count($gallery) > Media::MAX_IMAGES) throw new ValidationException('gallery_uploads[]', 'В галерее допускается не более 12 изображений');
            if ($main_image === '' && $gallery) $main_image = $gallery[0];
            $this->pdo->beginTransaction();
            $parameters = $data;
            unset($parameters['main_image'], $parameters['specifications'], $parameters['gallery']);
            $parameters['slug'] = $existing && $existing['name'] === $data['name'] ? $existing['slug'] : Slug::unique($this->pdo, 'products', $data['name'], $id);
            if ($id === null) {
                $fields = implode(', ', array_keys($parameters));
                $placeholders = ':' . implode(', :', array_keys($parameters));
                $stmt = $this->pdo->prepare("INSERT INTO products ($fields) VALUES ($placeholders)");
            } else {
                $updates = [];
                foreach (array_keys($parameters) as $field) {
                    $updates[] = $field . ' = :' . $field;
                }
                $parameters['id'] = $id;
                $stmt = $this->pdo->prepare('UPDATE products SET ' . implode(', ', $updates) . ', updated_at = NOW() WHERE id = :id');
            }
            $stmt->execute($parameters);
            $id = $id ?? (int)$this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare('DELETE FROM product_images WHERE product_id = ?');
            $stmt->execute([$id]);
            $stmt = $this->pdo->prepare('INSERT INTO product_images (product_id, image_path, is_main, sort_order) VALUES (?, ?, ?, ?)');
            foreach ($gallery as $order => $name) {
                $stmt->execute([$id, $name, $name === $main_image ? 1 : 0, $order]);
            }
            // Замена выполняется и для пустого списка: можно удалить все характеристики.
            $stmt = $this->pdo->prepare('DELETE FROM product_specifications WHERE product_id = ?');
            $stmt->execute([$id]);
            $stmt = $this->pdo->prepare('INSERT INTO product_specifications (product_id, spec_name, spec_value) VALUES (?, ?, ?)');
            foreach ($data['specifications'] as $spec) {
                $stmt->execute([$id, $spec['name'], $spec['value']]);
            }
            $this->pdo->commit();
            return $id;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            Media::discard($all_uploads ?? $uploads);
            throw $exception;
        }
    }

    public function delete($id) {
        if ($id <= 0 || !(new Product($this->pdo))->getById($id, true)) {
            throw new \InvalidArgumentException('Товар не найден');
        }
        // Сохраняем позиции уже оформленных заказов и связи с товаром.
        return (new Product($this->pdo))->delete($id);
    }
}
