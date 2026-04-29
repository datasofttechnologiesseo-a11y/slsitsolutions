<?php
require_once __DIR__ . '/db.php';

/**
 * Get a single setting value with optional default fallback.
 */
function get_setting(string $key, $default = null) {
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];

    try {
        $stmt = db()->prepare('SELECT s_value FROM settings WHERE s_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $val = $row ? $row['s_value'] : $default;
    } catch (\Throwable $e) {
        $val = $default;
    }
    $cache[$key] = $val;
    return $val;
}

/**
 * Set / upsert a single setting. Returns true on success.
 */
function set_setting(string $key, $value): bool {
    try {
        $stmt = db()->prepare(
            'INSERT INTO settings (s_key, s_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE s_value = VALUES(s_value)'
        );
        $stmt->execute([$key, (string)$value]);
        return true;
    } catch (\Throwable $e) {
        return false;
    }
}

/**
 * Return all settings rows for a category, ordered by sort_order.
 * Each row: [s_key, s_value, type, label, description, options, sort_order]
 */
function get_settings_by_category(string $category): array {
    try {
        $stmt = db()->prepare(
            'SELECT s_key, s_value, type, label, description, options, sort_order
             FROM settings WHERE category = ? ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    } catch (\Throwable $e) {
        return [];
    }
}

/**
 * Return all categories that have at least one setting.
 */
function get_setting_categories(): array {
    try {
        return db()->query('SELECT DISTINCT category FROM settings ORDER BY category')
                   ->fetchAll(PDO::FETCH_COLUMN);
    } catch (\Throwable $e) {
        return [];
    }
}
