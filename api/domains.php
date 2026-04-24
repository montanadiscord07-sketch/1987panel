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
    $limit = min((int)($_GET['limit'] ?? 100), 500);
    $stmt  = $isAdmin
        ? $pdo->prepare("SELECT d.*, u.username FROM domains d JOIN users u ON u.id = d.user_id ORDER BY d.created_at DESC LIMIT ?")
        : $pdo->prepare("SELECT * FROM domains WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");

    $isAdmin ? $stmt->execute([$limit]) : $stmt->execute([$uid, $limit]);
    echo json_encode(['success' => true, 'domains' => $stmt->fetchAll()]);
    exit;
}

// ── EKLE ──
if ($method === 'POST' && $action === 'create') {
    $domain  = strtolower(trim($data['domain'] ?? ''));
    $php_ver = in_array($data['php_ver'] ?? '', ['8.0','8.1','8.2','8.3']) ? $data['php_ver'] : '8.2';

    if (!$domain || !preg_match('/^[a-z0-9][a-z0-9\-\.]+\.[a-z]{2,}$/', $domain)) {
        echo json_encode(['error' => 'Geçersiz domain adı']); exit;
    }

    // Zaten var mı?
    $chk = $pdo->prepare("SELECT id FROM domains WHERE domain = ? LIMIT 1");
    $chk->execute([$domain]);
    if ($chk->fetch()) { echo json_encode(['error' => 'Bu domain zaten ekli']); exit; }

    $docRoot = WEB_ROOT . '/' . $domain . '/public_html';

    // Web dizini oluştur
    $r = createWebDir($domain);
    if (!$r['success']) { echo json_encode(['error' => 'Dizin oluşturulamadı: ' . $r['output']]); exit; }

    // Nginx config oluştur
    $nginxConf = <<<CONF
server {
    listen 80;
    server_name {$domain} www.{$domain};
    root {$docRoot};
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php{$php_ver}-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. { deny all; }
}
CONF;

    $confFile = NGINX_SITES . '/' . $domain;
    file_put_contents($confFile, $nginxConf);
    shellRun('sudo ln -sf ' . escapeshellarg($confFile) . ' ' . escapeshellarg(NGINX_ENABLED . '/' . $domain));
    $reload = nginxReload();
    if (!$reload['success']) {
        shellRun('sudo rm -f ' . escapeshellarg($confFile));
        echo json_encode(['error' => 'Nginx yeniden yüklenemedi']); exit;
    }

    // DB'ye kaydet
    $pdo->prepare("INSERT INTO domains (user_id, domain, doc_root, php_ver) VALUES (?,?,?,?)")
        ->execute([$uid, $domain, $docRoot, $php_ver]);

    echo json_encode(['success' => true, 'domain' => $domain]);
    exit;
}

// ── SİL ──
if ($method === 'POST' && $action === 'delete') {
    $id = (int)($data['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM domains WHERE id = ? " . ($isAdmin ? '' : 'AND user_id = ?') . " LIMIT 1");
    $isAdmin ? $stmt->execute([$id]) : $stmt->execute([$id, $uid]);
    $domain = $stmt->fetch();
    if (!$domain) { echo json_encode(['error' => 'Domain bulunamadı']); exit; }

    // Nginx config sil
    shellRun('sudo rm -f ' . escapeshellarg(NGINX_SITES   . '/' . $domain['domain']));
    shellRun('sudo rm -f ' . escapeshellarg(NGINX_ENABLED . '/' . $domain['domain']));
    nginxReload();

    // Web dizini sil
    removeWebDir($domain['domain']);

    // DB'den sil
    $pdo->prepare("DELETE FROM domains WHERE id = ?")->execute([$id]);

    echo json_encode(['success' => true]);
    exit;
}

// ── TOGGLE ──
if ($method === 'POST' && $action === 'toggle') {
    $id = (int)($data['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM domains WHERE id = ? " . ($isAdmin ? '' : 'AND user_id = ?') . " LIMIT 1");
    $isAdmin ? $stmt->execute([$id]) : $stmt->execute([$id, $uid]);
    $domain = $stmt->fetch();
    if (!$domain) { echo json_encode(['error' => 'Domain bulunamadı']); exit; }

    $newActive = $domain['active'] ? 0 : 1;
    $confSrc   = NGINX_SITES . '/' . $domain['domain'];
    $confDst   = NGINX_ENABLED . '/' . $domain['domain'];

    if ($newActive) {
        shellRun('sudo ln -sf ' . escapeshellarg($confSrc) . ' ' . escapeshellarg($confDst));
    } else {
        shellRun('sudo rm -f ' . escapeshellarg($confDst));
    }
    nginxReload();

    $pdo->prepare("UPDATE domains SET active = ? WHERE id = ?")->execute([$newActive, $id]);
    echo json_encode(['success' => true, 'active' => $newActive]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Geçersiz istek']);
