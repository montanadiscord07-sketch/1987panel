# Ubuntu'da 1987 Panel Kurulumu

## Basit Kurulum (Önerilen)

### Tek Komutla Kurulum
```bash
curl -sSL https://raw.githubusercontent.com/kullanici/1987panel/main/install/quick-install.sh | sudo bash
```

Veya:

```bash
wget -qO- https://raw.githubusercontent.com/kullanici/1987panel/main/install/quick-install.sh | sudo bash
```

---

## Manuel Kurulum

### 1. Sunucuya Bağlan
```bash
ssh root@sunucu-ip
```

### 2. Sistemi Güncelle
```bash
apt update && apt upgrade -y
```

### 3. Panel Dosyalarını İndir
```bash
cd /opt
git clone https://github.com/kullanici/1987panel.git
```

Veya ZIP ile:
```bash
cd /opt
wget https://github.com/kullanici/1987panel/archive/main.zip
unzip main.zip
mv 1987panel-main 1987panel
rm main.zip
```

### 4. Kurulum Scriptini Çalıştır
```bash
cd /opt/1987panel/install
chmod +x setup.sh
sudo bash setup.sh
```

### 5. Kurulum Sırasında
Script sizden şunları soracak:

**Panel DB Şifresi:**
```
Panel DB şifresi girin (min 12 karakter): ************
Şifreyi tekrar girin: ************
```

**MariaDB Root Şifresi:**
```
MariaDB root şifresi: ************
```

**Admin Panel Şifresi:**
```
Admin şifresi girin (min 12 karakter): ************
Şifreyi tekrar girin: ************
```

### 6. Kurulum Tamamlandı!
```
╔═══════════════════════════════════════════════════════╗
║                                                       ║
║          ✓ KURULUM BAŞARIYLA TAMAMLANDI!            ║
║                                                       ║
╚═══════════════════════════════════════════════════════╝

📍 Panel Bilgileri:
─────────────────────────────────────────────────────
🌐 Panel Adresi    : http://123.45.67.89:8080
👤 Kullanıcı Adı   : admin
🔑 Şifre           : (belirlediğiniz şifre)
📊 phpMyAdmin      : http://123.45.67.89:8080/phpmyadmin
```

---

## Kurulum Sonrası

### 1. Panele Giriş Yap
Tarayıcıda panel adresine git:
```
http://SUNUCU-IP:8080
```

Giriş bilgileri:
- **Kullanıcı:** admin
- **Şifre:** Kurulumda belirlediğin şifre

### 2. Install Dizinini Sil (ÖNEMLİ!)
```bash
rm -rf /opt/1987panel/install
```

### 3. Firewall Kontrolü
```bash
# Firewall durumunu kontrol et
ufw status

# Gerekli portlar açık mı kontrol et
ufw status numbered
```

Açık olması gereken portlar:
- 22 (SSH)
- 80 (HTTP)
- 443 (HTTPS)
- 8080 (Panel)
- 25, 587 (SMTP)
- 993, 995 (IMAP/POP3)
- 53 (DNS)

### 4. Servis Durumlarını Kontrol Et
```bash
# Nginx
systemctl status nginx

# PHP-FPM
systemctl status php8.2-fpm

# MariaDB
systemctl status mariadb

# Postfix
systemctl status postfix

# BIND9
systemctl status bind9
```

---

## İlk Yapılandırma

### 1. İlk Domain Ekle
1. Panele giriş yap
2. Sol menüden **"Domainler"** seç
3. **"Domain Ekle"** butonuna tıkla
4. Domain bilgilerini gir:
   - Domain: `ornek.com`
   - PHP Versiyon: `8.2` (önerilen)
5. **"Kaydet"** butonuna tıkla

### 2. DNS Ayarlarını Yap
Domain sağlayıcında (GoDaddy, Namecheap, vb.) A kaydı ekle:

```
Tip: A
Host: @
Değer: SUNUCU-IP
TTL: 3600
```

```
Tip: A
Host: www
Değer: SUNUCU-IP
TTL: 3600
```

DNS yayılmasını bekle (5-30 dakika):
```bash
# DNS kontrolü
dig +short ornek.com
nslookup ornek.com
```

### 3. SSL Sertifikası Al
1. Panelden **"SSL Sertifikaları"** menüsüne git
2. **"SSL Yayınla"** butonuna tıkla
3. Domain seç: `ornek.com`
4. Email gir: `admin@ornek.com`
5. **"Yayınla"** butonuna tıkla
6. 1-2 dakika bekle

⚠️ **Not:** SSL için domain'in DNS kaydı sunucuya işaret etmeli!

### 4. Veritabanı Oluştur
1. **"Veritabanları"** menüsüne git
2. **"Veritabanı Oluştur"** butonuna tıkla
3. Bilgileri gir:
   - DB Adı: `myapp_db`
   - Kullanıcı: `myapp_user`
   - Şifre: (boş bırak veya güçlü şifre)
4. **"Oluştur"** butonuna tıkla
5. ⚠️ **Bağlantı bilgilerini kaydet!**

### 5. Dosya Yükle
1. **"Dosya Yöneticisi"** menüsüne git
2. Domain klasörüne git: `/ornek.com/public_html`
3. **"Yükle"** butonuna tıkla
4. Dosyaları seç ve yükle

---

## Örnek Uygulamalar

### WordPress Kurulumu

#### 1. Domain ve DB Hazırla
Panelden:
- Domain ekle: `myblog.com`
- Veritabanı oluştur: `wp_db`, `wp_user`

#### 2. WordPress İndir
```bash
cd /var/www/myblog.com/public_html
wget https://wordpress.org/latest.tar.gz
tar -xzf latest.tar.gz
mv wordpress/* .
rm -rf wordpress latest.tar.gz
chown -R www-data:www-data .
```

#### 3. Kurulumu Tamamla
Tarayıcıda `http://myblog.com` adresine git ve kurulumu tamamla.

#### 4. SSL Ekle
Panelden SSL yayınla, sonra WordPress'te:
```
Ayarlar > Genel
WordPress Adresi: https://myblog.com
Site Adresi: https://myblog.com
```

---

### Laravel Kurulumu

#### 1. Domain ve DB Hazırla
Panelden:
- Domain ekle: `myapp.com` (PHP 8.2)
- Veritabanı oluştur: `laravel_db`, `laravel_user`

#### 2. Composer Kur (eğer yoksa)
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

#### 3. Laravel Kur
```bash
cd /var/www/myapp.com
composer create-project laravel/laravel .
```

#### 4. Nginx Config Düzenle
```bash
nano /etc/nginx/sites-available/myapp.com
```

`root` satırını değiştir:
```nginx
root /var/www/myapp.com/public;
```

Nginx'i yeniden yükle:
```bash
nginx -t
systemctl reload nginx
```

#### 5. .env Dosyasını Düzenle
```bash
nano /var/www/myapp.com/.env
```

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=şifreniz
```

#### 6. İzinleri Ayarla
```bash
cd /var/www/myapp.com
chown -R www-data:www-data .
chmod -R 755 storage bootstrap/cache
```

---

## Sorun Giderme

### Panel Açılmıyor

**Kontrol 1: Nginx çalışıyor mu?**
```bash
systemctl status nginx
```

Çalışmıyorsa:
```bash
systemctl start nginx
```

**Kontrol 2: PHP-FPM çalışıyor mu?**
```bash
systemctl status php8.2-fpm
```

Çalışmıyorsa:
```bash
systemctl start php8.2-fpm
```

**Kontrol 3: Port açık mı?**
```bash
netstat -tlnp | grep 8080
```

**Kontrol 4: Firewall engellemiyor mu?**
```bash
ufw status
ufw allow 8080/tcp
```

**Kontrol 5: Nginx loglarını kontrol et**
```bash
tail -f /var/log/nginx/error.log
```

---

### Veritabanı Bağlantı Hatası

**Kontrol 1: MariaDB çalışıyor mu?**
```bash
systemctl status mariadb
```

**Kontrol 2: Config doğru mu?**
```bash
cat /opt/1987panel/includes/config.php
```

**Kontrol 3: Kullanıcı erişimi var mı?**
```bash
mysql -u panel_user -p 1987panel
```

**Kontrol 4: MariaDB loglarını kontrol et**
```bash
tail -f /var/log/mysql/error.log
```

---

### SSL Yayınlanamıyor

**Kontrol 1: DNS kaydı doğru mu?**
```bash
dig +short ornek.com
```

Sunucu IP'si dönmeli!

**Kontrol 2: Port 80 açık mı?**
```bash
netstat -tlnp | grep :80
ufw allow 80/tcp
```

**Kontrol 3: Certbot loglarını kontrol et**
```bash
tail -f /var/log/letsencrypt/letsencrypt.log
```

**Kontrol 4: Manuel test**
```bash
certbot certonly --nginx -d ornek.com --dry-run
```

---

### Dosya Yüklenmiyor

**Kontrol 1: PHP upload limiti**
```bash
nano /etc/php/8.2/fpm/php.ini
```

Değiştir:
```ini
upload_max_filesize = 128M
post_max_size = 128M
max_execution_time = 300
```

Yeniden başlat:
```bash
systemctl restart php8.2-fpm
```

**Kontrol 2: Nginx upload limiti**
```bash
nano /etc/nginx/nginx.conf
```

Ekle:
```nginx
client_max_body_size 128M;
```

Yeniden yükle:
```bash
systemctl reload nginx
```

**Kontrol 3: Dizin izinleri**
```bash
chown -R www-data:www-data /var/www
chmod -R 755 /var/www
```

---

## Performans Optimizasyonu

### PHP-FPM Ayarları
```bash
nano /etc/php/8.2/fpm/pool.d/www.conf
```

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

### MariaDB Optimizasyonu
```bash
nano /etc/mysql/mariadb.conf.d/50-server.cnf
```

```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
query_cache_size = 64M
```

### Nginx Cache
```bash
nano /etc/nginx/nginx.conf
```

```nginx
fastcgi_cache_path /var/cache/nginx levels=1:2 keys_zone=PANEL:100m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";
```

---

## Güvenlik Sertleştirme

### 1. SSH Güvenliği
```bash
nano /etc/ssh/sshd_config
```

Değiştir:
```
Port 2222
PermitRootLogin no
PasswordAuthentication no
```

Yeniden başlat:
```bash
systemctl restart sshd
ufw allow 2222/tcp
```

### 2. Fail2ban Yapılandır
```bash
nano /etc/fail2ban/jail.local
```

```ini
[sshd]
enabled = true
port = 2222
maxretry = 3
bantime = 3600

[nginx-http-auth]
enabled = true
```

Yeniden başlat:
```bash
systemctl restart fail2ban
```

### 3. Otomatik Güncellemeler
```bash
apt install -y unattended-upgrades
dpkg-reconfigure -plow unattended-upgrades
```

### 4. Düzenli Yedek
```bash
mkdir -p /root/backups
nano /root/backup.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -p'ROOT_ŞİFRE' --all-databases > /root/backups/db_$DATE.sql
tar -czf /root/backups/files_$DATE.tar.gz /opt/1987panel /var/www
find /root/backups -mtime +7 -delete
```

Çalıştırılabilir yap:
```bash
chmod +x /root/backup.sh
```

Cron ekle:
```bash
crontab -e
```

```
0 2 * * * /root/backup.sh
```

---

## Yardım ve Destek

### Dokümantasyon
- 📖 **README:** `/opt/1987panel/README.md`
- 🔒 **Güvenlik:** `/opt/1987panel/SECURITY.md`
- 💻 **Detaylı Kurulum:** `/opt/1987panel/INSTALL.md`
- 🚀 **Hızlı Başlangıç:** `/opt/1987panel/QUICKSTART.md`

### Komutlar
```bash
# Servis durumları
systemctl status nginx php8.2-fpm mariadb

# Logları görüntüle
tail -f /var/log/nginx/error.log
tail -f /var/log/php8.2-fpm.log

# Yedek al
mysqldump -u root -p 1987panel > backup.sql
tar -czf panel-backup.tar.gz /opt/1987panel
```

### İletişim
- 🐛 **Bug:** GitHub Issues
- 💬 **Soru:** GitHub Discussions
- 🔐 **Güvenlik:** security@example.com

---

**Başarılı kurulumlar! 🎉**
