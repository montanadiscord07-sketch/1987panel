<?php
require_once 'includes/auth.php';
if (isLoggedIn()) { header('Location: /1987panel/dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Giriş — 1987 Panel</title>
  <link rel="icon" type="image/png" href="/1987panel/assets/img/favicon.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/1987panel/assets/css/base.css">
  <link rel="stylesheet" href="/1987panel/assets/css/login.css">
</head>
<body>
<div class="noise-bg"></div>

<div class="login-wrap">
  <div class="login-box">

    <div class="login-logo">
      <div style="width:56px;height:56px;background:var(--gold-dim);border:3px solid var(--gold);border-radius:12px;display:flex;align-items:center;justify-content:center;font-weight:900;color:var(--gold);font-size:1.4rem;margin:0 auto 12px;">87</div>
      <div class="login-brand">1987 Web Sağlayıcısı</div>
    </div>

    <div class="login-title">Panel Girişi</div>
    <div class="login-sub">Yönetim paneline erişmek için giriş yapın</div>

    <div class="login-error" id="login-error"></div>

    <form id="login-form">
      <div class="form-group">
        <label class="form-label">Kullanıcı Adı</label>
        <input type="text" class="form-input" id="username" placeholder="admin" autocomplete="username" required>
      </div>
      <div class="form-group">
        <label class="form-label">Şifre</label>
        <input type="password" class="form-input" id="password" placeholder="••••••••" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn btn-gold login-btn">
        <i class="fas fa-sign-in-alt"></i> Giriş Yap
      </button>
    </form>

  </div>
</div>

<script src="/1987panel/assets/js/login.js"></script>
</body>
</html>
