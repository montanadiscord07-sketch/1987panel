<?php require_once '../includes/auth.php'; requireLogin(); $user = currentUser(); ?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include '../includes/head.php'; ?>
  <link rel="stylesheet" href="/1987panel/assets/css/ssl.css">
  <title>SSL Sertifikaları — 1987 Panel</title>
</head>
<body>
<div class="noise-bg"></div>
<div class="layout">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">SSL Sertifikaları</div>
      <div class="topbar-actions">
        <button class="btn btn-gold btn-sm" onclick="openModal()"><i class="fas fa-plus"></i> SSL Yayınla</button>
      </div>
    </div>
    <div class="content">
      <div class="card" style="margin-bottom:20px;background:rgba(212,175,55,.04);border-color:rgba(212,175,55,.2);">
        <div style="font-size:.75rem;color:var(--dim);line-height:1.7;">
          <i class="fas fa-info-circle" style="color:var(--gold);margin-right:6px;"></i>
          SSL yayınlamak için domain'in A kaydının bu sunucuya işaret etmesi gerekir. Let's Encrypt ücretsiz SSL sertifikası sağlar (90 günde bir otomatik yenilenir).
        </div>
      </div>
      <div id="ssl-list"><div class="empty-state">Yükleniyor...</div></div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal">
  <div class="modal">
    <div class="modal-title"><i class="fas fa-lock" style="color:var(--gold);margin-right:8px;"></i>SSL Sertifikası Yayınla</div>
    <div class="form-group">
      <label class="form-label">Domain</label>
      <select class="form-input" id="inp-ssldomain"><option value="">Domain seçin...</option></select>
    </div>
    <div class="form-group">
      <label class="form-label">E-posta (Let's Encrypt bildirimleri için)</label>
      <input type="email" class="form-input" id="inp-sslemail" placeholder="admin@ornek.com">
    </div>
    <div id="modal-error" style="color:var(--red);font-size:.75rem;margin-bottom:12px;display:none;"></div>
    <div id="ssl-progress" class="ssl-progress">
      <i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i>SSL yayınlanıyor, bu işlem 1-2 dakika sürebilir...
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn btn-gold" id="modal-save-btn" onclick="issueSSL()"><i class="fas fa-lock"></i> Yayınla</button>
      <button class="btn btn-ghost" onclick="closeModal()">İptal</button>
    </div>
  </div>
</div>

<script src="/1987panel/assets/js/ssl.js"></script>
</body>
</html>
