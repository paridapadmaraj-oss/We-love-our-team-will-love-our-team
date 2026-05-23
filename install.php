<?php
// BloxScript - Installation Script
// Run this file ONCE to create the database and tables

$host = 'localhost';
$user = 'root';
$pass = '';

// Create connection without database
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS bloxscript_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "✅ Database 'bloxscript_db' created successfully.<br>";
} else {
    die("❌ Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db('bloxscript_db');

// ============ CREATE TABLES ============

// Users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'moderator', 'admin') NOT NULL DEFAULT 'user',
    status ENUM('active', 'banned', 'appealed') NOT NULL DEFAULT 'active',
    banned_at DATETIME NULL,
    ban_reason TEXT NULL,
    ban_ip VARCHAR(45) NULL,
    appeal_count INT NOT NULL DEFAULT 0,
    last_appeal_date DATETIME NULL,
    signup_cooling_until DATETIME NULL,
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    verification_token VARCHAR(64) NULL,
    reset_token VARCHAR(64) NULL,
    reset_expiry DATETIME NULL,
    total_views INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'users' created.<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// IP Bans table
$sql = "CREATE TABLE IF NOT EXISTS ip_bans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    banned_by INT NOT NULL,
    reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'ip_bans' created.<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Appeals table
$sql = "CREATE TABLE IF NOT EXISTS appeals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    appeal_text TEXT NOT NULL,
    email VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL,
    status ENUM('pending', 'approved', 'denied') NOT NULL DEFAULT 'pending',
    admin_notes TEXT NULL,
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'appeals' created.<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Donations table
$sql = "CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    username VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_method VARCHAR(50) DEFAULT 'UPI',
    transaction_id VARCHAR(100) NULL,
    screenshot_path VARCHAR(255) NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    ai_verified TINYINT(1) NOT NULL DEFAULT 0,
    verified_by INT NULL,
    verified_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'donations' created.<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Leaderboard table
$sql = "CREATE TABLE IF NOT EXISTS leaderboard (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    type ENUM('donation', 'views') NOT NULL DEFAULT 'donation',
    value DECIMAL(15,2) NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_type (username, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'leaderboard' created.<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Audit logs table
$sql = "CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'audit_logs' created.<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Settings table
$sql = "CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'settings' created.<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// ============ INSERT DEFAULT ADMIN ============
// Password: 2011Q (hashed with bcrypt)
$admin_password = password_hash('2011Q', PASSWORD_BCRYPT);
$check_admin = $conn->query("SELECT id FROM users WHERE username = 'admin122'");
if ($check_admin->num_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, status, email_verified) VALUES (?, ?, ?, 'admin', 'active', 1)");
    $email = 'admin@bloxscript.com';
    $uname = 'admin122';
    $stmt->bind_param("sss", $uname, $email, $admin_password);
    if ($stmt->execute()) {
        echo "✅ Default admin created: username='admin122', password='2011Q'<br>";
    } else {
        echo "❌ Error creating admin: " . $stmt->error . "<br>";
    }
    $stmt->close();
} else {
    echo "ℹ️ Admin user already exists, skipping.<br>";
}

// ============ DEFAULT SETTINGS ============
$default_settings = [
    'site_name' => 'BloxScript',
    'donation_upi' => 'Padmaraj@fam',
    'leaderboard_refresh' => '8',
    'appeal_cooling_days' => '3',
    'max_appeals' => '3',
    'signup_cooling_hours' => '24',
    'discord_invite' => 'https://discord.gg/swKTPSV4n'
];

foreach ($default_settings as $key => $value) {
    $check = $conn->query("SELECT id FROM settings WHERE setting_key = '$key'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value')");
    }
}
echo "✅ Default settings configured.<br>";

// Create uploads directory
if (!file_exists('uploads/screenshots')) {
    mkdir('uploads/screenshots', 0777, true);
    echo "✅ Uploads directory created.<br>";
}

$conn->close();
echo "<br><strong>✅ Installation Complete!</strong><br>";
echo "<a href='index.php'>Go to BloxScript Homepage</a> | ";
echo "<a href='admin/index.php'>Go to Admin Panel</a>";
echo "<br><br><small>⚠️ Delete this file after installation for security.</small>";
?>
