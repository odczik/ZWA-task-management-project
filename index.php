<?php
require "./src/functions/router.php";
require "./src/functions/jwtHandler.php";
require "./src/functions/account.php";
require "./src/functions/api/projects.php";

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

/* API Routes */
$router->post("/api/projects", function($data) use ($jwtHandler) {
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return createProject($data, $user, $pdo);
});

/* DB DEBUG */
$router->get("/users-table", function() {
    require "./src/functions/db_connect.php";
    $query = "SELECT * FROM users";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($users);
    exit;
});
$router->get("/projects-table", function() {
    require "./src/functions/db_connect.php";
    $query = "SELECT * FROM projects";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($users);
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