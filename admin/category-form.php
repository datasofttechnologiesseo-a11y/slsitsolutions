<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/blog.php';
require_admin();

$pageTitle = 'Category';

$id     = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$errors = [];
$notFound = false;

$row = ['name' => '', 'slug' => '', 'description' => ''];

if ($isEdit) {
    $stmt = db()->prepare('SELECT * FROM blog_categories WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) $notFound = true;
    else $row = array_merge($row, $found);
}

if (!$notFound && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $row['name']        = trim((string)($_POST['name']        ?? ''));
    $row['slug']        = trim((string)($_POST['slug']        ?? ''));
    $row['description'] = trim((string)($_POST['description'] ?? ''));

    if ($row['name'] === '' || mb_strlen($row['name']) > 100) $errors[] = 'Name is required (max 100 chars).';
    if (mb_strlen($row['description']) > 255) $errors[] = 'Description too long (max 255 chars).';

    if ($row['slug'] === '') $row['slug'] = slugify($row['name']);
    $row['slug'] = unique_slug(slugify($row['slug']), 'blog_categories', $isEdit ? $id : null);

    if (!$errors) {
        if ($isEdit) {
            $stmt = db()->prepare('UPDATE blog_categories SET name=?, slug=?, description=? WHERE id=?');
            $stmt->execute([$row['name'], $row['slug'], $row['description'], $id]);
        } else {
            $stmt = db()->prepare('INSERT INTO blog_categories (name, slug, description) VALUES (?, ?, ?)');
            $stmt->execute([$row['name'], $row['slug'], $row['description']]);
        }
        $_SESSION['flash'] = ['type'=>'success', 'msg'=>$isEdit ? 'Category updated.' : 'Category added.'];
        header('Location: categories.php');
        exit;
    }
}

require __DIR__ . '/_layout_top.php';

if ($notFound) {
    echo '<div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> Category not found.</div>';
    require __DIR__ . '/_layout_bottom.php';
    exit;
}
?>
<div class="page-head">
  <div>
    <a href="categories.php" style="font-size:13px;color:var(--text-mute);">
      <i class="fa-solid fa-arrow-left"></i> Back to categories
    </a>
    <h1 style="margin-top:6px;"><?= $isEdit ? 'Edit Category' : 'Add Category' ?></h1>
  </div>
</div>

<?php if ($errors): ?>
  <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><span><?= htmlspecialchars(implode(' ', $errors)) ?></span></div>
<?php endif; ?>

<form method="post" class="card" style="max-width:640px;">
  <?= csrf_field() ?>
  <div class="field">
    <label>Name *</label>
    <input class="input" type="text" name="name" id="cname" required maxlength="100" autofocus
           value="<?= htmlspecialchars($row['name']) ?>">
  </div>
  <div class="field">
    <label>URL Slug</label>
    <div style="display:flex;gap:8px;align-items:center;">
      <span style="color:var(--text-mute);font-size:13px;">/blog/category/</span>
      <input class="input" type="text" name="slug" id="cslug" maxlength="120"
             value="<?= htmlspecialchars($row['slug']) ?>" placeholder="auto-generated-from-name">
    </div>
  </div>
  <div class="field">
    <label>Description</label>
    <textarea class="textarea" name="description" maxlength="255" style="min-height:80px;"
              placeholder="Brief description of what this category covers (optional)."><?= htmlspecialchars($row['description']) ?></textarea>
  </div>

  <div style="display:flex;gap:10px;">
    <button type="submit" class="btn btn-success">
      <i class="fa-solid <?= $isEdit ? 'fa-floppy-disk' : 'fa-plus' ?>"></i>
      <?= $isEdit ? 'Update' : 'Create' ?>
    </button>
    <a href="categories.php" class="btn btn-ghost"><i class="fa-solid fa-xmark"></i> Cancel</a>
  </div>
</form>

<script>
(function(){
  var name = document.getElementById('cname'), slug = document.getElementById('cslug');
  var touched = <?= json_encode($row['slug'] !== '') ?>;
  slug.addEventListener('input', function(){ touched = true; });
  name.addEventListener('input', function(){
    if (touched) return;
    slug.value = name.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'').slice(0,120);
  });
})();
</script>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
