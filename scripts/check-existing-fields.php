<?php
/**
 * Check Existing Theme Option Fields
 *
 * Run this in WordPress to see which fields already exist
 * and which ones need to be added.
 *
 * Usage: Add to functions.php temporarily or run via wp-cli
 */

function cnf_check_existing_theme_option_fields() {
    if (!function_exists('pods')) {
        echo "Pods Framework not available\n";
        return;
    }

    // Get the theme options Pod
    $pod = pods('cnf_theme_options', 0);

    if (!$pod) {
        echo "Theme options Pod not found\n";
        return;
    }

    // Get all fields from the Pod
    $existing_fields = $pod->fields();

    echo "=== EXISTING THEME OPTION FIELDS ===\n\n";
    echo "Total fields: " . count($existing_fields) . "\n\n";

    $existing_field_names = array();
    foreach ($existing_fields as $field) {
        $existing_field_names[] = $field['name'];
        echo "✓ " . $field['name'] . " (" . $field['type'] . ")\n";
    }

    // List of all fields we want
    $required_fields = array(
        // Cookie Consent (21 fields)
        'cookie_title',
        'cookie_description',
        'cookie_tabs_settings',
        'cookie_tabs_information',
        'cookie_necessary_title',
        'cookie_necessary_description',
        'cookie_analytics_title',
        'cookie_analytics_description',
        'cookie_marketing_title',
        'cookie_marketing_description',
        'cookie_preferences_title',
        'cookie_preferences_description',
        'cookie_button_accept_all',
        'cookie_button_reject_all',
        'cookie_button_save_preferences',
        'cookie_button_manage_cookies',
        'cookie_privacy_policy_link',
        'cookie_privacy_policy_text',
        'cookie_about_title',
        'cookie_about_description',
        'cookie_privacy_link_text',

        // Homepage (10 fields)
        'home_hero_title',
        'home_hero_description',
        'home_hero_cta_quote',
        'home_hero_cta_brochure_text',
        'home_hero_cta_brochure_link',
        'home_hero_cta_phone',
        'home_about_title',
        'home_about_description',
        'home_features_title',
        'home_features_subtitle',

        // About Page (12 fields)
        'about_hero_label',
        'about_hero_title',
        'about_hero_description',
        'about_history_title',
        'about_history_content',
        'about_values_title',
        'about_values_content',
        'about_quality_title',
        'about_quality_content',
        'about_products_title',
        'about_products_content',
        'about_innovation_title',
        'about_innovation_content',

        // Contact Page (6 fields)
        'contact_meta_title',
        'contact_meta_description',
        'contact_meta_keywords',
        'contact_hero_label',
        'contact_hero_title',
        'contact_hero_description',

        // Dealers Page (11 fields)
        'dealers_meta_title',
        'dealers_meta_description',
        'dealers_meta_keywords',
        'dealers_hero_label',
        'dealers_hero_title',
        'dealers_hero_description',
        'dealers_become_title',
        'dealers_become_description',
        'dealers_become_phone_label',
        'dealers_become_phone_number',

        // Machines Page (11 fields)
        'machines_meta_title',
        'machines_meta_description',
        'machines_meta_keywords',
        'machines_hero_label',
        'machines_hero_title',
        'machines_hero_description',
        'machines_cta_title',
        'machines_cta_description',
        'machines_cta_button_call',
        'machines_cta_button_quote',

        // Section Labels (8 fields)
        'news_section_label',
        'news_section_title',
        'dealers_section_label',
        'dealers_section_title',
        'faqs_section_label',
        'faqs_section_title',
        'promotions_section_label',
        'promotions_section_title',

        // Contact Form (13 fields)
        'contact_form_label',
        'contact_form_title',
        'contact_form_description',
        'contact_form_field_name',
        'contact_form_field_email',
        'contact_form_field_phone',
        'contact_form_field_message',
        'contact_form_placeholder_name',
        'contact_form_placeholder_email',
        'contact_form_placeholder_phone',
        'contact_form_placeholder_message',
        'contact_form_submit',
        'contact_form_success',
        'contact_form_direct_title',

        // Quote Form (5 fields)
        'quote_form_label',
        'quote_form_title',
        'quote_form_description',
        'quote_form_submit',
        'quote_form_success',

        // Dealer Form (5 fields)
        'dealer_form_label',
        'dealer_form_title',
        'dealer_form_description',
        'dealer_form_submit',
        'dealer_form_success',

        // Breadcrumbs (7 fields)
        'breadcrumb_home',
        'breadcrumb_about',
        'breadcrumb_contact',
        'breadcrumb_dealers',
        'breadcrumb_minidumpers',
        'breadcrumb_news',
        'breadcrumb_faqs',

        // Header (1 field)
        'header_contact_button',

        // Footer (2 fields)
        'footer_copyright',
        'footer_show_words_logo',

        // Machine Filter (4 fields)
        'filter_load_capacity_label',
        'filter_track_width_label',
        'filter_engine_power_label',
        'filter_clear_button',
    );

    // Find missing fields
    $missing_fields = array_diff($required_fields, $existing_field_names);

    echo "\n\n=== MISSING FIELDS ===\n\n";
    if (empty($missing_fields)) {
        echo "✓ All fields already exist! No action needed.\n";
    } else {
        echo "Missing " . count($missing_fields) . " fields:\n\n";
        foreach ($missing_fields as $field) {
            echo "✗ " . $field . "\n";
        }
    }

    // Find extra fields (that exist but aren't in our list)
    $extra_fields = array_diff($existing_field_names, $required_fields);

    echo "\n\n=== EXTRA FIELDS (Not in Import) ===\n\n";
    if (empty($extra_fields)) {
        echo "✓ No extra fields.\n";
    } else {
        echo "Found " . count($extra_fields) . " fields not in import:\n\n";
        foreach ($extra_fields as $field) {
            echo "→ " . $field . " (will be preserved - don't worry!)\n";
        }
    }

    echo "\n\n=== SUMMARY ===\n\n";
    echo "Existing: " . count($existing_fields) . " fields\n";
    echo "Required: " . count($required_fields) . " fields\n";
    echo "Missing: " . count($missing_fields) . " fields\n";
    echo "Extra: " . count($extra_fields) . " fields\n";

    echo "\n\nSafe to import: " . (count($missing_fields) > 0 ? "YES - will add missing fields" : "NO - all fields already exist") . "\n";
}

// Run the check
add_action('init', function() {
    if (isset($_GET['check_theme_fields']) && current_user_can('manage_options')) {
        cnf_check_existing_theme_option_fields();
        die();
    }
});

// To use: Visit yoursite.com/?check_theme_fields=1
