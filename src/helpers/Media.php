<?php

namespace App\Helpers;

class Media {
    const MAX_IMAGES = 12;
    const MAX_BYTES = 10485760;

    public static function directory($group = 'products') {
        if (!in_array($group, ['products', 'categories'], true)) throw new \InvalidArgumentException('Invalid media group');
        return (getenv('STORE_MEDIA_PATH') ?: BASE_PATH . '/public/images') . '/' . $group;
    }

    public static function path($name, $group = 'products', $thumbnail = false) {
        if (!is_string($name) || basename($name) !== $name || strpos($name, '\\') !== false) return '';
        $suffix = ($thumbnail ? '/thumbs/' : '/') . $name;
        $uploaded = self::directory($group) . $suffix;
        return is_file($uploaded) ? $uploaded : BASE_PATH . '/public/images/' . $group . $suffix;
    }

    public static function url($name, $group = 'products', $thumbnail = false) {
        if (!is_string($name) || basename($name) !== $name || strpos($name, '\\') !== false) $name = 'placeholder.jpg';
        $thumb = $thumbnail && is_file(self::path($name, $group, true));
        return APP_URL . '/public/images/' . $group . ($thumb ? '/thumbs/' : '/') . rawurlencode($name);
    }

    public static function dimensions($name, $group = 'products', $thumbnail = false) {
        $path = self::path($name, $group, $thumbnail);
        if ($thumbnail && !is_file($path)) $path = self::path($name, $group);
        $size = is_file($path) ? @getimagesize($path) : false;
        return $size ? [(int)$size[0], (int)$size[1]] : [300, 300];
    }

    public static function names($group = 'products') {
        $names = [];
        foreach (array_unique([BASE_PATH . '/public/images/' . $group, self::directory($group)]) as $directory) {
            if (!is_dir($directory)) continue;
            foreach (scandir($directory) as $name) {
                if (is_file($directory . '/' . $name) && in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) $names[] = $name;
            }
        }
        natcasesort($names);
        return array_values(array_unique($names));
    }

    public static function uploads($files, $group = 'products', $field = 'gallery_uploads') {
        if (!$files || !isset($files['error'])) return [];
        if (!is_array($files['error'])) {
            $files = array_map(function ($value) { return [$value]; }, $files);
        }
        if (count($files['error']) > self::MAX_IMAGES) throw new ValidationException($field, 'Допускается не более 12 изображений');
        $saved = [];
        try {
            foreach ($files['error'] as $index => $error) {
                if ($error === UPLOAD_ERR_NO_FILE) continue;
                if ($error !== UPLOAD_ERR_OK) throw new ValidationException($field, 'Загрузка не завершена. Максимальный размер изображения — 10 МБ');
                $file = $files['tmp_name'][$index] ?? '';
                if (!is_string($file) || !is_uploaded_file($file) || filesize($file) > self::MAX_BYTES) throw new ValidationException($field, 'Недопустимый файл или размер больше 10 МБ');
                $info = @getimagesize($file);
                if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)
                    || $info[0] < 1 || $info[1] < 1 || $info[0] > 6000 || $info[1] > 6000 || $info[0] * $info[1] > 24000000) {
                    throw new ValidationException($field, 'Выберите JPEG, PNG, WebP или GIF до 6000 × 6000 и 24 мегапикселей');
                }
                if (!function_exists('imagecreatefromstring')) throw new ValidationException($field, 'Для обработки изображений включите расширение GD в PHP');
                $image = @imagecreatefromstring(file_get_contents($file));
                if (!$image) throw new ValidationException($field, 'Изображение повреждено и не может быть обработано');
                if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                    $orientation = (@exif_read_data($file)['Orientation'] ?? 1);
                    if (in_array($orientation, [2, 4, 5, 7], true)) imageflip($image, IMG_FLIP_HORIZONTAL);
                    $angle = [3 => 180, 4 => 180, 5 => -90, 6 => -90, 7 => 90, 8 => 90][$orientation] ?? 0;
                    if ($angle) $image = imagerotate($image, $angle, 0);
                }
                $extension = function_exists('imagewebp') ? 'webp' : 'png';
                $name = 'upload-' . bin2hex(random_bytes(12)) . '.' . $extension;
                $directory = self::directory($group);
                if (!is_dir($directory . '/thumbs') && !mkdir($directory . '/thumbs', 0755, true) && !is_dir($directory . '/thumbs')) {
                    throw new ValidationException($field, 'Не удалось подготовить папку изображений');
                }
                $saved[] = $name;
                foreach ([1800 => $directory . '/' . $name, 420 => $directory . '/thumbs/' . $name] as $max => $destination) {
                    $ratio = min(1, $max / max(imagesx($image), imagesy($image)));
                    $width = max(1, (int)round(imagesx($image) * $ratio));
                    $height = max(1, (int)round(imagesy($image) * $ratio));
                    $resized = imagecreatetruecolor($width, $height);
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    imagefilledrectangle($resized, 0, 0, $width, $height, imagecolorallocatealpha($resized, 255, 255, 255, 127));
                    imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
                    $ok = $extension === 'webp' ? imagewebp($resized, $destination, 85) : imagepng($resized, $destination, 7);
                    imagedestroy($resized);
                    if (!$ok) throw new ValidationException($field, 'Не удалось сохранить изображение');
                }
                imagedestroy($image);
            }
            return $saved;
        } catch (\Throwable $error) {
            self::discard($saved, $group);
            throw $error;
        }
    }

    public static function discard($names, $group = 'products') {
        foreach ($names as $name) {
            if (preg_match('/\Aupload-[a-f0-9]{24}\.(webp|png)\z/', $name) !== 1) continue;
            foreach (['/', '/thumbs/'] as $suffix) {
                $file = self::directory($group) . $suffix . $name;
                if (is_file($file)) unlink($file);
            }
        }
    }

    public static function categoryIcons() {
        $icons = [];
        foreach (glob(BASE_PATH . '/public/images/icons/*.svg') ?: [] as $file) $icons[] = 'icons/' . basename($file);
        foreach (self::names('categories') as $name) $icons[] = 'categories/' . $name;
        return $icons;
    }

    public static function categoryUrl($path) {
        return APP_URL . '/public/images/' . implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}
