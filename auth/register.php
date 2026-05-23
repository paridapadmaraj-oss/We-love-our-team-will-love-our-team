<?php
require_once '../includes/session.php';
if (isLoggedIn()) redirect('../index.php');
$page_title = 'Sign Up';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    if (empty($username)) $errors[] = 'Username is required.';
    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (empty($email)) $errors[] = 'Email is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    
    if (empty($errors)) {
        $db = getDB();
        
        // Check signup cooling
        if (!checkSignupCooling($email, $username)) {
            $errors[] = 'Please wait before creating a new account. 24-hour cooling period applies.';
        }
        
        // Check existing user
        $check = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'Username or email already exists.';
        }
    }
    
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $cooling_until = date('Y-m-d H:i:s', time() + 86400); // 24 hour cooling
        $stmt = $db->prepare("INSERT INTO users (username, email, password, signup_cooling_until) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed, $cooling_until);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Account created successfully! You can now login.';
            logAudit(null, 'New user registered: ' . $username);
            redirect('login.php');
        } else {
            $_SESSION['error'] = 'Registration failed. Please try again.';
        }
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

require_once '../includes/header.php';
?>

<div class="flex-center" style="min-height:60vh;">
    <div class="card" style="width:100%; max-width:420px;">
        <h2 style="text-align:center; margin-bottom:8px;">Join BloxScript</h2>
        <p style="text-align:center; color:var(--text2); margin-bottom:30px;">Create your account to get started</p>
        
        <form method="POST">
            <div class="fg">
                <label>Username</label>
                <input type="text" name="username" class="fi" required minlength="3" placeholder="Choose a username">
            </div>
            <div class="fg">
                <label>Email</label>
                <input type="email" name="email" class="fi" required placeholder="your@email.com">
            </div>
            <div class="fg">
                <label>Password</label>
                <input type="password" name="password" class="fi" required minlength="6" placeholder="At least 6 characters">
            </div>
            <div class="fg">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="fi" required placeholder="Repeat your password">
            </div>
            <p style="font-size:.8rem; color:var(--text3); margin-bottom:20px;">
                By signing up, you agree to our terms and community guidelines.
            </p>
            <button type="submit" class="btn btn-p" style="width:100%; justify-content:center;">Create Account</button>
        </form>
        
        <p style="text-align:center; margin-top:20px; color:var(--text2);">
            Already have an account? <a href="login.php">Sign In</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
