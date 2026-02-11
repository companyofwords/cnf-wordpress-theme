<?php
/**
 * Plugin Name: CNF Admin Customization
 * Description: Custom branded WordPress admin interface for CNF
 * Version: 1.0.0
 * Author: Wordsco
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ========================================================================
// CUSTOM LOGO & BRANDING
// ========================================================================

/**
 * Add CNF logo to admin bar and login page
 */
add_action('admin_head', 'cnf_custom_admin_logo');
add_action('login_head', 'cnf_custom_admin_logo');

/**
 * Inject CNF logo into admin menu with JavaScript
 */
add_action('admin_footer', 'cnf_inject_admin_menu_logo');
function cnf_inject_admin_menu_logo() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        if ($('#cnf-admin-logo').length === 0) {
            var logoHtml = '<img id="cnf-admin-logo" src="https://westgategroup.wpenginepowered.com/wp-content/uploads/cnf-logo-white.png" alt="CNF Mini Dumpers" />';
            $('#adminmenu').prepend(logoHtml);
        }
    });
    </script>
    <?php
}

function cnf_custom_admin_logo() {
    ?>
    <style>
        /* Admin Bar Logo */
        #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
            background-image: url('https://westgategroup.wpenginepowered.com/wp-content/uploads/cnf-logo-white.png') !important;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            content: '' !important;
            width: 32px !important;
            height: 32px !important;
        }

        /* Login Page Logo */
        .login h1 a {
            background-image: url('https://westgategroup.wpenginepowered.com/wp-content/uploads/cnf-logo-red.png') !important;
            background-size: contain !important;
            width: 320px !important;
            height: 120px !important;
            margin: 0 auto 25px !important;
        }

        /* Admin Menu Logo (top left) - Placeholder space */
        #cnf-admin-logo {
            display: block;
            width: 160px;
            max-width: 90%;
            height: auto;
            margin: 15px auto 25px;
            padding: 0 10px;
        }

        #adminmenu {
            padding-top: 10px;
        }
    </style>
    <?php
}

/**
 * Change login logo URL
 */
add_filter('login_headerurl', 'cnf_login_logo_url');
function cnf_login_logo_url() {
    return 'https://cnfminidumper.co.uk';
}

add_filter('login_headertext', 'cnf_login_logo_url_title');
function cnf_login_logo_url_title() {
    return 'CNF Mini Dumpers - Premium Tracked Mini Dumpers';
}

// ========================================================================
// BRAND COLORS IN ADMIN
// ========================================================================

add_action('admin_head', 'cnf_admin_brand_colors');
function cnf_admin_brand_colors() {
    ?>
    <style>
        :root {
            --cnf-red: #ee2742;
            --cnf-red-dark: #b81932;
            --cnf-charcoal: #212529;
        }

        /* Admin Bar */
        #wpadminbar {
            background: var(--cnf-charcoal) !important;
        }

        #wpadminbar .ab-item,
        #wpadminbar a.ab-item,
        #wpadminbar > #wp-toolbar span.ab-label,
        #wpadminbar > #wp-toolbar span.noticon {
            color: #fff !important;
        }

        #wpadminbar .ab-top-menu > li.hover > .ab-item,
        #wpadminbar .ab-top-menu > li:hover > .ab-item,
        #wpadminbar .ab-top-menu > li > .ab-item:focus,
        #wpadminbar.nojq .quicklinks .ab-top-menu > li > .ab-item:focus {
            background: var(--cnf-red) !important;
            color: #fff !important;
        }

        /* Primary Buttons */
        .wp-core-ui .button-primary {
            background: var(--cnf-red) !important;
            border-color: var(--cnf-red-dark) !important;
            text-shadow: none !important;
            box-shadow: none !important;
        }

        .wp-core-ui .button-primary:hover,
        .wp-core-ui .button-primary:focus {
            background: var(--cnf-red-dark) !important;
            border-color: var(--cnf-red-dark) !important;
        }

        /* Admin Menu */
        #adminmenu,
        #adminmenu .wp-submenu {
            background: #1e1e1e !important;
        }

        #adminmenu .wp-menu-name,
        #adminmenu a {
            color: rgba(240, 245, 250, 0.7) !important;
        }

        #adminmenu li.menu-top:hover,
        #adminmenu li.opensub > a.menu-top,
        #adminmenu li > a.menu-top:focus {
            background: var(--cnf-charcoal) !important;
        }

        #adminmenu li.menu-top:hover .wp-menu-name,
        #adminmenu li.menu-top:hover a {
            color: #fff !important;
        }

        #adminmenu .wp-has-current-submenu .wp-submenu,
        #adminmenu .wp-has-current-submenu .wp-submenu.wp-submenu-wrap,
        #adminmenu .wp-has-current-submenu.opensub .wp-submenu,
        #adminmenu a.wp-has-current-submenu:focus + .wp-submenu {
            background: var(--cnf-charcoal) !important;
        }

        #adminmenu .wp-submenu a:hover,
        #adminmenu .wp-submenu a:focus {
            color: var(--cnf-red) !important;
        }

        #adminmenu .current a.menu-top,
        #adminmenu .wp-has-current-submenu a.wp-has-current-submenu,
        #adminmenu li.current a.menu-top,
        #adminmenu .current .wp-menu-name {
            background: var(--cnf-red) !important;
            color: #fff !important;
        }

        /* Links */
        a {
            color: var(--cnf-red) !important;
        }

        a:hover,
        a:active,
        a:focus {
            color: var(--cnf-red-dark) !important;
        }

        /* Login Form */
        .login #login_error,
        .login .message,
        .login .success {
            border-left-color: var(--cnf-red) !important;
        }

        .login form {
            border: 1px solid #ddd;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .wp-core-ui .button-primary.button-hero {
            background: var(--cnf-red) !important;
            border-color: var(--cnf-red-dark) !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }

        /* Update Nag */
        .update-nag {
            border-left-color: var(--cnf-red) !important;
        }
    </style>
    <?php
}

// ========================================================================
// CUSTOM DASHBOARD
// ========================================================================

/**
 * Remove default dashboard widgets
 */
add_action('wp_dashboard_setup', 'cnf_remove_dashboard_widgets', 999);
function cnf_remove_dashboard_widgets() {
    global $wp_meta_boxes;

    // Remove default widgets
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health']);

    // Remove Genesis Blocks widget if it exists
    unset($wp_meta_boxes['dashboard']['normal']['default']['genesis_blocks_dashboard_widget']);
}

/**
 * Add custom welcome dashboard widget
 */
add_action('wp_dashboard_setup', 'cnf_add_custom_dashboard_widgets');
function cnf_add_custom_dashboard_widgets() {
    wp_add_dashboard_widget(
        'cnf_welcome_widget',
        '🏠 Welcome to CNF Mini Dumpers',
        'cnf_welcome_dashboard_widget'
    );

    wp_add_dashboard_widget(
        'cnf_content_overview',
        '📊 Content Overview',
        'cnf_content_overview_widget'
    );

    wp_add_dashboard_widget(
        'cnf_quick_links',
        '⚡ Quick Actions',
        'cnf_quick_links_widget'
    );
}

function cnf_welcome_dashboard_widget() {
    $current_user = wp_get_current_user();
    ?>
    <div style="padding: 20px;">
        <h2 style="margin-top: 0; color: #ee2742;">Welcome back, <?php echo esc_html($current_user->display_name); ?>!</h2>
        <p style="font-size: 16px; line-height: 1.6;">
            Welcome to the CNF Mini Dumpers content management system. From here you can manage all aspects of your website including machines, dealers, promotions, news articles, and FAQs.
        </p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-top: 20px;">
            <strong>🎯 Need help getting started?</strong>
            <ul style="margin: 10px 0 0 20px;">
                <li>Use the left menu to navigate between content types</li>
                <li>Click "Add New" to create new content</li>
                <li>Changes appear on the live site automatically</li>
                <li>Contact Wordsco for technical support</li>
            </ul>
        </div>
    </div>
    <?php
}

function cnf_content_overview_widget() {
    $machines_count = wp_count_posts('cnf_machine')->publish ?? 0;
    $dealers_count = wp_count_posts('cnf_dealer')->publish ?? 0;
    $promotions_count = wp_count_posts('cnf_promotion')->publish ?? 0;
    $news_count = wp_count_posts('post')->publish ?? 0;
    $faqs_count = wp_count_posts('faq')->publish ?? 0;
    $uses_count = wp_count_posts('cnf_use')->publish ?? 0;
    ?>
    <div style="padding: 10px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                    <strong>🔧 Machines</strong>
                </td>
                <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; text-align: right;">
                    <a href="edit.php?post_type=cnf_machine" style="text-decoration: none;">
                        <span style="background: #ee2742; color: white; padding: 4px 12px; border-radius: 12px; font-weight: bold;">
                            <?php echo $machines_count; ?>
                        </span>
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                    <strong>📍 Dealers</strong>
                </td>
                <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; text-align: right;">
                    <a href="edit.php?post_type=cnf_dealer" style="text-decoration: none;">
                        <span style="background: #ee2742; color: white; padding: 4px 12px; border-radius: 12px; font-weight: bold;">
                            <?php echo $dealers_count; ?>
                        </span>
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                    <strong>📢 Promotions</strong>
                </td>
                <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; text-align: right;">
                    <a href="edit.php?post_type=cnf_promotion" style="text-decoration: none;">
                        <span style="background: #ee2742; color: white; padding: 4px 12px; border-radius: 12px; font-weight: bold;">
                            <?php echo $promotions_count; ?>
                        </span>
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                    <strong>📰 News Articles</strong>
                </td>
                <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; text-align: right;">
                    <a href="edit.php" style="text-decoration: none;">
                        <span style="background: #ee2742; color: white; padding: 4px 12px; border-radius: 12px; font-weight: bold;">
                            <?php echo $news_count; ?>
                        </span>
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                    <strong>❓ FAQs</strong>
                </td>
                <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; text-align: right;">
                    <a href="edit.php?post_type=faq" style="text-decoration: none;">
                        <span style="background: #ee2742; color: white; padding: 4px 12px; border-radius: 12px; font-weight: bold;">
                            <?php echo $faqs_count; ?>
                        </span>
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding: 12px 0;">
                    <strong>🔨 Uses</strong>
                </td>
                <td style="padding: 12px 0; text-align: right;">
                    <a href="edit.php?post_type=cnf_use" style="text-decoration: none;">
                        <span style="background: #ee2742; color: white; padding: 4px 12px; border-radius: 12px; font-weight: bold;">
                            <?php echo $uses_count; ?>
                        </span>
                    </a>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

function cnf_quick_links_widget() {
    ?>
    <div style="padding: 10px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <a href="post-new.php?post_type=cnf_machine" class="button button-primary" style="padding: 12px; text-align: center; text-decoration: none;">
                🔧 Add Machine
            </a>
            <a href="post-new.php?post_type=cnf_dealer" class="button button-primary" style="padding: 12px; text-align: center; text-decoration: none;">
                📍 Add Dealer
            </a>
            <a href="post-new.php?post_type=cnf_promotion" class="button button-primary" style="padding: 12px; text-align: center; text-decoration: none;">
                📢 Add Promotion
            </a>
            <a href="post-new.php" class="button button-primary" style="padding: 12px; text-align: center; text-decoration: none;">
                📰 Add News
            </a>
            <a href="post-new.php?post_type=faq" class="button button-primary" style="padding: 12px; text-align: center; text-decoration: none;">
                ❓ Add FAQ
            </a>
            <a href="post-new.php?post_type=cnf_use" class="button button-primary" style="padding: 12px; text-align: center; text-decoration: none;">
                🔨 Add Use
            </a>
        </div>

        <hr style="margin: 20px 0;">

        <div style="text-align: center;">
            <a href="https://cnfminidumper.co.uk" target="_blank" class="button" style="padding: 12px 24px; text-decoration: none;">
                🌐 View Live Site
            </a>
        </div>
    </div>
    <?php
}

// ========================================================================
// HIDE COMMENTS & CLEANUP
// ========================================================================

/**
 * Remove Comments from admin menu and admin bar
 */
add_action('admin_menu', 'cnf_remove_comments_menu');
function cnf_remove_comments_menu() {
    remove_menu_page('edit-comments.php');
}

add_action('admin_bar_menu', 'cnf_remove_comments_admin_bar', 999);
function cnf_remove_comments_admin_bar($wp_admin_bar) {
    $wp_admin_bar->remove_node('comments');
}

/**
 * Disable comments site-wide
 */
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);
add_filter('comments_array', '__return_empty_array', 10, 2);

/**
 * Remove comments metabox
 */
add_action('admin_menu', 'cnf_remove_comments_metabox');
function cnf_remove_comments_metabox() {
    remove_meta_box('commentsdiv', 'post', 'normal');
    remove_meta_box('commentstatusdiv', 'post', 'normal');
    remove_meta_box('trackbacksdiv', 'post', 'normal');
}

// ========================================================================
// ADMIN FOOTER CUSTOMIZATION
// ========================================================================

add_filter('admin_footer_text', 'cnf_custom_admin_footer');
function cnf_custom_admin_footer() {
    echo '🔧 Built by <a href="https://wordsco.uk" target="_blank">Wordsco</a> | CNF Mini Dumpers Admin v1.0';
}

add_filter('update_footer', 'cnf_custom_version_footer', 999);
function cnf_custom_version_footer() {
    return 'WordPress ' . get_bloginfo('version');
}

// ========================================================================
// CLEAN UP ADMIN INTERFACE
// ========================================================================

/**
 * Remove unnecessary admin notices
 */
add_action('admin_head', 'cnf_hide_admin_notices', 999);
function cnf_hide_admin_notices() {
    // Only hide for non-admin users
    if (!current_user_can('manage_options')) {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }
}

/**
 * Customize admin columns for better UX
 */
add_filter('manage_posts_columns', 'cnf_customize_post_columns');
function cnf_customize_post_columns($columns) {
    // Remove comments column from all post types
    unset($columns['comments']);
    return $columns;
}
