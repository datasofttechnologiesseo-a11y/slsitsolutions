-- SLS IT Solutions — Generic key/value settings store (run on slsdb)
-- Used by the "System Settings" page in the admin panel.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `s_key` VARCHAR(100) NOT NULL,
  `s_value` TEXT,
  `category` VARCHAR(50) NOT NULL DEFAULT 'general',
  `type` VARCHAR(20) NOT NULL DEFAULT 'text',
  `label` VARCHAR(150) DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `options` VARCHAR(500) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_settings_key` (`s_key`),
  KEY `idx_settings_cat` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- Seed: SMTP settings (matches keys read by includes/mailer.php)
-- ----------------------------------------------------------------
INSERT IGNORE INTO `settings` (`s_key`, `s_value`, `category`, `type`, `label`, `description`, `options`, `sort_order`) VALUES
('mail_driver',     'smtp',                          'smtp', 'select',   'Mail Driver',         'Use SMTP for cPanel-authenticated sending. Use mail() only if your server is configured for it.', 'smtp,mail', 10),
('mail_host',       'mail.slsitsolutions.com',       'smtp', 'text',     'SMTP Host',           'Your cPanel mail server hostname (e.g. mail.yourdomain.com).', NULL, 20),
('mail_port',       '465',                           'smtp', 'number',   'SMTP Port',           '465 for SSL, 587 for TLS.', NULL, 30),
('mail_encryption', 'ssl',                           'smtp', 'select',   'Encryption',          'Match your port: SSL = 465, TLS = 587.', 'ssl,tls', 40),
('mail_username',   'sales@slsitsolutions.com',      'smtp', 'text',     'SMTP Username',       'Usually the full email address of the cPanel mail account.', NULL, 50),
('mail_password',   '',                              'smtp', 'password', 'SMTP Password',       'Password for the cPanel email account above.', NULL, 60),
('mail_from_email', 'sales@slsitsolutions.com',      'smtp', 'text',     'From Email',          'Address shown as the sender. Usually the same as SMTP Username.', NULL, 70),
('mail_from_name',  'SLS IT Solutions Website',      'smtp', 'text',     'From Name',           'Display name shown to the recipient.', NULL, 80),
('mail_to_email',   'sales@slsitsolutions.com',      'smtp', 'text',     'Send Enquiries To',   'Mailbox that receives contact-form enquiries.', NULL, 90),
('mail_to_name',    'SLS IT Solutions',              'smtp', 'text',     'Recipient Name',      'Display name on enquiry notification emails.', NULL, 100);
