<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/dashboard.css">
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/footer.css">
</head>
<body>
    <?php include 'src/views/components/navbar.phtml'; ?>

    <main>
        <nav class="sidebar">
            <h2>Dashboard</h2>
            <div class="sidebar-items">
                <a href="#" class="sidebar-item">Project 1</a>
                <a href="#" class="sidebar-item active">Project 2</a>
                <a href="#" class="sidebar-item">Project 3</a>
                <a href="#" class="sidebar-item">Project 4</a>

                <a href="#" class="sidebar-item">Project 1</a>
                <a href="#" class="sidebar-item">Project 2</a>
                <a href="#" class="sidebar-item">Project 3</a>
                <a href="#" class="sidebar-item">Project 4</a>
                <a href="#" class="sidebar-item">Project 1</a>
                <a href="#" class="sidebar-item">Project 2</a>
                <a href="#" class="sidebar-item">Project 3</a>
                <a href="#" class="sidebar-item">Project 4</a>
                <a href="#" class="sidebar-item">Project 1</a>
                <a href="#" class="sidebar-item">Project 2</a>
                <a href="#" class="sidebar-item">Project 3</a>
                <a href="#" class="sidebar-item">Project 4</a>
                <a href="#" class="sidebar-item">Project 1</a>
                <a href="#" class="sidebar-item">Project 2</a>
                <a href="#" class="sidebar-item">Project 3</a>
                <a href="#" class="sidebar-item">Project 4</a>

                <button href="#" class="sidebar-add-button">+</button>
            </div>
        </nav>
        <div class="divider"></div>
        <div class="table">

        </div>
    </main>
    
    <?php include 'src/views/components/footer.phtml'; ?>

    <!-- <?php // include 'src/functions/db_connect.php'; ?>

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
    
    ?> -->

    <script src="public/js/sticky.js"></script>
</body>
</html>
