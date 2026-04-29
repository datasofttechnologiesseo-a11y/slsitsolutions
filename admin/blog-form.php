<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/blog.php';
require_admin();

$pageTitle = 'Blog Post';

$id     = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$errors = [];
$notFound = false;

$row = [
    'title'        => '',
    'slug'         => '',
    'excerpt'      => '',
    'content'      => '',
    'cover_image'  => '',
    'author'       => 'SLS IT Solutions',
    'is_published' => 0,
    'meta_title'   => '',
    'meta_desc'    => '',
];

$selectedCatIds = [];
$selectedTagNames = [];

if ($isEdit) {
    $stmt = db()->prepare('SELECT * FROM blogs WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        $notFound = true;
    } else {
        $row = array_merge($row, $found);
        $selectedCatIds   = array_column(get_categories_for_blog($id), 'id');
        $selectedTagNames = array_column(get_tags_for_blog($id), 'name');
    }
}

if (!$notFound && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $row['title']       = trim((string)($_POST['title']       ?? ''));
    $row['slug']        = trim((string)($_POST['slug']        ?? ''));
    $row['excerpt']     = trim((string)($_POST['excerpt']     ?? ''));
    $row['content']     = (string)($_POST['content']         ?? '');
    $row['cover_image'] = trim((string)($_POST['cover_image'] ?? ''));
    $row['author']      = trim((string)($_POST['author']      ?? 'SLS IT Solutions'));
    $row['meta_title']  = trim((string)($_POST['meta_title']  ?? ''));
    $row['meta_desc']   = trim((string)($_POST['meta_desc']   ?? ''));

    $publishAction = $_POST['publish_action'] ?? 'draft'; // 'publish' or 'draft'
    $row['is_published'] = $publishAction === 'publish' ? 1 : 0;

    $selectedCatIds   = array_map('intval', (array)($_POST['categories'] ?? []));
    $tagsRaw          = (string)($_POST['tags'] ?? '');
    $selectedTagNames = array_filter(array_map('trim', explode(',', $tagsRaw)), fn($s) => $s !== '');

    if ($row['title'] === '' || mb_strlen($row['title']) > 200) $errors[] = 'Title is required (max 200 chars).';
    if (mb_strlen(strip_tags($row['content'])) < 30) $errors[] = 'Content is too short — please write at least 30 characters.';
    if (mb_strlen($row['excerpt']) > 500) $errors[] = 'Excerpt is too long (max 500 chars).';

    // Sanitize HTML
    $row['content'] = sanitize_html($row['content']);

    // Slug
    if ($row['slug'] === '') $row['slug'] = slugify($row['title']);
    $row['slug'] = unique_slug(slugify($row['slug']), 'blogs', $isEdit ? $id : null);

    if (!$errors) {
        if ($isEdit) {
            $sql = 'UPDATE blogs SET
                    title=:title, slug=:slug, excerpt=:excerpt, content=:content,
                    cover_image=:cover_image, author=:author, is_published=:is_published,
                    meta_title=:meta_title, meta_desc=:meta_desc,
                    published_at = CASE WHEN :pub2 = 1 AND published_at IS NULL THEN NOW() ELSE published_at END
                    WHERE id=:id';
            $params = [
                'title'=>$row['title'], 'slug'=>$row['slug'], 'excerpt'=>$row['excerpt'],
                'content'=>$row['content'], 'cover_image'=>$row['cover_image'],
                'author'=>$row['author'], 'is_published'=>$row['is_published'],
                'meta_title'=>$row['meta_title'], 'meta_desc'=>$row['meta_desc'],
                'pub2'=>$row['is_published'], 'id'=>$id,
            ];
            db()->prepare($sql)->execute($params);
            $blogId = $id;
        } else {
            $sql = 'INSERT INTO blogs (title, slug, excerpt, content, cover_image, author, is_published, meta_title, meta_desc, published_at)
                    VALUES (:title, :slug, :excerpt, :content, :cover_image, :author, :is_published, :meta_title, :meta_desc,
                            CASE WHEN :pub2 = 1 THEN NOW() ELSE NULL END)';
            $params = [
                'title'=>$row['title'], 'slug'=>$row['slug'], 'excerpt'=>$row['excerpt'],
                'content'=>$row['content'], 'cover_image'=>$row['cover_image'],
                'author'=>$row['author'], 'is_published'=>$row['is_published'],
                'meta_title'=>$row['meta_title'], 'meta_desc'=>$row['meta_desc'],
                'pub2'=>$row['is_published'],
            ];
            db()->prepare($sql)->execute($params);
            $blogId = (int)db()->lastInsertId();
        }

        set_blog_categories($blogId, $selectedCatIds);
        set_blog_tags_by_names($blogId, $selectedTagNames);

        $_SESSION['flash'] = ['type'=>'success', 'msg' => $isEdit ? 'Blog post updated.' : ($row['is_published'] ? 'Blog post published.' : 'Draft saved.')];
        header('Location: blogs.php');
        exit;
    }
}

$allCats = db()->query('SELECT id, name FROM blog_categories ORDER BY name')->fetchAll();

require __DIR__ . '/_layout_top.php';

if ($notFound) {
    echo '<div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> Blog post not found.</div>';
    require __DIR__ . '/_layout_bottom.php';
    exit;
}
?>
<div class="page-head">
  <div>
    <a href="blogs.php" style="font-size:13px;color:var(--text-mute);">
      <i class="fa-solid fa-arrow-left"></i> Back to posts
    </a>
    <h1 style="margin-top:6px;"><?= $isEdit ? 'Edit Post' : 'New Post' ?></h1>
    <p><?= $isEdit ? 'Update an existing blog post.' : 'Write and publish a new blog post.' ?></p>
  </div>
</div>

<?php if ($errors): ?>
  <div class="alert alert-error">
    <i class="fa-solid fa-circle-exclamation"></i>
    <span><?= htmlspecialchars(implode(' ', $errors)) ?></span>
  </div>
<?php endif; ?>

<!-- Quill 2.x — free, no API key, MIT licensed -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css">
<style>
  .ql-toolbar.ql-snow, .ql-container.ql-snow { border-color: var(--border); }
  .ql-toolbar.ql-snow { border-top-left-radius: 10px; border-top-right-radius: 10px; background:#fafbfd; }
  .ql-container.ql-snow { border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; min-height: 360px; font-family: 'Inter', sans-serif; font-size: 15px; }
  .ql-editor { min-height: 360px; line-height: 1.65; }
  .ql-editor h2 { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 22px; margin-top: 18px; }
  .ql-editor h3 { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 18px; margin-top: 14px; }
  .ql-editor img { max-width: 100%; height: auto; border-radius: 8px; }

  .cover-uploader {
    border: 2px dashed var(--border);
    border-radius: 12px;
    padding: 22px;
    text-align: center;
    color: var(--text-mute);
    background: #fafbfd;
    cursor: pointer;
    transition: border-color .15s, background .15s;
  }
  .cover-uploader:hover { border-color: var(--primary); background: #eef6fc; color: var(--primary); }
  .cover-uploader.has-image { padding: 0; border-style: solid; }
  .cover-uploader img { width: 100%; max-height: 220px; object-fit: cover; border-radius: 10px; display: block; }
  .cover-uploader.has-image .ph { display: none; }

  .chip-input-wrap {
    display:flex; flex-wrap:wrap; gap:6px;
    padding:8px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: #fff;
    min-height: 44px;
    align-items: center;
    position: relative;
  }
  .chip-input-wrap:focus-within { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(15,76,129,0.10); }
  .chip {
    background: rgba(15,76,129,0.08);
    color: var(--primary);
    padding: 4px 10px 4px 12px;
    border-radius: 999px;
    font-size: 13px;
    display: flex; align-items: center; gap: 6px;
  }
  .chip button { background:transparent; border:0; color:var(--primary); cursor:pointer; font-size:14px; padding:0; line-height:1; }
  .chip-input-wrap input.chip-input { flex:1; min-width: 120px; border:0; outline:none; padding:6px 4px; font-size:14px; background:transparent; }
  .tag-suggest {
    position: absolute; top: 100%; left:0; right:0;
    background:#fff; border:1px solid var(--border); border-radius:10px;
    box-shadow: var(--shadow-lg);
    margin-top:4px; max-height: 220px; overflow-y:auto;
    z-index: 10;
  }
  .tag-suggest div { padding:9px 12px; cursor:pointer; font-size:14px; }
  .tag-suggest div:hover, .tag-suggest div.active { background: var(--bg); }

  .cat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 8px;
  }
  .cat-pill {
    display: flex; align-items:center; gap:8px;
    padding: 9px 12px;
    border: 1px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    background: #fff;
    transition: background .15s, border-color .15s;
    font-size: 13px;
  }
  .cat-pill:hover { background: var(--bg); }
  .cat-pill input { margin: 0; }
  .cat-pill.checked { background: rgba(0,168,107,0.08); border-color: rgba(0,168,107,0.4); color: #065f46; font-weight:600; }
</style>

<form method="post" id="blogForm">
  <?= csrf_field() ?>
  <input type="hidden" name="content" id="contentField">
  <input type="hidden" name="publish_action" id="publishAction" value="<?= $row['is_published'] ? 'publish' : 'draft' ?>">

  <div class="row" style="grid-template-columns: 2fr 1fr; align-items:flex-start;">

    <div>
      <div class="card">
        <div class="field">
          <label>Title *</label>
          <input class="input" type="text" name="title" id="titleInput" maxlength="200" required
                 value="<?= htmlspecialchars($row['title']) ?>" placeholder="Your post title">
        </div>
        <div class="field">
          <label>URL Slug</label>
          <div style="display:flex;gap:8px;align-items:center;">
            <span style="color:var(--text-mute);font-size:13px;">/blog/</span>
            <input class="input" type="text" name="slug" id="slugInput" maxlength="220"
                   value="<?= htmlspecialchars($row['slug']) ?>" placeholder="auto-generated-from-title">
          </div>
        </div>

        <div class="field">
          <label>Excerpt <span style="color:var(--text-mute);font-weight:400;">(short summary, optional)</span></label>
          <textarea class="textarea" name="excerpt" maxlength="500" style="min-height:80px;"
                    placeholder="A 1-2 line summary used in listings."><?= htmlspecialchars($row['excerpt']) ?></textarea>
        </div>

        <div class="field">
          <label>Content *</label>
          <div id="editor"></div>
        </div>
      </div>

      <div class="card" style="margin-top:18px;">
        <h3 style="margin:0 0 12px;font-size:13px;color:var(--text-mute);text-transform:uppercase;letter-spacing:0.5px;">
          <i class="fa-solid fa-magnifying-glass-chart"></i> SEO
        </h3>
        <div class="field">
          <label>Meta Title</label>
          <input class="input" type="text" name="meta_title" maxlength="200"
                 value="<?= htmlspecialchars($row['meta_title']) ?>" placeholder="Used as the &lt;title&gt; tag">
        </div>
        <div class="field">
          <label>Meta Description</label>
          <textarea class="textarea" name="meta_desc" maxlength="300" style="min-height:70px;"
                    placeholder="Short description for search engines (140-160 chars)."><?= htmlspecialchars($row['meta_desc']) ?></textarea>
        </div>
      </div>
    </div>

    <div>
      <!-- Publish box -->
      <div class="card">
        <h3 style="margin:0 0 12px;font-size:13px;color:var(--text-mute);text-transform:uppercase;letter-spacing:0.5px;">
          <i class="fa-solid fa-rocket"></i> Publish
        </h3>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <button type="submit" class="btn btn-success" onclick="document.getElementById('publishAction').value='publish';">
            <i class="fa-solid fa-paper-plane"></i> <?= $row['is_published'] ? 'Update Post' : 'Publish Post' ?>
          </button>
          <button type="submit" class="btn btn-ghost" onclick="document.getElementById('publishAction').value='draft';">
            <i class="fa-regular fa-floppy-disk"></i> Save Draft
          </button>
        </div>
        <div style="margin-top:12px;color:var(--text-mute);font-size:12px;">
          <?php if ($isEdit): ?>
            Status:
            <?php if ($row['is_published']): ?>
              <span class="badge badge-active"><i class="fa-solid fa-circle"></i> Published</span>
            <?php else: ?>
              <span class="badge badge-inactive"><i class="fa-solid fa-circle"></i> Draft</span>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Cover image -->
      <div class="card" style="margin-top:18px;">
        <h3 style="margin:0 0 12px;font-size:13px;color:var(--text-mute);text-transform:uppercase;letter-spacing:0.5px;">
          <i class="fa-regular fa-image"></i> Cover Image
        </h3>
        <div class="cover-uploader <?= $row['cover_image'] ? 'has-image' : '' ?>" id="coverDrop">
          <?php if ($row['cover_image']): ?>
            <img src="../<?= htmlspecialchars($row['cover_image']) ?>" id="coverPreview">
          <?php endif; ?>
          <div class="ph">
            <i class="fa-solid fa-cloud-arrow-up" style="font-size:28px;color:var(--text-dim);margin-bottom:8px;"></i>
            <div>Click to upload cover</div>
            <div style="font-size:12px;margin-top:4px;">JPG, PNG, WEBP (max 5MB)</div>
          </div>
        </div>
        <input type="hidden" name="cover_image" id="coverField" value="<?= htmlspecialchars($row['cover_image']) ?>">
        <?php if (!empty($row['cover_image'])): ?>
          <button type="button" class="btn btn-ghost btn-sm" style="margin-top:10px;width:100%;justify-content:center;" onclick="removeCover()">
            <i class="fa-solid fa-trash"></i> Remove cover
          </button>
        <?php endif; ?>
      </div>

      <!-- Categories -->
      <div class="card" style="margin-top:18px;">
        <h3 style="margin:0 0 12px;font-size:13px;color:var(--text-mute);text-transform:uppercase;letter-spacing:0.5px;">
          <i class="fa-solid fa-folder"></i> Categories
        </h3>
        <?php if (!$allCats): ?>
          <p style="color:var(--text-mute);font-size:13px;">No categories yet. <a href="categories.php">Add categories</a> first.</p>
        <?php else: ?>
          <div class="cat-grid">
            <?php foreach ($allCats as $c):
              $checked = in_array((int)$c['id'], $selectedCatIds, true);
            ?>
              <label class="cat-pill <?= $checked?'checked':'' ?>">
                <input type="checkbox" name="categories[]" value="<?= (int)$c['id'] ?>" <?= $checked?'checked':'' ?>
                       onchange="this.parentElement.classList.toggle('checked', this.checked)">
                <span><?= htmlspecialchars($c['name']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Tags -->
      <div class="card" style="margin-top:18px;">
        <h3 style="margin:0 0 12px;font-size:13px;color:var(--text-mute);text-transform:uppercase;letter-spacing:0.5px;">
          <i class="fa-solid fa-tags"></i> Tags
        </h3>
        <div class="chip-input-wrap" id="chipWrap">
          <!-- chips inserted here -->
          <input type="text" class="chip-input" id="tagInput" placeholder="Type a tag and press Enter">
          <div class="tag-suggest" id="tagSuggest" style="display:none;"></div>
        </div>
        <input type="hidden" name="tags" id="tagsField" value="<?= htmlspecialchars(implode(',', $selectedTagNames)) ?>">
        <p style="color:var(--text-mute);font-size:12px;margin-top:8px;">
          <i class="fa-regular fa-circle-question"></i>
          Press Enter or comma to add. New tags are created automatically.
        </p>
      </div>

      <!-- Author -->
      <div class="card" style="margin-top:18px;">
        <div class="field" style="margin:0;">
          <label>Author</label>
          <input class="input" type="text" name="author" maxlength="120"
                 value="<?= htmlspecialchars($row['author']) ?>">
        </div>
      </div>
    </div>

  </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
(function(){
  /* ===== Slug auto-generate ===== */
  var titleInput = document.getElementById('titleInput');
  var slugInput  = document.getElementById('slugInput');
  var slugTouched = <?= json_encode($row['slug'] !== '') ?>;
  slugInput.addEventListener('input', function(){ slugTouched = true; });
  titleInput.addEventListener('input', function(){
    if (slugTouched) return;
    var s = this.value.toLowerCase()
      .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g,'');
    slugInput.value = s.slice(0, 220);
  });

  /* ===== Quill ===== */
  var toolbarOptions = [
    [{ 'header': [2, 3, 4, false] }],
    ['bold','italic','underline','strike'],
    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
    [{ 'align': [] }],
    ['blockquote','code-block'],
    ['link','image'],
    ['clean']
  ];

  var quill = new Quill('#editor', {
    theme: 'snow',
    placeholder: 'Write your post...',
    modules: { toolbar: toolbarOptions }
  });

  var initial = <?= json_encode($row['content']) ?>;
  if (initial) quill.clipboard.dangerouslyPasteHTML(initial);

  // Custom image upload — POST to /api/upload.php
  function selectImage(){
    var input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = function(){
      var file = input.files[0];
      if (!file) return;
      var fd = new FormData();
      fd.append('file', file);
      fetch('../api/upload.php', { method:'POST', body: fd, credentials:'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(j){
          if (j && j.success && j.url) {
            var range = quill.getSelection(true);
            quill.insertEmbed(range.index, 'image', '../' + j.url, 'user');
            quill.setSelection(range.index + 1);
          } else {
            alert(j.message || 'Upload failed.');
          }
        })
        .catch(function(){ alert('Upload failed.'); });
    };
    input.click();
  }
  quill.getModule('toolbar').addHandler('image', selectImage);

  /* ===== Form submit — sync editor HTML into hidden field ===== */
  document.getElementById('blogForm').addEventListener('submit', function(){
    document.getElementById('contentField').value = quill.root.innerHTML;
  });

  /* ===== Cover upload ===== */
  var coverDrop = document.getElementById('coverDrop');
  var coverField = document.getElementById('coverField');

  coverDrop.addEventListener('click', function(){
    var input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = function(){
      var file = input.files[0];
      if (!file) return;
      var fd = new FormData();
      fd.append('file', file);
      coverDrop.classList.add('uploading');
      fetch('../api/upload.php', { method:'POST', body: fd, credentials:'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(j){
          if (j && j.success && j.url) {
            coverField.value = j.url;
            coverDrop.innerHTML =
              '<img src="../' + j.url + '" id="coverPreview">' +
              '<div class="ph"><i class="fa-solid fa-cloud-arrow-up" style="font-size:28px;color:var(--text-dim);margin-bottom:8px;"></i>' +
              '<div>Click to upload cover</div></div>';
            coverDrop.classList.add('has-image');
          } else {
            alert(j.message || 'Upload failed.');
          }
        })
        .catch(function(){ alert('Upload failed.'); });
    };
    input.click();
  });

  window.removeCover = function(){
    coverField.value = '';
    coverDrop.classList.remove('has-image');
    coverDrop.innerHTML =
      '<div class="ph"><i class="fa-solid fa-cloud-arrow-up" style="font-size:28px;color:var(--text-dim);margin-bottom:8px;"></i>' +
      '<div>Click to upload cover</div>' +
      '<div style="font-size:12px;margin-top:4px;">JPG, PNG, WEBP (max 5MB)</div></div>';
  };

  /* ===== Tag chips ===== */
  var chipWrap   = document.getElementById('chipWrap');
  var tagInput   = document.getElementById('tagInput');
  var tagsField  = document.getElementById('tagsField');
  var suggest    = document.getElementById('tagSuggest');
  var tags       = (tagsField.value || '').split(',').map(function(s){return s.trim();}).filter(Boolean);

  function renderChips(){
    chipWrap.querySelectorAll('.chip').forEach(function(n){ n.remove(); });
    tags.forEach(function(t, i){
      var span = document.createElement('span');
      span.className = 'chip';
      span.innerHTML = '<span></span><button type="button" aria-label="remove">&times;</button>';
      span.querySelector('span').textContent = t;
      span.querySelector('button').onclick = function(){
        tags.splice(i, 1);
        syncTags();
      };
      chipWrap.insertBefore(span, tagInput);
    });
    syncTags();
  }
  function syncTags(){
    tagsField.value = tags.join(',');
    renderChips_no_recurse();
  }
  // Avoid render loop
  function renderChips_no_recurse(){
    chipWrap.querySelectorAll('.chip').forEach(function(n){ n.remove(); });
    tags.forEach(function(t, i){
      var span = document.createElement('span');
      span.className = 'chip';
      span.innerHTML = '<span></span><button type="button" aria-label="remove">&times;</button>';
      span.querySelector('span').textContent = t;
      span.querySelector('button').onclick = function(){
        tags.splice(i, 1);
        tagsField.value = tags.join(',');
        renderChips_no_recurse();
      };
      chipWrap.insertBefore(span, tagInput);
    });
    tagsField.value = tags.join(',');
  }

  function addTag(name){
    name = (name || '').trim().replace(/,+$/,'').trim();
    if (!name) return;
    if (tags.some(function(t){ return t.toLowerCase() === name.toLowerCase(); })) return;
    if (name.length > 60) name = name.slice(0, 60);
    tags.push(name);
    tagInput.value = '';
    suggest.style.display = 'none';
    renderChips_no_recurse();
  }

  tagInput.addEventListener('keydown', function(e){
    if (e.key === 'Enter' || e.key === ',') {
      e.preventDefault();
      addTag(tagInput.value);
    } else if (e.key === 'Backspace' && tagInput.value === '' && tags.length) {
      tags.pop();
      renderChips_no_recurse();
    }
  });

  var fetchTimer = null;
  tagInput.addEventListener('input', function(){
    clearTimeout(fetchTimer);
    var q = tagInput.value.trim();
    if (!q) { suggest.style.display = 'none'; return; }
    fetchTimer = setTimeout(function(){
      fetch('../api/tag-search.php?q=' + encodeURIComponent(q), { credentials:'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(j){
          var matches = (j.tags || []).filter(function(t){
            return !tags.some(function(x){ return x.toLowerCase() === t.name.toLowerCase(); });
          });
          if (!matches.length) { suggest.style.display = 'none'; return; }
          suggest.innerHTML = '';
          matches.forEach(function(t){
            var d = document.createElement('div');
            d.textContent = t.name;
            d.onclick = function(){ addTag(t.name); tagInput.focus(); };
            suggest.appendChild(d);
          });
          suggest.style.display = 'block';
        });
    }, 180);
  });

  document.addEventListener('click', function(e){
    if (!chipWrap.contains(e.target)) suggest.style.display = 'none';
  });

  renderChips_no_recurse();
})();
</script>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
