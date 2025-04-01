<?php
function login($data, $jwtHandler) {
    $username = $data["username"];
    $password = $data["password"];
    $remember = $data["remember"];

    $expiration = $remember ? time() + 3600 * 24 * 30 : time() + 3600;
        
    // TODO: User authentication (needs db)

    $payload = [
        "user_id" => 123,
        "username" => $username,
        "exp" => $expiration // 30 day expiration
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

function register($data, $jwtHandler) {
    // register

    // login

    header("Location: /dashboard");
    exit;
}

function logout() {
    setcookie("token", "", time() - 3600);
    header("Location: /");
    exit;
}