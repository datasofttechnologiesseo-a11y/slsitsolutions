<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$page_title = $page_title ?? 'SLS IT Solutions';
$page_description = $page_description ?? '';
$page_keywords = $page_keywords ?? '';
$canonical = $canonical ?? '';
$og_title = $og_title ?? $page_title;
$og_description = $og_description ?? $page_description;
$og_url = $og_url ?? $canonical;
$og_image = $og_image ?? 'https://www.slsitsolutions.com/assets/images/logo.png';
$twitter_title = $twitter_title ?? $page_title;
$twitter_description = $twitter_description ?? $page_description;
$extra_head = $extra_head ?? '';

function nav_active($page) {
  global $current_page;
  return $current_page === $page ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?></title>
  <?php if ($page_description): ?><meta name="description" content="<?= htmlspecialchars($page_description) ?>"><?php endif; ?>
  <?php if ($page_keywords): ?><meta name="keywords" content="<?= htmlspecialchars($page_keywords) ?>"><?php endif; ?>

  <!-- Open Graph -->
  <meta property="og:title" content="<?= htmlspecialchars($og_title) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($og_description) ?>">
  <meta property="og:type" content="website">
  <?php if ($og_url): ?><meta property="og:url" content="<?= htmlspecialchars($og_url) ?>"><?php endif; ?>
  <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
  <meta property="og:site_name" content="SLS IT Solutions">
  <meta property="og:locale" content="en_IN">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($twitter_title) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($twitter_description) ?>">

  <?php if ($canonical): ?><link rel="canonical" href="<?= htmlspecialchars($canonical) ?>"><?php endif; ?>

  <link rel="icon" type="image/jpeg" href="assets/images/logo-hd.jpeg">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link rel="stylesheet" href="assets/css/tailwind.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">

  <?= $extra_head ?>
</head>
<body>

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
          <span class="topbar-item">
            <i class="fas fa-location-dot"></i> Sector-2, Ballabgarh, Faridabad
          </span>
          <div class="topbar-divider"></div>
          <span class="topbar-item">
            <i class="fas fa-clock"></i> Mon-Sat, 9 AM - 6 PM
          </span>
        </div>
        <div class="topbar-right">
          <div class="topbar-social">
            <a href="https://www.linkedin.com/company/slsitsolutions" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a href="https://x.com/slsitsolutions" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
            <a href="https://www.facebook.com/slsitsolutions" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Navbar -->
  <nav class="navbar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        <a href="index.php" class="flex items-center">
          <img src="assets/images/logo-hd.jpeg" alt="SLS IT Solutions" class="h-12 logo-img">
        </a>
        <div class="nav-links hidden lg:flex items-center gap-8">
          <a href="index.php" class="nav-link <?= nav_active('index') ?>">Home</a>
          <a href="about.php" class="nav-link <?= nav_active('about') ?>">About</a>
          <div class="relative dropdown">
            <a href="services.php" class="nav-link <?= in_array($current_page, ['services','security','backup','infrastructure','support']) ? 'active' : '' ?> flex items-center gap-1">Services
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </a>
            <div class="dropdown-menu">
              <a href="security.php" class="dropdown-item">Security Solutions</a>
              <a href="backup.php" class="dropdown-item">Backup & Disaster Recovery</a>
              <a href="infrastructure.php" class="dropdown-item">Infrastructure Solutions</a>
              <a href="support.php" class="dropdown-item">IT Support & Consultancy</a>
            </div>
          </div>
          <a href="contact.php" class="nav-link <?= nav_active('contact') ?>">Contact</a>
          <a href="contact.php" class="btn-primary !py-2.5 !px-5">Get a Quote</a>
        </div>
        <div class="flex items-center gap-4 lg:hidden">
          <a href="contact.php" class="btn-primary !py-2 !px-4 text-sm">Get a Quote</a>
          <div class="hamburger" onclick="document.querySelector('.mobile-menu').classList.add('active');document.body.style.overflow='hidden';">
            <span></span><span></span><span></span>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- Mobile Menu -->
  <div class="mobile-menu">
    <div class="mobile-close absolute top-6 right-6 cursor-pointer" onclick="document.querySelector('.mobile-menu').classList.remove('active');document.body.style.overflow='';">
      <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </div>
    <a href="index.php">Home</a>
    <a href="about.php">About</a>
    <a href="services.php">Services</a>
    <a href="security.php" class="pl-4 text-base opacity-75">Security Solutions</a>
    <a href="backup.php" class="pl-4 text-base opacity-75">Backup & DR</a>
    <a href="infrastructure.php" class="pl-4 text-base opacity-75">Infrastructure</a>
    <a href="support.php" class="pl-4 text-base opacity-75">IT Support</a>
    <a href="contact.php">Contact</a>
  </div>
