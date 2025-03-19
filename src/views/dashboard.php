<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
    
    $token = $_COOKIE["token"];
    
    if(!$token) {
        header("Location: /login");
        exit;
    }
    
    // Verify JWT (decode and validate)
    $decoded = $jwtHandler->verifyJWT($token);
    echo "Token: " . $token . "<br>";
    echo "Decoded JWT:";
    echo "<pre>";
    print_r($decoded); // This will print the payload or error message
    echo "</pre>";
    
    if(is_array($decoded) && isset($decoded["error"])) {
        echo "Error: " . $decoded["error"];
        exit;
    }
    
    echo "User ID: " . $decoded->user_id . "<br>";
    echo "Username: " . $decoded->username . "<br>";
    
    ?>
</body>
</html>
