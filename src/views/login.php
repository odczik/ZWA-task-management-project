<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/footer.css">
</head>
<body>
    <?php include 'src/views/components/navbar.phtml'; ?>

    <div class="container">
        <h1>Login</H1>
    
        <form action="/login" method="POST">
            <div>
                <span>
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username">
                </span>
                <span>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password">
                </span>
            </div>
        
            <input type="submit" value="Login">
        </form>
    </div>

    <?php include 'src/views/components/footer.phtml'; ?>
</body>
</html> 
