<?php require_once '../includes/auth.php'; requireLogin(); $user = currentUser(); ?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <?php include '../includes/head.php'; ?>
  <link rel="stylesheet" href="/1987panel/assets/css/dns.css">
  <title>DNS Yönetimi — 1987 Panel</title>
</head>
<body>
<div class="noise-bg"></div>
<div class="layout">
  <?php include '../includes/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">DNS Yönetimi</div>
      <div class="topbar-actions">
        <button class="btn btn-gold btn-sm" onclick="openZoneModal()"><i class="fas fa-plus"></i> Zone Ekle</button>
      </div>
    </div>
    <div class="content">
      <div id="dns-list"><div class="empty-state">Yükleniyor...</div></div>
    </div>
  </div>
</div>

<!-- Zone Modal -->
<div class="modal-overlay" id="zone-modal">
  <div class="modal">
    <div class="modal-title"><i class="fas fa-network-wired" style="color:var(--gold);margin-right:8px;"></i>DNS Zone Ekle</div>
    <div class="form-group">
      <label class="form-label">Domain</label>
      <input type="text" class="form-input" id="inp-zone" placeholder="ornek.com">
    </div>
    <div id="zone-modal-error" style="color:var(--red);font-size:.75rem;margin-bottom:12px;display:none;"></div>
    <div style="display:flex;gap:8px;">
      <button class="btn btn-gold" id="zone-save-btn" onclick="saveZone()"><i class="fas fa-save"></i> Ekle</button>
      <button class="btn btn-ghost" onclick="closeZoneModal()">İptal</button>
    </div>
  </div>
</div>

<!-- Record Modal -->
<div class="modal-overlay" id="record-modal">
  <div class="modal">
    <div class="modal-title"><i class="fas fa-plus" style="color:var(--gold);margin-right:8px;"></i>DNS Kaydı Ekle</div>
    <input type="hidden" id="inp-zone-id">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <div class="form-group">
        <label class="form-label">Tür</label>
        <select class="form-input" id="inp-rtype">
          <option>A</option><option>AAAA</option><option>CNAME</option>
          <option>MX</option><option>TXT</option><option>NS</option><option>SRV</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">TTL</label>
        <input type="number" class="form-input" id="inp-ttl" value="3600">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Ad <span style="color:var(--dim);font-size:.65rem;">(@, www, mail...)</span></label>
      <input type="text" class="form-input" id="inp-rname" placeholder="@">
    </div>
    <div class="form-group">
      <label class="form-label">Değer</label>
      <input type="text" class="form-input" id="inp-rvalue" placeholder="1.2.3.4">
    </div>
    <div class="form-group" id="priority-group" style="display:none;">
      <label class="form-label">Öncelik (MX)</label>
      <input type="number" class="form-input" id="inp-priority" value="10">
    </div>
    <div id="record-modal-error" style="color:var(--red);font-size:.75rem;margin-bottom:12px;display:none;"></div>
    <div style="display:flex;gap:8px;">
      <button class="btn btn-gold" id="record-save-btn" onclick="saveRecord()"><i class="fas fa-save"></i> Ekle</button>
      <button class="btn btn-ghost" onclick="closeRecordModal()">İptal</button>
    </div>
  </div>
</div>

<script src="/1987panel/assets/js/dns.js"></script>
</body>
</html>
