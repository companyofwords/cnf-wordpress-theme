<?php
/**
 * Plugin Name: CNF Automated Setup
 * Plugin URI: https://cnfminidumper.co.uk
 * Description: Automated WordPress setup from TypeScript schema. Creates Pods, seeds content, uploads media, and customizes dashboard.
 * Version: 1.0.0
 * Author: Wordsco
 * Author URI: https://wordsco.uk
 * License: MIT
 * Text Domain: cnf-setup
 *
 * Must-Use Plugin (MU-Plugin)
 * This plugin automatically runs on every WordPress load.
 *
 * @package CNF_Setup
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CNF_SETUP_VERSION', '1.0.0');
define('CNF_SETUP_DIR', __DIR__);
define('CNF_SETUP_URL', plugins_url('', __FILE__));
define('CNF_SETUP_SCHEMA_FILE', CNF_SETUP_DIR . '/schema.json');

/**
 * CNF Setup Main Class
 *
 * Orchestrates the automated setup process.
 */
class CNF_Automated_Setup {

    /**
     * Singleton instance
     *
     * @var CNF_Automated_Setup
     */
    private static $instance = null;

    /**
     * Setup status option name
     *
     * @var string
     */
    private $setup_option = 'cnf_setup_completed';

    /**
     * Get singleton instance
     *
     * @return CNF_Automated_Setup
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // DISABLED: Automatic setup is disabled to prevent fatal errors
        // Setup must be triggered manually from Tools > CNF Setup

        // Add admin menu for manual setup/reset
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Add admin notices
        add_action('admin_notices', array($this, 'admin_notices'));
    }

    /**
     * Run Automated Setup
     *
     * Executes the complete setup process.
     */
    public function run_setup() {
        // Verify schema file exists
        if (!file_exists(CNF_SETUP_SCHEMA_FILE)) {
            $this->log_error('Schema file not found: ' . CNF_SETUP_SCHEMA_FILE);
            return false;
        }

        // Verify Pods is installed
        if (!function_exists('pods')) {
            $this->log_error('Pods Framework is not installed or activated.');
            return false;
        }

        try {
            $this->log('Starting CNF automated setup...');

            // Load includes
            $this->log('Loading include files...');
            $this->load_includes();
            $this->log('Include files loaded successfully');

            // Read schema
            $this->log('Reading schema file...');
            $schema = $this->read_schema();
            if (!$schema) {
                throw new Exception('Failed to read schema');
            }
            $this->log('Schema file read successfully');

            // Execute setup steps
            $this->log('Creating Pods...');
            $this->create_pods($schema);

            $this->log('Seeding content...');
            $this->seed_content($schema);

            $this->log('Uploading media...');
            $this->upload_media($schema);

            $this->log('Creating menus...');
            $this->create_menus($schema);

            $this->log('Customizing dashboard...');
            $this->customize_dashboard($schema);

            $this->log('Populating theme options...');
            $this->populate_theme_options($schema);

            // Mark setup as completed
            update_option($this->setup_option, time());

            $this->log('CNF automated setup completed successfully!');

            return true;

        } catch (Exception $e) {
            $this->log_error('Setup failed: ' . $e->getMessage());
            $this->log_error('Stack trace: ' . $e->getTraceAsString());
            return false;
        } catch (Error $e) {
            $this->log_error('PHP Error: ' . $e->getMessage());
            $this->log_error('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Load Include Files
     */
    private function load_includes() {
        require_once CNF_SETUP_DIR . '/includes/schema-reader.php';
        require_once CNF_SETUP_DIR . '/includes/pods-builder.php';
        require_once CNF_SETUP_DIR . '/includes/content-seeder.php';
        require_once CNF_SETUP_DIR . '/includes/media-uploader.php';
        require_once CNF_SETUP_DIR . '/includes/dashboard-customizer.php';
    }

    /**
     * Read Schema from JSON File
     *
     * @return array|false Schema data or false on failure
     */
    private function read_schema() {
        $schema_reader = new CNF_Schema_Reader(CNF_SETUP_SCHEMA_FILE);
        return $schema_reader->read();
    }

    /**
     * Create Pods from Schema
     *
     * @param array $schema Schema data
     */
    private function create_pods($schema) {
        $this->log('Creating Pods...');
        $pods_builder = new CNF_Pods_Builder($schema);
        $pods_builder->create_all();
        $this->log('Pods created successfully');
    }

    /**
     * Seed Content from Schema
     *
     * @param array $schema Schema data
     */
    private function seed_content($schema) {
        $this->log('Seeding content...');
        $content_seeder = new CNF_Content_Seeder($schema);
        $content_seeder->seed_all();
        $this->log('Content seeded successfully');
    }

    /**
     * Upload Media from Schema
     *
     * @param array $schema Schema data
     */
    private function upload_media($schema) {
        $this->log('Uploading media...');
        $media_uploader = new CNF_Media_Uploader($schema);
        $media_uploader->upload_all();
        $this->log('Media uploaded successfully');
    }

    /**
     * Create Menus from Schema
     *
     * @param array $schema Schema data
     */
    private function create_menus($schema) {
        $this->log('Creating navigation menus...');
        $content_seeder = new CNF_Content_Seeder($schema);
        $content_seeder->create_menus();
        $this->log('Menus created successfully');
    }

    /**
     * Customize Dashboard
     *
     * @param array $schema Schema data
     */
    private function customize_dashboard($schema) {
        $this->log('Customizing WordPress dashboard...');
        $dashboard_customizer = new CNF_Dashboard_Customizer($schema);
        $dashboard_customizer->apply_customizations();
        $this->log('Dashboard customized successfully');
    }

    /**
     * Populate Theme Options
     *
     * Populates the Theme Options settings page with default values
     *
     * @param array $schema Schema data
     */
    private function populate_theme_options($schema) {
        // Default values for Theme Options
        $theme_options = array(
            // TAB 1: Branding - Colors
            'brand_color' => '#ee2742',
            'primary_color' => '#d11f38',
            'primary_dark_color' => '#b81932',
            'charcoal_color' => '#212529',

            // TAB 1: Branding - Alt Texts
            'alt_logo' => 'CNF MiniDumper Logo',
            'alt_logo_white' => 'CNF Logo',
            'alt_mini_dumper_white' => 'Mini Dumper Icon',
            'alt_hero_image' => 'CNF Mini Dumpers',

            // TAB 2: Company Information
            'company_name' => 'CNF MiniDumper',
            'company_phone' => '0161 494 6000',
            'company_phone_link' => 'tel:01614946000',
            'company_email' => 'info@cnfminidumper.com',
            'company_address_street' => 'Bredbury Parkway',
            'company_address_city' => 'Bredbury',
            'company_address_county' => 'Stockport',
            'company_address_postcode' => 'SK6 2SN',
            'company_opening_hours_weekday' => 'Monday - Friday: 8am - 6pm',
            'company_opening_hours_saturday' => 'Saturday: 9am - 4pm',

            // TAB 3: Social Media
            'social_facebook' => 'https://facebook.com/cnfminidumpers',
            'social_twitter' => 'https://twitter.com/cnfminidumpers',
            'social_linkedin' => 'https://linkedin.com/company/cnf-minidumpers',
            'social_instagram' => 'https://instagram.com/cnfminidumpers',

            // TAB 4: Email Settings
            'email_from_name' => 'CNF Mini Dumpers',
            'email_from_address' => 'neil@wordsco.uk',
            'email_support' => 'neil@wordsco.uk',

            // TAB 5: UI Labels - Header
            'header_contact_button' => 'CONTACT US',
            'header_mobile_menu_title' => 'Navigation',
            'header_mobile_menu_screen_reader' => 'Open menu',

            // TAB 5: UI Labels - Hero
            'hero_quote_button' => 'GET A QUOTE',
            'hero_phone_button' => 'CALL 0161 494 6000',
            'hero_brochure_button' => 'VIEW BROCHURE',

            // TAB 5: UI Labels - Contact Form (Basic)
            'contact_form_title' => 'Get In Touch',
            'contact_form_description' => "Fill out the form and we'll get back to you within 24 hours",
            'contact_form_submit_button' => 'SEND MESSAGE',
            'contact_form_success' => 'Thank you! We will contact you shortly.',

            // TAB 5: UI Labels - Quote Form (Basic)
            'quote_form_title' => 'Request A Quote',
            'quote_form_description' => 'Get a personalized quote for your selected mini dumper',
            'quote_form_submit_button' => 'REQUEST QUOTE',
            'quote_form_success' => "Thank you! We'll send your quote within 24 hours.",

            // TAB 5: UI Labels - Sections
            'section_news_label' => 'LATEST NEWS',
            'section_news_title' => 'NEWS & UPDATES',
            'section_dealers_label' => 'FIND YOUR LOCAL DEALER',
            'section_dealers_title' => 'Authorised CNF Dealers',
            'section_faqs_label' => 'FAQS',
            'section_faqs_title' => 'FREQUENTLY ASKED QUESTIONS',
            'section_about_label' => 'ABOUT US',
            'section_about_title' => 'CNF MINI DUMPERS',
            'section_machine_features_label' => 'FEATURES',
            'section_machine_features_title' => 'INNOVATIVE DESIGN',
            'section_machine_features_description' => 'Every CNF mini dumper is engineered with advanced features that enhance safety, performance, and ease of use. From hydraulic controls to robust safety systems, our machines are built to handle the toughest jobs.',
            'section_promotions_label' => 'CURRENT PROMOTIONS',
            'section_promotions_title' => 'SPECIAL OFFERS',
            'section_machine_uses_title' => 'Ideal For',
            'section_machine_uses_description' => 'This machine is perfect for a variety of applications and use cases:',

            // TAB 5: UI Labels - Machine Filter (Basic)
            'machine_filter_title' => 'Choose The Perfect Machine',
            'machine_filter_description' => 'Filter by weight capacity, width, and features to find the ideal mini dumper for your project',
            'machine_no_results' => 'No machines match your selected needs. Try adjusting your filters.',

            // TAB 6: Breadcrumbs
            'breadcrumb_home' => 'Home',
            'breadcrumb_about' => 'About Us',
            'breadcrumb_dealers' => 'Dealers',
            'breadcrumb_mini_dumpers' => 'Mini Dumpers',
            'breadcrumb_contact' => 'Contact Us',
            'breadcrumb_uses' => 'Uses & Applications',
            'breadcrumb_faqs' => 'FAQs',
            'breadcrumb_news' => 'News',

            // TAB 7: Forms - Contact Form Extended
            'contact_form_label' => 'GET IN TOUCH',
            'contact_form_field_name' => 'Name *',
            'contact_form_field_email' => 'Email *',
            'contact_form_field_phone' => 'Phone *',
            'contact_form_field_message' => 'Message *',
            'contact_form_placeholder_name' => 'Your name',
            'contact_form_placeholder_email' => 'your@email.com',
            'contact_form_placeholder_phone' => '0161 2222222',
            'contact_form_placeholder_message' => 'Tell us about your project...',
            'contact_form_direct_contact_title' => 'OR CALL US DIRECTLY',

            // TAB 7: Forms - Quote Form Extended
            'quote_form_label' => 'REQUEST A QUOTE',
            'quote_form_field_name' => 'Name *',
            'quote_form_field_email' => 'Email *',
            'quote_form_field_phone' => 'Phone *',
            'quote_form_field_company' => 'Company',
            'quote_form_field_address' => 'Address *',
            'quote_form_field_postcode' => 'Postcode *',
            'quote_form_field_model' => 'Model *',
            'quote_form_field_finance_interest' => 'Interested in finance options?',
            'quote_form_field_message' => 'Message *',
            'quote_form_placeholder_name' => 'Your name',
            'quote_form_placeholder_email' => 'your@email.com',
            'quote_form_placeholder_phone' => '0161 2222222',
            'quote_form_placeholder_company' => 'Your company name',
            'quote_form_placeholder_address' => '123 High Street, Manchester',
            'quote_form_placeholder_postcode' => 'M1 1AE',
            'quote_form_placeholder_model' => 'Select a model',
            'quote_form_placeholder_message' => 'Tell us about your requirements...',
            'quote_form_direct_contact_title' => 'OR CALL US DIRECTLY',

            // TAB 7: Forms - Dealer Enquiry Form Extended
            'dealer_form_label' => 'DEALER ENQUIRY',
            'dealer_form_title' => 'Become A Dealer',
            'dealer_form_description' => 'Join our network of authorized CNF dealers across the UK',
            'dealer_form_field_name' => 'Name *',
            'dealer_form_field_email' => 'Email *',
            'dealer_form_field_phone' => 'Phone *',
            'dealer_form_field_company' => 'Company',
            'dealer_form_field_postcode' => 'Postcode',
            'dealer_form_field_message' => 'Message *',
            'dealer_form_placeholder_name' => 'Your name',
            'dealer_form_placeholder_email' => 'your@email.com',
            'dealer_form_placeholder_phone' => '0161 2222222',
            'dealer_form_placeholder_company' => 'Your company name',
            'dealer_form_placeholder_postcode' => 'e.g. SK6 2SN',
            'dealer_form_placeholder_message' => 'Tell us about your business and interest in becoming a dealer...',
            'dealer_form_submit_button' => 'SUBMIT ENQUIRY',
            'dealer_form_success' => "Thank you! We'll review your enquiry and be in touch soon.",
            'dealer_form_direct_contact_title' => 'OR CALL US DIRECTLY',

            // TAB 8: Machine Filter Extended
            'filter_load_capacity_label' => 'Load Capacity',
            'filter_load_capacity_min_label' => '500kg',
            'filter_load_capacity_max_label' => '1500kg',
            'filter_load_capacity_units' => 'kg',
            'filter_track_width_label' => 'Track Width',
            'filter_track_width_min_label' => '680mm',
            'filter_track_width_max_label' => '940mm',
            'filter_track_width_units' => 'mm',
            'filter_features_title' => 'Features & Actions',
            'filter_result_match_text' => 'match',
            'filter_result_machine_text' => 'machine',
            'filter_result_machines_text' => 'machines',
            'filter_result_match_needs_text' => 'your needs',
            'filter_card_view_details' => 'View Details',
            'filter_card_capacity_label' => 'Capacity:',
            'filter_card_width_label' => 'Width:',

            // TAB 9: Footer
            'footer_show_words_logo' => '1',  // Boolean stored as string '1' or '0'
            'footer_quick_links_title' => 'QUICK LINKS',
            'footer_our_machines_title' => 'OUR MACHINES',
            'footer_contact_info_title' => 'CONTACT INFO',
            'footer_phone_subtext' => 'Mon-Fri 8am-6pm',
            'footer_copyright' => '© 2024 CNF Mini Dumpers. All rights reserved.',

            // TAB 10: Cookie Consent
            'cookie_title' => 'COOKIE NOTICE',
            'cookie_description' => 'We use cookies to enhance your browsing experience and analyse our traffic. By clicking "Accept", you consent to our use of cookies.',
            'cookie_accept_button' => 'ACCEPT',
            'cookie_decline_button' => 'Decline',
            'cookie_preferences_title' => 'Cookie Preferences',
            'cookie_preferences_description' => 'We use cookies to enhance your browsing experience, serve personalised content, and analyse our traffic. You can customise your cookie preferences below.',
            'cookie_tabs_settings' => 'Settings',
            'cookie_tabs_information' => 'Information',
            'cookie_category_necessary_title' => 'Necessary Cookies',
            'cookie_category_necessary_description' => 'These cookies are essential for the website to function properly. They enable basic features like page navigation and access to secure areas.',
            'cookie_category_analytics_title' => 'Analytics Cookies',
            'cookie_category_analytics_description' => 'These cookies help us understand how visitors interact with our website by collecting and reporting information anonymously.',
            'cookie_category_marketing_title' => 'Marketing Cookies',
            'cookie_category_marketing_description' => 'These cookies are used to track visitors across websites to display relevant advertisements and marketing campaigns.',
            'cookie_category_preferences_title' => 'Preference Cookies',
            'cookie_category_preferences_description' => 'These cookies allow the website to remember choices you make and provide enhanced, personalized features.',
            'cookie_info_about_title' => 'About Cookies',
            'cookie_info_about_description' => 'Cookies are small text files that are placed on your device to help the website provide a better user experience. They are widely used to make websites work more efficiently and provide information to site owners.',
            'cookie_info_privacy_link' => 'Read our Privacy Policy',
            'cookie_button_accept_all' => 'Accept All',
            'cookie_button_reject_all' => 'Reject All',
            'cookie_button_save_preferences' => 'Save Preferences',
        );

        // Save each option
        foreach ($theme_options as $key => $value) {
            // Check if option already exists
            $existing = get_option('cnf_theme_options_' . $key);
            if ($existing === false) {
                // Option doesn't exist, create it
                update_option('cnf_theme_options_' . $key, $value);
                error_log("CNF Setup: Set theme option '{$key}' to '{$value}'");
            }
        }

        $this->log('Theme options populated successfully');
    }

    /**
     * Add Admin Menu
     */
    public function add_admin_menu() {
        add_management_page(
            'CNF Setup',
            'CNF Setup',
            'manage_options',
            'cnf-setup',
            array($this, 'admin_page')
        );
    }

    /**
     * Admin Page
     */
    public function admin_page() {
        $setup_completed = get_option($this->setup_option, false);
        $schema_exists = file_exists(CNF_SETUP_SCHEMA_FILE);
        $pods_active = function_exists('pods');

        ?>
        <div class="wrap">
            <h1>CNF Automated Setup</h1>

            <div class="card">
                <h2>Setup Status</h2>
                <table class="widefat">
                    <tr>
                        <td><strong>Setup Completed:</strong></td>
                        <td>
                            <?php if ($setup_completed): ?>
                                <span style="color: green;">✓ Yes</span>
                                (<?php echo date('Y-m-d H:i:s', $setup_completed); ?>)
                            <?php else: ?>
                                <span style="color: orange;">⚠ Not yet</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Schema File:</strong></td>
                        <td>
                            <?php if ($schema_exists): ?>
                                <span style="color: green;">✓ Found</span>
                                <code><?php echo CNF_SETUP_SCHEMA_FILE; ?></code>
                            <?php else: ?>
                                <span style="color: red;">✗ Not found</span>
                                <code><?php echo CNF_SETUP_SCHEMA_FILE; ?></code>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Pods Framework:</strong></td>
                        <td>
                            <?php if ($pods_active): ?>
                                <span style="color: green;">✓ Active</span>
                            <?php else: ?>
                                <span style="color: red;">✗ Not active</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <?php if ($setup_completed): ?>
                <div class="card">
                    <h2>Reset Setup</h2>
                    <p>Reset the setup to run it again. <strong>Warning:</strong> This will not delete existing content, but will attempt to recreate Pods and settings.</p>
                    <form method="post">
                        <?php wp_nonce_field('cnf_reset_setup', 'cnf_setup_nonce'); ?>
                        <input type="hidden" name="cnf_action" value="reset">
                        <button type="submit" class="button button-secondary">Reset Setup</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="card">
                    <h2>Run Setup</h2>
                    <?php if (!$schema_exists): ?>
                        <p style="color: red;"><strong>Error:</strong> Schema file not found. Please compile your schema:</p>
                        <pre>npm run build:schema</pre>
                    <?php elseif (!$pods_active): ?>
                        <p style="color: red;"><strong>Error:</strong> Pods Framework is not active. Please install and activate Pods.</p>
                    <?php else: ?>
                        <p>Click the button below to run the automated setup process.</p>
                        <form method="post">
                            <?php wp_nonce_field('cnf_run_setup', 'cnf_setup_nonce'); ?>
                            <input type="hidden" name="cnf_action" value="run">
                            <button type="submit" class="button button-primary">Run Setup Now</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <h2>Setup Log</h2>
                <pre style="background: #f0f0f0; padding: 15px; overflow: auto; max-height: 400px;"><?php
                    $log_file = CNF_SETUP_DIR . '/setup.log';
                    if (file_exists($log_file)) {
                        echo esc_html(file_get_contents($log_file));
                    } else {
                        echo 'No log file found.';
                    }
                ?></pre>
            </div>
        </div>
        <?php

        // Handle form submissions
        if (isset($_POST['cnf_action'])) {
            if ($_POST['cnf_action'] === 'reset' && wp_verify_nonce($_POST['cnf_setup_nonce'], 'cnf_reset_setup')) {
                delete_option($this->setup_option);
                echo '<div class="notice notice-success"><p>Setup reset successfully. Refresh the page to run setup again.</p></div>';
            } elseif ($_POST['cnf_action'] === 'run' && wp_verify_nonce($_POST['cnf_setup_nonce'], 'cnf_run_setup')) {
                $result = $this->run_setup();
                if ($result) {
                    echo '<div class="notice notice-success"><p>Setup completed successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Setup failed. Check the log for details.</p></div>';
                }
            }
        }
    }

    /**
     * Admin Notices
     */
    public function admin_notices() {
        $setup_completed = get_option($this->setup_option, false);

        if (!$setup_completed) {
            $schema_exists = file_exists(CNF_SETUP_SCHEMA_FILE);
            $pods_active = function_exists('pods');

            if (!$schema_exists || !$pods_active) {
                ?>
                <div class="notice notice-warning">
                    <p><strong>CNF Setup:</strong> Automated setup has not been completed yet.</p>
                    <?php if (!$schema_exists): ?>
                        <p>Schema file not found. Run: <code>npm run build:schema</code></p>
                    <?php endif; ?>
                    <?php if (!$pods_active): ?>
                        <p>Pods Framework is not active. Please activate it.</p>
                    <?php endif; ?>
                    <p><a href="<?php echo admin_url('tools.php?page=cnf-setup'); ?>">Go to CNF Setup</a></p>
                </div>
                <?php
            }
        }
    }

    /**
     * Log Message
     *
     * @param string $message Log message
     */
    private function log($message) {
        $log_file = CNF_SETUP_DIR . '/setup.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] {$message}\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);

        // Also log to error_log for debugging
        error_log('CNF Setup: ' . $message);
    }

    /**
     * Log Error
     *
     * @param string $message Error message
     */
    private function log_error($message) {
        $this->log('ERROR: ' . $message);
    }
}

// Initialize plugin
CNF_Automated_Setup::get_instance();
