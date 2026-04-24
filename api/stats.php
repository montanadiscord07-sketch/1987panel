<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');
requireLogin();

$user    = currentUser();
$uid     = $user['id'];
$isAdmin = isAdmin();

$domainCount = $pdo->prepare($isAdmin
    ? "SELECT COUNT(*) FROM domains"
    : "SELECT COUNT(*) FROM domains WHERE user_id = ?");
$domainCount->execute($isAdmin ? [] : [$uid]);

$dbCount = $pdo->prepare($isAdmin
    ? "SELECT COUNT(*) FROM databases"
    : "SELECT COUNT(*) FROM databases WHERE user_id = ?");
$dbCount->execute($isAdmin ? [] : [$uid]);

$mailCount = $pdo->prepare($isAdmin
    ? "SELECT COUNT(*) FROM mail_accounts"
    : "SELECT COUNT(*) FROM mail_accounts WHERE user_id = ?");
$mailCount->execute($isAdmin ? [] : [$uid]);

$sslCount = $pdo->prepare($isAdmin
    ? "SELECT COUNT(*) FROM ssl_certs WHERE status = 'active'"
    : "SELECT COUNT(*) FROM ssl_certs WHERE user_id = ? AND status = 'active'");
$sslCount->execute($isAdmin ? [] : [$uid]);

$stats = [
    'domains'   => (int)$domainCount->fetchColumn(),
    'databases' => (int)$dbCount->fetchColumn(),
    'mails'     => (int)$mailCount->fetchColumn(),
    'ssl'       => (int)$sslCount->fetchColumn(),
];

if ($isAdmin) {
    $u = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['users'] = (int)$u;
}

// Sunucu bilgisi (sadece admin)
if ($isAdmin) {
    $cpu   = shell_exec("top -bn1 | grep 'Cpu(s)' | awk '{print $2+$4\"%\"}'") ?? '—';
    $ram   = shell_exec("free -m | awk 'NR==2{printf \"%s/%sMB\", $3,$2}'") ?? '—';
    $disk  = shell_exec("df -h / | awk 'NR==2{print $3\"/\"$2}'") ?? '—';
    $uptime = shell_exec("uptime -p") ?? '—';

    $stats['server'] = [
        'cpu'    => trim($cpu),
        'ram'    => trim($ram),
        'disk'   => trim($disk),
        'uptime' => trim($uptime),
    ];
}

echo json_encode(['success' => true, 'stats' => $stats]);
