<?php
/**
 * Pods Builder
 *
 * Programmatically creates Pods custom post types, taxonomies, and fields.
 *
 * @package CNF_Setup
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CNF Pods Builder Class
 *
 * Creates Pods from schema definitions using Pods API.
 */
class CNF_Pods_Builder {

    /**
     * Schema data
     *
     * @var array
     */
    private $schema;

    /**
     * Constructor
     *
     * @param array $schema Schema data
     */
    public function __construct($schema) {
        $this->schema = $schema;
    }

    /**
     * Create All Pods
     *
     * Creates all post types, taxonomies, and fields defined in schema.
     */
    public function create_all() {
        if (!function_exists('pods_api')) {
            error_log('CNF Setup: Pods API not available');
            return false;
        }

        $pods_definitions = isset($this->schema['pods']) ? $this->schema['pods'] : array();

        foreach ($pods_definitions as $pod_config) {
            $this->create_pod($pod_config);
        }

        // Flush rewrite rules after creating post types
        flush_rewrite_rules();

        return true;
    }

    /**
     * Create Single Pod
     *
     * Creates a post type, taxonomy, or pod with fields.
     *
     * @param array $config Pod configuration
     */
    private function create_pod($config) {
        $pods_api = pods_api();

        // Extract pod configuration
        $pod_name = isset($config['name']) ? $config['name'] : '';
        $pod_label = isset($config['label']) ? $config['label'] : '';
        $pod_type = isset($config['type']) ? $config['type'] : 'post_type';
        $pod_options = isset($config['options']) ? $config['options'] : array();
        $pod_fields = isset($config['fields']) ? $config['fields'] : array();

        if (empty($pod_name)) {
            error_log('CNF Setup: Pod name is empty, skipping');
            return false;
        }

        error_log("CNF Setup: Creating pod: {$pod_name} (type: {$pod_type})");

        // Check if pod already exists
        $existing_pod = $pods_api->load_pod(array('name' => $pod_name), false);

        if ($existing_pod) {
            error_log("CNF Setup: Pod {$pod_name} already exists, updating...");
            // Update existing pod
            $pod_id = $existing_pod['id'];
        } else {
            // Create new pod
            $pod_params = array(
                'name' => $pod_name,
                'label' => $pod_label,
                'type' => $pod_type,
                'storage' => 'meta',
            );

            // Merge with custom options
            $pod_params = array_merge($pod_params, $pod_options);

            try {
                $pod_id = $pods_api->save_pod($pod_params);
                error_log("CNF Setup: Created pod {$pod_name} with ID {$pod_id}");
            } catch (Exception $e) {
                error_log("CNF Setup: Failed to create pod {$pod_name}: " . $e->getMessage());
                return false;
            }
        }

        // Create fields
        if (!empty($pod_fields) && $pod_id) {
            $this->create_fields($pod_id, $pod_name, $pod_fields);
        }

        return $pod_id;
    }

    /**
     * Create Fields for Pod
     *
     * Creates all custom fields for a pod.
     *
     * @param int $pod_id Pod ID
     * @param string $pod_name Pod name
     * @param array $fields Field definitions
     */
    private function create_fields($pod_id, $pod_name, $fields) {
        $pods_api = pods_api();

        foreach ($fields as $field_config) {
            $field_name = isset($field_config['name']) ? $field_config['name'] : '';
            $field_label = isset($field_config['label']) ? $field_config['label'] : $field_name;
            $field_type = isset($field_config['type']) ? $field_config['type'] : 'text';
            $field_options = isset($field_config['options']) ? $field_config['options'] : array();
            $field_required = isset($field_config['required']) ? $field_config['required'] : false;

            if (empty($field_name)) {
                error_log('CNF Setup: Field name is empty, skipping');
                continue;
            }

            // Build field parameters
            $field_params = array(
                'pod_id' => $pod_id,
                'name' => $field_name,
                'label' => $field_label,
                'type' => $field_type,
                'required' => $field_required ? '1' : '0',
            );

            // Add field-specific options
            if (!empty($field_options)) {
                $field_params = array_merge($field_params, $field_options);
            }

            // Handle repeatable fields
            if (isset($field_config['repeatable']) && $field_config['repeatable']) {
                $field_params['repeatable'] = '1';
            }

            // Create or update field
            try {
                $field_id = $pods_api->save_field($field_params);
                error_log("CNF Setup: Created field {$field_name} in pod {$pod_name}");
            } catch (Exception $e) {
                error_log("CNF Setup: Failed to create field {$field_name}: " . $e->getMessage());
            }
        }
    }

    /**
     * Create Taxonomy
     *
     * Creates a custom taxonomy and associates it with post types.
     *
     * @param array $config Taxonomy configuration
     */
    private function create_taxonomy($config) {
        $tax_name = isset($config['name']) ? $config['name'] : '';
        $tax_label = isset($config['label']) ? $config['label'] : '';
        $post_types = isset($config['post_types']) ? $config['post_types'] : array();
        $hierarchical = isset($config['hierarchical']) ? $config['hierarchical'] : true;

        if (empty($tax_name)) {
            error_log('CNF Setup: Taxonomy name is empty, skipping');
            return false;
        }

        // Register taxonomy
        $args = array(
            'labels' => array(
                'name' => $tax_label,
                'singular_name' => $tax_label,
            ),
            'hierarchical' => $hierarchical,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'show_tagcloud' => true,
            'show_admin_column' => true,
        );

        register_taxonomy($tax_name, $post_types, $args);

        error_log("CNF Setup: Registered taxonomy {$tax_name}");

        return true;
    }
}
