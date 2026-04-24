# 1987 Panel - Ubuntu Kurulum Kılavuzu

## Sistem Gereksinimleri

- **İşletim Sistemi:** Ubuntu 22.04 LTS veya 24.04 LTS
- **RAM:** Minimum 2GB (Önerilen 4GB)
- **Disk:** Minimum 20GB boş alan
- **İşlemci:** 2 CPU Core
- **Root Erişimi:** Gerekli

## Hızlı Kurulum (Otomatik)

### 1. Sunucuya Bağlanın
```bash
ssh root@sunucu-ip
```

### 2. Sistemi Güncelleyin
```bash
apt update && apt upgrade -y
```

### 3. Panel Dosyalarını İndirin
```bash
# Git ile (önerilen)
cd /opt
git clone https://github.com/kullanici/1987panel.git

# Veya ZIP ile
cd /opt
wget https://github.com/kullanici/1987panel/archive/main.zip
unzip main.zip
mv 1987panel-main 1987panel
```

### 4. Kurulum Scriptini Çalıştırın
```bash
cd /opt/1987panel/install
chmod +x setup.sh
sudo bash setup.sh
```

Kurulum sırasında sizden istenecekler:
- **Panel DB Şifresi:** Veritabanı için güçlü bir şifre
- **Admin Şifresi:** Panel admin hesabı için güçlü bir şifre

### 5. Kurulum Tamamlandı!
```
Panel Adresi: http://SUNUCU-IP:8080
Kullanıcı Adı: admin
Şifre: (belirlediğiniz şifre)
```

---

## Manuel Kurulum (Adım Adım)

### 1. Temel Paketleri Kurun
```bash
apt update
apt install -y curl wget unzip git software-properties-common
```

### 2. Nginx Kurun
```bash
apt install -y nginx
systemctl enable nginx
systemctl start nginx
```

### 3. PHP Kurun (Çoklu Versiyon)
```bash
# PHP Repository ekle
add-apt-repository -y ppa:ondrej/php
apt update

# PHP 8.2 (Ana versiyon)
apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-curl \
    php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath php8.2-gd

# PHP 8.3
apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl \
    php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-gd

# PHP 8.1
apt install -y php8.1-fpm php8.1-cli php8.1-mysql php8.1-curl \
    php8.1-mbstring php8.1-xml php8.1-zip php8.1-bcmath php8.1-gd

# PHP 8.0
apt install -y php8.0-fpm php8.0-cli php8.0-mysql php8.0-curl \
    php8.0-mbstring php8.0-xml php8.0-zip php8.0-bcmath php8.0-gd

# Servisleri başlat
systemctl enable php8.2-fpm php8.3-fpm php8.1-fpm php8.0-fpm
systemctl start php8.2-fpm php8.3-fpm php8.1-fpm php8.0-fpm
```

### 4. MariaDB Kurun
```bash
apt install -y mariadb-server mariadb-client
systemctl enable mariadb
systemctl start mariadb

# Güvenlik ayarları
mysql_secure_installation
```

Sorulara cevaplar:
- Switch to unix_socket authentication? **N**
- Change the root password? **Y** (güçlü şifre belirleyin)
- Remove anonymous users? **Y**
- Disallow root login remotely? **Y**
- Remove test database? **Y**
- Reload privilege tables? **Y**

### 5. Veritabanı Oluşturun
```bash
# MariaDB'ye giriş
mysql -u root -p

# Veritabanı ve kullanıcı oluştur
CREATE DATABASE 1987panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'panel_user'@'localhost' IDENTIFIED BY 'GÜÇLÜ_ŞİFRE';
GRANT ALL PRIVILEGES ON 1987panel.* TO 'panel_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Veritabanı şemasını yükle
mysql -u root -p 1987panel < /opt/1987panel/install/database.sql
```

### 6. Admin Şifresi Belirleyin
```bash
# Şifre hash'i oluştur
php -r "echo password_hash('ADMIN_ŞİFRENİZ', PASSWORD_BCRYPT, ['cost'=>12]);"

# Çıkan hash'i kopyalayın ve veritabanına kaydedin
mysql -u root -p 1987panel

UPDATE users SET password='HASH_BURAYA' WHERE username='admin';
EXIT;
```

### 7. Config Dosyasını Düzenleyin
```bash
nano /opt/1987panel/includes/config.php
```

Düzenlenecek satırlar:
```php
define('DB_PASS',      'panel_user_şifresi');
define('DB_ROOT_PASS', 'mariadb_root_şifresi');
define('SECRET_KEY',   'BURAYA_32_KARAKTER_RANDOM_KEY');
```

Secret key oluşturmak için:
```bash
openssl rand -hex 16
```

### 8. Mail Servisleri Kurun
```bash
# Postfix ve Dovecot
DEBIAN_FRONTEND=noninteractive apt install -y postfix dovecot-core \
    dovecot-imapd dovecot-pop3d dovecot-lmtpd

# Postfix yapılandırması
postconf -e "virtual_mailbox_domains = hash:/etc/postfix/virtual_domains"
postconf -e "virtual_mailbox_maps = hash:/etc/postfix/virtual_mailbox"
postconf -e "virtual_mailbox_base = /var/mail/vhosts"
postconf -e "virtual_uid_maps = static:5000"
postconf -e "virtual_gid_maps = static:5000"

# Mail dizini oluştur
mkdir -p /var/mail/vhosts
groupadd -g 5000 vmail
useradd -g vmail -u 5000 vmail -d /var/mail/vhosts -s /usr/sbin/nologin
chown -R vmail:vmail /var/mail/vhosts

# Dosyaları oluştur
touch /etc/postfix/virtual_domains
touch /etc/postfix/virtual_mailbox

systemctl restart postfix
```

### 9. DNS Servisi Kurun
```bash
apt install -y bind9 bind9utils bind9-doc

# Zone dizini oluştur
mkdir -p /etc/bind/zones

# Named.conf düzenle
echo 'include "/etc/bind/zones/*.conf";' >> /etc/bind/named.conf.local

systemctl enable bind9
systemctl restart bind9
```

### 10. Certbot Kurun (SSL için)
```bash
apt install -y certbot python3-certbot-nginx
```

### 11. Composer Kurun
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### 12. phpMyAdmin Kurun (Opsiyonel)
```bash
DEBIAN_FRONTEND=noninteractive apt install -y phpmyadmin

# Nginx için symlink
ln -s /usr/share/phpmyadmin /opt/1987panel/phpmyadmin
```

### 13. Nginx Panel Konfigürasyonu
```bash
nano /etc/nginx/sites-available/1987panel
```

İçeriği:
```nginx
server {
    listen 8080;
    server_name _;
    root /opt/1987panel;
    index index.php;

    # Güvenlik
    location ~ /\.(ht|git|env) { deny all; }
    location ~ \.(sql|sh|log)$ { deny all; }
    location ^~ /includes/ { deny all; }
    location ^~ /install/ { deny all; }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location /phpmyadmin {
        root /usr/share/;
        index index.php;
        location ~ ^/phpmyadmin/(.+\.php)$ {
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME /usr/share/$fastcgi_script_name;
            include fastcgi_params;
        }
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Aktif et:
```bash
ln -s /etc/nginx/sites-available/1987panel /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

### 14. Sudo Yetkileri Ayarla
```bash
nano /etc/sudoers.d/1987panel
```

İçeriği:
```
www-data ALL=(ALL) NOPASSWD: /usr/sbin/nginx
www-data ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx
www-data ALL=(ALL) NOPASSWD: /bin/systemctl restart nginx
www-data ALL=(ALL) NOPASSWD: /usr/sbin/certbot
www-data ALL=(ALL) NOPASSWD: /usr/sbin/named-checkzone
www-data ALL=(ALL) NOPASSWD: /bin/systemctl reload bind9
www-data ALL=(ALL) NOPASSWD: /bin/systemctl restart bind9
www-data ALL=(ALL) NOPASSWD: /usr/sbin/postmap
www-data ALL=(ALL) NOPASSWD: /bin/systemctl reload postfix
www-data ALL=(ALL) NOPASSWD: /bin/systemctl restart postfix
www-data ALL=(ALL) NOPASSWD: /bin/mkdir
www-data ALL=(ALL) NOPASSWD: /bin/rm
www-data ALL=(ALL) NOPASSWD: /bin/chown
www-data ALL=(ALL) NOPASSWD: /bin/chmod
www-data ALL=(ALL) NOPASSWD: /bin/ln
```

Kaydet ve izinleri ayarla:
```bash
chmod 440 /etc/sudoers.d/1987panel
```

### 15. Dosya İzinlerini Ayarla
```bash
chown -R www-data:www-data /opt/1987panel
chmod -R 755 /opt/1987panel
chmod 600 /opt/1987panel/includes/config.php
```

### 16. Firewall Ayarları
```bash
# UFW kur ve yapılandır
apt install -y ufw

# Portları aç
ufw allow 22/tcp      # SSH
ufw allow 80/tcp      # HTTP
ufw allow 443/tcp     # HTTPS
ufw allow 8080/tcp    # Panel
ufw allow 25/tcp      # SMTP
ufw allow 587/tcp     # SMTP Submission
ufw allow 993/tcp     # IMAPS
ufw allow 995/tcp     # POP3S
ufw allow 53/tcp      # DNS
ufw allow 53/udp      # DNS

# Aktif et
ufw --force enable
```

### 17. Fail2ban Kurun (Güvenlik)
```bash
apt install -y fail2ban
systemctl enable fail2ban
systemctl start fail2ban
```

---

## Kurulum Sonrası

### 1. Panele Giriş Yapın
```
http://SUNUCU-IP:8080
Kullanıcı: admin
Şifre: (belirlediğiniz şifre)
```

### 2. Install Dizinini Silin
```bash
rm -rf /opt/1987panel/install
```

### 3. İlk Domain Ekleyin
- Domainler menüsüne gidin
- "Domain Ekle" butonuna tıklayın
- Domain adını girin (örn: ornek.com)
- PHP versiyonunu seçin
- Kaydet

### 4. DNS Ayarlarını Yapın
Domain'inizin DNS kayıtlarını sunucunuza yönlendirin:
```
A     @      SUNUCU-IP
A     www    SUNUCU-IP
```

### 5. SSL Sertifikası Alın
- SSL Sertifikaları menüsüne gidin
- "SSL Yayınla" butonuna tıklayın
- Domain seçin ve email girin
- Let's Encrypt otomatik sertifika oluşturacak

---

## Sorun Giderme

### Panel Açılmıyor
```bash
# Nginx durumunu kontrol et
systemctl status nginx

# Nginx loglarını kontrol et
tail -f /var/log/nginx/error.log

# PHP-FPM durumunu kontrol et
systemctl status php8.2-fpm
```

### Veritabanı Bağlantı Hatası
```bash
# MariaDB durumunu kontrol et
systemctl status mariadb

# Config dosyasını kontrol et
cat /opt/1987panel/includes/config.php

# Veritabanı kullanıcısını test et
mysql -u panel_user -p 1987panel
```

### 403 Forbidden Hatası
```bash
# Dosya izinlerini kontrol et
ls -la /opt/1987panel

# Düzelt
chown -R www-data:www-data /opt/1987panel
chmod -R 755 /opt/1987panel
```

### SSL Yayınlanamıyor
```bash
# Domain DNS kaydını kontrol et
dig +short ornek.com

# Certbot loglarını kontrol et
tail -f /var/log/letsencrypt/letsencrypt.log

# Manuel test
certbot certonly --nginx -d ornek.com --dry-run
```

---

## Güvenlik Önerileri

### 1. SSH Güvenliği
```bash
# SSH port değiştir
nano /etc/ssh/sshd_config
# Port 22 -> Port 2222
systemctl restart sshd

# Firewall'u güncelle
ufw allow 2222/tcp
ufw delete allow 22/tcp
```

### 2. Root Login Kapat
```bash
nano /etc/ssh/sshd_config
# PermitRootLogin yes -> PermitRootLogin no
systemctl restart sshd
```

### 3. Otomatik Güncellemeler
```bash
apt install -y unattended-upgrades
dpkg-reconfigure -plow unattended-upgrades
```

### 4. Düzenli Yedek
```bash
# Yedek scripti oluştur
nano /root/backup.sh
```

İçeriği:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d)
mysqldump -u root -p'ROOT_ŞİFRE' 1987panel > /root/backup/db_$DATE.sql
tar -czf /root/backup/files_$DATE.tar.gz /opt/1987panel
find /root/backup -mtime +7 -delete
```

Çalıştırılabilir yap ve cron ekle:
```bash
chmod +x /root/backup.sh
crontab -e
# Ekle: 0 2 * * * /root/backup.sh
```

---

## Performans Optimizasyonu

### 1. PHP-FPM Ayarları
```bash
nano /etc/php/8.2/fpm/pool.d/www.conf
```

Düzenle:
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
```

### 2. MariaDB Optimizasyonu
```bash
nano /etc/mysql/mariadb.conf.d/50-server.cnf
```

Ekle:
```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
```

### 3. Nginx Cache
```bash
nano /etc/nginx/nginx.conf
```

Ekle:
```nginx
fastcgi_cache_path /var/cache/nginx levels=1:2 keys_zone=PANEL:100m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";
```

---

## Destek

Sorun yaşarsanız:
- GitHub Issues: https://github.com/kullanici/1987panel/issues
- Dokümantasyon: README.md
- Güvenlik: SECURITY.md

**Başarılı kurulumlar! 🚀**
