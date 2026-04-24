#!/bin/bash
# ══════════════════════════════════════════════════════
#  1987 Panel — Sunucu Kurulum Scripti
#  Ubuntu 22.04 / 24.04
# ══════════════════════════════════════════════════════

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log()  { echo -e "${GREEN}[✓]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }
err()  { echo -e "${RED}[✗]${NC} $1"; exit 1; }
info() { echo -e "${BLUE}[i]${NC} $1"; }

# Root kontrolü
[ "$EUID" -ne 0 ] && err "Root olarak çalıştır: sudo bash setup.sh"

# Ubuntu versiyon kontrolü
if [ -f /etc/os-release ]; then
    . /etc/os-release
    if [[ "$ID" != "ubuntu" ]]; then
        err "Bu script sadece Ubuntu için tasarlanmıştır"
    fi
    if [[ "$VERSION_ID" != "22.04" && "$VERSION_ID" != "24.04" ]]; then
        warn "Ubuntu 22.04 veya 24.04 önerilir. Mevcut: $VERSION_ID"
        read -p "Devam etmek istiyor musunuz? (e/h): " confirm
        [[ "$confirm" != "e" ]] && exit 0
    fi
fi

clear
echo ""
echo "  ╔═══════════════════════════════════════╗"
echo "  ║     1987 Panel — Kurulum Scripti     ║"
echo "  ║         Ubuntu 22.04 / 24.04         ║"
echo "  ╚═══════════════════════════════════════╝"
echo ""
info "Kurulum yaklaşık 10-15 dakika sürecek..."
echo ""

# ── 1. SİSTEM GÜNCELLEMESİ ──
log "Sistem güncelleniyor..."
apt update -qq && apt upgrade -y -qq

# ── 2. TEMEL PAKETLER ──
log "Temel paketler kuruluyor..."
apt install -y -qq curl wget unzip git software-properties-common

# ── 3. NGİNX ──
log "Nginx kuruluyor..."
apt install -y -qq nginx
systemctl enable nginx
systemctl start nginx

# ── 4. PHP 8.2 + DİĞER SÜRÜMLER ──
log "PHP kuruluyor..."
add-apt-repository -y ppa:ondrej/php > /dev/null 2>&1
apt update -qq
apt install -y -qq php8.2-fpm php8.2-cli php8.2-mysql php8.2-curl \
    php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-gd
apt install -y -qq php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl \
    php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-gd
apt install -y -qq php8.1-fpm php8.1-cli php8.1-mysql php8.1-curl \
    php8.1-mbstring php8.1-xml php8.1-zip php8.1-bcmath php8.1-gd
apt install -y -qq php8.0-fpm php8.0-cli php8.0-mysql php8.0-curl \
    php8.0-mbstring php8.0-xml php8.0-zip php8.0-bcmath php8.0-gd
systemctl enable php8.2-fpm php8.3-fpm php8.1-fpm php8.0-fpm
systemctl start  php8.2-fpm php8.3-fpm php8.1-fpm php8.0-fpm

# ── 5. MARİADB ──
log "MariaDB kuruluyor..."
apt install -y -qq mariadb-server mariadb-client
systemctl enable mariadb
systemctl start mariadb

# ── 6. POSTFIX + DOVECOT ──
log "Mail servisleri kuruluyor..."
DEBIAN_FRONTEND=noninteractive apt install -y -qq postfix dovecot-core \
    dovecot-imapd dovecot-pop3d dovecot-lmtpd

# ── 7. BIND9 ──
log "DNS servisi kuruluyor..."
apt install -y -qq bind9 bind9utils bind9-doc

# ── 8. CERTBOT ──
log "Certbot kuruluyor..."
apt install -y -qq certbot python3-certbot-nginx

# ── 9. COMPOSER ──
log "Composer kuruluyor..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer > /dev/null 2>&1

# ── 9.5. PHPMYADMIN ──
log "phpMyAdmin kuruluyor..."
DEBIAN_FRONTEND=noninteractive apt install -y -qq phpmyadmin

# ── 10. PYTHON + PLAYWRIGHT ──
log "Python paketleri kuruluyor..."
apt install -y -qq python3-pip
pip3 install -q playwright playwright-stealth mysql-connector-python
/root/.local/bin/playwright install chromium > /dev/null 2>&1
/root/.local/bin/playwright install-deps chromium > /dev/null 2>&1

# ── 11. PANEL DİZİNİ ──
log "Panel dizini hazırlanıyor..."
PANEL_DIR="/opt/1987panel"
WEB_DIR="/var/www"

mkdir -p $PANEL_DIR
mkdir -p $WEB_DIR
mkdir -p /etc/bind/zones

# ── 12. VERİTABANI KURULUMU ──
log "Veritabanı oluşturuluyor..."

# Güvenli şifre girişi
while true; do
    read -sp "  Panel DB şifresi girin (min 12 karakter): " DB_PASS
    echo ""
    if [ ${#DB_PASS} -lt 12 ]; then
        warn "Şifre en az 12 karakter olmalı!"
        continue
    fi
    read -sp "  Şifreyi tekrar girin: " DB_PASS_CONFIRM
    echo ""
    if [ "$DB_PASS" != "$DB_PASS_CONFIRM" ]; then
        warn "Şifreler eşleşmiyor!"
        continue
    fi
    break
done

# MariaDB root şifresini al
read -sp "  MariaDB root şifresi: " MYSQL_ROOT_PASS
echo ""

mysql -u root -p"$MYSQL_ROOT_PASS" -e "CREATE DATABASE IF NOT EXISTS \`1987panel\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || err "MariaDB bağlantı hatası!"
mysql -u root -p"$MYSQL_ROOT_PASS" -e "CREATE USER IF NOT EXISTS 'panel_user'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -u root -p"$MYSQL_ROOT_PASS" -e "GRANT ALL PRIVILEGES ON \`1987panel\`.* TO 'panel_user'@'localhost';"
mysql -u root -p"$MYSQL_ROOT_PASS" -e "FLUSH PRIVILEGES;"
mysql -u root -p"$MYSQL_ROOT_PASS" 1987panel < $(dirname "$0")/database.sql

# ── 13. ADMIN ŞİFRESİ ──
log "Admin hesabı oluşturuluyor..."

while true; do
    read -sp "  Admin şifresi girin (min 12 karakter): " ADMIN_PASS
    echo ""
    if [ ${#ADMIN_PASS} -lt 12 ]; then
        warn "Şifre en az 12 karakter olmalı!"
        continue
    fi
    read -sp "  Şifreyi tekrar girin: " ADMIN_PASS_CONFIRM
    echo ""
    if [ "$ADMIN_PASS" != "$ADMIN_PASS_CONFIRM" ]; then
        warn "Şifreler eşleşmiyor!"
        continue
    fi
    break
done

HASHED=$(php -r "echo password_hash('$ADMIN_PASS', PASSWORD_BCRYPT, ['cost'=>12]);")
mysql -u root -p"$MYSQL_ROOT_PASS" 1987panel -e "UPDATE users SET password='$HASHED', email='admin@localhost' WHERE username='admin';"

# ── 14. CONFIG GÜNCELLE ──
log "Config dosyası güncelleniyor..."
CONFIG_FILE="$PANEL_DIR/includes/config.php"
if [ -f "$CONFIG_FILE" ]; then
    # DB şifresi
    sed -i "s/define('DB_PASS',      'CHANGE_ME')/define('DB_PASS',      '$DB_PASS')/" $CONFIG_FILE
    # Root şifresi
    sed -i "s/define('DB_ROOT_PASS', '')/define('DB_ROOT_PASS', '$MYSQL_ROOT_PASS')/" $CONFIG_FILE
    # Secret key
    SECRET=$(openssl rand -hex 16)
    sed -i "s/CHANGE_ME_32_CHAR_SECRET_KEY_HERE/$SECRET/" $CONFIG_FILE
    # İzinleri ayarla
    chmod 600 $CONFIG_FILE
    chown www-data:www-data $CONFIG_FILE
fi
# ── 15. NGİNX PANEL CONFIG ──
log "Nginx panel config oluşturuluyor..."
cat > /etc/nginx/sites-available/1987panel << EOF
server {
    listen 8080;
    server_name _;
    root $PANEL_DIR;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location /phpmyadmin {
        root /usr/share/;
        index index.php;
        location ~ ^/phpmyadmin/(.+\.php)$ {
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME /usr/share/\$fastcgi_script_name;
            include fastcgi_params;
        }
        location ~* ^/phpmyadmin/(.+\.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt))$ {
            root /usr/share/;
        }
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. { deny all; }
    location ~* \.(sql|sh|env)$ { deny all; }
}
EOF

ln -sf /etc/nginx/sites-available/1987panel /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx || err "Nginx config hatası!"

# ── 15.5. BIND9 CONFIG ──
log "BIND9 konfigürasyonu yapılıyor..."
mkdir -p /etc/bind/zones
cat >> /etc/bind/named.conf.local << 'BINDEOF'
// 1987 Panel zones
include "/etc/bind/zones/*.conf";
BINDEOF
systemctl restart bind9

# ── 15.6. POSTFIX CONFIG ──
log "Postfix konfigürasyonu yapılıyor..."
touch /etc/postfix/virtual_domains
touch /etc/postfix/virtual_mailbox
postconf -e "virtual_mailbox_domains = hash:/etc/postfix/virtual_domains"
postconf -e "virtual_mailbox_maps = hash:/etc/postfix/virtual_mailbox"
postconf -e "virtual_mailbox_base = /var/mail/vhosts"
postconf -e "virtual_uid_maps = static:5000"
postconf -e "virtual_gid_maps = static:5000"
mkdir -p /var/mail/vhosts
groupadd -g 5000 vmail 2>/dev/null || true
useradd -g vmail -u 5000 vmail -d /var/mail/vhosts -s /usr/sbin/nologin 2>/dev/null || true
chown -R vmail:vmail /var/mail/vhosts
systemctl restart postfix

# ── 16. SUDO YETKİLERİ ──
log "Sudo yetkileri ayarlanıyor..."
cat > /etc/sudoers.d/1987panel << EOF
www-data ALL=(ALL) NOPASSWD: /usr/sbin/nginx, /bin/systemctl reload nginx, /bin/systemctl restart nginx
www-data ALL=(ALL) NOPASSWD: /usr/sbin/certbot
www-data ALL=(ALL) NOPASSWD: /usr/sbin/named-checkzone, /bin/systemctl reload bind9, /bin/systemctl restart bind9
www-data ALL=(ALL) NOPASSWD: /usr/sbin/postmap, /bin/systemctl reload postfix, /bin/systemctl restart postfix
www-data ALL=(ALL) NOPASSWD: /bin/mkdir, /bin/rm, /bin/chown, /bin/chmod, /bin/ln
www-data ALL=(ALL) NOPASSWD: /usr/bin/tee
EOF
chmod 440 /etc/sudoers.d/1987panel

# ── 17. DOSYA İZİNLERİ ──
log "Dosya izinleri ayarlanıyor..."
chown -R www-data:www-data $PANEL_DIR
chmod -R 755 $PANEL_DIR
chmod 600 $PANEL_DIR/includes/config.php

# ── 18. KURULUM DİZİNİNİ GÜVENLİ HALE GETİR ──
log "Kurulum dizini güvenli hale getiriliyor..."
chmod 000 $PANEL_DIR/install
warn "Kurulum tamamlandıktan sonra install dizinini silin: rm -rf $PANEL_DIR/install"

# ── 19. FIREWALL AYARLARI ──
log "Firewall yapılandırılıyor..."
if command -v ufw &> /dev/null; then
    ufw --force enable
    ufw allow 22/tcp
    ufw allow 80/tcp
    ufw allow 443/tcp
    ufw allow 8080/tcp
    ufw allow 25/tcp
    ufw allow 587/tcp
    ufw allow 993/tcp
    ufw allow 995/tcp
    ufw allow 53
    log "Firewall aktif edildi"
else
    warn "UFW bulunamadı, manuel olarak firewall ayarlayın"
fi

# ── 20. FAIL2BAN ──
log "Fail2ban kuruluyor..."
apt install -y -qq fail2ban
systemctl enable fail2ban
systemctl start fail2ban

# ── 21. ÖZET ──
clear
echo ""
echo "  ╔═══════════════════════════════════════════════════════╗"
echo "  ║                                                       ║"
echo "  ║          ✓ KURULUM BAŞARIYLA TAMAMLANDI!            ║"
echo "  ║                                                       ║"
echo "  ╚═══════════════════════════════════════════════════════╝"
echo ""
echo "  📍 Panel Bilgileri:"
echo "  ─────────────────────────────────────────────────────"
echo "  🌐 Panel Adresi    : http://$(hostname -I | awk '{print $1}'):8080"
echo "  👤 Kullanıcı Adı   : admin"
echo "  🔑 Şifre           : (belirlediğiniz şifre)"
echo "  📊 phpMyAdmin      : http://$(hostname -I | awk '{print $1}'):8080/phpmyadmin"
echo ""
echo "  ⚠️  ÖNEMLİ GÜVENLİK ADIMLARI:"
echo "  ─────────────────────────────────────────────────────"
echo "  1. Install dizinini silin:"
echo "     rm -rf $PANEL_DIR/install"
echo ""
echo "  2. SSH portunu değiştirin (opsiyonel):"
echo "     nano /etc/ssh/sshd_config"
echo ""
echo "  3. Düzenli yedek alın:"
echo "     mysqldump -u root -p 1987panel > backup.sql"
echo ""
echo "  4. SSL sertifikası ekleyin (panelden)"
echo ""
echo "  📚 Dokümantasyon:"
echo "  ─────────────────────────────────────────────────────"
echo "  • Kullanım Kılavuzu : $PANEL_DIR/README.md"
echo "  • Güvenlik Kılavuzu : $PANEL_DIR/SECURITY.md"
echo "  • Kurulum Detayları : $PANEL_DIR/INSTALL.md"
echo ""
echo "  🚀 İyi kullanımlar!"
echo ""
