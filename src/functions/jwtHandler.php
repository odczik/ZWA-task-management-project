<?php
require 'php-jwt/JWT.php';
require 'php-jwt/Key.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler {
    private $secret_key;

    public function __construct($secret_key) {
        $this->secret_key = $secret_key;
    }

    // Function to create JWT (encode)
    public function createJWT($payload) {
        return JWT::encode($payload, $this->secret_key, 'HS256');
    }

    // Function to verify JWT (decode and validate)
    public function verifyJWT($jwt) {
        try {
            // Decode JWT
            $decoded = JWT::decode($jwt, new Key($this->secret_key, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            return ["error" => "Invalid token: " . $e->getMessage()];
        }
    }
}
?>
