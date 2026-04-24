async function load() {
  const res  = await fetch('/1987panel/api/domains.php?action=list', { credentials: 'include' });
  const data = await res.json();
  const el   = document.getElementById('domain-list');

  if (!data.success || !data.domains.length) {
    el.innerHTML = '<div class="empty-state"><i class="fas fa-globe" style="font-size:2rem;margin-bottom:12px;display:block;opacity:.2;"></i>Henüz domain eklenmedi</div>';
    return;
  }

  el.innerHTML = '<div class="domain-grid">' + data.domains.map(d => `
    <div class="domain-card ${d.active ? '' : 'inactive'}" id="dc-${d.id}">
      <div>
        <div class="domain-name">
          <i class="fas fa-globe" style="color:var(--gold);font-size:.8rem;"></i>
          ${esc(d.domain)}
          ${d.ssl ? '<span class="badge badge-green">SSL</span>' : ''}
          ${!d.active ? '<span class="badge badge-dim">Pasif</span>' : ''}
        </div>
        <div class="domain-meta">
          <span><i class="fas fa-folder" style="margin-right:4px;"></i>${esc(d.doc_root)}</span>
          <span><i class="fas fa-code" style="margin-right:4px;"></i>PHP ${esc(d.php_ver)}</span>
          <span><i class="fas fa-calendar" style="margin-right:4px;"></i>${fmtDate(d.created_at)}</span>
          ${d.username ? `<span><i class="fas fa-user" style="margin-right:4px;"></i>${esc(d.username)}</span>` : ''}
        </div>
      </div>
      <div class="domain-actions">
        <a href="http://${esc(d.domain)}" target="_blank" class="btn btn-ghost btn-sm">
          <i class="fas fa-external-link-alt"></i>
        </a>
        <button class="btn btn-ghost btn-sm" onclick="toggleDomain(${d.id})">
          <i class="fas fa-${d.active ? 'pause' : 'play'}"></i>
        </button>
        <button class="btn btn-red btn-sm" onclick="deleteDomain(${d.id}, '${esc(d.domain)}')">
          <i class="fas fa-trash"></i>
        </button>
      </div>
    </div>`).join('') + '</div>';
}

function openModal() {
  document.getElementById('modal').classList.add('open');
  document.getElementById('inp-domain').focus();
}

function closeModal() {
  document.getElementById('modal').classList.remove('open');
  document.getElementById('inp-domain').value = '';
  document.getElementById('modal-error').style.display = 'none';
}

async function saveDomain() {
  const domain  = document.getElementById('inp-domain').value.trim();
  const php_ver = document.getElementById('inp-php').value;
  const errEl   = document.getElementById('modal-error');
  const btn     = document.getElementById('modal-save-btn');

  if (!domain) { errEl.textContent = 'Domain adı gerekli.'; errEl.style.display = 'block'; return; }

  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ekleniyor...';
  errEl.style.display = 'none';

  try {
    const res  = await fetch('/1987panel/api/domains.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ action: 'create', domain, php_ver }),
    });
    const data = await res.json();
    if (data.success) {
      closeModal();
      showToast('Domain eklendi: ' + domain);
      load();
    } else {
      errEl.textContent = data.error || 'Hata oluştu.';
      errEl.style.display = 'block';
    }
  } catch { errEl.textContent = 'Bağlantı hatası.'; errEl.style.display = 'block'; }
  finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Kaydet'; }
}

async function toggleDomain(id) {
  const res  = await fetch('/1987panel/api/domains.php', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ action: 'toggle', id }),
  });
  const data = await res.json();
  if (data.success) { showToast('Durum güncellendi'); load(); }
  else showToast(data.error || 'Hata', 'error');
}

async function deleteDomain(id, name) {
  if (!confirm(`"${name}" silinecek. Tüm dosyalar da silinir. Emin misin?`)) return;
  const res  = await fetch('/1987panel/api/domains.php', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ action: 'delete', id }),
  });
  const data = await res.json();
  if (data.success) { showToast('Domain silindi'); load(); }
  else showToast(data.error || 'Hata', 'error');
}

// Modal dışına tıklayınca kapat
document.getElementById('modal').addEventListener('click', (e) => {
  if (e.target === e.currentTarget) closeModal();
});

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
function logout() {
  fetch('/1987panel/api/auth.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'logout'}) })
    .then(() => window.location.href = '/1987panel/index.php');
}

load();
