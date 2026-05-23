<?php
require_once '../includes/session.php';
if (!isModerator()) { $_SESSION['error'] = 'Access denied.'; redirect('../index.php'); }
$page_title = 'Appeals';
$db = getDB();
$user = currentUser();

if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $aid = (int)$_GET['approve'];
    $appeal = $db->query("SELECT * FROM appeals WHERE id=$aid")->fetch_assoc();
    if ($appeal) {
        $db->query("UPDATE appeals SET status='approved', reviewed_by={$user['id']}, reviewed_at=NOW() WHERE id=$aid");
        $db->query("UPDATE users SET status='active' WHERE id={$appeal['user_id']}");
        logAudit($user['id'], 'Approved appeal #'.$aid);
        $_SESSION['success'] = 'Appeal approved. User unbanned.';
    }
    redirect('appeals.php');
}
if (isset($_GET['deny']) && is_numeric($_GET['deny'])) {
    $aid = (int)$_GET['deny'];
    $db->query("UPDATE appeals SET status='denied', reviewed_by={$user['id']}, reviewed_at=NOW() WHERE id=$aid");
    $_SESSION['success'] = 'Appeal denied.';
    redirect('appeals.php');
}

$pending = $db->query("SELECT * FROM appeals WHERE status='pending' ORDER BY created_at DESC");
$history = $db->query("SELECT a.*, u.username as reviewer_name FROM appeals a LEFT JOIN users u ON a.reviewed_by=u.id WHERE a.status!='pending' ORDER BY a.reviewed_at DESC LIMIT 20");

require_once '../includes/header.php';
?>
<h1 style="margin-bottom:30px;">📋 Appeal Management</h1>

<h3 style="margin-bottom:15px;">Pending Appeals (<?= $pending->num_rows ?>)</h3>
<div class="card" style="padding:0; margin-bottom:40px;">
    <?php if ($pending->num_rows > 0): ?>
    <table>
        <thead><tr><th>Username</th><th>Email</th><th>Appeal Text</th><th>Submitted</th><th>Actions</th></tr></thead>
        <tbody>
            <?php while($a = $pending->fetch_assoc()): ?>
            <tr>
                <td><strong><?= sanitize($a['username']) ?></strong></td>
                <td style="color:var(--text2);"><?= sanitize($a['email']) ?></td>
                <td style="max-width:300px;"><?= nl2br(sanitize($a['appeal_text'])) ?></td>
                <td style="font-size:.85rem;color:var(--text2);"><?= timeAgo($a['created_at']) ?></td>
                <td>
                    <a href="?approve=<?= $a['id'] ?>" class="btn btn-s btn-sm" onclick="return confirm('Approve this appeal?')">Approve</a>
                    <a href="?deny=<?= $a['id'] ?>" class="btn btn-d btn-sm" onclick="return confirm('Deny this appeal?')">Deny</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty"><p>No pending appeals</p></div>
    <?php endif; ?>
</div>

<h3 style="margin-bottom:15px;">Review History</h3>
<div class="card" style="padding:0;">
    <?php if ($history->num_rows > 0): ?>
    <table>
        <thead><tr><th>User</th><th>Status</th><th>Reviewed By</th><th>Date</th></tr></thead>
        <tbody>
            <?php while($a = $history->fetch_assoc()): ?>
            <tr>
                <td><strong><?= sanitize($a['username']) ?></strong></td>
                <td><span class="badge badge-<?= $a['status'] ?>"><?= $a['status'] ?></span></td>
                <td><?= sanitize($a['reviewer_name']??'System') ?></td>
                <td style="font-size:.85rem;color:var(--text2);"><?= timeAgo($a['reviewed_at']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty"><p>No review history</p></div>
    <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>
