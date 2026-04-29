<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/blog.php';
require_admin();

$pageTitle = 'Categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    if ($action === 'delete' && $id > 0) {
        db()->prepare('DELETE FROM blog_categories WHERE id = ?')->execute([$id]);
        $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Category deleted.'];
    }
    header('Location: categories.php');
    exit;
}

$rows = get_categories_with_counts();

require __DIR__ . '/_layout_top.php';
?>
<div class="page-head">
  <div>
    <h1>Categories</h1>
    <p><?= count($rows) ?> total</p>
  </div>
  <div class="right">
    <a class="btn btn-success" href="category-form.php"><i class="fa-solid fa-plus"></i> Add Category</a>
  </div>
</div>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Slug</th>
        <th>Description</th>
        <th style="width:100px;">Posts</th>
        <th style="width:120px;text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="5">
          <div class="empty">
            <i class="fa-solid fa-folder-open"></i>
            <div class="et">No categories yet</div>
            <div class="es">Categories help organize your blog posts.</div>
            <div style="margin-top:14px;"><a href="category-form.php" class="btn btn-success btn-sm"><i class="fa-solid fa-plus"></i> Add category</a></div>
          </div>
        </td></tr>
      <?php else: foreach ($rows as $r): ?>
        <tr>
          <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
          <td><span style="color:var(--text-mute);font-family:monospace;font-size:12px;">/<?= htmlspecialchars($r['slug']) ?></span></td>
          <td style="color:var(--text-mute);font-size:13px;max-width:340px;"><?= htmlspecialchars($r['description'] ?? '—') ?></td>
          <td><span class="badge badge-read"><i class="fa-regular fa-newspaper"></i> <?= (int)$r['post_count'] ?></span></td>
          <td style="text-align:right;">
            <div class="action-cell" style="justify-content:flex-end;">
              <a class="icon-btn" href="category-form.php?id=<?= (int)$r['id'] ?>" data-tip="Edit">
                <i class="fa-regular fa-pen-to-square"></i>
              </a>
              <form method="post" style="display:inline;" onsubmit="return confirm('Delete this category? Posts will keep existing without it.');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button type="submit" class="icon-btn danger" data-tip="Delete">
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

<?php require __DIR__ . '/_layout_bottom.php'; ?>
