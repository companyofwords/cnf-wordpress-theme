<?php
/**
 * Test each bootstrap function individually to find the error
 */

WP_CLI::line('');
WP_CLI::line('=== Testing Bootstrap Functions ===');
WP_CLI::line('');

// Test 1: cnf_get_theme_options
WP_CLI::line('1. Testing cnf_get_theme_options()...');
try {
    $options = cnf_get_theme_options();
    WP_CLI::success('✓ Returns ' . count($options) . ' options');
} catch (Exception $e) {
    WP_CLI::error('✗ Failed: ' . $e->getMessage());
}

// Test 2: cnf_get_menus
WP_CLI::line('2. Testing cnf_get_menus()...');
try {
    $menus = cnf_get_menus();
    WP_CLI::success('✓ Returns menus: ' . print_r(array_keys($menus), true));
} catch (Exception $e) {
    WP_CLI::error('✗ Failed: ' . $e->getMessage());
}

// Test 3: cnf_get_pages
WP_CLI::line('3. Testing cnf_get_pages()...');
try {
    $pages = cnf_get_pages();
    WP_CLI::success('✓ Returns ' . count($pages) . ' pages');
} catch (Exception $e) {
    WP_CLI::error('✗ Failed: ' . $e->getMessage());
}

// Test 4: cnf_get_machines
WP_CLI::line('4. Testing cnf_get_machines()...');
try {
    $machines = cnf_get_machines();
    WP_CLI::success('✓ Returns ' . count($machines) . ' machines');
} catch (Exception $e) {
    WP_CLI::error('✗ Failed: ' . $e->getMessage());
}

// Test 5: cnf_get_uses
WP_CLI::line('5. Testing cnf_get_uses()...');
try {
    $uses = cnf_get_uses();
    WP_CLI::success('✓ Returns ' . count($uses) . ' uses');
} catch (Exception $e) {
    WP_CLI::error('✗ Failed: ' . $e->getMessage());
}

// Test 6: cnf_get_faqs
WP_CLI::line('6. Testing cnf_get_faqs()...');
try {
    $faqs = cnf_get_faqs();
    WP_CLI::success('✓ Returns ' . count($faqs) . ' FAQs');
} catch (Exception $e) {
    WP_CLI::error('✗ Failed: ' . $e->getMessage());
}

// Test 7: cnf_get_news_posts
WP_CLI::line('7. Testing cnf_get_news_posts()...');
try {
    $posts = cnf_get_news_posts();
    WP_CLI::success('✓ Returns ' . count($posts) . ' posts');
} catch (Exception $e) {
    WP_CLI::error('✗ Failed: ' . $e->getMessage());
}

WP_CLI::line('');
WP_CLI::line('=== Testing Complete Bootstrap Function ===');
WP_CLI::line('');

// Test full bootstrap
try {
    $data = cnf_get_bootstrap_data();
    WP_CLI::success('✓ Bootstrap function works!');
    WP_CLI::line('Keys: ' . implode(', ', array_keys($data)));
} catch (Exception $e) {
    WP_CLI::error('✗ Bootstrap failed: ' . $e->getMessage());
}
