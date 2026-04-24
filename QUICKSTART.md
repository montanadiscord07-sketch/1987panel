# 1987 Panel - Hızlı Başlangıç

## 5 Dakikada Kurulum

### 1. Sunucuya Bağlan
```bash
ssh root@sunucu-ip
```

### 2. Kurulum Komutunu Çalıştır
```bash
cd /tmp
wget https://raw.githubusercontent.com/kullanici/1987panel/main/install/quick-install.sh
chmod +x quick-install.sh
sudo bash quick-install.sh
```

### 3. Kurulum Sırasında İstenecekler
- ✅ Panel DB şifresi (min 12 karakter)
- ✅ MariaDB root şifresi
- ✅ Admin panel şifresi (min 12 karakter)

### 4. Kurulum Tamamlandı!
```
http://SUNUCU-IP:8080
Kullanıcı: admin
Şifre: (belirlediğiniz şifre)
```

---

## İlk Adımlar

### 1. Panele Giriş Yap
- Tarayıcıda `http://SUNUCU-IP:8080` adresine git
- Kullanıcı adı: `admin`
- Şifre: Kurulumda belirlediğin şifre

### 2. İlk Domain'i Ekle
1. Sol menüden **"Domainler"** seç
2. **"Domain Ekle"** butonuna tıkla
3. Domain adını gir (örn: `ornek.com`)
4. PHP versiyonunu seç (önerilen: 8.2)
5. **"Kaydet"** butonuna tıkla

### 3. DNS Ayarlarını Yap
Domain sağlayıcında A kaydı ekle:
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

### 4. SSL Sertifikası Al
1. Sol menüden **"SSL Sertifikaları"** seç
2. **"SSL Yayınla"** butonuna tıkla
3. Domain'i seç
4. Email adresini gir
5. **"Yayınla"** butonuna tıkla
6. 1-2 dakika bekle (Let's Encrypt otomatik oluşturacak)

### 5. Veritabanı Oluştur
1. Sol menüden **"Veritabanları"** seç
2. **"Veritabanı Oluştur"** butonuna tıkla
3. DB adı gir (örn: `mydb`)
4. Kullanıcı adı gir (örn: `mydb_user`)
5. Şifre gir (veya boş bırak, otomatik oluşturulur)
6. **"Oluştur"** butonuna tıkla
7. ⚠️ **Bağlantı bilgilerini kaydet!** (tekrar gösterilmeyecek)

### 6. Dosya Yükle
1. Sol menüden **"Dosya Yöneticisi"** seç
2. Domain klasörüne git (örn: `/ornek.com/public_html`)
3. **"Yükle"** butonuna tıkla
4. Dosyaları seç ve yükle
5. Veya **"Dosya"** butonuyla yeni dosya oluştur

---

## Örnek: WordPress Kurulumu

### 1. Domain ve Veritabanı Hazırla
```
Domain: wordpress.com
DB Adı: wp_db
DB Kullanıcı: wp_user
DB Şifre: (güçlü şifre)
```

### 2. WordPress İndir
```bash
cd /var/www/wordpress.com/public_html
wget https://wordpress.org/latest.tar.gz
tar -xzf latest.tar.gz
mv wordpress/* .
rm -rf wordpress latest.tar.gz
chown -R www-data:www-data .
```

### 3. wp-config.php Oluştur
Dosya yöneticisinden `wp-config-sample.php`'yi kopyala ve `wp-config.php` olarak kaydet.

Düzenle:
```php
define('DB_NAME', 'wp_db');
define('DB_USER', 'wp_user');
define('DB_PASSWORD', 'şifreniz');
define('DB_HOST', 'localhost');
```

### 4. WordPress Kurulumunu Tamamla
Tarayıcıda `http://wordpress.com` adresine git ve kurulumu tamamla.

### 5. SSL Ekle
Panelden SSL sertifikası yayınla, sonra WordPress'te:
```
Ayarlar > Genel
WordPress Adresi: https://wordpress.com
Site Adresi: https://wordpress.com
```

---

## Sık Kullanılan İşlemler

### Mail Hesabı Oluştur
1. **"Mail Hesapları"** menüsüne git
2. **"Mail Domain Ekle"** ile domain ekle
3. **"Hesap Ekle"** ile mail adresi oluştur
4. Bağlantı bilgileri:
   - **IMAP:** sunucu-ip:993 (SSL)
   - **SMTP:** sunucu-ip:587 (STARTTLS)
   - **Kullanıcı:** tam email adresi
   - **Şifre:** belirlediğin şifre

### DNS Kaydı Ekle
1. **"DNS Yönetimi"** menüsüne git
2. **"Zone Ekle"** ile domain ekle
3. Zone'a tıkla, **"Kayıt Ekle"** butonuna tıkla
4. Kayıt türünü seç (A, CNAME, MX, TXT, vb.)
5. Değerleri gir ve kaydet

### Kullanıcı Ekle (Admin)
1. **"Kullanıcılar"** menüsüne git
2. **"Kullanıcı Ekle"** butonuna tıkla
3. Bilgileri gir
4. Rol seç (Admin veya User)
5. Kaydet

---

## Sorun Giderme

### Panel Açılmıyor
```bash
# Servisleri kontrol et
systemctl status nginx
systemctl status php8.2-fpm

# Yeniden başlat
systemctl restart nginx
systemctl restart php8.2-fpm
```

### Veritabanı Hatası
```bash
# MariaDB durumunu kontrol et
systemctl status mariadb

# Config dosyasını kontrol et
cat /opt/1987panel/includes/config.php
```

### SSL Hatası
```bash
# DNS kaydını kontrol et
dig +short domain.com

# Certbot loglarını kontrol et
tail -f /var/log/letsencrypt/letsencrypt.log
```

### Dosya Yüklenmiyor
```bash
# PHP upload limitini artır
nano /etc/php/8.2/fpm/php.ini

# Değiştir:
upload_max_filesize = 128M
post_max_size = 128M

# Yeniden başlat
systemctl restart php8.2-fpm
```

---

## Güvenlik İpuçları

### ✅ Yapılması Gerekenler
- Güçlü şifreler kullan (min 12 karakter)
- Install dizinini sil: `rm -rf /opt/1987panel/install`
- Düzenli yedek al
- Sistem güncellemelerini yap: `apt update && apt upgrade`
- Firewall aktif tut
- SSH portunu değiştir (opsiyonel)

### ❌ Yapılmaması Gerekenler
- Zayıf şifre kullanma
- Root ile SSH'a izin verme
- Yedek almadan işlem yapma
- Güvenlik güncellemelerini atlama
- Herkese açık phpMyAdmin bırakma

---

## Yardım ve Destek

### Dokümantasyon
- 📖 **Tam Kılavuz:** `/opt/1987panel/README.md`
- 🔒 **Güvenlik:** `/opt/1987panel/SECURITY.md`
- 💻 **Kurulum:** `/opt/1987panel/INSTALL.md`

### Komutlar
```bash
# Logları görüntüle
tail -f /var/log/nginx/error.log
tail -f /var/log/php8.2-fpm.log

# Servisleri yönet
systemctl status nginx
systemctl restart php8.2-fpm
systemctl reload nginx

# Veritabanı yedekle
mysqldump -u root -p 1987panel > backup.sql

# Dosya yedekle
tar -czf panel-backup.tar.gz /opt/1987panel
```

### İletişim
- 🐛 **Bug Bildirimi:** GitHub Issues
- 💬 **Soru-Cevap:** GitHub Discussions
- 🔐 **Güvenlik:** security@example.com

---

**Başarılı kullanımlar! 🚀**
