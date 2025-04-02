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
    // Check if the user is already logged in
    if(isset($_COOKIE["token"])) {
        $jwt = $_COOKIE["token"];
        $payload = $jwtHandler->verifyJWT($jwt);
        if($payload) {
            header("Location: /dashboard");
            exit;
        }
    }
    // If not logged in, proceed with login
    // Check data
    if(!isset($data["username"]) || !isset($data["password"])) {
        return;
    }

    require "./src/functions/db_connect.php";
    return login($data, $jwtHandler, $pdo);
});
$router->post("/register", function($data) use ($jwtHandler) {
    if(!count($data)) return;

    // Check data
    if(!isset($data["username"]) || !isset($data["email"]) || !isset($data["password"])) {
        return;
    }

    require "./src/functions/db_connect.php";
    return register($data, $jwtHandler, $pdo);
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