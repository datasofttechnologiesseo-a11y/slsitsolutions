<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();

$pageTitle = 'Enquiry';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: enquiries.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        db()->prepare('DELETE FROM enquiries WHERE id = ?')->execute([$id]);
        $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Enquiry deleted.'];
        header('Location: enquiries.php');
        exit;
    }
    if ($action === 'toggle_read') {
        db()->prepare('UPDATE enquiries SET is_read = 1 - is_read WHERE id = ?')->execute([$id]);
        header('Location: enquiry-view.php?id=' . $id);
        exit;
    }
}

$stmt = db()->prepare('SELECT * FROM enquiries WHERE id = ?');
$stmt->execute([$id]);
$e = $stmt->fetch();

$notFound = !$e;

if ($e && !$e['is_read']) {
    db()->prepare('UPDATE enquiries SET is_read = 1 WHERE id = ?')->execute([$id]);
    $e['is_read'] = 1;
}

$mailto  = $e ? 'mailto:' . rawurlencode($e['email']) . '?subject=' . rawurlencode('Re: Your enquiry to SLS IT Solutions') : '';
$initial = $e ? strtoupper(mb_substr($e['name'], 0, 1)) : '';
$hue     = $e ? crc32($e['email']) % 360 : 0;

require __DIR__ . '/_layout_top.php';

if ($notFound) {
    echo '<div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> Enquiry not found.</div>';
    require __DIR__ . '/_layout_bottom.php';
    exit;
}
?>
<div class="page-head">
  <div>
    <a href="enquiries.php" style="font-size:13px;color:var(--text-mute);">
      <i class="fa-solid fa-arrow-left"></i> Back to enquiries
    </a>
    <h1 style="margin-top:6px;">Enquiry from <?= htmlspecialchars($e['name']) ?></h1>
    <p>Received <?= htmlspecialchars(date('d M Y, H:i', strtotime($e['created_at']))) ?></p>
  </div>
  <div class="right">
    <a href="<?= htmlspecialchars($mailto) ?>" class="btn btn-primary">
      <i class="fa-solid fa-reply"></i> Reply by Email
    </a>
    <form method="post" style="display:inline;">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="toggle_read">
      <button class="btn btn-ghost" type="submit" data-tip="Mark unread"><i class="fa-regular fa-circle"></i> Mark Unread</button>
    </form>
    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this enquiry? This cannot be undone.');">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="delete">
      <button class="btn btn-danger" type="submit"><i class="fa-regular fa-trash-can"></i> Delete</button>
    </form>
  </div>
</div>

<div class="row" style="grid-template-columns: 2fr 1fr;">
  <div class="card">
    <div class="user-cell" style="margin-bottom:18px;padding-bottom:16px;border-bottom:1px solid var(--border-2);">
      <div class="av" style="width:54px;height:54px;font-size:18px;background:hsl(<?= $hue ?>,55%,48%);"><?= htmlspecialchars($initial) ?></div>
      <div class="meta">
        <div class="n" style="font-size:16px;"><?= htmlspecialchars($e['name']) ?></div>
        <div class="s">
          <a href="mailto:<?= htmlspecialchars($e['email']) ?>"><?= htmlspecialchars($e['email']) ?></a>
          <?php if ($e['phone']): ?> · <a href="tel:<?= htmlspecialchars($e['phone']) ?>"><?= htmlspecialchars($e['phone']) ?></a><?php endif; ?>
        </div>
      </div>
    </div>

    <h2 style="margin:0 0 12px;font-size:14px;color:var(--text-mute);text-transform:uppercase;letter-spacing:0.5px;">
      <i class="fa-regular fa-message"></i> Message
    </h2>
    <div style="white-space:pre-wrap;color:var(--text);font-size:15px;line-height:1.7;">
      <?= nl2br(htmlspecialchars($e['message'] ?? '(no message)')) ?>
    </div>
  </div>

  <div class="card">
    <h2 style="margin:0 0 14px;font-size:14px;color:var(--text-mute);text-transform:uppercase;letter-spacing:0.5px;">
      <i class="fa-solid fa-id-card"></i> Contact Details
    </h2>
    <table class="table" style="border:0;">
      <tbody>
        <tr><td style="color:var(--text-mute);padding-left:0;">Name</td><td style="padding-right:0;"><?= htmlspecialchars($e['name']) ?></td></tr>
        <?php if ($e['company']): ?>
          <tr><td style="color:var(--text-mute);padding-left:0;">Company</td><td style="padding-right:0;"><?= htmlspecialchars($e['company']) ?></td></tr>
        <?php endif; ?>
        <tr><td style="color:var(--text-mute);padding-left:0;">Email</td><td style="padding-right:0;"><a href="mailto:<?= htmlspecialchars($e['email']) ?>"><?= htmlspecialchars($e['email']) ?></a></td></tr>
        <?php if ($e['phone']): ?>
          <tr><td style="color:var(--text-mute);padding-left:0;">Phone</td><td style="padding-right:0;"><a href="tel:<?= htmlspecialchars($e['phone']) ?>"><?= htmlspecialchars($e['phone']) ?></a></td></tr>
        <?php endif; ?>
        <?php if ($e['service']): ?>
          <tr><td style="color:var(--text-mute);padding-left:0;">Service</td><td style="padding-right:0;"><?= htmlspecialchars($e['service']) ?></td></tr>
        <?php endif; ?>
        <tr><td style="color:var(--text-mute);padding-left:0;">IP</td><td style="padding-right:0;font-family:monospace;font-size:12px;"><?= htmlspecialchars($e['ip'] ?? '—') ?></td></tr>
        <tr><td style="color:var(--text-mute);padding-left:0;">Date</td><td style="padding-right:0;font-size:13px;"><?= htmlspecialchars(date('d M Y, H:i', strtotime($e['created_at']))) ?></td></tr>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
