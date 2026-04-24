<?php
require_once __DIR__ . '/db.php';

define('SESSION_COOKIE',   'p87_sid');
define('SESSION_LIFETIME', 86400);

function createSession(int $userId): void {
    global $pdo;
    $token     = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
    $ip        = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua        = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    $pdo->prepare("DELETE FROM sessions WHERE expires_at < NOW()")->execute();
    $pdo->prepare("INSERT INTO sessions (user_id, token, ip, user_agent, expires_at) VALUES (?,?,?,?,?)")
        ->execute([$userId, $token, $ip, $ua, $expiresAt]);

    setcookie(SESSION_COOKIE, $token, [
        'expires'  => time() + SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

$_currentUser        = null;
$_currentUserChecked = false;

function currentUser(): ?array {
    global $pdo, $_currentUser, $_currentUserChecked;
    if ($_currentUserChecked) return $_currentUser;
    $_currentUserChecked = true;

    $token = $_COOKIE[SESSION_COOKIE] ?? '';
    if (!$token) return null;

    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.email, u.role
        FROM sessions s
        JOIN users u ON u.id = s.user_id
        WHERE s.token = ? AND s.expires_at > NOW() AND u.active = 1
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $_currentUser = $stmt->fetch() ?: null;
    return $_currentUser;
}

function destroySession(): void {
    global $pdo;
    $token = $_COOKIE[SESSION_COOKIE] ?? '';
    if ($token) $pdo->prepare("DELETE FROM sessions WHERE token = ?")->execute([$token]);
    setcookie(SESSION_COOKIE, '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true]);
}

function isLoggedIn(): bool  { return currentUser() !== null; }
function isAdmin(): bool     { $u = currentUser(); return $u && $u['role'] === 'admin'; }

function requireLogin(): void {
    if (!isLoggedIn()) { header('Location: /1987panel/index.php'); exit; }
}
function requireAdmin(): void {
    if (!isAdmin()) { header('Location: /1987panel/index.php'); exit; }
}
