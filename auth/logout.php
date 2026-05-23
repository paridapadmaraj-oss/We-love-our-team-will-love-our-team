<?php
session_start();
require_once '../includes/functions.php';
if (isLoggedIn()) logAudit($_SESSION['user_id'], 'User logged out');
session_destroy();
session_start();
$_SESSION['success'] = 'You have been logged out.';
redirect('../index.php');
?>
