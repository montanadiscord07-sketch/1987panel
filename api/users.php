<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');
requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$data   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $data['action'] ?? $_GET['action'] ?? '';

// ── LİSTELE ──
if ($method === 'GET' && $action === 'list') {
    $stmt = $pdo->query("SELECT id, username, email, role, active, last_login, created_at FROM users ORDER BY created_at DESC");
    echo json_encode(['success' => true, 'users' => $stmt->fetchAll()]);
    exit;
}

// ── OLUŞTUR ──
if ($method === 'POST' && $action === 'create') {
    $username = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($data['username'] ?? '')));
    $email    = strtolower(trim($data['email'] ?? ''));
    $password = $data['password'] ?? '';
    $role     = in_array($data['role'] ?? '', ['admin','user']) ? $data['role'] : 'user';

    if (!$username || !$email || !$password) { echo json_encode(['error' => 'Tüm alanlar gerekli']); exit; }
    if (strlen($password) < 8) { echo json_encode(['error' => 'Şifre en az 8 karakter olmalı']); exit; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['error' => 'Geçersiz email']); exit; }

    $chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
    $chk->execute([$username, $email]);
    if ($chk->fetch()) { echo json_encode(['error' => 'Kullanıcı adı veya email zaten kullanımda']); exit; }

    $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?,?,?,?)")
        ->execute([$username, password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]), $email, $role]);

    echo json_encode(['success' => true]);
    exit;
}

// ── TOGGLE AKTİF ──
if ($method === 'POST' && $action === 'toggle_active') {
    $id   = (int)($data['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT id, active FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $u = $stmt->fetch();
    if (!$u) { echo json_encode(['error' => 'Bulunamadı']); exit; }
    $pdo->prepare("UPDATE users SET active = ? WHERE id = ?")->execute([$u['active'] ? 0 : 1, $id]);
    echo json_encode(['success' => true]);
    exit;
}

// ── ROL DEĞİŞTİR ──
if ($method === 'POST' && $action === 'change_role') {
    $id   = (int)($data['id'] ?? 0);
    $role = in_array($data['role'] ?? '', ['admin','user']) ? $data['role'] : 'user';
    $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$role, $id]);
    echo json_encode(['success' => true]);
    exit;
}

// ── ŞİFRE SIFIRLA ──
if ($method === 'POST' && $action === 'reset_password') {
    $id       = (int)($data['id'] ?? 0);
    $password = $data['password'] ?? '';
    if (strlen($password) < 8) { echo json_encode(['error' => 'Şifre en az 8 karakter']); exit; }
    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]), $id]);
    echo json_encode(['success' => true]);
    exit;
}

// ── SİL ──
if ($method === 'POST' && $action === 'delete') {
    $id = (int)($data['id'] ?? 0);
    if ($id === (int)currentUser()['id']) { echo json_encode(['error' => 'Kendinizi silemezsiniz']); exit; }
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Geçersiz istek']);
