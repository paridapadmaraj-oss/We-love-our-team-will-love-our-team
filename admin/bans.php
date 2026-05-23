<?php
require_once '../includes/session.php';
if (!isModerator()) { $_SESSION['error'] = 'Access denied.'; redirect('../index.php'); }
$page_title = 'Ban Management';
$db = getDB();

// Ban IP
if (isset($_GET['ban_ip']) && is_numeric($_GET['ban_ip'])) {
    $uid = (int)$_GET['ban_ip'];
    $target = $db->query("SELECT id, ip FROM users WHERE id=$uid")->fetch_assoc();
    // Get user's IP from logs
    $log = $db->query("SELECT ip_address FROM audit_logs WHERE user_id=$uid ORDER BY id DESC LIMIT 1")->fetch_assoc();
    $ip = $log['ip_address'] ?? $_SERVER['REMOTE_ADDR'];
    $check = $db->query("SELECT id FROM ip_bans WHERE ip_address='$ip'");
    if ($check->num_rows === 0) {
        $db->query("INSERT INTO ip_bans (ip_address, banned_by, reason) VALUES ('$ip', {$user['id']}, 'IP banned')");
        $_SESSION['success'] = "IP $ip banned.";
    }
    redirect('bans.php');
}

// Remove IP ban
if (isset($_GET['remove_ip']) && is_numeric($_GET['remove_ip'])) {
    $db->query("DELETE FROM ip_bans WHERE id=" . (int)$_GET['remove_ip']);
    $_SESSION['success'] = 'IP unblocked.';
    redirect('bans.php');
}

$ip_bans = $db->query("SELECT ib.*, u.username as banned_by_name FROM ip_bans ib LEFT JOIN users u ON ib.banned_by=u.id ORDER BY ib.created_at DESC");
$banned_users = $db->query("SELECT id, username, email, banned_at, ban_reason, appeal_count FROM users WHERE status='banned' ORDER BY banned_at DESC");

require_once '../includes/header.php';
?>
<h1 style="margin-bottom:30px;">🔨 Ban Management</h1>
<div class="grid-2">
    <div class="card" style="padding:0;">
        <h3 style="padding:20px 20px 0;">Banned Users</h3>
        <table>
            <thead><tr><th>User</th><th>Ban Date</th><th>Appeals</th><th>Action</th></tr></thead>
            <tbody>
                <?php while($b = $banned_users->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= sanitize($b['username']) ?></strong></td>
                    <td style="color:var(--text2);font-size:.85rem;"><?= date('M d, Y', strtotime($b['banned_at'])) ?></td>
                    <td><?= $b['appeal_count'] ?>/3</td>
                    <td><a href="users.php?unban=<?= $b['id'] ?>" class="btn btn-s btn-sm">Unban</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div class="card" style="padding:0;">
        <h3 style="padding:20px 20px 0;">IP Bans</h3>
        <table>
            <thead><tr><th>IP Address</th><th>By</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
                <?php while($ip = $ip_bans->fetch_assoc()): ?>
                <tr>
                    <td><code><?= $ip['ip_address'] ?></code></td>
                    <td><?= sanitize($ip['banned_by_name']??'System') ?></td>
                    <td style="color:var(--text2);font-size:.85rem;"><?= date('M d, Y', strtotime($ip['created_at'])) ?></td>
                    <td><a href="?remove_ip=<?= $ip['id'] ?>" class="btn btn-d btn-sm">Remove</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
