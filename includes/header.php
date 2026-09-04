<?php
/**
 * Shared <head> + navigation for every public page.
 *
 * Pages may set any of these before including this file:
 *   $page_title, $page_description, $page_keywords, $page_robots, $canonical,
 *   $page_type (WebPage|AboutPage|ContactPage|CollectionPage|ItemPage),
 *   $og_type, $og_title, $og_description, $og_url, $og_image, $og_image_alt,
 *   $twitter_title, $twitter_description, $preload_image,
 *   $breadcrumbs     = [['name'=>'Home','url'=>'https://…/'], …]  (auto-built if omitted)
 *   $structured_data = [ [...schema.org array...], … ]            (extra JSON-LD nodes)
 *   $article_meta    = ['published'=>ISO8601,'modified'=>ISO8601,'author'=>'','section'=>'','tags'=>[]]
 *   $extra_head      = raw HTML appended to <head>
 */
$site_url     = 'https://www.slsitsolutions.com';
$site_name    = 'SLS IT Solutions';
$current_page = basename($_SERVER['PHP_SELF'], '.php');

$page_title          = $page_title          ?? 'SLS IT Solutions | Managed IT Services, Cybersecurity & IT Support in Faridabad, Delhi NCR';
$page_description    = !empty($page_description) ? $page_description : 'SLS IT Solutions delivers managed IT services, cybersecurity, backup & disaster recovery, IT infrastructure and 24/7 support for businesses across Faridabad, Delhi NCR & India.';
$page_keywords       = $page_keywords       ?? '';
$page_robots         = $page_robots         ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
$page_type           = $page_type           ?? 'WebPage';
$canonical           = !empty($canonical) ? $canonical : ($site_url . ($current_page === 'index' ? '/' : '/' . $current_page . '.php'));
$og_type             = $og_type             ?? 'website';
$og_title            = $og_title            ?? $page_title;
$og_description      = $og_description      ?? $page_description;
$og_url              = !empty($og_url) ? $og_url : $canonical;
$default_og_image    = $site_url . '/assets/images/og-image.jpg';
$og_image            = !empty($og_image) ? $og_image : $default_og_image;
$og_image_alt        = $og_image_alt        ?? 'SLS IT Solutions - Managed IT, Cybersecurity, Backup & 24/7 Support';
$twitter_title       = $twitter_title       ?? $og_title;
$twitter_description = $twitter_description ?? $og_description;
$preload_image       = $preload_image       ?? null;
$breadcrumbs         = $breadcrumbs         ?? [];
$structured_data     = $structured_data     ?? [];
$article_meta        = $article_meta        ?? null;
$extra_head          = $extra_head          ?? '';

function nav_active($page) {
  global $current_page;
  return $current_page === $page ? 'active' : '';
}

// ---- Auto breadcrumbs for standard pages -----------------------------------
if (!$breadcrumbs && $current_page !== 'index') {
  $crumb_map = [
    'about'          => [['About Us', '/about.php']],
    'services'       => [['Services', '/services.php']],
    'security'       => [['Services', '/services.php'], ['Security Solutions', '/security.php']],
    'backup'         => [['Services', '/services.php'], ['Backup & Disaster Recovery', '/backup.php']],
    'infrastructure' => [['Services', '/services.php'], ['IT Infrastructure Solutions', '/infrastructure.php']],
    'support'        => [['Services', '/services.php'], ['IT Support & Consultancy', '/support.php']],
    'contact'        => [['Contact', '/contact.php']],
    'blog'           => [['Blog', '/blog.php']],
  ];
  $breadcrumbs = [['name' => 'Home', 'url' => $site_url . '/']];
  foreach ($crumb_map[$current_page] ?? [] as [$n, $u]) {
    $breadcrumbs[] = ['name' => $n, 'url' => $site_url . $u];
  }
}

// ---- Site-wide JSON-LD graph -----------------------------------------------
$org_id  = $site_url . '/#organization';
$site_id = $site_url . '/#website';

$graph = [
  [
    '@type'         => ['LocalBusiness', 'ProfessionalService'],
    '@id'           => $org_id,
    'name'          => $site_name,
    'alternateName' => 'SLS IT',
    'slogan'        => 'Service First',
    'description'   => 'Managed IT services, cybersecurity, backup & disaster recovery, IT infrastructure and 24/7 IT support for businesses in Faridabad, Delhi NCR and across India.',
    'url'           => $site_url . '/',
    'logo'          => ['@type' => 'ImageObject', 'url' => $site_url . '/assets/images/logo-600.png', 'width' => 600, 'height' => 147],
    'image'         => $default_og_image,
    'telephone'     => '+91-8383800914',
    'email'         => 'sales@slsitsolutions.com',
    'priceRange'    => '₹₹',
    'currenciesAccepted' => 'INR',
    'address'       => [
      '@type'           => 'PostalAddress',
      'streetAddress'   => 'Arya Nagar, Sector-2, Ballabgarh',
      'addressLocality' => 'Faridabad',
      'addressRegion'   => 'Haryana',
      'postalCode'      => '121004',
      'addressCountry'  => 'IN',
    ],
    'geo'           => ['@type' => 'GeoCoordinates', 'latitude' => 28.3290893, 'longitude' => 77.3336346],
    'hasMap'        => 'https://www.google.com/maps/search/?api=1&query=SLS+IT+SOLUTIONS&query_place_id=ChIJafRPhTTbDDkRQgElcopYxq4',
    'areaServed'    => [
      ['@type' => 'City', 'name' => 'Faridabad'],
      ['@type' => 'City', 'name' => 'Delhi'],
      ['@type' => 'City', 'name' => 'Gurugram'],
      ['@type' => 'City', 'name' => 'Noida'],
      ['@type' => 'City', 'name' => 'Ghaziabad'],
      ['@type' => 'Country', 'name' => 'India'],
    ],
    'openingHoursSpecification' => [[
      '@type'     => 'OpeningHoursSpecification',
      'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
      'opens'     => '09:00',
      'closes'    => '18:00',
    ]],
    'contactPoint'  => [[
      '@type'             => 'ContactPoint',
      'telephone'         => '+91-8383800914',
      'email'             => 'sales@slsitsolutions.com',
      'contactType'       => 'sales',
      'areaServed'        => 'IN',
      'availableLanguage' => ['en', 'hi'],
    ]],
    'knowsAbout'    => ['Managed IT Services', 'Cybersecurity', 'Endpoint Security', 'Firewall Management', 'Backup and Disaster Recovery', 'IT Infrastructure', 'Virtualization', 'Networking', 'DPDP Act Compliance', 'IT Support'],
    'sameAs'        => [
      'https://www.linkedin.com/company/slsitsolutions',
      'https://x.com/slsitsolutions',
      'https://www.facebook.com/slsitsolutions',
    ],
  ],
  [
    '@type'       => 'WebSite',
    '@id'         => $site_id,
    'url'         => $site_url . '/',
    'name'        => $site_name,
    'publisher'   => ['@id' => $org_id],
    'inLanguage'  => 'en-IN',
    'potentialAction' => [
      '@type'       => 'SearchAction',
      'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => $site_url . '/blog.php?q={search_term_string}'],
      'query-input' => 'required name=search_term_string',
    ],
  ],
  [
    '@type'       => $page_type,
    '@id'         => $canonical . '#webpage',
    'url'         => $canonical,
    'name'        => $page_title,
    'description' => $page_description,
    'isPartOf'    => ['@id' => $site_id],
    'about'       => ['@id' => $org_id],
    'inLanguage'  => 'en-IN',
    'primaryImageOfPage' => ['@type' => 'ImageObject', 'url' => $og_image],
  ] + ($breadcrumbs ? ['breadcrumb' => ['@id' => $canonical . '#breadcrumb']] : []),
];

if ($breadcrumbs) {
  $items = [];
  foreach ($breadcrumbs as $i => $bc) {
    $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $bc['name'], 'item' => $bc['url']];
  }
  $graph[] = ['@type' => 'BreadcrumbList', '@id' => $canonical . '#breadcrumb', 'itemListElement' => $items];
}
foreach ($structured_data as $node) { $graph[] = $node; }

$json_ld = json_encode(['@context' => 'https://schema.org', '@graph' => $graph],
  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#0f4c81">
  <meta name="color-scheme" content="light">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
  <?php if ($page_keywords): ?><meta name="keywords" content="<?= htmlspecialchars($page_keywords) ?>"><?php endif; ?>
  <meta name="robots" content="<?= htmlspecialchars($page_robots) ?>">
  <meta name="author" content="SLS IT Solutions">
  <meta name="geo.region" content="IN-HR">
  <meta name="geo.placename" content="Ballabgarh, Faridabad, Haryana">
  <meta name="geo.position" content="28.3290893;77.3336346">
  <meta name="ICBM" content="28.3290893, 77.3336346">
  <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">

  <!-- Open Graph -->
  <meta property="og:type" content="<?= htmlspecialchars($og_type) ?>">
  <meta property="og:site_name" content="SLS IT Solutions">
  <meta property="og:locale" content="en_IN">
  <meta property="og:title" content="<?= htmlspecialchars($og_title) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($og_description) ?>">
  <meta property="og:url" content="<?= htmlspecialchars($og_url) ?>">
  <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
  <meta property="og:image:secure_url" content="<?= htmlspecialchars($og_image) ?>">
  <?php if ($og_image === $default_og_image): ?>
  <meta property="og:image:type" content="image/jpeg">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <?php endif; ?>
  <meta property="og:image:alt" content="<?= htmlspecialchars($og_image_alt) ?>">
  <?php if ($og_type === 'article' && $article_meta): ?>
  <?php if (!empty($article_meta['published'])): ?><meta property="article:published_time" content="<?= htmlspecialchars($article_meta['published']) ?>"><?php endif; ?>
  <?php if (!empty($article_meta['modified'])): ?><meta property="article:modified_time" content="<?= htmlspecialchars($article_meta['modified']) ?>"><?php endif; ?>
  <?php if (!empty($article_meta['author'])): ?><meta property="article:author" content="<?= htmlspecialchars($article_meta['author']) ?>"><?php endif; ?>
  <?php if (!empty($article_meta['section'])): ?><meta property="article:section" content="<?= htmlspecialchars($article_meta['section']) ?>"><?php endif; ?>
  <?php foreach (($article_meta['tags'] ?? []) as $t): ?><meta property="article:tag" content="<?= htmlspecialchars($t) ?>">
  <?php endforeach; ?>
  <?php endif; ?>

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="@slsitsolutions">
  <meta name="twitter:creator" content="@slsitsolutions">
  <meta name="twitter:title" content="<?= htmlspecialchars($twitter_title) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($twitter_description) ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($og_image) ?>">
  <meta name="twitter:image:alt" content="<?= htmlspecialchars($og_image_alt) ?>">

  <!-- Icons -->
  <link rel="icon" href="assets/images/favicon-32.png" sizes="32x32" type="image/png">
  <link rel="icon" href="assets/images/logo-icon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="assets/images/apple-touch-icon.png">
  <link rel="manifest" href="site.webmanifest">

  <!-- Feeds -->
  <link rel="alternate" type="application/rss+xml" title="SLS IT Solutions Blog" href="<?= $site_url ?>/feed.php">

  <!-- Structured Data -->
  <script type="application/ld+json">
<?= $json_ld ?>

  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
  <link rel="dns-prefetch" href="https://www.google.com">

  <?php if ($preload_image): ?>
  <!-- Preload the hero (LCP) image -->
  <link rel="preload" as="image" href="<?= htmlspecialchars($preload_image) ?>" fetchpriority="high">
  <?php endif; ?>

  <!-- Critical CSS inlined for fast first paint -->
  <style>
    :root{--primary:#0f4c81;--secondary:#00a86b;--accent:#f59e0b;--dark:#0f172a;--text:#1e293b;--text-light:#64748b;--bg:#ffffff;--bg-alt:#f8fafc;--radius:12px;--shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -2px rgba(0,0,0,.1)}
    *,::after,::before{box-sizing:border-box;margin:0;padding:0}
    html{-webkit-text-size-adjust:100%;text-size-adjust:100%}
    body{font-family:'Inter',system-ui,-apple-system,sans-serif;color:var(--text);line-height:1.6;overflow-x:hidden;-webkit-font-smoothing:antialiased}
    .skip-link{position:absolute;left:8px;top:-100px;z-index:2000;background:var(--primary);color:#fff;padding:10px 16px;border-radius:8px;font-weight:600;text-decoration:none;transition:top .2s}
    .skip-link:focus{top:8px;outline:3px solid var(--accent)}
    .topbar{background:var(--dark);color:#94a3b8;font-size:.8rem;position:fixed;top:0;left:0;right:0;z-index:1000;transition:transform .3s ease}
    .topbar-inner{height:40px}
    .topbar.hidden{transform:translateY(-100%)}
    .topbar-inner{display:flex;justify-content:space-between;align-items:center}
    .topbar-left{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
    .topbar-item{color:#94a3b8;text-decoration:none;display:flex;align-items:center;gap:6px;transition:color .2s}
    .topbar-item:hover{color:#fff}
    .topbar-item i{color:var(--secondary);font-size:.75rem}
    .topbar-divider{width:1px;height:16px;background:#334155}
    .topbar-right{display:flex;align-items:center}
    .topbar-social{display:flex;gap:12px}
    .topbar-social a{color:#94a3b8;font-size:.85rem;transition:color .2s}
    .topbar-social a:hover{color:#fff}
    .navbar{position:fixed;top:40px;left:0;right:0;background:rgba(255,255,255,.97);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);z-index:999;transition:all .3s ease;border-bottom:1px solid rgba(15,76,129,.06)}
    .navbar.scrolled{top:0;box-shadow:0 4px 20px rgba(15,76,129,.08)}
    .nav-links{font-family:'Poppins',sans-serif}
    .nav-link{color:var(--text);font-weight:500;font-size:.95rem;padding:8px 4px;transition:color .2s;text-decoration:none;position:relative}
    .nav-link:hover,.nav-link.active{color:var(--primary)}
    .btn-primary{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;background:linear-gradient(135deg,var(--primary),#1a6bb5);color:#fff;font-weight:600;font-size:.95rem;border-radius:var(--radius);text-decoration:none;transition:all .3s ease;border:none;cursor:pointer;font-family:'Poppins',sans-serif}
    .btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(15,76,129,.3)}
    .logo-img{height:3rem;width:auto;max-width:none;flex-shrink:0}.logo-link{flex-shrink:0}
    .max-w-7xl{max-width:80rem;margin-left:auto;margin-right:auto}
    .hero-gradient{position:relative;min-height:100vh;display:flex;align-items:center;overflow:hidden}
    .hero-gradient::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,#0a2540 0%,#0f4c81 40%,#1a6bb5 70%,#0f4c81 100%);z-index:0}
    .hamburger{width:28px;height:20px;display:flex;flex-direction:column;justify-content:space-between;cursor:pointer;background:none;border:0;padding:0}
    .hamburger span{display:block;height:2px;background:var(--text);border-radius:2px;transition:all .3s}
    @media(max-width:1023px){.topbar-left{gap:8px}.topbar-divider,.topbar-right{display:none}.topbar{font-size:.7rem}}
    @media(prefers-reduced-motion:reduce){*,::before,::after{animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important;scroll-behavior:auto!important}.fade-up{opacity:1!important;transform:none!important}}
  </style>
  <noscript><style>.fade-up{opacity:1!important;transform:none!important}</style></noscript>

  <!-- Non-critical CSS loaded async -->
  <link rel="stylesheet" href="assets/css/tailwind.min.css">
  <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>
  <link rel="stylesheet" href="assets/css/style.css">

  <!-- Google Fonts loaded async -->
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap"></noscript>

  <?= $extra_head ?>
</head>
<body>
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <!-- Topbar -->
  <div class="topbar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="topbar-inner">
        <div class="topbar-left">
          <a href="tel:+918383800914" class="topbar-item">
            <i class="fas fa-phone"></i> +91 8383800914
          </a>
          <div class="topbar-divider"></div>
          <a href="mailto:sales@slsitsolutions.com" class="topbar-item">
            <i class="fas fa-envelope"></i> sales@slsitsolutions.com
          </a>
          <div class="topbar-divider"></div>
          <a href="contact.php#find-us" class="topbar-item">
            <i class="fas fa-location-dot"></i> Sector-2, Ballabgarh, Faridabad
          </a>
          <div class="topbar-divider"></div>
          <span class="topbar-item">
            <i class="fas fa-clock"></i> Mon-Sat, 9 AM - 6 PM
          </span>
        </div>
        <div class="topbar-right">
          <div class="topbar-social">
            <a href="https://www.linkedin.com/company/slsitsolutions" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a href="https://x.com/slsitsolutions" target="_blank" rel="noopener" aria-label="X (Twitter)"><i class="fab fa-x-twitter"></i></a>
            <a href="https://www.facebook.com/slsitsolutions" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Navbar -->
  <nav class="navbar" aria-label="Main navigation">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        <a href="index.php" class="flex items-center logo-link" aria-label="SLS IT Solutions home">
          <img src="assets/images/logo-hd.jpeg" alt="SLS IT Solutions - Managed IT Services & Cybersecurity, Faridabad" class="h-12 logo-img" width="216" height="53" fetchpriority="high" decoding="async">
        </a>
        <div class="nav-links hidden lg:flex items-center gap-8">
          <a href="index.php" class="nav-link <?= nav_active('index') ?>">Home</a>
          <a href="about.php" class="nav-link <?= nav_active('about') ?>">About</a>
          <div class="relative dropdown">
            <a href="services.php" class="nav-link <?= in_array($current_page, ['services','security','backup','infrastructure','support']) ? 'active' : '' ?> flex items-center gap-1">Services
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </a>
            <div class="mega-menu">
              <div class="mega-menu-grid">
              <!-- Column 1: IT Infrastructure Solutions -->
              <div class="mega-col mega-col-infra">
                <div class="mega-col-header infra">
                  <span class="mega-col-icon-bg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
                  </span>
                  <div>
                    <div class="mega-col-label">IT Infrastructure Solutions</div>
                    <div class="mega-col-sub">Managed IT & Security</div>
                  </div>
                </div>
                <a href="security.php" class="mega-item">
                  <span class="mega-item-icon blue">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                  </span>
                  <div class="mega-item-text">
                    <span class="mega-item-name">Security Solutions</span>
                    <span class="mega-item-desc">Firewall, endpoint & threat protection</span>
                  </div>
                </a>
                <a href="backup.php" class="mega-item">
                  <span class="mega-item-icon green">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                  </span>
                  <div class="mega-item-text">
                    <span class="mega-item-name">Backup & Disaster Recovery</span>
                    <span class="mega-item-desc">Data protection & business continuity</span>
                  </div>
                </a>
                <a href="infrastructure.php" class="mega-item">
                  <span class="mega-item-icon purple">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
                  </span>
                  <div class="mega-item-text">
                    <span class="mega-item-name">Infrastructure Solutions</span>
                    <span class="mega-item-desc">Servers, networking & cloud setup</span>
                  </div>
                </a>
                <a href="support.php" class="mega-item">
                  <span class="mega-item-icon orange">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                  </span>
                  <div class="mega-item-text">
                    <span class="mega-item-name">IT Support & Consultancy</span>
                    <span class="mega-item-desc">24/7 helpdesk & expert guidance</span>
                  </div>
                </a>
              </div>
              <!-- Divider -->
              <div class="mega-vert-divider"></div>
              <!-- Column 2: IT Development Solutions -->
              <div class="mega-col mega-col-dev">
                <div class="mega-col-header dev">
                  <span class="mega-col-icon-bg dev">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                  </span>
                  <div>
                    <div class="mega-col-label">IT Development Solutions</div>
                    <div class="mega-col-sub">Digital & AI Products</div>
                  </div>
                </div>
                <a href="https://datasofttechnologies.com/services/web-development" target="_blank" rel="noopener" class="mega-item">
                  <span class="mega-item-icon teal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                  </span>
                  <div class="mega-item-text">
                    <span class="mega-item-name">Web Development <svg class="mega-ext-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></span>
                    <span class="mega-item-desc">Responsive sites & web apps</span>
                  </div>
                </a>
                <a href="https://datasofttechnologies.com/services/mobile-app-development" target="_blank" rel="noopener" class="mega-item">
                  <span class="mega-item-icon pink">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                  </span>
                  <div class="mega-item-text">
                    <span class="mega-item-name">Application Development <svg class="mega-ext-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></span>
                    <span class="mega-item-desc">iOS & Android mobile apps</span>
                  </div>
                </a>
                <a href="https://datasofttechnologies.com/services/software-development" target="_blank" rel="noopener" class="mega-item">
                  <span class="mega-item-icon indigo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                  </span>
                  <div class="mega-item-text">
                    <span class="mega-item-name">Software Development <svg class="mega-ext-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></span>
                    <span class="mega-item-desc">Custom enterprise software</span>
                  </div>
                </a>
                <a href="https://datasofttechnologies.com/services/ai-development" target="_blank" rel="noopener" class="mega-item">
                  <span class="mega-item-icon yellow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                  </span>
                  <div class="mega-item-text">
                    <span class="mega-item-name">AI Development <svg class="mega-ext-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></span>
                    <span class="mega-item-desc">AI/ML solutions & automation</span>
                  </div>
                </a>
              </div>
              </div><!-- /.mega-menu-grid -->
              <!-- Footer CTA strip -->
              <a href="services.php" class="mega-menu-footer">
                <span class="mega-footer-text">
                  <span class="mega-footer-eyebrow">Need help choosing?</span>
                  <span class="mega-footer-title">Explore all services & get a free consultation</span>
                </span>
                <span class="mega-footer-cta">
                  View all
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </span>
              </a>
            </div>
          </div>
          <a href="blog.php" class="nav-link <?= in_array($current_page, ['blog','blog-detail']) ? 'active' : '' ?>">Blog</a>
          <a href="contact.php" class="nav-link <?= nav_active('contact') ?>">Contact</a>
          <a href="contact.php" class="btn-primary !py-2.5 !px-5">Get a Quote</a>
        </div>
        <div class="flex items-center gap-4 lg:hidden">
          <a href="contact.php" class="btn-primary !py-2 !px-4 text-sm">Get a Quote</a>
          <button type="button" class="hamburger" aria-label="Open menu" aria-controls="mobileMenu" aria-expanded="false">
            <span></span><span></span><span></span>
          </button>
        </div>
      </div>
    </div>
  </nav>

  <!-- Mobile Menu -->
  <div class="mobile-menu" id="mobileMenu" role="dialog" aria-modal="true" aria-label="Mobile navigation">
    <button type="button" class="mobile-close absolute top-6 right-6 cursor-pointer" aria-label="Close menu" style="background:none;border:0;padding:0;">
      <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <a href="index.php">Home</a>
    <a href="about.php">About</a>
    <a href="services.php">Services</a>
    <div class="mobile-mega-group">
      <div class="mobile-mega-title">IT Infrastructure Solutions</div>
      <div class="mobile-mega-sub">
        <a href="security.php">Security Solutions</a>
        <a href="backup.php">Backup & Disaster Recovery</a>
        <a href="infrastructure.php">Infrastructure Solutions</a>
        <a href="support.php">IT Support & Consultancy</a>
      </div>
    </div>
    <div class="mobile-mega-group">
      <div class="mobile-mega-title">IT Development Solutions</div>
      <div class="mobile-mega-sub">
        <a href="https://datasofttechnologies.com/services/web-development" target="_blank" rel="noopener">Web Development ↗</a>
        <a href="https://datasofttechnologies.com/services/mobile-app-development" target="_blank" rel="noopener">Application Development ↗</a>
        <a href="https://datasofttechnologies.com/services/software-development" target="_blank" rel="noopener">Software Development ↗</a>
        <a href="https://datasofttechnologies.com/services/ai-development" target="_blank" rel="noopener">AI Development ↗</a>
      </div>
    </div>
    <a href="blog.php">Blog</a>
    <a href="contact.php">Contact</a>
  </div>

  <main id="main-content">
