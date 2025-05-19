<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mango | Dashboard</title>
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

                require 'src/functions/db_connect.php';

                $stmt = $pdo->prepare("SELECT * FROM project_members WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $user["id"], PDO::PARAM_INT);
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
                    echo '<a href="?id=' . $project["id"] . '" class="sidebar-item' . ($currentProject == $project["id"] ? ' active' : null) . '"><span class="text" title="' . htmlspecialchars($project['name']) . '">' . htmlspecialchars($project['name']) . '</span><span class="color-container" style="background-color: #' . htmlspecialchars($project['color']) . ';"></span></a>';
                }
                if(count($projects) == 0) {
                    echo '<p class="sidebar-empty">No projects found</p>';
                }
                ?>

                <button class="sidebar-add-button">+</button>
                <div class="modal project-modal">
                    <form class="create-project-modal" action="/api/projects" method="POST">
                        <h2>Create Project</h2>
                        <span class="modal-inputs">
                            <label for="project-name">Project name</label>
                            <span>
                                <input type="text" id="project-name" name="name" placeholder="My awesome project" autocomplete="off" required>
                                <span class="modal-color-container">
                                    <input type="color" name="color" class="color" value="#2b7a6d">
                                </span>
                            </span>
                            <label for="project-description">Description</label>
                            <span>
                                <input type="text" id="project-description" name="description" placeholder="My awesome project" autocomplete="off">
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
                    $stmt->bindParam(':user_id', $user["id"], PDO::PARAM_INT);
                    $stmt->execute();
                    $member = $stmt->fetch(PDO::FETCH_ASSOC);
                }

                $isMember = false;
                if($member) $isMember = true;

                $canEdit = false;
                if(($member && ($member["role"] == "owner" || $member["role"] == "editor")) || ($project && $project["anyone_can_edit"])) {
                    $canEdit = true;
                }

                if(!$member && $project && !$project["is_public"]) {
                    $project = null;
                }

                if(!$member && isset($project["is_public"])) {
                    $member = [
                        "role" => $project["anyone_can_edit"] ? "editor" : "viewer"
                    ];
                }

                if($project) {
                    echo '<div class="table-header"' . ($canEdit ? " data-can-edit=\"true\"": "") . '>';
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
                            echo '<span class="icon-container public-icon">';
                            if($project["is_public"]) {
                                echo '<span class="icon public" title="Public"></span>';
                            } else {
                                echo '<span class="icon private" title="Private"></span>';
                            }
                            echo '</span>';
                            echo '<span class="icon-container anyone-icon">';
                            if($project["anyone_can_edit"]) {
                                echo '<span class="icon anyone" title="Anyone can edit"></span>';
                            } else {
                                echo '<span class="icon not-anyone" title="Only editors can edit"></span>';
                            }
                            echo '</span>';
                        echo '</span>';

                        if($isMember){
                        ?>
                        <span class="table-item members">
                            <button id="manage-members-button"><?php echo ($user["id"] == $project["owner_id"]) ? "Manage members" : "Project members" ?></button>
                        </span>
                        <div class="modal members-modal">
                            <form action="/api/members" method="POST">
                                <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                                <?php
                                // Fetch project members & invitees
                                $stmt = $pdo->prepare("SELECT * FROM project_members WHERE project_id = :project_id");
                                $stmt->bindParam(':project_id', $project['id'], PDO::PARAM_INT);
                                $stmt->execute();
                                $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $stmt = $pdo->prepare("SELECT * FROM invitations WHERE project_id = :project_id");
                                $stmt->bindParam(':project_id', $project['id'], PDO::PARAM_INT);
                                $stmt->execute();
                                $invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                // Display project members
                                echo '<h3>Members</h3>';
                                foreach($members as $member) {
                                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
                                    $stmt->bindParam(':id', $member['user_id'], PDO::PARAM_INT);
                                    $stmt->execute();
                                    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                                    echo '<div class="modal-member">';
                                    echo '<div class="modal-member-left">';
                                        echo '<span class="modal-member-profile-picture"><img src="public/profile-pictures/' . htmlspecialchars($userInfo['id']) . '.jpg" alt="Profile Picture" onerror="this.onerror=null; this.src=`public/profile-pictures/default.jpg`; console.clear();"></span>';
                                        echo '<span class="modal-member-name">' . htmlspecialchars($userInfo['username']) . '</span>';
                                        echo '<span class="icon-container role-icon' . (($member["role"] != "owner" && $user["id"] == $project["owner_id"]) ? (" member-role-button\" style=\"cursor: pointer;\" data-member-id=\"" .  $member["user_id"] . "\" data-member-role=\"" . $member["role"] . "\""): "\"") . '>';
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
                                    echo '</div>';
                                    echo '<div class="modal-member-right">';
                                        if($member["role"] != "owner" && $project["owner_id"] == $user["id"]) {
                                            echo '<button type="button" class="remove-member-button" onclick="fetch(\'/api/members\', {method: \'DELETE\', body: JSON.stringify({member_id: ' . $member["user_id"] . ', project_id: ' . $project["id"] . '})}).then(response => {if (!response.ok) {throw new Error(`HTTP error! status: ${response.status}`);}return response.json();}).then(data => {location.reload();}).catch(e => {alert(e);location.reload();});">Remove</button>';
                                        }
                                    echo '</div>';
                                    echo '</div>';
                                }
                                // Display project invitees
                                echo '<h3>Invitations</h3>';
                                foreach($invitations as $invitation) {
                                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
                                    $stmt->bindParam(':id', $invitation['user_id'], PDO::PARAM_INT);
                                    $stmt->execute();
                                    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                                    echo '<span class="modal-member">';
                                        echo '<span class="modal-member-name">' . htmlspecialchars($userInfo['username']) . '</span>';
                                        if($project["owner_id"] == $user["id"]) {
                                            echo '<button type="button" class="remove-member-button" onclick="fetch(\'/api/invitation\', {method: \'DELETE\', body: JSON.stringify({id: ' . $invitation["id"] . '})}).then(response => {if (!response.ok) {throw new Error(`HTTP error! status: ${response.status}`);}return response.json();}).then(data => {location.reload();}).catch(e => {alert(e);});">Remove</button>';
                                        }
                                    echo '</span>';
                                }
                                if(count($invitations) == 0) {
                                    echo '<p class="sidebar-empty">No invitations found</p>';
                                }
                                ?>
                                <?php
                                if($user["id"] == $project["owner_id"]) {
                                    echo '<button id="add-member-button">Invite</button>';
                                }
                                ?>
                            </form>
                        </div>
                        <?php
                        }
                        // echo '<span class="table-item"><button onclick="fetch(\'/api/projects\', {method: \'DELETE\', body: JSON.stringify({id: ' . $project["id"] . '})})">Delete</button></span>';
                    echo '</div>';
                    ?>
                    <div class="header-right">
                    <?php if($user["id"] == $project["owner_id"]) { ?>
                        <span style="color: var(--primary-dark);">Settings</span>
                        <span class="icon-container settings-button" style="cursor: pointer; background-color: var(--primary-dark);"><span class="icon settings" style="background-color: rgb(var(--primary-light-rgb), 0.8);"></span></span>
                        <div class="modal settings-modal">
                            <form class="settings-form" action="/api/projects" method="POST">
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
                                            <option value="1" <?php echo $project["anyone_can_edit"] ? 'selected' : null; ?>>Anyone with access to the project</option>
                                            <option value="0" <?php echo !$project["anyone_can_edit"] ? 'selected' : null; ?>>Only editors</option>
                                        </select>
                                    </span>
                                </span>
                                <div class="modal-buttons">
                                    <div>
                                        <button type="button" class="settings-delete-button">Delete</button>
                                    </div>
                                    <div>
                                        <button type="button" class="cancel-button settings-cancel-button">Cancel</button>
                                        <button type="submit">Save</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php } else if($isMember) { ?>
                        <button class="leave-button">Leave</button>
                    <?php } ?>
                    </div>
                    </div>
                    <div class="table-body">
                    <div class="table-tasks">
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
        </div>
    </main>
    
    <?php include 'src/views/components/footer.phtml'; ?>

    <script src="public/js/navbar.js"></script>
    <script src="public/js/resize.js"></script>
    <script src="public/js/dashboard.js"></script>
</body>
</html>
