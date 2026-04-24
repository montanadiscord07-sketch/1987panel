<?php require_once '../includes/auth.php'; requireLogin(); $user = currentUser(); ?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include '../includes/head.php'; ?>
  <link rel="stylesheet" href="/1987panel/assets/css/mail.css">
  <title>Mail Hesapları — 1987 Panel</title>
</head>
<body>
<div class="noise-bg"></div>
<div class="layout">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">Mail Hesapları</div>
      <div class="topbar-actions">
        <button class="btn btn-gold btn-sm" onclick="openDomainModal()"><i class="fas fa-plus"></i> Mail Domain Ekle</button>
      </div>
    </div>
    <div class="content">
      <div id="mail-list"><div class="empty-state">Yükleniyor...</div></div>
    </div>
  </div>
</div>

<!-- Domain Modal -->
<div class="modal-overlay" id="domain-modal">
  <div class="modal">
    <div class="modal-title"><i class="fas fa-envelope" style="color:var(--gold);margin-right:8px;"></i>Mail Domain Ekle</div>
    <div class="form-group">
      <label class="form-label">Domain</label>
      <input type="text" class="form-input" id="inp-maildomain" placeholder="ornek.com">
    </div>
    <div id="domain-modal-error" style="color:var(--red);font-size:.75rem;margin-bottom:12px;display:none;"></div>
    <div style="display:flex;gap:8px;">
      <button class="btn btn-gold" id="domain-save-btn" onclick="saveMailDomain()"><i class="fas fa-save"></i> Ekle</button>
      <button class="btn btn-ghost" onclick="closeDomainModal()">İptal</button>
    </div>
  </div>
</div>

<!-- Hesap Modal -->
<div class="modal-overlay" id="account-modal">
  <div class="modal">
    <div class="modal-title"><i class="fas fa-user" style="color:var(--gold);margin-right:8px;"></i>Mail Hesabı Ekle</div>
    <input type="hidden" id="inp-domain-id">
    <div class="form-group">
      <label class="form-label">Kullanıcı Adı</label>
      <div style="display:flex;align-items:center;gap:8px;">
        <input type="text" class="form-input" id="inp-mailuser" placeholder="info" style="flex:1;">
        <span style="color:var(--dim);font-size:.85rem;">@<span id="domain-label"></span></span>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Şifre</label>
      <input type="password" class="form-input" id="inp-mailpass" placeholder="••••••••">
    </div>
    <div class="form-group">
      <label class="form-label">Kota (MB)</label>
      <input type="number" class="form-input" id="inp-quota" value="1024" min="100">
    </div>
    <div id="account-modal-error" style="color:var(--red);font-size:.75rem;margin-bottom:12px;display:none;"></div>
    <div style="display:flex;gap:8px;">
      <button class="btn btn-gold" id="account-save-btn" onclick="saveMailAccount()"><i class="fas fa-save"></i> Ekle</button>
      <button class="btn btn-ghost" onclick="closeAccountModal()">İptal</button>
    </div>
  </div>
</div>

<script src="/1987panel/assets/js/mail.js"></script>
</body>
</html>
