<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/settings.php';
require_admin();

$pageTitle = 'System Settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $vals = $_POST['settings'] ?? [];
    if (is_array($vals)) {
        // Only save keys we know about (whitelist)
        $known = db()->query('SELECT s_key, type FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
        $saved = 0;
        foreach ($vals as $k => $v) {
            if (!isset($known[$k])) continue;
            // Password placeholder: if empty AND existing value present, keep current value
            if ($known[$k] === 'password' && $v === '') continue;
            set_setting($k, (string)$v);
            $saved++;
        }
        $_SESSION['flash'] = ['type'=>'success', 'msg' => "Settings saved ($saved updated)."];
    }
    header('Location: settings.php');
    exit;
}

$categories = get_setting_categories();

require __DIR__ . '/_layout_top.php';
?>
<div class="page-head">
  <div>
    <h1>System Settings</h1>
    <p>Manage SMTP and other site configuration. Values here override <code style="font-size:12px;background:#eef2f7;padding:2px 6px;border-radius:4px;">includes/config.php</code>.</p>
  </div>
</div>

<form method="post" id="settingsForm">
  <?= csrf_field() ?>

  <?php foreach ($categories as $cat):
    $rows = get_settings_by_category($cat);
    $title = ['smtp' => 'SMTP / Mail Configuration', 'general' => 'General'][$cat] ?? ucfirst($cat);
    $icon  = ['smtp' => 'fa-envelope', 'general' => 'fa-sliders'][$cat]    ?? 'fa-gear';
  ?>
    <div class="card" style="margin-bottom:20px;">
      <div class="toolbar" style="margin:0 0 18px;">
        <div>
          <h2 style="margin:0;font-size:16px;font-weight:700;font-family:'Poppins',sans-serif;">
            <i class="fa-solid <?= htmlspecialchars($icon) ?>" style="color:var(--primary);margin-right:8px;"></i>
            <?= htmlspecialchars($title) ?>
          </h2>
          <?php if ($cat === 'smtp'): ?>
            <p style="margin:4px 0 0;color:var(--text-mute);font-size:13px;">Used for sending contact-form enquiries. Test before saving to verify your credentials work.</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="row" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
        <?php foreach ($rows as $r):
          $opts = $r['options'] ? array_map('trim', explode(',', $r['options'])) : [];
          $val  = $r['s_value'];
        ?>
          <div class="field" style="margin:0;">
            <label for="s_<?= htmlspecialchars($r['s_key']) ?>"><?= htmlspecialchars($r['label'] ?: $r['s_key']) ?></label>

            <?php if ($r['type'] === 'select' && $opts): ?>
              <select class="select" id="s_<?= htmlspecialchars($r['s_key']) ?>" name="settings[<?= htmlspecialchars($r['s_key']) ?>]">
                <?php foreach ($opts as $o): ?>
                  <option value="<?= htmlspecialchars($o) ?>" <?= $o === $val ? 'selected' : '' ?>><?= htmlspecialchars(strtoupper($o)) ?></option>
                <?php endforeach; ?>
              </select>
            <?php elseif ($r['type'] === 'password'): ?>
              <div style="position:relative;">
                <input class="input"
                       id="s_<?= htmlspecialchars($r['s_key']) ?>"
                       name="settings[<?= htmlspecialchars($r['s_key']) ?>]"
                       type="password"
                       autocomplete="new-password"
                       value=""
                       placeholder="<?= $val !== '' ? '••••••••  (leave blank to keep current)' : 'Not set' ?>">
                <button type="button" tabindex="-1"
                        onclick="var i=this.previousElementSibling; i.type=i.type==='password'?'text':'password'; this.querySelector('i').classList.toggle('fa-eye'); this.querySelector('i').classList.toggle('fa-eye-slash');"
                        style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:transparent;border:0;color:var(--text-dim);cursor:pointer;padding:6px 8px;">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
            <?php elseif ($r['type'] === 'number'): ?>
              <input class="input" id="s_<?= htmlspecialchars($r['s_key']) ?>" name="settings[<?= htmlspecialchars($r['s_key']) ?>]" type="number" value="<?= htmlspecialchars($val) ?>">
            <?php elseif ($r['type'] === 'textarea'): ?>
              <textarea class="textarea" id="s_<?= htmlspecialchars($r['s_key']) ?>" name="settings[<?= htmlspecialchars($r['s_key']) ?>]" rows="3"><?= htmlspecialchars($val) ?></textarea>
            <?php else: ?>
              <input class="input" id="s_<?= htmlspecialchars($r['s_key']) ?>" name="settings[<?= htmlspecialchars($r['s_key']) ?>]" type="text" value="<?= htmlspecialchars($val) ?>">
            <?php endif; ?>

            <?php if ($r['description']): ?>
              <div style="font-size:12px;color:var(--text-mute);margin-top:6px;">
                <i class="fa-regular fa-circle-question"></i> <?= htmlspecialchars($r['description']) ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($cat === 'smtp'): ?>
        <!-- Test mail row -->
        <div style="margin-top:22px;padding-top:18px;border-top:1px solid var(--border-2);">
          <h3 style="margin:0 0 10px;font-size:13px;color:var(--text-mute);text-transform:uppercase;letter-spacing:0.5px;">
            <i class="fa-solid fa-flask"></i> Test SMTP
          </h3>
          <p style="color:var(--text-mute);font-size:13px;margin:0 0 12px;">
            Sends a one-line email using the values currently in this form (without saving). Useful for verifying credentials before clicking Save.
          </p>
          <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
            <div class="field" style="margin:0;flex:1;min-width:240px;max-width:380px;">
              <label for="testEmailTo">Send Test To</label>
              <input class="input" type="email" id="testEmailTo" placeholder="you@example.com" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
            <button type="button" class="btn btn-ghost" id="testMailBtn">
              <i class="fa-solid fa-paper-plane"></i> Send Test Email
            </button>
          </div>
          <div id="testMailResult" class="alert" style="display:none;margin-top:14px;"></div>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <div style="display:flex;gap:10px;margin-top:18px;">
    <button class="btn btn-success" type="submit">
      <i class="fa-solid fa-floppy-disk"></i> Save Settings
    </button>
    <a class="btn btn-ghost" href="dashboard.php">
      <i class="fa-solid fa-xmark"></i> Cancel
    </a>
  </div>
</form>

<script>
(function(){
  var btn  = document.getElementById('testMailBtn');
  var out  = document.getElementById('testMailResult');
  if (!btn) return;

  btn.addEventListener('click', function(){
    var to = document.getElementById('testEmailTo').value.trim();
    if (!to) { showResult('error','Please enter an email address to send the test to.'); return; }

    // Collect current SMTP form values (so admin can test before saving)
    var fd = new FormData();
    fd.append('csrf_token', '<?= csrf_token() ?>');
    fd.append('to', to);
    document.querySelectorAll('[name^="settings[mail_"]').forEach(function(el){
      var key = el.name.match(/\[(.+)\]/)[1];
      fd.append(key, el.value);
    });

    btn.disabled = true;
    var orig = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Sending...';
    showResult('info', 'Sending test email...');

    fetch('../api/test-mail.php', { method:'POST', body: fd, credentials: 'same-origin' })
      .then(function(r){ return r.json().then(function(j){ return { ok:r.ok, body:j }; }); })
      .then(function(res){
        if (res.ok && res.body.success) {
          showResult('success', res.body.message || 'Test email sent.');
        } else {
          showResult('error', (res.body && res.body.message) || 'Test failed.');
        }
      })
      .catch(function(e){ showResult('error', 'Request failed: ' + e.message); })
      .finally(function(){ btn.disabled = false; btn.innerHTML = orig; });
  });

  function showResult(kind, msg){
    out.style.display = 'flex';
    out.className = 'alert alert-' + kind;
    out.innerHTML = '<i class="fa-solid ' + (kind==='success'?'fa-circle-check':kind==='error'?'fa-circle-exclamation':'fa-circle-info') + '"></i><span></span>';
    out.querySelector('span').textContent = msg;
  }
})();
</script>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
