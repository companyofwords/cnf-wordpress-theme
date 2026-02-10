<?php
/**
 * Plugin Name: CNF REST API Endpoints
 * Description: Custom REST API endpoints for React frontend data fetching
 * Version: 1.0.0
 * Author: Wordsco
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Custom REST API Endpoints
 */
add_action('rest_api_init', function() {

    // ========================================================================
    // Bootstrap Endpoint - Get all data needed for site initialization
    // ========================================================================
    register_rest_route('cnf/v1', '/bootstrap', array(
        'methods' => 'GET',
        'callback' => 'cnf_get_bootstrap_data',
        'permission_callback' => '__return_true', // Public endpoint
    ));

    // ========================================================================
    // Theme Options Endpoint - Get all theme options (178 fields)
    // ========================================================================
    register_rest_route('cnf/v1', '/theme-options', array(
        'methods' => 'GET',
        'callback' => 'cnf_get_theme_options',
        'permission_callback' => '__return_true', // Public endpoint
    ));

    // ========================================================================
    // Machines Endpoint - Get all CNF machines
    // ========================================================================
    register_rest_route('cnf/v1', '/machines', array(
        'methods' => 'GET',
        'callback' => 'cnf_get_machines',
        'permission_callback' => '__return_true',
    ));

    // ========================================================================
    // Single Machine Endpoint
    // ========================================================================
    register_rest_route('cnf/v1', '/machines/(?P<slug>[a-z0-9-]+)', array(
        'methods' => 'GET',
        'callback' => 'cnf_get_machine_by_slug',
        'permission_callback' => '__return_true',
        'args' => array(
            'slug' => array(
                'validate_callback' => function($param) {
                    return is_string($param);
                }
            ),
        ),
    ));

    // ========================================================================
    // Uses Endpoint - Get all recommended uses
    // ========================================================================
    register_rest_route('cnf/v1', '/uses', array(
        'methods' => 'GET',
        'callback' => 'cnf_get_uses',
        'permission_callback' => '__return_true',
    ));

    // ========================================================================
    // FAQs Endpoint - Get all FAQs
    // ========================================================================
    register_rest_route('cnf/v1', '/faqs', array(
        'methods' => 'GET',
        'callback' => 'cnf_get_faqs',
        'permission_callback' => '__return_true',
    ));
});

/**
 * Get Bootstrap Data
 *
 * Returns all data needed for site initialization
 */
function cnf_get_bootstrap_data() {
    $data = array(
        'site' => array(
            'title' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'url' => get_site_url(),
            'email' => get_option('admin_email'),
        ),
        'menus' => cnf_get_menus(),
        'pages' => cnf_get_pages(),
        'options' => cnf_get_theme_options(),
        'pods' => array(
            'cnf_machines' => cnf_get_machines(),
            'cnf_uses' => cnf_get_uses(),
            'faqs' => cnf_get_faqs(),
        ),
        'posts' => cnf_get_news_posts(),
    );

    return rest_ensure_response($data);
}

/**
 * Get All Menus
 */
function cnf_get_menus() {
    $menus = array();

    // Get primary menu
    $locations = get_nav_menu_locations();
    if (isset($locations['primary'])) {
        $menu_items = wp_get_nav_menu_items($locations['primary']);
        $menus['primary'] = array(
            'name' => 'Primary Menu',
            'items' => cnf_format_menu_items($menu_items),
        );
    }

    return $menus;
}

/**
 * Format Menu Items for REST API
 */
function cnf_format_menu_items($menu_items) {
    $formatted = array();

    if (!$menu_items) {
        return $formatted;
    }

    foreach ($menu_items as $item) {
        $formatted[] = array(
            'ID' => $item->ID,
            'title' => $item->title,
            'url' => $item->url,
            'menu_item_parent' => $item->menu_item_parent,
            'menu_order' => $item->menu_order,
        );
    }

    return $formatted;
}

/**
 * Get All Pages
 */
function cnf_get_pages() {
    $pages = get_posts(array(
        'post_type' => 'page',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));

    $formatted = array();

    foreach ($pages as $page) {
        $page_data = array(
            'id' => $page->ID,
            'slug' => $page->post_name,
            'title' => array('rendered' => $page->post_title),
            'content' => array('rendered' => apply_filters('the_content', $page->post_content)),
            'excerpt' => array('rendered' => $page->post_excerpt),
        );

        // Only add Pods data if Pods is available
        if (function_exists('pods')) {
            try {
                $pod = pods('page', $page->ID);
                if ($pod && $pod->exists()) {
                    $page_data['pods'] = $pod->export();
                }
            } catch (Exception $e) {
                // Pods not available or error - skip
                $page_data['pods'] = array();
            }
        } else {
            $page_data['pods'] = array();
        }

        $formatted[] = $page_data;
    }

    return $formatted;
}

/**
 * Get Theme Options (All 178 fields)
 */
function cnf_get_theme_options() {
    global $wpdb;

    // Get all options that start with 'cnf_theme_options_'
    $results = $wpdb->get_results(
        "SELECT option_name, option_value
         FROM {$wpdb->options}
         WHERE option_name LIKE 'cnf_theme_options_%'",
        ARRAY_A
    );

    $options = array();

    foreach ($results as $row) {
        // Remove 'cnf_theme_options_' prefix
        $key = str_replace('cnf_theme_options_', '', $row['option_name']);
        $options[$key] = maybe_unserialize($row['option_value']);
    }

    return rest_ensure_response($options);
}

/**
 * Get All Machines (CNF Mini Dumpers)
 */
function cnf_get_machines() {
    // Check if Pods is available
    if (!function_exists('pods')) {
        return array();
    }

    try {
        $machines = pods('cnf_machine', array(
            'limit' => -1,
            'orderby' => 'menu_order ASC',
        ));

        $data = array();

        if ($machines && $machines->total() > 0) {
            while ($machines->fetch()) {
                $data[] = array(
                    'id' => $machines->id(),
                    'slug' => $machines->field('slug'),
                    'title' => array('rendered' => $machines->field('post_title')),
                    'content' => array('rendered' => $machines->field('post_content')),
                    'pods' => $machines->export(),
                );
            }
        }

        return $data;
    } catch (Exception $e) {
        error_log('CNF REST API: Failed to get machines - ' . $e->getMessage());
        return array();
    }
}

/**
 * Get Machine by Slug
 */
function cnf_get_machine_by_slug($request) {
    $slug = $request['slug'];

    // Check if Pods is available
    if (!function_exists('pods')) {
        return new WP_Error('pods_unavailable', 'Pods Framework not available', array('status' => 503));
    }

    try {
        $machine = pods('cnf_machine', array(
            'where' => "post_name = '$slug'",
            'limit' => 1,
        ));

        if (!$machine || $machine->total() === 0) {
            return new WP_Error('not_found', 'Machine not found', array('status' => 404));
        }

        $machine->fetch();

        $data = array(
            'id' => $machine->id(),
            'slug' => $machine->field('slug'),
            'title' => array('rendered' => $machine->field('post_title')),
            'content' => array('rendered' => $machine->field('post_content')),
            'pods' => $machine->export(),
        );

        return rest_ensure_response($data);
    } catch (Exception $e) {
        error_log('CNF REST API: Failed to get machine by slug - ' . $e->getMessage());
        return new WP_Error('server_error', 'Failed to retrieve machine', array('status' => 500));
    }
}

/**
 * Get All Uses/Applications
 */
function cnf_get_uses() {
    // Check if Pods is available
    if (!function_exists('pods')) {
        return array();
    }

    try {
        $uses = pods('cnf_use', array(
            'limit' => -1,
        ));

        $data = array();

        if ($uses && $uses->total() > 0) {
            while ($uses->fetch()) {
                $data[] = array(
                    'id' => $uses->id(),
                    'slug' => $uses->field('slug'),
                    'title' => array('rendered' => $uses->field('post_title')),
                    'content' => array('rendered' => $uses->field('post_content')),
                    'excerpt' => array('rendered' => $uses->field('post_excerpt')),
                    'pods' => $uses->export(),
                );
            }
        }

        return $data;
    } catch (Exception $e) {
        error_log('CNF REST API: Failed to get uses - ' . $e->getMessage());
        return array();
    }
}

/**
 * Get All FAQs
 */
function cnf_get_faqs() {
    // Check if Pods is available
    if (!function_exists('pods')) {
        return array();
    }

    try {
        $faqs = pods('faq', array(
            'limit' => -1,
        ));

        $data = array();

        if ($faqs && $faqs->total() > 0) {
            while ($faqs->fetch()) {
                $data[] = array(
                    'id' => $faqs->id(),
                    'slug' => $faqs->field('slug'),
                    'title' => array('rendered' => $faqs->field('post_title')),
                    'content' => array('rendered' => $faqs->field('post_content')),
                    'pods' => $faqs->export(),
                );
            }
        }

        return $data;
    } catch (Exception $e) {
        error_log('CNF REST API: Failed to get FAQs - ' . $e->getMessage());
        return array();
    }
}

/**
 * Get News Posts
 */
function cnf_get_news_posts() {
    $posts = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));

    $data = array();

    foreach ($posts as $post) {
        $post_data = array(
            'id' => $post->ID,
            'slug' => $post->post_name,
            'title' => array('rendered' => $post->post_title),
            'content' => array('rendered' => apply_filters('the_content', $post->post_content)),
            'excerpt' => array('rendered' => $post->post_excerpt),
            'date' => $post->post_date,
        );

        // Only add Pods data if Pods is available
        if (function_exists('pods')) {
            try {
                $pod = pods('post', $post->ID);
                if ($pod && $pod->exists()) {
                    $post_data['pods'] = $pod->export();
                }
            } catch (Exception $e) {
                // Pods not available or error - skip
                $post_data['pods'] = array();
            }
        } else {
            $post_data['pods'] = array();
        }

        $data[] = $post_data;
    }

    return $data;
}
