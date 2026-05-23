<?php
require_once 'includes/session.php';
$page_title = 'Home';
$db = getDB();

// Check if leaderboard needs refresh
$last_refresh = getSetting('last_leaderboard_refresh');
if (!$last_refresh || (time() - strtotime($last_refresh)) > 28800) {
    refreshLeaderboard();
}

// Stats
$total_users = $db->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_donations = $db->query("SELECT COALESCE(SUM(amount),0) as t FROM donations WHERE status='approved'")->fetch_assoc()['t'];
$top_donors = $db->query("SELECT username, value FROM leaderboard WHERE type='donation' ORDER BY value DESC LIMIT 5");
$top_views = $db->query("SELECT username, value FROM leaderboard WHERE type='views' ORDER BY value DESC LIMIT 5");

require_once 'includes/header.php';
?>

<div class="hero">
    <h1>Welcome to BloxScript</h1>
    <p>The #1 community platform for Roblox script creators. Share, discover, and learn Lua scripting together.</p>
    <?php if (!isLoggedIn()): ?>
        <a href="auth/register.php" class="btn btn-p" style="font-size:1.1rem;">Get Started Free</a>
    <?php endif; ?>
</div>

<!-- Stats -->
<div class="grid-3" style="margin-bottom:40px;">
    <div class="card stat">
        <div class="stat-num"><?= number_format($total_users) ?></div>
        <div class="stat-label">Community Members</div>
    </div>
    <div class="card stat">
        <div class="stat-num"><?= formatAmount($total_donations) ?></div>
        <div class="stat-label">Total Donations</div>
    </div>
    <div class="card stat">
        <div class="stat-num">#1</div>
        <div class="stat-label">Script Sharing Platform</div>
    </div>
</div>

<!-- Leaderboards -->
<div class="grid-2">
    <div class="card">
        <h2 style="margin-bottom:20px; font-size:1.3rem;">🏆 Top Donors</h2>
        <?php if ($top_donors->num_rows > 0): $rank=1; ?>
            <?php while($row = $top_donors->fetch_assoc()): ?>
                <div class="leaderboard-item">
                    <div class="rank-num <?= $rank<=3?'rank-'.$rank:'' ?>"><?= $rank ?></div>
                    <div style="flex:1;"><strong><?= sanitize($row['username']) ?></strong></div>
                    <div style="color:var(--accent3); font-weight:700;"><?= formatAmount($row['value']) ?></div>
                </div>
            <?php $rank++; endwhile; ?>
        <?php else: ?>
            <div class="empty"><p>No donations yet. Be the first!</p></div>
        <?php endif; ?>
        <a href="leaderboard.php?type=donation" style="display:block; text-align:center; margin-top:15px; color:var(--accent2);">View Full Leaderboard →</a>
    </div>

    <div class="card">
        <h2 style="margin-bottom:20px; font-size:1.3rem;">👁️ Most Viewed Creators</h2>
        <?php if ($top_views->num_rows > 0): $rank=1; ?>
            <?php while($row = $top_views->fetch_assoc()): ?>
                <div class="leaderboard-item">
                    <div class="rank-num <?= $rank<=3?'rank-'.$rank:'' ?>"><?= $rank ?></div>
                    <div style="flex:1;"><strong><?= sanitize($row['username']) ?></strong></div>
                    <div style="color:var(--accent2); font-weight:700;"><?= number_format($row['value']) ?> views</div>
                </div>
            <?php $rank++; endwhile; ?>
        <?php else: ?>
            <div class="empty"><p>No views recorded yet.</p></div>
        <?php endif; ?>
        <a href="leaderboard.php?type=views" style="display:block; text-align:center; margin-top:15px; color:var(--accent2);">View Full Leaderboard →</a>
    </div>
</div>

<!-- Features -->
<div class="grid-3" style="margin-top:40px;">
    <div class="card" style="text-align:center;">
        <div style="font-size:3rem; margin-bottom:15px;">📜</div>
        <h3>Share Scripts</h3>
        <p style="color:var(--text2);">Upload and share your Lua scripts with the community.</p>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:3rem; margin-bottom:15px;">🏅</div>
        <h3>Leaderboards</h3>
        <p style="color:var(--text2);">Compete for top donor and most viewed creator spots.</p>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:3rem; margin-bottom:15px;">🛡️</div>
        <h3>Safe & Moderated</h3>
        <p style="color:var(--text2);">Active moderation and appeal system for a safe community.</p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
