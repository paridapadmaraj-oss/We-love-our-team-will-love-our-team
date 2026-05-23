<?php
require_once '../includes/session.php';
if (isLoggedIn()) redirect('../index.php');
$page_title = 'Forgot Password';

$step = 1;
$email = $_SESSION['reset_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    if (isset($_POST['send_otp'])) {
        $email = sanitize($_POST['email'] ?? '');
        $check = $db->prepare("SELECT id, username FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $user = $check->get_result()->fetch_assoc();
        if ($user) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $token = generateToken();
            $expiry = date('Y-m-d H:i:s', time() + 900);
            $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?");
            $stmt->bind_param("sss", $token, $expiry, $email);
            $stmt->execute();
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_token'] = $token;
            $sent = sendEmailOTP($email, $otp);
            if ($sent) {
                $_SESSION['success'] = 'OTP sent to your email.';
            } else {
                $_SESSION['info'] = "DEMO: Your OTP is <strong>$otp</strong>";
            }
            $step = 2;
        } else {
            $_SESSION['error'] = 'No account found with that email.';
        }
    } elseif (isset($_POST['verify_otp'])) {
        if (($_POST['otp'] ?? '') === ($_SESSION['reset_otp'] ?? '')) {
            $step = 3;
            $_SESSION['success'] = 'OTP verified! Set new password.';
        } else {
            $_SESSION['error'] = 'Invalid OTP.';
            $step = 2;
        }
    } elseif (isset($_POST['reset_password'])) {
        $pw = $_POST['password'] ?? '';
        $cf = $_POST['confirm'] ?? '';
        if (strlen($pw) < 6) $_SESSION['error'] = 'Password must be 6+ chars.';
        elseif ($pw !== $cf) $_SESSION['error'] = 'Passwords do not match.';
        else {
            $hash = password_hash($pw, PASSWORD_BCRYPT);
            $em = $_SESSION['reset_email'];
            $st = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE email = ?");
            $st->bind_param("ss", $hash, $em);
            $st->execute();
            unset($_SESSION['reset_otp'], $_SESSION['reset_email'], $_SESSION['reset_token']);
            $_SESSION['success'] = 'Password reset successfully! Login now.';
            redirect('login.php');
        }
        $step = 3;
    }
}
require_once '../includes/header.php';
?>
<div class="flex-center" style="min-height:60vh;">
    <div class="card" style="width:100%; max-width:420px;">
        <h2 style="text-align:center; margin-bottom:8px;">Reset Password</h2>
        <p style="text-align:center; color:var(--text2); margin-bottom:30px;">
            <?= $step===1 ? 'Enter your registered email' : ($step===2 ? 'Enter the OTP sent to your email' : 'Create a new password') ?>
        </p>
        <form method="POST">
            <?php if ($step === 1): ?>
                <div class="fg"><label>Email</label><input type="email" name="email" class="fi" required placeholder="your@email.com"></div>
                <button type="submit" name="send_otp" class="btn btn-p" style="width:100%; justify-content:center;">Send OTP</button>
            <?php elseif ($step === 2): ?>
                <div class="fg"><label>OTP Code</label><input type="text" name="otp" class="fi otp-input" required maxlength="6" placeholder="000000" inputmode="numeric"></div>
                <button type="submit" name="verify_otp" class="btn btn-p" style="width:100%; justify-content:center;">Verify OTP</button>
            <?php else: ?>
                <div class="fg"><label>New Password</label><input type="password" name="password" class="fi" required minlength="6"></div>
                <div class="fg"><label>Confirm Password</label><input type="password" name="confirm" class="fi" required></div>
                <button type="submit" name="reset_password" class="btn btn-p" style="width:100%; justify-content:center;">Reset Password</button>
            <?php endif; ?>
        </form>
        <p style="text-align:center; margin-top:20px;"><a href="login.php">Back to Login</a></p>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
