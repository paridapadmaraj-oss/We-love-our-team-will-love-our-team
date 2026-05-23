<?php
// BloxScript - Session Management
session_start();
require_once __DIR__ . '/functions.php';

// Check IP ban
if (isIPBanned() && !isAdmin()) {
    if (basename($_SERVER['PHP_SELF']) !== 'login.php' && basename($_SERVER['PHP_SELF']) !== 'appeal.php' && basename($_SERVER['PHP_SELF']) !== 'index.php') {
        // Allow only login and appeal pages
    } else {
        // Allow access to public pages
    }
}

// Check user ban
if (isLoggedIn() && isBanned()) {
    $allowed_pages = ['login.php', 'logout.php', 'appeal.php', 'index.php'];
    $current = basename($_SERVER['PHP_SELF']);
    if (!in_array($current, $allowed_pages) && $current !== 'appeal.php') {
        $_SESSION['error'] = 'Your account has been banned. You can submit an appeal.';
        redirect('../appeal.php');
    }
}

// Session regeneration for security
if (!isset($_SESSION['created'])) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
?>
