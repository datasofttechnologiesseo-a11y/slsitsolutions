// SLS IT Solutions - Main JavaScript

document.addEventListener('DOMContentLoaded', function () {

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ---- Topbar / navbar / scroll-to-top: one passive, rAF-throttled scroll handler
  var navbar = document.querySelector('.navbar');
  var topbar = document.querySelector('.topbar');
  var scrollTopBtn = document.getElementById('scrollTopBtn');
  var ticking = false;

  function onScroll() {
    var y = window.scrollY || window.pageYOffset;
    if (navbar) {
      navbar.classList.toggle('scrolled', y > 50);
      if (topbar) topbar.classList.toggle('hidden', y > 50);
    }
    if (scrollTopBtn) {
      var show = y > 400;
      scrollTopBtn.style.opacity = show ? '1' : '0';
      scrollTopBtn.style.visibility = show ? 'visible' : 'hidden';
    }
    ticking = false;
  }
  window.addEventListener('scroll', function () {
    if (!ticking) { window.requestAnimationFrame(onScroll); ticking = true; }
  }, { passive: true });
  onScroll();

  // ---- Mobile menu
  var hamburger = document.querySelector('.hamburger');
  var mobileMenu = document.querySelector('.mobile-menu');
  var mobileClose = document.querySelector('.mobile-close');

  function openMenu() {
    if (!mobileMenu) return;
    mobileMenu.classList.add('active');
    document.body.style.overflow = 'hidden';
    if (hamburger) hamburger.setAttribute('aria-expanded', 'true');
    if (mobileClose) mobileClose.focus();
  }
  function closeMenu() {
    if (!mobileMenu) return;
    mobileMenu.classList.remove('active');
    document.body.style.overflow = '';
    if (hamburger) { hamburger.setAttribute('aria-expanded', 'false'); }
  }
  if (hamburger) hamburger.addEventListener('click', openMenu);
  if (mobileClose) mobileClose.addEventListener('click', function () { closeMenu(); hamburger && hamburger.focus(); });
  if (mobileMenu) {
    mobileMenu.querySelectorAll('a').forEach(function (link) { link.addEventListener('click', closeMenu); });
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && mobileMenu && mobileMenu.classList.contains('active')) { closeMenu(); hamburger && hamburger.focus(); }
  });

  // ---- Scroll-in animations
  var fadeElements = document.querySelectorAll('.fade-up');
  if (reduceMotion || !('IntersectionObserver' in window)) {
    fadeElements.forEach(function (el) { el.classList.add('visible'); });
  } else {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
    fadeElements.forEach(function (el) { observer.observe(el); });
  }

  // ---- Counter animation (rAF based)
  var counters = document.querySelectorAll('.counter');
  function animateCounter(el) {
    var target = parseInt(el.getAttribute('data-target'), 10) || 0;
    var suffix = el.getAttribute('data-suffix') || '';
    if (reduceMotion) { el.textContent = target + suffix; return; }
    var duration = 1500;
    var start = null;
    function step(ts) {
      if (!start) start = ts;
      var p = Math.min((ts - start) / duration, 1);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.floor(target * eased) + suffix;
      if (p < 1) window.requestAnimationFrame(step);
    }
    window.requestAnimationFrame(step);
  }
  if ('IntersectionObserver' in window) {
    var counterObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach(function (c) { counterObserver.observe(c); });
  } else {
    counters.forEach(animateCounter);
  }

  // ---- Smooth scroll for same-page anchors (skip bare "#")
  document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      var id = this.getAttribute('href');
      var target;
      try { target = document.querySelector(id); } catch (err) { target = null; }
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
        if (target.hasAttribute('tabindex') || /^(A|BUTTON|INPUT|TEXTAREA|SELECT)$/.test(target.tagName)) target.focus();
        history.replaceState(null, '', id);
      }
    });
  });

  // ---- External links: open safely
  document.querySelectorAll('a[target="_blank"]').forEach(function (a) {
    var rel = (a.getAttribute('rel') || '').split(/\s+/);
    if (rel.indexOf('noopener') === -1) rel.push('noopener');
    a.setAttribute('rel', rel.join(' ').trim());
  });

});
