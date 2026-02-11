<?php
/**
 * Check and Report Field Types
 * 
 * Visit: https://westgategroup.wpenginepowered.com/check-field-types.php?key=check-2024
 */

require_once('wp-load.php');

if (!isset($_GET['key']) || $_GET['key'] !== 'check-2024') {
    die('Access denied. Add ?key=check-2024');
}

echo '<h1>Theme Options Field Type Report</h1><pre>';

global $wpdb;

// Get all theme options
$results = $wpdb->get_results(
    "SELECT option_name 
     FROM {$wpdb->options} 
     WHERE option_name LIKE 'cnf_theme_options_%'
     ORDER BY option_name",
    ARRAY_A
);

$total_fields = count($results);
$empty_fields = 0;
$filled_fields = 0;
$problematic_fields = [];

// Fields that should be 'paragraphtext' for multi-line text
$multiline_fields = [
    'cookie_preferences_description',
    'machine_filter_description',
    'machine_filter_no_results_text',
    'footer_about_description',
    // Add any field that should support multiple lines
];

echo "TOTAL FIELDS IN DATABASE: {$total_fields}\n";
echo str_repeat('=', 60) . "\n\n";

echo "CHECKING FIELD VALUES...\n";
echo str_repeat('=', 60) . "\n";

foreach ($results as $row) {
    $field_name = str_replace('cnf_theme_options_', '', $row['option_name']);
    $value = get_option($row['option_name']);
    
    if (empty($value)) {
        $empty_fields++;
        
        // Check if it's a field that should be paragraphtext
        if (in_array($field_name, $multiline_fields)) {
            $problematic_fields[] = $field_name;
            echo "⚠️  {$field_name} - EMPTY (should be paragraphtext)\n";
        }
    } else {
        $filled_fields++;
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "SUMMARY\n";
echo str_repeat('=', 60) . "\n";
echo "Total fields: {$total_fields}\n";
echo "Filled fields: {$filled_fields}\n";
echo "Empty fields: {$empty_fields}\n";
echo "Problematic fields: " . count($problematic_fields) . "\n";

if (count($problematic_fields) > 0) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "FIELDS NEEDING ATTENTION\n";
    echo str_repeat('=', 60) . "\n";
    
    foreach ($problematic_fields as $field) {
        echo "• {$field}\n";
    }
    
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "HOW TO FIX\n";
    echo str_repeat('=', 60) . "\n";
    echo "In Pods Admin:\n";
    echo "1. Go to Pods Admin → Settings Groups\n";
    echo "2. Edit 'CNF Theme Options'\n";
    echo "3. Find these fields\n";
    echo "4. Change field type from 'textarea' to 'Paragraph Text'\n";
    echo "5. Save\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "API FIELD COUNT CHECK\n";
echo str_repeat('=', 60) . "\n";

// Check API
$api_url = 'https://westgategroup.wpengine.com/wp-json/cnf/v1/bootstrap';
$response = wp_remote_get($api_url);

if (!is_wp_error($response)) {
    $body = json_decode($response['body'], true);
    $api_field_count = count($body['options'] ?? []);
    echo "API returns: {$api_field_count} fields\n";
    echo "Database has: {$total_fields} fields\n";
    echo "Difference: " . ($total_fields - $api_field_count) . " fields\n";
    
    if ($total_fields > $api_field_count) {
        echo "\n⚠️  Some fields are not being returned by the API!\n";
        echo "This could be due to:\n";
        echo "- Invalid field types\n";
        echo "- Serialization issues\n";
        echo "- Empty arrays being filtered out\n";
    }
}

echo '</pre>';
