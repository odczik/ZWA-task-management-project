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
            echo json_encode(["message" => "Username already exists!"]);
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
            echo json_encode(["message" => "Email already exists!"]);
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