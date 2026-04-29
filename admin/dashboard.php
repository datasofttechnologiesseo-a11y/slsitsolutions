<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/_layout_top.php';
require_once __DIR__ . '/../includes/testimonial_helpers.php';

$stats = [
    'enq_total'    => (int)db()->query('SELECT COUNT(*) FROM enquiries')->fetchColumn(),
    'enq_unread'   => (int)db()->query('SELECT COUNT(*) FROM enquiries WHERE is_read=0')->fetchColumn(),
    'enq_today'    => (int)db()->query('SELECT COUNT(*) FROM enquiries WHERE DATE(created_at)=CURDATE()')->fetchColumn(),
    'testi_active' => (int)db()->query('SELECT COUNT(*) FROM testimonials WHERE is_active=1')->fetchColumn(),
    'testi_total'  => (int)db()->query('SELECT COUNT(*) FROM testimonials')->fetchColumn(),
    'blogs_pub'    => (int)db()->query('SELECT COUNT(*) FROM blogs WHERE is_published=1')->fetchColumn(),
    'blogs_draft'  => (int)db()->query('SELECT COUNT(*) FROM blogs WHERE is_published=0')->fetchColumn(),
];

$latest = db()->query('SELECT id, name, email, service, is_read, created_at, message FROM enquiries ORDER BY id DESC LIMIT 6')->fetchAll();
?>
<div class="page-head">
  <div>
    <h1>Welcome back, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>!</h1>
    <p>Here's what's happening on your site right now.</p>
  </div>
  <div class="right">
    <a href="testimonial-form.php" class="btn btn-success"><i class="fa-solid fa-plus"></i> Add Testimonial</a>
  </div>
</div>

<div class="stat-grid">
  <div class="stat stat-blue">
    <div class="ico"><i class="fa-solid fa-envelope-open-text"></i></div>
    <div class="label"><i class="fa-regular fa-envelope"></i> Total Enquiries</div>
    <div class="value"><?= $stats['enq_total'] ?></div>
    <div class="sub">All-time submissions</div>
  </div>
  <div class="stat stat-red">
    <div class="ico"><i class="fa-solid fa-bell"></i></div>
    <div class="label"><i class="fa-regular fa-circle-dot"></i> Unread</div>
    <div class="value"><?= $stats['enq_unread'] ?></div>
    <div class="sub">Awaiting your reply</div>
  </div>
  <div class="stat stat-green">
    <div class="ico"><i class="fa-solid fa-calendar-day"></i></div>
    <div class="label"><i class="fa-regular fa-calendar"></i> Today</div>
    <div class="value"><?= $stats['enq_today'] ?></div>
    <div class="sub">Received in last 24h</div>
  </div>
  <div class="stat stat-orange">
    <div class="ico"><i class="fa-solid fa-star"></i></div>
    <div class="label"><i class="fa-regular fa-star"></i> Testimonials</div>
    <div class="value"><?= $stats['testi_active'] ?> <span style="color:var(--text-dim);font-size:18px;font-weight:500;">/ <?= $stats['testi_total'] ?></span></div>
    <div class="sub">Active on website</div>
  </div>
  <div class="stat stat-blue">
    <div class="ico"><i class="fa-solid fa-newspaper"></i></div>
    <div class="label"><i class="fa-regular fa-newspaper"></i> Blog Posts</div>
    <div class="value"><?= $stats['blogs_pub'] ?><?php if ($stats['blogs_draft']>0): ?> <span style="color:var(--text-dim);font-size:18px;font-weight:500;">+ <?= $stats['blogs_draft'] ?> draft</span><?php endif; ?></div>
    <div class="sub"><?= $stats['blogs_pub'] ?> published<?= $stats['blogs_draft']>0 ? ', '.$stats['blogs_draft'].' draft' : '' ?></div>
  </div>
</div>

<div class="card">
  <div class="toolbar" style="margin:0 0 14px;">
    <div>
      <h2 style="margin:0;font-size:16px;font-weight:700;font-family:'Poppins',sans-serif;">
        <i class="fa-solid fa-clock-rotate-left" style="color:var(--primary);margin-right:6px;"></i>
        Latest Enquiries
      </h2>
      <p style="margin:4px 0 0;color:var(--text-mute);font-size:13px;">The 6 most recent contact form submissions.</p>
    </div>
    <div class="spacer"></div>
    <a href="enquiries.php" class="btn btn-ghost btn-sm">View all <i class="fa-solid fa-arrow-right"></i></a>
  </div>

  <?php if (!$latest): ?>
    <div class="empty">
      <i class="fa-regular fa-envelope-open"></i>
      <div class="et">No enquiries yet</div>
      <div class="es">They'll show up here once visitors submit the contact form.</div>
    </div>
  <?php else: ?>
    <div class="table-wrap" style="border:0;box-shadow:none;">
      <table class="table">
        <thead>
          <tr>
            <th>Contact</th>
            <th>Service</th>
            <th>Status</th>
            <th>Received</th>
            <th style="width: 90px;text-align:right;">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($latest as $r):
          $initial = strtoupper(mb_substr($r['name'], 0, 1));
          $hue = crc32($r['email']) % 360;
        ?>
          <tr class="<?= $r['is_read'] ? '' : 'row-unread' ?>">
            <td>
              <div class="user-cell">
                <div class="av" style="background:hsl(<?= $hue ?>,55%,48%);"><?= htmlspecialchars($initial) ?></div>
                <div class="meta">
                  <div class="n"><?= htmlspecialchars($r['name']) ?></div>
                  <div class="s"><?= htmlspecialchars($r['email']) ?></div>
                </div>
              </div>
            </td>
            <td><?= htmlspecialchars($r['service'] ?: '—') ?></td>
            <td>
              <?php if ($r['is_read']): ?>
                <span class="badge badge-read"><i class="fa-solid fa-circle"></i> Read</span>
              <?php else: ?>
                <span class="badge badge-unread"><i class="fa-solid fa-circle"></i> New</span>
              <?php endif; ?>
            </td>
            <td style="color:var(--text-mute);font-size:13px;"><?= htmlspecialchars(date('d M Y, H:i', strtotime($r['created_at']))) ?></td>
            <td style="text-align:right;">
              <a href="enquiry-view.php?id=<?= (int)$r['id'] ?>" class="icon-btn primary" data-tip="View">
                <i class="fa-solid fa-eye"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
