<?php
function updateProfile($data, $user, $pdo) {
    // Check if the required fields are present
    if (!isset($data['username']) || !isset($data['email'])) {
        header("HTTP/1.1 400 Bad Request");
        header("Content-Type: application/json");
        return json_encode(["error" => "Missing required fields"]);
    }

    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Update the password if provided
        if (!empty($data["password-old"]) && !empty($data["password"])) {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user->user_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && password_verify($data["password-old"], $result['password'])) {
                $hashedPassword = password_hash($data["password"], PASSWORD_BCRYPT);
                $passwordStmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $passwordStmt->bindParam(':password', $hashedPassword);
                $passwordStmt->bindParam(':id', $user->user_id);
                if (!$passwordStmt->execute()) {
                    $pdo->rollBack();
                    header("HTTP/1.1 500 Internal Server Error");
                    header("Content-Type: application/json");
                    return json_encode(["error" => "Failed to update password"]);
                }
            } else {
                $pdo->rollBack();
                header("HTTP/1.1 400 Bad Request");
                header("Content-Type: application/json");
                return json_encode(["error" => "Old password is incorrect"]);
            }
        }

        // Update the username and email
        $profileStmt = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
        $profileStmt->bindParam(':username', $data['username']);
        $profileStmt->bindParam(':email', $data['email']);
        $profileStmt->bindParam(':id', $user->user_id);

        if (!$profileStmt->execute()) {
            $pdo->rollBack();
            header("HTTP/1.1 500 Internal Server Error");
            header("Content-Type: application/json");
            return json_encode(["error" => "Failed to update profile"]);
        }

        // Update the profile picture if provided
        if (isset($_FILES['profile']) && $_FILES['profile']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile']['tmp_name'];
            $fileName = $_FILES['profile']['name'];
            $fileSize = $_FILES['profile']['size'];
            $fileType = $_FILES['profile']['type'];

            // Validate the file type is jpg, png, or gif
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($fileType, $allowedTypes)) {
                $pdo->rollBack();
                header("HTTP/1.1 400 Bad Request");
                header("Content-Type: application/json");
                return json_encode(["error" => "Invalid file type. Only JPG, PNG, and GIF are allowed."]);
            }

            // Check file size (limit to 2MB)
            if ($fileSize > 2 * 1024 * 1024) {
                $pdo->rollBack();
                header("HTTP/1.1 400 Bad Request");
                header("Content-Type: application/json");
                return json_encode(["error" => "File size exceeds the limit of 2MB."]);
            }

            // Move the uploaded file to the desired location
            $destinationPath = __DIR__ . "/../../../public/profile-pictures/" . $user->user_id . ".jpg";
            if (!move_uploaded_file($fileTmpPath, $destinationPath)) {
                $pdo->rollBack();
                header("HTTP/1.1 500 Internal Server Error");
                header("Content-Type: application/json");
                return json_encode(["error" => "Failed to move uploaded file. Check directory permissions."]);
            }
        }

        // Commit the transaction
        $pdo->commit();
        return header("Location: /account");
    } catch (PDOException $e) {
        $pdo->rollBack();
        header("HTTP/1.1 500 Internal Server Error");
        header("Content-Type: application/json");
        return json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}