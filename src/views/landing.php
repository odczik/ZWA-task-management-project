<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing page</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/footer.css">
</head>
<body>
    <?php include 'src/views/components/navbar.phtml'; ?>

    <header>
        <h1>Welcome</h1>
        <p>This is the landing page</p>
    </header>

    <main class="home-content">
        Some content
    </main>

    <?php include 'src/views/components/footer.phtml'; ?>

    <script src="public/js/sticky.js"></script>
</body>
</html> 
