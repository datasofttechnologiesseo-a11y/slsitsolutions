<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/blog.php';
require_admin();

$pageTitle = 'Blog Posts';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        db()->prepare('DELETE FROM blogs WHERE id = ?')->execute([$id]);
        $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Blog post deleted.'];
    } elseif ($action === 'toggle' && $id > 0) {
        // Flip publish state; set/clear published_at appropriately
        $stmt = db()->prepare('SELECT is_published, published_at FROM blogs WHERE id = ?');
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if ($r) {
            if ((int)$r['is_published'] === 1) {
                db()->prepare('UPDATE blogs SET is_published=0 WHERE id=?')->execute([$id]);
                $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Post unpublished (now a draft).'];
            } else {
                db()->prepare('UPDATE blogs SET is_published=1, published_at=COALESCE(published_at, NOW()) WHERE id=?')->execute([$id]);
                $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Post published.'];
            }
        }
    }
    header('Location: blogs.php' . ($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : ''));
    exit;
}

// Filters
$q       = trim((string)($_GET['q']      ?? ''));
$status  = $_GET['status'] ?? '';   // '', 'published', 'draft'
$catId   = (int)($_GET['cat']  ?? 0);
$tagId   = (int)($_GET['tag']  ?? 0);

$joins  = '';
$where  = [];
$params = [];

if ($catId > 0) {
    $joins  .= ' INNER JOIN blog_category_map mc ON mc.blog_id = b.id ';
    $where[]  = 'mc.category_id = ?';
    $params[] = $catId;
}
if ($tagId > 0) {
    $joins  .= ' INNER JOIN blog_tag_map mt ON mt.blog_id = b.id ';
    $where[]  = 'mt.tag_id = ?';
    $params[] = $tagId;
}
if ($q !== '') {
    $where[]  = '(b.title LIKE ? OR b.excerpt LIKE ?)';
    $like = "%$q%";
    array_push($params, $like, $like);
}
if ($status === 'published') $where[] = 'b.is_published = 1';
if ($status === 'draft')     $where[] = 'b.is_published = 0';

$wsql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$perPage = 12;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$cnt = db()->prepare("SELECT COUNT(DISTINCT b.id) FROM blogs b $joins $wsql");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$stmt = db()->prepare("
    SELECT DISTINCT b.id, b.title, b.slug, b.is_published, b.views, b.created_at, b.published_at, b.cover_image, b.author
    FROM blogs b $joins $wsql
    ORDER BY b.id DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$rows = $stmt->fetchAll();

$cats = db()->query('SELECT id, name FROM blog_categories ORDER BY name')->fetchAll();
$tags = db()->query('SELECT id, name FROM blog_tags ORDER BY name')->fetchAll();

function bqs(array $extra = []): string {
    $q = array_merge($_GET, $extra);
    unset($q['ids'], $q['action'], $q['csrf_token']);
    return http_build_query($q);
}

$totalAll = (int)db()->query('SELECT COUNT(*) FROM blogs')->fetchColumn();
$pubAll   = (int)db()->query('SELECT COUNT(*) FROM blogs WHERE is_published=1')->fetchColumn();

require __DIR__ . '/_layout_top.php';
?>
<div class="page-head">
  <div>
    <h1>Blog Posts</h1>
    <p><?= $totalAll ?> total · <?= $pubAll ?> published · <?= $totalAll-$pubAll ?> draft</p>
  </div>
  <div class="right">
    <a class="btn btn-success" href="blog-form.php"><i class="fa-solid fa-plus"></i> New Post</a>
  </div>
</div>

<form method="get" class="card" style="padding:14px 18px;margin-bottom:18px;">
  <div class="toolbar" style="margin:0;">
    <div class="search-box">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input class="input" type="text" name="q" placeholder="Search title or excerpt..." value="<?= htmlspecialchars($q) ?>">
    </div>
    <select class="select" name="status" style="max-width:150px;">
      <option value="">All status</option>
      <option value="published" <?= $status==='published'?'selected':'' ?>>Published</option>
      <option value="draft"     <?= $status==='draft'?'selected':'' ?>>Draft</option>
    </select>
    <select class="select" name="cat" style="max-width:200px;">
      <option value="0">All categories</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= $catId===(int)$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select class="select" name="tag" style="max-width:200px;">
      <option value="0">All tags</option>
      <?php foreach ($tags as $t): ?>
        <option value="<?= (int)$t['id'] ?>" <?= $tagId===(int)$t['id']?'selected':'' ?>><?= htmlspecialchars($t['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
    <?php if ($q||$status||$catId||$tagId): ?>
      <a class="btn btn-ghost btn-sm" href="blogs.php"><i class="fa-solid fa-xmark"></i> Clear</a>
    <?php endif; ?>
  </div>
</form>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th>Title</th>
        <th>Categories</th>
        <th>Tags</th>
        <th style="width:100px;">Views</th>
        <th style="width:110px;">Status</th>
        <th style="width:140px;">Date</th>
        <th style="width:160px;text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="7">
        <div class="empty">
          <i class="fa-regular fa-newspaper"></i>
          <div class="et">No blog posts yet</div>
          <div class="es">Create your first post to share with your audience.</div>
          <div style="margin-top:14px;"><a href="blog-form.php" class="btn btn-success btn-sm"><i class="fa-solid fa-plus"></i> New post</a></div>
        </div>
      </td></tr>
    <?php else: foreach ($rows as $r):
      $rcats = get_categories_for_blog((int)$r['id']);
      $rtags = get_tags_for_blog((int)$r['id']);
    ?>
      <tr>
        <td>
          <div style="display:flex;gap:12px;align-items:center;">
            <?php if (!empty($r['cover_image'])): ?>
              <img src="../<?= htmlspecialchars($r['cover_image']) ?>" alt=""
                   style="width:54px;height:40px;object-fit:cover;border-radius:8px;flex-shrink:0;">
            <?php else: ?>
              <div style="width:54px;height:40px;background:#eef2f7;color:#94a3b8;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fa-regular fa-image"></i>
              </div>
            <?php endif; ?>
            <div style="min-width:0;">
              <div style="font-weight:600;color:var(--text);"><?= htmlspecialchars($r['title']) ?></div>
              <div style="color:var(--text-mute);font-size:12px;">/<?= htmlspecialchars($r['slug']) ?></div>
            </div>
          </div>
        </td>
        <td>
          <?php if ($rcats): foreach ($rcats as $c): ?>
            <span class="badge badge-read" style="margin-right:4px;"><?= htmlspecialchars($c['name']) ?></span>
          <?php endforeach; else: ?><span style="color:var(--text-dim);">—</span><?php endif; ?>
        </td>
        <td style="color:var(--text-mute);font-size:13px;max-width:240px;">
          <?php if ($rtags): ?>
            <?= htmlspecialchars(implode(', ', array_column($rtags,'name'))) ?>
          <?php else: ?><span style="color:var(--text-dim);">—</span><?php endif; ?>
        </td>
        <td><i class="fa-regular fa-eye" style="color:var(--text-dim);"></i> <?= (int)$r['views'] ?></td>
        <td>
          <?php if ($r['is_published']): ?>
            <span class="badge badge-active"><i class="fa-solid fa-circle"></i> Published</span>
          <?php else: ?>
            <span class="badge badge-inactive"><i class="fa-solid fa-circle"></i> Draft</span>
          <?php endif; ?>
        </td>
        <td style="color:var(--text-mute);font-size:13px;">
          <?= htmlspecialchars(date('d M Y', strtotime($r['published_at'] ?: $r['created_at']))) ?>
        </td>
        <td style="text-align:right;">
          <div class="action-cell" style="justify-content:flex-end;">
            <?php if ($r['is_published']): ?>
              <a class="icon-btn" target="_blank" href="../blog-detail.php?slug=<?= urlencode($r['slug']) ?>" data-tip="View on site">
                <i class="fa-solid fa-up-right-from-square"></i>
              </a>
            <?php endif; ?>
            <a class="icon-btn" href="blog-form.php?id=<?= (int)$r['id'] ?>" data-tip="Edit">
              <i class="fa-regular fa-pen-to-square"></i>
            </a>
            <form method="post" style="display:inline;">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button type="submit" class="icon-btn <?= $r['is_published'] ? 'warn' : 'success' ?>" data-tip="<?= $r['is_published'] ? 'Unpublish' : 'Publish' ?>">
                <i class="fa-regular <?= $r['is_published'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
              </button>
            </form>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this blog post? This cannot be undone.');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button type="submit" class="icon-btn danger" data-tip="Delete"><i class="fa-regular fa-trash-can"></i></button>
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
    <?php if ($page>1): ?><a href="?<?= bqs(['page'=>$page-1]) ?>"><i class="fa-solid fa-chevron-left"></i></a><?php else: ?><span class="disabled"><i class="fa-solid fa-chevron-left"></i></span><?php endif; ?>
    <?php if ($first>1): ?><a href="?<?= bqs(['page'=>1]) ?>">1</a><?php if ($first>2): ?><span class="disabled">…</span><?php endif; endif; ?>
    <?php for ($i=$first; $i<=$last; $i++): ?>
      <?php if ($i===$page): ?><span class="active"><?= $i ?></span><?php else: ?><a href="?<?= bqs(['page'=>$i]) ?>"><?= $i ?></a><?php endif; ?>
    <?php endfor; ?>
    <?php if ($last<$pages): ?><?php if ($last<$pages-1): ?><span class="disabled">…</span><?php endif; ?><a href="?<?= bqs(['page'=>$pages]) ?>"><?= $pages ?></a><?php endif; ?>
    <?php if ($page<$pages): ?><a href="?<?= bqs(['page'=>$page+1]) ?>"><i class="fa-solid fa-chevron-right"></i></a><?php else: ?><span class="disabled"><i class="fa-solid fa-chevron-right"></i></span><?php endif; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
