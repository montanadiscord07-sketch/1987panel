<?php
require_once 'includes/auth.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include 'includes/head.php'; ?>
  <title>Dashboard — 1987 Panel</title>
</head>
<body>
<div class="noise-bg"></div>

<div class="layout">
  <?php include 'includes/sidebar.php'; ?>

  <div class="main">
    <div class="topbar">
      <div class="topbar-title">Dashboard</div>
      <div class="topbar-actions">
        <span style="font-size:.72rem;color:var(--dim);" id="server-time"></span>
      </div>
    </div>

    <div class="content">

      <!-- İstatistikler -->
      <div class="stats-grid" id="stats-grid">
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-globe"></i></div>
          <div>
            <div class="stat-value" id="stat-domains">—</div>
            <div class="stat-label">Domain</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-database"></i></div>
          <div>
            <div class="stat-value" id="stat-dbs">—</div>
            <div class="stat-label">Veritabanı</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-envelope"></i></div>
          <div>
            <div class="stat-value" id="stat-mails">—</div>
            <div class="stat-label">Mail Hesabı</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-lock"></i></div>
          <div>
            <div class="stat-value" id="stat-ssl">—</div>
            <div class="stat-label">SSL Sertifikası</div>
          </div>
        </div>
        <?php if (isAdmin()): ?>
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-users"></i></div>
          <div>
            <div class="stat-value" id="stat-users">—</div>
            <div class="stat-label">Kullanıcı</div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Sunucu Bilgisi -->
      <div class="card" style="margin-bottom:20px;">
        <div style="font-size:.65rem;color:var(--dim);letter-spacing:2px;text-transform:uppercase;margin-bottom:16px;">Sunucu Durumu</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;" id="server-info">
          <div><div style="font-size:.65rem;color:var(--dim);margin-bottom:4px;">CPU</div><div id="srv-cpu" style="font-weight:700;">—</div></div>
          <div><div style="font-size:.65rem;color:var(--dim);margin-bottom:4px;">RAM</div><div id="srv-ram" style="font-weight:700;">—</div></div>
          <div><div style="font-size:.65rem;color:var(--dim);margin-bottom:4px;">Disk</div><div id="srv-disk" style="font-weight:700;">—</div></div>
          <div><div style="font-size:.65rem;color:var(--dim);margin-bottom:4px;">Uptime</div><div id="srv-uptime" style="font-weight:700;">—</div></div>
        </div>
      </div>

      <!-- Son Domainler -->
      <div class="card">
        <div class="page-header" style="margin-bottom:16px;">
          <div style="font-size:.65rem;color:var(--dim);letter-spacing:2px;text-transform:uppercase;">Son Eklenen Domainler</div>
          <a href="/1987panel/pages/domains.php" class="btn btn-ghost btn-sm">Tümünü Gör</a>
        </div>
        <div id="recent-domains"><div class="empty-state">Yükleniyor...</div></div>
      </div>

    </div>
  </div>
</div>

<script src="/1987panel/assets/js/dashboard.js"></script>
</body>
</html>
