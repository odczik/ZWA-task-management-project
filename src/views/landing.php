<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing page</title>
</head>
<body>
    <?php include 'src/views/components/navbar.phtml'; ?>

    <h1>Welcome</H1>

    <?php
        require __DIR__ . '/../functions/jwtHandler.php';

        // Initialize the handler with your secret key
        $jwtHandler = new JWTHandler("your_secret_key");
        
        // Example Payload (data you want to store in the token)
        $payload = [
            "user_id" => 123,
            "email" => "user@example.com",
            "exp" => time() + 3600 // 1-hour expiration
        ];
        
        // Create JWT
        $jwt = $jwtHandler->createJWT($payload);
        echo "Generated JWT: " . $jwt . "<br>";

        // // Set the token cookie
        // setcookie("token", $jwt, [
        //     'expires' => time() + (60 * 60 * 24),
        //     'secure' => isset($_SERVER['HTTPS']),
        //     'httponly' => true,
        //     'samesite' => "Strict"  // "Strict", "Lax", or "None"
        // ]);

        // Verify JWT (decode and validate)
        $decoded = $jwtHandler->verifyJWT($jwt);
        echo "Decoded JWT:";
        echo "<pre>";
        print_r($decoded); // This will print the payload or error message
        echo "</pre>";
    ?>

    <?php include 'src/views/components/footer.phtml'; ?>
</body>
</html> 