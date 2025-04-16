<?php
// File: add_secret.php
require_once 'config.php';
require_authentication();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_secret'])) {
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
        
        // Save the secret
        if (empty($errors)) {
            $db = get_db_connection();
            $user_id = $_SESSION['user_id'];
            
            // Encrypt the content
            $encrypted = encrypt_data($content, $_SESSION['user_key']);
            
            $stmt = $db->prepare("INSERT INTO secrets (user_id, title, content, iv) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$user_id, $title, $encrypted['data'], $encrypted['iv']])) {
                $_SESSION['message'] = "Secret added successfully";
                header("Location: dashboard.php");
                exit;
            } else {
                $errors[] = "Failed to save secret";
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
    <title>Add Secret - Secrets Vault</title>
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
            <h2 class="text-2xl font-bold mb-6">Add New Secret</h2>
            
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
                           value="<?= isset($_POST['title']) ? html_escape($_POST['title']) : '' ?>">
                </div>
                
                <div class="mb-6">
                    <label for="content" class="block text-gray-700 text-sm font-bold mb-2">Secret Content</label>
                    <textarea id="content" name="content" rows="6"
                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                              ><?= isset($_POST['content']) ? html_escape($_POST['content']) : '' ?></textarea>
                </div>
                
                <div class="flex items-center justify-between">
                    <a href="dashboard.php" class="text-blue-500 hover:text-blue-700">Back to Dashboard</a>
                    <button type="submit" name="add_secret" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Save Secret
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>