<?php
require "./src/functions/router.php";
require "./src/functions/jwtHandler.php";
require "./src/functions/account.php";
require "./src/functions/api/projects.php";
require "./src/functions/api/tasks.php";

$jwtHandler = new JWTHandler("your_secret_key");
$router = new Router();

/* ========== */
/* Web Routes */
/* ========== */

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

/* ========== */
/* API Routes */
/* ========== */

$router->post("/api/projects", function($data) use ($jwtHandler) {
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return createProject($data, $user, $pdo);
});
$router->delete("/api/projects", function($data) use ($jwtHandler) {
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return deleteProject($data, $user, $pdo);
});

$router->get("/api/tasks", function($data) use ($jwtHandler) {
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return getTasks($data, $user, $pdo);
});
$router->post("/api/tasks", function($data) use ($jwtHandler) {
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return createTask($data, $user, $pdo);
});
$router->delete("/api/tasks", function($data) use ($jwtHandler) {
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return deleteTask($data, $user, $pdo);
});
$router->patch("/api/tasks", function($data) use ($jwtHandler) {
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return updatePosition($data, $user, $pdo);
});

/* ======== */
/* DB DEBUG */
/* ======== */

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
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($projects);
    exit;
});
$router->get("/project-members-table", function() {
    require "./src/functions/db_connect.php";
    $query = "SELECT * FROM project_members";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($members);
    exit;
});
$router->get("/tasks-table", function() {
    require "./src/functions/db_connect.php";
    $query = "SELECT * FROM tasks";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($tasks);
    exit;
});
$router->get("/invitations-table", function() {
    require "./src/functions/db_connect.php";
    $query = "SELECT * FROM invitations";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($tasks);
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