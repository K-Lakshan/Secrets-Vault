<?php
// File: delete_secret.php
require_once 'config.php';
require_authentication();

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

// Validate CSRF token
if (!verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['message'] = "Invalid request";
    header("Location: dashboard.php");
    exit;
}

// Check if secret ID is provided
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $_SESSION['message'] = "Invalid secret ID";
    header("Location: dashboard.php");
    exit;
}

$secret_id = $_POST['id'];
$user_id = $_SESSION['user_id'];
$db = get_db_connection();

// Check if the secret exists and belongs to the user before deletion
$stmt = $db->prepare("SELECT id FROM secrets WHERE id = ? AND user_id = ?");
$stmt->execute([$secret_id, $user_id]);

if ($stmt->rowCount() === 0) {
    $_SESSION['message'] = "Secret not found";
    header("Location: dashboard.php");
    exit;
}

// Delete the secret
$stmt = $db->prepare("DELETE FROM secrets WHERE id = ? AND user_id = ?");
$stmt->execute([$secret_id, $user_id]);

$_SESSION['message'] = "Secret deleted successfully";
header("Location: dashboard.php");
exit;

?>