<?php
// File: view_secret.php
require_once 'config.php';
require_authentication();

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

// Decrypt the content
$decrypted_content = decrypt_data($secret['content'], $secret['iv'], $_SESSION['user_key']);

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Secret - Secrets Vault</title>
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
            <div class="flex justify-between items-start mb-6">
                <h2 class="text-2xl font-bold"><?= html_escape($secret['title']) ?></h2>
                <p class="text-gray-600 text-sm">Created: <?= html_escape(date('M j, Y g:i A', strtotime($secret['created_at']))) ?></p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded mb-6 whitespace-pre-wrap">
                <?= nl2br(html_escape($decrypted_content)) ?>
            </div>
            
            <div class="flex justify-between">
                <a href="dashboard.php" class="text-blue-500 hover:text-blue-700">Back to Dashboard</a>
                <div>
                    <a href="edit_secret.php?id=<?= $secret_id ?>" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded mr-2">
                        Edit
                    </a>
                    <form method="POST" action="delete_secret.php" class="inline">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="id" value="<?= $secret_id ?>">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded"
                                onclick="return confirm('Are you sure you want to delete this secret?')">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
