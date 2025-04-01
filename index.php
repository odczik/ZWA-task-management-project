<?php
require "./src/functions/router.php";
require "./src/functions/jwtHandler.php";
require "./src/functions/account.php";

$jwtHandler = new JWTHandler("your_secret_key");
$router = new Router();

$router->get("/", function() {
    return render("./src/views/landing.php");
});

$router->get("/dashboard", function() {
    return render("./src/views/dashboard.php");
});

$router->post("/login", function($data) use ($jwtHandler) {
    if(!count($data)) return;
    return login($data, $jwtHandler);
});
$router->post("/register", function($data) use ($jwtHandler) {
    if(!count($data)) return;
    return register($data, $jwtHandler);
});
$router->get("/logout", function() {
    return logout();
});

$router->get("/db", function() {
    require "./src/functions/db_connect.php";
    exit;
});

$router->dispatch();

// Helper function to render pages
function render($path, $data = []) {
    global $jwtHandler;
    $data["jwtHandler"] = $jwtHandler;

    extract($data);
    ob_start();
    require $path;
    return ob_get_clean();
}