<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? sanitize($page_title) . ' - ' : '' ?>BloxScript</title>
    <meta name="description" content="BloxScript - Community Platform for Roblox Lua Scripts">
    <link rel="icon" type="image/png" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📜</text></svg>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --bg: #0a0b1e; --bg2: #12142d; --card: #1a1d3e; --input: #0f1130;
            --accent: #6c5ce7; --accent2: #a855f7; --accent3: #00d4aa;
            --text: #e2e8f0; --text2: #94a3b8; --text3: #64748b;
            --border: #2a2d5a; --danger: #ef4444; --success: #22c55e; --warn: #f59e0b;
            --grad: linear-gradient(135deg,#6c5ce7,#a855f7,#00d4aa);
            --shadow: 0 4px 20px rgba(108,92,231,0.15);
            --r: 12px;
        }
        body {
            font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
            background: var(--bg); color: var(--text); min-height: 100vh; line-height: 1.6;
        }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg2); }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 4px; }
        a { color: var(--accent2); text-decoration: none; transition: .3s; }
        a:hover { color: var(--accent3); }

        /* NAVBAR */
        nav {
            background: var(--bg2); border-bottom: 1px solid var(--border);
            padding: 0 20px; position: sticky; top: 0; z-index: 1000;
            backdrop-filter: blur(10px);
        }
        .nav-inner {
            max-width: 1300px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
            height: 70px;
        }
        .logo {
            font-size: 1.8rem; font-weight: 800;
            background: var(--grad); -webkit-background-clip: text;
            -webkit-text-fill-color: transparent; letter-spacing: -1px;
        }
        .logo span { -webkit-text-fill-color: var(--accent3); }
        .nav-links { display: flex; align-items: center; gap: 8px; }
        .nav-links a, .nav-links button {
            color: var(--text2); padding: 10px 18px; border-radius: 8px;
            font-size: .9rem; border: none; background: none; cursor: pointer;
            transition: .3s; font-weight: 500;
        }
        .nav-links a:hover, .nav-links button:hover { color: var(--text); background: rgba(108,92,231,0.1); }
        .btn-nav {
            background: var(--grad) !important; color: #fff !important;
            padding: 10px 24px !important; border-radius: 8px !important;
        }
        .btn-nav:hover { transform: translateY(-2px); box-shadow: var(--shadow); }

        .user-badge {
            display: flex; align-items: center; gap: 10px;
            padding: 6px 16px; border-radius: 8px; background: rgba(108,92,231,0.1);
        }
        .user-badge .avatar {
            width: 32px; height: 32px; border-radius: 50%; background: var(--grad);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .8rem;
        }
        .user-badge .role-tag {
            font-size: .65rem; padding: 2px 8px; border-radius: 20px; background: var(--accent);
        }
        .user-badge .role-tag.admin { background: var(--danger); }
        .user-badge .role-tag.mod { background: var(--warn); color: #000; }

        /* CONTAINER */
        .container { max-width: 1300px; margin: 0 auto; padding: 30px 20px; }

        /* ALERTS */
        .alert {
            padding: 16px 20px; border-radius: var(--r); margin-bottom: 20px;
            font-size: .95rem; display: flex; align-items: center; gap: 12px;
            animation: slideIn .3s ease;
        }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .alert-s { background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.3); color: var(--success); }
        .alert-e { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: var(--danger); }
        .alert-i { background: rgba(108,92,231,0.15); border: 1px solid rgba(108,92,231,0.3); color: var(--accent2); }
        .alert-w { background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.3); color: var(--warn); }

        /* FORMS */
        .fg { margin-bottom: 20px; }
        .fg label { display: block; margin-bottom: 8px; color: var(--text2); font-size: .9rem; font-weight: 600; }
        .fi, .fta, .fs {
            width: 100%; padding: 14px 16px; background: var(--input);
            border: 1px solid var(--border); border-radius: 8px;
            color: var(--text); font-size: .95rem; transition: .3s; outline: none;
        }
        .fi:focus, .fta:focus, .fs:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(108,92,231,0.2); }
        .fta { min-height: 120px; resize: vertical; }

        .btn {
            padding: 14px 28px; border: none; border-radius: 8px;
            font-size: .95rem; font-weight: 600; cursor: pointer;
            transition: .3s; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-p { background: var(--grad); color: #fff; }
        .btn-p:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
        .btn-d { background: var(--danger); color: #fff; }
        .btn-s { background: var(--success); color: #fff; }
        .btn-w { background: var(--warn); color: #000; }
        .btn-o { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .btn-o:hover { border-color: var(--accent); color: var(--accent); }
        .btn-sm { padding: 8px 16px; font-size: .85rem; }

        /* CARDS & GRID */
        .card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: var(--r); padding: 25px; transition: .3s;
        }
        .card:hover { border-color: var(--accent); transform: translateY(-2px); box-shadow: var(--shadow); }
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; }
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fill, minmax(450px, 1fr)); gap: 24px; }
        .flex { display: flex; gap: 20px; flex-wrap: wrap; }
        .flex-center { display: flex; align-items: center; justify-content: center; }
        .flex-between { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; }

        /* HERO */
        .hero {
            text-align: center; padding: 80px 20px;
            background: linear-gradient(135deg, rgba(108,92,231,0.1), rgba(168,85,247,0.1), rgba(0,212,170,0.05));
            border-radius: var(--r); margin-bottom: 40px;
        }
        .hero h1 { font-size: 3.5rem; margin-bottom: 16px; background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p { font-size: 1.2rem; color: var(--text2); max-width: 700px; margin: 0 auto 30px; }

        /* TABLE */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--border); }
        th { color: var(--text2); font-weight: 600; font-size: .85rem; text-transform: uppercase; letter-spacing: 1px; }
        tr:hover td { background: rgba(108,92,231,0.05); }

        /* STATS */
        .stat { text-align: center; padding: 20px; }
        .stat-num { font-size: 2rem; font-weight: 800; background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-label { color: var(--text2); font-size: .85rem; margin-top: 4px; }

        /* FOOTER */
        footer {
            background: var(--bg2); border-top: 1px solid var(--border);
            padding: 40px 20px; margin-top: 60px; text-align: center;
        }
        footer p { color: var(--text3); font-size: .9rem; }

        /* MISC */
        .badge {
            display: inline-block; padding: 4px 12px; border-radius: 20px;
            font-size: .75rem; font-weight: 600;
        }
        .badge-pending { background: rgba(245,158,11,0.2); color: var(--warn); }
        .badge-approved { background: rgba(34,197,94,0.2); color: var(--success); }
        .badge-denied, .badge-banned { background: rgba(239,68,68,0.2); color: var(--danger); }
        .badge-active { background: rgba(34,197,94,0.2); color: var(--success); }
        .badge-admin { background: var(--danger); color: #fff; }
        .badge-mod { background: var(--warn); color: #000; }
        .badge-user { background: var(--accent); color: #fff; }

        .empty {
            text-align: center; padding: 60px 20px; color: var(--text3);
        }
        .empty h3 { font-size: 1.4rem; margin-bottom: 8px; color: var(--text2); }

        .loading { text-align: center; padding: 40px; color: var(--text2); }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .nav-links { gap: 4px; }
            .nav-links a { padding: 8px 12px; font-size: .8rem; }
            .hero h1 { font-size: 2rem; }
            .hero p { font-size: 1rem; }
            .grid-2 { grid-template-columns: 1fr; }
            .container { padding: 20px 15px; }
        }

        /* OPT INLINE */
        .otp-input { letter-spacing: 8px; font-size: 1.5rem; text-align: center; font-weight: 700; }
        .leaderboard-item {
            display: flex; align-items: center; gap: 16px;
            padding: 14px 20px; border-bottom: 1px solid var(--border);
            transition: .3s;
        }
        .leaderboard-item:hover { background: rgba(108,92,231,0.05); }
        .rank-num { width: 36px; height: 36px; border-radius: 50%; background: var(--grad); display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
        .rank-1 { background: linear-gradient(135deg, #ffd700, #ffaa00); }
        .rank-2 { background: linear-gradient(135deg, #c0c0c0, #a0a0a0); }
        .rank-3 { background: linear-gradient(135deg, #cd7f32, #a06030); }
        .donate-qr { text-align: center; padding: 30px; }
        .donate-qr img { max-width: 250px; border-radius: var(--r); }
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7); z-index: 9999;
            display: none; align-items: center; justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal { background: var(--card); border-radius: var(--r); padding: 30px; max-width: 500px; width: 90%; border: 1px solid var(--border); }
        .modal h2 { margin-bottom: 20px; }
    </style>
</head>
<body>

<nav>
    <div class="nav-inner">
        <a href="index.php" class="logo">Blox<span>Script</span></a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="leaderboard.php">Leaderboard</a>
            <a href="donate.php">Donate</a>
            <?php if (isLoggedIn()): ?>
                <?php $user = currentUser(); ?>
                <a href="appeal.php">Appeal</a>
                <?php if (isModerator()): ?>
                    <a href="admin/dashboard.php">Admin Panel</a>
                <?php endif; ?>
                <div class="user-badge">
                    <div class="avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                    <span><?= sanitize($user['username']) ?></span>
                    <?php if ($user['role'] === 'admin'): ?>
                        <span class="role-tag admin">Admin</span>
                    <?php elseif ($user['role'] === 'moderator'): ?>
                        <span class="role-tag mod">Mod</span>
                    <?php endif; ?>
                </div>
                <a href="auth/logout.php" class="btn-o btn-sm">Logout</a>
            <?php else: ?>
                <a href="auth/login.php">Login</a>
                <a href="auth/register.php" class="btn-nav">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-s"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-e"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['info'])): ?>
        <div class="alert alert-i"><?= $_SESSION['info']; unset($_SESSION['info']); ?></div>
    <?php endif; ?>
