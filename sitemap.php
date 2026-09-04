<?php
/**
 * Dynamic XML sitemap.
 * Served at /sitemap.php and (via .htaccess rewrite) at /sitemap.xml.
 * Static pages are listed first, then every published blog post, then blog categories.
 */
header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: public, max-age=3600');

$site = 'https://www.slsitsolutions.com';

$static = [
  ['/',                    '1.0', 'weekly'],
  ['/services.php',        '0.9', 'monthly'],
  ['/security.php',        '0.8', 'monthly'],
  ['/backup.php',          '0.8', 'monthly'],
  ['/infrastructure.php',  '0.8', 'monthly'],
  ['/support.php',         '0.8', 'monthly'],
  ['/about.php',           '0.8', 'monthly'],
  ['/blog.php',            '0.8', 'weekly'],
  ['/contact.php',         '0.7', 'monthly'],
];

$posts = [];
$cats  = [];
$latestPost = null;
try {
  require_once __DIR__ . '/includes/db.php';
  $posts = db()->query("SELECT slug, published_at, updated_at FROM blogs WHERE is_published = 1 ORDER BY published_at DESC")->fetchAll();
  $cats  = db()->query("SELECT DISTINCT c.slug, MAX(b.updated_at) AS updated_at
                         FROM blog_categories c
                         INNER JOIN blog_category_map m ON m.category_id = c.id
                         INNER JOIN blogs b ON b.id = m.blog_id AND b.is_published = 1
                         GROUP BY c.slug")->fetchAll();
  if ($posts) $latestPost = $posts[0];
} catch (\Throwable $e) {
  // DB unavailable: emit static pages only
}

function sm_date($dt): string {
  $ts = $dt ? strtotime($dt) : false;
  return date('c', $ts ?: time());
}

// lastmod for static pages: the file's own modification time
function sm_file_lastmod(string $path): string {
  $file = __DIR__ . '/' . ($path === '/' ? 'index.php' : ltrim($path, '/'));
  return date('c', is_file($file) ? filemtime($file) : time());
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

foreach ($static as [$path, $prio, $freq]) {
  $lastmod = ($path === '/blog.php' && $latestPost) ? sm_date($latestPost['updated_at'] ?: $latestPost['published_at']) : sm_file_lastmod($path);
  echo "  <url>\n";
  echo "    <loc>" . htmlspecialchars($site . $path, ENT_XML1) . "</loc>\n";
  echo "    <lastmod>{$lastmod}</lastmod>\n";
  echo "    <changefreq>{$freq}</changefreq>\n";
  echo "    <priority>{$prio}</priority>\n";
  echo "  </url>\n";
}

foreach ($posts as $p) {
  $loc = $site . '/blog-detail.php?slug=' . rawurlencode($p['slug']);
  echo "  <url>\n";
  echo "    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
  echo "    <lastmod>" . sm_date($p['updated_at'] ?: $p['published_at']) . "</lastmod>\n";
  echo "    <changefreq>monthly</changefreq>\n";
  echo "    <priority>0.7</priority>\n";
  echo "  </url>\n";
}

foreach ($cats as $c) {
  $loc = $site . '/blog.php?category=' . rawurlencode($c['slug']);
  echo "  <url>\n";
  echo "    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
  echo "    <lastmod>" . sm_date($c['updated_at']) . "</lastmod>\n";
  echo "    <changefreq>weekly</changefreq>\n";
  echo "    <priority>0.5</priority>\n";
  echo "  </url>\n";
}

echo "</urlset>\n";
