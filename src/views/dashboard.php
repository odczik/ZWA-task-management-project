<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/footer.css">
</head>
<body>
    <?php include 'src/views/components/navbar.phtml'; ?>

    <header>
        <h1>Dashboard</h1>
        <p>This is the dashboard</p>
    </header>

    <?php include 'src/functions/db_connect.php'; ?>

    <?php
    
    $token = $_COOKIE["token"];
    
    if(!$token) {
        header("Location: /");
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

    <?php include 'src/views/components/footer.phtml'; ?>
    
    <script src="public/js/sticky.js"></script>
</body>
</html>
