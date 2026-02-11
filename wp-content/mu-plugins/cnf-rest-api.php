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

// Only load if WordPress is fully initialized and we're not in AJAX context
if (!function_exists('register_rest_route')) {
    return;
}

// Add error handler to prevent fatal errors
function cnf_rest_api_error_handler($errno, $errstr, $errfile, $errline) {
    error_log("CNF REST API Error: [$errno] $errstr in $errfile on line $errline");
    return true; // Don't execute PHP internal error handler
}
set_error_handler('cnf_rest_api_error_handler', E_ERROR | E_WARNING);

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
    // Bootstrap-Fresh Endpoint - Bypasses WP Engine cache with timestamp
    // ========================================================================
    register_rest_route('cnf/v1', '/bootstrap-fresh', array(
        'methods' => 'GET',
        'callback' => 'cnf_get_bootstrap_data_fresh',
        'permission_callback' => '__return_true', // Public endpoint
    ));

    // ========================================================================
    // Bootstrap V2 Endpoint - New endpoint to bypass cache
    // ========================================================================
    register_rest_route('cnf/v1', '/bootstrap-v2', array(
        'methods' => 'GET',
        'callback' => 'cnf_get_bootstrap_data_v2',
        'permission_callback' => '__return_true', // Public endpoint
    ));

    // ========================================================================
    // Theme Options Endpoint - Get all theme options (178 fields)
    // ========================================================================
    register_rest_route('cnf/v1', '/theme-options', array(
        'methods' => 'GET',
        'callback' => 'cnf_get_theme_options_endpoint',
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
function cnf_get_bootstrap_data($request = null) {
    // Build data array with error handling
    $data = array(
        'site' => array(
            'title' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'url' => get_site_url(),
            'email' => get_option('admin_email'),
        ),
    );

    // Get menus (with error handling)
    try {
        $data['menus'] = cnf_get_menus();
    } catch (Exception $e) {
        error_log('CNF Bootstrap: Failed to get menus - ' . $e->getMessage());
        $data['menus'] = array();
    }

    // Get pages (with error handling)
    try {
        $data['pages'] = cnf_get_pages();
    } catch (Exception $e) {
        error_log('CNF Bootstrap: Failed to get pages - ' . $e->getMessage());
        $data['pages'] = array();
    }

    // Get theme options (with error handling)
    try {
        $data['options'] = cnf_get_theme_options();
    } catch (Exception $e) {
        error_log('CNF Bootstrap: Failed to get theme options - ' . $e->getMessage());
        $data['options'] = array();
    }

    // Get Pods data (with error handling)
    $data['pods'] = array();
    try {
        $data['pods']['cnf_machines'] = cnf_get_machines();
    } catch (Exception $e) {
        error_log('CNF Bootstrap: Failed to get machines - ' . $e->getMessage());
        $data['pods']['cnf_machines'] = array();
    }

    try {
        $data['pods']['cnf_uses'] = cnf_get_uses();
    } catch (Exception $e) {
        error_log('CNF Bootstrap: Failed to get uses - ' . $e->getMessage());
        $data['pods']['cnf_uses'] = array();
    }

    try {
        $data['pods']['faqs'] = cnf_get_faqs();
    } catch (Exception $e) {
        error_log('CNF Bootstrap: Failed to get FAQs - ' . $e->getMessage());
        $data['pods']['faqs'] = array();
    }

    try {
        $data['pods']['cnf_dealers'] = cnf_get_dealers();
    } catch (Exception $e) {
        error_log('CNF Bootstrap: Failed to get dealers - ' . $e->getMessage());
        $data['pods']['cnf_dealers'] = array();
    }

    try {
        $data['pods']['cnf_promotions'] = cnf_get_promotions();
    } catch (Exception $e) {
        error_log('CNF Bootstrap: Failed to get promotions - ' . $e->getMessage());
        $data['pods']['cnf_promotions'] = array();
    }

    // Get posts (with error handling)
    try {
        $data['posts'] = cnf_get_news_posts();
    } catch (Exception $e) {
        error_log('CNF Bootstrap: Failed to get posts - ' . $e->getMessage());
        $data['posts'] = array();
    }

    return rest_ensure_response($data);
}

/**
 * Get Bootstrap Data (Fresh - Bypasses WP Engine Cache)
 *
 * Same as cnf_get_bootstrap_data() but with timestamp to force new cache key
 */
function cnf_get_bootstrap_data_fresh($request = null) {
    // Build data array with error handling
    $data = array(
        'site' => array(
            'title' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'url' => get_site_url(),
            'email' => get_option('admin_email'),
        ),
    );

    // Get menus (with error handling)
    try {
        $data['menus'] = cnf_get_menus();
    } catch (Exception $e) {
        error_log('CNF Bootstrap Fresh: Failed to get menus - ' . $e->getMessage());
        $data['menus'] = array();
    }

    // Get pages (with error handling)
    try {
        $data['pages'] = cnf_get_pages();
    } catch (Exception $e) {
        error_log('CNF Bootstrap Fresh: Failed to get pages - ' . $e->getMessage());
        $data['pages'] = array();
    }

    // Get theme options (with error handling)
    $options_count = 0;
    try {
        $data['options'] = cnf_get_theme_options();
        $options_count = count($data['options']);
    } catch (Exception $e) {
        error_log('CNF Bootstrap Fresh: Failed to get theme options - ' . $e->getMessage());
        $data['options'] = array();
    }

    // Get Pods data (with error handling)
    $data['pods'] = array();
    try {
        $data['pods']['cnf_machines'] = cnf_get_machines();
    } catch (Exception $e) {
        error_log('CNF Bootstrap Fresh: Failed to get machines - ' . $e->getMessage());
        $data['pods']['cnf_machines'] = array();
    }

    try {
        $data['pods']['cnf_uses'] = cnf_get_uses();
    } catch (Exception $e) {
        error_log('CNF Bootstrap Fresh: Failed to get uses - ' . $e->getMessage());
        $data['pods']['cnf_uses'] = array();
    }

    try {
        $data['pods']['faqs'] = cnf_get_faqs();
    } catch (Exception $e) {
        error_log('CNF Bootstrap Fresh: Failed to get FAQs - ' . $e->getMessage());
        $data['pods']['faqs'] = array();
    }

    try {
        $data['pods']['cnf_dealers'] = cnf_get_dealers();
    } catch (Exception $e) {
        error_log('CNF Bootstrap Fresh: Failed to get dealers - ' . $e->getMessage());
        $data['pods']['cnf_dealers'] = array();
    }

    try {
        $data['pods']['cnf_promotions'] = cnf_get_promotions();
    } catch (Exception $e) {
        error_log('CNF Bootstrap Fresh: Failed to get promotions - ' . $e->getMessage());
        $data['pods']['cnf_promotions'] = array();
    }

    // Get posts (with error handling)
    try {
        $data['posts'] = cnf_get_news_posts();
    } catch (Exception $e) {
        error_log('CNF Bootstrap Fresh: Failed to get posts - ' . $e->getMessage());
        $data['posts'] = array();
    }

    // Add debug info
    $data['_debug'] = array(
        'endpoint' => 'bootstrap-fresh',
        'timestamp' => time(),
        'options_count' => $options_count,
        'cache_headers' => 'aggressive',
    );

    return rest_ensure_response($data);
}

/**
 * Get All Menus
 */
function cnf_get_menus($request = null) {
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
function cnf_get_pages($request = null) {
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
 * Returns array directly for use in bootstrap endpoint
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

    // Return array directly (not wrapped in REST response)
    // This function is used within the bootstrap endpoint
    return $options;
}

/**
 * Theme Options REST API Endpoint Handler
 * Wraps cnf_get_theme_options() in REST response for direct endpoint access
 */
function cnf_get_theme_options_endpoint($request = null) {
    return rest_ensure_response(cnf_get_theme_options());
}

/**
 * Get featured image data for a post
 * Returns array in WordPress REST API _embedded format
 */
function cnf_get_featured_image_data($post_id) {
    $featured_image_id = get_post_thumbnail_id($post_id);

    if (!$featured_image_id) {
        return array();
    }

    $image_data = wp_get_attachment_image_src($featured_image_id, 'full');
    if (!$image_data) {
        return array();
    }

    $featured_media = array(
        'id' => $featured_image_id,
        'source_url' => $image_data[0],
        'alt_text' => get_post_meta($featured_image_id, '_wp_attachment_image_alt', true),
        'media_details' => array(
            'width' => $image_data[1],
            'height' => $image_data[2],
            'sizes' => array(
                'thumbnail' => array(
                    'source_url' => wp_get_attachment_image_src($featured_image_id, 'thumbnail')[0] ?? '',
                ),
                'medium' => array(
                    'source_url' => wp_get_attachment_image_src($featured_image_id, 'medium')[0] ?? '',
                ),
                'large' => array(
                    'source_url' => wp_get_attachment_image_src($featured_image_id, 'large')[0] ?? '',
                ),
            ),
        ),
    );

    return array($featured_media);
}

/**
 * Get All Machines (CNF Mini Dumpers)
 */
function cnf_get_machines($request = null) {
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
                    '_embedded' => array(
                        'wp:featuredmedia' => cnf_get_featured_image_data($machines->id())
                    ),
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
            '_embedded' => array(
                'wp:featuredmedia' => cnf_get_featured_image_data($machine->id())
            ),
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
function cnf_get_uses($request = null) {
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
function cnf_get_faqs($request = null) {
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
 * Get All Dealers
 *
 * Falls back to native WordPress functions if Pods doesn't recognize the posts
 */
function cnf_get_dealers($request = null) {
    try {
        // First try Pods if available
        if (function_exists('pods')) {
            $dealers_pod = pods('cnf_dealer', array(
                'limit' => -1,
                'orderby' => 'post_title ASC',
            ));

            // If Pods works and has dealers, use it
            if ($dealers_pod && $dealers_pod->total() > 0) {
                $data = array();
                while ($dealers_pod->fetch()) {
                    $data[] = array(
                        'id' => $dealers_pod->id(),
                        'slug' => $dealers_pod->field('slug'),
                        'title' => array('rendered' => $dealers_pod->field('post_title')),
                        'content' => array('rendered' => $dealers_pod->field('post_content')),
                        'pods' => $dealers_pod->export(),
                    );
                }
                return $data;
            }
        }

        // Fallback to native WordPress if Pods doesn't work
        $posts = get_posts(array(
            'post_type' => 'cnf_dealer',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        $data = array();

        foreach ($posts as $post) {
            // Get post meta for Pods fields
            $pods_data = array(
                'name' => get_post_meta($post->ID, 'name', true),
                'address' => get_post_meta($post->ID, 'address', true),
                'sales_area' => get_post_meta($post->ID, 'sales_area', true),
                'phone' => get_post_meta($post->ID, 'phone', true),
                'website' => get_post_meta($post->ID, 'website', true),
            );

            $data[] = array(
                'id' => $post->ID,
                'slug' => $post->post_name,
                'title' => array('rendered' => $post->post_title),
                'content' => array('rendered' => apply_filters('the_content', $post->post_content)),
                'pods' => $pods_data,
            );
        }

        return $data;
    } catch (Exception $e) {
        error_log('CNF REST API: Failed to get dealers - ' . $e->getMessage());
        return array();
    }
}

/**
 * Get All Promotions
 *
 * Falls back to native WordPress functions if Pods doesn't recognize the posts
 */
function cnf_get_promotions($request = null) {
    try {
        // First try Pods if available
        if (function_exists('pods')) {
            $promotions_pod = pods('cnf_promotion', array(
                'limit' => -1,
                'orderby' => 'menu_order ASC',
            ));

            // If Pods works and has promotions, use it
            if ($promotions_pod && $promotions_pod->total() > 0) {
                $data = array();
                while ($promotions_pod->fetch()) {
                    $data[] = array(
                        'id' => $promotions_pod->id(),
                        'slug' => $promotions_pod->field('slug'),
                        'title' => array('rendered' => $promotions_pod->field('post_title')),
                        'content' => array('rendered' => $promotions_pod->field('post_content')),
                        'pods' => $promotions_pod->export(),
                    );
                }
                return $data;
            }
        }

        // Fallback to native WordPress if Pods doesn't work
        $posts = get_posts(array(
            'post_type' => 'cnf_promotion',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ));

        $data = array();

        foreach ($posts as $post) {
            // Get post meta for Pods fields
            $pods_data = array(
                'title' => get_post_meta($post->ID, 'title', true) ?: $post->post_title,
                'description' => get_post_meta($post->ID, 'description', true) ?: $post->post_content,
                'icon_name' => get_post_meta($post->ID, 'icon_name', true),
                'background_image' => get_post_meta($post->ID, 'background_image', true),
            );

            $data[] = array(
                'id' => $post->ID,
                'slug' => $post->post_name,
                'title' => array('rendered' => $post->post_title),
                'content' => array('rendered' => apply_filters('the_content', $post->post_content)),
                'pods' => $pods_data,
            );
        }

        return $data;
    } catch (Exception $e) {
        error_log('CNF REST API: Failed to get promotions - ' . $e->getMessage());
        return array();
    }
}

/**
 * Get News Posts
 */
function cnf_get_news_posts($request = null) {
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

/**
 * Get Bootstrap Data V2 - Completely new endpoint to bypass cache
 *
 * Same as bootstrap but with a new route name to avoid cached errors
 */
function cnf_get_bootstrap_data_v2($request = null) {
    // Build data array with error handling
    $data = array(
        'site' => array(
            'title' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'url' => get_site_url(),
            'email' => get_option('admin_email'),
        ),
    );

    // Get menus (with error handling)
    try {
        $data['menus'] = cnf_get_menus();
    } catch (Exception $e) {
        error_log('CNF Bootstrap V2: Failed to get menus - ' . $e->getMessage());
        $data['menus'] = array();
    }

    // Get pages (with error handling)
    try {
        $data['pages'] = cnf_get_pages();
    } catch (Exception $e) {
        error_log('CNF Bootstrap V2: Failed to get pages - ' . $e->getMessage());
        $data['pages'] = array();
    }

    // Get theme options (with error handling)
    $options_count = 0;
    try {
        $data['options'] = cnf_get_theme_options();
        $options_count = count($data['options']);
    } catch (Exception $e) {
        error_log('CNF Bootstrap V2: Failed to get theme options - ' . $e->getMessage());
        $data['options'] = array();
    }

    // Get Pods data (with error handling)
    $data['pods'] = array();
    try {
        $data['pods']['cnf_machines'] = cnf_get_machines();
    } catch (Exception $e) {
        error_log('CNF Bootstrap V2: Failed to get machines - ' . $e->getMessage());
        $data['pods']['cnf_machines'] = array();
    }

    try {
        $data['pods']['cnf_uses'] = cnf_get_uses();
    } catch (Exception $e) {
        error_log('CNF Bootstrap V2: Failed to get uses - ' . $e->getMessage());
        $data['pods']['cnf_uses'] = array();
    }

    try {
        $data['pods']['faqs'] = cnf_get_faqs();
    } catch (Exception $e) {
        error_log('CNF Bootstrap V2: Failed to get FAQs - ' . $e->getMessage());
        $data['pods']['faqs'] = array();
    }

    try {
        $data['pods']['cnf_dealers'] = cnf_get_dealers();
    } catch (Exception $e) {
        error_log('CNF Bootstrap V2: Failed to get dealers - ' . $e->getMessage());
        $data['pods']['cnf_dealers'] = array();
    }

    try {
        $data['pods']['cnf_promotions'] = cnf_get_promotions();
    } catch (Exception $e) {
        error_log('CNF Bootstrap V2: Failed to get promotions - ' . $e->getMessage());
        $data['pods']['cnf_promotions'] = array();
    }

    // Get posts (with error handling)
    try {
        $data['posts'] = cnf_get_news_posts();
    } catch (Exception $e) {
        error_log('CNF Bootstrap V2: Failed to get posts - ' . $e->getMessage());
        $data['posts'] = array();
    }

    // Add debug info
    $data['_debug'] = array(
        'endpoint' => 'bootstrap-v2',
        'timestamp' => time(),
        'options_count' => $options_count,
        'version' => '2.0',
    );

    return rest_ensure_response($data);
}

/**
 * Add no-cache headers to bootstrap endpoints
 */
add_filter('rest_post_dispatch', function($result, $server, $request) {
    $route = $request->get_route();

    // Apply aggressive headers to fresh/v2 endpoints
    if ($route === '/cnf/v1/bootstrap-fresh' || $route === '/cnf/v1/bootstrap-v2') {
        $server->send_header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0, private');
        $server->send_header('Pragma', 'no-cache');
        $server->send_header('Expires', '0');
        $server->send_header('X-CNF-Cache', 'disabled');
        $server->send_header('X-CNF-Fresh', 'true');
        $server->send_header('X-CNF-Timestamp', time());
    }

    return $result;
}, 10, 3);
