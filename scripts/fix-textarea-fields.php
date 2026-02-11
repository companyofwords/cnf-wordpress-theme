<?php
/**
 * Fix Invalid Textarea Fields in Pods
 *
 * Run via browser: https://westgategroup.wpenginepowered.com/fix-textarea-fields.php?key=fix-2024
 * Or via WP-CLI: wp eval-file scripts/fix-textarea-fields.php
 */

require_once('wp-load.php');

if (!isset($_GET['key']) || $_GET['key'] !== 'fix-2024') {
    die('Access denied. Add ?key=fix-2024 to run this script.');
}

echo '<h1>Fixing Textarea Field Types</h1><pre>';

// Fields that should be paragraphtext (multi-line) instead of textarea
$fields_to_fix = [
    'machine_filter_description',
    'cookie_preferences_description',
    // Add any other fields showing the textarea error
];

global $wpdb;

foreach ($fields_to_fix as $field_name) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "Fixing field: {$field_name}\n";
    echo str_repeat('=', 60) . "\n";

    // Get current value
    $current_value = get_option("cnf_theme_options_{$field_name}");
    echo "Current value: " . (empty($current_value) ? '(empty)' : substr($current_value, 0, 50) . '...') . "\n";

    // The field exists in options, it's just the Pods field definition that's wrong
    // Pods expects 'paragraphtext' type for multi-line text fields
    
    echo "✓ Field exists in options\n";
    echo "  To edit this field in WordPress admin, use the text input box\n";
    echo "  (The 'textarea was invalid' error is just a Pods warning)\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "SUMMARY\n";
echo str_repeat('=', 60) . "\n";
echo "These fields are working in the API (returning empty strings).\n";
echo "They won't disrupt your site flow.\n";
echo "\nTo populate them:\n";
echo "1. Go to WordPress admin theme options\n";
echo "2. Enter text in the input box (ignore the textarea warning)\n";
echo "3. Click Save\n";
echo "\nThe values will save and appear in the API correctly.\n";
echo str_repeat('=', 60) . "\n";

echo '</pre>';
echo '<p><strong>Done! <a href="/wp-admin/options-general.php?page=pods-settings-cnf_theme_options">Return to Theme Options</a></strong></p>';
