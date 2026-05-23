<?php
require_once 'includes/session.php';
$page_title = 'Donate';
$db = getDB();
$upi_id = getSetting('donation_upi') ?: 'Padmaraj@fam';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_donation'])) {
    $username = sanitize($_POST['username'] ?? '');
    if (empty($username)) $_SESSION['error'] = 'Username is required.';
    else {
        $file = $_FILES['screenshot'] ?? null;
        if ($file && $file['error'] === 0) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (in_array($ext, $allowed) && $file['size'] <= 5242880) {
                $path = 'uploads/screenshots/' . time() . '_' . generateToken(8) . '.' . $ext;
                move_uploaded_file($file['tmp_name'], $path);
                
                $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
                $stmt = $db->prepare("INSERT INTO donations (user_id, username, screenshot_path, status) VALUES (?, ?, ?, 'pending')");
                $stmt->bind_param("iss", $user_id, $username, $path);
                
                if ($stmt->execute()) {
                    // AI verification
                    $ai_amount = aiVerifyDonationAmount($path);
                    if ($ai_amount !== null) {
                        $db->query("UPDATE donations SET amount = $ai_amount, ai_verified = 1 WHERE id = " . $stmt->insert_id);
                    }
                    logAudit($user_id, 'Donation submitted by ' . $username);
                    $_SESSION['success'] = 'Donation submitted! Admin will review it shortly.';
                } else {
                    $_SESSION['error'] = 'Failed to submit donation.';
                }
            } else {
                $_SESSION['error'] = 'Invalid file. Allowed: jpg,png,gif (max 5MB).';
            }
        } else {
            $_SESSION['error'] = 'Please upload a payment screenshot.';
        }
    }
}

require_once 'includes/header.php';
?>
<div class="grid-2">
    <div class="card">
        <h2 style="margin-bottom:20px;">💖 Support BloxScript</h2>
        <p style="color:var(--text2); margin-bottom:25px;">Your donations help us maintain the platform and servers. Thank you for your support!</p>
        <div class="donate-qr">
            <div style="background:#fff; border-radius:var(--r); padding:20px; display:inline-block;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=upi://pay?pa=<?= urlencode($upi_id) ?>&pn=BloxScript&cu=INR" alt="UPI QR Code">
            </div>
            <p style="margin-top:15px; font-size:1.1rem;">UPI ID: <strong style="color:var(--accent3);"><?= sanitize($upi_id) ?></strong></p>
        </div>
    </div>
    <div class="card">
        <h2 style="margin-bottom:20px;">📤 Submit Payment</h2>
        <p style="color:var(--text2); margin-bottom:25px;">After paying via UPI, submit the transaction screenshot below.</p>
        <form method="POST" enctype="multipart/form-data">
            <div class="fg">
                <label>Your Username</label>
                <input type="text" name="username" class="fi" required placeholder="Your Roblox/BloxScript username" value="<?= isLoggedIn() ? sanitize(currentUser()['username']) : '' ?>">
            </div>
            <div class="fg">
                <label>Payment Screenshot</label>
                <input type="file" name="screenshot" class="fi" accept="image/*" required>
                <p style="font-size:.8rem; color:var(--text3); margin-top:4px;">Upload the UPI payment confirmation screenshot (max 5MB)</p>
            </div>
            <button type="submit" name="submit_donation" class="btn btn-p" style="width:100%; justify-content:center;">Submit Donation</button>
        </form>
        <hr style="border-color:var(--border); margin:25px 0;">
        <p style="text-align:center; color:var(--text2); font-size:.9rem;">
            Having issues? <a href="https://discord.gg/swKTPSV4n" target="_blank">Join our Discord</a> for support
        </p>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
