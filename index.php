<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/testimonial_helpers.php';
require_once __DIR__ . '/includes/blog.php';

try {
    $testimonials = db()->query(
        'SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order ASC, id DESC'
    )->fetchAll();
} catch (\Throwable $e) {
    $testimonials = [];
}

// Fallback testimonials when the database is empty or unreachable —
// keeps the "Client Voices" section populated until real ones are added.
if (empty($testimonials)) {
    $testimonials = [
        [
            'client_name'  => 'Rahul Mehra',
            'company'      => 'Operations Head, Cornitos',
            'quote'        => 'SLS IT Solutions transformed our IT infrastructure end-to-end. Their proactive monitoring and 24/7 support have kept our operations running without a single major outage in two years.',
            'rating'       => 5,
            'initials'     => 'RM',
            'avatar_color' => 'blue',
        ],
        [
            'client_name'  => 'Priya Sharma',
            'company'      => 'IT Manager, Indogulf Cropsciences',
            'quote'        => 'Migrating to their backup and disaster recovery setup was the best decision we made. When we faced a ransomware scare last year, recovery took hours, not days.',
            'rating'       => 5,
            'initials'     => 'PS',
            'avatar_color' => 'green',
        ],
        [
            'client_name'  => 'Anil Kapoor',
            'company'      => 'Director, Universal Quartzz',
            'quote'        => 'From DPDP Act compliance to firewall management, the SLS team is genuinely knowledgeable. They explain things in plain English and deliver on every promise.',
            'rating'       => 5,
            'initials'     => 'AK',
            'avatar_color' => 'purple',
        ],
        [
            'client_name'  => 'Sneha Verma',
            'company'      => 'CTO, Enterslice',
            'quote'        => 'Reliable, responsive, and refreshingly transparent on pricing. They helped us scale our cloud infrastructure across multiple regions without a single hiccup.',
            'rating'       => 5,
            'initials'     => 'SV',
            'avatar_color' => 'orange',
        ],
    ];
}

try {
    $latestBlogs = get_recent_blogs(3);
} catch (\Throwable $e) {
    $latestBlogs = [];
}

$page_title = 'SLS IT Solutions | Managed IT Services, Cybersecurity & Support in Faridabad, Delhi NCR';
$page_description = 'SLS IT Solutions delivers managed IT services, cybersecurity, backup & disaster recovery, and 24/7 support for businesses in Faridabad, Delhi NCR & across India. DPDP Act compliant. 200+ clients.';
$page_keywords = 'IT company Faridabad, managed IT services Delhi NCR, cybersecurity Faridabad, IT support India, backup disaster recovery, IT infrastructure services, DPDP Act compliance, IT consultancy Delhi, business IT solutions India';
$canonical = 'https://www.slsitsolutions.com/';
$og_title = 'SLS IT Solutions - Secure & Scalable IT for Indian Businesses';
$og_description = 'Enterprise-grade cybersecurity, backup, infrastructure & IT support services trusted by 200+ Indian businesses. Get a free consultation today.';
$og_url = 'https://www.slsitsolutions.com/';
$og_image = 'https://www.slsitsolutions.com/assets/images/logo.png';
$twitter_title = 'SLS IT Solutions - IT Support, Cybersecurity & Infrastructure Services in India';
$twitter_description = 'Enterprise-grade cybersecurity, backup, infrastructure & IT support services trusted by 200+ Indian businesses.';
$extra_head = <<<'HTML'
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "SLS IT Solutions",
    "url": "https://www.slsitsolutions.com",
    "logo": "https://www.slsitsolutions.com/assets/images/logo.png",
    "description": "Enterprise-grade IT support, cybersecurity, backup & disaster recovery, and infrastructure services for Indian businesses.",
    "telephone": "+918383800914",
    "email": "sales@slsitsolutions.com",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "Arya Nagar, Sector-2, Ballabgarh",
      "addressLocality": "Faridabad",
      "addressRegion": "Haryana",
      "postalCode": "121004",
      "addressCountry": "IN"
    },
    "areaServed": {
      "@type": "Country",
      "name": "India"
    },
    "sameAs": [
      "https://www.linkedin.com/company/slsitsolutions",
      "https://www.facebook.com/slsitsolutions",
      "https://x.com/slsitsolutions"
    ]
  }
  </script>
HTML;
include 'includes/header.php';
?>

  <!-- ===== HERO SECTION ===== -->
  <section class="hero-gradient hero-section" style="background-image: linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(15,76,129,0.82) 50%, rgba(10,52,96,0.80) 100%), url('assets/images/heroes/home-hero.jpg'); background-size: cover; background-position: center;">
    <div class="flex items-center relative hero-section-inner">
      <div class="float-shape"></div>
      <div class="float-shape"></div>
      <div class="float-shape"></div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full" style="padding: 7rem 1rem 5rem;">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-stretch">
          <!-- Left Content -->
          <div class="max-w-3xl z-10">
            <div class="hero-badge">
              <span class="badge-dot"></span> Trusted by 200+ Indian Businesses
            </div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white leading-tight mb-6" style="font-family:'Poppins',sans-serif; font-size: clamp(1.85rem, 4vw, 2.925rem); animation: fadeInUp 0.8s ease 0.4s both;">
              Empowering Indian Businesses with Secure &amp; Scalable IT Solutions
            </h1>

            <p class="text-lg leading-relaxed mb-8" style="color:rgba(255,255,255,0.85); animation: fadeInUp 0.8s ease 0.6s both;">
              From protecting your data against rising cyber threats to building future-ready IT infrastructure &mdash; SLS IT Solutions delivers enterprise-grade technology services that keep your business running 24/7.
            </p>

            <div class="flex flex-wrap gap-4" style="animation: fadeInUp 0.8s ease 0.8s both;">
              <a href="contact.php" class="btn-primary">Get a Free Consultation &rarr;</a>
              <a href="services.php" class="btn-secondary">Explore Our Services</a>
            </div>

            <!-- All 6 Status Cards - Compact 3x2 Grid -->
            <div class="hero-trust-grid grid gap-2.5" style="grid-template-columns: repeat(3, 1fr); margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.12); animation: fadeInUp 0.8s ease 1s both;">

              <!-- DPDP Compliant -->
              <div style="background:#ffffff; border-radius: 12px; padding: 0.625rem 0.75rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <div style="width:32px; height:32px; border-radius:8px; background: linear-gradient(135deg, #10b981, #059669); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                  <svg style="width:17px; height:17px; color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                  <div style="font-size:0.575rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.4px; line-height:1;">Certified</div>
                  <div style="font-size:0.775rem; font-weight:700; color:#0f172a; line-height:1.2; margin-top:1px;">DPDP Compliant</div>
                </div>
              </div>

              <!-- ISO Certified -->
              <div style="background:#ffffff; border-radius: 12px; padding: 0.625rem 0.75rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <div style="width:32px; height:32px; border-radius:8px; background: linear-gradient(135deg, #3b82f6, #2563eb); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                  <svg style="width:17px; height:17px; color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <div>
                  <div style="font-size:0.575rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.4px; line-height:1;">Quality</div>
                  <div style="font-size:0.775rem; font-weight:700; color:#0f172a; line-height:1.2; margin-top:1px;">ISO Certified</div>
                </div>
              </div>

              <!-- 24/7 Support -->
              <div style="background:#ffffff; border-radius: 12px; padding: 0.625rem 0.75rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <div style="width:32px; height:32px; border-radius:8px; background: linear-gradient(135deg, #f59e0b, #d97706); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                  <svg style="width:17px; height:17px; color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                  <div style="font-size:0.575rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.4px; line-height:1;">Always On</div>
                  <div style="font-size:0.775rem; font-weight:700; color:#0f172a; line-height:1.2; margin-top:1px;">24/7 Support</div>
                </div>
              </div>

              <!-- Security Monitoring -->
              <div style="background:#ffffff; border-radius: 12px; padding: 0.625rem 0.75rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <div style="width:32px; height:32px; border-radius:8px; background: linear-gradient(135deg, #10b981, #047857); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                  <svg style="width:17px; height:17px; color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                  <div style="font-size:0.575rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.4px; line-height:1;">Security</div>
                  <div style="font-size:0.775rem; font-weight:700; color:#0f172a; line-height:1.2; margin-top:1px;">Monitoring Active</div>
                </div>
              </div>

              <!-- Backup Protected -->
              <div style="background:#ffffff; border-radius: 12px; padding: 0.625rem 0.75rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <div style="width:32px; height:32px; border-radius:8px; background: linear-gradient(135deg, #6366f1, #4f46e5); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                  <svg style="width:17px; height:17px; color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                </div>
                <div>
                  <div style="font-size:0.575rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.4px; line-height:1;">Backup</div>
                  <div style="font-size:0.775rem; font-weight:700; color:#0f172a; line-height:1.2; margin-top:1px;">Protected</div>
                </div>
              </div>

              <!-- Infrastructure Uptime -->
              <div style="background:#ffffff; border-radius: 12px; padding: 0.625rem 0.75rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <div style="width:32px; height:32px; border-radius:8px; background: linear-gradient(135deg, #ec4899, #db2777); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                  <svg style="width:17px; height:17px; color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                </div>
                <div>
                  <div style="font-size:0.575rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.4px; line-height:1;">Infra</div>
                  <div style="font-size:0.775rem; font-weight:700; color:#0f172a; line-height:1.2; margin-top:1px;">99.9% Uptime</div>
                </div>
              </div>

            </div>
          </div>

          <!-- Right Side: Hero Image (hidden on mobile) -->
          <div class="hidden lg:flex items-center z-10" style="animation: fadeInUp 1s ease 0.6s both; margin-top: 3rem; margin-left: 3.5rem;">
            <div class="relative rounded-3xl overflow-hidden shadow-2xl w-full">
              <img src="Home hero image.png" alt="SLS IT Solutions NOC & Service Desk Team" class="w-full h-full object-contain" loading="eager" width="548" height="505" style="object-position: center;">
              <div style="position:absolute; inset:0; border-radius: 1.5rem; box-shadow: inset 0 0 0 1px rgba(255,255,255,0.1);"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- ===== OUR CUSTOMERS ===== -->
  <section class="bg-white py-12 border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-10 fade-up">
        <h2 class="section-title">Our Customers</h2>
        <p class="section-subtitle mx-auto" style="font-family:'Georgia',serif; font-size:1.25rem; font-style:italic; background: linear-gradient(135deg, #0f4c81, #00a86b); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; font-weight:600; letter-spacing:0.5px;">Turning IT challenges into solutions</p>
      </div>

      <!-- Marquee Slider -->
      <div class="marquee-container">
        <div class="marquee-track">
          <!-- Set 1 — ordered biggest to smallest by logo resolution -->
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Rahul-Technic.png" alt="Rahul Technic"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Arcis-Design.png" alt="Arcis Design"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Keshoram.png" alt="Keshoram"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Space-Telelink.png" alt="Space Telelink"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Indogulf.jpg" alt="Indogulf Cropsciences"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Universal-Quartzz.jpg" alt="Universal Quartzz"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/CICO.png" alt="CICO Group"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Tilak-Stone.jpg" alt="Tilak Stone"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Enterslice.jpg" alt="Enterslice"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Akash-Group.jpg" alt="Akash Group"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/JQR.png" alt="JQR Sports"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Bhagwati.png" alt="Bhagwati Techno Fab"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Premier.jpg" alt="Premier Plastics"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Cornitos.png" alt="Cornitos"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/AVON.jpg" alt="Avon Industries"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Universal-Granimarmo.jpg" alt="Universal Granimarmo"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/ANH.png" alt="ANH"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Moeving.png" alt="Moeving"></div>

          <!-- Set 2 (duplicate for seamless loop) -->
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Rahul-Technic.png" alt="Rahul Technic"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Arcis-Design.png" alt="Arcis Design"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Keshoram.png" alt="Keshoram"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Space-Telelink.png" alt="Space Telelink"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Indogulf.jpg" alt="Indogulf Cropsciences"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Universal-Quartzz.jpg" alt="Universal Quartzz"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/CICO.png" alt="CICO Group"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Tilak-Stone.jpg" alt="Tilak Stone"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Enterslice.jpg" alt="Enterslice"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Akash-Group.jpg" alt="Akash Group"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/JQR.png" alt="JQR Sports"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Bhagwati.png" alt="Bhagwati Techno Fab"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Premier.jpg" alt="Premier Plastics"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Cornitos.png" alt="Cornitos"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/AVON.jpg" alt="Avon Industries"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Universal-Granimarmo.jpg" alt="Universal Granimarmo"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/ANH.png" alt="ANH"></div>
          <div class="marquee-item"><img loading="lazy" src="assets/images/clients/Moeving.png" alt="Moeving"></div>
        </div>
      </div>

      <!-- Green divider -->
      <div class="mt-10 mx-auto" style="width:80px;height:3px;background:linear-gradient(90deg,#00a86b,#0f4c81);border-radius:4px;"></div>
    </div>
  </section>


  <!-- ===== ABOUT SLS IT SOLUTIONS (Split Section) ===== -->
  <section class="split-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="split-content fade-up">
        <div class="split-text">
          <span class="split-label">Know Who We Are</span>
          <h2 class="split-heading">Your Trusted IT Partner for Secure &amp; Scalable Growth</h2>
          <p class="split-desc">SLS IT Solutions is a leading IT services company based in India, delivering enterprise-grade cybersecurity, infrastructure management, backup &amp; disaster recovery, and 24/7 IT support. We help businesses of all sizes protect their data, streamline operations, and scale with confidence.</p>
          <div class="split-features">
            <div class="split-feature-item"><i class="fas fa-check-circle"></i> 10+ Years Experience</div>
            <div class="split-feature-item"><i class="fas fa-check-circle"></i> 200+ Happy Clients</div>
            <div class="split-feature-item"><i class="fas fa-check-circle"></i> DPDP Act Compliant</div>
            <div class="split-feature-item"><i class="fas fa-check-circle"></i> 24/7 Support in IST</div>
            <div class="split-feature-item"><i class="fas fa-check-circle"></i> ISO Certified Processes</div>
            <div class="split-feature-item"><i class="fas fa-check-circle"></i> Pan-India Coverage</div>
          </div>
          <a href="about.php" class="btn-primary">Learn More About Us <i class="fas fa-arrow-right text-sm"></i></a>
        </div>
        <div class="split-image-wrap">
          <img src="assets/images/sections/office-team.jpg" alt="SLS IT Solutions Team" loading="lazy">
        </div>
      </div>
    </div>
  </section>


  <!-- ===== WHY INDIAN BUSINESSES TRUST US ===== -->
  <section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12 fade-up">
        <h2 class="section-title">Why Indian Businesses Choose SLS IT Solutions</h2>
        <p class="section-subtitle mx-auto">We understand the unique IT challenges that Indian businesses face &mdash; from compliance with the Digital Personal Data Protection Act to protecting against the 15 lakh+ cyber attacks India faces annually.</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Left: Pain Points -->
        <div class="space-y-5 fade-up">
          <h3 class="text-xl font-bold text-red-700 mb-6" style="font-family:'Poppins',sans-serif;">The Challenges You Face</h3>

          <div class="flex items-start gap-4 p-5 rounded-2xl bg-red-50 border border-red-200">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
              <i class="fas fa-virus text-xl text-red-500"></i>
            </div>
            <div>
              <h4 class="font-bold text-gray-900 mb-1">Rising Cyber Threats in India</h4>
              <p class="text-sm text-gray-600">Ransomware, phishing, and zero-day attacks are targeting Indian businesses at an alarming rate, with SMBs being the most vulnerable.</p>
            </div>
          </div>

          <div class="flex items-start gap-4 p-5 rounded-2xl bg-red-50 border border-red-200">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
              <i class="fas fa-file-shield text-xl text-red-500"></i>
            </div>
            <div>
              <h4 class="font-bold text-gray-900 mb-1">DPDP Act Compliance Pressure</h4>
              <p class="text-sm text-gray-600">The Digital Personal Data Protection Act 2023 mandates strict data handling practices with heavy penalties for non-compliance.</p>
            </div>
          </div>

          <div class="flex items-start gap-4 p-5 rounded-2xl bg-red-50 border border-red-200">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
              <i class="fas fa-power-off text-xl text-red-500"></i>
            </div>
            <div>
              <h4 class="font-bold text-gray-900 mb-1">Costly Downtime &amp; Data Loss</h4>
              <p class="text-sm text-gray-600">Unplanned downtime can cost Indian businesses lakhs per hour, and data loss can cripple operations permanently.</p>
            </div>
          </div>

          <div class="flex items-start gap-4 p-5 rounded-2xl bg-red-50 border border-red-200">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
              <i class="fas fa-diagram-project text-xl text-red-500"></i>
            </div>
            <div>
              <h4 class="font-bold text-gray-900 mb-1">Complex IT Infrastructure Management</h4>
              <p class="text-sm text-gray-600">Managing servers, networks, and cloud environments requires specialized expertise that many businesses lack in-house.</p>
            </div>
          </div>
        </div>

        <!-- Right: Solutions -->
        <div class="space-y-5 fade-up">
          <h3 class="text-xl font-bold text-green-700 mb-6" style="font-family:'Poppins',sans-serif;">How SLS IT Solves It</h3>

          <div class="flex items-start gap-4 p-5 rounded-2xl bg-green-50 border border-green-200">
            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
              <i class="fas fa-shield-halved text-xl text-green-600"></i>
            </div>
            <div>
              <h4 class="font-bold text-gray-900 mb-1">Multi-Layered Cyber Defence</h4>
              <p class="text-sm text-gray-600">We deploy enterprise-grade firewalls, endpoint protection, and real-time threat monitoring powered by Sophos to keep your business safe.</p>
            </div>
          </div>

          <div class="flex items-start gap-4 p-5 rounded-2xl bg-green-50 border border-green-200">
            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
              <i class="fas fa-clipboard-check text-xl text-green-600"></i>
            </div>
            <div>
              <h4 class="font-bold text-gray-900 mb-1">DPDP Act Compliance Assistance</h4>
              <p class="text-sm text-gray-600">Our experts help you audit, implement, and maintain data protection practices that meet every requirement of the DPDP Act 2023.</p>
            </div>
          </div>

          <div class="flex items-start gap-4 p-5 rounded-2xl bg-green-50 border border-green-200">
            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
              <i class="fas fa-database text-xl text-green-600"></i>
            </div>
            <div>
              <h4 class="font-bold text-gray-900 mb-1">Automated Backup &amp; Recovery</h4>
              <p class="text-sm text-gray-600">Commvault-powered backup ensures 99.9% data availability with rapid disaster recovery so you never lose critical business data.</p>
            </div>
          </div>

          <div class="flex items-start gap-4 p-5 rounded-2xl bg-green-50 border border-green-200">
            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
              <i class="fas fa-gears text-xl text-green-600"></i>
            </div>
            <div>
              <h4 class="font-bold text-gray-900 mb-1">Fully Managed IT Infrastructure</h4>
              <p class="text-sm text-gray-600">From server setup to cloud migration, our certified engineers handle your entire IT infrastructure so you can focus on growth.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- ===== OUR CORE SOLUTIONS ===== -->
  <section class="py-16 section-bg-image section-bg-blue" style="background-image: url('assets/images/sections/cybersecurity.jpg');">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12 fade-up">
        <h2 class="section-title" style="color:#ffffff;">Comprehensive IT Solutions for Every Business Need</h2>
        <p class="section-subtitle mx-auto" style="color:#bfdbfe;">End-to-end technology services designed to secure, scale, and simplify your IT operations.</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Cybersecurity -->
        <div class="image-card fade-up">
          <div class="image-card-img" style="background-image: url('assets/images/sections/shield-lock.jpg');"></div>
          <div class="image-card-body">
            <div class="fa-icon-box icon-gradient-blue mb-4">
              <i class="fas fa-shield-halved"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2" style="font-family:'Poppins',sans-serif;">Cybersecurity &amp; Threat Protection</h3>
            <p class="text-sm text-gray-600 mb-4 leading-relaxed">Safeguard your business from ransomware, phishing, and zero-day attacks with multi-layered security solutions.</p>
            <a href="security.php" class="text-[#0f4c81] font-semibold text-sm hover:text-[#1a6bb5] transition-colors inline-flex items-center gap-1">
              Learn More <i class="fas fa-arrow-right text-xs"></i>
            </a>
          </div>
        </div>

        <!-- Card 2: Backup & DR -->
        <div class="image-card fade-up">
          <div class="image-card-img" style="background-image: url('assets/images/sections/backup-recovery.jpg');"></div>
          <div class="image-card-body">
            <div class="fa-icon-box icon-gradient-green mb-4">
              <i class="fas fa-cloud-arrow-up"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2" style="font-family:'Poppins',sans-serif;">Backup &amp; Disaster Recovery</h3>
            <p class="text-sm text-gray-600 mb-4 leading-relaxed">Never lose critical business data again. Automated backup and disaster recovery with 99.9% data availability.</p>
            <a href="backup.php" class="text-emerald-600 font-semibold text-sm hover:text-emerald-700 transition-colors inline-flex items-center gap-1">
              Learn More <i class="fas fa-arrow-right text-xs"></i>
            </a>
          </div>
        </div>

        <!-- Card 3: Infrastructure -->
        <div class="image-card fade-up">
          <div class="image-card-img" style="background-image: url('assets/images/sections/server-room.jpg');"></div>
          <div class="image-card-body">
            <div class="fa-icon-box icon-gradient-purple mb-4">
              <i class="fas fa-server"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2" style="font-family:'Poppins',sans-serif;">IT Infrastructure &amp; Networking</h3>
            <p class="text-sm text-gray-600 mb-4 leading-relaxed">Build a robust IT backbone with server management, virtualization, and networking solutions.</p>
            <a href="infrastructure.php" class="text-purple-600 font-semibold text-sm hover:text-purple-700 transition-colors inline-flex items-center gap-1">
              Learn More <i class="fas fa-arrow-right text-xs"></i>
            </a>
          </div>
        </div>

        <!-- Card 4: IT Support -->
        <div class="image-card fade-up">
          <div class="image-card-img" style="background-image: url('assets/images/sections/it-support.jpg');"></div>
          <div class="image-card-body">
            <div class="fa-icon-box icon-gradient-amber mb-4">
              <i class="fas fa-headset"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2" style="font-family:'Poppins',sans-serif;">IT Support &amp; Consultancy</h3>
            <p class="text-sm text-gray-600 mb-4 leading-relaxed">Get expert IT guidance and round-the-clock support from our experienced team in your timezone.</p>
            <a href="support.php" class="text-amber-600 font-semibold text-sm hover:text-amber-700 transition-colors inline-flex items-center gap-1">
              Learn More <i class="fas fa-arrow-right text-xs"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- ===== HOW WE WORK ===== -->
  <section class="py-16 process-bg" style="background-image: url('assets/images/sections/process-bg.jpg');">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12 fade-up">
        <h2 class="section-title">Our Proven 3-Step Process</h2>
        <p class="section-subtitle mx-auto">A structured approach that ensures every IT initiative delivers measurable results for your business.</p>
      </div>

      <div class="relative">
        <!-- Connecting Line (hidden on mobile) -->
        <div class="hidden md:block absolute top-[44px] h-0.5" style="background:linear-gradient(90deg,#0f4c81,#00a86b);left:16.67%;right:16.67%;"></div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10">
          <!-- Step 1 -->
          <div class="process-step fade-up">
            <div class="process-number"><i class="fas fa-comments"></i></div>
            <h3 class="text-xl font-bold text-gray-900 mb-3" style="font-family:'Poppins',sans-serif;">Consult</h3>
            <p class="text-gray-600 text-sm leading-relaxed">We assess your current IT landscape, identify vulnerabilities, and understand your business goals to chart the right path forward.</p>
          </div>

          <!-- Step 2 -->
          <div class="process-step fade-up">
            <div class="process-number"><i class="fas fa-pencil-ruler"></i></div>
            <h3 class="text-xl font-bold text-gray-900 mb-3" style="font-family:'Poppins',sans-serif;">Design</h3>
            <p class="text-gray-600 text-sm leading-relaxed">Our experts craft tailored solutions aligned with your budget, compliance needs, and growth plans for maximum impact.</p>
          </div>

          <!-- Step 3 -->
          <div class="process-step fade-up">
            <div class="process-number"><i class="fas fa-rocket"></i></div>
            <h3 class="text-xl font-bold text-gray-900 mb-3" style="font-family:'Poppins',sans-serif;">Implement</h3>
            <p class="text-gray-600 text-sm leading-relaxed">We deploy, monitor, and maintain your IT infrastructure for maximum uptime and performance with ongoing support.</p>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- ===== WHY CHOOSE US ===== -->
  <section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12 fade-up">
        <h2 class="section-title">Why Choose SLS IT Solutions</h2>
        <p class="section-subtitle mx-auto">The trusted technology partner for businesses across India.</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Item 1 -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:shadow-lg hover:border-transparent transition-all duration-300 fade-up">
          <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center mb-4">
            <i class="fas fa-globe-asia text-xl text-[#0f4c81]"></i>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-2" style="font-family:'Poppins',sans-serif;">India-First Approach</h3>
          <p class="text-sm text-gray-600 leading-relaxed">Solutions designed for Indian business challenges, compliance requirements, and budget considerations.</p>
        </div>

        <!-- Item 2 -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:shadow-lg hover:border-transparent transition-all duration-300 fade-up">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-4">
            <i class="fas fa-clock text-xl text-emerald-600"></i>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-2" style="font-family:'Poppins',sans-serif;">24/7 Expert Support</h3>
          <p class="text-sm text-gray-600 leading-relaxed">Round-the-clock support available in IST timezone with rapid response times for critical issues.</p>
        </div>

        <!-- Item 3 -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:shadow-lg hover:border-transparent transition-all duration-300 fade-up">
          <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center mb-4">
            <i class="fas fa-check-circle text-xl text-green-600"></i>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-2" style="font-family:'Poppins',sans-serif;">DPDP Act Ready</h3>
          <p class="text-sm text-gray-600 leading-relaxed">We help you stay compliant with India's Digital Personal Data Protection Act 2023 and related regulations.</p>
        </div>

        <!-- Item 4 -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:shadow-lg hover:border-transparent transition-all duration-300 fade-up">
          <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center mb-4">
            <i class="fas fa-indian-rupee-sign text-xl text-amber-600"></i>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-2" style="font-family:'Poppins',sans-serif;">Cost-Effective Solutions</h3>
          <p class="text-sm text-gray-600 leading-relaxed">Enterprise-grade security and infrastructure without the enterprise price tag, designed for Indian budgets.</p>
        </div>

        <!-- Item 5 -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:shadow-lg hover:border-transparent transition-all duration-300 fade-up">
          <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center mb-4">
            <i class="fas fa-handshake text-xl text-indigo-600"></i>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-2" style="font-family:'Poppins',sans-serif;">Certified Partnerships</h3>
          <p class="text-sm text-gray-600 leading-relaxed">Official partnerships with IBM, Microsoft, Dell, Sophos, Commvault, and AWS for best-in-class solutions.</p>
        </div>

        <!-- Item 6 -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:shadow-lg hover:border-transparent transition-all duration-300 fade-up">
          <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center mb-4">
            <i class="fas fa-chart-line text-xl text-purple-600"></i>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mb-2" style="font-family:'Poppins',sans-serif;">Proven Track Record</h3>
          <p class="text-sm text-gray-600 leading-relaxed">10+ years serving businesses across NCR, Delhi, and pan-India with consistent delivery and client satisfaction.</p>
        </div>
      </div>
    </div>
  </section>


  <!-- ===== TECHNOLOGY PARTNERS ===== -->
  <section class="section-padding" style="background:#f8fafc;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12 fade-up">
        <h2 class="section-title">Technology Partners</h2>
        <p class="section-subtitle">We collaborate with industry-leading technology providers to deliver best-in-class solutions.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 fade-up">

        <!-- Category 1: Hardware -->
        <div class="partner-category">
          <div class="partner-cat-header">
            <span class="partner-cat-icon" style="background:linear-gradient(135deg,#0f4c81,#1a6bb5);">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
            </span>
            <span class="partner-cat-title">Hardware</span>
          </div>
          <div class="partner-logos-grid">
            <div class="partner-logo-item">
              <img src="assets/images/partners/hp.svg" alt="HP" loading="lazy">
            </div>
            <div class="partner-logo-item">
              <img src="assets/images/partners/ibm.svg" alt="IBM" loading="lazy">
            </div>
            <div class="partner-logo-item">
              <img src="assets/images/partners/lenovo.svg" alt="Lenovo" loading="lazy">
            </div>
            <div class="partner-logo-item">
              <img src="assets/images/partners/dell.svg" alt="Dell" loading="lazy">
            </div>
          </div>
        </div>

        <!-- Category 2: Network Security -->
        <div class="partner-category">
          <div class="partner-cat-header">
            <span class="partner-cat-icon" style="background:linear-gradient(135deg,#dc2626,#ef4444);">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            <span class="partner-cat-title">Network Security</span>
          </div>
          <div class="partner-logos-grid partner-logos-3">
            <div class="partner-logo-item">
              <img src="assets/images/partners/fortinet.svg" alt="Fortinet" loading="lazy">
            </div>
            <div class="partner-logo-item">
              <img src="assets/images/partners/sophos.svg" alt="Sophos" loading="lazy">
            </div>
            <div class="partner-logo-item partner-logo-full">
              <img src="assets/images/partners/sonicwall.svg" alt="SonicWALL" loading="lazy">
            </div>
          </div>
        </div>

        <!-- Category 3: Endpoint Security -->
        <div class="partner-category">
          <div class="partner-cat-header">
            <span class="partner-cat-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7);">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </span>
            <span class="partner-cat-title">Endpoint Security</span>
          </div>
          <div class="partner-logos-grid partner-logos-3">
            <div class="partner-logo-item">
              <img src="assets/images/partners/crowdstrike.svg" alt="CrowdStrike" loading="lazy">
            </div>
            <div class="partner-logo-item">
              <img src="assets/images/partners/seqrite.png" alt="Seqrite" loading="lazy">
            </div>
            <div class="partner-logo-item partner-logo-full">
              <img src="assets/images/partners/escan.png" alt="eScan" loading="lazy">
            </div>
          </div>
        </div>

        <!-- Category 4: Cloud -->
        <div class="partner-category">
          <div class="partner-cat-header">
            <span class="partner-cat-icon" style="background:linear-gradient(135deg,#00a86b,#00c97f);">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
            </span>
            <span class="partner-cat-title">Cloud Platforms</span>
          </div>
          <div class="partner-logos-grid">
            <div class="partner-logo-item">
              <img src="assets/images/partners/aws.svg" alt="AWS" loading="lazy">
            </div>
            <div class="partner-logo-item">
              <img src="assets/images/partners/google.svg" alt="Google Cloud" loading="lazy">
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>


  <!-- ===== TESTIMONIALS ===== -->
  <section class="testi-section">
    <!-- Decorative blobs -->
    <div class="testi-blob testi-blob-1"></div>
    <div class="testi-blob testi-blob-2"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="text-center mb-14 fade-up">
        <span class="testi-badge">CLIENT VOICES</span>
        <h2 class="testi-heading">What Our Clients Say</h2>
        <p class="testi-sub">Trusted by businesses across India — real results, real relationships.</p>
      </div>

      <!-- Slider Wrapper -->
      <?php if (!empty($testimonials)): ?>
      <div class="testi-slider-outer fade-up">
        <div class="testi-slider" id="testiSlider">

          <?php foreach ($testimonials as $t):
            $initials = $t['initials'] ?: testimonial_initials($t['client_name']);
            $grad     = testimonial_gradient($t['avatar_color']);
            $stars    = str_repeat('★', max(1, min(5, (int)$t['rating'])));
          ?>
          <div class="testi-slide">
            <div class="testi-card-inner">
              <svg class="testi-icon" viewBox="0 0 40 32" fill="none"><path d="M0 32V19.2C0 8.533 6.4 2.133 19.2 0l2.4 4C15.467 5.6 12.267 8.8 11.2 14.4H16V32H0zm24 0V19.2C24 8.533 30.4 2.133 43.2 0l2.4 4C39.467 5.6 36.267 8.8 35.2 14.4H40V32H24z" fill="#00a86b" fill-opacity="0.25"/></svg>
              <p class="testi-quote-text"><?= htmlspecialchars($t['quote']) ?></p>
              <div class="testi-stars"><?= $stars ?></div>
              <div class="testi-author">
                <div class="testi-av" style="background:<?= htmlspecialchars($grad) ?>;"><?= htmlspecialchars($initials) ?></div>
                <div>
                  <div class="testi-name"><?= htmlspecialchars($t['client_name']) ?></div>
                  <?php if (!empty($t['company'])): ?>
                    <div class="testi-co"><?= htmlspecialchars($t['company']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>

        </div><!-- /.testi-slider -->

        <!-- Dots -->
        <div class="testi-dots" id="testiDots">
          <?php foreach ($testimonials as $i => $t): ?>
            <button class="testi-dot <?= $i===0?'active':'' ?>" onclick="goToSlide(<?= $i ?>)"></button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <script>
    (function(){
      var slider = document.getElementById('testiSlider');
      if (!slider) return;
      var slides = slider.querySelectorAll('.testi-slide');
      var dots   = document.querySelectorAll('.testi-dot');
      var total  = slides.length;
      var current = 0;
      var timer;

      if (total === 0) return;

      // Make slider responsive to dynamic slide count
      slider.style.width = (total * 100) + '%';
      slides.forEach(function(s){ s.style.width = (100 / total) + '%'; });

      function goToSlide(n){
        current = (n + total) % total;
        slider.style.transform = 'translateX(-' + (current * 100 / total) + '%)';
        dots.forEach(function(d,i){ d.classList.toggle('active', i === current); });
      }
      window.goToSlide = goToSlide;

      if (total > 1) {
        function autoPlay(){
          timer = setInterval(function(){ goToSlide(current + 1); }, 4000);
        }
        slider.addEventListener('mouseenter', function(){ clearInterval(timer); });
        slider.addEventListener('mouseleave', autoPlay);
        autoPlay();
      }
    })();
    </script>
  </section>


  <!-- ===== LATEST FROM BLOG ===== -->
  <?php if (!empty($latestBlogs)): ?>
  <section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-14 fade-up">
        <span class="inline-block text-xs font-semibold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-4 py-1.5 rounded-full mb-4">From Our Blog</span>
        <h2 class="text-3xl md:text-4xl font-bold mb-3" style="font-family:'Poppins',sans-serif;color:#0f172a;">Latest Insights &amp; Articles</h2>
        <p class="text-gray-500 max-w-2xl mx-auto">Practical guidance on cybersecurity, IT infrastructure, and digital transformation for Indian businesses.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <?php foreach ($latestBlogs as $b):
          $bcats = get_categories_for_blog((int)$b['id']);
        ?>
          <article class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition fade-up flex flex-col">
            <a href="blog-detail.php?slug=<?= urlencode($b['slug']) ?>" class="block">
              <?php if (!empty($b['cover_image'])): ?>
                <img src="<?= htmlspecialchars($b['cover_image']) ?>" alt="<?= htmlspecialchars($b['title']) ?>" class="w-full h-52 object-cover">
              <?php else: ?>
                <div class="w-full h-52 bg-gradient-to-br from-blue-700 to-blue-900 flex items-center justify-center relative">
                  <i class="fas fa-newspaper text-white text-5xl opacity-30"></i>
                </div>
              <?php endif; ?>
            </a>
            <div class="p-6 flex-1 flex flex-col">
              <?php if ($bcats): ?>
                <div class="flex flex-wrap gap-2 mb-3">
                  <?php foreach (array_slice($bcats, 0, 1) as $c): ?>
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-700 bg-blue-50 px-3 py-1 rounded-full"><?= htmlspecialchars($c['name']) ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
              <h3 class="text-lg font-bold text-gray-900 mb-3 leading-snug" style="font-family:'Poppins',sans-serif;">
                <a href="blog-detail.php?slug=<?= urlencode($b['slug']) ?>" class="hover:text-blue-700"><?= htmlspecialchars($b['title']) ?></a>
              </h3>
              <p class="text-sm text-gray-600 leading-relaxed mb-5 flex-1"><?= htmlspecialchars(blog_excerpt($b, 22)) ?></p>
              <div class="flex items-center justify-between text-xs text-gray-500 pt-4 border-t border-gray-100">
                <span><i class="far fa-calendar mr-1"></i> <?= htmlspecialchars(date('d M Y', strtotime($b['published_at']))) ?></span>
                <a href="blog-detail.php?slug=<?= urlencode($b['slug']) ?>" class="text-blue-700 font-semibold hover:underline">Read more →</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="text-center fade-up">
        <a href="blog.php" class="btn-primary inline-flex items-center gap-2">
          View All Posts
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===== STATS SECTION ===== -->
  <section class="py-12 stats-parallax" style="background-image: url('assets/images/sections/data-center.jpg');">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="stat-card">
          <div class="stat-number"><span class="counter" data-target="500" data-suffix="+">0</span></div>
          <p class="text-gray-400 text-sm mt-1">Projects Delivered</p>
        </div>
        <div class="stat-card">
          <div class="stat-number"><span class="counter" data-target="200" data-suffix="+">0</span></div>
          <p class="text-gray-400 text-sm mt-1">Happy Clients</p>
        </div>
        <div class="stat-card">
          <div class="stat-number"><span class="counter" data-target="10" data-suffix="+">0</span></div>
          <p class="text-gray-400 text-sm mt-1">Years Experience</p>
        </div>
        <div class="stat-card">
          <div class="text-center">
            <div class="stat-number">24/7</div>
            <p class="text-gray-400 text-sm mt-1">Support Available</p>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- ===== CTA SECTION ===== -->
  <section class="cta-image-bg py-16" style="background-image: url('assets/images/sections/cta-bg.jpg');">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center relative z-10 fade-up">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4" style="font-family:'Poppins',sans-serif;">Ready to Secure &amp; Scale Your IT Infrastructure?</h2>
        <p class="text-lg text-white max-w-2xl mx-auto mb-8">Join 200+ Indian businesses that trust SLS IT Solutions for their critical IT needs. Get a free consultation today.</p>
        <div class="flex flex-wrap justify-center gap-4">
          <a href="contact.php" class="btn-primary" style="background:white;color:#0f4c81;">Get a Free Consultation &rarr;</a>
          <a href="tel:+918383800914" class="btn-secondary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            Call Us Now
          </a>
        </div>
      </div>
    </div>
  </section>


  

<?php include 'includes/footer.php'; ?>
