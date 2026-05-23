<?php
require_once 'includes/session.php';
$page_title = 'Appeal Ban';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_appeal'])) {
    $appeal_text = sanitize($_POST['appeal_text'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    
    if (empty($appeal_text) || empty($email) || empty($username)) {
        $_SESSION['error'] = 'All fields are required.';
    } else {
        // Find user by username
        $user = $db->prepare("SELECT id, appeal_count, last_appeal_date, status FROM users WHERE username = ?");
        $user->bind_param("s", $username);
        $user->execute();
        $u = $user->get_result()->fetch_assoc();
        
        if ($u && $u['status'] === 'banned') {
            $check = canAppeal($u['id']);
            if ($check['can']) {
                $stmt = $db->prepare("INSERT INTO appeals (user_id, appeal_text, email, username) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $u['id'], $appeal_text, $email, $username);
                if ($stmt->execute()) {
                    $db->query("UPDATE users SET appeal_count = appeal_count + 1, last_appeal_date = NOW(), status = 'appealed' WHERE id = " . $u['id']);
                    logAudit($u['id'], 'Appeal submitted');
                    $_SESSION['success'] = 'Your appeal has been submitted. Admin will review it. You can also appeal via Discord.';
                }
            } else {
                $_SESSION['error'] = $check['reason'];
            }
        } else {
            // Allow appeal even if user not found - they can explain
            $stmt = $db->prepare("INSERT INTO appeals (user_id, appeal_text, email, username) VALUES (NULL, ?, ?, ?)");
            $stmt->bind_param("sss", $appeal_text, $email, $username);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Your appeal has been submitted. We will review it shortly.';
            } else {
                $_SESSION['error'] = 'Failed to submit appeal.';
            }
        }
    }
}

require_once 'includes/header.php';
?>
<div class="flex-center" style="min-height:60vh;">
    <div class="card" style="width:100%; max-width:550px;">
        <h2 style="text-align:center; margin-bottom:8px;">🛡️ Appeal a Ban</h2>
        <p style="text-align:center; color:var(--text2); margin-bottom:25px;">If you've been banned, you can submit an appeal (max 3 appeals, 3-day cooldown each)</p>
        
        <form method="POST">
            <div class="fg">
                <label>Your Username</label>
                <input type="text" name="username" class="fi" required placeholder="Your BloxScript username">
            </div>
            <div class="fg">
                <label>Your Email</label>
                <input type="email" name="email" class="fi" required placeholder="Your registered email">
            </div>
            <div class="fg">
                <label>Appeal Explanation</label>
                <textarea name="appeal_text" class="fta" required placeholder="Explain why you should be unbanned. Be honest and detailed..."></textarea>
            </div>
            <button type="submit" name="submit_appeal" class="btn btn-p" style="width:100%; justify-content:center;">Submit Appeal</button>
        </form>
        
        <hr style="border-color:var(--border); margin:25px 0;">
        <p style="text-align:center; color:var(--text2); font-size:.9rem;">
            Prefer Discord? <a href="https://discord.gg/swKTPSV4n" target="_blank">Appeal via our Discord server</a>
        </p>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
