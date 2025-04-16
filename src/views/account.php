<?php

if(!$jwtHandler->isLoggedIn()) {
    header("Location: /");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="icon" type="image/x-icon" href="public/favicon.ico">
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/account.css">
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/footer.css">
</head>
<body>
    <?php include 'src/views/components/navbar.phtml'; ?>

    <?php print_r($user) ?>
    <main>
        <div class="container">
            <h1>Account</h1>
            <div class="account-info">
                <h2>Account Information</h2>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user["username"]); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user["email"]); ?></p>
                <p><strong>Created At:</strong> <?php echo htmlspecialchars($user["created_at"]); ?></p>
            </div>
            <div class="account-actions">
                <h2>Actions</h2>
                <a href="/logout" class="btn">Logout</a>
            </div>
        </div>
    </main>
    
    <?php include 'src/views/components/footer.phtml'; ?>

    <script src="public/js/navbar.js"></script>
</body>
</html>
