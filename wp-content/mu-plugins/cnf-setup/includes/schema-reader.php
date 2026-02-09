<?php
/**
 * Schema Reader
 *
 * Reads and validates the compiled schema.json file.
 *
 * @package CNF_Setup
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CNF Schema Reader Class
 *
 * Reads schema.json (compiled from wp-schema.ts) and validates structure.
 */
class CNF_Schema_Reader {

    /**
     * Schema file path
     *
     * @var string
     */
    private $schema_file;

    /**
     * Schema data
     *
     * @var array
     */
    private $schema;

    /**
     * Constructor
     *
     * @param string $schema_file Path to schema.json file
     */
    public function __construct($schema_file) {
        $this->schema_file = $schema_file;
    }

    /**
     * Read Schema File
     *
     * @return array|false Schema data or false on failure
     */
    public function read() {
        if (!file_exists($this->schema_file)) {
            error_log('CNF Setup: Schema file not found: ' . $this->schema_file);
            return false;
        }

        $json_content = file_get_contents($this->schema_file);
        if ($json_content === false) {
            error_log('CNF Setup: Failed to read schema file');
            return false;
        }

        $schema = json_decode($json_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('CNF Setup: Invalid JSON in schema file: ' . json_last_error_msg());
            return false;
        }

        // Validate schema structure
        if (!$this->validate_schema($schema)) {
            error_log('CNF Setup: Invalid schema structure');
            return false;
        }

        $this->schema = $schema;
        return $schema;
    }

    /**
     * Validate Schema Structure
     *
     * Ensures schema has required sections.
     *
     * @param array $schema Schema data
     * @return bool True if valid, false otherwise
     */
    private function validate_schema($schema) {
        $required_sections = array('pods', 'menus', 'siteSettings');

        foreach ($required_sections as $section) {
            if (!isset($schema[$section])) {
                error_log("CNF Setup: Missing required schema section: {$section}");
                return false;
            }
        }

        return true;
    }

    /**
     * Get Pods Definitions
     *
     * @return array Pod definitions
     */
    public function get_pods() {
        return isset($this->schema['pods']) ? $this->schema['pods'] : array();
    }

    /**
     * Get Content Items
     *
     * @return array Content items
     */
    public function get_content() {
        return isset($this->schema['sampleContent']) ? $this->schema['sampleContent'] : array();
    }

    /**
     * Get Media Items
     *
     * @return array Media items
     */
    public function get_media() {
        return isset($this->schema['mediaLibrary']) ? $this->schema['mediaLibrary'] : array();
    }

    /**
     * Get Menu Definitions
     *
     * @return array Menu definitions
     */
    public function get_menus() {
        return isset($this->schema['menus']) ? $this->schema['menus'] : array();
    }

    /**
     * Get Site Settings
     *
     * @return array Site settings
     */
    public function get_site_settings() {
        return isset($this->schema['siteSettings']) ? $this->schema['siteSettings'] : array();
    }

    /**
     * Get Dashboard Customization
     *
     * @return array Dashboard customization options
     */
    public function get_dashboard_customization() {
        return isset($this->schema['dashboardCustomization']) ? $this->schema['dashboardCustomization'] : array();
    }
}
