<?php
require "./src/functions/router.php";
require "./src/functions/jwtHandler.php";
require "./src/functions/account.php";

$jwtHandler = new JWTHandler("your_secret_key");
$router = new Router();

$router->get("/", function() {
    return render("./src/views/landing.php");
});

$router->get("/dashboard", function() use ($jwtHandler) {
    return render("./src/views/dashboard.php", ["jwtHandler" => $jwtHandler]);
});

$router->get("/login", function() {
    return render("./src/views/login.php");
});
$router->post("/login", function($data) use ($jwtHandler) {
    if(!count($data)) return;
    return login($data, $jwtHandler);
});

$router->dispatch();

// Helper function to render pages
function render($path, $data = []) {
    extract($data);
    ob_start();
    require $path;
    return ob_get_clean();
}
