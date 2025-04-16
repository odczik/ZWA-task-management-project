<?php
require 'php-jwt/JWT.php';
require 'php-jwt/Key.php';
require 'php-jwt/JWTExceptionWithPayloadInterface.php';
require 'php-jwt/SignatureInvalidException.php';
require 'php-jwt/ExpiredException.php';
require 'php-jwt/BeforeValidException.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;

class JWTHandler {
    private $secret_key;
    private $cachedPayload = null;
    private $cachedUser = null;

    public function __construct($secret_key) {
        $this->secret_key = $secret_key;
    }

    // Function to create JWT (encode)
    public function createJWT($payload) {
        return JWT::encode($payload, $this->secret_key, 'HS256');
    }

    // Function to verify JWT (decode and validate)
    public function verifyJWT($jwt) {
        if ($this->cachedPayload !== null) {
            return $this->cachedPayload; // Return cached payload if available
        }
        try {
            // Decode JWT
            $decoded = JWT::decode($jwt, new Key($this->secret_key, 'HS256'));
            // Check if the token is expired
            if (isset($decoded->exp) && $decoded->exp < time()) {
                return null; // Token is expired
            }
            // Check if the token is valid
            if (isset($decoded->iat) && $decoded->iat > time()) {
                return null; // Token is not yet valid
            }
            // Check if the token version is valid
            if (isset($decoded->token_version)) {
                // Check if the token version is cached
                if ($this->cachedUser !== null) {
                    // Check if the token version matches the one in the database
                    if ($this->cachedUser['token_version'] !== $decoded->token_version) {
                        return null; // Token version mismatch
                    }
                }
                require "./src/functions/db_connect.php";
                $stmt = $pdo->prepare("SELECT token_version FROM users WHERE id = :id");
                $stmt->bindParam(':id', $decoded->user_id);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                // Cache the result to avoid multiple queries
                $this->cachedUser = $result;
                // Check if the token version matches the one in the database
                if ($result && $result['token_version'] !== $decoded->token_version) {
                    return null; // Token version mismatch
                }
            }

            // Cache the payload for future use
            $this->cachedPayload = $decoded;
            // Return the decoded payload
            return $decoded;
        } catch (exception $e) {    // Catch all exceptions to handle invalid tokens
            return null;
        }
    }

    // Function to check if user is logged in
    public function isLoggedIn() {
        return $this->getUser() !== null;
    }

    // Function to get user from JWT
    public function getUser() {
        if (isset($_COOKIE['token'])) {
            $decoded = $this->verifyJWT($_COOKIE['token']);
            if (is_object($decoded) && isset($decoded->user_id)) {
                return $decoded;
            }
        }
        return null;
    }
}
?>