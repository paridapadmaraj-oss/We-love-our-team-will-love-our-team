<?php
require_once '../includes/session.php';
if (!isModerator()) { $_SESSION['error'] = 'Access denied.'; redirect('../index.php'); }
$page_title = 'Manage Users';
$db = getDB();
$user = currentUser();

// Ban user
if (isset($_GET['ban']) && is_numeric($_GET['ban'])) {
    $uid = (int)$_GET['ban'];
    $target = $db->query("SELECT id, role FROM users WHERE id = $uid")->fetch_assoc();
    if ($target && $target['role'] !== 'admin') {
        $ip = $db->query("SELECT last_login, id FROM users WHERE id = $uid")->fetch_assoc();
        $stmt = $db->prepare("UPDATE users SET status='banned', banned_at=NOW(), ban_reason='Violation of terms' WHERE id=?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        logAudit($user['id'], 'Banned user ID: '.$uid);
        $_SESSION['success'] = 'User banned.';
    } else $_SESSION['error'] = 'Cannot ban admin.';
    redirect('users.php');
}

// Unban user
if (isset($_GET['unban']) && is_numeric($_GET['unban'])) {
    $uid = (int)$_GET['unban'];
    $db->query("UPDATE users SET status='active', banned_at=NULL, ban_reason=NULL WHERE id=$uid");
    $_SESSION['success'] = 'User unbanned.';
    redirect('users.php');
}

$search = $_GET['search'] ?? '';
$query = "SELECT * FROM users WHERE username LIKE ? OR email LIKE ? ORDER BY created_at DESC LIMIT 50";
$s = "%$search%";
$stmt = $db->prepare($query);
$stmt->bind_param("ss", $s, $s);
$stmt->execute();
$users = $stmt->get_result();

require_once '../includes/header.php';
?>
<div class="flex-between" style="margin-bottom:30px;">
    <h1>👥 Manage Users</h1>
    <form method="GET" style="display:flex; gap:10px;">
        <input type="text" name="search" class="fi" placeholder="Search username/email..." value="<?= sanitize($search) ?>">
        <button type="submit" class="btn btn-p btn-sm">Search</button>
    </form>
</div>
<div class="card" style="padding:0; overflow-x:auto;">
    <table>
        <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Appeals</th><th>Views</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
            <?php while($u = $users->fetch_assoc()): ?>
            <tr>
                <td>#<?= $u['id'] ?></td>
                <td><strong><?= sanitize($u['username']) ?></strong></td>
                <td style="color:var(--text2);"><?= sanitize($u['email']) ?></td>
                <td><span class="badge badge-<?= $u['role'] ?>"><?= $u['role'] ?></span></td>
                <td><span class="badge badge-<?= $u['status']==='active'?'active':($u['status']==='banned'?'banned':'pending') ?>"><?= $u['status'] ?></span></td>
                <td><?= $u['appeal_count'] ?>/3</td>
                <td><?= number_format($u['total_views']) ?></td>
                <td style="color:var(--text2);font-size:.85rem;"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <?php if ($u['role'] !== 'admin'): ?>
                        <?php if ($u['status'] !== 'banned'): ?>
                            <a href="?ban=<?= $u['id'] ?>" class="btn btn-d btn-sm" onclick="return confirm('Ban this user?')">Ban</a>
                        <?php else: ?>
                            <a href="?unban=<?= $u['id'] ?>" class="btn btn-s btn-sm">Unban</a>
                        <?php endif; ?>
                        <?php if (isAdmin()): ?>
                            <a href="?ban_ip=<?= $u['id'] ?>" class="btn btn-w btn-sm">Ban IP</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:var(--text3);">🔒 Admin</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php require_once '../includes/footer.php'; ?>
