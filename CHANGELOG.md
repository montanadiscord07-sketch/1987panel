# Değişiklik Günlüğü

## [1.0.0] - 2024-01-15

### Eklenenler
- ✅ Domain yönetimi (Nginx konfigürasyonu)
- ✅ Veritabanı yönetimi (MariaDB)
- ✅ Dosya yöneticisi
- ✅ Mail hesapları (Postfix + Dovecot)
- ✅ DNS yönetimi (BIND9)
- ✅ SSL sertifikaları (Let's Encrypt)
- ✅ Kullanıcı yönetimi
- ✅ Rol tabanlı yetkilendirme
- ✅ Sunucu istatistikleri
- ✅ Güvenli oturum yönetimi
- ✅ PHP versiyon seçimi (8.0, 8.1, 8.2, 8.3)
- ✅ Otomatik kurulum scripti
- ✅ Modern UI/UX tasarımı
- ✅ Responsive tasarım

### Güvenlik
- ✅ BCrypt şifre hashleme (cost: 12)
- ✅ Prepared statements (SQL injection koruması)
- ✅ XSS koruması (HTML escape)
- ✅ Path traversal koruması
- ✅ Dosya tipi kontrolü
- ✅ Shell komut güvenliği
- ✅ Rate limiting (basit)
- ✅ Hassas dosya erişim engeli

### Düzeltmeler
- ✅ API yolları düzeltildi (/1987panel/api/)
- ✅ CSS/JS yolları düzeltildi
- ✅ Logo dosyası yerine CSS ile logo oluşturuldu
- ✅ Sidebar linkleri düzeltildi
- ✅ Dashboard linkleri düzeltildi
- ✅ Logout yönlendirmeleri düzeltildi
- ✅ Güvenlik açıkları kapatıldı

### Bilinen Sorunlar
- ⚠️ CSRF koruması eksik (v1.1.0'da eklenecek)
- ⚠️ 2FA desteği yok
- ⚠️ Email doğrulama yok
- ⚠️ Şifre sıfırlama mekanizması yok
- ⚠️ Audit logging eksik

## [Planlanan 1.1.0]

### Eklenecekler
- [ ] CSRF token implementasyonu
- [ ] Gelişmiş rate limiting
- [ ] Audit logging
- [ ] Session fixation koruması
- [ ] Password policy
- [ ] Email bildirimleri
- [ ] Backup/restore özelliği
- [ ] Cron job yönetimi

### İyileştirmeler
- [ ] Performans optimizasyonları
- [ ] Daha detaylı hata mesajları
- [ ] Gelişmiş arama ve filtreleme
- [ ] Toplu işlemler
- [ ] API dokümantasyonu

## [Planlanan 1.2.0]

### Eklenecekler
- [ ] 2FA desteği (TOTP)
- [ ] Email doğrulama
- [ ] Şifre sıfırlama
- [ ] IP whitelist/blacklist
- [ ] Gelişmiş güvenlik logları
- [ ] FTP hesapları
- [ ] Git entegrasyonu
- [ ] Monitoring ve alerting

### İyileştirmeler
- [ ] Multi-language desteği
- [ ] Dark/Light tema
- [ ] Gelişmiş dashboard
- [ ] Grafik ve raporlar
- [ ] API rate limiting
