let currentPath = '/';
let editingPath = '';

async function load(path) {
  currentPath = path || currentPath;
  document.getElementById('current-path').textContent = currentPath;
  updateBreadcrumb();

  const res  = await fetch(`/api/files.php?action=list&path=${encodeURIComponent(currentPath)}`, { credentials: 'include' });
  const data = await res.json();
  const el   = document.getElementById('file-list');

  if (!data.success) { el.innerHTML = `<div class="empty-state">${esc(data.error || 'Hata')}</div>`; return; }
  if (!data.items.length) { el.innerHTML = '<div class="empty-state">Boş dizin</div>'; return; }

  const icons = { dir:'fa-folder', php:'fa-code', html:'fa-code', css:'fa-palette', js:'fa-file-code', txt:'fa-file-alt', sql:'fa-database', zip:'fa-file-archive', jpg:'fa-image', png:'fa-image', gif:'fa-image' };

  el.innerHTML = data.items.map(f => {
    const icon = f.type === 'dir' ? 'fa-folder' : (icons[f.ext] || 'fa-file');
    const cls  = f.type === 'dir' ? 'dir' : 'file';
    return `
      <div class="file-row">
        <div class="file-icon ${cls}"><i class="fas ${icon}"></i></div>
        <div class="file-name" onclick="${f.type === 'dir' ? `load('${esc(currentPath)}/${esc(f.name)}')` : `editFile('${esc(currentPath)}/${esc(f.name)}')`}">${esc(f.name)}</div>
        <div class="file-size">${f.type === 'file' ? fmtSize(f.size) : ''}</div>
        <div class="file-date">${f.modified}</div>
        <div class="file-actions">
          ${f.type === 'file' ? `<a href="/api/files.php?action=download&path=${encodeURIComponent(currentPath+'/'+f.name)}" class="btn btn-ghost btn-sm"><i class="fas fa-download"></i></a>` : ''}
          <button class="btn btn-red btn-sm" onclick="deleteItem('${esc(currentPath)}/${esc(f.name)}','${esc(f.name)}')"><i class="fas fa-trash"></i></button>
        </div>
      </div>`;
  }).join('');
}

function updateBreadcrumb() {
  const parts = currentPath.split('/').filter(Boolean);
  let html = `<span class="breadcrumb-item" onclick="load('/')"><i class="fas fa-home"></i></span>`;
  let acc = '';
  parts.forEach(p => {
    acc += '/' + p;
    const path = acc;
    html += `<span class="breadcrumb-sep">/</span><span class="breadcrumb-item" onclick="load('${esc(path)}')">${esc(p)}</span>`;
  });
  document.getElementById('breadcrumb').innerHTML = html;
}

async function editFile(path) {
  const res  = await fetch(`/api/files.php?action=read&path=${encodeURIComponent(path)}`, { credentials: 'include' });
  const data = await res.json();
  if (!data.success) { showToast(data.error || 'Okunamadı', 'error'); return; }
  editingPath = path;
  document.getElementById('edit-title').textContent = path.split('/').pop();
  document.getElementById('edit-content').value = data.content;
  document.getElementById('edit-modal').classList.add('open');
}

async function saveFile() {
  const content = document.getElementById('edit-content').value;
  const res  = await fetch('/1987panel/api/files.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'save', path: editingPath, content}) });
  const data = await res.json();
  if (data.success) { document.getElementById('edit-modal').classList.remove('open'); showToast('Kaydedildi'); }
  else showToast(data.error || 'Hata', 'error');
}

async function deleteItem(path, name) {
  if (!confirm(`"${name}" silinecek. Emin misin?`)) return;
  const res  = await fetch('/1987panel/api/files.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'delete', path}) });
  const data = await res.json();
  if (data.success) { showToast('Silindi'); load(); }
  else showToast(data.error || 'Hata', 'error');
}

async function newFolder() {
  const name = prompt('Klasör adı:');
  if (!name) return;
  const res  = await fetch('/1987panel/api/files.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'mkdir', path: currentPath, name}) });
  const data = await res.json();
  if (data.success) { showToast('Klasör oluşturuldu'); load(); }
  else showToast(data.error || 'Hata', 'error');
}

async function newFile() {
  const name = prompt('Dosya adı:');
  if (!name) return;
  const res  = await fetch('/1987panel/api/files.php', { method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({action:'touch', path: currentPath, name}) });
  const data = await res.json();
  if (data.success) { showToast('Dosya oluşturuldu'); load(); }
  else showToast(data.error || 'Hata', 'error');
}

function uploadFile() { document.getElementById('file-input').click(); }

async function doUpload(input) {
  const formData = new FormData();
  formData.append('path', currentPath);
  Array.from(input.files).forEach(f => formData.append('files[]', f));
  const res  = await fetch('/1987panel/api/files.php?action=upload', { method:'POST', credentials:'include', body: formData });
  const data = await res.json();
  if (data.success) { showToast(`${data.uploaded.length} dosya yüklendi`); load(); }
  else showToast(data.error || 'Hata', 'error');
  input.value = '';
}

document.getElementById('edit-modal').addEventListener('click', e => { if (e.target === e.currentTarget) e.currentTarget.classList.remove('open'); });

function fmtSize(b) { if (b < 1024) return b+'B'; if (b < 1048576) return (b/1024).toFixed(1)+'KB'; return (b/1048576).toFixed(1)+'MB'; }
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

load('/');
