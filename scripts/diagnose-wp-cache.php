<?php
/**
 * WP-CLI Diagnostic Script
 *
 * Usage: wp eval-file diagnose-wp-cache.php
 *
 * This will show EXACTLY what's in the database vs what the API returns
 */

global $wpdb;

WP_CLI::line('');
WP_CLI::line('=== DIAGNOSTIC REPORT ===');
WP_CLI::line('');

// Test 1: Direct database query
WP_CLI::line('TEST 1: Direct Database Query');
WP_CLI::line('----------------------------');

$db_results = $wpdb->get_results(
    "SELECT option_name, option_value, autoload
     FROM {$wpdb->options}
     WHERE option_name LIKE 'cnf_theme_options_%'
     ORDER BY option_name",
    ARRAY_A
);

WP_CLI::line('Database count: ' . count($db_results));
WP_CLI::line('First 10 options:');
foreach (array_slice($db_results, 0, 10) as $row) {
    WP_CLI::line('  - ' . str_replace('cnf_theme_options_', '', $row['option_name']) .
                 ' (autoload: ' . $row['autoload'] . ')');
}
WP_CLI::line('');

// Test 2: What cnf_get_theme_options() returns
WP_CLI::line('TEST 2: cnf_get_theme_options() Function');
WP_CLI::line('--------------------------------------');

if (function_exists('cnf_get_theme_options')) {
    $options = cnf_get_theme_options();
    WP_CLI::line('Function returns: ' . count($options) . ' options');
    WP_CLI::line('Keys: ' . implode(', ', array_slice(array_keys($options), 0, 10)));
} else {
    WP_CLI::error('cnf_get_theme_options() function not found!');
}
WP_CLI::line('');

// Test 3: REST API simulation
WP_CLI::line('TEST 3: REST API Simulation');
WP_CLI::line('---------------------------');

$rest_results = $wpdb->get_results(
    "SELECT option_name, option_value
     FROM {$wpdb->options}
     WHERE option_name LIKE 'cnf_theme_options_%'",
    ARRAY_A
);

$rest_options = array();
foreach ($rest_results as $row) {
    $key = str_replace('cnf_theme_options_', '', $row['option_name']);
    $rest_options[$key] = maybe_unserialize($row['option_value']);
}

WP_CLI::line('REST API would return: ' . count($rest_options) . ' options');
WP_CLI::line('');

// Test 4: Check for object cache
WP_CLI::line('TEST 4: Object Cache Check');
WP_CLI::line('-------------------------');

if (wp_using_ext_object_cache()) {
    WP_CLI::warning('External object cache IS ACTIVE (this could be caching the query)');

    // Try to flush it
    wp_cache_flush();
    WP_CLI::line('  ✓ Flushed object cache');

    // Re-run the query
    $after_flush = $wpdb->get_results(
        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'cnf_theme_options_%'",
        ARRAY_A
    );
    WP_CLI::line('  After flush: ' . count($after_flush) . ' options');
} else {
    WP_CLI::success('No external object cache (good!)');
}
WP_CLI::line('');

// Test 5: Check specific critical options
WP_CLI::line('TEST 5: Critical Options Check');
WP_CLI::line('-----------------------------');

$critical_options = array(
    'filter_load_capacity_label',
    'filter_track_width_label',
    'filter_engine_power_label',
    'filter_clear_button',
    'home_hero_title',
    'cookie_title',
);

foreach ($critical_options as $field) {
    $value = get_option('cnf_theme_options_' . $field, '[NOT FOUND]');
    $status = ($value === '[NOT FOUND]') ? '✗ MISSING' : '✓ Found';
    WP_CLI::line('  ' . $status . ': ' . $field . ' = "' . substr($value, 0, 30) . '"');
}
WP_CLI::line('');

// Test 6: Make a real REST API call
WP_CLI::line('TEST 6: Actual REST API Response');
WP_CLI::line('-------------------------------');

$response = wp_remote_get(home_url('/wp-json/cnf/v1/theme-options'));
if (!is_wp_error($response)) {
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    WP_CLI::line('REST API returns: ' . count($data) . ' options');
    WP_CLI::line('First 5 keys: ' . implode(', ', array_slice(array_keys($data), 0, 5)));
} else {
    WP_CLI::error('REST API call failed: ' . $response->get_error_message());
}
WP_CLI::line('');

// Summary
WP_CLI::line('=== SUMMARY ===');
WP_CLI::line('');
WP_CLI::line('Database has: ' . count($db_results) . ' options');
WP_CLI::line('Function returns: ' . count($options) . ' options');
WP_CLI::line('REST API returns: ' . count($data) . ' options');
WP_CLI::line('');

if (count($db_results) != count($data)) {
    WP_CLI::error('MISMATCH DETECTED! Database has ' . count($db_results) . ' but REST API returns ' . count($data));
    WP_CLI::line('');
    WP_CLI::line('Possible causes:');
    WP_CLI::line('1. WP Engine page cache (purge via WP Engine dashboard)');
    WP_CLI::line('2. WordPress transient cache');
    WP_CLI::line('3. REST API endpoint cache');
    WP_CLI::line('4. Function code issue in cnf-rest-api.php');
} else {
    WP_CLI::success('Everything matches! Cache is clear.');
}
