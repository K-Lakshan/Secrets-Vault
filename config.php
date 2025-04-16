<?php
// File: config.php - Database configuration and global settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'secrets_vault');
define('DB_USER', 'root'); // Change in production
define('DB_PASS', ''); // Change in production
define('SITE_KEY', 'your_secrets_key'); // Change this to a secure random string
define('CIPHER_METHOD', 'aes-256-cbc');

// Connection function
function get_db_connection() {
    try {
        $conn = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $conn;
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

// Security functions
function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    
    // Store in database if user is logged in
    if (isset($_SESSION['user_id'])) {
        $db = get_db_connection();
        $stmt = $db->prepare("INSERT INTO csrf_tokens (user_id, token) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $token]);
    }
    
    return $token;
}

function verify_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $token) {
        return false;
    }
    
    return true;
}

function encrypt_data($data, $encryption_key) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(CIPHER_METHOD));
    $encrypted = openssl_encrypt($data, CIPHER_METHOD, $encryption_key, 0, $iv);
    return [
        'data' => $encrypted,
        'iv' => bin2hex($iv)
    ];
}

function decrypt_data($encrypted_data, $iv, $encryption_key) {
    return openssl_decrypt(
        $encrypted_data,
        CIPHER_METHOD, 
        $encryption_key, 
        0, 
        hex2bin($iv)
    );
}

// User encryption key management
function generate_user_encryption_key() {
    return bin2hex(random_bytes(32));
}

function encrypt_user_key($user_key) {
    // Encrypt user's encryption key with site key before storing
    $encrypted = encrypt_data($user_key, SITE_KEY);
    return $encrypted['data'];
}

function decrypt_user_key($encrypted_key) {
    // Simple encryption for demo - in production use a more secure approach
    return openssl_decrypt($encrypted_key, CIPHER_METHOD, SITE_KEY, 0, str_repeat('0', 16));
}

// Authentication helpers
function is_authenticated() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['user_id']);
}

function require_authentication() {
    if (!is_authenticated()) {
        header('Location: login.php');
        exit;
    }
}

// Function to sanitize output to prevent XSS
function html_escape($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>
