<?php

namespace App\Helpers;

class Asset
{
    public static function css($path)
    {
        return self::versioned('css', $path);
    }

    public static function js($path)
    {
        return self::versioned('js', $path);
    }

    public static function image($path)
    {
        return self::versioned('images', $path);
    }

    public static function url($path = '')
    {
        return APP_URL . '/' . ltrim($path, '/');
    }

    private static function versioned($directory, $path)
    {
        $url = APP_URL . '/public/' . $directory . '/' . $path;
        $file = dirname(__DIR__, 2) . '/public/' . $directory . '/' . $path;

        return is_file($file) ? $url . '?v=' . filemtime($file) : $url;
    }
}
