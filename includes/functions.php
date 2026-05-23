<?php
// BloxScript - Core Functions (continued)
require_once __DIR__ . '/../config/database.php';

function isLoggedIn() { return isset($_SESSION['user_id']); }
function getUserRole() { return $_SESSION['user_role'] ?? 'guest'; }
function isAdmin() { return getUserRole() === 'admin'; }
function isModerator() { return getUserRole() === 'moderator' || isAdmin(); }
function redirect($url) { header("Location: $url"); exit; }
function sanitize($input) { return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8'); }
function generateToken($length = 32) { return bin2hex(random_bytes($length)); }

function currentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function isBanned() {
    if (!isLoggedIn()) return false;
    $user = currentUser();
    return $user && $user['status'] === 'banned';
}

function isIPBanned() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM ip_bans WHERE ip_address = ?");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getCSRFToken() {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = generateToken();
    return $_SESSION['csrf_token'];
}

function verifyCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function timeAgo($datetime) {
    $ts = strtotime($datetime); $d = time() - $ts;
    if ($d < 60) return 'just now';
    if ($d < 3600) return floor($d/60).'m ago';
    if ($d < 86400) return floor($d/3600).'h ago';
    if ($d < 604800) return floor($d/86400).'d ago';
    return date('M j, Y', $ts);
}

function formatAmount($amount) { return '₹'.number_format($amount, 2); }

function checkSignupCooling($email, $username) {
    $db = getDB();
    $stmt = $db->prepare("SELECT signup_cooling_until FROM users WHERE email = ? OR username = ? ORDER BY signup_cooling_until DESC LIMIT 1");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    if ($row = $stmt->get_result()->fetch_assoc()) {
        if ($row['signup_cooling_until'] && strtotime($row['signup_cooling_until']) > time()) return false;
    }
    return true;
}

function refreshLeaderboard() {
    $db = getDB();
    $db->query("DELETE FROM leaderboard WHERE type = 'donation'");
    $r = $db->query("SELECT username, SUM(amount) as total FROM donations WHERE status='approved' GROUP BY username ORDER BY total DESC");
    while ($row = $r->fetch_assoc()) {
        $s = $db->prepare("INSERT INTO leaderboard (username, type, value) VALUES (?, 'donation', ?)");
        $s->bind_param("sd", $row['username'], $row['total']); $s->execute();
    }
    $db->query("DELETE FROM leaderboard WHERE type = 'views'");
    $r = $db->query("SELECT username, total_views FROM users WHERE total_views > 0 ORDER BY total_views DESC");
    while ($row = $r->fetch_assoc()) {
        $s = $db->prepare("INSERT INTO leaderboard (username, type, value) VALUES (?, 'views', ?)");
        $s->bind_param("sd", $row['username'], $row['total_views']); $s->execute();
    }
    updateSetting('last_leaderboard_refresh', date('Y-m-d H:i:s'));
}

function sendEmailOTP($email, $otp) {
    $subj = "BloxScript - Password Reset OTP";
    $msg = "Your OTP for password reset: $otp\nValid for 15 minutes.\n- BloxScript Team";
    $headers = "From: noreply@bloxscript.com\r\nReply-To: support@bloxscript.com\r\nX-Mailer: PHP/".phpversion();
    return mail($email, $subj, $msg, $headers);
}

function aiVerifyDonationAmount($screenshot_path) {
    $full_path = __DIR__ . '/../' . $screenshot_path;
    $amount = 0.00;
    
    // Try filename patterns
    preg_match('/(\d+[\.\,]\d{2})/', basename($screenshot_path), $m);
    if (!empty($m[1])) $amount = floatval(str_replace(',', '.', $m[1]));
    
    // Try Tesseract OCR if available
    $output = []; $rc = -1;
    @exec("which tesseract 2>/dev/null", $output, $rc);
    if ($rc === 0 && file_exists($full_path)) {
        $outfile = __DIR__ . '/../uploads/ocr_' . time();
        @exec("tesseract " . escapeshellarg($full_path) . " " . escapeshellarg($outfile) . " 2>/dev/null", $o, $r);
        $txtfile = $outfile . '.txt';
        if ($r === 0 && file_exists($txtfile)) {
            $text = file_get_contents($txtfile);
            preg_match_all('/(?:₹|Rs\.?|INR|\$)?\s*(\d+[\.\,]\d{2})/', $text, $ams);
            if (!empty($ams[1])) {
                foreach ($ams[1] as $am) {
                    $v = floatval(str_replace(',', '', $am));
                    if ($v > $amount && $v < 100000) $amount = $v;
                }
            }
            @unlink($txtfile);
        }
    }
    return $amount > 0 ? $amount : null;
}

function canAppeal($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT appeal_count, last_appeal_date FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();
    if (!$u) return ['can' => false, 'reason' => 'User not found'];
    if ($u['appeal_count'] >= 3) return ['can' => false, 'reason' => 'Maximum 3 appeals reached'];
    if ($u['last_appeal_date']) {
        $days_passed = (time() - strtotime($u['last_appeal_date'])) / 86400;
        if ($days_passed < 3) return ['can' => false, 'reason' => 'Please wait ' . ceil(3 - $days_passed) . ' more day(s) before appealing'];
    }
    return ['can' => true, 'reason' => ''];
}
?>
