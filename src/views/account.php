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

    <main>
        <h1>Account</h1>
        <form class="account-info" action="/api/account" method="POST" enctype="multipart/form-data">
            <div class="account-details-container">
                <div class="account-info-container">
                    <span>
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" value="<?= htmlspecialchars($user["username"]) ?>" autocomplete="off">
                    </span>
                    <span>
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user["email"]) ?>" autocomplete="off">
                    </span>
                    <span>
                        <label for="password">Password</label>
                        <input type="password" name="password-old" id="password-old" placeholder="Old Password">
                        <input type="password" name="password" id="password" placeholder="New Password">
                    </span>
                </div>
                <div class="account-profile-container">
                    <img src="public/profile-pictures/<?= htmlspecialchars($user["id"]) ?>.jpg" alt="Profile Picture" id="profile-preview" onerror="this.onerror=null; this.src='public/profile-pictures/default.jpg'; console.clear();">
                    <input type="file" name="profile" id="profile" accept="image/*" onchange="document.getElementById('profile-preview').src = window.URL.createObjectURL(this.files[0])">
                </div>
            </div>
            <span class="buttons">
                <button type="button" onclick="location.reload()">Cancel</button>
                <button type="submit">Save Changes</button>
            </span>
        </form>
    </main>
    
    <?php include 'src/views/components/footer.phtml'; ?>

    <script src="public/js/navbar.js"></script>
</body>
</html>
