// Saat
function updateClock() {
  const el = document.getElementById('server-time');
  if (el) el.textContent = new Date().toLocaleString('tr-TR');
}
setInterval(updateClock, 1000);
updateClock();

// Logout
async function logout() {
  await fetch('/1987panel/api/auth.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ action: 'logout' }),
  });
  window.location.href = '/1987panel/index.php';
}

// İstatistikler
async function loadStats() {
  try {
    const res  = await fetch('/1987panel/api/stats.php', { credentials: 'include' });
    const data = await res.json();
    if (!data.success) return;

    const s = data.stats;
    document.getElementById('stat-domains').textContent = s.domains ?? 0;
    document.getElementById('stat-dbs').textContent     = s.databases ?? 0;
    document.getElementById('stat-mails').textContent   = s.mails ?? 0;
    document.getElementById('stat-ssl').textContent     = s.ssl ?? 0;
    if (document.getElementById('stat-users'))
      document.getElementById('stat-users').textContent = s.users ?? 0;

    // Sunucu bilgisi
    if (s.server) {
      document.getElementById('srv-cpu').textContent   = s.server.cpu   || '—';
      document.getElementById('srv-ram').textContent   = s.server.ram   || '—';
      document.getElementById('srv-disk').textContent  = s.server.disk  || '—';
      document.getElementById('srv-uptime').textContent = s.server.uptime || '—';
    }
  } catch(e) { console.error(e); }
}

// Son domainler
async function loadRecentDomains() {
  try {
    const res  = await fetch('/1987panel/api/domains.php?action=list&limit=5', { credentials: 'include' });
    const data = await res.json();
    const el   = document.getElementById('recent-domains');
    if (!data.success || !data.domains.length) {
      el.innerHTML = '<div class="empty-state">Henüz domain eklenmedi</div>';
      return;
    }
    el.innerHTML = `
      <table class="table">
        <thead><tr><th>Domain</th><th>Durum</th><th>SSL</th><th>Eklenme</th></tr></thead>
        <tbody>
          ${data.domains.map(d => `
            <tr>
              <td><a href="/1987panel/pages/domains.php">${esc(d.domain)}</a></td>
              <td><span class="badge badge-${d.active ? 'green' : 'red'}">${d.active ? 'Aktif' : 'Pasif'}</span></td>
              <td><span class="badge badge-${d.ssl ? 'green' : 'dim'}">${d.ssl ? 'SSL' : 'Yok'}</span></td>
              <td style="color:var(--dim);font-size:.72rem;">${fmtDate(d.created_at)}</td>
            </tr>`).join('')}
        </tbody>
      </table>`;
  } catch(e) { console.error(e); }
}

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtDate(s) { return new Date(s).toLocaleDateString('tr-TR', { day:'2-digit', month:'short', year:'numeric' }); }

function showToast(msg, type = 'success') {
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 400); }, 3000);
}

loadStats();
loadRecentDomains();
