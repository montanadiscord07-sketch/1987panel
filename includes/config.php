<?php
// ── VERİTABANI ──
define('DB_HOST',      'localhost');
define('DB_NAME',      '1987panel');
define('DB_USER',      'panel_user');
define('DB_PASS',      'CHANGE_ME');
define('DB_ROOT_PASS', '');          // MariaDB root şifresi (setup sonrası doldur)
define('DB_CHARSET',   'utf8mb4');

// ── PANEL ──
define('PANEL_NAME',    '1987 Web Sağlayıcısı');
define('PANEL_VERSION', '1.0.0');
define('PANEL_PORT',    '8080');
define('PANEL_URL',     'http://localhost:' . PANEL_PORT);

// ── ŞİFRELEME ──
define('SECRET_KEY', 'CHANGE_ME_32_CHAR_SECRET_KEY_HERE');

// ── SİSTEM YOLLARI ──
define('NGINX_SITES',   '/etc/nginx/sites-available');
define('NGINX_ENABLED', '/etc/nginx/sites-enabled');
define('WEB_ROOT',      '/var/www');
define('BIND_ZONES',    '/etc/bind/zones');
define('MAIL_VHOSTS',   '/etc/postfix/virtual');
define('SSL_DIR',       '/etc/letsencrypt/live');
