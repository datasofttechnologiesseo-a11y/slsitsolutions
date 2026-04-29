<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();

$current = basename($_SERVER['SCRIPT_NAME']);
$user    = admin_user();

// Sidebar/topbar counters
$counts = [
    'enquiries_unread' => (int)db()->query('SELECT COUNT(*) FROM enquiries WHERE is_read=0')->fetchColumn(),
];

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Pages -> breadcrumb label
$pageMap = [
    'dashboard.php'        => 'Dashboard',
    'enquiries.php'        => 'Enquiries',
    'enquiry-view.php'     => 'Enquiry Detail',
    'testimonials.php'     => 'Testimonials',
    'testimonial-form.php' => isset($_GET['id']) ? 'Edit Testimonial' : 'Add Testimonial',
    'blogs.php'            => 'Blog Posts',
    'blog-form.php'        => isset($_GET['id']) ? 'Edit Blog Post' : 'New Blog Post',
    'categories.php'       => 'Categories',
    'category-form.php'    => isset($_GET['id']) ? 'Edit Category' : 'Add Category',
    'tags.php'             => 'Tags',
];
$breadHere = $pageMap[$current] ?? ($pageTitle ?? 'Admin');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? $breadHere) ?> — SLS IT Solutions Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/admin.css">
<link rel="icon" href="../assets/images/logo-icon.svg" type="image/svg+xml">
</head>
<body>
<div class="app">

  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <div class="brand-logo">
        <img src="../assets/images/logo.png" alt="SLS IT Solutions">
      </div>
      <div class="brand-text">
        <div class="t1">SLS IT Solutions</div>
        <div class="t2">Admin Panel</div>
      </div>
    </div>

    <div class="nav-label">Main</div>
    <nav>
      <a href="dashboard.php" class="<?= $current==='dashboard.php' ? 'active':'' ?>">
        <i class="fa-solid fa-gauge-high"></i><span>Dashboard</span>
      </a>
      <a href="enquiries.php" class="<?= in_array($current, ['enquiries.php','enquiry-view.php'], true) ? 'active':'' ?>">
        <i class="fa-solid fa-envelope"></i><span>Enquiries</span>
        <?php if ($counts['enquiries_unread'] > 0): ?>
          <span class="badge-side"><?= $counts['enquiries_unread'] ?></span>
        <?php endif; ?>
      </a>
      <a href="testimonials.php" class="<?= in_array($current, ['testimonials.php','testimonial-form.php'], true) ? 'active':'' ?>">
        <i class="fa-solid fa-star"></i><span>Testimonials</span>
      </a>
    </nav>

    <div class="nav-label">Blog</div>
    <nav>
      <a href="blogs.php" class="<?= in_array($current, ['blogs.php','blog-form.php'], true) ? 'active':'' ?>">
        <i class="fa-solid fa-newspaper"></i><span>Posts</span>
      </a>
      <a href="categories.php" class="<?= in_array($current, ['categories.php','category-form.php'], true) ? 'active':'' ?>">
        <i class="fa-solid fa-folder"></i><span>Categories</span>
      </a>
      <a href="tags.php" class="<?= $current==='tags.php' ? 'active':'' ?>">
        <i class="fa-solid fa-tags"></i><span>Tags</span>
      </a>
    </nav>

    <div class="nav-label">Site</div>
    <nav>
      <a href="../index.php" target="_blank">
        <i class="fa-solid fa-up-right-from-square"></i><span>View Public Site</span>
      </a>
    </nav>

    <div class="side-foot">
      <a href="#" onclick="confirmLogout(event)" style="display:flex;align-items:center;gap:10px;color:#fca5a5;padding:11px 14px;font-size:14px;border-radius:10px;background:rgba(220,38,38,0.10);">
        <i class="fa-solid fa-right-from-bracket"></i><span>Sign Out</span>
      </a>
    </div>
  </aside>

  <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

  <div class="main">

    <header class="topbar">
      <button class="tb-btn menu-toggle" onclick="toggleSidebar()" data-tip="Menu">
        <i class="fa-solid fa-bars"></i>
      </button>

      <div class="crumb">
        <i class="fa-solid fa-house"></i>
        <a href="dashboard.php">Admin</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="here"><?= htmlspecialchars($breadHere) ?></span>
      </div>

      <div class="spacer"></div>

      <div class="topbar-tools">
        <a href="enquiries.php" class="tb-btn" data-tip="Enquiries">
          <i class="fa-regular fa-envelope"></i>
          <?php if ($counts['enquiries_unread'] > 0): ?><span class="dot"></span><?php endif; ?>
        </a>
        <a href="../index.php" target="_blank" class="tb-btn" data-tip="View Site">
          <i class="fa-solid fa-globe"></i>
        </a>

        <div style="position:relative;" id="profileWrap">
          <div class="profile" onclick="toggleDropdown()">
            <div class="pa"><?= htmlspecialchars(strtoupper(mb_substr($user['name'],0,1))) ?></div>
            <div>
              <div class="pname"><?= htmlspecialchars($user['name']) ?></div>
              <div class="pmail"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <i class="fa-solid fa-chevron-down chev"></i>
          </div>
          <div class="dropdown" id="profileDropdown">
            <div style="padding:12px 12px 8px;">
              <div style="font-size:13px;font-weight:600;color:#0f172a;"><?= htmlspecialchars($user['name']) ?></div>
              <div style="font-size:12px;color:#64748b;"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <div class="dd-sep"></div>
            <a href="dashboard.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
            <a href="../index.php" target="_blank"><i class="fa-solid fa-up-right-from-square"></i> View Site</a>
            <div class="dd-sep"></div>
            <button type="button" class="dd-danger" onclick="confirmLogout(event)">
              <i class="fa-solid fa-right-from-bracket"></i> Sign Out
            </button>
          </div>
        </div>
      </div>
    </header>

    <div class="content">
      <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
          <i class="fa-solid <?= $flash['type']==='success' ? 'fa-circle-check' : ($flash['type']==='error' ? 'fa-circle-exclamation' : 'fa-circle-info') ?>"></i>
          <span><?= htmlspecialchars($flash['msg']) ?></span>
        </div>
      <?php endif; ?>
