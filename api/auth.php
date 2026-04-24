<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$data   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $data['action'] ?? '';

// ── GİRİŞ ──
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';

    if (!$username || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Kullanıcı adı ve şifre gerekli']);
        exit;
    }

    // Rate limiting için basit kontrol
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sessions WHERE ip = ? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    $stmt->execute([$ip]);
    if ($stmt->fetchColumn() > 10) {
        usleep(2000000); // 2 saniye beklet
        http_response_code(429);
        echo json_encode(['error' => 'Çok fazla deneme. Lütfen bekleyin.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND active = 1 LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        usleep(300000);
        http_response_code(401);
        echo json_encode(['error' => 'Kullanıcı adı veya şifre hatalı']);
        exit;
    }

    $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
    createSession($user['id']);

    echo json_encode(['success' => true, 'user' => ['id' => $user['id'], 'username' => $user['username'], 'role' => $user['role']]]);
    exit;
}

// ── ÇIKIŞ ──
if ($action === 'logout') {
    destroySession();
    echo json_encode(['success' => true]);
    exit;
}

// ── BEN KİMİM ──
if ($action === 'me') {
    $user = currentUser();
    if (!$user) { http_response_code(401); echo json_encode(['error' => 'Oturum yok']); exit; }
    echo json_encode(['success' => true, 'user' => $user]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Geçersiz istek']);
