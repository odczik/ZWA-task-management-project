<?php
class Router {
    private $routes = [];

    public function get($path, $callback) {
        $this->routes["GET"][$path] = $callback;
    }

    public function post($path, $callback) {
        $this->routes["POST"][$path] = $callback($_POST);
    }

    public function dispatch() {
        $method = $_SERVER["REQUEST_METHOD"];
        $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

        if (isset($this->routes[$method][$path])) {
            echo call_user_func($this->routes[$method][$path]);
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }
    }
}
