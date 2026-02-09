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
        // Check if setup has already been completed
        $setup_completed = get_option($this->setup_option, false);

        if (!$setup_completed) {
            // Run setup on admin_init (after plugins are loaded)
            add_action('admin_init', array($this, 'run_setup'), 5);
        }

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
            $this->load_includes();

            // Read schema
            $schema = $this->read_schema();
            if (!$schema) {
                throw new Exception('Failed to read schema');
            }

            // Execute setup steps
            $this->create_pods($schema);
            $this->seed_content($schema);
            $this->upload_media($schema);
            $this->create_menus($schema);
            $this->customize_dashboard($schema);

            // Mark setup as completed
            update_option($this->setup_option, time());

            $this->log('CNF automated setup completed successfully!');

            return true;

        } catch (Exception $e) {
            $this->log_error('Setup failed: ' . $e->getMessage());
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
