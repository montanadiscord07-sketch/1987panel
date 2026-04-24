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

// ── ZONE LİSTELE ──
if ($method === 'GET' && $action === 'zones') {
    $stmt = $isAdmin
        ? $pdo->query("SELECT dz.*, u.username, (SELECT COUNT(*) FROM dns_records dr WHERE dr.zone_id = dz.id) as record_count FROM dns_zones dz JOIN users u ON u.id = dz.user_id ORDER BY dz.created_at DESC")
        : $pdo->prepare("SELECT *, (SELECT COUNT(*) FROM dns_records dr WHERE dr.zone_id = dns_zones.id) as record_count FROM dns_zones WHERE user_id = ? ORDER BY created_at DESC");
    $isAdmin ? $stmt->execute() : $stmt->execute([$uid]);
    echo json_encode(['success' => true, 'zones' => $stmt->fetchAll()]);
    exit;
}

// ── ZONE EKLE ──
if ($method === 'POST' && $action === 'add_zone') {
    $domain = strtolower(trim($data['domain'] ?? ''));
    if (!$domain || !preg_match('/^[a-z0-9][a-z0-9\-\.]+\.[a-z]{2,}$/', $domain)) {
        echo json_encode(['error' => 'Geçersiz domain']); exit;
    }
    $chk = $pdo->prepare("SELECT id FROM dns_zones WHERE domain = ? LIMIT 1");
    $chk->execute([$domain]);
    if ($chk->fetch()) { echo json_encode(['error' => 'Bu zone zaten var']); exit; }

    $pdo->prepare("INSERT INTO dns_zones (user_id, domain) VALUES (?,?)")->execute([$uid, $domain]);
    $zoneId = $pdo->lastInsertId();

    // Varsayılan kayıtlar
    $serverIp = trim(shell_exec("hostname -I | awk '{print $1}'") ?? '127.0.0.1');
    $defaults = [
        ['A',   '@',    $serverIp, 3600, 0],
        ['A',   'www',  $serverIp, 3600, 0],
        ['MX',  '@',    'mail.'.$domain.'.', 3600, 10],
        ['A',   'mail', $serverIp, 3600, 0],
        ['TXT', '@',    'v=spf1 a mx ~all', 3600, 0],
    ];
    $ins = $pdo->prepare("INSERT INTO dns_records (zone_id, type, name, value, ttl, priority) VALUES (?,?,?,?,?,?)");
    foreach ($defaults as $r) $ins->execute(array_merge([$zoneId], $r));

    _writeZoneFile($zoneId, $domain);
    echo json_encode(['success' => true]);
    exit;
}

// ── ZONE SİL ──
if ($method === 'POST' && $action === 'delete_zone') {
    $id   = (int)($data['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM dns_zones WHERE id = ? " . ($isAdmin ? '' : 'AND user_id = ?') . " LIMIT 1");
    $isAdmin ? $stmt->execute([$id]) : $stmt->execute([$id, $uid]);
    $zone = $stmt->fetch();
    if (!$zone) { echo json_encode(['error' => 'Bulunamadı']); exit; }

    shellRun('sudo rm -f ' . escapeshellarg(BIND_ZONES . '/' . $zone['domain'] . '.zone'));
    $pdo->prepare("DELETE FROM dns_zones WHERE id = ?")->execute([$id]);
    _reloadBind();
    echo json_encode(['success' => true]);
    exit;
}

// ── KAYITLARI LİSTELE ──
if ($method === 'GET' && $action === 'records') {
    $zoneId = (int)($_GET['zone_id'] ?? 0);
    $stmt   = $pdo->prepare("SELECT * FROM dns_records WHERE zone_id = ? ORDER BY type, name");
    $stmt->execute([$zoneId]);
    echo json_encode(['success' => true, 'records' => $stmt->fetchAll()]);
    exit;
}

// ── KAYIT EKLE ──
if ($method === 'POST' && $action === 'add_record') {
    $zoneId   = (int)($data['zone_id'] ?? 0);
    $type     = strtoupper($data['type'] ?? '');
    $name     = trim($data['name'] ?? '@');
    $value    = trim($data['value'] ?? '');
    $ttl      = max(60, (int)($data['ttl'] ?? 3600));
    $priority = (int)($data['priority'] ?? 0);

    $allowed = ['A','AAAA','CNAME','MX','TXT','NS','SRV'];
    if (!in_array($type, $allowed) || !$value) { echo json_encode(['error' => 'Geçersiz kayıt']); exit; }

    // Zone bu kullanıcıya ait mi?
    $stmt = $pdo->prepare("SELECT * FROM dns_zones WHERE id = ? " . ($isAdmin ? '' : 'AND user_id = ?') . " LIMIT 1");
    $isAdmin ? $stmt->execute([$zoneId]) : $stmt->execute([$zoneId, $uid]);
    $zone = $stmt->fetch();
    if (!$zone) { echo json_encode(['error' => 'Zone bulunamadı']); exit; }

    $pdo->prepare("INSERT INTO dns_records (zone_id, type, name, value, ttl, priority) VALUES (?,?,?,?,?,?)")
        ->execute([$zoneId, $type, $name, $value, $ttl, $priority]);

    _writeZoneFile($zoneId, $zone['domain']);
    echo json_encode(['success' => true]);
    exit;
}

// ── KAYIT SİL ──
if ($method === 'POST' && $action === 'delete_record') {
    $id     = (int)($data['id'] ?? 0);
    $record = $pdo->prepare("SELECT dr.*, dz.domain, dz.user_id FROM dns_records dr JOIN dns_zones dz ON dz.id = dr.zone_id WHERE dr.id = ? LIMIT 1");
    $record->execute([$id]);
    $r = $record->fetch();
    if (!$r || (!$isAdmin && $r['user_id'] != $uid)) { echo json_encode(['error' => 'Bulunamadı']); exit; }

    $pdo->prepare("DELETE FROM dns_records WHERE id = ?")->execute([$id]);
    _writeZoneFile($r['zone_id'], $r['domain']);
    echo json_encode(['success' => true]);
    exit;
}

function _writeZoneFile(int $zoneId, string $domain): void {
    global $pdo;
    $records = $pdo->prepare("SELECT * FROM dns_records WHERE zone_id = ? ORDER BY type, name");
    $records->execute([$zoneId]);
    $recs = $records->fetchAll();

    $serial = date('Ymd') . '01';
    $zone   = "\$ORIGIN {$domain}.\n";
    $zone  .= "\$TTL 3600\n";
    $zone  .= "@ IN SOA ns1.{$domain}. admin.{$domain}. (\n";
    $zone  .= "    {$serial} ; Serial\n";
    $zone  .= "    3600       ; Refresh\n";
    $zone  .= "    900        ; Retry\n";
    $zone  .= "    604800     ; Expire\n";
    $zone  .= "    300 )      ; Minimum TTL\n\n";
    $zone  .= "@ IN NS ns1.{$domain}.\n\n";

    foreach ($recs as $r) {
        $name = $r['name'] === '@' ? '@' : $r['name'];
        if ($r['type'] === 'MX') {
            $zone .= "{$name} {$r['ttl']} IN {$r['type']} {$r['priority']} {$r['value']}\n";
        } else {
            $zone .= "{$name} {$r['ttl']} IN {$r['type']} {$r['value']}\n";
        }
    }

    $file = BIND_ZONES . '/' . $domain . '.zone';
    file_put_contents($file, $zone);
    _reloadBind();
}

function _reloadBind(): void {
    shellRun('sudo systemctl reload bind9');
}

http_response_code(400);
echo json_encode(['error' => 'Geçersiz istek']);
