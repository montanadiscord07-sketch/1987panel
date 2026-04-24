document.getElementById('inp-rtype')?.addEventListener('change', function() {
  document.getElementById('priority-group').style.display = this.value === 'MX' ? 'block' : 'none';
});

async function load() {
  const res  = await fetch('/1987panel/api/dns.php?action=zones', { credentials: 'include' });
  const data = await res.json();
  const el   = document.getElementById('dns-list');
  if (!data.success || !data.zones.length) {
    el.innerHTML = '<div class="empty-state"><i class="fas fa-network-wired" style="font-size:2rem;margin-bottom:12px;display:block;opacity:.2;"></i>Henüz DNS zone eklenmedi</div>';
    return;
  }
  el.innerHTML = data.zones.map(z => `
    <div class="dns-zone-card" id="dzc-${z.id}">
      <div class="dns-zone-header" onclick="toggleZone(${z.id})">
        <div class="dns-zone-name">
          <i class="fas fa-network-wired" style="color:var(--gold);font-size:.8rem;"></i>
          ${esc(z.domain)}
          <span class="badge badge-dim">${z.record_count} kayıt</span>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
          <button class="btn btn-gold btn-sm" onclick="event.stopPropagation();openRecordModal(${z.id})"><i class="fas fa-plus"></i> Kayıt Ekle</button>
          <button class="btn btn-red btn-sm" onclick="event.stopPropagation();deleteZone(${z.id},'${esc(z.domain)}')"><i class="fas fa-trash"></i></button>
          <i class="fas fa-chevron-down" style="color:var(--dim);font-size:.7rem;" id="zchev-${z.id}"></i>
        </div>
      </div>
      <div class="dns-zone-body" id="dzb-${z.id}">
        <div id="records-${z.id}"><div style="color:var(--dim);font-size:.75rem;">Yükleniyor...</div></div>
      </div>
    </div>`).join('');
}

async function toggleZone(id) {
  const body = document.getElementById('dzb-' + id);
  const chev = document.getElementById('zchev-' + id);
  const isOpen = body.classList.contains('open');
  body.classList.toggle('open');
  chev.style.transform = isOpen ? '' : 'rotate(180deg)';
  if (!isOpen) await loadRecords(id);
}

async function loadRecords(zoneId) {
  const res  = await fetch(`/api/dns.php?action=records&zone_id=${zoneId}`, { credentials: 'include' });
  const data = await res.json();
  const el   = document.getElementById('records-' + zoneId);
  if (!data.success || !data.records.length) { el.innerHTML = '<div style="color:var(--dim);font-size:.75rem;padding:8px 0;">Kayıt yok</div>'; return; }
  el.innerHTML = `
    <table class="table" style="margin-top:8px;">
      <thead><tr><th>Tür</th><th>Ad</th><th>Değer</th><th>TTL</th><th></th></tr></thead>
      <tbody>
        ${data.records.map(r => `
          <tr>
            <td><span class="record-type-badge rt-${r.type}">${r.type}</span></td>
            <td style="font-family:monospace;font-size:.78rem;">${esc(r.name)}</td>
            <td style="font-family:monospace;font-size:.78rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;">${esc(r.value)}</td>
            <td style="color:var(--dim);font-size:.72rem;">${r.ttl}</td>
            <td><button class="btn btn-red btn-sm" onclick="deleteRecord(${r.id},${zoneId})"><i class="fas fa-trash"></i></button></td>
          </tr>`).join('')}
      </tbody>
    </table>`;
}

function openZoneModal() { document.getElementById('zone-modal').classList.add('open'); document.getElementById('inp-zone').focus(); }
function closeZoneModal() { document.getElementById('zone-modal').classList.remove('open'); document.getElementById('inp-zone').value = ''; document.getElementById('zone-modal-error').style.display = 'none'; }

async function saveZone() {
  const domain = document.getElementById('inp-zone').value.trim();
  const errEl  = document.getElementById('zone-modal-error');
  const btn    = document.getElementById('zone-save-btn');
  if (!domain) { errEl.textContent = 'Domain gerekli.'; errEl.style.display = 'block'; return; }
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  try {
    const res  = await fetch('/1987panel/api/dns.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'add_zone', domain}) });
    const data = await res.json();
    if (data.success) { closeZoneModal(); showToast('Zone eklendi'); load(); }
    else { errEl.textContent = data.error || 'Hata.'; errEl.style.display = 'block'; }
  } catch { errEl.textContent = 'Bağlantı hatası.'; errEl.style.display = 'block'; }
  finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Ekle'; }
}

async function deleteZone(id, name) {
  if (!confirm(`"${name}" zone silinecek. Emin misin?`)) return;
  const res  = await fetch('/1987panel/api/dns.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'delete_zone', id}) });
  const data = await res.json();
  if (data.success) { showToast('Zone silindi'); load(); }
  else showToast(data.error || 'Hata', 'error');
}

function openRecordModal(zoneId) { document.getElementById('inp-zone-id').value = zoneId; document.getElementById('record-modal').classList.add('open'); }
function closeRecordModal() { document.getElementById('record-modal').classList.remove('open'); document.getElementById('record-modal-error').style.display = 'none'; }

async function saveRecord() {
  const zoneId   = document.getElementById('inp-zone-id').value;
  const type     = document.getElementById('inp-rtype').value;
  const name     = document.getElementById('inp-rname').value.trim() || '@';
  const value    = document.getElementById('inp-rvalue').value.trim();
  const ttl      = document.getElementById('inp-ttl').value;
  const priority = document.getElementById('inp-priority').value;
  const errEl    = document.getElementById('record-modal-error');
  const btn      = document.getElementById('record-save-btn');
  if (!value) { errEl.textContent = 'Değer gerekli.'; errEl.style.display = 'block'; return; }
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  try {
    const res  = await fetch('/1987panel/api/dns.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'add_record', zone_id:+zoneId, type, name, value, ttl:+ttl, priority:+priority}) });
    const data = await res.json();
    if (data.success) { closeRecordModal(); showToast('Kayıt eklendi'); await loadRecords(+zoneId); }
    else { errEl.textContent = data.error || 'Hata.'; errEl.style.display = 'block'; }
  } catch { errEl.textContent = 'Bağlantı hatası.'; errEl.style.display = 'block'; }
  finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Ekle'; }
}

async function deleteRecord(id, zoneId) {
  if (!confirm('Bu kayıt silinecek. Emin misin?')) return;
  const res  = await fetch('/1987panel/api/dns.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'delete_record', id}) });
  const data = await res.json();
  if (data.success) { showToast('Kayıt silindi'); await loadRecords(zoneId); }
  else showToast(data.error || 'Hata', 'error');
}

document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', e => { if (e.target === e.currentTarget) m.classList.remove('open'); }));

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
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
