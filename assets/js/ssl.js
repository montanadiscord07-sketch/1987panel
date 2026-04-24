async function load() {
  const res  = await fetch('/1987panel/api/ssl.php?action=list', { credentials: 'include' });
  const data = await res.json();
  const el   = document.getElementById('ssl-list');
  if (!data.success || !data.certs.length) {
    el.innerHTML = '<div class="empty-state"><i class="fas fa-lock" style="font-size:2rem;margin-bottom:12px;display:block;opacity:.2;"></i>Henüz SSL sertifikası yok</div>';
    return;
  }
  el.innerHTML = data.certs.map(c => `
    <div class="ssl-card ${c.status}" id="sslc-${c.id}">
      <div>
        <div class="ssl-domain"><i class="fas fa-lock" style="color:var(--gold);margin-right:8px;font-size:.8rem;"></i>${esc(c.domain)}</div>
        <div class="ssl-meta">
          <span><span class="badge badge-${c.status === 'active' ? 'green' : c.status === 'expired' ? 'red' : 'gold'}">${statusLabel(c.status)}</span></span>
          ${c.issued_at  ? `<span><i class="fas fa-calendar-check" style="margin-right:4px;"></i>Yayınlandı: ${fmtDate(c.issued_at)}</span>` : ''}
          ${c.expires_at ? `<span><i class="fas fa-calendar-times" style="margin-right:4px;"></i>Bitiş: ${fmtDate(c.expires_at)}</span>` : ''}
          ${c.username   ? `<span><i class="fas fa-user" style="margin-right:4px;"></i>${esc(c.username)}</span>` : ''}
        </div>
      </div>
      <div class="ssl-actions">
        ${c.status === 'active' ? `<button class="btn btn-red btn-sm" onclick="revokeSSL(${c.id},'${esc(c.domain)}')"><i class="fas fa-times"></i> İptal</button>` : ''}
      </div>
    </div>`).join('');
}

async function openModal() {
  // Domain listesini çek
  const res  = await fetch('/1987panel/api/domains.php?action=list', { credentials: 'include' });
  const data = await res.json();
  const sel  = document.getElementById('inp-ssldomain');
  sel.innerHTML = '<option value="">Domain seçin...</option>';
  if (data.success) {
    data.domains.filter(d => !d.ssl).forEach(d => {
      const opt = document.createElement('option');
      opt.value = d.domain;
      opt.textContent = d.domain;
      sel.appendChild(opt);
    });
  }
  document.getElementById('modal').classList.add('open');
}

function closeModal() { document.getElementById('modal').classList.remove('open'); document.getElementById('modal-error').style.display = 'none'; document.getElementById('ssl-progress').classList.remove('show'); }

async function issueSSL() {
  const domain = document.getElementById('inp-ssldomain').value;
  const email  = document.getElementById('inp-sslemail').value.trim();
  const errEl  = document.getElementById('modal-error');
  const btn    = document.getElementById('modal-save-btn');
  const prog   = document.getElementById('ssl-progress');

  if (!domain || !email) { errEl.textContent = 'Domain ve email gerekli.'; errEl.style.display = 'block'; return; }

  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Yayınlanıyor...';
  errEl.style.display = 'none';
  prog.classList.add('show');

  try {
    const res  = await fetch('/1987panel/api/ssl.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'issue', domain, email}) });
    const data = await res.json();
    if (data.success) { closeModal(); showToast('SSL sertifikası yayınlandı!'); load(); }
    else { errEl.textContent = data.error || 'Hata.'; errEl.style.display = 'block'; prog.classList.remove('show'); }
  } catch { errEl.textContent = 'Bağlantı hatası.'; errEl.style.display = 'block'; prog.classList.remove('show'); }
  finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-lock"></i> Yayınla'; }
}

async function revokeSSL(id, domain) {
  if (!confirm(`"${domain}" SSL sertifikası iptal edilecek. Emin misin?`)) return;
  const res  = await fetch('/1987panel/api/ssl.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'revoke', id}) });
  const data = await res.json();
  if (data.success) { showToast('SSL iptal edildi'); load(); }
  else showToast(data.error || 'Hata', 'error');
}

function statusLabel(s) { return {active:'Aktif', expired:'Süresi Dolmuş', pending:'Bekliyor', failed:'Başarısız'}[s] || s; }
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
