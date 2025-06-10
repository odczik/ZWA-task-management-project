<?php
/* =================================== */
/* This is awfull don't read this shit */
/* =================================== */

function login($data, $jwtHandler, $pdo) {
    $username = $data["username"];
    $password = $data["password"];
    $remember = isset($data["remember"]);
    
    $expiration = $remember ? time() + 3600 * 24 * 30 : time() + 3600;
    
    // Retrieve user from database
    $query = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["message" => "User not found!"]);
        return;
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["message" => "Invalid password!"]);
        return;
    }

    // Create JWT payload
    $payload = [
        "user_id" => $user['id'],
        "username" => $user['username'],
        "token_version" => $user['token_version'],
        "iat" => time(), // Issued at
        "exp" => $expiration
    ];
    
    // Create JWT
    $jwt = $jwtHandler->createJWT($payload);
    
    // Set the token cookie
    setcookie("token", $jwt, [
        'expires' => $expiration,
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => "Strict"
    ]);
    
    header("Location: /dashboard");
    exit;
}

function register($data, $jwtHandler, $pdo) {
    // Register user
    $username = trim($data['username']);
    $email = trim($data['email']);
    $password = $data['password'];
    // Hash the password (using bcrypt)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Check if the username is already taken
        $query = "SELECT COUNT(*) FROM users WHERE username = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $usernameExists = $stmt->fetchColumn();

        if ($usernameExists) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(["message" => "Username already in use."]);
            return;
        }

        // Prepare the SQL query to insert the user
        $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $pdo->prepare($query);

        // Bind parameters to prevent SQL injection
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

        // Execute the query
        $stmt->execute();

        // Login user
        login($data, $jwtHandler, $pdo);
    } catch (PDOException $e) {
        header('Content-Type: application/json; charset=utf-8');
        // Handle duplicate email error
        if ($e->errorInfo[1] == 1062) { // MySQL error code for duplicate entry
            echo json_encode(["message" => "Email already in use."]);
        } else {
            // Handle other errors
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    exit;
}

function logout() {
    setcookie("token", "", time() - 3600);
    header("Location: /");
    exit;
}

function hasInvites($jwtHandler){
    require "./src/functions/db_connect.php";

    $user = $jwtHandler->getUser();
    if (!$user) {
        return false;
    }

    // Fetch invites
    $stmt = $pdo->prepare("SELECT * FROM invitations WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
    $stmt->execute();
    $invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($invitations)) {
        return false;
    } else {
        return true;
    }
}

function deleteAccount($jwtHandler, $pdo) {
    $user = $jwtHandler->getUser();
    if (!$user) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["message" => "Unauthorized"]);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Delete profile picture file
        $profilePicturePath = "./public/profile-pictures/" . $user->user_id . ".jpg";
        if (file_exists($profilePicturePath)) {
            if (!unlink($profilePicturePath)) {
                $pdo->rollBack();
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(["message" => "Failed to delete profile picture"]);
                return;
            }
        }

        // Delete user from database
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $user->user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Also delete associated data (e.g., invitations, projects, tasks, etc.)
        // Delete invitations associated with the user
        $stmtInvites = $pdo->prepare("DELETE FROM invitations WHERE user_id = :user_id");
        $stmtInvites->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
        $stmtInvites->execute();

        // Fetch all projects owned by the user and delete them and their tasks
        $stmtProjects = $pdo->prepare("SELECT id FROM projects WHERE owner_id = :owner_id");
        $stmtProjects->bindParam(':owner_id', $user->user_id, PDO::PARAM_INT);
        $stmtProjects->execute();
        $projects = $stmtProjects->fetchAll(PDO::FETCH_ASSOC);
        foreach ($projects as $project) {
            // Delete tasks associated with the project
            $stmtTasks = $pdo->prepare("DELETE FROM tasks WHERE project_id = :project_id");
            $stmtTasks->bindParam(':project_id', $project['id'], PDO::PARAM_INT);
            $stmtTasks->execute();
            // Delete project members
            $stmtMembers = $pdo->prepare("DELETE FROM project_members WHERE project_id = :project_id");
            $stmtMembers->bindParam(':project_id', $project['id'], PDO::PARAM_INT);
            $stmtMembers->execute();
            // Delete the project itself
            $stmtDeleteProject = $pdo->prepare("DELETE FROM projects WHERE id = :project_id");
            $stmtDeleteProject->bindParam(':project_id', $project['id'], PDO::PARAM_INT);
            $stmtDeleteProject->execute();
        }

        // Delete project members
        $stmtMembers = $pdo->prepare("DELETE FROM project_members WHERE user_id = :user_id");
        $stmtMembers->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
        $stmtMembers->execute();
    } catch (PDOException $e) {
        $pdo->rollBack();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["message" => "Failed to delete account: " . $e->getMessage()]);
        return;
    }

    // Commit the transaction
    $pdo->commit();
    // Logout the user
    setcookie("token", "", time() - 3600, "/");
    header("Location: /");
    exit;
}