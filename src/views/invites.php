<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="icon" type="image/x-icon" href="public/favicon.ico">
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/invites.css">
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/footer.css">
</head>
<body>
    <?php include 'src/views/components/navbar.phtml'; ?>

    <main>
        <?php
        require 'src/functions/db_connect.php';

        // Fetch invites
        $stmt = $pdo->prepare("SELECT * FROM invitations WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
        $stmt->execute();
        $invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($invitations as $invite) {
            // Fetch project details
            $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :project_id");
            $stmt->bindParam(':project_id', $invite['project_id'], PDO::PARAM_INT);
            $stmt->execute();
            $project = $stmt->fetch(PDO::FETCH_ASSOC);

            // Invited by user details
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $invite['invited_by'], PDO::PARAM_INT);
            $stmt->execute();
            $invitedBy = $stmt->fetch(PDO::FETCH_ASSOC);

            // Display the invitation
            echo "<div class='invite'>";
            echo "<h2>Project name: " . htmlspecialchars($project['name']) . "</h2>";
            echo "<p>Invited by: " . htmlspecialchars($invitedBy["username"]) . "</p>";
            echo '<button type="button" class="accept-button" onclick="fetch(\'/api/invitation\', {method: \'PUT\', body: JSON.stringify({id: ' . $invite["id"] . ', accept: true})}).then(response => {if (!response.ok) {throw new Error(`HTTP error! status: ${response.status}`);}return response.json();}).then(data => {location.href = `/dashboard?id=${data.id}`}).catch(e => {alert(e);});">Accept</button>';
            echo '<button type="button" class="decline-button" onclick="fetch(\'/api/invitation\', {method: \'PUT\', body: JSON.stringify({id: ' . $invite["id"] . '})}).then(response => {if (!response.ok) {throw new Error(`HTTP error! status: ${response.status}`);}return response.json();}).then(data => {location.reload()}).catch(e => {alert(e);});">Decline</button>';
            echo "</div>";
        }

        if (empty($invitations)) {
            echo "<p>No invitations found.</p>";
        }
        ?>
    </main>
    
    <?php include 'src/views/components/footer.phtml'; ?>

    <script src="public/js/navbar.js"></script>
</body>
</html>
