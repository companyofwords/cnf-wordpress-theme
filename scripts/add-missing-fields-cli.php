<?php
/**
 * WP-CLI Command to Add Missing Theme Option Fields
 *
 * Usage: wp eval-file add-missing-fields-cli.php
 *
 * This script will:
 * 1. Check which fields are missing
 * 2. Add only the missing fields (won't touch existing ones)
 * 3. Set default values
 * 4. Preserve all existing field data
 */

if (!function_exists('pods')) {
    WP_CLI::error('Pods Framework not available');
    return;
}

// Field definitions with types and defaults
$field_definitions = array(
    // Cookie Consent
    array('name' => 'cookie_title', 'type' => 'text', 'label' => 'Cookie Title', 'default' => 'COOKIE NOTICE'),
    array('name' => 'cookie_description', 'type' => 'paragraph', 'label' => 'Cookie Description', 'default' => 'We use cookies to improve your experience.'),
    array('name' => 'cookie_tabs_settings', 'type' => 'text', 'label' => 'Cookie Tab: Settings', 'default' => 'Settings'),
    array('name' => 'cookie_tabs_information', 'type' => 'text', 'label' => 'Cookie Tab: Information', 'default' => 'Information'),
    array('name' => 'cookie_necessary_title', 'type' => 'text', 'label' => 'Necessary Cookies Title', 'default' => 'Necessary Cookies'),
    array('name' => 'cookie_necessary_description', 'type' => 'paragraph', 'label' => 'Necessary Cookies Description', 'default' => 'Essential for the website to function.'),
    array('name' => 'cookie_analytics_title', 'type' => 'text', 'label' => 'Analytics Cookies Title', 'default' => 'Analytics Cookies'),
    array('name' => 'cookie_analytics_description', 'type' => 'paragraph', 'label' => 'Analytics Cookies Description', 'default' => 'Help us improve our website.'),
    array('name' => 'cookie_marketing_title', 'type' => 'text', 'label' => 'Marketing Cookies Title', 'default' => 'Marketing Cookies'),
    array('name' => 'cookie_marketing_description', 'type' => 'paragraph', 'label' => 'Marketing Cookies Description', 'default' => 'Used to deliver personalized content.'),
    array('name' => 'cookie_preferences_title', 'type' => 'text', 'label' => 'Preference Cookies Title', 'default' => 'Preference Cookies'),
    array('name' => 'cookie_preferences_description', 'type' => 'paragraph', 'label' => 'Preference Cookies Description', 'default' => 'Remember your preferences.'),
    array('name' => 'cookie_button_accept_all', 'type' => 'text', 'label' => 'Button: Accept All', 'default' => 'Accept All'),
    array('name' => 'cookie_button_reject_all', 'type' => 'text', 'label' => 'Button: Reject All', 'default' => 'Reject All'),
    array('name' => 'cookie_button_save_preferences', 'type' => 'text', 'label' => 'Button: Save Preferences', 'default' => 'Save Preferences'),
    array('name' => 'cookie_button_manage_cookies', 'type' => 'text', 'label' => 'Button: Manage Cookies', 'default' => 'Manage Cookies'),
    array('name' => 'cookie_privacy_policy_link', 'type' => 'text', 'label' => 'Privacy Policy Link', 'default' => '/privacy-policy'),
    array('name' => 'cookie_privacy_policy_text', 'type' => 'text', 'label' => 'Privacy Policy Text', 'default' => 'Privacy Policy'),
    array('name' => 'cookie_about_title', 'type' => 'text', 'label' => 'About Cookies Title', 'default' => 'What Are Cookies?'),
    array('name' => 'cookie_about_description', 'type' => 'paragraph', 'label' => 'About Cookies Description', 'default' => 'Cookies are small text files stored on your device.'),
    array('name' => 'cookie_privacy_link_text', 'type' => 'text', 'label' => 'Privacy Link Text', 'default' => 'Read our Privacy Policy'),

    // Homepage
    array('name' => 'home_hero_title', 'type' => 'text', 'label' => 'Hero Title', 'default' => 'Premium Italian Mini Dumpers'),
    array('name' => 'home_hero_description', 'type' => 'paragraph', 'label' => 'Hero Description', 'default' => 'Professional tracked mini dumpers for construction and landscaping.'),
    array('name' => 'home_hero_cta_quote', 'type' => 'text', 'label' => 'Hero CTA: Quote Button', 'default' => 'Request a Quote'),
    array('name' => 'home_hero_cta_brochure_text', 'type' => 'text', 'label' => 'Hero CTA: Brochure Text', 'default' => 'Download Brochure'),
    array('name' => 'home_hero_cta_brochure_link', 'type' => 'text', 'label' => 'Hero CTA: Brochure Link', 'default' => '/brochure.pdf'),
    array('name' => 'home_hero_cta_phone', 'type' => 'text', 'label' => 'Hero CTA: Phone Button', 'default' => 'Call Us'),
    array('name' => 'home_about_title', 'type' => 'text', 'label' => 'About Section Title', 'default' => 'About Us'),
    array('name' => 'home_about_description', 'type' => 'wysiwyg', 'label' => 'About Section Description', 'default' => 'Quality mini dumpers from Italy.'),
    array('name' => 'home_features_title', 'type' => 'text', 'label' => 'Features Section Title', 'default' => 'Premium Features'),
    array('name' => 'home_features_subtitle', 'type' => 'text', 'label' => 'Features Section Subtitle', 'default' => 'Built for Performance'),

    // About Page
    array('name' => 'about_hero_label', 'type' => 'text', 'label' => 'Hero Label', 'default' => 'About'),
    array('name' => 'about_hero_title', 'type' => 'text', 'label' => 'Hero Title', 'default' => 'About CNF Mini Dumpers'),
    array('name' => 'about_hero_description', 'type' => 'paragraph', 'label' => 'Hero Description', 'default' => 'Your trusted partner for premium equipment.'),
    array('name' => 'about_history_title', 'type' => 'text', 'label' => 'History Section Title', 'default' => 'Our Story'),
    array('name' => 'about_history_content', 'type' => 'wysiwyg', 'label' => 'History Section Content', 'default' => ''),
    array('name' => 'about_values_title', 'type' => 'text', 'label' => 'Values Section Title', 'default' => 'Our Values'),
    array('name' => 'about_values_content', 'type' => 'wysiwyg', 'label' => 'Values Section Content', 'default' => ''),
    array('name' => 'about_quality_title', 'type' => 'text', 'label' => 'Quality Section Title', 'default' => 'Quality Promise'),
    array('name' => 'about_quality_content', 'type' => 'wysiwyg', 'label' => 'Quality Section Content', 'default' => ''),
    array('name' => 'about_products_title', 'type' => 'text', 'label' => 'Products Section Title', 'default' => 'Our Products'),
    array('name' => 'about_products_content', 'type' => 'wysiwyg', 'label' => 'Products Section Content', 'default' => ''),
    array('name' => 'about_innovation_title', 'type' => 'text', 'label' => 'Innovation Section Title', 'default' => 'Innovation'),
    array('name' => 'about_innovation_content', 'type' => 'wysiwyg', 'label' => 'Innovation Section Content', 'default' => ''),

    // Contact Page
    array('name' => 'contact_meta_title', 'type' => 'text', 'label' => 'Meta Title', 'default' => 'Contact Us'),
    array('name' => 'contact_meta_description', 'type' => 'paragraph', 'label' => 'Meta Description', 'default' => 'Get in touch with our team.'),
    array('name' => 'contact_meta_keywords', 'type' => 'text', 'label' => 'Meta Keywords', 'default' => ''),
    array('name' => 'contact_hero_label', 'type' => 'text', 'label' => 'Hero Label', 'default' => 'Contact'),
    array('name' => 'contact_hero_title', 'type' => 'text', 'label' => 'Hero Title', 'default' => 'Get In Touch'),
    array('name' => 'contact_hero_description', 'type' => 'paragraph', 'label' => 'Hero Description', 'default' => 'We\'re here to help.'),

    // Dealers Page
    array('name' => 'dealers_meta_title', 'type' => 'text', 'label' => 'Meta Title', 'default' => 'Find a Dealer'),
    array('name' => 'dealers_meta_description', 'type' => 'paragraph', 'label' => 'Meta Description', 'default' => 'Locate your nearest dealer.'),
    array('name' => 'dealers_meta_keywords', 'type' => 'text', 'label' => 'Meta Keywords', 'default' => ''),
    array('name' => 'dealers_hero_label', 'type' => 'text', 'label' => 'Hero Label', 'default' => 'Dealers'),
    array('name' => 'dealers_hero_title', 'type' => 'text', 'label' => 'Hero Title', 'default' => 'Find Your Local Dealer'),
    array('name' => 'dealers_hero_description', 'type' => 'paragraph', 'label' => 'Hero Description', 'default' => 'Authorized dealers across the UK.'),
    array('name' => 'dealers_become_title', 'type' => 'text', 'label' => 'Become Dealer Title', 'default' => 'Become a Dealer'),
    array('name' => 'dealers_become_description', 'type' => 'paragraph', 'label' => 'Become Dealer Description', 'default' => 'Join our dealer network.'),
    array('name' => 'dealers_become_phone_label', 'type' => 'text', 'label' => 'Become Dealer Phone Label', 'default' => 'Call us:'),
    array('name' => 'dealers_become_phone_number', 'type' => 'text', 'label' => 'Become Dealer Phone Number', 'default' => ''),

    // Machines Page
    array('name' => 'machines_meta_title', 'type' => 'text', 'label' => 'Meta Title', 'default' => 'Mini Dumpers'),
    array('name' => 'machines_meta_description', 'type' => 'paragraph', 'label' => 'Meta Description', 'default' => 'Professional tracked mini dumpers.'),
    array('name' => 'machines_meta_keywords', 'type' => 'text', 'label' => 'Meta Keywords', 'default' => ''),
    array('name' => 'machines_hero_label', 'type' => 'text', 'label' => 'Hero Label', 'default' => 'Our Machines'),
    array('name' => 'machines_hero_title', 'type' => 'text', 'label' => 'Hero Title', 'default' => 'Premium Mini Dumpers'),
    array('name' => 'machines_hero_description', 'type' => 'paragraph', 'label' => 'Hero Description', 'default' => 'Italian engineering excellence.'),
    array('name' => 'machines_cta_title', 'type' => 'text', 'label' => 'CTA Title', 'default' => 'Find Your Perfect Machine'),
    array('name' => 'machines_cta_description', 'type' => 'paragraph', 'label' => 'CTA Description', 'default' => 'Contact us for more information.'),
    array('name' => 'machines_cta_button_call', 'type' => 'text', 'label' => 'CTA Button: Call', 'default' => 'Call Us'),
    array('name' => 'machines_cta_button_quote', 'type' => 'text', 'label' => 'CTA Button: Quote', 'default' => 'Request Quote'),

    // Section Labels
    array('name' => 'news_section_label', 'type' => 'text', 'label' => 'News Section Label', 'default' => 'News'),
    array('name' => 'news_section_title', 'type' => 'text', 'label' => 'News Section Title', 'default' => 'Latest News'),
    array('name' => 'dealers_section_label', 'type' => 'text', 'label' => 'Dealers Section Label', 'default' => 'Dealers'),
    array('name' => 'dealers_section_title', 'type' => 'text', 'label' => 'Dealers Section Title', 'default' => 'Find a Dealer'),
    array('name' => 'faqs_section_label', 'type' => 'text', 'label' => 'FAQs Section Label', 'default' => 'FAQs'),
    array('name' => 'faqs_section_title', 'type' => 'text', 'label' => 'FAQs Section Title', 'default' => 'Frequently Asked Questions'),
    array('name' => 'promotions_section_label', 'type' => 'text', 'label' => 'Promotions Section Label', 'default' => 'Promotions'),
    array('name' => 'promotions_section_title', 'type' => 'text', 'label' => 'Promotions Section Title', 'default' => 'Special Offers'),

    // Contact Form
    array('name' => 'contact_form_label', 'type' => 'text', 'label' => 'Form Label', 'default' => 'Contact Form'),
    array('name' => 'contact_form_title', 'type' => 'text', 'label' => 'Form Title', 'default' => 'Send Us a Message'),
    array('name' => 'contact_form_description', 'type' => 'paragraph', 'label' => 'Form Description', 'default' => 'Fill out the form below.'),
    array('name' => 'contact_form_field_name', 'type' => 'text', 'label' => 'Field: Name', 'default' => 'Name'),
    array('name' => 'contact_form_field_email', 'type' => 'text', 'label' => 'Field: Email', 'default' => 'Email'),
    array('name' => 'contact_form_field_phone', 'type' => 'text', 'label' => 'Field: Phone', 'default' => 'Phone'),
    array('name' => 'contact_form_field_message', 'type' => 'text', 'label' => 'Field: Message', 'default' => 'Message'),
    array('name' => 'contact_form_placeholder_name', 'type' => 'text', 'label' => 'Placeholder: Name', 'default' => 'Your name'),
    array('name' => 'contact_form_placeholder_email', 'type' => 'text', 'label' => 'Placeholder: Email', 'default' => 'your@email.com'),
    array('name' => 'contact_form_placeholder_phone', 'type' => 'text', 'label' => 'Placeholder: Phone', 'default' => 'Your phone number'),
    array('name' => 'contact_form_placeholder_message', 'type' => 'text', 'label' => 'Placeholder: Message', 'default' => 'Your message'),
    array('name' => 'contact_form_submit', 'type' => 'text', 'label' => 'Submit Button Text', 'default' => 'Send Message'),
    array('name' => 'contact_form_success', 'type' => 'text', 'label' => 'Success Message', 'default' => 'Thank you! We\'ll be in touch soon.'),
    array('name' => 'contact_form_direct_title', 'type' => 'text', 'label' => 'Direct Contact Title', 'default' => 'Or contact us directly:'),

    // Quote Form
    array('name' => 'quote_form_label', 'type' => 'text', 'label' => 'Form Label', 'default' => 'Request Quote'),
    array('name' => 'quote_form_title', 'type' => 'text', 'label' => 'Form Title', 'default' => 'Get a Quote'),
    array('name' => 'quote_form_description', 'type' => 'paragraph', 'label' => 'Form Description', 'default' => 'Request pricing information.'),
    array('name' => 'quote_form_submit', 'type' => 'text', 'label' => 'Submit Button Text', 'default' => 'Request Quote'),
    array('name' => 'quote_form_success', 'type' => 'text', 'label' => 'Success Message', 'default' => 'Quote request submitted!'),

    // Dealer Form
    array('name' => 'dealer_form_label', 'type' => 'text', 'label' => 'Form Label', 'default' => 'Dealer Enquiry'),
    array('name' => 'dealer_form_title', 'type' => 'text', 'label' => 'Form Title', 'default' => 'Become a Dealer'),
    array('name' => 'dealer_form_description', 'type' => 'paragraph', 'label' => 'Form Description', 'default' => 'Join our dealer network.'),
    array('name' => 'dealer_form_submit', 'type' => 'text', 'label' => 'Submit Button Text', 'default' => 'Submit Enquiry'),
    array('name' => 'dealer_form_success', 'type' => 'text', 'label' => 'Success Message', 'default' => 'Enquiry submitted!'),

    // Breadcrumbs
    array('name' => 'breadcrumb_home', 'type' => 'text', 'label' => 'Home Breadcrumb', 'default' => 'Home'),
    array('name' => 'breadcrumb_about', 'type' => 'text', 'label' => 'About Breadcrumb', 'default' => 'About'),
    array('name' => 'breadcrumb_contact', 'type' => 'text', 'label' => 'Contact Breadcrumb', 'default' => 'Contact'),
    array('name' => 'breadcrumb_dealers', 'type' => 'text', 'label' => 'Dealers Breadcrumb', 'default' => 'Dealers'),
    array('name' => 'breadcrumb_minidumpers', 'type' => 'text', 'label' => 'Mini Dumpers Breadcrumb', 'default' => 'Mini Dumpers'),
    array('name' => 'breadcrumb_news', 'type' => 'text', 'label' => 'News Breadcrumb', 'default' => 'News'),
    array('name' => 'breadcrumb_faqs', 'type' => 'text', 'label' => 'FAQs Breadcrumb', 'default' => 'FAQs'),

    // Header
    array('name' => 'header_contact_button', 'type' => 'text', 'label' => 'Header Contact Button Text', 'default' => 'Contact Us'),

    // Footer
    array('name' => 'footer_copyright', 'type' => 'text', 'label' => 'Footer Copyright Text', 'default' => '© 2024 CNF Mini Dumpers'),
    array('name' => 'footer_show_words_logo', 'type' => 'boolean', 'label' => 'Show WORDS Logo in Footer', 'default' => '1'),

    // Machine Filter
    array('name' => 'filter_load_capacity_label', 'type' => 'text', 'label' => 'Load Capacity Label', 'default' => 'Load Capacity'),
    array('name' => 'filter_track_width_label', 'type' => 'text', 'label' => 'Track Width Label', 'default' => 'Track Width'),
    array('name' => 'filter_engine_power_label', 'type' => 'text', 'label' => 'Engine Power Label', 'default' => 'Engine Power'),
    array('name' => 'filter_clear_button', 'type' => 'text', 'label' => 'Clear Filters Button', 'default' => 'Clear Filters'),
);

// Get existing fields
$pod_api = pods_api();
$pod = $pod_api->load_pod(array('name' => 'cnf_theme_options'));

if (!$pod) {
    WP_CLI::error('Theme options Pod not found');
    return;
}

$existing_fields = $pod['fields'];
$existing_field_names = array_keys($existing_fields);

WP_CLI::line('');
WP_CLI::line('=== ADDING MISSING THEME OPTION FIELDS ===');
WP_CLI::line('');

$added_count = 0;
$skipped_count = 0;

foreach ($field_definitions as $field_def) {
    $field_name = $field_def['name'];

    if (in_array($field_name, $existing_field_names)) {
        WP_CLI::line('⊘ ' . $field_name . ' (already exists, skipping)');
        $skipped_count++;
        continue;
    }

    // Add the field
    $params = array(
        'pod' => 'cnf_theme_options',
        'name' => $field_def['name'],
        'label' => $field_def['label'],
        'type' => $field_def['type'],
    );

    // Add default value if provided
    if (!empty($field_def['default'])) {
        $params['default_value'] = $field_def['default'];
    }

    try {
        $result = $pod_api->add_field($params);

        if ($result) {
            WP_CLI::success('✓ Added: ' . $field_name);
            $added_count++;

            // Set the option value to the default
            if (!empty($field_def['default'])) {
                update_option('cnf_theme_options_' . $field_name, $field_def['default']);
            }
        } else {
            WP_CLI::warning('✗ Failed to add: ' . $field_name);
        }
    } catch (Exception $e) {
        WP_CLI::warning('✗ Error adding ' . $field_name . ': ' . $e->getMessage());
    }
}

WP_CLI::line('');
WP_CLI::line('=== SUMMARY ===');
WP_CLI::line('');
WP_CLI::line('Fields added: ' . $added_count);
WP_CLI::line('Fields skipped (already exist): ' . $skipped_count);
WP_CLI::line('Total fields processed: ' . count($field_definitions));
WP_CLI::line('');

if ($added_count > 0) {
    WP_CLI::success('Successfully added ' . $added_count . ' new fields!');
    WP_CLI::line('');
    WP_CLI::line('Next steps:');
    WP_CLI::line('1. Visit WordPress Admin → Settings → Theme Options');
    WP_CLI::line('2. Customize the content as needed');
    WP_CLI::line('3. Verify changes appear in the React app');
} else {
    WP_CLI::success('All fields already exist! Nothing to add.');
}
