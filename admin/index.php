<?php
require_once '../includes/session.php';
$page_title = 'Admin Login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password === '2011Q') {
        // Check if admin user exists
        $db = getDB();
        $admin = $db->query("SELECT id, password FROM users WHERE username = 'admin122' AND role = 'admin'")->fetch_assoc();
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_role'] = 'admin';
            session_regenerate_id(true);
            $_SESSION['success'] = 'Welcome to Admin Panel.';
            redirect('dashboard.php');
        }
    }
    // Fallback auth
    $db = getDB();
    $stmt = $db->prepare("SELECT id, password, role FROM users WHERE (username = 'admin122' OR role = 'admin') AND role IN ('admin','moderator') LIMIT 1");
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_role'] = $admin['role'];
        session_regenerate_id(true);
        redirect('dashboard.php');
    }
    $_SESSION['error'] = 'Invalid credentials.';
}

require_once '../includes/header.php';
?>
<div class="flex-center" style="min-height:60vh;">
    <div class="card" style="width:100%; max-width:400px;">
        <h2 style="text-align:center; margin-bottom:8px;">🔐 Admin Panel</h2>
        <p style="text-align:center; color:var(--text2); margin-bottom:30px;">Enter admin password to continue</p>
        <form method="POST">
            <div class="fg"><label>Admin Password</label><input type="password" name="password" class="fi" required></div>
            <button type="submit" class="btn btn-p" style="width:100%; justify-content:center;">Access Panel</button>
        </form>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
