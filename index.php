<?php
require "./src/functions/router.php";
require "./src/functions/jwtHandler.php";
require "./src/functions/account.php";
require "./src/functions/api/projects.php";
require "./src/functions/api/tasks.php";
require "./src/functions/api/profile.php";
require "./src/functions/api/members.php";

$jwtHandler = new JWTHandler("your_secret_key");
$router = new Router();

/* ========== */
/* Web Routes */
/* ========== */

$router->get("/", function() {
    return render("./src/views/landing.php");
});

$router->get("/invites", function() use ($jwtHandler) {
    // Decode the JWT to get user information
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("Location: /");
        exit;
    }

    return render("./src/views/invites.php", ["user" => $user]);
});

$router->get("/account", function() use ($jwtHandler) {
    // Check if the user is logged in
    if(!$jwtHandler->isLoggedIn()) {
        header("Location: /");
        exit;
    }
    // Decode the JWT to get user information
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("Location: /");
        exit;
    }

    // Fetch the user data from the database
    require "./src/functions/db_connect.php";
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $user->user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return render("./src/views/account.php", ["user" => $user]);
});

$router->get("/dashboard", function() use ($jwtHandler) {
    // Check if the user is logged in
    if(!$jwtHandler->isLoggedIn()) {
        header("Location: /");
        exit;
    }
    // Decode the JWT to get user information
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("Location: /");
        exit;
    }

    // Fetch the user data from the database
    require "./src/functions/db_connect.php";
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $user->user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return render("./src/views/dashboard.php", ["user" => $user]);
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

$router->post("/api/account", function($data) use ($jwtHandler) {
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return updateProfile($data, $user, $pdo, $jwtHandler);
});

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
$router->patch("/api/projects", function($data) use ($jwtHandler) {
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return updateProject($data, $user, $pdo);
});

$router->get("/api/tasks", function($data) use ($jwtHandler) { // return tasks for a project
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return getTasks($data, $user, $pdo);
});
$router->post("/api/tasks", function($data) use ($jwtHandler) { // create task
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return createTask($data, $user, $pdo);
});
$router->delete("/api/tasks", function($data) use ($jwtHandler) { // delete task
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return deleteTask($data, $user, $pdo);
});
$router->patch("/api/tasks", function($data) use ($jwtHandler) {  // update task
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return updateTask($data, $user, $pdo);
});

$router->delete("/api/members", function($data) use ($jwtHandler) { // remove member from project
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return removeMember($data, $user, $pdo);
});

$router->post("/api/invitation", function($data) use ($jwtHandler) { // invite member to project
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return inviteMember($data, $user, $pdo);
});
$router->delete("/api/invitation", function($data) use ($jwtHandler) { // revoke invite
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return revokeInvite($data, $user, $pdo);
});
$router->put("/api/invitation", function($data) use ($jwtHandler) { // revoke invite
    require "./src/functions/db_connect.php";
    $user = $jwtHandler->getUser();
    if(!$user) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
    }
    return handleInvite($data, $user, $pdo);
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
$router->get("/preferences-table", function() {
    require "./src/functions/db_connect.php";
    $query = "SELECT * FROM preferences";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $preferences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($preferences);
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