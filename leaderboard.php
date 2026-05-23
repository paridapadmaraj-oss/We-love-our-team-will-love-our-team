<?php
require_once 'includes/session.php';
$page_title = 'Leaderboard';
$db = getDB();

$type = $_GET['type'] ?? 'donation';
$last_refresh = getSetting('last_leaderboard_refresh');
if (!$last_refresh || (time() - strtotime($last_refresh)) > 28800) refreshLeaderboard();

$data = $db->prepare("SELECT username, value FROM leaderboard WHERE type = ? ORDER BY value DESC LIMIT 50");
$data->bind_param("s", $type);
$data->execute();
$rows = $data->get_result();

$total_donations = $db->query("SELECT COALESCE(SUM(amount),0) as t FROM donations WHERE status='approved'")->fetch_assoc()['t'];
require_once 'includes/header.php';
?>
<div class="flex-between" style="margin-bottom:30px;">
    <h1>🏆 Leaderboard</h1>
    <div style="display:flex; gap:10px;">
        <a href="?type=donation" class="btn <?= $type==='donation'?'btn-p':'btn-o' ?> btn-sm">Donations</a>
        <a href="?type=views" class="btn <?= $type==='views'?'btn-p':'btn-o' ?> btn-sm">Views</a>
    </div>
</div>

<?php if ($type === 'donation'): ?>
    <div class="card" style="margin-bottom:30px; text-align:center;">
        <div class="stat-num"><?= formatAmount($total_donations) ?></div>
        <div class="stat-label">Total Donations Received</div>
    </div>
<?php endif; ?>

<div class="card" style="padding:0;">
    <?php if ($rows->num_rows > 0): $rank=1; ?>
        <?php while($row = $rows->fetch_assoc()): ?>
            <div class="leaderboard-item">
                <div class="rank-num <?= $rank<=3?'rank-'.$rank:'' ?>"><?= $rank ?></div>
                <div style="flex:1;"><strong><?= sanitize($row['username']) ?></strong></div>
                <div style="font-weight:700; color:<?= $type==='donation'?'var(--accent3)':'var(--accent2)' ?>;">
                    <?= $type==='donation' ? formatAmount($row['value']) : number_format($row['value']).' views' ?>
                </div>
            </div>
        <?php $rank++; endwhile; ?>
    <?php else: ?>
        <div class="empty"><h3>No entries yet</h3><p>Be the first on the leaderboard!</p></div>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
