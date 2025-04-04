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
    <link rel="stylesheet" href="public/css/dashboard.css">
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/footer.css">
</head>
<body>
    <?php include 'src/views/components/navbar.phtml'; ?>

    <main>
        <nav class="sidebar">
            <h2>Projects</h2>
            <div class="sidebar-items">
                <?php

                require_once 'src/functions/db_connect.php';

                $user = $jwtHandler->getUser();

                $stmt = $pdo->prepare("SELECT * FROM projects WHERE owner_id = :user_id");
                $stmt->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
                $stmt->execute();
                $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $currentProject = isset($_GET['id']) ? $_GET['id'] : null;

                foreach($projects as $project) {
                    echo '<a href="?id=' . $project["id"] . '" class="sidebar-item' . ($currentProject == $project["id"] ? ' active' : null) . '"><span class="text" title="' . htmlspecialchars($project['name']) . '">' . htmlspecialchars($project['name']) . '</span><span class="color-container"><input type="color" class="color" value="#' . htmlspecialchars($project['color']) . '"></span></a>';
                    // echo '<a href="#" class="sidebar-item"><span class="text" title="' . htmlspecialchars($project['name']) . '">' . htmlspecialchars($project['name']) . '</span><span class="color" style="background-color: #' . htmlspecialchars($project['color']) . ';"></span></a>';
                }
                if(count($projects) == 0) {
                    echo '<p class="sidebar-empty">No projects found</p>';
                }
                ?>
                
                <button href="#" class="sidebar-add-button">+</button>
                <div class="modal project-modal">
                    <form class="create-project-modal" action="/api/projects" method="POST">
                        <h2>Create Project</h2>
                        <span class="modal-inputs">
                            <label for="name">Project name</label>
                            <span>
                                <input type="text" id="name" name="name" placeholder="My awesome project" autocomplete="off" required>
                                <input type="color" name="color" value="#000000">
                            </span>
                        </span>
                        <span class="modal-buttons">
                            <button type="button" class="cancel-button">Cancel</button>
                            <button type="submit">Create</button>
                        </span>
                    </form>
                </div>
            </div>
        </nav>
        <div class="divider"></div>
        <div class="table">

        </div>
    </main>
    
    <?php include 'src/views/components/footer.phtml'; ?>

    <script src="public/js/navbar.js"></script>
    <script src="public/js/resize.js"></script>
    <script src="public/js/dashboard.js"></script>
</body>
</html>
