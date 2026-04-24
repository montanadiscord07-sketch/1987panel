<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/shell.php';

header('Content-Type: application/json');
requireLogin();

$user    = currentUser();
$uid     = $user['id'];
$isAdmin = isAdmin();
$method  = $_SERVER['REQUEST_METHOD'];
$data    = json_decode(file_get_contents('php://input'), true) ?? [];
$action  = $data['action'] ?? $_GET['action'] ?? '';

// ── MAİL DOMAİNLERİ LİSTELE ──
if ($method === 'GET' && $action === 'domains') {
    $stmt = $isAdmin
        ? $pdo->query("SELECT md.*, u.username, (SELECT COUNT(*) FROM mail_accounts ma WHERE ma.domain_id = md.id) as account_count FROM mail_domains md JOIN users u ON u.id = md.user_id ORDER BY md.created_at DESC")
        : $pdo->prepare("SELECT *, (SELECT COUNT(*) FROM mail_accounts ma WHERE ma.domain_id = mail_domains.id) as account_count FROM mail_domains WHERE user_id = ? ORDER BY created_at DESC");
    $isAdmin ? $stmt->execute() : $stmt->execute([$uid]);
    echo json_encode(['success' => true, 'domains' => $stmt->fetchAll()]);
    exit;
}

// ── MAİL DOMAİN EKLE ──
if ($method === 'POST' && $action === 'add_domain') {
    $domain = strtolower(trim($data['domain'] ?? ''));
    if (!$domain || !preg_match('/^[a-z0-9][a-z0-9\-\.]+\.[a-z]{2,}$/', $domain)) {
        echo json_encode(['error' => 'Geçersiz domain']); exit;
    }
    $chk = $pdo->prepare("SELECT id FROM mail_domains WHERE domain = ? LIMIT 1");
    $chk->execute([$domain]);
    if ($chk->fetch()) { echo json_encode(['error' => 'Bu domain zaten ekli']); exit; }

    // Postfix virtual domains
    $pdo->prepare("INSERT INTO mail_domains (user_id, domain) VALUES (?,?)")->execute([$uid, $domain]);
    _rebuildPostfix();

    echo json_encode(['success' => true]);
    exit;
}

// ── MAİL DOMAİN SİL ──
if ($method === 'POST' && $action === 'delete_domain') {
    $id   = (int)($data['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM mail_domains WHERE id = ? " . ($isAdmin ? '' : 'AND user_id = ?') . " LIMIT 1");
    $isAdmin ? $stmt->execute([$id]) : $stmt->execute([$id, $uid]);
    $md = $stmt->fetch();
    if (!$md) { echo json_encode(['error' => 'Bulunamadı']); exit; }

    $pdo->prepare("DELETE FROM mail_domains WHERE id = ?")->execute([$id]);
    _rebuildPostfix();
    echo json_encode(['success' => true]);
    exit;
}

// ── HESAPLARI LİSTELE ──
if ($method === 'GET' && $action === 'accounts') {
    $domainId = (int)($_GET['domain_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM mail_accounts WHERE domain_id = ? " . ($isAdmin ? '' : 'AND user_id = ?') . " ORDER BY created_at DESC");
    $isAdmin ? $stmt->execute([$domainId]) : $stmt->execute([$domainId, $uid]);
    echo json_encode(['success' => true, 'accounts' => $stmt->fetchAll()]);
    exit;
}

// ── HESAP EKLE ──
if ($method === 'POST' && $action === 'add_account') {
    $domainId = (int)($data['domain_id'] ?? 0);
    $username = preg_replace('/[^a-z0-9._\-]/', '', strtolower(trim($data['username'] ?? '')));
    $password = $data['password'] ?? '';
    $quota    = max(100, (int)($data['quota_mb'] ?? 1024));

    if (!$username || !$password) { echo json_encode(['error' => 'Kullanıcı adı ve şifre gerekli']); exit; }

    $stmt = $pdo->prepare("SELECT * FROM mail_domains WHERE id = ? " . ($isAdmin ? '' : 'AND user_id = ?') . " LIMIT 1");
    $isAdmin ? $stmt->execute([$domainId]) : $stmt->execute([$domainId, $uid]);
    $md = $stmt->fetch();
    if (!$md) { echo json_encode(['error' => 'Domain bulunamadı']); exit; }

    $email = $username . '@' . $md['domain'];
    $chk   = $pdo->prepare("SELECT id FROM mail_accounts WHERE email = ? LIMIT 1");
    $chk->execute([$email]);
    if ($chk->fetch()) { echo json_encode(['error' => 'Bu mail adresi zaten var']); exit; }

    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $pdo->prepare("INSERT INTO mail_accounts (user_id, domain_id, username, email, password, quota_mb) VALUES (?,?,?,?,?,?)")
        ->execute([$uid, $domainId, $username, $email, $hashed, $quota]);

    _rebuildPostfix();
    echo json_encode(['success' => true, 'email' => $email]);
    exit;
}

// ── HESAP SİL ──
if ($method === 'POST' && $action === 'delete_account') {
    $id   = (int)($data['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM mail_accounts WHERE id = ? " . ($isAdmin ? '' : 'AND user_id = ?') . " LIMIT 1");
    $isAdmin ? $stmt->execute([$id]) : $stmt->execute([$id, $uid]);
    $acc = $stmt->fetch();
    if (!$acc) { echo json_encode(['error' => 'Bulunamadı']); exit; }

    $pdo->prepare("DELETE FROM mail_accounts WHERE id = ?")->execute([$id]);
    _rebuildPostfix();
    echo json_encode(['success' => true]);
    exit;
}

// ── ŞİFRE DEĞİŞTİR ──
if ($method === 'POST' && $action === 'change_password') {
    $id       = (int)($data['id'] ?? 0);
    $password = $data['password'] ?? '';
    if (!$password || strlen($password) < 8) { echo json_encode(['error' => 'Şifre en az 8 karakter olmalı']); exit; }

    $stmt = $pdo->prepare("SELECT id FROM mail_accounts WHERE id = ? " . ($isAdmin ? '' : 'AND user_id = ?') . " LIMIT 1");
    $isAdmin ? $stmt->execute([$id]) : $stmt->execute([$id, $uid]);
    if (!$stmt->fetch()) { echo json_encode(['error' => 'Bulunamadı']); exit; }

    $pdo->prepare("UPDATE mail_accounts SET password = ? WHERE id = ?")->execute([password_hash($password, PASSWORD_BCRYPT), $id]);
    _rebuildPostfix();
    echo json_encode(['success' => true]);
    exit;
}

function _rebuildPostfix(): void {
    global $pdo;
    // Virtual mailbox domains
    $domains = $pdo->query("SELECT domain FROM mail_domains WHERE active = 1")->fetchAll(PDO::FETCH_COLUMN);
    file_put_contents('/etc/postfix/virtual_domains', implode("\n", $domains) . "\n");

    // Virtual mailbox maps
    $accounts = $pdo->query("SELECT ma.email, md.domain, ma.username FROM mail_accounts ma JOIN mail_domains md ON md.id = ma.domain_id WHERE ma.active = 1")->fetchAll();
    $lines = [];
    foreach ($accounts as $a) {
        $lines[] = $a['email'] . "\t" . $a['domain'] . '/' . $a['username'] . '/';
    }
    file_put_contents('/etc/postfix/virtual_mailbox', implode("\n", $lines) . "\n");

    shellRun('sudo postmap /etc/postfix/virtual_mailbox');
    shellRun('sudo postmap /etc/postfix/virtual_domains');
    shellRun('sudo systemctl reload postfix');
}

http_response_code(400);
echo json_encode(['error' => 'Geçersiz istek']);
