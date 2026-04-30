<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
admin_session_start();

if (admin_user()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$emailVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } else {
        $emailVal = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($emailVal === '' || $password === '') {
            $error = 'Email and password are required.';
        } else {
            [$ok, $msg] = admin_login($emailVal, $password);
            if ($ok) {
                header('Location: dashboard.php');
                exit;
            }
            $error = $msg;
        }
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign In — SLS IT Solutions Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/admin.css">
<link rel="icon" href="../assets/images/logo-icon.svg" type="image/svg+xml">
</head>
<body>

<div class="login-page">

  <!-- ============== Hero side (image / brand) ============== -->
  <div class="login-hero">
    <div class="hero-top">
      <div class="hero-logo">
        <div style="background:#fff;border-radius:10px;padding:6px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;">
          <img src="../assets/images/logo.png" alt="SLS" style="max-width:100%;max-height:100%;">
        </div>
        <div>
          <div style="font-weight:700;font-size:16px;line-height:1;font-family:'Poppins',sans-serif;">SLS IT Solutions</div>
          <div style="color:#94a3b8;font-size:11px;letter-spacing:0.6px;text-transform:uppercase;">Admin Control Panel</div>
        </div>
      </div>
    </div>

    <div class="hero-mid">
      <h1>Manage your business with confidence.</h1>
      <p>Track customer enquiries, update testimonials, and stay on top of everything happening on your website — all from one secure panel.</p>

      <div class="hero-features">
        <div class="feat">
          <i class="fa-solid fa-shield-halved"></i>
          <div><strong>Secure Access</strong><span>Encrypted &amp; protected</span></div>
        </div>
        <div class="feat">
          <i class="fa-solid fa-bolt"></i>
          <div><strong>Real-time</strong><span>Live enquiries inbox</span></div>
        </div>
        <div class="feat">
          <i class="fa-solid fa-star"></i>
          <div><strong>Testimonials</strong><span>One-click control</span></div>
        </div>
        <div class="feat">
          <i class="fa-solid fa-headset"></i>
          <div><strong>24/7 Support</strong><span>Always here for you</span></div>
        </div>
      </div>
    </div>

    <div class="hero-bot">© <?= date('Y') ?> SLS IT Solutions. All rights reserved.</div>
  </div>

  <!-- ============== Form side ============== -->
  <div class="login-form-side">
    <div class="login-card">

      <div class="lc-head">
        <h2>Welcome back 👋</h2>
        <p>Sign in to your admin account.</p>
      </div>

      <div class="login-error" id="loginError" style="<?= $error ? '' : 'display:none' ?>">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span id="loginErrorText"><?= htmlspecialchars($error) ?></span>
      </div>

      <form method="post" id="loginForm" novalidate>
        <?= csrf_field() ?>

        <!-- Step 1: Email -->
        <div class="login-step <?= $emailVal && $error !== '' ? '' : 'active' ?>" id="stepEmail">
          <div class="field">
            <label for="email">Email Address</label>
            <div class="input-group">
              <i class="fa-solid fa-envelope ig-icon"></i>
              <input class="ig-input" type="email" id="email" name="email" required autofocus
                     value="<?= htmlspecialchars($emailVal) ?>" placeholder="you@slsitsolutions.com" autocomplete="username">
            </div>
          </div>
          <button type="button" class="btn btn-primary btn-block" id="continueBtn">
            <span>Continue</span>
            <i class="fa-solid fa-arrow-right"></i>
          </button>
        </div>

        <!-- Step 2: Password -->
        <div class="login-step <?= $emailVal && $error !== '' ? 'active' : '' ?>" id="stepPassword">
          <div class="identity-pill">
            <div class="av" id="emAv">A</div>
            <div class="em" id="emText"><?= htmlspecialchars($emailVal) ?></div>
            <button type="button" class="change" onclick="goBackToEmail()">Change</button>
          </div>

          <div class="field">
            <label for="password">Password</label>
            <div class="input-group has-end">
              <i class="fa-solid fa-lock ig-icon"></i>
              <input class="ig-input" type="password" id="password" name="password" required
                     placeholder="Enter your password" autocomplete="current-password">
              <button type="button" class="ig-end" id="togglePw" data-tip="Show/hide">
                <i class="fa-regular fa-eye" id="pwIcon"></i>
              </button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block" id="signInBtn">
            <i class="fa-solid fa-right-to-bracket"></i>
            <span>Sign In</span>
          </button>
        </div>

      </form>

      <div style="text-align:center;margin-top:22px;color:var(--text-mute);font-size:13px;">
        <i class="fa-solid fa-shield-halved" style="color:var(--accent);"></i>
        Protected by 256-bit encryption
      </div>

    </div>
  </div>

</div>

<script>
(function(){
  var stepEmail    = document.getElementById('stepEmail');
  var stepPassword = document.getElementById('stepPassword');
  var emailInput   = document.getElementById('email');
  var pwInput      = document.getElementById('password');
  var continueBtn  = document.getElementById('continueBtn');
  var errorBox     = document.getElementById('loginError');
  var errorText    = document.getElementById('loginErrorText');
  var emAv         = document.getElementById('emAv');
  var emText       = document.getElementById('emText');
  var togglePw     = document.getElementById('togglePw');
  var pwIcon       = document.getElementById('pwIcon');
  var form         = document.getElementById('loginForm');

  function showError(msg){
    errorText.textContent = msg;
    errorBox.style.display = 'flex';
  }
  function hideError(){ errorBox.style.display = 'none'; }

  function showStep(which){
    stepEmail.classList.toggle('active', which === 'email');
    stepPassword.classList.toggle('active', which === 'password');
    if (which === 'password') {
      setTimeout(function(){ pwInput.focus(); }, 60);
    } else {
      setTimeout(function(){ emailInput.focus(); }, 60);
    }
  }

  function checkEmail(){
    hideError();
    var email = (emailInput.value || '').trim();
    if (!email) { showError('Please enter your email address.'); return; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      showError('Please enter a valid email address.'); return;
    }

    var orig = continueBtn.innerHTML;
    continueBtn.disabled = true;
    continueBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Checking...</span>';

    var fd = new FormData();
    fd.append('email', email);

    fetch('../api/check-email.php', { method:'POST', body: fd })
      .then(function(r){ return r.json().then(function(j){ return { ok:r.ok, body:j }; }); })
      .then(function(res){
        if (res.ok && res.body.exists) {
          emAv.textContent = (email[0] || 'A').toUpperCase();
          emText.textContent = email;
          showStep('password');
        } else {
          showError((res.body && res.body.message) || 'No account found with this email address.');
        }
      })
      .catch(function(){
        showError('Network error. Please try again.');
      })
      .finally(function(){
        continueBtn.disabled = false;
        continueBtn.innerHTML = orig;
      });
  }

  continueBtn.addEventListener('click', checkEmail);
  emailInput.addEventListener('keydown', function(e){
    if (e.key === 'Enter') { e.preventDefault(); checkEmail(); }
  });

  window.goBackToEmail = function(){
    pwInput.value = '';
    hideError();
    showStep('email');
  };

  togglePw.addEventListener('click', function(){
    if (pwInput.type === 'password') {
      pwInput.type = 'text';
      pwIcon.classList.remove('fa-eye');
      pwIcon.classList.add('fa-eye-slash');
    } else {
      pwInput.type = 'password';
      pwIcon.classList.remove('fa-eye-slash');
      pwIcon.classList.add('fa-eye');
    }
  });

  form.addEventListener('submit', function(e){
    if (!stepPassword.classList.contains('active')) {
      e.preventDefault();
      checkEmail();
      return;
    }
    var btn = document.getElementById('signInBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Signing in...</span>';
  });

  // If server returned an error tied to password step, set avatar from email
  if (stepPassword.classList.contains('active') && emailInput.value) {
    emAv.textContent = (emailInput.value[0] || 'A').toUpperCase();
  }
})();
</script>

</body>
</html>
