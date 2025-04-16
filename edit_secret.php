<?php
// File: edit_secret.php
require_once 'config.php';
require_authentication();

$errors = [];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Invalid secret ID";
    header("Location: dashboard.php");
    exit;
}

$secret_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$db = get_db_connection();

// Get the secret
$stmt = $db->prepare("SELECT id, title, content, iv, created_at FROM secrets WHERE id = ? AND user_id = ?");
$stmt->execute([$secret_id, $user_id]);
$secret = $stmt->fetch();

// Check if secret exists and belongs to user
if (!$secret) {
    $_SESSION['message'] = "Secret not found";
    header("Location: dashboard.php");
    exit;
}

// Decrypt the content for displaying in the form
$decrypted_content = decrypt_data($secret['content'], $secret['iv'], $_SESSION['user_key']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_secret'])) {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Invalid request";
    } else {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        
        // Validation
        if (empty($title)) {
            $errors[] = "Title is required";
        }
        
        if (empty($content)) {
            $errors[] = "Content is required";
        }
        
        // Update the secret
        if (empty($errors)) {
            // Encrypt the updated content
            $encrypted = encrypt_data($content, $_SESSION['user_key']);
            
            $stmt = $db->prepare("UPDATE secrets SET title = ?, content = ?, iv = ? WHERE id = ? AND user_id = ?");
            
            if ($stmt->execute([$title, $encrypted['data'], $encrypted['iv'], $secret_id, $user_id])) {
                $_SESSION['message'] = "Secret updated successfully";
                header("Location: dashboard.php");
                exit;
            } else {
                $errors[] = "Failed to update secret";
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Secret - Secrets Vault</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Secrets Vault</h1>
            <div class="flex items-center">
                <span class="mr-4">Welcome, <?= html_escape($_SESSION['username']) ?></span>
                <a href="logout.php" class="bg-blue-700 hover:bg-blue-800 px-3 py-1 rounded">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto p-4">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6">Edit Secret</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc pl-5">
                        <?php foreach ($errors as $error): ?>
                            <li><?= html_escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="mb-4">
                    <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title</label>
                    <input type="text" id="title" name="title" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           value="<?= html_escape($secret['title']) ?>">
                </div>
                
                <div class="mb-6">
                    <label for="content" class="block text-gray-700 text-sm font-bold mb-2">Secret Content</label>
                    <textarea id="content" name="content" rows="6"
                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                              ><?= html_escape($decrypted_content) ?></textarea>
                </div>
                
                <div class="flex items-center justify-between">
                    <a href="dashboard.php" class="text-blue-500 hover:text-blue-700">Back to Dashboard</a>
                    <button type="submit" name="update_secret" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Update Secret
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
