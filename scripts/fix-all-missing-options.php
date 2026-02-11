<?php
/**
 * WP-CLI Command to Fix ALL Missing Theme Options
 *
 * Usage: wp eval-file fix-all-missing-options.php
 *
 * This ensures EVERY expected field has a value
 */

$all_field_defaults = array(
    // Cookie Consent (complete set)
    'cookie_title' => 'COOKIE NOTICE',
    'cookie_description' => 'We use cookies to improve your experience.',
    'cookie_tabs_settings' => 'Settings',
    'cookie_tabs_information' => 'Information',
    'cookie_necessary_title' => 'Necessary Cookies',
    'cookie_necessary_description' => 'Essential for the website to function.',
    'cookie_analytics_title' => 'Analytics Cookies',
    'cookie_analytics_description' => 'Help us improve our website.',
    'cookie_marketing_title' => 'Marketing Cookies',
    'cookie_marketing_description' => 'Used to deliver personalized content.',
    'cookie_preferences_title' => 'Preference Cookies',
    'cookie_preferences_description' => 'Remember your preferences.',
    'cookie_button_accept_all' => 'Accept All',
    'cookie_button_reject_all' => 'Reject All',
    'cookie_button_save_preferences' => 'Save Preferences',
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

    // Contact Form
    'contact_form_label' => 'Contact Form',
    'contact_form_title' => 'Send Us a Message',
    'contact_form_description' => 'Fill out the form below.',
    'contact_form_field_name' => 'Name',
    'contact_form_field_email' => 'Email',
    'contact_form_field_phone' => 'Phone',
    'contact_form_field_message' => 'Message',
    'contact_form_placeholder_name' => 'Your name',
    'contact_form_placeholder_email' => 'your@email.com',
    'contact_form_placeholder_phone' => 'Your phone number',
    'contact_form_placeholder_message' => 'Your message',
    'contact_form_submit' => 'Send Message',
    'contact_form_success' => 'Thank you! We\'ll be in touch soon.',
    'contact_form_direct_title' => 'Or contact us directly:',

    // Quote Form
    'quote_form_label' => 'Request Quote',
    'quote_form_title' => 'Get a Quote',
    'quote_form_description' => 'Request pricing information.',
    'quote_form_submit' => 'Request Quote',
    'quote_form_success' => 'Quote request submitted!',

    // Dealer Form
    'dealer_form_label' => 'Dealer Enquiry',
    'dealer_form_title' => 'Become a Dealer',
    'dealer_form_description' => 'Join our dealer network.',
    'dealer_form_submit' => 'Submit Enquiry',
    'dealer_form_success' => 'Enquiry submitted!',

    // Breadcrumbs
    'breadcrumb_home' => 'Home',
    'breadcrumb_about' => 'About',
    'breadcrumb_contact' => 'Contact',
    'breadcrumb_dealers' => 'Dealers',
    'breadcrumb_minidumpers' => 'Mini Dumpers',
    'breadcrumb_news' => 'News',
    'breadcrumb_faqs' => 'FAQs',

    // Header
    'header_contact_button' => 'Contact Us',

    // Footer
    'footer_copyright' => '© ' . date('Y') . ' CNF Mini Dumpers',
    'footer_show_words_logo' => '1',

    // Machine Filter (CRITICAL - fixes loadCapacity error)
    'filter_load_capacity_label' => 'Load Capacity',
    'filter_track_width_label' => 'Track Width',
    'filter_engine_power_label' => 'Engine Power',
    'filter_clear_button' => 'Clear Filters',
);

WP_CLI::line('');
WP_CLI::line('=== FIXING ALL MISSING THEME OPTIONS ===');
WP_CLI::line('');

$set_count = 0;
$already_set_count = 0;

foreach ($all_field_defaults as $field_name => $default_value) {
    $option_name = 'cnf_theme_options_' . $field_name;

    // Get existing value
    $existing_value = get_option($option_name);

    // Only set if it doesn't exist or is empty
    if ($existing_value === false || $existing_value === '') {
        update_option($option_name, $default_value);
        WP_CLI::success('✓ Set: ' . $field_name);
        $set_count++;
    } else {
        WP_CLI::line('⊘ Skip: ' . $field_name . ' (already set)');
        $already_set_count++;
    }
}

WP_CLI::line('');
WP_CLI::line('=== VERIFICATION ===');
WP_CLI::line('');

// Count all CNF theme options
$count = 0;
$all_options = wp_load_alloptions();
foreach ($all_options as $key => $value) {
    if (strpos($key, 'cnf_theme_options_') === 0) {
        $count++;
    }
}

WP_CLI::line('Total CNF theme options in database: ' . $count);
WP_CLI::line('');

WP_CLI::line('=== SUMMARY ===');
WP_CLI::line('');
WP_CLI::line('Options set: ' . $set_count);
WP_CLI::line('Options already set: ' . $already_set_count);
WP_CLI::line('Total in database: ' . $count);
WP_CLI::line('');

if ($set_count > 0) {
    WP_CLI::success('Fixed ' . $set_count . ' missing options!');
    WP_CLI::line('');
    WP_CLI::line('CRITICAL: Now run these commands:');
    WP_CLI::line('1. wp cache flush');
    WP_CLI::line('2. Restart React dev server (Ctrl+C, then pnpm dev)');
} else {
    WP_CLI::success('All options already set!');
}
