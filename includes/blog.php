<?php
require_once __DIR__ . '/db.php';

/* ============================================================
 *  Slug helpers
 * ============================================================ */

function slugify(string $text, int $maxLen = 200): string {
    $text = trim($text);
    if (function_exists('iconv')) {
        $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($t !== false) $text = $t;
    }
    $text = strtolower($text);
    $text = preg_replace('~[^a-z0-9]+~', '-', $text);
    $text = trim($text, '-');
    if ($text === '') $text = 'post-' . substr(bin2hex(random_bytes(4)), 0, 6);
    if (strlen($text) > $maxLen) $text = substr($text, 0, $maxLen);
    return rtrim($text, '-');
}

function unique_slug(string $base, string $table, ?int $ignoreId = null): string {
    $base = $base !== '' ? $base : 'item-' . substr(bin2hex(random_bytes(4)), 0, 6);
    $slug = $base;
    $i = 2;
    while (true) {
        $sql = "SELECT id FROM `$table` WHERE slug = ?" . ($ignoreId ? ' AND id <> ?' : '') . ' LIMIT 1';
        $stmt = db()->prepare($sql);
        $params = [$slug];
        if ($ignoreId) $params[] = $ignoreId;
        $stmt->execute($params);
        if (!$stmt->fetch()) return $slug;
        $slug = $base . '-' . $i++;
    }
}

/* ============================================================
 *  HTML sanitizer
 *  Whitelist tags + safe attributes; strips scripts/handlers/javascript:
 * ============================================================ */

function sanitize_html(string $html): string {
    if (trim($html) === '') return '';

    $allowed = [
        'p','br','strong','b','em','i','u','s','strike','del','ins',
        'a','ul','ol','li',
        'h1','h2','h3','h4','h5','h6',
        'blockquote','pre','code','hr',
        'img','figure','figcaption',
        'span','div','small','sub','sup',
        'table','thead','tbody','tr','th','td',
    ];
    $allowedAttrs = [
        '*'      => ['class','style'],
        'a'      => ['href','title','target','rel'],
        'img'    => ['src','alt','title','width','height','loading'],
        'th'     => ['colspan','rowspan'],
        'td'     => ['colspan','rowspan'],
    ];

    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $wrapped = '<?xml encoding="UTF-8"?><div id="__sls_root">' . $html . '</div>';
    $dom->loadHTML($wrapped, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $root = $dom->getElementById('__sls_root');
    if (!$root) return '';

    walk_sanitize($root, $allowed, $allowedAttrs);

    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $dom->saveHTML($child);
    }
    return trim($out);
}

function walk_sanitize(DOMNode $node, array $allowed, array $allowedAttrs): void {
    if (!$node->hasChildNodes()) return;
    $children = iterator_to_array($node->childNodes);
    foreach ($children as $child) {
        if (!($child instanceof DOMElement)) {
            if ($child instanceof DOMComment) {
                $child->parentNode->removeChild($child);
            }
            continue;
        }
        $tag = strtolower($child->nodeName);

        if (!in_array($tag, $allowed, true)) {
            // Drop the tag but keep children inline (unless it's a dangerous tag)
            if (in_array($tag, ['script','style','iframe','object','embed','form','input','button','textarea','select','svg','math'], true)) {
                $child->parentNode->removeChild($child);
            } else {
                while ($child->firstChild) {
                    $child->parentNode->insertBefore($child->firstChild, $child);
                }
                $child->parentNode->removeChild($child);
            }
            continue;
        }

        // Attribute filter
        $attrs = iterator_to_array($child->attributes);
        foreach ($attrs as $attr) {
            $name = strtolower($attr->nodeName);
            $val  = (string)$attr->nodeValue;

            $ok = false;
            if (isset($allowedAttrs['*']) && in_array($name, $allowedAttrs['*'], true)) $ok = true;
            if (isset($allowedAttrs[$tag]) && in_array($name, $allowedAttrs[$tag], true)) $ok = true;

            // Reject all on* handlers
            if (str_starts_with($name, 'on')) $ok = false;

            if (!$ok) { $child->removeAttribute($attr->nodeName); continue; }

            // Filter URL attrs
            if ($name === 'href' || $name === 'src') {
                $clean = trim($val);
                if (preg_match('~^\s*javascript:~i', $clean) || preg_match('~^\s*data:(?!image/(png|jpe?g|gif|webp|svg\+xml))~i', $clean)) {
                    $child->removeAttribute($attr->nodeName);
                    continue;
                }
            }

            // For external links, force rel="noopener"
            if ($tag === 'a' && $name === 'target' && $val === '_blank') {
                $rel = $child->getAttribute('rel');
                if (stripos($rel, 'noopener') === false) {
                    $child->setAttribute('rel', trim($rel . ' noopener noreferrer'));
                }
            }
        }

        walk_sanitize($child, $allowed, $allowedAttrs);
    }
}

/* ============================================================
 *  Mapping helpers (categories ↔ blogs, tags ↔ blogs)
 * ============================================================ */

function get_categories_for_blog(int $blogId): array {
    $stmt = db()->prepare(
        'SELECT c.id, c.name, c.slug FROM blog_categories c
         INNER JOIN blog_category_map m ON m.category_id = c.id
         WHERE m.blog_id = ? ORDER BY c.name'
    );
    $stmt->execute([$blogId]);
    return $stmt->fetchAll();
}

function get_tags_for_blog(int $blogId): array {
    $stmt = db()->prepare(
        'SELECT t.id, t.name, t.slug FROM blog_tags t
         INNER JOIN blog_tag_map m ON m.tag_id = t.id
         WHERE m.blog_id = ? ORDER BY t.name'
    );
    $stmt->execute([$blogId]);
    return $stmt->fetchAll();
}

/** Replace category links for a blog; $categoryIds is an array of int IDs. */
function set_blog_categories(int $blogId, array $categoryIds): void {
    db()->prepare('DELETE FROM blog_category_map WHERE blog_id = ?')->execute([$blogId]);
    $ids = array_unique(array_filter(array_map('intval', $categoryIds), fn($i) => $i > 0));
    if (!$ids) return;
    $ins = db()->prepare('INSERT IGNORE INTO blog_category_map (blog_id, category_id) VALUES (?, ?)');
    foreach ($ids as $cid) $ins->execute([$blogId, $cid]);
}

/** Replace tag links for a blog by tag NAMES (reuses existing, creates new as needed). */
function set_blog_tags_by_names(int $blogId, array $names): void {
    db()->prepare('DELETE FROM blog_tag_map WHERE blog_id = ?')->execute([$blogId]);
    $clean = [];
    foreach ($names as $n) {
        $n = trim((string)$n);
        if ($n === '' || mb_strlen($n) > 60) continue;
        $clean[mb_strtolower($n)] = $n;
    }
    if (!$clean) return;

    $findBySlug = db()->prepare('SELECT id FROM blog_tags WHERE slug = ?');
    $findByName = db()->prepare('SELECT id FROM blog_tags WHERE LOWER(name) = LOWER(?)');
    $insert     = db()->prepare('INSERT INTO blog_tags (name, slug) VALUES (?, ?)');
    $link       = db()->prepare('INSERT IGNORE INTO blog_tag_map (blog_id, tag_id) VALUES (?, ?)');

    foreach ($clean as $name) {
        // First try exact name match
        $findByName->execute([$name]);
        $row = $findByName->fetch();
        if (!$row) {
            // Try matching by canonical slug
            $baseSlug = slugify($name, 80);
            $findBySlug->execute([$baseSlug]);
            $row = $findBySlug->fetch();
            if (!$row) {
                // Genuinely new tag — generate a unique slug
                $slug = unique_slug($baseSlug, 'blog_tags');
                $insert->execute([$name, $slug]);
                $tagId = (int)db()->lastInsertId();
                $link->execute([$blogId, $tagId]);
                continue;
            }
        }
        $link->execute([$blogId, (int)$row['id']]);
    }
}

/* ============================================================
 *  Listing helpers
 * ============================================================ */

function published_blog_count(): int {
    return (int)db()->query("SELECT COUNT(*) FROM blogs WHERE is_published=1")->fetchColumn();
}

function get_blog_by_slug(string $slug): ?array {
    $stmt = db()->prepare('SELECT * FROM blogs WHERE slug = ? AND is_published = 1 LIMIT 1');
    $stmt->execute([$slug]);
    $r = $stmt->fetch();
    return $r ?: null;
}

function get_recent_blogs(int $limit = 3): array {
    $limit = max(1, $limit);
    $stmt = db()->prepare(
        "SELECT id, title, slug, excerpt, cover_image, author, published_at
         FROM blogs WHERE is_published=1
         ORDER BY published_at DESC, id DESC
         LIMIT $limit"
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_categories_with_counts(): array {
    $sql = 'SELECT c.id, c.name, c.slug, c.description,
                   COUNT(DISTINCT m.blog_id) AS post_count
            FROM blog_categories c
            LEFT JOIN blog_category_map m ON m.category_id = c.id
            LEFT JOIN blogs b ON b.id = m.blog_id AND b.is_published = 1
            GROUP BY c.id ORDER BY c.name';
    return db()->query($sql)->fetchAll();
}

function get_top_categories(int $limit = 6): array {
    $limit = max(1, $limit);
    $sql = "SELECT c.id, c.name, c.slug,
                   COUNT(b.id) AS post_count
            FROM blog_categories c
            INNER JOIN blog_category_map m ON m.category_id = c.id
            INNER JOIN blogs b ON b.id = m.blog_id AND b.is_published = 1
            GROUP BY c.id ORDER BY post_count DESC, c.name ASC LIMIT $limit";
    return db()->query($sql)->fetchAll();
}

function blog_excerpt(array $b, int $words = 28): string {
    if (!empty($b['excerpt'])) return $b['excerpt'];
    $text = trim(strip_tags($b['content'] ?? ''));
    $arr  = preg_split('/\s+/', $text);
    if (count($arr) <= $words) return $text;
    return implode(' ', array_slice($arr, 0, $words)) . '…';
}

function blog_cover_url(array $b): ?string {
    return !empty($b['cover_image']) ? $b['cover_image'] : null;
}

function blog_reading_minutes(string $html): int {
    $words = preg_split('/\s+/', trim(strip_tags($html)));
    $count = $words ? count(array_filter($words)) : 0;
    return max(1, (int)ceil($count / 220));
}

function blog_author_initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $out   = '';
    foreach ($parts as $p) {
        if ($p === '') continue;
        $out .= mb_strtoupper(mb_substr($p, 0, 1));
        if (mb_strlen($out) >= 2) break;
    }
    return $out !== '' ? $out : 'A';
}
