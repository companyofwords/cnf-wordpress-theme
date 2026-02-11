<?php
/**
 * Create CNF Promotions
 *
 * Upload this file to your WordPress root and visit it in your browser:
 * https://westgategroup.wpengine.com/create-promotions.php?key=create-promotions-2024
 *
 * This will create all 3 promotion posts with their metadata.
 * Delete this file after running it.
 */

// Load WordPress
require_once('wp-load.php');

// Security check
if (!isset($_GET['key']) || $_GET['key'] !== 'create-promotions-2024') {
    die('Access denied. Add ?key=create-promotions-2024 to the URL to run this script.');
}

// Promotion data from full-wp.ts
$promotions = [
    [
        'title' => 'Security',
        'description' => 'Enhanced security features for your peace of mind.',
        'icon_name' => 'shield',
        'background_image' => '/uploads/cnf-hero.webp',
        'menu_order' => 1,
    ],
    [
        'title' => 'Maintenance',
        'description' => 'Regular maintenance to keep your system running smoothly.',
        'icon_name' => 'wrench',
        'background_image' => '/uploads/cnf-t95-home.webp',
        'menu_order' => 2,
    ],
    [
        'title' => 'Performance',
        'description' => 'Boosted performance for faster and more efficient operations.',
        'icon_name' => 'trophy',
        'background_image' => '/uploads/cnf-news.webp',
        'menu_order' => 3,
    ],
];

echo '<h1>Creating CNF Promotions</h1>';
echo '<pre>';

$created = 0;
$skipped = 0;
$errors = 0;

foreach ($promotions as $promo_data) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "Processing: {$promo_data['title']}\n";
    echo str_repeat('=', 60) . "\n";

    // Check if promotion already exists
    $existing = get_posts([
        'post_type' => 'cnf_promotion',
        'title' => $promo_data['title'],
        'post_status' => 'any',
        'numberposts' => 1,
    ]);

    if (!empty($existing)) {
        echo "✓ Promotion already exists (ID: {$existing[0]->ID})\n";
        $skipped++;
        continue;
    }

    // Create the post
    $post_id = wp_insert_post([
        'post_title' => $promo_data['title'],
        'post_type' => 'cnf_promotion',
        'post_status' => 'publish',
        'post_content' => $promo_data['description'],
        'menu_order' => $promo_data['menu_order'],
    ]);

    if (is_wp_error($post_id)) {
        echo "✗ Error creating promotion: " . $post_id->get_error_message() . "\n";
        $errors++;
        continue;
    }

    echo "✓ Created post (ID: {$post_id})\n";

    // Add post meta for Pods fields
    update_post_meta($post_id, 'title', $promo_data['title']);
    update_post_meta($post_id, 'description', $promo_data['description']);
    update_post_meta($post_id, 'icon_name', $promo_data['icon_name']);
    update_post_meta($post_id, 'background_image', $promo_data['background_image']);

    echo "✓ Post meta saved successfully\n";
    echo "  - Title: {$promo_data['title']}\n";
    echo "  - Description: {$promo_data['description']}\n";
    echo "  - Icon: {$promo_data['icon_name']}\n";
    echo "  - Background: {$promo_data['background_image']}\n";
    echo "  - Order: {$promo_data['menu_order']}\n";
    $created++;
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "SUMMARY\n";
echo str_repeat('=', 60) . "\n";
echo "Created: {$created}\n";
echo "Skipped (already exist): {$skipped}\n";
echo "Errors: {$errors}\n";
echo "Total: " . count($promotions) . "\n";

if ($created > 0) {
    echo "\n✓ SUCCESS! {$created} promotions were created.\n";
    echo "\nPlease delete this file (create-promotions.php) for security.\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "Verifying API endpoint...\n";
echo str_repeat('=', 60) . "\n";

// Test the cnf_get_promotions function
if (function_exists('cnf_get_promotions')) {
    $api_promotions = cnf_get_promotions();
    echo "✓ API function found\n";
    echo "  Promotions in API: " . count($api_promotions) . "\n";

    if (count($api_promotions) > 0) {
        echo "\nFirst promotion in API:\n";
        print_r($api_promotions[0]);
    }
} else {
    echo "✗ cnf_get_promotions() function not found\n";
    echo "  Make sure cnf-rest-api.php has been updated\n";
}

echo '</pre>';

// Clear WordPress cache
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo '<p>✓ WordPress cache cleared</p>';
}

echo '<p><strong>Done! <a href="/">Return to home</a></strong></p>';
echo '<p style="color: red;"><strong>IMPORTANT: Delete this file (create-promotions.php) for security.</strong></p>';
