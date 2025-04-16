<?php
// File: dashboard.php
require_once 'config.php';
require_authentication();

$db = get_db_connection();
$user_id = $_SESSION['user_id'];
$user_key = $_SESSION['user_key'];

// Get all secrets for this user
$stmt = $db->prepare("SELECT id, title, content, iv, created_at FROM secrets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$secrets = $stmt->fetchAll();

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Secrets Vault</title>
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
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Your Secrets</h2>
            <a href="add_secret.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                Add New Secret
            </a>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= html_escape($_SESSION['message']) ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($secrets)): ?>
            <div class="bg-gray-100 p-6 rounded-lg text-center">
                <p class="text-gray-600">You haven't added any secrets yet.</p>
                <a href="add_secret.php" class="text-blue-500 hover:underline mt-2 inline-block">Add your first secret</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($secrets as $secret): ?>
                    <div class="bg-white p-4 rounded-lg shadow hover:shadow-md transition-shadow">
                        <h3 class="text-lg font-semibold mb-2"><?= html_escape($secret['title']) ?></h3>
                        <p class="text-gray-600 text-sm mb-4">Created: <?= html_escape(date('M j, Y g:i A', strtotime($secret['created_at']))) ?></p>
                        
                        <div class="flex justify-between mt-4">
                            <a href="view_secret.php?id=<?= $secret['id'] ?>" class="text-blue-500 hover:text-blue-700">View</a>
                            <a href="edit_secret.php?id=<?= $secret['id'] ?>" class="text-green-500 hover:text-green-700">Edit</a>
                            <form method="POST" action="delete_secret.php" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="id" value="<?= $secret['id'] ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 bg-transparent border-none cursor-pointer"
                                        onclick="return confirm('Are you sure you want to delete this secret?')">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
