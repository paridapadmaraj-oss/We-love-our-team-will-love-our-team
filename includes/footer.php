</div><!-- .container -->
<footer>
    <div style="max-width:1300px; margin:0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; margin-bottom:20px;">
            <div>
                <span style="font-size:1.5rem; font-weight:800; background:var(--grad); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">BloxScript</span>
                <p style="color:var(--text3); font-size:.85rem; margin-top:4px;">Community Script Platform</p>
            </div>
            <div style="display:flex; gap:20px;">
                <a href="index.php">Home</a>
                <a href="leaderboard.php">Leaderboard</a>
                <a href="donate.php">Donate</a>
                <a href="appeal.php">Appeal</a>
                <a href="https://discord.gg/swKTPSV4n" target="_blank">Discord</a>
            </div>
        </div>
        <hr style="border-color:var(--border); margin:20px 0;">
        <p>&copy; <?= date('Y') ?> BloxScript. All rights reserved. Not affiliated with Roblox Corporation.</p>
    </div>
</footer>
<script>
// Auto-hide alerts after 5 seconds
document.querySelectorAll('.alert').forEach(el => {
    setTimeout(() => { el.style.opacity = '0'; el.style.transition = '0.5s'; setTimeout(() => el.remove(), 500); }, 5000);
});
</script>
</body>
</html>
<?php $db = null; ?>
