<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');
requireLogin();

$user    = currentUser();
$uid     = $user['id'];
$isAdmin = isAdmin();

// Kullanıcının erişebileceği kök dizin
$baseDir     = '/var/www';
$userDomains = [];
if (!$isAdmin) {
    $stmt = $pdo->prepare("SELECT domain FROM domains WHERE user_id = ?");
    $stmt->execute([$uid]);
    $userDomains = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
$method = $_SERVER['REQUEST_METHOD'];
$data   = json_decode(file_get_contents('php://input'), true) ?? [];
$action  = $data['action'] ?? $_GET['action'] ?? '';

function safePath(string $base, string $path): string|false {
    global $isAdmin, $userDomains;
    $real = realpath($base . '/' . ltrim($path, '/'));
    if (!$real || strpos($real, realpath($base)) !== 0) return false;
    // Normal kullanıcı sadece kendi domain klasörlerine erişebilir
    if (!$isAdmin) {
        $allowed = false;
        foreach ($userDomains as $domain) {
            if (strpos($real, realpath($base . '/' . $domain)) === 0) {
                $allowed = true;
                break;
            }
        }
        if (!$allowed) return false;
    }
    return $real;
}

// ── LİSTELE ──
if ($method === 'GET' && $action === 'list') {
    $path = $_GET['path'] ?? '/';
    $dir  = safePath($baseDir, $path);
    if (!$dir || !is_dir($dir)) { echo json_encode(['error' => 'Geçersiz dizin']); exit; }

    $items = [];
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $full = $dir . '/' . $item;
        $items[] = [
            'name'     => $item,
            'type'     => is_dir($full) ? 'dir' : 'file',
            'size'     => is_file($full) ? filesize($full) : 0,
            'modified' => date('Y-m-d H:i', filemtime($full)),
            'ext'      => is_file($full) ? strtolower(pathinfo($item, PATHINFO_EXTENSION)) : '',
        ];
    }
    usort($items, fn($a,$b) => $a['type'] === $b['type'] ? strcmp($a['name'],$b['name']) : ($a['type'] === 'dir' ? -1 : 1));
    echo json_encode(['success' => true, 'items' => $items, 'path' => str_replace(realpath($baseDir), '', $dir) ?: '/']);
    exit;
}

// ── DOSYA İNDİR ──
if ($method === 'GET' && $action === 'download') {
    $path = $_GET['path'] ?? '';
    $file = safePath($baseDir, $path);
    if (!$file || !is_file($file)) { echo json_encode(['error' => 'Dosya bulunamadı']); exit; }
    $name = basename($file);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $name . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

// ── DOSYA OKU ──
if ($method === 'GET' && $action === 'read') {
    $path = $_GET['path'] ?? '';
    $file = safePath($baseDir, $path);
    if (!$file || !is_file($file)) { echo json_encode(['error' => 'Dosya bulunamadı']); exit; }
    if (filesize($file) > 512 * 1024) { echo json_encode(['error' => 'Dosya çok büyük (max 512KB)']); exit; }
    
    // Güvenlik: Hassas dosyaları okumayı engelle
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $blocked = ['sh', 'sql', 'env', 'key', 'pem', 'log'];
    if (in_array($ext, $blocked)) {
        echo json_encode(['error' => 'Bu dosya türü okunamaz']); exit;
    }
    
    echo json_encode(['success' => true, 'content' => file_get_contents($file)]);
    exit;
}

// ── DOSYA KAYDET ──
if ($method === 'POST' && $action === 'save') {
    $path    = $data['path'] ?? '';
    $content = $data['content'] ?? '';
    $file    = safePath($baseDir, $path);
    if (!$file) { echo json_encode(['error' => 'Geçersiz yol']); exit; }
    file_put_contents($file, $content);
    echo json_encode(['success' => true]);
    exit;
}

// ── KLASÖR OLUŞTUR ──
if ($method === 'POST' && $action === 'mkdir') {
    $path = $data['path'] ?? '';
    $name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $data['name'] ?? '');
    $dir  = safePath($baseDir, $path);
    if (!$dir || !$name) { echo json_encode(['error' => 'Geçersiz']); exit; }
    mkdir($dir . '/' . $name, 0755, true);
    echo json_encode(['success' => true]);
    exit;
}

// ── DOSYA SİL ──
if ($method === 'POST' && $action === 'delete') {
    $path = $data['path'] ?? '';
    $full = safePath($baseDir, $path);
    if (!$full) { echo json_encode(['error' => 'Geçersiz yol']); exit; }
    if (is_dir($full)) {
        exec('rm -rf ' . escapeshellarg($full));
    } else {
        unlink($full);
    }
    echo json_encode(['success' => true]);
    exit;
}

// ── DOSYA YÜKLE ──
if ($method === 'POST' && $action === 'upload') {
    $path = $_POST['path'] ?? '/';
    $dir  = safePath($baseDir, $path);
    if (!$dir || !is_dir($dir)) { echo json_encode(['error' => 'Geçersiz dizin']); exit; }

    $uploaded = [];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'text/plain', 'text/html', 'text/css', 'application/javascript', 'application/json', 'application/pdf', 'application/zip'];
    
    foreach ($_FILES['files']['name'] as $i => $name) {
        $name = basename($name);
        $dest = $dir . '/' . $name;
        
        // Dosya türü kontrolü
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['files']['tmp_name'][$i]);
        finfo_close($finfo);
        
        // Güvenlik: Tehlikeli dosya türlerini engelle
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $blocked_ext = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'sh', 'exe', 'bat', 'cmd'];
        
        if (in_array($ext, $blocked_ext)) {
            continue; // Tehlikeli dosyayı atla
        }
        
        if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $dest)) {
            $uploaded[] = $name;
        }
    }
    echo json_encode(['success' => true, 'uploaded' => $uploaded]);
    exit;
}

// ── YENİ DOSYA ──
if ($method === 'POST' && $action === 'touch') {
    $path = $data['path'] ?? '';
    $name = $data['name'] ?? '';
    $dir  = safePath($baseDir, $path);
    if (!$dir || !$name) { echo json_encode(['error' => 'Geçersiz']); exit; }
    touch($dir . '/' . basename($name));
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Geçersiz istek']);
