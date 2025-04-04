<?php

function createProject($data, $user, $pdo) {
    if(!isset($data["name"]) || !isset($data["color"])) {
        header("HTTP/1.1 400 Bad Request");
        return json_encode(["error" => "Missing required fields"]);
    }

    $data["color"] = substr($data["color"], 1); // Remove the '#' from the color

    $stmt = $pdo->prepare("INSERT INTO projects (name, color, owner_id) VALUES (:name, :color, :owner_id)");
    $stmt->bindParam(':name', $data["name"]);
    $stmt->bindParam(':color', $data["color"]);
    $stmt->bindParam(':owner_id', $user->user_id);
    
    if($stmt->execute()) {
        header("HTTP/1.1 201 Created");
        return json_encode(["message" => "Project created successfully", "project_id" => $pdo->lastInsertId()]);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        return json_encode(["error" => "Failed to create project"]);
    }
}