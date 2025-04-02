<?php

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
        echo "User not found!";
        return;
    }
    print_r($user);
    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo "Invalid password!";
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

    // Prepare the SQL query to insert the user
    $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
    
    // Prepare the statement
    $stmt = $pdo->prepare($query);

    // Bind parameters to prevent SQL injection
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

    try {
        // Execute the query
        $stmt->execute();
        echo "Registration successful!";
    } catch (PDOException $e) {
        // Handle error
        echo "Error: " . $e->getMessage();
    }

    // Login user
    login($data, $jwtHandler, $pdo);
    exit;
}

function logout() {
    setcookie("token", "", time() - 3600);
    header("Location: /");
    exit;
}