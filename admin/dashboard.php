<?php
require_once '../includes/session.php';
if (!isModerator()) { $_SESSION['error'] = 'Access denied.'; redirect('../index.php'); }
$page_title = 'Admin Dashboard';
$db = getDB();
$user = currentUser();

// Stats
$total_users = $db->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$banned_users = $db->query("SELECT COUNT(*) as c FROM users WHERE status='banned'")->fetch_assoc()['c'];
$pending_appeals = $db->query("SELECT COUNT(*) as c FROM appeals WHERE status='pending'")->fetch_assoc()['c'];
$pending_donations = $db->query("SELECT COUNT(*) as c FROM donations WHERE status='pending'")->fetch_assoc()['c'];
$total_donations = $db->query("SELECT COALESCE(SUM(amount),0) as t FROM donations WHERE status='approved'")->fetch_assoc()['t'];

require_once '../includes/header.php';
?>
<div class="flex-between" style="margin-bottom:30px;">
    <h1>📊 Admin Dashboard</h1>
    <div style="display:flex; gap:10px;">
        <a href="dashboard.php" class="btn btn-p btn-sm">Dashboard</a>
        <a href="users.php" class="btn btn-o btn-sm">Users</a>
        <a href="bans.php" class="btn btn-o btn-sm">Bans</a>
        <a href="appeals.php" class="btn btn-o btn-sm">Appeals</a>
        <a href="donations.php" class="btn btn-o btn-sm">Donations</a>
        <a href="leaderboard.php" class="btn btn-o btn-sm">Leaderboard</a>
    </div>
</div>

<div class="grid-3" style="margin-bottom:30px;">
    <div class="card stat"><div class="stat-num"><?= $total_users ?></div><div class="stat-label">Total Users</div></div>
    <div class="card stat"><div class="stat-num" style="color:var(--danger);"><?= $banned_users ?></div><div class="stat-label">Banned Users</div></div>
    <div class="card stat"><div class="stat-num" style="color:var(--warn);"><?= $pending_appeals ?></div><div class="stat-label">Pending Appeals</div></div>
    <div class="card stat"><div class="stat-num" style="color:var(--warn);"><?= $pending_donations ?></div><div class="stat-label">Pending Donations</div></div>
    <div class="card stat"><div class="stat-num"><?= formatAmount($total_donations) ?></div><div class="stat-label">Total Donations</div></div>
    <div class="card stat"><div class="stat-num"><?= $user['role'] ?></div><div class="stat-label">Your Role</div></div>
</div>

<div class="grid-2">
    <div class="card">
        <h3 style="margin-bottom:15px;">Quick Actions</h3>
        <div style="display:flex; flex-direction:column; gap:10px;">
            <a href="users.php" class="btn btn-o" style="justify-content:center;">Manage Users</a>
            <a href="bans.php" class="btn btn-o" style="justify-content:center;">Ban Management</a>
            <a href="appeals.php" class="btn btn-o" style="justify-content:center;">Review Appeals</a>
            <a href="donations.php" class="btn btn-o" style="justify-content:center;">Verify Donations</a>
            <a href="leaderboard.php" class="btn btn-o" style="justify-content:center;">Edit Leaderboard</a>
        </div>
    </div>
    <div class="card">
        <h3 style="margin-bottom:15px;">Settings</h3>
        <form method="POST" action="leaderboard.php">
            <div class="fg"><label>Donation UPI ID</label><input type="text" name="upi_id" class="fi" value="<?= sanitize(getSetting('donation_upi')?:'Padmaraj@fam') ?>"></div>
            <button type="submit" name="update_settings" class="btn btn-p btn-sm">Save Settings</button>
        </form>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
