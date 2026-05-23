<?php
require_once '../includes/session.php';
if (isLoggedIn()) redirect('../index.php');
$page_title = 'Login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'banned') {
                $_SESSION['error'] = 'Your account is banned. You can submit an appeal.';
                redirect('../appeal.php');
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            session_regenerate_id(true);
            $db->query("UPDATE users SET last_login = NOW() WHERE id = " . $user['id']);
            logAudit($user['id'], 'User logged in');
            $_SESSION['success'] = 'Welcome back, ' . sanitize($user['username']) . '!';
            redirect('../index.php');
        } else {
            $_SESSION['error'] = 'Invalid username/email or password.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="flex-center" style="min-height:60vh;">
    <div class="card" style="width:100%; max-width:420px;">
        <h2 style="text-align:center; margin-bottom:8px;">Welcome Back</h2>
        <p style="text-align:center; color:var(--text2); margin-bottom:30px;">Sign in to your BloxScript account</p>
        
        <form method="POST">
            <div class="fg">
                <label>Username or Email</label>
                <input type="text" name="username" class="fi" required placeholder="Enter your username or email">
            </div>
            <div class="fg">
                <label>Password</label>
                <input type="password" name="password" class="fi" required placeholder="Enter your password">
            </div>
            <div style="text-align:right; margin-bottom:20px;">
                <a href="forgot_password.php" style="font-size:.85rem; color:var(--accent2);">Forgot Password?</a>
            </div>
            <button type="submit" class="btn btn-p" style="width:100%; justify-content:center;">Sign In</button>
        </form>
        
        <p style="text-align:center; margin-top:20px; color:var(--text2);">
            Don't have an account? <a href="register.php">Sign Up</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
