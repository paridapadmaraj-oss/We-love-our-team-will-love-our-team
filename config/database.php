<?php
// BloxScript - Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bloxscript_db');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Database connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
    }
    return $conn;
}

function getSetting($key) {
    $db = getDB();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return null;
}

function updateSetting($key, $value) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param("sss", $key, $value, $value);
    return $stmt->execute();
}

function logAudit($user_id, $action, $details = null) {
    $db = getDB();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $details, $ip);
    $stmt->execute();
}
?>
