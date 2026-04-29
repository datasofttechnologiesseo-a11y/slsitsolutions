<?php
// Copy this file to config.php and fill in your real values.
// includes/config.php is gitignored and must NOT be committed.

return [

    // -------- Database --------
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'database' => 'slsdb',
        'username' => 'slsuser',
        'password' => 'Sls@12345',
        'charset'  => 'utf8mb4',
    ],

    // -------- Mail (SMTP — recommended for cPanel) --------
    // Use the email account you create in cPanel > Email Accounts.
    // Typical cPanel values:
    //   host: mail.yourdomain.com
    //   port: 465 (SSL) or 587 (TLS)
    'mail' => [
        'driver'      => 'smtp',                  // 'smtp' or 'mail'
        'host'        => 'mail.slsitsolutions.com',
        'port'        => 465,
        'encryption'  => 'ssl',                   // 'ssl' or 'tls'
        'username'    => 'sales@slsitsolutions.com',
        'password'    => 'CHANGE_ME',
        'from_email'  => 'sales@slsitsolutions.com',
        'from_name'   => 'SLS IT Solutions Website',
        'to_email'    => 'sales@slsitsolutions.com',  // where enquiries land
        'to_name'     => 'SLS IT Solutions',
    ],

    // -------- App --------
    'app' => [
        'name' => 'SLS IT Solutions',
        // Used for absolute URLs in emails. Leave blank for relative.
        'base_url' => '',
        // Set to true on production to suppress error details
        'production' => false,
    ],
];
