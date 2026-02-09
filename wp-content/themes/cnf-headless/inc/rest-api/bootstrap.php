<?php
/**
 * Bootstrap REST API Endpoint
 * Returns all site data in one request - matches full-wp.ts structure
 *
 * @package CNF_Headless
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Bootstrap REST API Endpoint
 *
 * Endpoint: /wp-json/app/v1/bootstrap
 * Returns: All site data in one optimized request
 */
add_action('rest_api_init', function () {
    register_rest_route('app/v1', '/bootstrap', array(
        'methods' => 'GET',
        'callback' => 'cnf_get_bootstrap_data',
        'permission_callback' => '__return_true', // Public endpoint
    ));
});

/**
 * Main Bootstrap Data Function
 *
 * Returns all site data needed by React frontend in one request.
 * This eliminates multiple API calls on initial page load.
 *
 * @return array Bootstrap data including settings, menus, pages, and custom post types
 */
function cnf_get_bootstrap_data() {
    return array(
        'siteSettings' => cnf_get_site_settings(),
        'menus' => cnf_get_menus(),
        'pages' => cnf_get_pages(),
        'cnfMachines' => cnf_get_machines(),
        'cnfUses' => cnf_get_uses(),
        'faqs' => cnf_get_faqs(),
    );
}

/**
 * Get Site Settings
 *
 * Returns WordPress site configuration and options.
 *
 * @return array Site settings
 */
function cnf_get_site_settings() {
    return array(
        'title' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'url' => get_site_url(),
        'email' => get_bloginfo('admin_email'),
        'timezone' => get_option('timezone_string') ?: 'UTC',
        'date_format' => get_option('date_format') ?: 'F j, Y',
        'time_format' => get_option('time_format') ?: 'g:i a',
        'start_of_week' => (int) get_option('start_of_week', 0),
        'language' => get_locale(),
        'use_smilies' => (bool) get_option('use_smilies'),
        'default_category' => (int) get_option('default_category'),
        'default_post_format' => get_option('default_post_format', '0'),
        'posts_per_page' => (int) get_option('posts_per_page', 10),
        'default_ping_status' => get_option('default_ping_status'),
        'default_comment_status' => get_option('default_comment_status'),
        'show_words_logo' => true, // Custom option for client branding
    );
}

/**
 * Get Navigation Menus
 *
 * Returns all registered navigation menus with their items and hierarchical structure.
 *
 * @return array Menus indexed by location (primary, footer)
 */
function cnf_get_menus() {
    $menus = array();
    $locations = get_nav_menu_locations();

    foreach ($locations as $location => $menu_id) {
        if (!$menu_id) {
            continue;
        }

        $menu = wp_get_nav_menu_object($menu_id);
        if (!$menu) {
            continue;
        }

        $menu_items = wp_get_nav_menu_items($menu_id);
        if (!$menu_items) {
            $menu_items = array();
        }

        $menus[$location] = array(
            'term_id' => $menu->term_id,
            'name' => $menu->name,
            'slug' => $location,
            'term_group' => 0,
            'term_taxonomy_id' => $menu->term_taxonomy_id,
            'taxonomy' => 'nav_menu',
            'description' => $menu->description,
            'parent' => 0,
            'count' => $menu->count,
            'filter' => 'raw',
            'items' => cnf_format_menu_items($menu_items),
        );
    }

    return $menus;
}

/**
 * Format Menu Items with Hierarchical Structure
 *
 * Organizes menu items with children nested under parents.
 *
 * @param array $menu_items WordPress menu items
 * @return array Formatted menu items with children
 */
function cnf_format_menu_items($menu_items) {
    if (empty($menu_items)) {
        return array();
    }

    $formatted = array();
    $item_children = array();

    // Build children array
    foreach ($menu_items as $item) {
        if ($item->menu_item_parent) {
            $item_children[$item->menu_item_parent][] = $item;
        }
    }

    // Format top-level items
    foreach ($menu_items as $item) {
        if (!$item->menu_item_parent) {
            $formatted_item = cnf_format_single_menu_item($item);

            // Add children if exist
            if (isset($item_children[$item->ID])) {
                $formatted_item['children'] = array();
                foreach ($item_children[$item->ID] as $child) {
                    $formatted_item['children'][] = cnf_format_single_menu_item($child);
                }
            }

            $formatted[] = $formatted_item;
        }
    }

    return $formatted;
}

/**
 * Format Single Menu Item
 *
 * Converts WordPress menu item object to array with all properties.
 *
 * @param object $item WordPress menu item
 * @return array Formatted menu item
 */
function cnf_format_single_menu_item($item) {
    return array(
        'ID' => $item->ID,
        'post_author' => $item->post_author,
        'post_date' => $item->post_date,
        'post_date_gmt' => $item->post_date_gmt,
        'post_content' => $item->post_content,
        'post_title' => $item->post_title,
        'post_excerpt' => $item->post_excerpt,
        'post_status' => $item->post_status,
        'comment_status' => $item->comment_status,
        'ping_status' => $item->ping_status,
        'post_password' => $item->post_password,
        'post_name' => $item->post_name,
        'to_ping' => $item->to_ping,
        'pinged' => $item->pinged,
        'post_modified' => $item->post_modified,
        'post_modified_gmt' => $item->post_modified_gmt,
        'post_content_filtered' => $item->post_content_filtered,
        'post_parent' => $item->post_parent,
        'guid' => $item->guid,
        'menu_order' => $item->menu_order,
        'post_type' => $item->post_type,
        'post_mime_type' => $item->post_mime_type,
        'comment_count' => $item->comment_count,
        'filter' => $item->filter,
        'db_id' => $item->ID,
        'menu_item_parent' => $item->menu_item_parent,
        'object_id' => $item->object_id,
        'object' => $item->object,
        'type' => $item->type,
        'type_label' => $item->type_label,
        'url' => $item->url,
        'title' => $item->title,
        'target' => $item->target,
        'attr_title' => $item->attr_title,
        'description' => $item->description,
        'classes' => $item->classes,
        'xfn' => $item->xfn,
    );
}

/**
 * Get Pages
 *
 * Returns specific pages needed by the React frontend.
 *
 * @return array Pages indexed by slug
 */
function cnf_get_pages() {
    $pages_array = array();
    $page_slugs = array('home', 'about', 'contact', 'dealers', 'minidumpers', 'faqs', 'privacy-policy', 'terms-and-conditions');

    foreach ($page_slugs as $slug) {
        $page = get_page_by_path($slug);

        // Handle home page (might be at root)
        if (!$page && $slug === 'home') {
            $page = get_page_by_path('');
            if (!$page) {
                // Get front page
                $front_page_id = get_option('page_on_front');
                if ($front_page_id) {
                    $page = get_post($front_page_id);
                }
            }
        }

        if ($page) {
            $pages_array[$slug] = cnf_format_page($page);
        }
    }

    return $pages_array;
}

/**
 * Format Page
 *
 * Converts WordPress page to formatted array with Pods fields.
 *
 * @param WP_Post $page WordPress page object
 * @return array Formatted page data
 */
function cnf_format_page($page) {
    $featured_image = get_the_post_thumbnail_url($page->ID, 'full');

    return array(
        'id' => $page->ID,
        'date' => $page->post_date,
        'date_gmt' => $page->post_date_gmt,
        'modified' => $page->post_modified,
        'modified_gmt' => $page->post_modified_gmt,
        'slug' => $page->post_name,
        'status' => $page->post_status,
        'type' => $page->post_type,
        'link' => get_permalink($page->ID),
        'title' => array(
            'rendered' => get_the_title($page->ID),
        ),
        'content' => array(
            'rendered' => apply_filters('the_content', $page->post_content),
        ),
        'excerpt' => array(
            'rendered' => get_the_excerpt($page->ID),
        ),
        'author' => $page->post_author,
        'featured_media' => get_post_thumbnail_id($page->ID),
        'comment_status' => $page->comment_status,
        'ping_status' => $page->ping_status,
        'template' => get_page_template_slug($page->ID),
        'parent' => $page->post_parent,
        'menu_order' => $page->menu_order,
        'meta' => array(),
        'pods' => cnf_get_pods_fields($page->ID),
        '_embedded' => array(
            'wp:featuredmedia' => $featured_image ? array(array(
                'id' => get_post_thumbnail_id($page->ID),
                'source_url' => $featured_image,
                'alt_text' => get_post_meta(get_post_thumbnail_id($page->ID), '_wp_attachment_image_alt', true),
            )) : array(),
        ),
    );
}

/**
 * Get Pods Fields
 *
 * Retrieves all custom Pods fields for a post/page.
 *
 * @param int $post_id WordPress post ID
 * @return array Pods field data
 */
function cnf_get_pods_fields($post_id) {
    if (!function_exists('pods')) {
        return array();
    }

    $pod = pods(get_post_type($post_id), $post_id);
    if (!$pod || !$pod->exists()) {
        return array();
    }

    $fields = $pod->fields();
    $pods_data = array();

    foreach ($fields as $field_name => $field_data) {
        $value = $pod->field($field_name);

        // Handle different field types
        if ($field_data['type'] === 'file') {
            // File field - return with ID and guid
            if (is_array($value)) {
                $pods_data[$field_name] = array(
                    'ID' => $value['ID'],
                    'guid' => $value['guid'],
                    'post_title' => isset($value['post_title']) ? $value['post_title'] : '',
                );
            }
        } elseif ($field_data['type'] === 'pick') {
            // Relationship field - return IDs
            $pods_data[$field_name] = is_array($value) ? array_column($value, 'ID') : array();
        } else {
            $pods_data[$field_name] = $value;
        }
    }

    return $pods_data;
}

/**
 * Get CNF Machines
 *
 * Returns all cnf_machine custom post type entries.
 *
 * @return array Machine posts
 */
function cnf_get_machines() {
    $machines = array();

    $args = array(
        'post_type' => 'cnf_machine',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'post_status' => 'publish',
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $machines[] = cnf_format_machine(get_post());
        }
        wp_reset_postdata();
    }

    return $machines;
}

/**
 * Format Machine
 *
 * Converts cnf_machine post to formatted array with Pods fields.
 *
 * @param WP_Post $post Machine post object
 * @return array Formatted machine data
 */
function cnf_format_machine($post) {
    $featured_image = get_the_post_thumbnail_url($post->ID, 'full');

    return array(
        'id' => $post->ID,
        'date' => $post->post_date,
        'date_gmt' => $post->post_date_gmt,
        'modified' => $post->post_modified,
        'modified_gmt' => $post->post_modified_gmt,
        'slug' => $post->post_name,
        'status' => $post->post_status,
        'type' => 'cnf_machine',
        'link' => get_permalink($post->ID),
        'title' => array(
            'rendered' => get_the_title($post->ID),
        ),
        'content' => array(
            'rendered' => apply_filters('the_content', $post->post_content),
        ),
        'excerpt' => array(
            'rendered' => get_the_excerpt($post->ID),
        ),
        'author' => $post->post_author,
        'featured_media' => get_post_thumbnail_id($post->ID),
        'comment_status' => $post->comment_status,
        'ping_status' => $post->ping_status,
        'template' => '',
        'meta' => array(),
        'pods' => cnf_get_pods_fields($post->ID),
        'machine_category' => wp_get_post_terms($post->ID, 'machine_category', array('fields' => 'ids')),
        'machine_industry' => wp_get_post_terms($post->ID, 'machine_industry', array('fields' => 'ids')),
        '_embedded' => array(
            'wp:featuredmedia' => $featured_image ? array(array(
                'id' => get_post_thumbnail_id($post->ID),
                'source_url' => $featured_image,
                'alt_text' => get_post_meta(get_post_thumbnail_id($post->ID), '_wp_attachment_image_alt', true),
                'media_details' => array(
                    'width' => 1200,
                    'height' => 800,
                    'file' => basename($featured_image),
                ),
                'mime_type' => 'image/webp',
                'media_type' => 'image',
            )) : array(),
        ),
    );
}

/**
 * Get CNF Uses
 *
 * Returns all cnf_use custom post type entries.
 *
 * @return array Use case posts
 */
function cnf_get_uses() {
    $uses = array();

    $args = array(
        'post_type' => 'cnf_use',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'post_status' => 'publish',
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $uses[] = cnf_format_use(get_post());
        }
        wp_reset_postdata();
    }

    return $uses;
}

/**
 * Format Use Case
 *
 * Converts cnf_use post to formatted array with Pods fields.
 *
 * @param WP_Post $post Use case post object
 * @return array Formatted use case data
 */
function cnf_format_use($post) {
    $featured_image = get_the_post_thumbnail_url($post->ID, 'full');

    return array(
        'id' => $post->ID,
        'date' => $post->post_date,
        'date_gmt' => $post->post_date_gmt,
        'modified' => $post->post_modified,
        'modified_gmt' => $post->post_modified_gmt,
        'slug' => $post->post_name,
        'status' => $post->post_status,
        'type' => 'cnf_use',
        'link' => get_permalink($post->ID),
        'title' => array(
            'rendered' => get_the_title($post->ID),
        ),
        'content' => array(
            'rendered' => apply_filters('the_content', $post->post_content),
        ),
        'excerpt' => array(
            'rendered' => get_the_excerpt($post->ID),
        ),
        'author' => $post->post_author,
        'featured_media' => get_post_thumbnail_id($post->ID),
        'comment_status' => $post->comment_status,
        'ping_status' => $post->ping_status,
        'template' => '',
        'meta' => array(),
        'pods' => cnf_get_pods_fields($post->ID),
        'use_category' => wp_get_post_terms($post->ID, 'use_category', array('fields' => 'ids')),
        '_embedded' => array(
            'wp:featuredmedia' => $featured_image ? array(array(
                'id' => get_post_thumbnail_id($post->ID),
                'source_url' => $featured_image,
                'alt_text' => get_post_meta(get_post_thumbnail_id($post->ID), '_wp_attachment_image_alt', true),
            )) : array(),
        ),
    );
}

/**
 * Get FAQs
 *
 * Returns all faq custom post type entries, ordered by custom 'order' field.
 *
 * @return array FAQ posts
 */
function cnf_get_faqs() {
    $faqs = array();

    $args = array(
        'post_type' => 'faq',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'post_status' => 'publish',
    );

    // Try to order by Pods 'order' field if it exists
    if (function_exists('pods')) {
        $args['meta_key'] = 'order';
        $args['orderby'] = 'meta_value_num';
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $faqs[] = cnf_format_faq(get_post());
        }
        wp_reset_postdata();
    }

    return $faqs;
}

/**
 * Format FAQ
 *
 * Converts faq post to formatted array with Pods fields.
 *
 * @param WP_Post $post FAQ post object
 * @return array Formatted FAQ data
 */
function cnf_format_faq($post) {
    return array(
        'id' => $post->ID,
        'date' => $post->post_date,
        'date_gmt' => $post->post_date_gmt,
        'modified' => $post->post_modified,
        'modified_gmt' => $post->post_modified_gmt,
        'slug' => $post->post_name,
        'status' => $post->post_status,
        'type' => 'faq',
        'link' => get_permalink($post->ID),
        'title' => array(
            'rendered' => get_the_title($post->ID),
        ),
        'content' => array(
            'rendered' => '',
        ),
        'excerpt' => array(
            'rendered' => '',
        ),
        'author' => $post->post_author,
        'featured_media' => 0,
        'comment_status' => $post->comment_status,
        'ping_status' => $post->ping_status,
        'template' => '',
        'meta' => array(),
        'pods' => cnf_get_pods_fields($post->ID),
        'faq_category' => wp_get_post_terms($post->ID, 'faq_category', array('fields' => 'ids')),
        '_embedded' => array(),
    );
}
