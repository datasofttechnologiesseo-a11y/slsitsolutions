<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/blog.php';

// Filters from URL
$catSlug = trim((string)($_GET['category'] ?? ''));
$tagSlug = trim((string)($_GET['tag'] ?? ''));
$q       = trim((string)($_GET['q'] ?? ''));

$activeCat = null;
$activeTag = null;

if ($catSlug !== '') {
    $stmt = db()->prepare('SELECT id, name, slug, description FROM blog_categories WHERE slug = ?');
    $stmt->execute([$catSlug]);
    $activeCat = $stmt->fetch() ?: null;
}
if ($tagSlug !== '') {
    $stmt = db()->prepare('SELECT id, name, slug FROM blog_tags WHERE slug = ?');
    $stmt->execute([$tagSlug]);
    $activeTag = $stmt->fetch() ?: null;
}

// Build query
$joins  = '';
$where  = ['b.is_published = 1'];
$params = [];

if ($activeCat) {
    $joins  .= ' INNER JOIN blog_category_map mc ON mc.blog_id = b.id ';
    $where[]  = 'mc.category_id = ?';
    $params[] = (int)$activeCat['id'];
}
if ($activeTag) {
    $joins  .= ' INNER JOIN blog_tag_map mt ON mt.blog_id = b.id ';
    $where[]  = 'mt.tag_id = ?';
    $params[] = (int)$activeTag['id'];
}
if ($q !== '') {
    $where[]  = '(b.title LIKE ? OR b.excerpt LIKE ?)';
    $like = "%$q%";
    array_push($params, $like, $like);
}

$wsql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$perPage = 9;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$cnt = db()->prepare("SELECT COUNT(DISTINCT b.id) FROM blogs b $joins $wsql");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$stmt = db()->prepare("
    SELECT DISTINCT b.id, b.title, b.slug, b.excerpt, b.cover_image, b.author, b.published_at, b.content
    FROM blogs b $joins $wsql
    ORDER BY b.published_at DESC, b.id DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$rows = $stmt->fetchAll();

$catsAll = get_categories_with_counts();

function blog_qs(array $extra = []): string {
    $q = array_merge($_GET, $extra);
    return http_build_query($q);
}

// Page meta
$page_title = $activeCat
    ? htmlspecialchars($activeCat['name']) . ' Articles | SLS IT Solutions Blog'
    : ($activeTag ? '#' . htmlspecialchars($activeTag['name']) . ' | SLS IT Solutions Blog'
                  : 'Insights & Articles | SLS IT Solutions Blog');
$page_description = $activeCat
    ? ($activeCat['description'] ?: ('Articles in '.$activeCat['name'].' from SLS IT Solutions.'))
    : 'Practical IT, cybersecurity, and infrastructure insights from SLS IT Solutions for Indian businesses.';
$canonical = 'https://www.slsitsolutions.com/blog.php';

include 'includes/header.php';
?>

<!-- ========== PAGE HEADER ========== -->
<section class="page-header" style="background-image: linear-gradient(135deg, rgba(15,23,42,0.88), rgba(10,52,96,0.82), rgba(15,76,129,0.78)), url('assets/images/heroes/about-hero.jpg'); background-size: cover; background-position: center;">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="breadcrumb mb-6">
      <a href="index.php">Home</a>
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <?php if ($activeCat || $activeTag): ?>
        <a href="blog.php" style="color:#bfdbfe;">Blog</a>
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white">
          <?= htmlspecialchars($activeCat['name'] ?? ('#' . $activeTag['name'])) ?>
        </span>
      <?php else: ?>
        <span class="text-white">Blog</span>
      <?php endif; ?>
    </div>
    <h1 class="text-4xl md:text-5xl font-bold text-white mb-4" style="font-family:'Poppins',sans-serif;">
      <?php if ($activeCat): ?>
        <?= htmlspecialchars($activeCat['name']) ?>
      <?php elseif ($activeTag): ?>
        Tag: <?= htmlspecialchars($activeTag['name']) ?>
      <?php else: ?>
        Insights &amp; Articles
      <?php endif; ?>
    </h1>
    <p class="text-lg text-blue-200 max-w-2xl">
      <?php if ($activeCat && $activeCat['description']): ?>
        <?= htmlspecialchars($activeCat['description']) ?>
      <?php elseif ($activeTag): ?>
        Posts tagged with <strong><?= htmlspecialchars($activeTag['name']) ?></strong>.
      <?php else: ?>
        Practical guidance on cybersecurity, IT infrastructure, and digital transformation for Indian businesses.
      <?php endif; ?>
    </p>
  </div>
</section>

<!-- ========== BLOG GRID ========== -->
<section class="py-16 bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

      <!-- Sidebar -->
      <aside class="lg:col-span-1 space-y-6">

        <!-- Search -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <h3 class="font-semibold text-gray-900 mb-3" style="font-family:'Poppins',sans-serif;">
            <i class="fas fa-magnifying-glass text-blue-700 mr-2"></i> Search
          </h3>
          <form method="get" action="blog.php">
            <?php if ($activeCat): ?><input type="hidden" name="category" value="<?= htmlspecialchars($activeCat['slug']) ?>"><?php endif; ?>
            <?php if ($activeTag): ?><input type="hidden" name="tag"      value="<?= htmlspecialchars($activeTag['slug']) ?>"><?php endif; ?>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                   class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Search articles...">
          </form>
        </div>

        <!-- Categories -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <h3 class="font-semibold text-gray-900 mb-3" style="font-family:'Poppins',sans-serif;">
            <i class="fas fa-folder text-blue-700 mr-2"></i> Categories
          </h3>
          <ul class="space-y-1">
            <li>
              <a href="blog.php" class="flex items-center justify-between py-2 px-3 rounded-lg <?= !$activeCat ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' ?>">
                <span>All categories</span>
              </a>
            </li>
            <?php foreach ($catsAll as $c): if ((int)$c['post_count'] === 0) continue; ?>
              <li>
                <a href="blog.php?category=<?= urlencode($c['slug']) ?>"
                   class="flex items-center justify-between py-2 px-3 rounded-lg <?= ($activeCat && $activeCat['slug']===$c['slug']) ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' ?>">
                  <span><?= htmlspecialchars($c['name']) ?></span>
                  <span class="text-xs text-gray-400"><?= (int)$c['post_count'] ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Recent posts -->
        <?php $recent = get_recent_blogs(4); if ($recent): ?>
          <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <h3 class="font-semibold text-gray-900 mb-3" style="font-family:'Poppins',sans-serif;">
              <i class="fas fa-clock-rotate-left text-blue-700 mr-2"></i> Recent posts
            </h3>
            <ul class="space-y-3">
              <?php foreach ($recent as $rp): ?>
                <li>
                  <a href="blog-detail.php?slug=<?= urlencode($rp['slug']) ?>" class="flex gap-3 group">
                    <?php if (!empty($rp['cover_image'])): ?>
                      <img src="<?= htmlspecialchars($rp['cover_image']) ?>" alt="" class="w-16 h-12 object-cover rounded-lg flex-shrink-0">
                    <?php else: ?>
                      <div class="w-16 h-12 bg-gradient-to-br from-blue-100 to-blue-50 rounded-lg flex-shrink-0 flex items-center justify-center text-blue-400">
                        <i class="fas fa-newspaper"></i>
                      </div>
                    <?php endif; ?>
                    <div class="min-w-0">
                      <div class="text-sm text-gray-900 font-medium group-hover:text-blue-700 line-clamp-2"><?= htmlspecialchars($rp['title']) ?></div>
                      <div class="text-xs text-gray-500 mt-1"><?= htmlspecialchars(date('d M Y', strtotime($rp['published_at']))) ?></div>
                    </div>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

      </aside>

      <!-- Main grid -->
      <div class="lg:col-span-3">

        <?php if ($q !== ''): ?>
          <div class="mb-5 text-sm text-gray-600">
            <?= $total ?> result<?= $total===1?'':'s' ?> for "<strong><?= htmlspecialchars($q) ?></strong>"
            <a href="blog.php" class="ml-2 text-blue-700 hover:underline">Clear</a>
          </div>
        <?php endif; ?>

        <?php if (!$rows): ?>
          <div class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-sm">
            <i class="far fa-newspaper text-4xl text-gray-300 mb-3"></i>
            <h3 class="text-lg font-semibold text-gray-800 mb-1">No posts found</h3>
            <p class="text-gray-500 text-sm">Try a different search or category.</p>
          </div>
        <?php else: ?>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($rows as $r):
              $bcats = get_categories_for_blog((int)$r['id']);
            ?>
              <article class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition fade-up flex flex-col">
                <a href="blog-detail.php?slug=<?= urlencode($r['slug']) ?>" class="block">
                  <?php if (!empty($r['cover_image'])): ?>
                    <img src="<?= htmlspecialchars($r['cover_image']) ?>" alt="<?= htmlspecialchars($r['title']) ?>" class="w-full h-48 object-cover">
                  <?php else: ?>
                    <div class="w-full h-48 bg-gradient-to-br from-blue-700 to-blue-900 flex items-center justify-center">
                      <i class="fas fa-newspaper text-white text-4xl opacity-40"></i>
                    </div>
                  <?php endif; ?>
                </a>
                <div class="p-5 flex-1 flex flex-col">
                  <?php if ($bcats): ?>
                    <div class="flex flex-wrap gap-2 mb-3">
                      <?php foreach (array_slice($bcats, 0, 2) as $c): ?>
                        <a href="blog.php?category=<?= urlencode($c['slug']) ?>" class="text-xs font-semibold uppercase tracking-wider text-blue-700 bg-blue-50 px-3 py-1 rounded-full hover:bg-blue-100"><?= htmlspecialchars($c['name']) ?></a>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                  <h3 class="text-lg font-bold text-gray-900 mb-2 leading-snug" style="font-family:'Poppins',sans-serif;">
                    <a href="blog-detail.php?slug=<?= urlencode($r['slug']) ?>" class="hover:text-blue-700"><?= htmlspecialchars($r['title']) ?></a>
                  </h3>
                  <p class="text-sm text-gray-600 leading-relaxed mb-4 flex-1"><?= htmlspecialchars(blog_excerpt($r, 24)) ?></p>
                  <div class="flex items-center justify-between text-xs text-gray-500 pt-3 border-t border-gray-100">
                    <span><i class="far fa-calendar mr-1"></i> <?= htmlspecialchars(date('d M Y', strtotime($r['published_at']))) ?></span>
                    <a href="blog-detail.php?slug=<?= urlencode($r['slug']) ?>" class="text-blue-700 font-semibold hover:underline">Read more →</a>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

          <?php if ($pages > 1):
            $first = max(1, $page - 2); $last = min($pages, $page + 2);
          ?>
            <nav class="mt-10 flex justify-center items-center gap-2">
              <?php if ($page > 1): ?>
                <a href="?<?= blog_qs(['page'=>$page-1]) ?>" class="px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"><i class="fas fa-chevron-left"></i></a>
              <?php endif; ?>
              <?php for ($i=$first; $i<=$last; $i++): ?>
                <?php if ($i === $page): ?>
                  <span class="px-4 py-2 rounded-lg bg-blue-700 text-white font-semibold"><?= $i ?></span>
                <?php else: ?>
                  <a href="?<?= blog_qs(['page'=>$i]) ?>" class="px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"><?= $i ?></a>
                <?php endif; ?>
              <?php endfor; ?>
              <?php if ($page < $pages): ?>
                <a href="?<?= blog_qs(['page'=>$page+1]) ?>" class="px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"><i class="fas fa-chevron-right"></i></a>
              <?php endif; ?>
            </nav>
          <?php endif; ?>

        <?php endif; ?>

      </div>
    </div>

  </div>
</section>

<?php include 'includes/footer.php'; ?>
