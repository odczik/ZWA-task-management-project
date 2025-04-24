<?php

function getTasks($data, $user, $pdo) {
    if(!isset($_GET["project_id"])) {
        header("HTTP/1.1 400 Bad Request");
        return json_encode(["error" => "Missing required fields"]);
    }

    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE project_id = :project_id");
    $stmt->bindParam(':project_id', $_GET["project_id"]);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header("Content-Type: application/json; charset=utf-8");
    return json_encode($tasks);
}

function createTask($data, $user, $pdo) {
    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Fetch the project details
        $stmt = $pdo->prepare("SELECT is_public, anyone_can_edit FROM projects WHERE id = :project_id");
        $stmt->bindParam(':project_id', $data["project_id"]);
        $stmt->execute();
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        // Validate user permissions
        $stmt = $pdo->prepare("SELECT * FROM project_members WHERE project_id = :project_id AND user_id = :user_id");
        $stmt->bindParam(':project_id', $data["project_id"]);
        $stmt->bindParam(':user_id', $user->user_id);
        $stmt->execute();
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$member && (!$project["anyone_can_edit"] || !$project["is_public"])) {
            $pdo->rollBack();
            header("HTTP/1.1 403 Forbidden");
            return json_encode(["error" => "You do not have permission to modify this project"]);
        }

        if (isset($data["is_major"])) {
            if (!isset($data["title"]) || !isset($data["project_id"])) {
                $pdo->rollBack();
                header("HTTP/1.1 400 Bad Request");
                return json_encode(["error" => "Missing required fields"]);
            }

            $position = 0;
            $stmt = $pdo->prepare("SELECT MAX(position) FROM tasks WHERE project_id = :project_id AND assigned_under IS NULL");
            $stmt->bindParam(':project_id', $data["project_id"]);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && $result["MAX(position)"] !== null) {
                $position = $result["MAX(position)"] + 1;
            }

            $stmt = $pdo->prepare("INSERT INTO tasks (title, project_id, is_major, position) VALUES (:title, :project_id, :is_major, :position)");
            $stmt->bindParam(':title', $data["title"]);
            $stmt->bindParam(':project_id', $data["project_id"]);
            $stmt->bindParam(':is_major', $data["is_major"]);
            $stmt->bindParam(':position', $position);
            $stmt->execute();

            $task_id = $pdo->lastInsertId();

            // Commit the transaction
            $pdo->commit();
            header("HTTP/1.1 201 Created");
            header("Content-Type: application/json; charset=utf-8");
            return json_encode(["task_id" => $task_id, "position" => $position]);
        } else {
            if (!isset($data["title"]) || !isset($data["project_id"]) || !isset($data["assigned_under"])) {
                $pdo->rollBack();
                header("HTTP/1.1 400 Bad Request");
                return json_encode(["error" => "Missing required fields"]);
            }

            $position = 0;
            $stmt = $pdo->prepare("SELECT MAX(position) FROM tasks WHERE project_id = :project_id AND assigned_under = :assigned_under");
            $stmt->bindParam(':project_id', $data["project_id"]);
            $stmt->bindParam(':assigned_under', $data["assigned_under"]);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && $result["MAX(position)"] !== null) {
                $position = $result["MAX(position)"] + 1;
            }

            $stmt = $pdo->prepare("INSERT INTO tasks (title, project_id, assigned_under, position) VALUES (:title, :project_id, :assigned_under, :position)");
            $stmt->bindParam(':title', $data["title"]);
            $stmt->bindParam(':project_id', $data["project_id"]);
            $stmt->bindParam(':assigned_under', $data["assigned_under"]);
            $stmt->bindParam(':position', $position);
            $stmt->execute();

            $task_id = $pdo->lastInsertId();

            // Commit the transaction
            $pdo->commit();
            header("HTTP/1.1 201 Created");
            header("Content-Type: application/json; charset=utf-8");
            return json_encode(["task_id" => $task_id, "position" => $position]);
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        header("HTTP/1.1 500 Internal Server Error");
        return json_encode(["error" => "Failed to create task: " . $e->getMessage()]);
    }
}

function updateTask($data, $user, $pdo) {
    if(isset($data["position"])) {
        try {
            // Start a transaction
            $pdo->beginTransaction();

            // Fetch the project details
            $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = (SELECT project_id FROM tasks WHERE id = :task_id)");
            $stmt->bindParam(':task_id', $data["task_id"]);
            $stmt->execute();
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // Validate user permissions
            $stmt = $pdo->prepare("SELECT * FROM project_members WHERE project_id = (SELECT project_id FROM tasks WHERE id = :task_id) AND user_id = :user_id");
            $stmt->bindParam(':task_id', $data["task_id"]);
            $stmt->bindParam(':user_id', $user->user_id);
            $stmt->execute();
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ((!$member && (!$project["anyone_can_edit"] || !$project["is_public"])) || $member["role"] == "viewer") {
                $pdo->rollBack();
                header("HTTP/1.1 403 Forbidden");
                return json_encode(["error" => "You do not have permission to modify this task"]);
            }
    
            if (!isset($data["task_id"]) || !isset($data["position"])) {
                $pdo->rollBack();
                header("HTTP/1.1 400 Bad Request");
                return json_encode(["error" => "Missing required fields"]);
            }
    
            if(isset($data["assigned_under"])) {
                $stmt = $pdo->prepare("UPDATE tasks SET position = :position, assigned_under = :assigned_under WHERE id = :task_id");
                $stmt->bindParam(':assigned_under', $data["assigned_under"]);
            } else {
                $stmt = $pdo->prepare("UPDATE tasks SET position = :position WHERE id = :task_id");
            }
            $stmt->bindParam(':position', $data["position"]);
            $stmt->bindParam(':task_id', $data["task_id"]);
            $stmt->execute();
    
            // Commit the transaction
            $pdo->commit();
            header("HTTP/1.1 200 OK");
            return json_encode(["message" => "Task position updated successfully"]);
        } catch (Exception $e) {
            $pdo->rollBack();
            header("HTTP/1.1 500 Internal Server Error");
            return json_encode(["error" => "Failed to update task position: " . $e->getMessage()]);
        }
    } else if(isset($data["title"])) {
        try {
            // Start a transaction
            $pdo->beginTransaction();
    
            // Validate user permissions
            $stmt = $pdo->prepare("SELECT * FROM project_members WHERE project_id = (SELECT project_id FROM tasks WHERE id = :task_id) AND user_id = :user_id");
            $stmt->bindParam(':task_id', $data["task_id"]);
            $stmt->bindParam(':user_id', $user->user_id);
            $stmt->execute();
            $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$member) {
                $pdo->rollBack();
                header("HTTP/1.1 403 Forbidden");
                return json_encode(["error" => "You do not have permission to modify this task"]);
            }
    
            if (!isset($data["task_id"]) || !isset($data["title"])) {
                $pdo->rollBack();
                header("HTTP/1.1 400 Bad Request");
                return json_encode(["error" => "Missing required fields"]);
            }
    
            $stmt = $pdo->prepare("UPDATE tasks SET title = :title WHERE id = :task_id");
            $stmt->bindParam(':title', $data["title"]);
            $stmt->bindParam(':task_id', $data["task_id"]);
            $stmt->execute();
    
            // Commit the transaction
            $pdo->commit();
            header("HTTP/1.1 200 OK");
            return json_encode(["message" => "Task title updated successfully"]);
        } catch (Exception $e) {
            $pdo->rollBack();
            header("HTTP/1.1 500 Internal Server Error");
            return json_encode(["error" => "Failed to update task title: " . $e->getMessage()]);
        }
    } else {
        header("HTTP/1.1 400 Bad Request");
        return json_encode(["error" => "Missing required fields"]);
    }
}

function deleteTask($data, $user, $pdo) {
    if (!isset($data["task_id"])) {
        header("HTTP/1.1 400 Bad Request");
        return json_encode(["error" => "Missing required fields"]);
    }
    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Fetch the project details
        $stmt = $pdo->prepare("SELECT anyone_can_edit FROM projects WHERE id = :project_id");
        $stmt->bindParam(':project_id', $data["project_id"]);
        $stmt->execute();
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        // Validate user permissions
        $stmt = $pdo->prepare("SELECT * FROM project_members WHERE project_id = (SELECT project_id FROM tasks WHERE id = :task_id) AND user_id = :user_id");
        $stmt->bindParam(':task_id', $data["task_id"]);
        $stmt->bindParam(':user_id', $user->user_id);
        $stmt->execute();
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$member && (!$project["anyone_can_edit"] || !$project["is_public"])) {
            $pdo->rollBack();
            header("HTTP/1.1 403 Forbidden");
            return json_encode(["error" => "You do not have permission to delete this task"]);
        }

        // Get task details
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = :task_id");
        $stmt->bindParam(':task_id', $data["task_id"]);
        $stmt->execute();
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$task) {
            $pdo->rollBack();
            header("HTTP/1.1 404 Not Found");
            return json_encode(["error" => "Task not found"]);
        }
        // Check if the task is a major task
        if ($task["is_major"]) {
            // Delete all subtasks
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE assigned_under = :task_id");
            $stmt->bindParam(':task_id', $data["task_id"]);
            $stmt->execute();
        }

        // Delete the task
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :task_id");
        $stmt->bindParam(':task_id', $data["task_id"]);
        $stmt->execute();

        // Commit the transaction
        $pdo->commit();
        header("HTTP/1.1 200 OK");
        return json_encode(["message" => "Task deleted successfully"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        header("HTTP/1.1 500 Internal Server Error");
        return json_encode(["error" => "Failed to delete task: " . $e->getMessage()]);
    }
}