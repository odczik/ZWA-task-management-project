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
        return header("Location: /dashboard");
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        return json_encode(["error" => "Failed to delete project"]);
    }
}