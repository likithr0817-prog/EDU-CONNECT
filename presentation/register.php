<?php
require_once __DIR__ . '/../application/config.php';
if (isLoggedIn()) redirect(BASE_URL."/presentation/{$_SESSION['role']}/dashboard.php");
$flash = getFlash();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register | Edu-Connect</title>
<link rel="stylesheet" href="<?=BASE_URL?>/presentation/css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-box" style="max-width:560px">
    <div class="auth-logo">
      <h1>EduConnect</h1>
      <p class="tagline">Join the Community</p>
    </div>
    <?php if ($flash): ?>
    <div class="alert alert-<?=$flash['type']==='success'?'success':'error'?>"><?=htmlspecialchars($flash['message'])?></div>
    <?php endif; ?>
    <div class="auth-tabs">
      <button class="auth-tab active" onclick="switchRole('student',this)">Student</button>
      <button class="auth-tab" onclick="switchRole('teacher',this)">Teacher</button>
    </div>
    <form method="POST" action="<?=BASE_URL?>/application/auth.php">
      <input type="hidden" name="action" value="register">
      <input type="hidden" name="role" id="roleField" value="student">
      <div class="form-row">
        <div class="form-group"><label>Full Name</label><input type="text" name="name" class="form-control" placeholder="Your full name" required maxlength="100"></div>
        <div class="form-group"><label>Age</label><input type="number" name="age" class="form-control" placeholder="Age" min="5" max="100"></div>
      </div>
      <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" placeholder="you@email.com" required></div>
      <div class="form-row">
        <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" placeholder="Min 6 chars" required minlength="6"></div>
        <div class="form-group"><label>Confirm Password</label><input type="password" name="confirm_password" class="form-control" placeholder="Repeat" required></div>
      </div>
      <div class="form-group"><label>College / University</label><input type="text" name="college_name" class="form-control" placeholder="Your institution" maxlength="200"></div>
      <div class="form-group"><label>Contact Number</label><input type="text" name="contact_number" class="form-control" placeholder="+91 0000000000" maxlength="15"></div>
      <div id="studentFields">
        <div class="form-group"><label>Class / Section</label><input type="text" name="class_name" class="form-control" placeholder="e.g. CS-A 3rd Year" maxlength="100"></div>
      </div>
      <div id="teacherFields" style="display:none">
        <div class="form-group"><label>Subject / Department</label><input type="text" name="subject" class="form-control" placeholder="e.g. Mathematics" maxlength="100"></div>
      </div>
      <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px;padding:14px">Create Account &nbsp;→</button>
    </form>
    <div style="text-align:center;margin-top:20px;font-size:.86rem;color:var(--tx3)">
      Already registered? <a href="<?=BASE_URL?>/presentation/login.php" style="color:var(--c);font-weight:700">Sign in</a>
    </div>
    <div style="margin-top:16px;padding:12px 16px;background:rgba(110,86,255,.08);border-radius:var(--rsm);border:1px solid var(--rim2)">
      <p style="font-size:.76rem;color:var(--tx3);line-height:1.5">
        ⚡ Accounts require <strong style="color:var(--tx2)">admin approval</strong> before you can log in.
      </p>
    </div>
  </div>
</div>
<script>
function switchRole(r, btn) {
  document.querySelectorAll('.auth-tab').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('roleField').value = r;
  document.getElementById('studentFields').style.display = r==='student'?'':'none';
  document.getElementById('teacherFields').style.display = r==='teacher'?'':'none';
}
</script>
</body></html>
