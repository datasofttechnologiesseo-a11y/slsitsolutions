<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/testimonial_helpers.php';
require_admin();

$pageTitle = 'Testimonial';

$id    = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$errors = [];
$notFound = false;

$row = [
    'client_name'  => '',
    'company'      => '',
    'quote'        => '',
    'initials'     => '',
    'avatar_color' => 'blue',
    'rating'       => 5,
    'sort_order'   => 0,
    'is_active'    => 1,
];

if ($isEdit) {
    $stmt = db()->prepare('SELECT * FROM testimonials WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        $notFound = true;
    } else {
        $row = array_merge($row, $found);
    }
}

if (!$notFound && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $row['client_name']  = trim((string)($_POST['client_name']  ?? ''));
    $row['company']      = trim((string)($_POST['company']      ?? ''));
    $row['quote']        = trim((string)($_POST['quote']        ?? ''));
    $row['initials']     = trim((string)($_POST['initials']     ?? ''));
    $row['avatar_color'] = (string)($_POST['avatar_color'] ?? 'blue');
    $row['rating']       = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $row['sort_order']   = (int)($_POST['sort_order'] ?? 0);
    $row['is_active']    = isset($_POST['is_active']) ? 1 : 0;

    if ($row['client_name'] === '' || mb_strlen($row['client_name']) > 120) $errors[] = 'Client name is required.';
    if ($row['quote'] === '') $errors[] = 'Quote is required.';
    if (!array_key_exists($row['avatar_color'], testimonial_palette())) $row['avatar_color'] = 'blue';
    if ($row['initials'] === '') $row['initials'] = testimonial_initials($row['client_name']);
    $row['initials'] = mb_substr($row['initials'], 0, 4);

    if (!$errors) {
        // Only the 8 editable columns — $row may contain extra keys (id, created_at, updated_at)
        // when editing, which would break PDO named-param binding.
        $data = [
            'client_name'  => $row['client_name'],
            'company'      => $row['company'],
            'quote'        => $row['quote'],
            'initials'     => $row['initials'],
            'avatar_color' => $row['avatar_color'],
            'rating'       => $row['rating'],
            'sort_order'   => $row['sort_order'],
            'is_active'    => $row['is_active'],
        ];

        if ($isEdit) {
            $sql = 'UPDATE testimonials SET client_name=:client_name, company=:company, quote=:quote,
                    initials=:initials, avatar_color=:avatar_color, rating=:rating,
                    sort_order=:sort_order, is_active=:is_active WHERE id=:id';
            $data['id'] = $id;
        } else {
            $sql = 'INSERT INTO testimonials (client_name, company, quote, initials, avatar_color, rating, sort_order, is_active)
                    VALUES (:client_name, :company, :quote, :initials, :avatar_color, :rating, :sort_order, :is_active)';
        }
        db()->prepare($sql)->execute($data);
        $_SESSION['flash'] = ['type'=>'success', 'msg'=> $isEdit ? 'Testimonial updated.' : 'Testimonial added.'];
        header('Location: testimonials.php');
        exit;
    }
}

$palette = testimonial_palette();

require __DIR__ . '/_layout_top.php';

if ($notFound) {
    echo '<div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> Testimonial not found.</div>';
    require __DIR__ . '/_layout_bottom.php';
    exit;
}
?>
<div class="page-head">
  <div>
    <a href="testimonials.php" style="font-size:13px;color:var(--text-mute);">
      <i class="fa-solid fa-arrow-left"></i> Back to testimonials
    </a>
    <h1 style="margin-top:6px;"><?= $isEdit ? 'Edit Testimonial' : 'Add Testimonial' ?></h1>
    <p><?= $isEdit ? 'Update an existing client testimonial.' : 'Create a new client testimonial for the home page.' ?></p>
  </div>
</div>

<?php if ($errors): ?>
  <div class="alert alert-error">
    <i class="fa-solid fa-circle-exclamation"></i>
    <span><?= htmlspecialchars(implode(' ', $errors)) ?></span>
  </div>
<?php endif; ?>

<form method="post">
  <?= csrf_field() ?>

  <div class="row" style="grid-template-columns: 2fr 1fr;">

    <div class="card">
      <div class="field">
        <label>Client Name *</label>
        <input class="input" type="text" name="client_name" required maxlength="120"
               value="<?= htmlspecialchars($row['client_name']) ?>" oninput="updatePreview()">
      </div>
      <div class="field">
        <label>Company / Location</label>
        <input class="input" type="text" name="company" maxlength="150"
               value="<?= htmlspecialchars($row['company']) ?>" oninput="updatePreview()"
               placeholder="e.g. Rahul Technic, Faridabad">
      </div>
      <div class="field">
        <label>Quote / Testimonial *</label>
        <textarea class="textarea" name="quote" required oninput="updatePreview()"><?= htmlspecialchars($row['quote']) ?></textarea>
      </div>

      <div class="row">
        <div class="field">
          <label>Initials (avatar)</label>
          <input class="input" type="text" name="initials" maxlength="4" id="initialsInput"
                 value="<?= htmlspecialchars($row['initials']) ?>" oninput="updatePreview()"
                 placeholder="Auto from name">
        </div>
        <div class="field">
          <label>Rating</label>
          <select class="select" name="rating" onchange="updatePreview()">
            <?php for ($i=5;$i>=1;$i--): ?>
              <option value="<?= $i ?>" <?= $row['rating']==$i?'selected':'' ?>><?= $i ?> ★</option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="field">
          <label>Display Order</label>
          <input class="input" type="number" name="sort_order" value="<?= (int)$row['sort_order'] ?>">
        </div>
      </div>

      <div class="field">
        <label>Avatar Color</label>
        <div class="swatch-grid" id="swatches">
          <?php foreach ($palette as $key => $g):
            $grad = testimonial_gradient($key);
            $active = $key === $row['avatar_color'];
          ?>
            <div class="swatch <?= $active?'active':'' ?>"
                 style="background:<?= htmlspecialchars($grad) ?>;"
                 data-color="<?= htmlspecialchars($key) ?>"
                 onclick="pickColor(this)"
                 title="<?= htmlspecialchars(ucfirst($key)) ?>"></div>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="avatar_color" id="avatar_color" value="<?= htmlspecialchars($row['avatar_color']) ?>">
      </div>

      <div class="field">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
          <input type="checkbox" name="is_active" value="1" <?= $row['is_active']?'checked':'' ?>>
          <span>Show on website</span>
        </label>
      </div>

      <div style="display:flex;gap:10px;margin-top:18px;">
        <button class="btn btn-success" type="submit">
          <i class="fa-solid <?= $isEdit ? 'fa-floppy-disk' : 'fa-plus' ?>"></i>
          <?= $isEdit ? 'Update' : 'Create' ?> Testimonial
        </button>
        <a class="btn btn-ghost" href="testimonials.php">
          <i class="fa-solid fa-xmark"></i> Cancel
        </a>
      </div>
    </div>

    <div class="card">
      <h2 style="margin:0 0 12px;font-size:13px;color:var(--text-mute);text-transform:uppercase;letter-spacing:0.5px;">
        <i class="fa-regular fa-eye"></i> Live Preview
      </h2>
      <div class="testi-preview">
        <div class="stars" id="pvStars"><?= str_repeat('★',(int)$row['rating']) ?></div>
        <div class="quote" id="pvQuote">"<?= htmlspecialchars($row['quote'] ?: 'Your testimonial quote will appear here as you type...') ?>"</div>
        <div class="author">
          <div class="av" id="pvAv" style="background:<?= htmlspecialchars(testimonial_gradient($row['avatar_color'])) ?>;">
            <?= htmlspecialchars($row['initials'] ?: testimonial_initials($row['client_name'] ?: '?')) ?>
          </div>
          <div>
            <div class="name" id="pvName"><?= htmlspecialchars($row['client_name'] ?: 'Client Name') ?></div>
            <div class="co"   id="pvCo"  ><?= htmlspecialchars($row['company']     ?: 'Company') ?></div>
          </div>
        </div>
      </div>
      <p style="color:var(--text-mute);font-size:12px;margin-top:14px;">
        <i class="fa-solid fa-circle-info"></i>
        This is approximately how the card will appear in the slider on the home page.
      </p>
    </div>

  </div>
</form>

<script>
var palette = <?= json_encode(array_map(fn($k)=>testimonial_gradient($k), array_combine(array_keys($palette), array_keys($palette)))) ?>;

function pickColor(el){
  document.querySelectorAll('.swatch').forEach(function(s){ s.classList.remove('active'); });
  el.classList.add('active');
  var key = el.dataset.color;
  document.getElementById('avatar_color').value = key;
  document.getElementById('pvAv').style.background = palette[key];
}

function autoInitials(name){
  if (!name) return '?';
  var parts = name.trim().split(/\s+/);
  var out = '';
  for (var i = 0; i < parts.length && out.length < 2; i++) {
    if (parts[i]) out += parts[i][0].toUpperCase();
  }
  return out || '?';
}

function updatePreview(){
  var name    = document.querySelector('[name=client_name]').value;
  var company = document.querySelector('[name=company]').value;
  var quote   = document.querySelector('[name=quote]').value;
  var rating  = parseInt(document.querySelector('[name=rating]').value, 10);
  var initEl  = document.getElementById('initialsInput');

  document.getElementById('pvName').textContent  = name || 'Client Name';
  document.getElementById('pvCo').textContent    = company || 'Company';
  document.getElementById('pvQuote').textContent = '"' + (quote || 'Your testimonial quote will appear here as you type...') + '"';
  document.getElementById('pvStars').textContent = '★'.repeat(rating);
  document.getElementById('pvAv').textContent    = (initEl.value || autoInitials(name)).toUpperCase().slice(0,4);
}
</script>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
