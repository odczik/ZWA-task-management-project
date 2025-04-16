<?php

function updateProfile($data, $user, $pdo) {
    // Check if the required fields are present
    if (!isset($data['username']) || !isset($data['email']) || !isset($data['password']) || !isset($data['password-old'])) {
        return json_encode(["error" => "Missing required fields"]);
    }

    try {
        // Start a transaction
        $pdo->beginTransaction();

        if($data["password-old"] !== "" && $data["password"] !== "") {
            // Check if the old password is correct
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user->user_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($result && password_verify($data["password-old"], $result['password'])) {
                // Update the password
                $hashedPassword = password_hash($data["password"], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':id', $user->user_id);
                if (!$stmt->execute()) {
                    header("HTTP/1.1 500 Internal Server Error");
                    header("Content-Type: application/json");
                    return json_encode(["error" => "Failed to update password"]);
                }
            } else {
                header("HTTP/1.1 400 Bad Request");
                header("Content-Type: application/json");
                return json_encode(["error" => "Old password is incorrect"]);
            }
        }
    
        // Prepare the SQL statement to update the user profile
        $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
        // Bind the parameters
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':id', $user->user_id);
    
        // Update the profile picture if provided
        if (isset($_FILES['profile']) && $_FILES['profile']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile']['tmp_name'];
            $fileName = $_FILES['profile']['name'];
            $fileSize = $_FILES['profile']['size'];
            $fileType = $_FILES['profile']['type'];
    
            // Validate the file type is jpg, png, or gif
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($fileType, $allowedTypes)) {
                header("HTTP/1.1 400 Bad Request");
                header("Content-Type: application/json");
                return json_encode(["error" => "Invalid file type. Only JPG, PNG, and GIF are allowed."]);
            }
            // Check file size (limit to 2MB)
            if ($fileSize > 2 * 1024 * 1024) {
                header("HTTP/1.1 400 Bad Request");
                header("Content-Type: application/json");
                return json_encode(["error" => "File size exceeds the limit of 2MB."]);
            }
    
            // Move the uploaded file to the desired location
            $destinationPath = __DIR__ . "/../../../public/profile-pictures/" . $user->user_id . ".jpg";
            
            if (!move_uploaded_file($fileTmpPath, $destinationPath)) {
                header("HTTP/1.1 500 Internal Server Error");
                header("Content-Type: application/json");
                return json_encode(["error" => "Failed to move uploaded file. Check directory permissions."]);
            }
        }
    
        // Execute the statement and check for errors
        if ($stmt->execute()) {
            return header("Location: /account");
        } else {
            return json_encode(["error" => "Failed to update profile"]);
        }
    } catch (PDOException $e) {
        $pdo->rollBack(); // Rollback the transaction in case of error

        header("HTTP/1.1 500 Internal Server Error");
        header("Content-Type: application/json");
        return json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}