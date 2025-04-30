<?php

function createProject($data, $user, $pdo) {
    if(!isset($data["name"]) || !isset($data["color"])) {
        header("HTTP/1.1 400 Bad Request");
        return json_encode(["error" => "Missing required fields"]);
    }

    $data["color"] = substr($data["color"], 1); // Remove the '#' from the color

    $stmt = $pdo->prepare("INSERT INTO projects (name, description, color, owner_id) VALUES (:name, :description, :color, :owner_id)");
    $stmt->bindParam(':name', $data["name"]);
    $stmt->bindParam(':description', $data["description"]);
    $stmt->bindParam(':color', $data["color"]);
    $stmt->bindParam(':owner_id', $user->user_id);
    
    if($stmt->execute()) {
        $project_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (:project_id, :user_id, :role)");
        $stmt->bindParam(':project_id', $project_id);
        $stmt->bindParam(':user_id', $user->user_id);
        $role = "owner"; // Default role for the owner
        $stmt->bindParam(':role', $role);
        $stmt->execute();

        header("HTTP/1.1 201 Created");
        return json_encode(["message" => "Project created successfully", "project_id" => $project_id]);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        return json_encode(["error" => "Failed to create project"]);
    }
}

function deleteProject($data, $user, $pdo) {
    if(!isset($data["id"])) {
        header("HTTP/1.1 400 Bad Request");
        return json_encode(["error" => "Missing required fields"]);
    }

    $stmt = $pdo->prepare("SELECT * FROM project_members WHERE project_id = :id AND role = 'owner' AND user_id = :user_id");
    $stmt->bindParam(':id', $data["id"]);
    $stmt->bindParam(':user_id', $user->user_id);
    $stmt->execute();
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$owner) {
        header("HTTP/1.1 403 Forbidden");
        return json_encode(["error" => "You are not the owner of this project"]);
    }

    try {
        $pdo->beginTransaction();

        // Delete project members first
        $stmt = $pdo->prepare("DELETE FROM project_members WHERE project_id = :id");
        $stmt->bindParam(':id', $data["id"]);
        $stmt->execute();

        // Delete project tasks
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE project_id = :id");
        $stmt->bindParam(':id', $data["id"]);
        $stmt->execute();

        // Delete project
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = :id");
        $stmt->bindParam(':id', $data["id"]);

        if($stmt->execute()) {
            $pdo->commit();
            header("HTTP/1.1 200 OK");
            header("Content-Type: application/json; charset=utf-8");
            return json_encode(["message" => "Project deleted successfully"]);
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            return json_encode(["error" => "Failed to delete project"]);
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        header("HTTP/1.1 500 Internal Server Error");
        return json_encode(["error" => "Failed to delete project"]);
    }    
}

function updateProject($data, $user, $pdo) {
    if(!isset($data["id"]) || !isset($data["name"]) || !isset($data["color"]) || !isset($data["description"]) || !isset($data["is_public"]) || !isset($data["anyone_can_edit"])) {
        header("HTTP/1.1 400 Bad Request");
        return json_encode(["error" => "Missing required fields"]);
    }

    $data["color"] = substr($data["color"], 1); // Remove the '#' from the color

    $stmt = $pdo->prepare("UPDATE projects SET name = :name, description = :description, color = :color, is_public = :is_public, anyone_can_edit = :anyone_can_edit WHERE id = :id AND owner_id = :owner_id");
    $stmt->bindParam(':name', $data["name"]);
    $stmt->bindParam(':description', $data["description"]);
    $stmt->bindParam(':color', $data["color"]);
    $stmt->bindParam(':id', $data["id"]);
    $stmt->bindParam(':is_public', $data["is_public"]);
    $stmt->bindParam(':anyone_can_edit', $data["anyone_can_edit"]);
    $stmt->bindParam(':owner_id', $user->user_id);
    
    if($stmt->execute()) {
        header("HTTP/1.1 200 OK");
        header("Content-Type: application/json; charset=utf-8");
        return json_encode(["message" => "Project updated successfully", "project_id" => $data["id"]]);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        header("Content-Type: application/json; charset=utf-8");
        return json_encode(["error" => "Failed to update project"]);
    }
}