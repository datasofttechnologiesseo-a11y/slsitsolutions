<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/blog.php';

$slug = trim((string)($_GET['slug'] ?? ''));
if ($slug === '') { header('Location: blog.php'); exit; }

$post = get_blog_by_slug($slug);
if (!$post) {
    http_response_code(404);
    $page_title = 'Post not found | SLS IT Solutions';
    include 'includes/header.php';
    ?>
    <section class="page-header" style="background:linear-gradient(135deg,#0f172a,#0f4c81);">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 py-20 text-center">
        <h1 class="text-4xl font-bold text-white mb-3" style="font-family:'Poppins',sans-serif;">Post Not Found</h1>
        <p class="text-blue-200 mb-6">The blog post you're looking for doesn't exist or has been removed.</p>
        <a href="blog.php" class="btn-primary">← Back to Blog</a>
      </div>
    </section>
    <?php
    include 'includes/footer.php';
    exit;
}

// Increment views (best-effort)
try {
    db()->prepare('UPDATE blogs SET views = views + 1 WHERE id = ?')->execute([(int)$post['id']]);
} catch (\Throwable $e) {}

$cats = get_categories_for_blog((int)$post['id']);
$tags = get_tags_for_blog((int)$post['id']);

// Related posts — same categories, exclude current
$related = [];
if ($cats) {
    $catIds = array_map(fn($c) => (int)$c['id'], $cats);
    $place  = implode(',', array_fill(0, count($catIds), '?'));
    $stmt = db()->prepare("
        SELECT DISTINCT b.id, b.title, b.slug, b.cover_image, b.published_at
        FROM blogs b
        INNER JOIN blog_category_map m ON m.blog_id = b.id
        WHERE m.category_id IN ($place) AND b.is_published = 1 AND b.id <> ?
        ORDER BY b.published_at DESC LIMIT 3
    ");
    $stmt->execute([...$catIds, (int)$post['id']]);
    $related = $stmt->fetchAll();
}

$page_title       = $post['meta_title'] ?: ($post['title'] . ' | SLS IT Solutions Blog');
$page_description = $post['meta_desc']  ?: blog_excerpt($post, 30);
$canonical        = 'https://www.slsitsolutions.com/blog-detail.php?slug=' . urlencode($post['slug']);
$og_image         = !empty($post['cover_image']) ? 'https://www.slsitsolutions.com/' . $post['cover_image'] : null;

// Sidebar data
$allCategories = get_categories_with_counts();
$allTags       = db()->query("SELECT t.id, t.name, t.slug,
                                     (SELECT COUNT(*) FROM blog_tag_map m INNER JOIN blogs b ON b.id = m.blog_id
                                      WHERE m.tag_id = t.id AND b.is_published = 1) AS post_count
                              FROM blog_tags t
                              HAVING post_count > 0
                              ORDER BY post_count DESC, t.name LIMIT 18")->fetchAll();
$recentExcluding = db()->prepare("SELECT id, title, slug, cover_image, published_at
                                  FROM blogs WHERE is_published = 1 AND id <> ?
                                  ORDER BY published_at DESC, id DESC LIMIT 4");
$recentExcluding->execute([(int)$post['id']]);
$recentPosts = $recentExcluding->fetchAll();

include 'includes/header.php';
?>

<?php
  $readingMinutes = blog_reading_minutes($post['content']);
  $authorInitials = blog_author_initials($post['author']);
  $hasCover       = !empty($post['cover_image']);
  $shareURL       = urlencode($canonical);
  $shareTitle     = urlencode($post['title']);
?>

<!-- ========== POST HERO (compact) ========== -->
<section class="post-hero">
  <div class="post-hero-grid"></div>
  <div class="post-hero-blob post-hero-blob-1"></div>
  <div class="post-hero-blob post-hero-blob-2"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <nav class="post-breadcrumb">
      <a href="index.php"><i class="fas fa-house"></i> Home</a>
      <span class="sep">/</span>
      <a href="blog.php">Blog</a>
      <span class="sep">/</span>
      <span class="here"><?= htmlspecialchars(mb_strimwidth($post['title'], 0, 60, '…')) ?></span>
    </nav>

    <?php if ($cats): ?>
      <div class="post-cats">
        <?php foreach ($cats as $c): ?>
          <a href="blog.php?category=<?= urlencode($c['slug']) ?>"><i class="fas fa-folder"></i> <?= htmlspecialchars($c['name']) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <h1 class="post-title-h"><?= htmlspecialchars($post['title']) ?></h1>

    <?php if (!empty($post['excerpt'])): ?>
      <p class="post-subtitle-h"><?= htmlspecialchars($post['excerpt']) ?></p>
    <?php endif; ?>

    <div class="post-meta-row">
      <div class="post-author">
        <div class="post-author-av"><?= htmlspecialchars($authorInitials) ?></div>
        <div class="post-author-info">
          <div class="post-author-name"><?= htmlspecialchars($post['author']) ?></div>
          <div class="post-author-sub">Author</div>
        </div>
      </div>
      <div class="post-meta-divider"></div>
      <div class="post-meta-item" data-tip="Published">
        <i class="far fa-calendar"></i>
        <span><?= htmlspecialchars(date('d M Y', strtotime($post['published_at']))) ?></span>
      </div>
      <div class="post-meta-item" data-tip="Reading time">
        <i class="far fa-clock"></i>
        <span><?= $readingMinutes ?> min read</span>
      </div>
      <div class="post-meta-item" data-tip="Views">
        <i class="far fa-eye"></i>
        <span><?= number_format((int)$post['views'] + 1) ?> views</span>
      </div>
    </div>
  </div>
</section>

<!-- ========== POST BODY (2-column) ========== -->
<section class="post-body">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="post-grid">

      <!-- ===== Sidebar (left) ===== -->
      <aside class="post-sidebar">

        <!-- Author card -->
        <div class="ps-card">
          <div class="ps-author">
            <div class="ps-author-av"><?= htmlspecialchars($authorInitials) ?></div>
            <div>
              <div class="ps-author-name"><?= htmlspecialchars($post['author']) ?></div>
              <div class="ps-author-sub">Written by</div>
            </div>
          </div>
        </div>

        <!-- Share -->
        <div class="ps-card">
          <div class="ps-head"><i class="fas fa-share-nodes"></i> Share this post</div>
          <div class="ps-share">
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareURL ?>" target="_blank" rel="noopener" data-tip="LinkedIn" class="ps-share-li"><i class="fab fa-linkedin-in"></i></a>
            <a href="https://twitter.com/intent/tweet?url=<?= $shareURL ?>&text=<?= $shareTitle ?>" target="_blank" rel="noopener" data-tip="X (Twitter)" class="ps-share-x"><i class="fab fa-x-twitter"></i></a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareURL ?>" target="_blank" rel="noopener" data-tip="Facebook" class="ps-share-fb"><i class="fab fa-facebook-f"></i></a>
            <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareURL ?>" target="_blank" rel="noopener" data-tip="WhatsApp" class="ps-share-wa"><i class="fab fa-whatsapp"></i></a>
            <button type="button" class="ps-share-copy" data-tip="Copy link" onclick="copyLink(this)"><i class="fas fa-link"></i></button>
          </div>
        </div>

        <!-- Categories -->
        <?php if ($allCategories): ?>
          <div class="ps-card">
            <div class="ps-head"><i class="fas fa-folder"></i> Categories</div>
            <ul class="ps-list">
              <?php $catIdsForPost = array_column($cats, 'id');
                    foreach ($allCategories as $c):
                      if ((int)$c['post_count'] === 0) continue;
                      $isActive = in_array($c['id'], $catIdsForPost);
              ?>
                <li>
                  <a href="blog.php?category=<?= urlencode($c['slug']) ?>" class="<?= $isActive ? 'is-active' : '' ?>">
                    <span><?= htmlspecialchars($c['name']) ?></span>
                    <span class="ps-count"><?= (int)$c['post_count'] ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Tags -->
        <?php if ($allTags): ?>
          <div class="ps-card">
            <div class="ps-head"><i class="fas fa-tags"></i> Popular Tags</div>
            <div class="ps-tags">
              <?php $postTagIds = array_column($tags, 'id');
                    foreach ($allTags as $t):
                      $isActive = in_array($t['id'], $postTagIds);
              ?>
                <a href="blog.php?tag=<?= urlencode($t['slug']) ?>" class="<?= $isActive ? 'is-active' : '' ?>">#<?= htmlspecialchars($t['name']) ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Recent posts -->
        <?php if ($recentPosts): ?>
          <div class="ps-card">
            <div class="ps-head"><i class="fas fa-clock-rotate-left"></i> Recent Posts</div>
            <ul class="ps-recent">
              <?php foreach ($recentPosts as $rp): ?>
                <li>
                  <a href="blog-detail.php?slug=<?= urlencode($rp['slug']) ?>">
                    <?php if (!empty($rp['cover_image'])): ?>
                      <img src="<?= htmlspecialchars($rp['cover_image']) ?>" alt="">
                    <?php else: ?>
                      <div class="ps-recent-ph"><i class="fas fa-newspaper"></i></div>
                    <?php endif; ?>
                    <div class="ps-recent-meta">
                      <div class="ps-recent-title"><?= htmlspecialchars($rp['title']) ?></div>
                      <div class="ps-recent-date"><i class="far fa-calendar"></i> <?= htmlspecialchars(date('d M Y', strtotime($rp['published_at']))) ?></div>
                    </div>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

      </aside>

      <!-- ===== Main content (right) ===== -->
      <main class="post-main">

        <?php if ($hasCover): ?>
          <figure class="post-cover-card">
            <img src="<?= htmlspecialchars($post['cover_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
          </figure>
        <?php endif; ?>

        <article class="post-content">
          <?= $post['content'] /* sanitized at save time */ ?>
        </article>

        <?php if ($tags): ?>
          <div class="post-bottom-block">
            <div class="post-bottom-head"><i class="fas fa-tags"></i> Tagged</div>
            <div class="post-bottom-tags">
              <?php foreach ($tags as $t): ?>
                <a href="blog.php?tag=<?= urlencode($t['slug']) ?>">#<?= htmlspecialchars($t['name']) ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="post-bottom-block">
          <div class="post-bottom-head"><i class="fas fa-share-nodes"></i> Share this post</div>
          <div class="post-bottom-share">
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $shareURL ?>" target="_blank" rel="noopener" class="bs-li"><i class="fab fa-linkedin-in"></i> LinkedIn</a>
            <a href="https://twitter.com/intent/tweet?url=<?= $shareURL ?>&text=<?= $shareTitle ?>" target="_blank" rel="noopener" class="bs-x"><i class="fab fa-x-twitter"></i> X</a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareURL ?>" target="_blank" rel="noopener" class="bs-fb"><i class="fab fa-facebook-f"></i> Facebook</a>
            <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareURL ?>" target="_blank" rel="noopener" class="bs-wa"><i class="fab fa-whatsapp"></i> WhatsApp</a>
          </div>
        </div>

      </main>

    </div>
  </div>
</section>

<!-- ========== RELATED ========== -->
<?php if ($related): ?>
<section class="py-16 bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-10 fade-up">
      <h2 class="text-3xl font-bold mb-2" style="font-family:'Poppins',sans-serif;color:#0f172a;">Related Articles</h2>
      <p class="text-gray-500">More posts you might enjoy.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach ($related as $rp): ?>
        <a href="blog-detail.php?slug=<?= urlencode($rp['slug']) ?>" class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition fade-up block">
          <?php if (!empty($rp['cover_image'])): ?>
            <img src="<?= htmlspecialchars($rp['cover_image']) ?>" alt="" class="w-full h-44 object-cover">
          <?php else: ?>
            <div class="w-full h-44 bg-gradient-to-br from-blue-700 to-blue-900 flex items-center justify-center">
              <i class="fas fa-newspaper text-white text-4xl opacity-40"></i>
            </div>
          <?php endif; ?>
          <div class="p-5">
            <h3 class="font-bold text-gray-900 mb-2 leading-snug" style="font-family:'Poppins',sans-serif;"><?= htmlspecialchars($rp['title']) ?></h3>
            <div class="text-xs text-gray-500"><i class="far fa-calendar mr-1"></i> <?= htmlspecialchars(date('d M Y', strtotime($rp['published_at']))) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-10 fade-up">
      <a href="blog.php" class="btn-outline">← View all posts</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Post styling -->
<style>
/* ============ Hero (compact) ============ */
.post-hero {
  position: relative;
  overflow: hidden;
  /* 9rem top clears the fixed topbar (40px) + navbar (80px) on every page,
     matching the convention used by .page-header on other inner pages. */
  padding: 9rem 0 2.5rem;
  background:
    radial-gradient(1200px 500px at 80% -10%, rgba(0,168,107,0.18), transparent 60%),
    radial-gradient(900px 500px at -10% 100%, rgba(26,107,181,0.30), transparent 60%),
    linear-gradient(135deg, #050b1a 0%, #0b1a35 45%, #0f3a6a 100%);
  color: #fff;
  isolation: isolate;
}
.post-hero-grid {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px);
  background-size: 56px 56px;
  -webkit-mask-image: radial-gradient(ellipse at 50% 30%, #000 30%, transparent 75%);
          mask-image: radial-gradient(ellipse at 50% 30%, #000 30%, transparent 75%);
  opacity: 0.4;
  pointer-events: none;
}
.post-hero-blob {
  position: absolute;
  width: 480px; height: 480px; border-radius: 50%;
  filter: blur(120px); opacity: 0.4;
  pointer-events: none;
}
.post-hero-blob-1 { background: #00a86b; top: -160px; right: -160px; }
.post-hero-blob-2 { background: #1a6bb5; bottom: -160px; left: -180px; }

.post-breadcrumb {
  display: flex; align-items: center; flex-wrap: wrap;
  gap: 8px;
  font-size: 13px;
  color: #94a3b8;
  margin-bottom: 22px;
}
.post-breadcrumb a {
  color: #cbd5e1;
  display: inline-flex; align-items: center; gap: 6px;
  transition: color .15s;
}
.post-breadcrumb a:hover { color: #fff; text-decoration: none; }
.post-breadcrumb .sep { color: #475569; }
.post-breadcrumb .here { color: #fff; font-weight: 500; max-width: 360px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.post-cats { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 18px; }
.post-cats a {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.6px;
  text-transform: uppercase;
  padding: 6px 14px;
  border-radius: 999px;
  background: rgba(0, 168, 107, 0.15);
  color: #6ee7b7;
  border: 1px solid rgba(0, 168, 107, 0.35);
  transition: background .15s, transform .15s, color .15s;
  backdrop-filter: blur(6px);
}
.post-cats a i { font-size: 10px; }
.post-cats a:hover {
  background: rgba(0,168,107,0.28);
  color: #fff;
  transform: translateY(-1px);
  text-decoration: none;
}

.post-title-h {
  font-family: 'Poppins', sans-serif;
  font-weight: 700;
  font-size: clamp(28px, 3.8vw, 44px);
  line-height: 1.15;
  letter-spacing: -0.5px;
  color: #fff;
  margin: 0 0 14px;
  max-width: 920px;
  text-wrap: balance;
}
.post-subtitle-h {
  font-size: clamp(15px, 1.4vw, 17px);
  line-height: 1.6;
  color: #cbd5e1;
  max-width: 820px;
  margin: 0 0 26px;
}

.post-meta-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 18px;
}
.post-author { display: flex; align-items: center; gap: 10px; }
.post-author-av {
  width: 40px; height: 40px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, #00a86b, #00c97f);
  color: #fff;
  font-weight: 700;
  font-size: 14px;
  font-family: 'Poppins', sans-serif;
  box-shadow: 0 6px 20px rgba(0,168,107,0.35);
  border: 2px solid rgba(255,255,255,0.10);
}
.post-author-name {
  color: #fff;
  font-weight: 600;
  font-size: 14px;
  line-height: 1.2;
}
.post-author-sub {
  color: #94a3b8;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.6px;
}
.post-meta-divider {
  width: 1px;
  height: 28px;
  background: rgba(255,255,255,0.14);
  margin: 0 6px;
}
.post-meta-item {
  display: inline-flex; align-items: center; gap: 8px;
  color: #cbd5e1;
  font-size: 13px;
}
.post-meta-item i { color: #6ee7b7; font-size: 13px; }

@media (max-width: 1024px) {
  .post-hero { padding: 7rem 0 2rem; }
}
@media (max-width: 640px) {
  .post-hero { padding: 6rem 0 1.5rem; }
  .post-meta-divider { display: none; }
  .post-meta-row { gap: 14px 18px; }
}

/* ============ Body — 2-column grid ============ */
.post-body { background: #fff; padding: 28px 0 64px; }

.post-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 320px;
  gap: 40px;
  align-items: start;
}
.post-main    { grid-column: 1; grid-row: 1; }
.post-sidebar { grid-column: 2; grid-row: 1; }
@media (max-width: 1024px) {
  .post-grid { grid-template-columns: 1fr; gap: 30px; }
  .post-main    { grid-column: 1; grid-row: auto; }
  .post-sidebar { grid-column: 1; grid-row: auto; }
}

/* ============ Sidebar ============ */
.post-sidebar {
  display: flex;
  flex-direction: column;
  gap: 18px;
  position: sticky;
  /* Site navbar = 80px, topbar = 40px (hides on scroll). 130px keeps clearance in both states. */
  top: 130px;
}
@media (max-width: 1024px) {
  .post-sidebar { position: static; order: 2; top: auto; }
}

.ps-card {
  background: #fff;
  border: 1px solid #e5e9f0;
  border-radius: 14px;
  padding: 18px 20px;
  box-shadow: 0 1px 2px rgba(15,23,42,0.04);
}

.ps-head {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.7px;
  color: #64748b;
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.ps-head i { color: #0f4c81; font-size: 12px; }

/* Author */
.ps-author { display: flex; align-items: center; gap: 12px; }
.ps-author-av {
  width: 50px; height: 50px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, #00a86b, #00c97f);
  color: #fff;
  font-weight: 700;
  font-size: 17px;
  font-family: 'Poppins', sans-serif;
  flex-shrink: 0;
  box-shadow: 0 6px 14px rgba(0,168,107,0.30);
}
.ps-author-name { font-weight: 600; color: #0f172a; font-size: 14px; line-height: 1.2; }
.ps-author-sub { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.6px; margin-top: 2px; }

/* Share */
.ps-share { display: flex; gap: 8px; flex-wrap: wrap; }
.ps-share a, .ps-share button {
  width: 38px; height: 38px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 14px;
  border: 0; cursor: pointer;
  transition: transform .15s, box-shadow .15s, opacity .15s;
}
.ps-share a:hover, .ps-share button:hover { transform: translateY(-2px); text-decoration: none; }
.ps-share-li { background: #0a66c2; }
.ps-share-x  { background: #0f172a; }
.ps-share-fb { background: #1877f2; }
.ps-share-wa { background: #25d366; }
.ps-share-copy { background: #475569; }
.ps-share-copy.copied { background: #00a86b; }

/* List (categories) */
.ps-list { list-style: none; margin: 0; padding: 0; }
.ps-list li + li { margin-top: 4px; }
.ps-list a {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 9px 12px;
  border-radius: 8px;
  color: #334155;
  font-size: 14px;
  transition: background .15s, color .15s;
}
.ps-list a:hover { background: #f1f5f9; color: #0f4c81; text-decoration: none; }
.ps-list a.is-active {
  background: rgba(15,76,129,0.08);
  color: #0f4c81;
  font-weight: 600;
}
.ps-list a.is-active::before {
  content: ''; width: 4px; height: 16px; background: #00a86b; border-radius: 4px; margin-right: 10px; flex-shrink: 0;
}
.ps-list .ps-count {
  background: #f1f5f9;
  color: #64748b;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 999px;
  min-width: 22px;
  text-align: center;
}
.ps-list a:hover .ps-count, .ps-list a.is-active .ps-count { background: #0f4c81; color: #fff; }

/* Tags cloud */
.ps-tags { display: flex; flex-wrap: wrap; gap: 6px; }
.ps-tags a {
  display: inline-block;
  font-size: 12px;
  padding: 5px 10px;
  border-radius: 999px;
  background: #f1f5f9;
  color: #475569;
  transition: background .15s, color .15s;
}
.ps-tags a:hover { background: #0f4c81; color: #fff; text-decoration: none; }
.ps-tags a.is-active { background: rgba(0,168,107,0.15); color: #00a86b; font-weight: 600; }

/* Recent posts */
.ps-recent { list-style: none; margin: 0; padding: 0; }
.ps-recent li + li { margin-top: 12px; padding-top: 12px; border-top: 1px solid #f1f5f9; }
.ps-recent a {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  color: #0f172a;
}
.ps-recent a:hover { text-decoration: none; }
.ps-recent img, .ps-recent-ph {
  width: 60px; height: 48px;
  object-fit: cover;
  border-radius: 8px;
  flex-shrink: 0;
}
.ps-recent-ph {
  background: linear-gradient(135deg, #0f4c81, #1a6bb5);
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  font-size: 16px;
}
.ps-recent-title {
  font-size: 13px;
  font-weight: 600;
  line-height: 1.4;
  color: #0f172a;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  margin-bottom: 4px;
}
.ps-recent a:hover .ps-recent-title { color: #0f4c81; }
.ps-recent-date { font-size: 11px; color: #64748b; }

/* ============ Main content ============ */
.post-main { min-width: 0; }
@media (max-width: 1024px) { .post-main { order: 1; } }

.post-cover-card {
  position: relative;
  margin: 0 0 36px;
  border-radius: 18px;
  overflow: hidden;
  background: #0b1220;
  box-shadow:
    0 16px 40px -12px rgba(15, 23, 42, 0.30),
    0 4px 12px -4px rgba(15, 23, 42, 0.18),
    0 0 0 1px rgba(15, 23, 42, 0.05);
}
.post-cover-card img {
  width: 100%;
  display: block;
  height: auto;
  max-height: 460px;
  object-fit: cover;
}

/* Bottom blocks (tags / share repeated below content) */
.post-bottom-block {
  margin-top: 36px;
  padding-top: 24px;
  border-top: 1px solid #e5e9f0;
}
.post-bottom-head {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  color: #64748b;
  margin-bottom: 12px;
}
.post-bottom-head i { color: #00a86b; margin-right: 4px; }
.post-bottom-tags { display: flex; flex-wrap: wrap; gap: 8px; }
.post-bottom-tags a {
  font-size: 13px;
  padding: 6px 14px;
  border-radius: 999px;
  background: #f1f5f9;
  color: #475569;
  transition: background .15s, color .15s;
}
.post-bottom-tags a:hover { background: #0f4c81; color: #fff; text-decoration: none; }

.post-bottom-share { display: flex; flex-wrap: wrap; gap: 8px; }
.post-bottom-share a {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 9px 16px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  transition: transform .15s, opacity .15s;
}
.post-bottom-share a:hover { transform: translateY(-1px); text-decoration: none; opacity: 0.92; }
.post-bottom-share .bs-li { background: #0a66c2; }
.post-bottom-share .bs-x  { background: #0f172a; }
.post-bottom-share .bs-fb { background: #1877f2; }
.post-bottom-share .bs-wa { background: #25d366; }

/* ============ Article content ============ */
.post-content { font-size: 17px; line-height: 1.8; color: #1f2937; }
.post-content h2 { font-family:'Poppins',sans-serif; font-size: 28px; font-weight: 700; color:#0f172a; margin: 36px 0 14px; }
.post-content h3 { font-family:'Poppins',sans-serif; font-size: 22px; font-weight: 700; color:#0f172a; margin: 28px 0 12px; }
.post-content h4 { font-family:'Poppins',sans-serif; font-size: 18px; font-weight: 600; color:#0f172a; margin: 22px 0 10px; }
.post-content p { margin: 0 0 18px; }
.post-content a { color: #0f4c81; text-decoration: underline; text-underline-offset: 3px; }
.post-content a:hover { color: #00a86b; }
.post-content ul, .post-content ol { margin: 0 0 18px; padding-left: 26px; }
.post-content li { margin-bottom: 8px; }
.post-content blockquote { border-left: 4px solid #00a86b; background:#f8fafc; padding: 14px 22px; margin: 22px 0; color:#334155; font-style: italic; border-radius: 0 8px 8px 0; }
.post-content img { border-radius: 12px; margin: 22px 0; max-width: 100%; height: auto; }
.post-content pre, .post-content code { background:#0f172a; color:#e2e8f0; padding: 2px 7px; border-radius: 5px; font-family: 'JetBrains Mono', monospace; font-size: 14px; }
.post-content pre { padding: 16px 20px; overflow-x: auto; margin: 18px 0; }
.post-content pre code { background: transparent; padding: 0; }
.post-content table { width:100%; border-collapse: collapse; margin: 20px 0; }
.post-content th, .post-content td { border: 1px solid #e5e7eb; padding: 10px 12px; text-align: left; }
.post-content th { background:#f8fafc; font-weight:600; }
.post-content hr { border: 0; border-top: 1px solid #e5e7eb; margin: 30px 0; }
</style>

<script>
function copyLink(btn){
  var url = window.location.href;
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url).then(function(){
      var i = btn.querySelector('i');
      var prev = i.className;
      btn.classList.add('copied');
      i.className = 'fas fa-check';
      setTimeout(function(){ btn.classList.remove('copied'); i.className = prev; }, 1800);
    });
  } else {
    var ta = document.createElement('textarea');
    ta.value = url; document.body.appendChild(ta); ta.select();
    try { document.execCommand('copy'); } catch(e){}
    document.body.removeChild(ta);
  }
}
</script>

<?php include 'includes/footer.php'; ?>
