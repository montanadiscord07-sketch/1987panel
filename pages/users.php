<?php require_once '../includes/auth.php'; requireAdmin(); $user = currentUser(); ?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include '../includes/head.php'; ?>
  <link rel="stylesheet" href="/1987panel/assets/css/users.css">
  <title>Kullanıcılar — 1987 Panel</title>
</head>
<body>
<div class="noise-bg"></div>
<div class="layout">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">Kullanıcı Yönetimi</div>
      <div class="topbar-actions">
        <button class="btn btn-gold btn-sm" onclick="openModal()"><i class="fas fa-plus"></i> Kullanıcı Ekle</button>
      </div>
    </div>
    <div class="content">
      <div id="user-list"><div class="empty-state">Yükleniyor...</div></div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal">
  <div class="modal">
    <div class="modal-title"><i class="fas fa-user-plus" style="color:var(--gold);margin-right:8px;"></i>Kullanıcı Ekle</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div class="form-group">
        <label class="form-label">Kullanıcı Adı</label>
        <input type="text" class="form-input" id="inp-uname" placeholder="kullanici">
      </div>
      <div class="form-group">
        <label class="form-label">Rol</label>
        <select class="form-input" id="inp-role">
          <option value="user">Kullanıcı</option>
          <option value="admin">Admin</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">E-posta</label>
      <input type="email" class="form-input" id="inp-uemail" placeholder="kullanici@ornek.com">
    </div>
    <div class="form-group">
      <label class="form-label">Şifre</label>
      <input type="password" class="form-input" id="inp-upass" placeholder="En az 8 karakter">
    </div>
    <div id="modal-error" style="color:var(--red);font-size:.75rem;margin-bottom:12px;display:none;"></div>
    <div style="display:flex;gap:8px;">
      <button class="btn btn-gold" id="modal-save-btn" onclick="saveUser()"><i class="fas fa-save"></i> Kaydet</button>
      <button class="btn btn-ghost" onclick="closeModal()">İptal</button>
    </div>
  </div>
</div>

<script src="/1987panel/assets/js/users.js"></script>
</body>
</html>
