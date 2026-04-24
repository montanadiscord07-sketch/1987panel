# Güvenlik Politikası

## Güvenlik Özellikleri

### Kimlik Doğrulama
- ✅ BCrypt şifre hashleme (cost: 12)
- ✅ Güvenli oturum yönetimi (token tabanlı)
- ✅ Oturum süresi sınırlaması (24 saat)
- ✅ IP ve User-Agent kontrolü
- ✅ Otomatik oturum temizleme

### Yetkilendirme
- ✅ Rol tabanlı erişim kontrolü (RBAC)
- ✅ Kullanıcı bazlı kaynak izolasyonu
- ✅ Admin/User ayrımı
- ✅ Her API endpoint'te yetki kontrolü

### Veri Güvenliği
- ✅ Prepared statements (SQL injection koruması)
- ✅ XSS koruması (HTML escape)
- ✅ CSRF token (gelecek sürümde)
- ✅ Input validasyonu
- ✅ Output sanitizasyonu

### Dosya Güvenliği
- ✅ Path traversal koruması
- ✅ Dosya tipi kontrolü
- ✅ Boyut sınırlaması
- ✅ Kullanıcı bazlı dizin erişimi
- ✅ Hassas dosyalara erişim engeli

### Sistem Güvenliği
- ✅ Sudo yetkileri sınırlı
- ✅ Shell komutları escapeshellarg ile korunuyor
- ✅ Hata mesajları kullanıcıya detay vermiyor
- ✅ Güvenlik başlıkları (X-Frame-Options, vb.)

## Bilinen Güvenlik Konuları

### Kritik
- ❌ CSRF koruması eksik (gelecek sürümde eklenecek)
- ❌ Rate limiting yok (brute force saldırılarına açık)
- ❌ 2FA desteği yok

### Orta
- ⚠️ Session fixation koruması geliştirilmeli
- ⚠️ Password policy zorunlu değil
- ⚠️ Audit logging eksik

### Düşük
- ⚠️ Email doğrulama yok
- ⚠️ Şifre sıfırlama mekanizması yok

## Güvenlik Önerileri

### Kurulum Sonrası
1. **Config dosyasını güvenli hale getirin:**
```bash
chmod 600 includes/config.php
```

2. **Install dizinini silin:**
```bash
rm -rf install/
```

3. **Güçlü şifreler kullanın:**
- En az 12 karakter
- Büyük/küçük harf, rakam, özel karakter

4. **Firewall kuralları:**
```bash
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 8080/tcp
ufw enable
```

5. **Fail2ban kurun:**
```bash
apt install fail2ban
systemctl enable fail2ban
```

### Üretim Ortamı
1. **HTTPS kullanın:**
- Panel için SSL sertifikası alın
- HTTP'yi HTTPS'e yönlendirin

2. **Veritabanı güvenliği:**
```bash
mysql_secure_installation
```

3. **PHP güvenlik ayarları:**
```ini
expose_php = Off
display_errors = Off
log_errors = On
```

4. **Nginx güvenlik başlıkları:**
```nginx
add_header X-Frame-Options "SAMEORIGIN";
add_header X-Content-Type-Options "nosniff";
add_header X-XSS-Protection "1; mode=block";
```

### Düzenli Bakım
- ✅ Sistem güncellemelerini yapın
- ✅ Log dosyalarını kontrol edin
- ✅ Kullanıcı aktivitelerini izleyin
- ✅ Yedek alın
- ✅ Güvenlik taramalarını çalıştırın

## Güvenlik Açığı Bildirimi

Güvenlik açığı bulursanız:
1. **Hemen bildirin** (public issue açmayın)
2. Detaylı açıklama yapın
3. Proof of concept ekleyin (opsiyonel)
4. İletişim bilgilerinizi paylaşın

## Güvenlik Güncellemeleri

### v1.0.0 (Mevcut)
- BCrypt şifre hashleme
- Prepared statements
- XSS koruması
- Path traversal koruması
- Rol tabanlı yetkilendirme

### Planlanan (v1.1.0)
- CSRF token implementasyonu
- Rate limiting
- Audit logging
- Session fixation koruması
- Password policy

### Gelecek (v1.2.0)
- 2FA desteği
- Email doğrulama
- Şifre sıfırlama
- IP whitelist/blacklist
- Gelişmiş güvenlik logları

## Güvenlik Kontrol Listesi

### Kurulum
- [ ] Güçlü admin şifresi belirlendi
- [ ] Config dosyası güvence altına alındı
- [ ] Install dizini silindi
- [ ] Firewall yapılandırıldı
- [ ] Fail2ban kuruldu

### Yapılandırma
- [ ] HTTPS aktif
- [ ] Güvenlik başlıkları eklendi
- [ ] PHP güvenlik ayarları yapıldı
- [ ] Veritabanı güvenliği sağlandı
- [ ] Log dosyaları yapılandırıldı

### İzleme
- [ ] Log dosyaları düzenli kontrol ediliyor
- [ ] Kullanıcı aktiviteleri izleniyor
- [ ] Sistem güncellemeleri yapılıyor
- [ ] Yedekler düzenli alınıyor
- [ ] Güvenlik taramaları çalıştırılıyor

## İletişim

Güvenlik konularında: security@example.com
