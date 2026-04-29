<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();

$pageTitle = 'Enquiries';

// Bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';
    $ids    = array_map('intval', (array)($_POST['ids'] ?? []));
    $ids    = array_values(array_filter($ids, fn($i) => $i > 0));

    if ($ids) {
        $place = implode(',', array_fill(0, count($ids), '?'));
        if ($action === 'delete') {
            $stmt = db()->prepare("DELETE FROM enquiries WHERE id IN ($place)");
            $stmt->execute($ids);
            $_SESSION['flash'] = ['type'=>'success', 'msg'=>count($ids).' enquiry(ies) deleted.'];
        } elseif ($action === 'mark_read') {
            $stmt = db()->prepare("UPDATE enquiries SET is_read=1 WHERE id IN ($place)");
            $stmt->execute($ids);
            $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Marked as read.'];
        } elseif ($action === 'mark_unread') {
            $stmt = db()->prepare("UPDATE enquiries SET is_read=0 WHERE id IN ($place)");
            $stmt->execute($ids);
            $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Marked as unread.'];
        }
    }
    header('Location: enquiries.php' . ($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : ''));
    exit;
}

$q       = trim((string)($_GET['q']      ?? ''));
$service = trim((string)($_GET['service']?? ''));
$status  = $_GET['status'] ?? '';

$where  = [];
$params = [];
if ($q !== '') {
    $where[] = '(name LIKE ? OR email LIKE ? OR company LIKE ? OR message LIKE ?)';
    $like = "%{$q}%";
    array_push($params, $like, $like, $like, $like);
}
if ($service !== '') { $where[] = 'service = ?'; $params[] = $service; }
if ($status === 'unread') $where[] = 'is_read = 0';
if ($status === 'read')   $where[] = 'is_read = 1';

$wsql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$perPage = 15;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$count = db()->prepare("SELECT COUNT(*) FROM enquiries $wsql");
$count->execute($params);
$total = (int)$count->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$rowsStmt = db()->prepare("SELECT id, name, email, company, service, is_read, created_at, message FROM enquiries $wsql ORDER BY id DESC LIMIT $perPage OFFSET $offset");
$rowsStmt->execute($params);
$rows = $rowsStmt->fetchAll();

$svcOptions = db()->query("SELECT DISTINCT service FROM enquiries WHERE service IS NOT NULL AND service<>'' ORDER BY service")->fetchAll(PDO::FETCH_COLUMN);

function qs(array $extra = []): string {
    $q = array_merge($_GET, $extra);
    unset($q['ids'], $q['action'], $q['csrf_token']);
    return http_build_query($q);
}

$totalAll  = (int)db()->query('SELECT COUNT(*) FROM enquiries')->fetchColumn();
$unreadAll = (int)db()->query('SELECT COUNT(*) FROM enquiries WHERE is_read=0')->fetchColumn();

require __DIR__ . '/_layout_top.php';
?>
<div class="page-head">
  <div>
    <h1>Enquiries</h1>
    <p><?= $totalAll ?> total · <?= $unreadAll ?> unread</p>
  </div>
</div>

<form method="get" class="card" style="padding:14px 18px;margin-bottom:18px;">
  <div class="toolbar" style="margin:0;">
    <div class="search-box">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input class="input" type="text" name="q" placeholder="Search by name, email, company, message..." value="<?= htmlspecialchars($q) ?>">
    </div>
    <select class="select" name="service" style="max-width:200px;">
      <option value="">All services</option>
      <?php foreach ($svcOptions as $s): ?>
        <option value="<?= htmlspecialchars($s) ?>" <?= $service===$s?'selected':'' ?>><?= htmlspecialchars($s) ?></option>
      <?php endforeach; ?>
    </select>
    <select class="select" name="status" style="max-width:160px;">
      <option value="">All status</option>
      <option value="unread" <?= $status==='unread'?'selected':'' ?>>Unread only</option>
      <option value="read"   <?= $status==='read'?'selected':'' ?>>Read only</option>
    </select>
    <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
    <?php if ($q!=='' || $service!=='' || $status!==''): ?>
      <a class="btn btn-ghost btn-sm" href="enquiries.php"><i class="fa-solid fa-xmark"></i> Clear</a>
    <?php endif; ?>
  </div>
</form>

<form method="post" id="bulkForm">
  <?= csrf_field() ?>
  <input type="hidden" name="action" id="bulkAction" value="">

  <div class="toolbar">
    <button type="button" class="btn btn-ghost btn-sm" onclick="bulk('mark_read')">
      <i class="fa-regular fa-circle-check"></i> Mark read
    </button>
    <button type="button" class="btn btn-ghost btn-sm" onclick="bulk('mark_unread')">
      <i class="fa-regular fa-circle"></i> Mark unread
    </button>
    <button type="button" class="btn btn-danger btn-sm" onclick="if(confirm('Delete selected enquiries? This cannot be undone.')) bulk('delete')">
      <i class="fa-regular fa-trash-can"></i> Delete
    </button>
    <div class="spacer"></div>
    <span style="color:var(--text-mute);font-size:13px;"><span id="selCount">0</span> selected</span>
  </div>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th style="width:32px;"><input type="checkbox" onchange="toggleAll(this)"></th>
          <th>Contact</th>
          <th>Service</th>
          <th>Status</th>
          <th>Received</th>
          <th style="width:60px;text-align:right;"></th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="6">
          <div class="empty">
            <i class="fa-regular fa-envelope-open"></i>
            <div class="et">No enquiries found</div>
            <div class="es">Try adjusting your filters or search query.</div>
          </div>
        </td></tr>
      <?php else: foreach ($rows as $r):
        $initial = strtoupper(mb_substr($r['name'], 0, 1));
        $hue = crc32($r['email']) % 360;
      ?>
        <tr class="<?= $r['is_read'] ? '' : 'row-unread' ?>">
          <td><input type="checkbox" name="ids[]" value="<?= (int)$r['id'] ?>" class="enq-cb"></td>
          <td>
            <div class="user-cell">
              <div class="av" style="background:hsl(<?= $hue ?>,55%,48%);"><?= htmlspecialchars($initial) ?></div>
              <div class="meta">
                <div class="n"><?= htmlspecialchars($r['name']) ?></div>
                <div class="s"><?= htmlspecialchars($r['email']) ?><?= $r['company'] ? ' · '.htmlspecialchars($r['company']) : '' ?></div>
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
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</form>

<?php if ($pages > 1): ?>
  <?php
    $first = max(1, $page - 2);
    $last  = min($pages, $page + 2);
  ?>
  <div class="pager">
    <span class="info">Showing <?= $offset+1 ?>–<?= min($offset+$perPage, $total) ?> of <?= $total ?></span>

    <?php if ($page > 1): ?>
      <a href="?<?= qs(['page'=>$page-1]) ?>"><i class="fa-solid fa-chevron-left"></i></a>
    <?php else: ?>
      <span class="disabled"><i class="fa-solid fa-chevron-left"></i></span>
    <?php endif; ?>

    <?php if ($first > 1): ?>
      <a href="?<?= qs(['page'=>1]) ?>">1</a>
      <?php if ($first > 2): ?><span class="disabled">…</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $first; $i <= $last; $i++): ?>
      <?php if ($i === $page): ?>
        <span class="active"><?= $i ?></span>
      <?php else: ?>
        <a href="?<?= qs(['page'=>$i]) ?>"><?= $i ?></a>
      <?php endif; ?>
    <?php endfor; ?>

    <?php if ($last < $pages): ?>
      <?php if ($last < $pages-1): ?><span class="disabled">…</span><?php endif; ?>
      <a href="?<?= qs(['page'=>$pages]) ?>"><?= $pages ?></a>
    <?php endif; ?>

    <?php if ($page < $pages): ?>
      <a href="?<?= qs(['page'=>$page+1]) ?>"><i class="fa-solid fa-chevron-right"></i></a>
    <?php else: ?>
      <span class="disabled"><i class="fa-solid fa-chevron-right"></i></span>
    <?php endif; ?>
  </div>
<?php endif; ?>

<script>
function toggleAll(cb){
  document.querySelectorAll('.enq-cb').forEach(function(c){ c.checked = cb.checked; });
  updateCount();
}
function updateCount(){
  document.getElementById('selCount').textContent = document.querySelectorAll('.enq-cb:checked').length;
}
document.querySelectorAll('.enq-cb').forEach(function(c){ c.addEventListener('change', updateCount); });
function bulk(action){
  var n = document.querySelectorAll('.enq-cb:checked').length;
  if (n === 0) { alert('Please select at least one enquiry.'); return; }
  document.getElementById('bulkAction').value = action;
  document.getElementById('bulkForm').submit();
}
</script>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
