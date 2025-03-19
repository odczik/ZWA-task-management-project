<?php
function login($data, $jwtHandler) {
    $username = $data["username"];
    $password = $data["password"];
        
    // TODO: User authentication (needs db)

    $payload = [
        "user_id" => 123,
        "username" => $username,
        "exp" => time() + 3600 // 1-hour expiration
    ];
    
    // Create JWT
    $jwt = $jwtHandler->createJWT($payload);

    // Set the token cookie
    setcookie("token", $jwt, [
        'expires' => time() + (60 * 60),
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => "Strict"
    ]);

    header("Location: /dashboard");
    exit;
}

function logout() {
    setcookie("token", "", time() - 3600);
    header("Location: /");
    exit;
}