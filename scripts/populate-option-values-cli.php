<?php
/**
 * WP-CLI Command to Populate Theme Option Values
 *
 * Usage: wp eval-file populate-option-values-cli.php
 *
 * This script ensures all theme option fields have values in the database
 * so the REST API can return them.
 */

// Field definitions with default values
$field_defaults = array(
    // Cookie Consent
    'cookie_necessary_title' => 'Necessary Cookies',
    'cookie_necessary_description' => 'Essential for the website to function.',
    'cookie_analytics_title' => 'Analytics Cookies',
    'cookie_analytics_description' => 'Help us improve our website.',
    'cookie_marketing_title' => 'Marketing Cookies',
    'cookie_marketing_description' => 'Used to deliver personalized content.',
    'cookie_button_manage_cookies' => 'Manage Cookies',
    'cookie_privacy_policy_link' => '/privacy-policy',
    'cookie_privacy_policy_text' => 'Privacy Policy',
    'cookie_about_title' => 'What Are Cookies?',
    'cookie_about_description' => 'Cookies are small text files stored on your device.',
    'cookie_privacy_link_text' => 'Read our Privacy Policy',

    // Homepage
    'home_hero_title' => 'Premium Italian Mini Dumpers',
    'home_hero_description' => 'Professional tracked mini dumpers for construction and landscaping.',
    'home_hero_cta_quote' => 'Request a Quote',
    'home_hero_cta_brochure_text' => 'Download Brochure',
    'home_hero_cta_brochure_link' => '/brochure.pdf',
    'home_hero_cta_phone' => 'Call Us',
    'home_about_title' => 'About Us',
    'home_about_description' => 'Quality mini dumpers from Italy.',
    'home_features_title' => 'Premium Features',
    'home_features_subtitle' => 'Built for Performance',

    // About Page
    'about_hero_label' => 'About',
    'about_hero_title' => 'About CNF Mini Dumpers',
    'about_hero_description' => 'Your trusted partner for premium equipment.',
    'about_history_title' => 'Our Story',
    'about_history_content' => '',
    'about_values_title' => 'Our Values',
    'about_values_content' => '',
    'about_quality_title' => 'Quality Promise',
    'about_quality_content' => '',
    'about_products_title' => 'Our Products',
    'about_products_content' => '',
    'about_innovation_title' => 'Innovation',
    'about_innovation_content' => '',

    // Contact Page
    'contact_meta_title' => 'Contact Us',
    'contact_meta_description' => 'Get in touch with our team.',
    'contact_meta_keywords' => '',
    'contact_hero_label' => 'Contact',
    'contact_hero_title' => 'Get In Touch',
    'contact_hero_description' => 'We\'re here to help.',

    // Dealers Page
    'dealers_meta_title' => 'Find a Dealer',
    'dealers_meta_description' => 'Locate your nearest dealer.',
    'dealers_meta_keywords' => '',
    'dealers_hero_label' => 'Dealers',
    'dealers_hero_title' => 'Find Your Local Dealer',
    'dealers_hero_description' => 'Authorized dealers across the UK.',
    'dealers_become_title' => 'Become a Dealer',
    'dealers_become_description' => 'Join our dealer network.',
    'dealers_become_phone_label' => 'Call us:',
    'dealers_become_phone_number' => '',

    // Machines Page
    'machines_meta_title' => 'Mini Dumpers',
    'machines_meta_description' => 'Professional tracked mini dumpers.',
    'machines_meta_keywords' => '',
    'machines_hero_label' => 'Our Machines',
    'machines_hero_title' => 'Premium Mini Dumpers',
    'machines_hero_description' => 'Italian engineering excellence.',
    'machines_cta_title' => 'Find Your Perfect Machine',
    'machines_cta_description' => 'Contact us for more information.',
    'machines_cta_button_call' => 'Call Us',
    'machines_cta_button_quote' => 'Request Quote',

    // Section Labels
    'news_section_label' => 'News',
    'news_section_title' => 'Latest News',
    'dealers_section_label' => 'Dealers',
    'dealers_section_title' => 'Find a Dealer',
    'faqs_section_label' => 'FAQs',
    'faqs_section_title' => 'Frequently Asked Questions',
    'promotions_section_label' => 'Promotions',
    'promotions_section_title' => 'Special Offers',

    // Forms
    'contact_form_submit' => 'Send Message',
    'contact_form_direct_title' => 'Or contact us directly:',
    'quote_form_submit' => 'Request Quote',
    'dealer_form_submit' => 'Submit Enquiry',

    // Breadcrumbs
    'breadcrumb_minidumpers' => 'Mini Dumpers',

    // Machine Filter
    'filter_engine_power_label' => 'Engine Power',
    'filter_clear_button' => 'Clear Filters',
);

WP_CLI::line('');
WP_CLI::line('=== POPULATING THEME OPTION VALUES ===');
WP_CLI::line('');

$updated_count = 0;
$skipped_count = 0;

foreach ($field_defaults as $field_name => $default_value) {
    $option_name = 'cnf_theme_options_' . $field_name;

    // Check if option already exists
    $existing_value = get_option($option_name, false);

    if ($existing_value !== false && $existing_value !== '') {
        WP_CLI::line('⊘ ' . $field_name . ' (already has value, skipping)');
        $skipped_count++;
        continue;
    }

    // Set the option value
    $result = update_option($option_name, $default_value);

    if ($result) {
        WP_CLI::success('✓ Set: ' . $field_name . ' = "' . substr($default_value, 0, 50) . (strlen($default_value) > 50 ? '...' : '') . '"');
        $updated_count++;
    } else {
        WP_CLI::warning('✗ Failed to set: ' . $field_name);
    }
}

WP_CLI::line('');
WP_CLI::line('=== SUMMARY ===');
WP_CLI::line('');
WP_CLI::line('Values set: ' . $updated_count);
WP_CLI::line('Values skipped (already exist): ' . $skipped_count);
WP_CLI::line('Total processed: ' . count($field_defaults));
WP_CLI::line('');

// Verify total option count
$all_options = wp_list_pluck(
    wp_load_alloptions(),
    'option_value',
    'option_name'
);

$cnf_options = array_filter(
    $all_options,
    function($key) {
        return strpos($key, 'cnf_theme_options_') === 0;
    },
    ARRAY_FILTER_USE_KEY
);

WP_CLI::line('Total CNF theme options in database: ' . count($cnf_options));
WP_CLI::line('');

if ($updated_count > 0) {
    WP_CLI::success('Successfully populated ' . $updated_count . ' option values!');
    WP_CLI::line('');
    WP_CLI::line('Next: Clear WordPress cache and restart React dev server');
} else {
    WP_CLI::success('All option values already populated!');
}
