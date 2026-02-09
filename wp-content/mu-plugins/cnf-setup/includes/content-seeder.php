<?php
/**
 * Content Seeder
 *
 * Seeds WordPress with content from schema definitions.
 *
 * @package CNF_Setup
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CNF Content Seeder Class
 *
 * Creates posts, pages, and navigation menus from schema.
 */
class CNF_Content_Seeder {

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
     * Seed All Content
     *
     * Creates all content items defined in schema.
     */
    public function seed_all() {
        $content_items = isset($this->schema['content']) ? $this->schema['content'] : array();

        foreach ($content_items as $content) {
            $this->create_content_item($content);
        }

        return true;
    }

    /**
     * Create Single Content Item
     *
     * Creates a post or page with Pods fields.
     *
     * @param array $content Content configuration
     */
    private function create_content_item($content) {
        $post_type = isset($content['post_type']) ? $content['post_type'] : 'post';
        $title = isset($content['title']) ? $content['title'] : '';
        $post_content = isset($content['content']) ? $content['content'] : '';
        $slug = isset($content['slug']) ? $content['slug'] : '';
        $status = isset($content['status']) ? $content['status'] : 'publish';
        $pods_fields = isset($content['fields']) ? $content['fields'] : array();
        $featured_image = isset($content['featured_image']) ? $content['featured_image'] : '';

        if (empty($title)) {
            error_log('CNF Setup: Content title is empty, skipping');
            return false;
        }

        // Check if content already exists (by slug)
        if (!empty($slug)) {
            $existing_post = get_page_by_path($slug, OBJECT, $post_type);
            if ($existing_post) {
                error_log("CNF Setup: Content '{$title}' already exists (slug: {$slug}), skipping");
                return $existing_post->ID;
            }
        }

        // Create post/page
        $post_data = array(
            'post_title' => $title,
            'post_content' => $post_content,
            'post_name' => $slug,
            'post_status' => $status,
            'post_type' => $post_type,
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            error_log("CNF Setup: Failed to create content '{$title}': " . $post_id->get_error_message());
            return false;
        }

        error_log("CNF Setup: Created {$post_type} '{$title}' with ID {$post_id}");

        // Set Pods fields
        if (!empty($pods_fields) && function_exists('pods')) {
            $this->set_pods_fields($post_id, $post_type, $pods_fields);
        }

        // Set featured image
        if (!empty($featured_image)) {
            $this->set_featured_image($post_id, $featured_image);
        }

        return $post_id;
    }

    /**
     * Set Pods Fields for Content
     *
     * @param int $post_id Post ID
     * @param string $post_type Post type
     * @param array $fields Fields data
     */
    private function set_pods_fields($post_id, $post_type, $fields) {
        $pod = pods($post_type, $post_id);

        if (!$pod || !$pod->exists()) {
            error_log("CNF Setup: Failed to load pod for post {$post_id}");
            return false;
        }

        foreach ($fields as $field_name => $field_value) {
            try {
                $pod->save($field_name, $field_value);
                error_log("CNF Setup: Set field '{$field_name}' for post {$post_id}");
            } catch (Exception $e) {
                error_log("CNF Setup: Failed to set field '{$field_name}': " . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * Set Featured Image
     *
     * @param int $post_id Post ID
     * @param string $image_filename Image filename
     */
    private function set_featured_image($post_id, $image_filename) {
        // Find image in media library by filename
        $args = array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => '_wp_attached_file',
                    'value' => $image_filename,
                    'compare' => 'LIKE',
                ),
            ),
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $attachment_id = $query->posts[0]->ID;
            set_post_thumbnail($post_id, $attachment_id);
            error_log("CNF Setup: Set featured image for post {$post_id}");
        } else {
            error_log("CNF Setup: Featured image '{$image_filename}' not found in media library");
        }
    }

    /**
     * Create Navigation Menus
     *
     * Creates WordPress navigation menus from schema.
     */
    public function create_menus() {
        $menus = isset($this->schema['menus']) ? $this->schema['menus'] : array();

        foreach ($menus as $menu_config) {
            $this->create_menu($menu_config);
        }

        return true;
    }

    /**
     * Create Single Menu
     *
     * @param array $config Menu configuration
     */
    private function create_menu($config) {
        $menu_name = isset($config['name']) ? $config['name'] : '';
        $menu_location = isset($config['location']) ? $config['location'] : '';
        $menu_items = isset($config['items']) ? $config['items'] : array();

        if (empty($menu_name)) {
            error_log('CNF Setup: Menu name is empty, skipping');
            return false;
        }

        // Check if menu already exists
        $existing_menu = wp_get_nav_menu_object($menu_name);

        if ($existing_menu) {
            $menu_id = $existing_menu->term_id;
            error_log("CNF Setup: Menu '{$menu_name}' already exists");
        } else {
            // Create menu
            $menu_id = wp_create_nav_menu($menu_name);

            if (is_wp_error($menu_id)) {
                error_log("CNF Setup: Failed to create menu '{$menu_name}': " . $menu_id->get_error_message());
                return false;
            }

            error_log("CNF Setup: Created menu '{$menu_name}' with ID {$menu_id}");
        }

        // Add menu items
        foreach ($menu_items as $item) {
            $this->add_menu_item($menu_id, $item);
        }

        // Set menu location
        if (!empty($menu_location)) {
            $locations = get_theme_mod('nav_menu_locations');
            $locations[$menu_location] = $menu_id;
            set_theme_mod('nav_menu_locations', $locations);
            error_log("CNF Setup: Assigned menu '{$menu_name}' to location '{$menu_location}'");
        }

        return $menu_id;
    }

    /**
     * Add Menu Item
     *
     * @param int $menu_id Menu ID
     * @param array $item Menu item configuration
     * @param int $parent_id Parent menu item ID (for sub-items)
     */
    private function add_menu_item($menu_id, $item, $parent_id = 0) {
        $item_title = isset($item['title']) ? $item['title'] : '';
        $item_url = isset($item['url']) ? $item['url'] : '';
        $item_type = isset($item['type']) ? $item['type'] : 'custom';
        $item_object_id = isset($item['object_id']) ? $item['object_id'] : 0;
        $item_children = isset($item['children']) ? $item['children'] : array();

        if (empty($item_title)) {
            error_log('CNF Setup: Menu item title is empty, skipping');
            return false;
        }

        $menu_item_data = array(
            'menu-item-title' => $item_title,
            'menu-item-url' => $item_url,
            'menu-item-status' => 'publish',
            'menu-item-type' => $item_type,
            'menu-item-parent-id' => $parent_id,
        );

        if ($item_type !== 'custom' && !empty($item_object_id)) {
            $menu_item_data['menu-item-object-id'] = $item_object_id;
            $menu_item_data['menu-item-object'] = $item_type;
        }

        $menu_item_id = wp_update_nav_menu_item($menu_id, 0, $menu_item_data);

        if (is_wp_error($menu_item_id)) {
            error_log("CNF Setup: Failed to add menu item '{$item_title}': " . $menu_item_id->get_error_message());
            return false;
        }

        error_log("CNF Setup: Added menu item '{$item_title}' with ID {$menu_item_id}");

        // Add child items (submenu)
        if (!empty($item_children)) {
            foreach ($item_children as $child) {
                $this->add_menu_item($menu_id, $child, $menu_item_id);
            }
        }

        return $menu_item_id;
    }
}
