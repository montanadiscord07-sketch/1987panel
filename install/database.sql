-- ══════════════════════════════════════════════════════
--  1987 Panel — Veritabanı
-- ══════════════════════════════════════════════════════
CREATE DATABASE IF NOT EXISTS `1987panel`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `1987panel`;

-- ── KULLANICILAR ──
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(64)          NOT NULL UNIQUE,
    password   VARCHAR(255)         NOT NULL,
    email      VARCHAR(255)         NOT NULL UNIQUE,
    role       ENUM('admin','user') NOT NULL DEFAULT 'user',
    active     TINYINT(1)           NOT NULL DEFAULT 1,
    last_login DATETIME             NULL,
    created_at DATETIME             NOT NULL DEFAULT NOW()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── OTURUMLAR ──
CREATE TABLE IF NOT EXISTS sessions (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT         NOT NULL,
    token      VARCHAR(64) NOT NULL UNIQUE,
    ip         VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    expires_at DATETIME    NOT NULL,
    created_at DATETIME    NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── DOMAİNLER ──
CREATE TABLE IF NOT EXISTS domains (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    domain     VARCHAR(255) NOT NULL UNIQUE,
    doc_root   VARCHAR(500) NOT NULL,
    php_ver    VARCHAR(10)  NOT NULL DEFAULT '8.2',
    ssl        TINYINT(1)   NOT NULL DEFAULT 0,
    active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── VERİTABANLARI ──
CREATE TABLE IF NOT EXISTS databases (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    db_name    VARCHAR(64)  NOT NULL UNIQUE,
    db_user    VARCHAR(64)  NOT NULL UNIQUE,
    db_pass    TEXT         NOT NULL,
    db_host    VARCHAR(64)  NOT NULL DEFAULT 'localhost',
    created_at DATETIME     NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── MAİL DOMAINLER ──
CREATE TABLE IF NOT EXISTS mail_domains (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    domain     VARCHAR(255) NOT NULL UNIQUE,
    active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── MAİL HESAPLARI ──
CREATE TABLE IF NOT EXISTS mail_accounts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    domain_id  INT          NOT NULL,
    username   VARCHAR(64)  NOT NULL,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   TEXT         NOT NULL,
    quota_mb   INT          NOT NULL DEFAULT 1024,
    active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id)   REFERENCES users(id)        ON DELETE CASCADE,
    FOREIGN KEY (domain_id) REFERENCES mail_domains(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── DNS ZONE'LAR ──
CREATE TABLE IF NOT EXISTS dns_zones (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    domain     VARCHAR(255) NOT NULL UNIQUE,
    active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── DNS KAYITLARI ──
CREATE TABLE IF NOT EXISTS dns_records (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    zone_id  INT          NOT NULL,
    type     ENUM('A','AAAA','CNAME','MX','TXT','NS','SRV') NOT NULL,
    name     VARCHAR(255) NOT NULL,
    value    TEXT         NOT NULL,
    ttl      INT          NOT NULL DEFAULT 3600,
    priority INT          NOT NULL DEFAULT 0,
    FOREIGN KEY (zone_id) REFERENCES dns_zones(id) ON DELETE CASCADE,
    INDEX idx_zone_id (zone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── SSL SERTİFİKALARI ──
CREATE TABLE IF NOT EXISTS ssl_certs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    domain     VARCHAR(255) NOT NULL UNIQUE,
    issued_at  DATETIME     NULL,
    expires_at DATETIME     NULL,
    auto_renew TINYINT(1)   NOT NULL DEFAULT 1,
    status     ENUM('active','expired','pending','failed') NOT NULL DEFAULT 'pending',
    created_at DATETIME     NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── VARSAYILAN ADMIN ──
INSERT INTO users (username, password, email, role)
VALUES (
    'admin',
    '$2y$12$placeholder_will_be_set_by_setup_script',
    'admin@localhost',
    'admin'
) ON DUPLICATE KEY UPDATE username = username;
