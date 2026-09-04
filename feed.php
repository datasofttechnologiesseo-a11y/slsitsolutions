<?php
/**
 * RSS 2.0 feed of the latest published blog posts.
 */
header('Content-Type: application/rss+xml; charset=UTF-8');
header('Cache-Control: public, max-age=1800');

$site = 'https://www.slsitsolutions.com';
$posts = [];
try {
  require_once __DIR__ . '/includes/db.php';
  require_once __DIR__ . '/includes/blog.php';
  $posts = db()->query("SELECT id, title, slug, excerpt, content, cover_image, author, published_at, updated_at
                        FROM blogs WHERE is_published = 1
                        ORDER BY published_at DESC, id DESC LIMIT 20")->fetchAll();
} catch (\Throwable $e) {
  $posts = [];
}

function rss_x(string $s): string { return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8'); }

$lastBuild = $posts ? date(DATE_RSS, strtotime($posts[0]['published_at'])) : date(DATE_RSS);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <channel>
    <title>SLS IT Solutions Blog</title>
    <link><?= $site ?>/blog.php</link>
    <atom:link href="<?= $site ?>/feed.php" rel="self" type="application/rss+xml" />
    <description>Practical IT, cybersecurity, backup and infrastructure insights for Indian businesses from SLS IT Solutions, Faridabad.</description>
    <language>en-in</language>
    <lastBuildDate><?= $lastBuild ?></lastBuildDate>
    <image>
      <url><?= $site ?>/assets/images/logo-600.png</url>
      <title>SLS IT Solutions Blog</title>
      <link><?= $site ?>/blog.php</link>
    </image>
<?php foreach ($posts as $p):
  $url   = $site . '/blog-detail.php?slug=' . rawurlencode($p['slug']);
  $cats  = [];
  try { $cats = get_categories_for_blog((int)$p['id']); } catch (\Throwable $e) {}
  $desc  = !empty($p['excerpt']) ? $p['excerpt'] : blog_excerpt($p, 40);
?>
    <item>
      <title><?= rss_x($p['title']) ?></title>
      <link><?= rss_x($url) ?></link>
      <guid isPermaLink="true"><?= rss_x($url) ?></guid>
      <pubDate><?= date(DATE_RSS, strtotime($p['published_at'])) ?></pubDate>
      <dc:creator><?= rss_x($p['author'] ?: 'SLS IT Solutions') ?></dc:creator>
<?php foreach ($cats as $c): ?>
      <category><?= rss_x($c['name']) ?></category>
<?php endforeach; ?>
      <description><?= rss_x($desc) ?></description>
<?php if (!empty($p['cover_image'])): ?>
      <enclosure url="<?= rss_x($site . '/' . ltrim($p['cover_image'], '/')) ?>" type="image/jpeg" length="0" />
<?php endif; ?>
      <content:encoded><![CDATA[<?= str_replace(']]>', ']]]]><![CDATA[>', $p['content']) ?>]]></content:encoded>
    </item>
<?php endforeach; ?>
  </channel>
</rss>
