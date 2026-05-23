<?php
require_once '../includes/session.php';
if (!isModerator()) { $_SESSION['error'] = 'Access denied.'; redirect('../index.php'); }
$page_title = 'Donation Management';
$db = getDB();
$user = currentUser();

if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $did = (int)$_GET['approve'];
    $donation = $db->query("SELECT * FROM donations WHERE id=$did")->fetch_assoc();
    if ($donation) {
        $db->query("UPDATE donations SET status='approved', verified_by={$user['id']}, verified_at=NOW() WHERE id=$did");
        
        // Update user's total_views (as engagement)
        if ($donation['user_id']) {
            $db->query("UPDATE users SET total_views = total_views + 1 WHERE id={$donation['user_id']}");
        }
        
        refreshLeaderboard();
        logAudit($user['id'], 'Approved donation #'.$did.' amount: '.$donation['amount']);
        $_SESSION['success'] = 'Donation approved and leaderboard updated.';
    }
    redirect('donations.php');
}
if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
    $did = (int)$_GET['reject'];
    $db->query("UPDATE donations SET status='rejected', verified_by={$user['id']}, verified_at=NOW() WHERE id=$did");
    $_SESSION['success'] = 'Donation rejected.';
    redirect('donations.php');
}
if (isset($_GET['set_amount']) && is_numeric($_GET['set_amount']) && isset($_GET['amt'])) {
    $did = (int)$_GET['set_amount'];
    $amt = floatval($_GET['amt']);
    $db->query("UPDATE donations SET amount=$amt, ai_verified=1 WHERE id=$did");
    $_SESSION['success'] = 'Amount updated.';
    redirect('donations.php');
}

$pending = $db->query("SELECT * FROM donations WHERE status='pending' ORDER BY created_at DESC");
$approved = $db->query("SELECT * FROM donations WHERE status='approved' ORDER BY created_at DESC LIMIT 20");

require_once '../includes/header.php';
?>
<div class="flex-between" style="margin-bottom:30px;">
    <h1>💰 Donation Management</h1>
    <a href="?refresh_lb=1" class="btn btn-p btn-sm">Refresh Leaderboard</a>
</div>

<h3 style="margin-bottom:15px;">Pending Verifications (<?= $pending->num_rows ?>)</h3>
<div class="card" style="padding:0; margin-bottom:40px;">
    <?php if ($pending->num_rows > 0): ?>
    <table>
        <thead><tr><th>User</th><th>Screenshot</th><th>AI Amount</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
            <?php while($d = $pending->fetch_assoc()): ?>
            <tr>
                <td><strong><?= sanitize($d['username']) ?></strong></td>
                <td><a href="../<?= $d['screenshot_path'] ?>" target="_blank" class="btn btn-o btn-sm">View 📸</a></td>
                <td><?= $d['amount'] > 0 ? formatAmount($d['amount']).($d['ai_verified']?' 🤖':'') : '<em>AI pending</em>' ?></td>
                <td style="font-size:.85rem;color:var(--text2);"><?= timeAgo($d['created_at']) ?></td>
                <td style="display:flex; gap:5px; flex-wrap:wrap;">
                    <a href="?approve=<?= $d['id'] ?>" class="btn btn-s btn-sm" onclick="return confirm('Approve?')">Approve</a>
                    <a href="?reject=<?= $d['id'] ?>" class="btn btn-d btn-sm" onclick="return confirm('Reject?')">Reject</a>
                    <form method="GET" style="display:flex; gap:5px;">
                        <input type="hidden" name="set_amount" value="<?= $d['id'] ?>">
                        <input type="text" name="amt" class="fi" style="width:80px;padding:6px 10px;" placeholder="Amount">
                        <button type="submit" class="btn btn-w btn-sm">Set</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty"><p>No pending donations</p></div>
    <?php endif; ?>
</div>

<h3 style="margin-bottom:15px;">Approved Donations</h3>
<div class="card" style="padding:0;">
    <?php if ($approved->num_rows > 0): ?>
    <table>
        <thead><tr><th>User</th><th>Amount</th><th>Verified</th><th>Date</th></tr></thead>
        <tbody>
            <?php while($d = $approved->fetch_assoc()): ?>
            <tr>
                <td><strong><?= sanitize($d['username']) ?></strong></td>
                <td style="color:var(--accent3);font-weight:700;"><?= formatAmount($d['amount']) ?></td>
                <td><?= $d['ai_verified'] ? '🤖 AI' : '👤 Manual' ?></td>
                <td style="font-size:.85rem;color:var(--text2);"><?= timeAgo($d['created_at']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty"><p>No approved donations yet</p></div>
    <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>
