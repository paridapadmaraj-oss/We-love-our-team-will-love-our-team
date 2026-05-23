<?php
require_once '../includes/session.php';
if (!isModerator()) { $_SESSION['error'] = 'Access denied.'; redirect('../index.php'); }
$page_title = 'Leaderboard Management';
$db = getDB();
$user = currentUser();

// Update settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $upi = sanitize($_POST['upi_id'] ?? '');
    updateSetting('donation_upi', $upi);
    $_SESSION['success'] = 'Settings updated.';
    redirect('leaderboard.php');
}

// Manual leaderboard edit
if (isset($_POST['edit_entry'])) {
    $id = (int)$_POST['entry_id'];
    $val = floatval($_POST['value']);
    $db->query("UPDATE leaderboard SET value=$val WHERE id=$id");
    $_SESSION['success'] = 'Leaderboard updated.';
    redirect('leaderboard.php');
}

// Force refresh
if (isset($_GET['refresh'])) {
    refreshLeaderboard();
    $_SESSION['success'] = 'Leaderboard refreshed.';
    redirect('leaderboard.php');
}

$type = $_GET['type'] ?? 'donation';
$data = $db->query("SELECT * FROM leaderboard WHERE type='$type' ORDER BY value DESC LIMIT 50");

require_once '../includes/header.php';
?>
<div class="flex-between" style="margin-bottom:30px;">
    <h1>🏆 Leaderboard Management</h1>
    <div style="display:flex; gap:10px;">
        <a href="?type=donation" class="btn <?= $type==='donation'?'btn-p':'btn-o' ?> btn-sm">Donations</a>
        <a href="?type=views" class="btn <?= $type==='views'?'btn-p':'btn-o' ?> btn-sm">Views</a>
        <a href="?refresh=1" class="btn btn-w btn-sm">Refresh Now</a>
    </div>
</div>

<div class="card" style="padding:0;">
    <table>
        <thead><tr><th>Rank</th><th>Username</th><th>Value</th><th>Last Updated</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if ($data->num_rows > 0): $rank=1; ?>
                <?php while($row = $data->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?= $rank ?></strong></td>
                    <td><strong><?= sanitize($row['username']) ?></strong></td>
                    <td style="font-weight:700;color:var(--accent3);">
                        <?= $type==='donation' ? formatAmount($row['value']) : number_format($row['value']) ?>
                    </td>
                    <td style="font-size:.85rem;color:var(--text2);"><?= timeAgo($row['updated_at']) ?></td>
                    <td>
                        <form method="POST" style="display:flex; gap:5px;">
                            <input type="hidden" name="entry_id" value="<?= $row['id'] ?>">
                            <input type="text" name="value" class="fi" style="width:100px;padding:6px 10px;" value="<?= $row['value'] ?>">
                            <button type="submit" name="edit_entry" class="btn btn-p btn-sm">Update</button>
                        </form>
                    </td>
                </tr>
                <?php $rank++; endwhile; ?>
            <?php else: ?>
            <tr><td colspan="5"><div class="empty"><p>No entries found</p></div></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once '../includes/footer.php'; ?>
