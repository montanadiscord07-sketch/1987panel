<?php require_once '../includes/auth.php'; requireLogin(); $user = currentUser(); ?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include '../includes/head.php'; ?>
  <link rel="stylesheet" href="/1987panel/assets/css/databases.css">
  <title>Veritabanları — 1987 Panel</title>
</head>
<body>
<div class="noise-bg"></div>
<div class="layout">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">Veritabanları</div>
      <div class="topbar-actions">
        <button class="btn btn-gold btn-sm" onclick="openModal()"><i class="fas fa-plus"></i> Veritabanı Oluştur</button>
      </div>
    </div>
    <div class="content">
      <div id="db-list"><div class="empty-state">Yükleniyor...</div></div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modal">
  <div class="modal">
    <div class="modal-title"><i class="fas fa-database" style="color:var(--gold);margin-right:8px;"></i>Veritabanı Oluştur</div>
    <div class="form-group">
      <label class="form-label">Veritabanı Adı</label>
      <input type="text" class="form-input" id="inp-dbname" placeholder="mydb">
    </div>
    <div class="form-group">
      <label class="form-label">Kullanıcı Adı</label>
      <input type="text" class="form-input" id="inp-dbuser" placeholder="mydb_user">
    </div>
    <div class="form-group">
      <label class="form-label">Şifre <span style="color:var(--dim);font-size:.65rem;">(boş bırakırsan otomatik oluşturulur)</span></label>
      <input type="text" class="form-input" id="inp-dbpass" placeholder="Otomatik">
    </div>
    <div id="modal-error" style="color:var(--red);font-size:.75rem;margin-bottom:12px;display:none;"></div>
    <div style="display:flex;gap:8px;">
      <button class="btn btn-gold" id="modal-save-btn" onclick="saveDb()"><i class="fas fa-save"></i> Oluştur</button>
      <button class="btn btn-ghost" onclick="closeModal()">İptal</button>
    </div>
  </div>
</div>

<!-- Credentials Modal -->
<div class="modal-overlay" id="cred-modal">
  <div class="modal">
    <div class="modal-title"><i class="fas fa-key" style="color:var(--gold);margin-right:8px;"></i>Bağlantı Bilgileri</div>
    <div class="cred-box" id="cred-box"></div>
    <p style="font-size:.7rem;color:var(--red);margin-top:12px;"><i class="fas fa-exclamation-triangle"></i> Bu bilgileri kaydedin, şifre tekrar gösterilmeyecek!</p>
    <button class="btn btn-gold" style="margin-top:16px;" onclick="document.getElementById('cred-modal').classList.remove('open')">Tamam, Kaydettim</button>
  </div>
</div>

<script src="/1987panel/assets/js/databases.js"></script>
</body>
</html>
