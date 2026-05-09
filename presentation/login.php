<?php
require_once __DIR__ . '/../application/config.php';
if (isLoggedIn()) redirect(BASE_URL."/presentation/{$_SESSION['role']}/dashboard.php");
$flash = getFlash();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign In | Edu-Connect</title>
<link rel="stylesheet" href="<?=BASE_URL?>/presentation/css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-box">
    <div class="auth-logo">
      <h1>EduConnect</h1>
      <p class="tagline">Knowledge · Community · Growth</p>
    </div>
    <?php if ($flash): ?>
    <div class="alert alert-<?=$flash['type']==='success'?'success':'error'?>"><?=htmlspecialchars($flash['message'])?></div>
    <?php endif; ?>
    <form method="POST" action="<?=BASE_URL?>/application/auth.php">
      <input type="hidden" name="action" value="login">
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="you@university.edu" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px;padding:14px">
        Sign In &nbsp;→
      </button>
    </form>
    <div style="text-align:center;margin-top:24px;font-size:.86rem;color:var(--tx3)">
      Don't have an account?
      <a href="<?=BASE_URL?>/presentation/register.php" style="color:var(--c);font-weight:700">Create one</a>
    </div>
    <div style="margin-top:20px;padding:12px 16px;background:rgba(110,86,255,.08);
                border-radius:var(--rsm);border:1px solid var(--rim2);text-align:center">
      <span style="font-size:.75rem;color:var(--tx3)">
       Default admin: <strong style="color:var(--vb)">admin@educonnect.com</strong> / <strong style="color:var(--vb)">password</strong>
      </span>
    </div>
  </div>
</div>
</body></html>
