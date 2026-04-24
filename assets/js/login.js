document.getElementById('login-form').addEventListener('submit', async (e) => {
  e.preventDefault();

  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value;
  const errEl    = document.getElementById('login-error');
  const btn      = e.target.querySelector('button[type="submit"]');

  if (!username || !password) {
    errEl.textContent = 'Kullanıcı adı ve şifre gerekli.';
    errEl.classList.add('show');
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Giriş yapılıyor...';
  errEl.classList.remove('show');

  try {
    const res  = await fetch('/1987panel/api/auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ action: 'login', username, password }),
    });
    const data = await res.json();

    if (data.success) {
      window.location.href = '/1987panel/dashboard.php';
    } else {
      errEl.textContent = data.error || 'Giriş başarısız.';
      errEl.classList.add('show');
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Giriş Yap';
    }
  } catch {
    errEl.textContent = 'Bağlantı hatası, tekrar dene.';
    errEl.classList.add('show');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Giriş Yap';
  }
});
