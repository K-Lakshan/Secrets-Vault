<?php
// File: index.php
require_once 'config.php';

// Redirect to dashboard if logged in, otherwise to login page
if (is_authenticated()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
?>
