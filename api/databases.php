<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/config.php';

header('Content-Type: application/json');
requireLogin();

$user    = currentUser();
$uid     = $user['id'];
$isAdmin = isAdmin();
$method  = $_SERVER['REQUEST_METHOD'];
$data    = json_decode(file_get_contents('php://input'), true) ?? [];
$action  = $data['action'] ?? $_GET['action'] ?? '';

function rootPdo(): PDO {
    return new PDO('mysql:host=' . DB_HOST, 'root', DB_ROOT_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

// ── LİSTELE ──
if ($method === 'GET' && $action === 'list') {
    $stmt = $isAdmin
        ? $pdo->query("SELECT d.*, u.username FROM databases d JOIN users u ON u.id = d.user_id ORDER BY d.created_at DESC")
        : $pdo->prepare("SELECT * FROM databases WHERE user_id = ? ORDER BY created_at DESC");
    $isAdmin ? $stmt->execute() : $stmt->execute([$uid]);
    echo json_encode(['success' => true, 'databases' => $stmt->fetchAll()]);
    exit;
}

// ── OLUŞTUR ──
if ($method === 'POST' && $action === 'create') {
    $db_name = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($data['db_name'] ?? '')));
    $db_user = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($data['db_user'] ?? '')));
    $db_pass = $data['db_pass'] ?: bin2hex(random_bytes(8));

    if (!$db_name || !$db_user) { echo json_encode(['error' => 'DB adı ve kullanıcı gerekli']); exit; }
    if (strlen($db_name) > 64 || strlen($db_user) > 32) { echo json_encode(['error' => 'İsim çok uzun']); exit; }

    // Zaten var mı?
    $chk = $pdo->prepare("SELECT id FROM databases WHERE db_name = ? OR db_user = ? LIMIT 1");
    $chk->execute([$db_name, $db_user]);
    if ($chk->fetch()) { echo json_encode(['error' => 'Bu DB adı veya kullanıcı zaten kullanımda']); exit; }

    try {
        $r = rootPdo();
        $r->exec("CREATE DATABASE `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $r->exec("CREATE USER '{$db_user}'@'localhost' IDENTIFIED BY " . $r->quote($db_pass));
        $r->exec("GRANT ALL PRIVILEGES ON `{$db_name}`.* TO '{$db_user}'@'localhost'");
        $r->exec("FLUSH PRIVILEGES");
    } catch (PDOException $e) {
        echo json_encode(['error' => 'DB oluşturulamadı: ' . $e->getMessage()]); exit;
    }

    $pdo->prepare("INSERT INTO databases (user_id, db_name, db_user, db_pass) VALUES (?,?,?,?)")
        ->execute([$uid, $db_name, $db_user, password_hash($db_pass, PASSWORD_BCRYPT)]);

    echo json_encode(['success' => true, 'db_name' => $db_name, 'db_user' => $db_user, 'db_pass' => $db_pass]);
    exit;
}

// ── SİL ──
if ($method === 'POST' && $action === 'delete') {
    $id   = (int)($data['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM databases WHERE id = ? " . ($isAdmin ? '' : 'AND user_id = ?') . " LIMIT 1");
    $isAdmin ? $stmt->execute([$id]) : $stmt->execute([$id, $uid]);
    $db = $stmt->fetch();
    if (!$db) { echo json_encode(['error' => 'Bulunamadı']); exit; }

    try {
        $r = rootPdo();
        $r->exec("DROP DATABASE IF EXISTS `{$db['db_name']}`");
        $r->exec("DROP USER IF EXISTS '{$db['db_user']}'@'localhost'");
        $r->exec("FLUSH PRIVILEGES");
    } catch (PDOException $e) {
        // Sessizce devam et
    }

    $pdo->prepare("DELETE FROM databases WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Geçersiz istek']);
