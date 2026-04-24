async function load() {
  const res  = await fetch('/1987panel/api/databases.php?action=list', { credentials: 'include' });
  const data = await res.json();
  const el   = document.getElementById('db-list');
  if (!data.success || !data.databases.length) {
    el.innerHTML = '<div class="empty-state"><i class="fas fa-database" style="font-size:2rem;margin-bottom:12px;display:block;opacity:.2;"></i>Henüz veritabanı oluşturulmadı</div>';
    return;
  }
  el.innerHTML = data.databases.map(d => `
    <div class="db-card" id="dbc-${d.id}">
      <div>
        <div class="db-name"><i class="fas fa-database" style="color:var(--gold);margin-right:8px;font-size:.8rem;"></i>${esc(d.db_name)}</div>
        <div class="db-meta">
          <span><i class="fas fa-user" style="margin-right:4px;"></i>${esc(d.db_user)}</span>
          <span><i class="fas fa-server" style="margin-right:4px;"></i>${esc(d.db_host)}</span>
          <span><i class="fas fa-calendar" style="margin-right:4px;"></i>${fmtDate(d.created_at)}</span>
          ${d.username ? `<span><i class="fas fa-user-tie" style="margin-right:4px;"></i>${esc(d.username)}</span>` : ''}
        </div>
      </div>
      <div class="db-actions">
        <button class="btn btn-red btn-sm" onclick="deleteDb(${d.id}, '${esc(d.db_name)}')"><i class="fas fa-trash"></i></button>
      </div>
    </div>`).join('');
}

function openModal() { document.getElementById('modal').classList.add('open'); document.getElementById('inp-dbname').focus(); }
function closeModal() {
  document.getElementById('modal').classList.remove('open');
  ['inp-dbname','inp-dbuser','inp-dbpass'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('modal-error').style.display = 'none';
}

async function saveDb() {
  const db_name = document.getElementById('inp-dbname').value.trim();
  const db_user = document.getElementById('inp-dbuser').value.trim();
  const db_pass = document.getElementById('inp-dbpass').value.trim();
  const errEl   = document.getElementById('modal-error');
  const btn     = document.getElementById('modal-save-btn');

  if (!db_name || !db_user) { errEl.textContent = 'DB adı ve kullanıcı gerekli.'; errEl.style.display = 'block'; return; }

  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Oluşturuluyor...';
  errEl.style.display = 'none';

  try {
    const res  = await fetch('/1987panel/api/databases.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
      body: JSON.stringify({ action: 'create', db_name, db_user, db_pass }),
    });
    const data = await res.json();
    if (data.success) {
      closeModal();
      // Bağlantı bilgilerini göster
      document.getElementById('cred-box').innerHTML = `
        <div class="cred-row"><span class="cred-label">Host:</span><span class="cred-value">localhost</span></div>
        <div class="cred-row"><span class="cred-label">Port:</span><span class="cred-value">3306</span></div>
        <div class="cred-row"><span class="cred-label">Veritabanı:</span><span class="cred-value">${esc(data.db_name)}</span></div>
        <div class="cred-row"><span class="cred-label">Kullanıcı:</span><span class="cred-value">${esc(data.db_user)}</span></div>
        <div class="cred-row"><span class="cred-label">Şifre:</span><span class="cred-value">${esc(data.db_pass)}</span></div>
        <div class="cred-row"><span class="cred-label">phpMyAdmin:</span><span class="cred-value" style="font-size:.7rem;">Sunucu IP:8080/phpmyadmin</span></div>`;
      document.getElementById('cred-modal').classList.add('open');
      load();
    } else { errEl.textContent = data.error || 'Hata.'; errEl.style.display = 'block'; }
  } catch { errEl.textContent = 'Bağlantı hatası.'; errEl.style.display = 'block'; }
  finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Oluştur'; }
}

async function deleteDb(id, name) {
  if (!confirm(`"${name}" veritabanı silinecek. Emin misin?`)) return;
  const res  = await fetch('/1987panel/api/databases.php', {
    method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include',
    body: JSON.stringify({ action: 'delete', id }),
  });
  const data = await res.json();
  if (data.success) { showToast('Veritabanı silindi'); load(); }
  else showToast(data.error || 'Hata', 'error');
}

document.getElementById('modal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal(); });

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtDate(s) { return new Date(s).toLocaleDateString('tr-TR', { day:'2-digit', month:'short', year:'numeric' }); }
function showToast(msg, type='success') {
  const t = document.createElement('div'); t.className = `toast toast-${type}`; t.textContent = msg;
  document.body.appendChild(t); setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 400); }, 3000);
}
function logout() {
  fetch('/1987panel/api/auth.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'logout'}) })
    .then(() => window.location.href = '/1987panel/index.php');
}

load();
