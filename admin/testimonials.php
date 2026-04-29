<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/testimonial_helpers.php';
require_admin();

$pageTitle = 'Testimonials';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        db()->prepare('DELETE FROM testimonials WHERE id = ?')->execute([$id]);
        $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Testimonial deleted.'];
    } elseif ($action === 'toggle' && $id > 0) {
        db()->prepare('UPDATE testimonials SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
        $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Testimonial visibility updated.'];
    }
    header('Location: testimonials.php' . ($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : ''));
    exit;
}

// Pagination
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;
$total   = (int)db()->query('SELECT COUNT(*) FROM testimonials')->fetchColumn();
$pages   = max(1, (int)ceil($total / $perPage));

$rows = db()->query("SELECT * FROM testimonials ORDER BY sort_order ASC, id DESC LIMIT $perPage OFFSET $offset")->fetchAll();
$active = (int)db()->query('SELECT COUNT(*) FROM testimonials WHERE is_active=1')->fetchColumn();

function tqs(array $extra = []): string {
    $q = array_merge($_GET, $extra);
    unset($q['ids'], $q['action'], $q['csrf_token']);
    return http_build_query($q);
}

require __DIR__ . '/_layout_top.php';
?>
<div class="page-head">
  <div>
    <h1>Testimonials</h1>
    <p><?= $total ?> total · <?= $active ?> visible on website</p>
  </div>
  <div class="right">
    <a class="btn btn-success" href="testimonial-form.php">
      <i class="fa-solid fa-plus"></i> Add Testimonial
    </a>
  </div>
</div>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th style="width:60px;">Avatar</th>
        <th>Client</th>
        <th>Quote</th>
        <th style="width:100px;">Rating</th>
        <th style="width:70px;">Order</th>
        <th style="width:90px;">Status</th>
        <th style="width:160px;text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="7">
        <div class="empty">
          <i class="fa-regular fa-star"></i>
          <div class="et">No testimonials yet</div>
          <div class="es">Add your first client testimonial to display on the home page.</div>
          <div style="margin-top:14px;"><a href="testimonial-form.php" class="btn btn-success btn-sm"><i class="fa-solid fa-plus"></i> Add testimonial</a></div>
        </div>
      </td></tr>
    <?php else: foreach ($rows as $r):
      $initials = $r['initials'] ?: testimonial_initials($r['client_name']);
      $grad     = testimonial_gradient($r['avatar_color']);
    ?>
      <tr>
        <td>
          <div class="user-cell">
            <div class="av" style="background:<?= htmlspecialchars($grad) ?>;"><?= htmlspecialchars($initials) ?></div>
          </div>
        </td>
        <td>
          <div class="meta">
            <div class="n"><?= htmlspecialchars($r['client_name']) ?></div>
            <?php if ($r['company']): ?>
              <div class="s"><?= htmlspecialchars($r['company']) ?></div>
            <?php endif; ?>
          </div>
        </td>
        <td style="max-width:380px;color:var(--text-mute);font-size:13px;">
          <?= htmlspecialchars(mb_substr($r['quote'], 0, 130)) . (mb_strlen($r['quote'])>130?'…':'') ?>
        </td>
        <td><span class="stars"><?= str_repeat('★', (int)$r['rating']) ?></span></td>
        <td><?= (int)$r['sort_order'] ?></td>
        <td>
          <?php if ($r['is_active']): ?>
            <span class="badge badge-active"><i class="fa-solid fa-circle"></i> Active</span>
          <?php else: ?>
            <span class="badge badge-inactive"><i class="fa-solid fa-circle"></i> Hidden</span>
          <?php endif; ?>
        </td>
        <td style="text-align:right;">
          <div class="action-cell" style="justify-content:flex-end;">
            <a class="icon-btn" href="testimonial-form.php?id=<?= (int)$r['id'] ?>" data-tip="Edit">
              <i class="fa-regular fa-pen-to-square"></i>
            </a>
            <form method="post" style="display:inline;">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="icon-btn <?= $r['is_active'] ? 'warn' : 'success' ?>" type="submit" data-tip="<?= $r['is_active'] ? 'Hide' : 'Show' ?>">
                <i class="fa-regular <?= $r['is_active'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
              </button>
            </form>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this testimonial?');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="icon-btn danger" type="submit" data-tip="Delete">
                <i class="fa-regular fa-trash-can"></i>
              </button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php if ($pages > 1):
  $first = max(1, $page - 2);
  $last  = min($pages, $page + 2);
?>
  <div class="pager">
    <span class="info">Showing <?= $offset+1 ?>–<?= min($offset+$perPage, $total) ?> of <?= $total ?></span>

    <?php if ($page > 1): ?>
      <a href="?<?= tqs(['page'=>$page-1]) ?>"><i class="fa-solid fa-chevron-left"></i></a>
    <?php else: ?>
      <span class="disabled"><i class="fa-solid fa-chevron-left"></i></span>
    <?php endif; ?>

    <?php if ($first > 1): ?>
      <a href="?<?= tqs(['page'=>1]) ?>">1</a>
      <?php if ($first > 2): ?><span class="disabled">…</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $first; $i <= $last; $i++): ?>
      <?php if ($i === $page): ?>
        <span class="active"><?= $i ?></span>
      <?php else: ?>
        <a href="?<?= tqs(['page'=>$i]) ?>"><?= $i ?></a>
      <?php endif; ?>
    <?php endfor; ?>

    <?php if ($last < $pages): ?>
      <?php if ($last < $pages-1): ?><span class="disabled">…</span><?php endif; ?>
      <a href="?<?= tqs(['page'=>$pages]) ?>"><?= $pages ?></a>
    <?php endif; ?>

    <?php if ($page < $pages): ?>
      <a href="?<?= tqs(['page'=>$page+1]) ?>"><i class="fa-solid fa-chevron-right"></i></a>
    <?php else: ?>
      <span class="disabled"><i class="fa-solid fa-chevron-right"></i></span>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
