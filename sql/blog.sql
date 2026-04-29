-- SLS IT Solutions — Blog module schema (run on slsdb)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `blogs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL,
  `excerpt` VARCHAR(500) DEFAULT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `cover_image` VARCHAR(255) DEFAULT NULL,
  `author` VARCHAR(120) NOT NULL DEFAULT 'SLS IT Solutions',
  `is_published` TINYINT(1) NOT NULL DEFAULT 0,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  `meta_title` VARCHAR(200) DEFAULT NULL,
  `meta_desc` VARCHAR(300) DEFAULT NULL,
  `published_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_blog_slug` (`slug`),
  KEY `idx_blog_pub` (`is_published`, `published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_tags` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(60) NOT NULL,
  `slug` VARCHAR(80) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_tag_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_category_map` (
  `blog_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`blog_id`, `category_id`),
  KEY `idx_bcm_cat` (`category_id`),
  CONSTRAINT `fk_bcm_blog` FOREIGN KEY (`blog_id`) REFERENCES `blogs`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bcm_cat`  FOREIGN KEY (`category_id`) REFERENCES `blog_categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_tag_map` (
  `blog_id` INT UNSIGNED NOT NULL,
  `tag_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`blog_id`, `tag_id`),
  KEY `idx_btm_tag` (`tag_id`),
  CONSTRAINT `fk_btm_blog` FOREIGN KEY (`blog_id`) REFERENCES `blogs`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_btm_tag`  FOREIGN KEY (`tag_id`)  REFERENCES `blog_tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed categories
INSERT IGNORE INTO `blog_categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Cybersecurity', 'cybersecurity', 'Threat protection, ransomware, zero-trust and more.'),
(2, 'IT Infrastructure', 'it-infrastructure', 'Networks, servers, virtualisation and cloud.'),
(3, 'Backup & Recovery', 'backup-recovery', 'Data protection, DR planning, business continuity.'),
(4, 'DPDP & Compliance', 'dpdp-compliance', 'India\'s DPDP Act, audits and compliance posture.'),
(5, 'Industry News', 'industry-news', 'Updates, advisories and ecosystem news.'),
(6, 'Tips & Best Practices', 'tips-best-practices', 'Practical guides for Indian SMEs.');

-- Seed tags
INSERT IGNORE INTO `blog_tags` (`id`, `name`, `slug`) VALUES
(1,'Zero Trust','zero-trust'),
(2,'Ransomware','ransomware'),
(3,'DPDP Act','dpdp-act'),
(4,'Cloud','cloud'),
(5,'Server Migration','server-migration'),
(6,'Firewall','firewall'),
(7,'SME India','sme-india'),
(8,'MSP','msp'),
(9,'Audit','audit'),
(10,'SLA','sla');

-- Seed posts
INSERT IGNORE INTO `blogs`
(`id`,`title`,`slug`,`excerpt`,`content`,`cover_image`,`author`,`is_published`,`meta_title`,`meta_desc`,`published_at`)
VALUES
(1, 'Why DPDP Act Compliance Matters for Indian SMEs in 2025',
 'why-dpdp-act-compliance-matters-for-indian-smes',
 'A practical, no-nonsense look at India\'s Digital Personal Data Protection Act and what every SME needs to do this year.',
 '<h2>Introduction</h2><p>India\'s <strong>Digital Personal Data Protection Act (DPDP)</strong> is reshaping how businesses handle personal data. Whether you have 10 employees or 500, the rules apply.</p><h3>What changes for your business?</h3><ul><li>Explicit consent for data collection</li><li>Clear purpose limitation</li><li>Right to erasure for users</li><li>Mandatory breach notifications</li></ul><h3>Where most SMEs fall short</h3><p>Many small businesses still rely on shared drives, unencrypted backups, and personal email for client data. That won\'t cut it under DPDP.</p><blockquote>The cost of getting compliance wrong is far higher than the cost of doing it right from day one.</blockquote><h3>Where to start</h3><p>Begin with a quick audit: where does personal data live, who has access, and how is it protected? From there a structured roadmap is straightforward.</p>',
 NULL, 'Arbaz Khan', 1,
 'DPDP Act Compliance for Indian SMEs — A Practical 2025 Guide',
 'A practical, no-nonsense look at India\'s DPDP Act and what every SME needs to do — consent, breach notifications, audit and more.',
 NOW() - INTERVAL 5 DAY),

(2, 'Ransomware in Manufacturing: Five Lessons From Recent Indian Incidents',
 'ransomware-in-manufacturing-five-lessons',
 'Looking at real ransomware cases at Indian manufacturers — and the controls that would have stopped them.',
 '<h2>The pattern is the same</h2><p>Almost every ransomware case we\'ve handled at <strong>SLS IT Solutions</strong> follows a familiar pattern: phishing, lateral movement, and weak backups.</p><h3>Five lessons we\'ve learned</h3><ol><li><strong>Email is still the front door.</strong> MFA, DMARC and user training matter.</li><li><strong>Flat networks accelerate damage.</strong> Segmentation buys you time.</li><li><strong>"We have backups" isn\'t enough.</strong> They must be offline and tested.</li><li><strong>Endpoint visibility is non-negotiable.</strong> EDR pays for itself the first incident.</li><li><strong>Have a runbook.</strong> Decisions made under pressure are bad decisions.</li></ol><p>Our team can run a ransomware-readiness assessment in under a week.</p>',
 NULL, 'Arbaz Khan', 1,
 'Ransomware in Manufacturing — 5 Lessons From Indian Incidents',
 'Real ransomware cases at Indian manufacturers — and the practical security controls that would have prevented them.',
 NOW() - INTERVAL 12 DAY),

(3, 'Cloud or On-Premise? A Practical Decision Framework for SMEs',
 'cloud-or-on-premise-a-practical-decision-framework',
 'Cloud isn\'t always cheaper. On-premise isn\'t always safer. Here\'s how to decide based on workload, regulation, and team.',
 '<p>The cloud-vs-on-prem debate has matured. The honest answer is: <em>it depends</em>. Here is the framework we use with our clients.</p><h3>Three questions to ask</h3><ol><li>What does the workload <strong>actually</strong> need? IOPS, latency, uptime?</li><li>What does the law require for this data?</li><li>Do you have someone to run it 24×7?</li></ol><h3>When cloud wins</h3><ul><li>Spiky or growing workloads</li><li>Distributed teams</li><li>You don\'t want to manage hardware refresh cycles</li></ul><h3>When on-prem still wins</h3><ul><li>Predictable, heavy I/O workloads</li><li>Tight regulatory boundaries</li><li>Existing investment in DC space</li></ul><p>Hybrid is the most common answer in 2025. Pick by workload, not by ideology.</p>',
 NULL, 'Arbaz Khan', 1,
 'Cloud vs On-Premise — A Practical Framework for Indian SMEs',
 'Cloud isn\'t always cheaper. On-premise isn\'t always safer. Here\'s a practical decision framework for SMEs in 2025.',
 NOW() - INTERVAL 22 DAY);

-- Map sample posts to categories/tags
INSERT IGNORE INTO `blog_category_map` (`blog_id`,`category_id`) VALUES
(1,4),(1,6),
(2,1),(2,5),(2,6),
(3,2),(3,6);

INSERT IGNORE INTO `blog_tag_map` (`blog_id`,`tag_id`) VALUES
(1,3),(1,9),(1,7),
(2,2),(2,7),(2,9),
(3,4),(3,7);

SET FOREIGN_KEY_CHECKS = 1;
