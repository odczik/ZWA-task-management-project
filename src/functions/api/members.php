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

function handleInvite($data, $user, $pdo) {
    $invitationId = $data['id'];
    $accept = isset($data['accept']) ? $data['accept'] : false;

    // Check if the invitation exists
    $stmt = $pdo->prepare("SELECT * FROM invitations WHERE id = :invitation_id AND user_id = :user_id");
    $stmt->bindParam(':invitation_id', $invitationId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
    $stmt->execute();
    $invite = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invite) {
        header("HTTP/1.1 404 Not Found");
        header("Content-Type: application/json");
        return json_encode(["error" => "Invitation not found."]);
    }

    $pdo->beginTransaction();

    if ($accept) {
        // Accept the invitation
        $stmt = $pdo->prepare("INSERT INTO project_members (project_id, user_id, role) VALUES (:project_id, :user_id, 'viewer')");
        $stmt->bindParam(':project_id', $invite['project_id'], PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            // Delete the invitation after accepting it
            $stmt = $pdo->prepare("DELETE FROM invitations WHERE id = :invitation_id");
            $stmt->bindParam(':invitation_id', $invitationId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $pdo->commit();
                header("HTTP/1.1 200 OK");
                header("Content-Type: application/json");
                return json_encode(["success" => "Invitation accepted successfully.", "id" => $invite['project_id']]);
            } else {
                $pdo->rollBack();
                header("HTTP/1.1 500 Internal Server Error");
                header("Content-Type: application/json");
                return json_encode(["error" => "Failed to delete invitation after accepting."]);
            }
        } else {
            $pdo->rollBack();
            header("HTTP/1.1 500 Internal Server Error");
            header("Content-Type: application/json");
            return json_encode(["error" => "Failed to accept invitation."]);
        }
    } else {
        // Decline the invitation
        $stmt = $pdo->prepare("DELETE FROM invitations WHERE id = :invitation_id");
        $stmt->bindParam(':invitation_id', $invitationId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $pdo->commit();
            header("HTTP/1.1 200 OK");
            header("Content-Type: application/json");
            return json_encode(["success" => "Invitation declined successfully."]);
        } else {
            $pdo->rollBack();
            header("HTTP/1.1 500 Internal Server Error");
            header("Content-Type: application/json");
            return json_encode(["error" => "Failed to decline invitation."]);
        }
    }
}

function updateMember($data, $user, $pdo) {
    error_log("updateMember called with data: " . json_encode($data)); // Debugging line
    $projectId = $data['project_id'];
    $memberId = $data['member_id'];
    $newRole = $data['role'];

    // Check if the user is a project owner
    $stmt = $pdo->prepare("SELECT role FROM project_members WHERE project_id = :project_id AND user_id = :user_id");
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
    $stmt->execute();
    $role = $stmt->fetchColumn();

    if ($role !== 'owner') {
        header("HTTP/1.1 403 Forbidden");
        header("Content-Type: application/json");
        return json_encode(["error" => "You do not have permission to update members in this project."]);
    }

    // Update the member's role
    $stmt = $pdo->prepare("UPDATE project_members SET role = :role WHERE project_id = :project_id AND user_id = :member_id");
    $stmt->bindParam(':role', $newRole, PDO::PARAM_STR);
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->bindParam(':member_id', $memberId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        header("HTTP/1.1 200 OK");
        header("Content-Type: application/json");
        return json_encode(["success" => "Member role updated successfully."]);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        header("Content-Type: application/json");
        return json_encode(["error" => "Failed to update member role."]);
    }
}

function removeMember($data, $user, $pdo) {
    $projectId = $data['project_id'];
    if(isset($data["member_id"])) $memberId = $data['member_id'];

    // Check if the user is a project owner
    $stmt = $pdo->prepare("SELECT role FROM project_members WHERE project_id = :project_id AND user_id = :user_id");
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
    $stmt->execute();
    $role = $stmt->fetchColumn();

    if ($role !== 'owner' && isset($memberId)) {
        header("HTTP/1.1 403 Forbidden");
        header("Content-Type: application/json");
        return json_encode(["error" => "You do not have permission to remove members from this project."]);
    }

    if(!isset($memberId)) {
        $memberId = $user->user_id; // If no member ID is provided, remove the current user
    }

    // Remove the member from the project
    $stmt = $pdo->prepare("DELETE FROM project_members WHERE project_id = :project_id AND user_id = :member_id");
    $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    $stmt->bindParam(':member_id', $memberId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        header("HTTP/1.1 200 OK");
        header("Content-Type: application/json");
        return json_encode(["success" => "Member removed successfully."]);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        header("Content-Type: application/json");
        return json_encode(["error" => "Failed to remove member."]);
    }
}