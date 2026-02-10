<?php
/**
 * CNF Headless Theme
 *
 * Custom REST API endpoints for React Router 7 frontend.
 * This theme is headless and serves content via WordPress REST API only.
 *
 * @package CNF_Headless
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define theme constants
define('CNF_THEME_VERSION', '1.0.0');
define('CNF_THEME_DIR', get_template_directory());
define('CNF_THEME_URL', get_template_directory_uri());

/**
 * Theme Setup
 *
 * Register theme support, navigation menus, and hooks.
 */
function cnf_theme_setup() {
    // Add theme support
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'cnf-headless'),
        'footer' => __('Footer Menu', 'cnf-headless'),
    ));

    // Add image sizes
    add_image_size('cnf-thumbnail', 300, 300, true);
    add_image_size('cnf-medium', 768, 768, false);
    add_image_size('cnf-large', 1200, 1200, false);
}
add_action('after_setup_theme', 'cnf_theme_setup');

/**
 * Enable CORS for API Requests
 *
 * Allows React app (localhost:5173, localhost:3000, production domains)
 * to access WordPress REST API from different origins.
 */
function cnf_add_cors_http_header() {
    // Allow React app origins (development + production)
    $allowed_origins = array(
        'http://localhost:5173',          // Vite dev server
        'http://localhost:3000',          // Alternative dev port
        'https://cnfminidumper.co.uk',   // Production domain
        'https://www.cnfminidumper.co.uk' // Production www
    );

    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    if (in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        // For development, allow all origins (comment out in production)
        // header("Access-Control-Allow-Origin: *");
    }

    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 3600");

    // Handle preflight OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        status_header(200);
        exit();
    }
}
add_action('init', 'cnf_add_cors_http_header');
add_action('rest_api_init', 'cnf_add_cors_http_header');

/**
 * Remove unnecessary REST API endpoints
 *
 * Reduces API surface area for security.
 */
function cnf_disable_rest_endpoints($endpoints) {
    // Disable comments endpoint (if not using comments)
    if (isset($endpoints['/wp/v2/comments'])) {
        unset($endpoints['/wp/v2/comments']);
    }

    // Disable users endpoint for non-authenticated requests
    if (isset($endpoints['/wp/v2/users']) && !is_user_logged_in()) {
        unset($endpoints['/wp/v2/users']);
    }

    return $endpoints;
}
add_filter('rest_endpoints', 'cnf_disable_rest_endpoints');

/**
 * Add custom REST API fields to responses
 *
 * Adds featured image URL directly to post responses.
 */
function cnf_add_featured_image_to_rest($data, $post, $context) {
    $featured_image_id = get_post_thumbnail_id($post->ID);
    if ($featured_image_id) {
        $image = wp_get_attachment_image_src($featured_image_id, 'full');
        $data->data['featured_image_url'] = $image ? $image[0] : null;
        $data->data['featured_image_alt'] = get_post_meta($featured_image_id, '_wp_attachment_image_alt', true);
    } else {
        $data->data['featured_image_url'] = null;
        $data->data['featured_image_alt'] = null;
    }
    return $data;
}
add_filter('rest_prepare_post', 'cnf_add_featured_image_to_rest', 10, 3);
add_filter('rest_prepare_page', 'cnf_add_featured_image_to_rest', 10, 3);

/**
 * Custom REST API Endpoints
 *
 * NOTE: REST API endpoints moved to MU-plugin: /mu-plugins/cnf-rest-api.php
 * Bootstrap endpoint available at: /wp-json/cnf/v1/bootstrap
 * Theme options endpoint available at: /wp-json/cnf/v1/theme-options
 */

/**
 * Admin Customization (Optional - can be moved to MU-plugin)
 *
 * Basic WordPress dashboard customizations.
 */
function cnf_customize_admin_dashboard() {
    // Remove unnecessary dashboard widgets
    remove_meta_box('dashboard_primary', 'dashboard', 'side');         // WordPress Events and News
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');     // Quick Draft
    remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');   // Recent Drafts
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');      // Activity
}
add_action('wp_dashboard_setup', 'cnf_customize_admin_dashboard');

/**
 * Custom Admin Footer Text
 */
function cnf_admin_footer_text() {
    echo 'Built with <a href="https://wordsco.uk" target="_blank">Wordsco</a> | CNF Headless WordPress System v' . CNF_THEME_VERSION;
}
add_filter('admin_footer_text', 'cnf_admin_footer_text');

/**
 * Enqueue Admin Styles (Optional)
 *
 * Add custom branding to WordPress admin.
 */
function cnf_admin_styles() {
    echo '<style>
        #wpadminbar .ab-icon:before {
            content: "\f468" !important;
        }
        .login h1 a {
            background-image: url(' . CNF_THEME_URL . '/assets/logo.png);
            background-size: contain;
            width: 200px;
            height: 80px;
        }
    </style>';
}
add_action('admin_head', 'cnf_admin_styles');
add_action('login_head', 'cnf_admin_styles');

/**
 * Disable File Editor
 *
 * Security: Prevent editing plugin/theme files from admin.
 */
if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

/**
 * Increase Upload Limits (if needed)
 *
 * Note: Server PHP settings may override these.
 */
@ini_set('upload_max_size', '64M');
@ini_set('post_max_size', '64M');
@ini_set('max_execution_time', '300');

/**
 * Clean up WordPress head
 *
 * Remove unnecessary tags since we're headless.
 */
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);

/**
 * Disable XML-RPC
 *
 * Security: Disable XML-RPC if not needed.
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Security Headers
 */
function cnf_add_security_headers() {
    if (!is_admin()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}
add_action('send_headers', 'cnf_add_security_headers');

/**
 * Log API Errors (Development Only)
 *
 * Uncomment for debugging REST API issues.
 */
// function cnf_log_rest_errors($result, $server, $request) {
//     if (is_wp_error($result)) {
//         error_log('REST API Error: ' . $result->get_error_message());
//     }
//     return $result;
// }
// add_filter('rest_pre_dispatch', 'cnf_log_rest_errors', 10, 3);

/**
 * Theme Activation Hook
 *
 * Runs when theme is activated.
 */
function cnf_theme_activation() {
    // Flush rewrite rules to register custom endpoints
    flush_rewrite_rules();

    // Set default permalink structure if not set
    if (get_option('permalink_structure') === '') {
        update_option('permalink_structure', '/%postname%/');
        flush_rewrite_rules();
    }
}
add_action('after_switch_theme', 'cnf_theme_activation');
