<?php
require "./src/functions/router.php";

$router = new Router();

$router->get("/", function() {
    return render("./src/views/landing.php");
});

$router->get("/login", function() {
    return render("./src/views/login.php");
});
$router->post("/login", function($data) {
    if(!count($data)) return;
    
    print_r($data);
    return;
});

$router->dispatch();

// Helper function to render pages
function render($path, $data = []) {
    extract($data);
    ob_start();
    require $path;
    return ob_get_clean();
}
