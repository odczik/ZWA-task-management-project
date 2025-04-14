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

                $stmt = $pdo->prepare("SELECT * FROM project_members WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
                $stmt->execute();
                $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $projects = array_map(function($project) use ($pdo) {
                    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
                    $stmt->bindParam(':id', $project['project_id'], PDO::PARAM_INT);
                    $stmt->execute();
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                }, $projects);

                $currentProject = isset($_GET['id']) ? $_GET['id'] : null;

                foreach($projects as $project) {
                    echo '<a href="?id=' . $project["id"] . '" class="sidebar-item' . ($currentProject == $project["id"] ? ' active' : null) . '"><span class="text" title="' . htmlspecialchars($project['name']) . '">' . htmlspecialchars($project['name']) . '</span><span class="color-container"><input type="color" class="color" value="#' . htmlspecialchars($project['color']) . '"></span></a>';
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
                                <span class="modal-color-container">
                                    <input type="color" name="color" class="color" value="#2b7a6d">
                                </span>
                            </span>
                            <label for="description">Description</label>
                            <span>
                                <input type="text" id="description" name="description" placeholder="My awesome project" autocomplete="off">
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
            <?php
            
            if($currentProject) {
                $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
                $stmt->bindParam(':id', $currentProject, PDO::PARAM_INT);
                $stmt->execute();
                $project = $stmt->fetch(PDO::FETCH_ASSOC);

                $member = null;

                if($project) {
                    $stmt = $pdo->prepare("SELECT * FROM project_members WHERE project_id = :project_id AND user_id = :user_id");
                    $stmt->bindParam(':project_id', $currentProject, PDO::PARAM_INT);
                    $stmt->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $member = $stmt->fetch(PDO::FETCH_ASSOC);
                }

                if(!$member) {
                    $project = null;
                }

                if($project) {
                    echo '<div class="table-header">';
                    echo '<div class="header-left">';
                    echo '<h2>' . htmlspecialchars($project['name']) . '</h2>';
                        echo '<span class="table-item icons">';
                            echo '<span class="icon-container role-icon">';
                            switch($member["role"]) {
                                case "owner": 
                                    echo '<span class="icon owner" title="Owner"></span>';
                                    break;
                                case "editor":
                                    echo '<span class="icon editor" title="Editor"></span>';
                                    break;
                                case "viewer":  
                                    echo '<span class="icon viewer" title="Viewer"></span>';
                                    break;
                            }
                            echo '</span>';
                            echo '<span class="icon-container public-icon"' . ($member["role"] == "owner" ? ' style="cursor: pointer;"' : null) . '>';
                            if($project["is_public"]) {
                                echo '<span class="icon public" title="Public"></span>';
                            } else {
                                echo '<span class="icon private" title="Private"></span>';
                            }
                            echo '</span>';
                            echo '<span class="icon-container anyone-icon"' . ($member["role"] == "owner" ? ' style="cursor: pointer;"' : null) . '>';
                            if($project["anyone_can_edit"]) {
                                echo '<span class="icon anyone" title="Anyone can edit"></span>';
                            } else {
                                echo '<span class="icon not-anyone" title="Only editors can edit"></span>';
                            }
                            echo '</span>';
                        echo '</span>';
                        // echo '<span class="table-item"><button onclick="fetch(\'/api/projects\', {method: \'DELETE\', body: JSON.stringify({id: ' . $project["id"] . '})})">Delete</button></span>';
                    echo '</div>';
                    echo '<div class="header-right">';
                        echo '<span style="color: var(--primary-dark);">Settings</span>';
                        echo '<span class="icon-container" style="cursor: pointer; background-color: var(--primary-dark);"><span class="icon settings" style="background-color: rgb(var(--primary-light-rgb), 0.8);"></span></span>';
                    ?>
                    <div class="modal settings-modal">
                        <form class="settings-form" action="/api/projects" method="PATCH">
                            <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                            <h2>Project settings</h2>
                            <span class="modal-inputs">
                                <label for="name">Project name</label>
                                <span>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($project['name']); ?>" placeholder="My awesome project" autocomplete="off" required>
                                    <span class="modal-color-container">
                                        <input type="color" name="color" class="color" value="#<?php echo htmlspecialchars($project['color']); ?>">
                                    </span>
                                </span>
                                <label for="description">Description</label>
                                <span>
                                    <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($project['description']); ?>" placeholder="My awesome project" autocomplete="off">
                                </span>
                                <span>
                                    <label for="is_public">Project visibility</label>
                                    <select name="is_public" id="is_public">
                                        <option value="1" <?php echo $project["is_public"] ? 'selected' : null; ?>>Public</option>
                                        <option value="0" <?php echo !$project["is_public"] ? 'selected' : null; ?>>Private</option>
                                    </select>
                                </span>
                                <span>
                                    <label for="anyone_can_edit">Who can edit this project</label>
                                    <select name="anyone_can_edit" id="anyone_can_edit">
                                        <option value="1" <?php echo $project["anyone_can_edit"] ? 'selected' : null; ?>>Anyone with a link</option>
                                        <option value="0" <?php echo !$project["anyone_can_edit"] ? 'selected' : null; ?>>Only editors</option>
                                    </select>
                                </span>
                            </span>
                            <span class="modal-buttons">
                                <button type="button" class="cancel-button settings-cancel-button">Cancel</button>
                                <button type="submit">Save</button>
                            </span>
                        </form>
                    </div>
                    <?php
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="table-body">';
                    echo '<div class="table-tasks">';

                    ?>
                    <div class="add-major-task">
                        <span>+</span>
                    </div>
                    <?php

                    echo '</div>';
                } else {
                    echo '<p class="sidebar-empty">Project not found</p>';
                }
            } else {
                echo '<p class="sidebar-empty">Select a project to view tasks</p>';
            }

            ?>
        </div>
    </main>
    
    <?php include 'src/views/components/footer.phtml'; ?>

    <script src="public/js/navbar.js"></script>
    <script src="public/js/resize.js"></script>
    <script src="public/js/dashboard.js"></script>
</body>
</html>
