async function load() {
  const res  = await fetch('/1987panel/api/users.php?action=list', { credentials: 'include' });
  const data = await res.json();
  const el   = document.getElementById('user-list');
  if (!data.success || !data.users.length) {
    el.innerHTML = '<div class="empty-state">Kullanıcı yok</div>';
    return;
  }
  el.innerHTML = data.users.map(u => `
    <div class="user-card ${u.active ? '' : 'inactive'}" id="uc-${u.id}">
      <div class="user-avatar">${esc(u.username.charAt(0).toUpperCase())}</div>
      <div class="user-info">
        <div class="user-name">${esc(u.username)} <span class="badge badge-${u.role === 'admin' ? 'gold' : 'dim'}">${u.role === 'admin' ? 'Admin' : 'Kullanıcı'}</span></div>
        <div class="user-email">${esc(u.email)}</div>
        <div class="user-meta">
          <span>${u.active ? '<span style="color:var(--green);">Aktif</span>' : '<span style="color:var(--red);">Pasif</span>'}</span>
          <span>Son giriş: ${u.last_login ? fmtDate(u.last_login) : 'Hiç'}</span>
          <span>Kayıt: ${fmtDate(u.created_at)}</span>
        </div>
      </div>
      <div class="user-actions">
        <button class="btn btn-ghost btn-sm" onclick="resetPass(${u.id})"><i class="fas fa-key"></i> Şifre</button>
        <button class="btn btn-ghost btn-sm" onclick="toggleRole(${u.id},'${u.role}')">
          <i class="fas fa-${u.role === 'admin' ? 'user-minus' : 'user-shield'}"></i>
          ${u.role === 'admin' ? 'Admin Kaldır' : 'Admin Yap'}
        </button>
        <button class="btn btn-ghost btn-sm" onclick="toggleActive(${u.id})">
          <i class="fas fa-${u.active ? 'ban' : 'check'}"></i>
          ${u.active ? 'Askıya Al' : 'Aktif Et'}
        </button>
        <button class="btn btn-red btn-sm" onclick="deleteUser(${u.id},'${esc(u.username)}')"><i class="fas fa-trash"></i></button>
      </div>
    </div>`).join('');
}

function openModal() { document.getElementById('modal').classList.add('open'); document.getElementById('inp-uname').focus(); }
function closeModal() {
  document.getElementById('modal').classList.remove('open');
  ['inp-uname','inp-uemail','inp-upass'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('modal-error').style.display = 'none';
}

async function saveUser() {
  const username = document.getElementById('inp-uname').value.trim();
  const email    = document.getElementById('inp-uemail').value.trim();
  const password = document.getElementById('inp-upass').value;
  const role     = document.getElementById('inp-role').value;
  const errEl    = document.getElementById('modal-error');
  const btn      = document.getElementById('modal-save-btn');
  if (!username || !email || !password) { errEl.textContent = 'Tüm alanlar gerekli.'; errEl.style.display = 'block'; return; }
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  try {
    const res  = await fetch('/1987panel/api/users.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'create', username, email, password, role}) });
    const data = await res.json();
    if (data.success) { closeModal(); showToast('Kullanıcı oluşturuldu'); load(); }
    else { errEl.textContent = data.error || 'Hata.'; errEl.style.display = 'block'; }
  } catch { errEl.textContent = 'Bağlantı hatası.'; errEl.style.display = 'block'; }
  finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Kaydet'; }
}

async function toggleActive(id) {
  const res  = await fetch('/1987panel/api/users.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'toggle_active', id}) });
  const data = await res.json();
  if (data.success) { showToast('Durum güncellendi'); load(); }
  else showToast(data.error || 'Hata', 'error');
}

async function toggleRole(id, currentRole) {
  const newRole = currentRole === 'admin' ? 'user' : 'admin';
  if (!confirm(`Rol "${newRole}" olarak değiştirilecek. Emin misin?`)) return;
  const res  = await fetch('/1987panel/api/users.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'change_role', id, role: newRole}) });
  const data = await res.json();
  if (data.success) { showToast('Rol güncellendi'); load(); }
  else showToast(data.error || 'Hata', 'error');
}

async function resetPass(id) {
  const pass = prompt('Yeni şifre (en az 8 karakter):');
  if (!pass || pass.length < 8) return;
  const res  = await fetch('/1987panel/api/users.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'reset_password', id, password: pass}) });
  const data = await res.json();
  if (data.success) showToast('Şifre güncellendi');
  else showToast(data.error || 'Hata', 'error');
}

async function deleteUser(id, name) {
  if (!confirm(`"${name}" silinecek. Emin misin?`)) return;
  const res  = await fetch('/1987panel/api/users.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'delete', id}) });
  const data = await res.json();
  if (data.success) { showToast('Kullanıcı silindi'); load(); }
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
