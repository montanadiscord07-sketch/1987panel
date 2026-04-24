# 1987 Panel - Web Hosting Yönetim Paneli

Modern, güvenli ve kullanıcı dostu web hosting yönetim paneli.

## Özellikler

### Hosting Yönetimi
- ✅ Domain yönetimi (Nginx konfigürasyonu)
- ✅ Veritabanı yönetimi (MariaDB)
- ✅ Dosya yöneticisi (FTP alternatifi)
- ✅ PHP versiyon seçimi (8.0, 8.1, 8.2, 8.3)

### Servisler
- ✅ Mail hesapları (Postfix + Dovecot)
- ✅ DNS yönetimi (BIND9)
- ✅ SSL sertifikaları (Let's Encrypt)

### Yönetim
- ✅ Kullanıcı yönetimi
- ✅ Rol tabanlı yetkilendirme (Admin/User)
- ✅ Sunucu istatistikleri
- ✅ Güvenli oturum yönetimi

## Kurulum

### Gereksinimler
- Ubuntu 22.04 / 24.04
- Root erişimi
- En az 2GB RAM
- En az 20GB disk alanı

### Otomatik Kurulum

```bash
cd 1987panel/install
sudo bash setup.sh
```

Kurulum scripti otomatik olarak:
- Nginx, PHP, MariaDB kurulumu
- Postfix, Dovecot, BIND9 kurulumu
- Certbot kurulumu
- Veritabanı oluşturma
- Admin hesabı oluşturma
- Gerekli izinleri ayarlama

### Manuel Kurulum

1. Veritabanını oluşturun:
```bash
mysql -u root -p < install/database.sql
```

2. Config dosyasını düzenleyin:
```bash
nano includes/config.php
```

3. Gerekli servisleri kurun ve yapılandırın.

## Kullanım

### Giriş
Panel adresinize gidin: `http://sunucu-ip:8080`

Varsayılan giriş bilgileri:
- Kullanıcı adı: `admin`
- Şifre: Kurulum sırasında belirlediğiniz şifre

### Domain Ekleme
1. Domainler menüsüne gidin
2. "Domain Ekle" butonuna tıklayın
3. Domain adını ve PHP versiyonunu seçin
4. Kaydet

### Veritabanı Oluşturma
1. Veritabanları menüsüne gidin
2. "Veritabanı Oluştur" butonuna tıklayın
3. DB adı, kullanıcı adı ve şifre girin
4. Bağlantı bilgilerini kaydedin

### SSL Yayınlama
1. SSL Sertifikaları menüsüne gidin
2. "SSL Yayınla" butonuna tıklayın
3. Domain seçin ve email girin
4. Let's Encrypt otomatik olarak sertifika oluşturacak

## Güvenlik

### Öneriler
- ✅ Güçlü şifreler kullanın
- ✅ Düzenli yedek alın
- ✅ Firewall kurallarını ayarlayın
- ✅ SSH port değiştirin
- ✅ Fail2ban kurun

### Firewall Ayarları
```bash
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP
ufw allow 443/tcp   # HTTPS
ufw allow 8080/tcp  # Panel
ufw allow 25/tcp    # SMTP
ufw allow 587/tcp   # SMTP Submission
ufw allow 993/tcp   # IMAPS
ufw allow 995/tcp   # POP3S
ufw enable
```

## Sorun Giderme

### Panel açılmıyor
```bash
# Nginx durumunu kontrol et
sudo systemctl status nginx

# Nginx loglarını kontrol et
sudo tail -f /var/log/nginx/error.log
```

### Veritabanı bağlantı hatası
```bash
# MariaDB durumunu kontrol et
sudo systemctl status mariadb

# Config dosyasını kontrol et
nano includes/config.php
```

### SSL yayınlanamıyor
```bash
# Domain DNS kaydını kontrol et
dig +short domain.com

# Certbot loglarını kontrol et
sudo tail -f /var/log/letsencrypt/letsencrypt.log
```

## Teknik Detaylar

### Teknolojiler
- **Backend:** PHP 8.2+
- **Veritabanı:** MariaDB 10.6+
- **Web Server:** Nginx
- **Mail:** Postfix + Dovecot
- **DNS:** BIND9
- **SSL:** Let's Encrypt (Certbot)

### Dizin Yapısı
```
1987panel/
├── api/              # API endpoints
├── assets/           # CSS, JS, resimler
├── includes/         # PHP include dosyaları
├── install/          # Kurulum dosyaları
├── pages/            # Sayfa dosyaları
├── index.php         # Giriş sayfası
└── dashboard.php     # Ana panel
```

### Veritabanı Tabloları
- `users` - Kullanıcılar
- `sessions` - Oturumlar
- `domains` - Domainler
- `databases` - Veritabanları
- `mail_domains` - Mail domainleri
- `mail_accounts` - Mail hesapları
- `dns_zones` - DNS zone'ları
- `dns_records` - DNS kayıtları
- `ssl_certs` - SSL sertifikaları

## Lisans

Bu proje özel kullanım içindir.

## Destek

Sorunlar için GitHub Issues kullanın.

## Versiyon

**v1.0.0** - İlk sürüm
