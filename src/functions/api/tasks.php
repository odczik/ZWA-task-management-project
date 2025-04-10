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

    return json_encode($tasks);
}

function createTask($data, $user, $pdo) {
    $stmt = null;

    if(isset($data["is_major"])) {
        if(!isset($data["title"]) || !isset($data["project_id"])) {
            header("HTTP/1.1 400 Bad Request");
            return json_encode(["error" => "Missing required fields"]);
        }

        $stmt = $pdo->prepare("INSERT INTO tasks (title, project_id, is_major) VALUES (:title, :project_id, :is_major)");
        $stmt->bindParam(':title', $data["title"]);
        $stmt->bindParam(':project_id', $data["project_id"]);
        $stmt->bindParam(':is_major', $data["is_major"]);

        if($stmt->execute()) {
            $task_id = $pdo->lastInsertId();
            
            header("HTTP/1.1 201 Created");
            header("Content-Type: application/json; charset=utf-8");
            return json_encode(["task_id" => $task_id]);
        } else {
            return header("HTTP/1.1 500 Internal Server Error");
        }
    } else {
        if(!isset($data["title"]) || !isset($data["project_id"]) || !isset($data["assigned_under"])) {
            header("HTTP/1.1 400 Bad Request");
            return json_encode(["error" => "Missing required fields"]);
        }

        $position = 0;
        $stmt = $pdo->prepare("SELECT MAX(position) FROM tasks WHERE project_id = :project_id AND assigned_under = :assigned_under");
        $stmt->bindParam(':project_id', $data["project_id"]);
        $stmt->bindParam(':assigned_under', $data["assigned_under"]);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if($result && $result["MAX(position)"] !== null) {
            $position = $result["MAX(position)"] + 1;
        }
        $stmt = null; // Reset the statement

        $stmt = $pdo->prepare("INSERT INTO tasks (title, project_id, assigned_under, position) VALUES (:title, :project_id, :assigned_under, :position)");
        $stmt->bindParam(':title', $data["title"]);
        $stmt->bindParam(':project_id', $data["project_id"]);
        $stmt->bindParam(':assigned_under', $data["assigned_under"]);
        $stmt->bindParam(':project_id', $data["project_id"]);
        $stmt->bindParam(':position', $position);

        if($stmt->execute()) {
            return header("HTTP/1.1 201 Created");
        } else {
            return header("HTTP/1.1 500 Internal Server Error");
        }
    }
}