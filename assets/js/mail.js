async function load() {
  const res  = await fetch('/1987panel/api/mail.php?action=domains', { credentials: 'include' });
  const data = await res.json();
  const el   = document.getElementById('mail-list');
  if (!data.success || !data.domains.length) {
    el.innerHTML = '<div class="empty-state"><i class="fas fa-envelope" style="font-size:2rem;margin-bottom:12px;display:block;opacity:.2;"></i>Henüz mail domain eklenmedi</div>';
    return;
  }
  el.innerHTML = data.domains.map(d => `
    <div class="mail-domain-card" id="mdc-${d.id}">
      <div class="mail-domain-header" onclick="toggleDomain(${d.id})">
        <div class="mail-domain-name">
          <i class="fas fa-envelope" style="color:var(--gold);font-size:.8rem;"></i>
          ${esc(d.domain)}
          <span class="badge badge-dim">${d.account_count} hesap</span>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
          <button class="btn btn-gold btn-sm" onclick="event.stopPropagation();openAccountModal(${d.id},'${esc(d.domain)}')">
            <i class="fas fa-plus"></i> Hesap Ekle
          </button>
          <button class="btn btn-red btn-sm" onclick="event.stopPropagation();deleteMailDomain(${d.id},'${esc(d.domain)}')">
            <i class="fas fa-trash"></i>
          </button>
          <i class="fas fa-chevron-down" style="color:var(--dim);font-size:.7rem;" id="chev-${d.id}"></i>
        </div>
      </div>
      <div class="mail-domain-body" id="mdb-${d.id}">
        <div id="accounts-${d.id}"><div style="color:var(--dim);font-size:.75rem;">Yükleniyor...</div></div>
      </div>
    </div>`).join('');
}

async function toggleDomain(id) {
  const body = document.getElementById('mdb-' + id);
  const chev = document.getElementById('chev-' + id);
  const isOpen = body.classList.contains('open');
  body.classList.toggle('open');
  chev.style.transform = isOpen ? '' : 'rotate(180deg)';
  if (!isOpen) await loadAccounts(id);
}

async function loadAccounts(domainId) {
  const res  = await fetch(`/api/mail.php?action=accounts&domain_id=${domainId}`, { credentials: 'include' });
  const data = await res.json();
  const el   = document.getElementById('accounts-' + domainId);
  if (!data.success || !data.accounts.length) {
    el.innerHTML = '<div style="color:var(--dim);font-size:.75rem;padding:8px 0;">Henüz hesap yok</div>';
    return;
  }
  el.innerHTML = data.accounts.map(a => `
    <div class="mail-account-row">
      <div>
        <div class="mail-account-email">${esc(a.email)}</div>
        <div class="mail-account-meta">Kota: ${a.quota_mb} MB · ${fmtDate(a.created_at)}</div>
      </div>
      <div style="display:flex;gap:8px;">
        <button class="btn btn-ghost btn-sm" onclick="changePass(${a.id})"><i class="fas fa-key"></i> Şifre</button>
        <button class="btn btn-red btn-sm" onclick="deleteAccount(${a.id},'${esc(a.email)}')"><i class="fas fa-trash"></i></button>
      </div>
    </div>`).join('');
}

function openDomainModal() { document.getElementById('domain-modal').classList.add('open'); }
function closeDomainModal() { document.getElementById('domain-modal').classList.remove('open'); document.getElementById('inp-maildomain').value = ''; document.getElementById('domain-modal-error').style.display = 'none'; }

async function saveMailDomain() {
  const domain = document.getElementById('inp-maildomain').value.trim();
  const errEl  = document.getElementById('domain-modal-error');
  const btn    = document.getElementById('domain-save-btn');
  if (!domain) { errEl.textContent = 'Domain gerekli.'; errEl.style.display = 'block'; return; }
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  try {
    const res  = await fetch('/1987panel/api/mail.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'add_domain', domain}) });
    const data = await res.json();
    if (data.success) { closeDomainModal(); showToast('Mail domain eklendi'); load(); }
    else { errEl.textContent = data.error || 'Hata.'; errEl.style.display = 'block'; }
  } catch { errEl.textContent = 'Bağlantı hatası.'; errEl.style.display = 'block'; }
  finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Ekle'; }
}

async function deleteMailDomain(id, name) {
  if (!confirm(`"${name}" mail domain silinecek. Tüm hesaplar da silinir. Emin misin?`)) return;
  const res  = await fetch('/1987panel/api/mail.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'delete_domain', id}) });
  const data = await res.json();
  if (data.success) { showToast('Domain silindi'); load(); }
  else showToast(data.error || 'Hata', 'error');
}

function openAccountModal(domainId, domain) {
  document.getElementById('inp-domain-id').value = domainId;
  document.getElementById('domain-label').textContent = domain;
  document.getElementById('account-modal').classList.add('open');
  document.getElementById('inp-mailuser').focus();
}
function closeAccountModal() { document.getElementById('account-modal').classList.remove('open'); }

async function saveMailAccount() {
  const domainId = document.getElementById('inp-domain-id').value;
  const username = document.getElementById('inp-mailuser').value.trim();
  const password = document.getElementById('inp-mailpass').value;
  const quota_mb = document.getElementById('inp-quota').value;
  const errEl    = document.getElementById('account-modal-error');
  const btn      = document.getElementById('account-save-btn');
  if (!username || !password) { errEl.textContent = 'Kullanıcı adı ve şifre gerekli.'; errEl.style.display = 'block'; return; }
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  try {
    const res  = await fetch('/1987panel/api/mail.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'add_account', domain_id: +domainId, username, password, quota_mb: +quota_mb}) });
    const data = await res.json();
    if (data.success) { closeAccountModal(); showToast('Hesap oluşturuldu: ' + data.email); load(); }
    else { errEl.textContent = data.error || 'Hata.'; errEl.style.display = 'block'; }
  } catch { errEl.textContent = 'Bağlantı hatası.'; errEl.style.display = 'block'; }
  finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Ekle'; }
}

async function changePass(id) {
  const pass = prompt('Yeni şifre (en az 8 karakter):');
  if (!pass || pass.length < 8) return;
  const res  = await fetch('/1987panel/api/mail.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'change_password', id, password: pass}) });
  const data = await res.json();
  if (data.success) showToast('Şifre güncellendi');
  else showToast(data.error || 'Hata', 'error');
}

async function deleteAccount(id, email) {
  if (!confirm(`"${email}" silinecek. Emin misin?`)) return;
  const res  = await fetch('/1987panel/api/mail.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'delete_account', id}) });
  const data = await res.json();
  if (data.success) { showToast('Hesap silindi'); load(); }
  else showToast(data.error || 'Hata', 'error');
}

document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', e => { if (e.target === e.currentTarget) { m.classList.remove('open'); } }));

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
