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

// ── LİSTELE ──
if ($method === 'GET' && $action === 'list') {
    $stmt = $isAdmin
        ? $pdo->query("SELECT s.*, u.username FROM ssl_certs s JOIN users u ON u.id = s.user_id ORDER BY s.created_at DESC")
        : $pdo->prepare("SELECT * FROM ssl_certs WHERE user_id = ? ORDER BY created_at DESC");
    $isAdmin ? $stmt->execute() : $stmt->execute([$uid]);
    echo json_encode(['success' => true, 'certs' => $stmt->fetchAll()]);
    exit;
}

// ── SSL YAYINLA ──
if ($method === 'POST' && $action === 'issue') {
    $domain = strtolower(trim($data['domain'] ?? ''));
    $email  = trim($data['email'] ?? '');

    if (!$domain || !$email) { echo json_encode(['error' => 'Domain ve email gerekli']); exit; }

    // Domain bu kullanıcıya ait mi?
    $stmt = $pdo->prepare("SELECT id FROM domains WHERE domain = ? " . ($isAdmin ? '' : 'AND user_id = ?') . " LIMIT 1");
    $isAdmin ? $stmt->execute([$domain]) : $stmt->execute([$domain, $uid]);
    if (!$stmt->fetch()) { echo json_encode(['error' => 'Domain bulunamadı veya yetkiniz yok']); exit; }

    // Zaten var mı?
    $chk = $pdo->prepare("SELECT id FROM ssl_certs WHERE domain = ? LIMIT 1");
    $chk->execute([$domain]);
    $existing = $chk->fetch();

    if ($existing) {
        $pdo->prepare("UPDATE ssl_certs SET status = 'pending' WHERE domain = ?")->execute([$domain]);
    } else {
        $pdo->prepare("INSERT INTO ssl_certs (user_id, domain, status) VALUES (?,?,?)")
            ->execute([$uid, $domain, 'pending']);
    }

    $result = certbotIssue($domain, $email);

    if ($result['success']) {
        $expires = date('Y-m-d H:i:s', strtotime('+90 days'));
        $pdo->prepare("UPDATE ssl_certs SET status='active', issued_at=NOW(), expires_at=? WHERE domain=?")
            ->execute([$expires, $domain]);
        // Nginx'i güncelle
        $pdo->prepare("UPDATE domains SET ssl=1 WHERE domain=?")->execute([$domain]);
        echo json_encode(['success' => true]);
    } else {
        $pdo->prepare("UPDATE ssl_certs SET status='failed' WHERE domain=?")->execute([$domain]);
        echo json_encode(['error' => 'SSL yayınlanamadı: ' . $result['output']]);
    }
    exit;
}

// ── SSL İPTAL ──
if ($method === 'POST' && $action === 'revoke') {
    $id   = (int)($data['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM ssl_certs WHERE id = ? " . ($isAdmin ? '' : 'AND user_id = ?') . " LIMIT 1");
    $isAdmin ? $stmt->execute([$id]) : $stmt->execute([$id, $uid]);
    $cert = $stmt->fetch();
    if (!$cert) { echo json_encode(['error' => 'Bulunamadı']); exit; }

    certbotRevoke($cert['domain']);
    $pdo->prepare("UPDATE ssl_certs SET status='expired' WHERE id=?")->execute([$id]);
    $pdo->prepare("UPDATE domains SET ssl=0 WHERE domain=?")->execute([$cert['domain']]);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Geçersiz istek']);
