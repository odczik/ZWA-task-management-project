<?php
require "./src/functions/router.php";

$router = new Router();

$router->get("/", function() {
    return render("./src/views/landing.php");
});

$router->dispatch();

// Helper function to render pages
function render($path, $data = []) {
    extract($data);
    ob_start();
    require $path;
    return ob_get_clean();
}