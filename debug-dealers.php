<?php
/**
 * Debug Dealers
 *
 * Upload and visit: https://westgategroup.wpengine.com/debug-dealers.php?key=debug-2024
 */

require_once('wp-load.php');

if (!isset($_GET['key']) || $_GET['key'] !== 'debug-2024') {
    die('Access denied. Add ?key=debug-2024');
}

echo '<h1>Debug Dealers</h1><pre>';

// Check if Pods exists
echo "Pods available: " . (function_exists('pods') ? 'YES' : 'NO') . "\n\n";

// Get all cnf_dealer posts
$posts = get_posts([
    'post_type' => 'cnf_dealer',
    'posts_per_page' => -1,
    'post_status' => 'any',
]);

echo "Total cnf_dealer posts: " . count($posts) . "\n\n";

foreach ($posts as $post) {
    echo str_repeat('=', 60) . "\n";
    echo "Post ID: {$post->ID}\n";
    echo "Title: {$post->post_title}\n";
    echo "Status: {$post->post_status}\n";
    echo "Type: {$post->post_type}\n";

    // Try to get Pods data
    if (function_exists('pods')) {
        echo "\nTrying pods('cnf_dealer', {$post->ID})...\n";
        $pod = pods('cnf_dealer', $post->ID);

        if ($pod && $pod->exists()) {
            echo "✓ Pod exists\n";
            echo "Pod ID: " . $pod->id() . "\n";

            // Try to get fields
            $fields = ['name', 'address', 'sales_area', 'phone', 'website'];
            foreach ($fields as $field) {
                $value = $pod->field($field);
                echo "  {$field}: " . ($value ? $value : '(empty)') . "\n";
            }

            // Try export
            echo "\nTrying export...\n";
            $export = $pod->export();
            echo "Export result:\n";
            print_r($export);
        } else {
            echo "✗ Pod doesn't exist or failed to load\n";
        }

        // Also try pods() with array
        echo "\nTrying pods() with array query...\n";
        $pod2 = pods('cnf_dealer', ['where' => "ID = {$post->ID}", 'limit' => 1]);
        if ($pod2 && $pod2->total() > 0) {
            echo "✓ Found via array query\n";
            $pod2->fetch();
            echo "Fields after fetch:\n";
            foreach ($fields as $field) {
                $value = $pod2->field($field);
                echo "  {$field}: " . ($value ? $value : '(empty)') . "\n";
            }
        } else {
            echo "✗ Not found via array query\n";
        }
    }

    // Get post meta
    echo "\nPost meta:\n";
    $meta = get_post_meta($post->ID);
    foreach ($meta as $key => $value) {
        if (strpos($key, 'name') !== false || strpos($key, 'address') !== false ||
            strpos($key, 'sales') !== false || strpos($key, 'phone') !== false ||
            strpos($key, 'website') !== false) {
            echo "  {$key}: " . print_r($value, true);
        }
    }

    echo "\n";
}

// Test the cnf_get_dealers function directly
echo str_repeat('=', 60) . "\n";
echo "Testing cnf_get_dealers() function...\n";
echo str_repeat('=', 60) . "\n";

if (function_exists('cnf_get_dealers')) {
    $dealers = cnf_get_dealers();
    echo "Result count: " . count($dealers) . "\n";
    if (count($dealers) > 0) {
        echo "\nFirst dealer:\n";
        print_r($dealers[0]);
    }
} else {
    echo "Function not found\n";
}

// Try manual Pods query
echo "\n" . str_repeat('=', 60) . "\n";
echo "Manual Pods query...\n";
echo str_repeat('=', 60) . "\n";

if (function_exists('pods')) {
    $dealers_pod = pods('cnf_dealer', [
        'limit' => -1,
        'orderby' => 'post_title ASC',
    ]);

    echo "Total found: " . ($dealers_pod ? $dealers_pod->total() : 0) . "\n\n";

    if ($dealers_pod && $dealers_pod->total() > 0) {
        $count = 0;
        while ($dealers_pod->fetch()) {
            $count++;
            echo "Dealer {$count}:\n";
            echo "  ID: " . $dealers_pod->id() . "\n";
            echo "  Title: " . $dealers_pod->field('post_title') . "\n";
            echo "  Slug: " . $dealers_pod->field('slug') . "\n";

            $export = $dealers_pod->export();
            echo "  Export keys: " . implode(', ', array_keys($export)) . "\n";
            echo "  Pods data:\n";
            if (isset($export['pods'])) {
                print_r($export['pods']);
            } else {
                echo "    (no pods data)\n";
            }
            echo "\n";
        }
    }
}

echo '</pre>';
