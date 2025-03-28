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
        } catch (ExpiredException $e) {
            return ["error" => "Token has expired: " . $e->getMessage()];
        } catch (SignatureInvalidException $e) {
            return ["error" => "Invalid signature: " . $e->getMessage()];
        } catch (BeforeValidException $e) {
            return ["error" => "Token is not yet valid: " . $e->getMessage()];
        } catch (Exception $e) {
            return ["error" => "Invalid token: " . $e->getMessage()];
        }
    }

    // Function to check if user is logged in
    public function isLoggedIn() {
        if (isset($_COOKIE['token'])) {
            $decoded = $this->verifyJWT($_COOKIE['token']);
            return is_object($decoded) && isset($decoded->user_id);
        }
        return false;
    }
}
?>