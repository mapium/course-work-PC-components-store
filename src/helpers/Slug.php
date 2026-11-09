<?php

namespace App\Helpers;

class Slug {
    public static function make($name, $limit = 240) {
        $letters = preg_split('//u', 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя', -1, PREG_SPLIT_NO_EMPTY);
        $latin = ['a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','kh','ts','ch','sh','shch','','y','','e','yu','ya'];
        $upper = preg_split('//u', 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ', -1, PREG_SPLIT_NO_EMPTY);
        $name = strtr($name, array_combine($letters, $latin) + array_combine($upper, $latin));
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        return trim(substr(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name)), 0, $limit), '-') ?: 'item';
    }

    public static function unique($pdo, $table, $name, $id = null) {
        if (!in_array($table, ['products', 'categories'], true)) throw new \InvalidArgumentException('Invalid slug table');
        $base = self::make($name, $table === 'categories' ? 85 : 235);
        $slug = $base;
        $stmt = $pdo->prepare("SELECT id FROM $table WHERE slug = ? AND id <> ?");
        for ($number = 2; ; $number++) {
            $stmt->execute([$slug, $id ?? 0]);
            if (!$stmt->fetchColumn()) return $slug;
            $slug = $base . '-' . $number;
        }
    }
}
