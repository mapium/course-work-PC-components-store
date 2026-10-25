<?php

namespace App\Helpers;

class Asset
{
    public static function css($path)
    {
        return APP_URL . '/public/css/' . $path;
    }

    public static function js($path)
    {
        return APP_URL . '/public/js/' . $path;
    }

    public static function image($path)
    {
        return APP_URL . '/public/images/' . $path;
    }

    public static function url($path = '')
    {
        return APP_URL . '/' . ltrim($path, '/');
    }
}
