<?php
class Router {
    private $routes = [];

    public function get    ($path, $callback) { $this->routes["GET"]    [$path] = $callback; }
    public function post   ($path, $callback) { $this->routes["POST"]   [$path] = $callback; }
    public function put    ($path, $callback) { $this->routes["PUT"]    [$path] = $callback; }
    public function delete ($path, $callback) { $this->routes["DELETE"] [$path] = $callback; }
    public function patch  ($path, $callback) { $this->routes["PATCH"]  [$path] = $callback; }

    public function dispatch() {
        $method = $_SERVER["REQUEST_METHOD"];
        $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

        // Parse the request body
        $body = file_get_contents("php://input");
        $data = $body ? json_decode($body, true) : [];

        if (isset($this->routes[$method][$path])) {
            echo call_user_func($this->routes[$method][$path], $data);
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }
    }
}
