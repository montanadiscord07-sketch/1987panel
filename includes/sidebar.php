<?php $currentPage = basename($_SERVER['PHP_SELF'], '.php'); ?>
<aside class="sidebar" id="sidebar">

  <div class="sidebar-logo">
    <div style="width:36px;height:36px;background:var(--gold-dim);border:2px solid var(--gold);border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:900;color:var(--gold);font-size:1rem;">87</div>
    <div class="sidebar-brand">1987<br>Panel</div>
  </div>

  <nav class="sidebar-nav">

    <div class="nav-section">
      <div class="nav-section-title">Genel</div>
      <a href="/1987panel/dashboard.php" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
        <i class="fas fa-th-large"></i> Dashboard
      </a>
    </div>

    <div class="nav-section">
      <div class="nav-section-title">Hosting</div>
      <a href="/1987panel/pages/domains.php" class="nav-item <?= $currentPage === 'domains' ? 'active' : '' ?>">
        <i class="fas fa-globe"></i> Domainler
      </a>
      <a href="/1987panel/pages/databases.php" class="nav-item <?= $currentPage === 'databases' ? 'active' : '' ?>">
        <i class="fas fa-database"></i> Veritabanları
      </a>
      <a href="/1987panel/pages/files.php" class="nav-item <?= $currentPage === 'files' ? 'active' : '' ?>">
        <i class="fas fa-folder"></i> Dosya Yöneticisi
      </a>
    </div>

    <div class="nav-section">
      <div class="nav-section-title">Servisler</div>
      <a href="/1987panel/pages/mail.php" class="nav-item <?= $currentPage === 'mail' ? 'active' : '' ?>">
        <i class="fas fa-envelope"></i> Mail Hesapları
      </a>
      <a href="/1987panel/pages/dns.php" class="nav-item <?= $currentPage === 'dns' ? 'active' : '' ?>">
        <i class="fas fa-network-wired"></i> DNS Yönetimi
      </a>
      <a href="/1987panel/pages/ssl.php" class="nav-item <?= $currentPage === 'ssl' ? 'active' : '' ?>">
        <i class="fas fa-lock"></i> SSL Sertifikaları
      </a>
    </div>

    <?php if (isAdmin()): ?>
    <div class="nav-section">
      <div class="nav-section-title">Yönetim</div>
      <a href="/1987panel/pages/users.php" class="nav-item <?= $currentPage === 'users' ? 'active' : '' ?>">
        <i class="fas fa-users"></i> Kullanıcılar
      </a>
    </div>
    <?php endif; ?>

  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-user-avatar"><?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?></div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name"><?= htmlspecialchars($user['username'] ?? '') ?></div>
        <div class="sidebar-user-role"><?= $user['role'] === 'admin' ? 'Admin' : 'Kullanıcı' ?></div>
      </div>
    </div>
    <a href="#" onclick="logout()" class="nav-item">
      <i class="fas fa-sign-out-alt"></i> Çıkış Yap
    </a>
  </div>

</aside>
