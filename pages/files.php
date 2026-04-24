<?php require_once '../includes/auth.php'; requireLogin(); $user = currentUser(); ?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include '../includes/head.php'; ?>
  <link rel="stylesheet" href="/1987panel/assets/css/files.css">
  <title>Dosya Yöneticisi — 1987 Panel</title>
</head>
<body>
<div class="noise-bg"></div>
<div class="layout">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">Dosya Yöneticisi</div>
      <div class="topbar-actions">
        <span id="current-path" style="font-size:.72rem;color:var(--dim);font-family:monospace;">/</span>
        <button class="btn btn-gold btn-sm" onclick="uploadFile()"><i class="fas fa-upload"></i> Yükle</button>
        <button class="btn btn-ghost btn-sm" onclick="newFolder()"><i class="fas fa-folder-plus"></i> Klasör</button>
        <button class="btn btn-ghost btn-sm" onclick="newFile()"><i class="fas fa-file-plus"></i> Dosya</button>
      </div>
    </div>
    <div class="content">
      <!-- Breadcrumb -->
      <div id="breadcrumb" style="margin-bottom:16px;font-size:.75rem;color:var(--dim);"></div>
      <!-- Dosya listesi -->
      <div class="card" style="padding:0;overflow:hidden;">
        <div id="file-list"><div class="empty-state">Yükleniyor...</div></div>
      </div>
    </div>
  </div>
</div>

<!-- Upload input -->
<input type="file" id="file-input" style="display:none;" multiple onchange="doUpload(this)">

<!-- Edit Modal -->
<div class="modal-overlay" id="edit-modal">
  <div class="modal" style="max-width:700px;">
    <div class="modal-title" id="edit-title">Dosya Düzenle</div>
    <textarea class="form-input" id="edit-content" style="min-height:320px;font-family:monospace;font-size:.8rem;"></textarea>
    <div style="display:flex;gap:8px;margin-top:12px;">
      <button class="btn btn-gold" onclick="saveFile()"><i class="fas fa-save"></i> Kaydet</button>
      <button class="btn btn-ghost" onclick="document.getElementById('edit-modal').classList.remove('open')">İptal</button>
    </div>
  </div>
</div>

<script src="/1987panel/assets/js/files.js"></script>
</body>
</html>
