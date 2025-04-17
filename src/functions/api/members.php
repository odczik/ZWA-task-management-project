<?php

function inviteMember($data, $user, $pdo) {
    $projectId = $data['project_id'];
    $email = $data['email'];

    // Check if the user is a project owner or admin
    $stmt = $pdo->prepare("SELECT role FROM project_members WHERE project_id = :project_id AND user_id = :user_id");
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
    $stmt->execute();
    $role = $stmt->fetchColumn();

    if ($role !== 'owner') {
        header("HTTP/1.1 403 Forbidden");
        header("Content-Type: application/json");
        return json_encode(["error" => "You do not have permission to invite members to this project."]);
    }

    // Check if the email is already a member of the project
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $existingMember = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT * FROM project_members WHERE project_id = :project_id AND user_id = :user_id");
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $existingMember['id'], PDO::PARAM_INT);
    $stmt->execute();
    $existingMember = $stmt->fetch(PDO::FETCH_ASSOC);
    if($existingMember) {
        header("HTTP/1.1 400 Bad Request");
        header("Content-Type: application/json");
        return json_encode(["error" => "User is already a member of this project."]);
    }

    // Fetch user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $userToInvite = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userToInvite) {
        header("HTTP/1.1 404 Not Found");
        header("Content-Type: application/json");
        return json_encode(["error" => "User with this email does not exist."]);
    }

    // Invite the user to the project
    $stmt = $pdo->prepare("INSERT INTO invitations (project_id, user_id, invited_by) VALUES (:project_id, :user_id, :invited_by)");
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userToInvite['id'], PDO::PARAM_INT);
    $stmt->bindParam(':invited_by', $user->user_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        header("HTTP/1.1 200 OK");
        header("Content-Type: application/json");
        return json_encode(["success" => "User invited successfully."]);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        header("Content-Type: application/json");
        return json_encode(["error" => "Failed to invite user."]);
    }
}

function revokeInvite($data, $user, $pdo) {
    $invitationId = $data['id'];

    // Check if the user is a project owner
    $stmt = $pdo->prepare("SELECT project_id FROM invitations WHERE id = :invitation_id");
    $stmt->bindParam(':invitation_id', $invitationId, PDO::PARAM_INT);
    $stmt->execute();
    $projectId = $stmt->fetchColumn();

    error_log("Invitation ID: " . $invitationId); // Debugging line
    error_log("Project ID: " . $projectId); // Debugging line

    if (!$projectId) {
        header("HTTP/1.1 404 Not Found");
        header("Content-Type: application/json");
        return json_encode(["error" => "Invitation not found."]);
    }

    $stmt = $pdo->prepare("SELECT role FROM project_members WHERE project_id = :project_id AND user_id = :user_id");
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
    $stmt->execute();
    $role = $stmt->fetchColumn();
    if ($role !== 'owner') {
        header("HTTP/1.1 403 Forbidden");
        header("Content-Type: application/json");
        return json_encode(["error" => "You do not have permission to revoke this invitation."]);
    }

    // Revoke the invitation
    $stmt = $pdo->prepare("DELETE FROM invitations WHERE id = :invitation_id");
    $stmt->bindParam(':invitation_id', $invitationId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        header("HTTP/1.1 200 OK");
        header("Content-Type: application/json");
        return json_encode(["success" => "Invitation revoked successfully."]);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        header("Content-Type: application/json");
        return json_encode(["error" => "Failed to revoke invitation."]);
    }
}