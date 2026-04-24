<?php
require_once '../includes/auth.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include '../includes/head.php'; ?>
  <link rel="stylesheet" href="/1987panel/assets/css/domains.css">
  <title>Domainler — 1987 Panel</title>
</head>
<body>
<div class="noise-bg"></div>
<div class="layout">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main">

    <div class="topbar">
      <div class="topbar-title">Domainler</div>
      <div class="topbar-actions">
        <button class="btn btn-gold btn-sm" onclick="openModal()">
          <i class="fas fa-plus"></i> Domain Ekle
        </button>
      </div>
    </div>

    <div class="content">
      <div id="domain-list"><div class="empty-state">Yükleniyor...</div></div>
    </div>

  </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modal">
  <div class="modal">
    <div class="modal-title"><i class="fas fa-globe" style="color:var(--gold);margin-right:8px;"></i>Domain Ekle</div>
    <div class="form-group">
      <label class="form-label">Domain Adı</label>
      <input type="text" class="form-input" id="inp-domain" placeholder="ornek.com">
    </div>
    <div class="form-group">
      <label class="form-label">PHP Versiyonu</label>
      <select class="form-input" id="inp-php">
        <option value="8.2">PHP 8.2</option>
        <option value="8.3">PHP 8.3</option>
        <option value="8.1">PHP 8.1</option>
        <option value="8.0">PHP 8.0</option>
      </select>
    </div>
    <div id="modal-error" style="color:var(--red);font-size:.75rem;margin-bottom:12px;display:none;"></div>
    <div style="display:flex;gap:8px;">
      <button class="btn btn-gold" id="modal-save-btn" onclick="saveDomain()">
        <i class="fas fa-save"></i> Kaydet
      </button>
      <button class="btn btn-ghost" onclick="closeModal()">İptal</button>
    </div>
  </div>
</div>

<script src="/1987panel/assets/js/domains.js"></script>
</body>
</html>
