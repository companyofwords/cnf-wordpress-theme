<?php
/**
 * Check Machine Pods Fields
 *
 * Visit: https://westgategroup.wpenginepowered.com/check-machine-fields.php?key=check-2024
 */

require_once('wp-load.php');

if (!isset($_GET['key']) || $_GET['key'] !== 'check-2024') {
    die('Access denied. Add ?key=check-2024');
}

echo '<h1>Machine Pods Fields Report</h1><pre>';

if (!function_exists('pods')) {
    echo "ERROR: Pods Framework not available\n";
    exit;
}

// Get one machine to see what fields exist
$machines = pods('cnf_machine', array('limit' => 1));

if (!$machines || $machines->total() === 0) {
    echo "No machines found\n";
    exit;
}

$machines->fetch();

echo "Machine Found:\n";
echo str_repeat('=', 60) . "\n";
echo "ID: " . $machines->id() . "\n";
echo "Post Title: " . $machines->field('post_title') . "\n";
echo "Slug: " . $machines->field('slug') . "\n\n";

echo "ALL PODS FIELDS:\n";
echo str_repeat('=', 60) . "\n";

$all_fields = $machines->export();

foreach ($all_fields as $field_name => $field_value) {
    $value_preview = is_array($field_value) ? json_encode($field_value) : $field_value;
    $value_preview = substr($value_preview, 0, 100);

    echo sprintf("%-30s %s\n", $field_name . ":", $value_preview);
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "LOOKING FOR TAGLINE/TITLE FIELD\n";
echo str_repeat('=', 60) . "\n";

$possible_fields = ['tagline', 'marketing_title', 'title', 'heading', 'display_title'];

foreach ($possible_fields as $field) {
    $value = $machines->field($field);
    if ($value) {
        echo "✓ FOUND: {$field} = {$value}\n";
    } else {
        echo "✗ NOT FOUND: {$field}\n";
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "RECOMMENDATION\n";
echo str_repeat('=', 60) . "\n";
echo "If no tagline field exists, you need to:\n";
echo "1. Go to Pods Admin → Edit Pod → CNF Machine\n";
echo "2. Add a new field called 'tagline' or 'title'\n";
echo "3. Field type: Plain Text\n";
echo "4. Save the pod\n";
echo "5. Then edit each machine and add the taglines:\n";
echo "   - T50: COMPACT POWER\n";
echo "   - T70: VERSATILE WORKHORSE\n";
echo "   - T95: ENHANCED EFFICIENCY\n";
echo "   - T150: MAXIMUM CAPACITY\n";

echo '</pre>';
