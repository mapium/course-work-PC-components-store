<?php

namespace App\Helpers;

class Router {
    private $routes = [];
    private $currentRoute = null;

    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }

    public function resolve($method, $path) {
        $path = parse_url($path, PHP_URL_PATH);
        $path = str_replace(parse_url('http://localhost/course-work-PC-components-store', PHP_URL_PATH), '', $path);
        $path = trim($path, '/') ?: '/';

        if (isset($this->routes[$method][$path])) {
            return $this->routes[$method][$path];
        }

        // Простой роутинг с параметрами
        foreach ($this->routes[$method] ?? [] as $route => $callback) {
            if ($this->matchRoute($route, $path)) {
                return $callback;
            }
        }

        return null;
    }

    private function matchRoute($route, $path) {
        $route = preg_replace('@:([a-z_]+)@i', '(?P<$1>[^/]+)', $route);
        $route = '@^' . $route . '$@';
        return preg_match($route, $path);
    }
}
