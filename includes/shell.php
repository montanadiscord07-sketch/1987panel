<?php
// Sistem komutlarını güvenli çalıştırma

function shellRun(string $cmd): array {
    $output   = [];
    $exitCode = 0;
    
    // Komut güvenlik kontrolü
    $dangerous = ['rm -rf /', 'dd if=', 'mkfs', ':(){:|:&};:', 'fork bomb'];
    foreach ($dangerous as $pattern) {
        if (stripos($cmd, $pattern) !== false) {
            return ['success' => false, 'output' => 'Tehlikeli komut engellendi', 'code' => 1];
        }
    }
    
    exec($cmd . ' 2>&1', $output, $exitCode);
    return [
        'success' => $exitCode === 0,
        'output'  => implode("\n", $output),
        'code'    => $exitCode,
    ];
}

function nginxReload(): array {
    return shellRun('sudo systemctl reload nginx');
}

function nginxTest(): array {
    return shellRun('sudo nginx -t');
}

function bindReload(): array {
    return shellRun('sudo systemctl reload bind9');
}

function certbotIssue(string $domain, string $email): array {
    $domain = escapeshellarg($domain);
    $email  = escapeshellarg($email);
    return shellRun("sudo certbot --nginx -d $domain --email $email --agree-tos --non-interactive");
}

function certbotRevoke(string $domain): array {
    $domain = escapeshellarg($domain);
    return shellRun("sudo certbot delete --cert-name $domain --non-interactive");
}

function createWebDir(string $domain): array {
    $path = escapeshellarg(WEB_ROOT . '/' . $domain . '/public_html');
    shellRun("sudo mkdir -p $path");
    return shellRun("sudo chown -R www-data:www-data " . escapeshellarg(WEB_ROOT . '/' . $domain));
}

function removeWebDir(string $domain): array {
    $path = escapeshellarg(WEB_ROOT . '/' . $domain);
    return shellRun("sudo rm -rf $path");
}
