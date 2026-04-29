<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/blog.php';
require_admin();

$pageTitle = 'Tags';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '' || mb_strlen($name) > 60) {
            $errors[] = 'Tag name is required (max 60 chars).';
        } else {
            $slug = unique_slug(slugify($name, 80), 'blog_tags');
            db()->prepare('INSERT IGNORE INTO blog_tags (name, slug) VALUES (?, ?)')->execute([$name, $slug]);
            $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Tag added.'];
            header('Location: tags.php'); exit;
        }
    } elseif ($action === 'rename') {
        $id   = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        if ($id > 0 && $name !== '' && mb_strlen($name) <= 60) {
            $slug = unique_slug(slugify($name, 80), 'blog_tags', $id);
            db()->prepare('UPDATE blog_tags SET name=?, slug=? WHERE id=?')->execute([$name, $slug, $id]);
            $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Tag updated.'];
        }
        header('Location: tags.php'); exit;
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            db()->prepare('DELETE FROM blog_tags WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = ['type'=>'success', 'msg'=>'Tag deleted.'];
        }
        header('Location: tags.php'); exit;
    }
}

$rows = db()->query("
    SELECT t.id, t.name, t.slug,
           (SELECT COUNT(*) FROM blog_tag_map m WHERE m.tag_id = t.id) AS post_count
    FROM blog_tags t
    ORDER BY t.name
")->fetchAll();

require __DIR__ . '/_layout_top.php';
?>
<div class="page-head">
  <div>
    <h1>Tags</h1>
    <p><?= count($rows) ?> total</p>
  </div>
</div>

<?php if ($errors): ?>
  <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><span><?= htmlspecialchars(implode(' ', $errors)) ?></span></div>
<?php endif; ?>

<form method="post" class="card" style="margin-bottom:18px;">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="add">
  <div style="display:flex;gap:10px;align-items:flex-end;">
    <div class="field" style="flex:1;margin:0;">
      <label>Add a new tag</label>
      <input class="input" type="text" name="name" maxlength="60" required placeholder="e.g. Phishing, Cloud Security, ISO 27001">
    </div>
    <button class="btn btn-success" type="submit"><i class="fa-solid fa-plus"></i> Add</button>
  </div>
</form>

<div class="table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th>Tag</th>
        <th>Slug</th>
        <th style="width:100px;">Posts</th>
        <th style="width:160px;text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="4">
        <div class="empty">
          <i class="fa-solid fa-tags"></i>
          <div class="et">No tags yet</div>
          <div class="es">Tags get created automatically when you add them to a post, or use the form above.</div>
        </div>
      </td></tr>
    <?php else: foreach ($rows as $r): ?>
      <tr id="row-<?= (int)$r['id'] ?>">
        <td>
          <span class="tag-name"><strong><?= htmlspecialchars($r['name']) ?></strong></span>
          <form method="post" class="tag-rename" style="display:none;gap:6px;align-items:center;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="rename">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <input class="input" name="name" type="text" maxlength="60" value="<?= htmlspecialchars($r['name']) ?>" style="max-width:240px;">
            <button class="btn btn-success btn-sm" type="submit"><i class="fa-solid fa-check"></i></button>
            <button class="btn btn-ghost btn-sm" type="button" onclick="cancelRename(<?= (int)$r['id'] ?>)"><i class="fa-solid fa-xmark"></i></button>
          </form>
        </td>
        <td><span style="color:var(--text-mute);font-family:monospace;font-size:12px;">/<?= htmlspecialchars($r['slug']) ?></span></td>
        <td><span class="badge badge-read"><i class="fa-regular fa-newspaper"></i> <?= (int)$r['post_count'] ?></span></td>
        <td style="text-align:right;">
          <div class="action-cell" style="justify-content:flex-end;">
            <button class="icon-btn" type="button" data-tip="Rename" onclick="startRename(<?= (int)$r['id'] ?>)">
              <i class="fa-regular fa-pen-to-square"></i>
            </button>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this tag? Posts will keep existing without it.');">
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

<script>
function startRename(id){
  var row = document.getElementById('row-' + id);
  row.querySelector('.tag-name').style.display = 'none';
  row.querySelector('.tag-rename').style.display = 'flex';
  row.querySelector('.tag-rename input[name=name]').focus();
}
function cancelRename(id){
  var row = document.getElementById('row-' + id);
  row.querySelector('.tag-name').style.display = '';
  row.querySelector('.tag-rename').style.display = 'none';
}
</script>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
