<?php
/**
 * Dashboard Customizer
 *
 * Customizes WordPress admin dashboard based on schema definitions.
 *
 * @package CNF_Setup
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CNF Dashboard Customizer Class
 *
 * Applies dashboard customizations from schema.
 */
class CNF_Dashboard_Customizer {

    /**
     * Schema data
     *
     * @var array
     */
    private $schema;

    /**
     * Dashboard customization options
     *
     * @var array
     */
    private $customizations;

    /**
     * Constructor
     *
     * @param array $schema Schema data
     */
    public function __construct($schema) {
        $this->schema = $schema;
        $this->customizations = isset($schema['dashboardCustomization']) ? $schema['dashboardCustomization'] : array();
    }

    /**
     * Apply All Customizations
     *
     * Applies dashboard customizations and registers hooks.
     */
    public function apply_customizations() {
        // Remove menu items
        if (isset($this->customizations['remove_menu_items'])) {
            $this->register_menu_removal($this->customizations['remove_menu_items']);
        }

        // Reorder menu items
        if (isset($this->customizations['menu_order'])) {
            $this->register_menu_reorder($this->customizations['menu_order']);
        }

        // Add custom branding
        if (isset($this->customizations['branding'])) {
            $this->apply_branding($this->customizations['branding']);
        }

        // Remove dashboard widgets
        if (isset($this->customizations['remove_widgets'])) {
            $this->register_widget_removal($this->customizations['remove_widgets']);
        }

        error_log('CNF Setup: Dashboard customizations applied');

        return true;
    }

    /**
     * Register Menu Removal
     *
     * Registers hook to remove unwanted admin menu items.
     *
     * @param array $menu_items Menu items to remove
     */
    private function register_menu_removal($menu_items) {
        add_action('admin_menu', function() use ($menu_items) {
            foreach ($menu_items as $menu_slug) {
                remove_menu_page($menu_slug);
                error_log("CNF Setup: Removed menu item: {$menu_slug}");
            }
        }, 999);
    }

    /**
     * Register Menu Reorder
     *
     * Registers hook to reorder admin menu items.
     *
     * @param array $menu_order New menu order
     */
    private function register_menu_reorder($menu_order) {
        add_filter('custom_menu_order', '__return_true');
        add_filter('menu_order', function() use ($menu_order) {
            return $menu_order;
        });
    }

    /**
     * Apply Branding
     *
     * Applies custom branding (logo, colors, etc.).
     *
     * @param array $branding Branding options
     */
    private function apply_branding($branding) {
        // Custom login logo
        if (isset($branding['login_logo'])) {
            add_action('login_enqueue_scripts', function() use ($branding) {
                echo '<style type="text/css">
                    #login h1 a {
                        background-image: url(' . esc_url($branding['login_logo']) . ');
                        background-size: contain;
                        width: 200px;
                        height: 80px;
                    }
                </style>';
            });
        }

        // Custom admin logo
        if (isset($branding['admin_logo'])) {
            add_action('admin_head', function() use ($branding) {
                echo '<style type="text/css">
                    #wpadminbar .ab-icon:before {
                        content: "" !important;
                        background-image: url(' . esc_url($branding['admin_logo']) . ');
                        background-size: contain;
                        width: 20px;
                        height: 20px;
                    }
                </style>';
            });
        }

        // Custom admin footer text
        if (isset($branding['footer_text'])) {
            add_filter('admin_footer_text', function() use ($branding) {
                return $branding['footer_text'];
            });
        }

        // Custom admin colors
        if (isset($branding['admin_color_scheme'])) {
            add_action('admin_head', function() use ($branding) {
                $colors = $branding['admin_color_scheme'];
                echo '<style type="text/css">
                    #adminmenu, #adminmenu .wp-submenu, #adminmenuback, #adminmenuwrap {
                        background-color: ' . esc_attr($colors['menu_bg']) . ' !important;
                    }
                    #adminmenu a {
                        color: ' . esc_attr($colors['menu_text']) . ' !important;
                    }
                    #adminmenu .wp-submenu a:hover {
                        background-color: ' . esc_attr($colors['menu_hover']) . ' !important;
                    }
                </style>';
            });
        }
    }

    /**
     * Register Widget Removal
     *
     * Registers hook to remove unwanted dashboard widgets.
     *
     * @param array $widgets Widgets to remove
     */
    private function register_widget_removal($widgets) {
        add_action('wp_dashboard_setup', function() use ($widgets) {
            foreach ($widgets as $widget_id) {
                remove_meta_box($widget_id, 'dashboard', 'normal');
                remove_meta_box($widget_id, 'dashboard', 'side');
                remove_meta_box($widget_id, 'dashboard', 'column3');
                remove_meta_box($widget_id, 'dashboard', 'column4');
                error_log("CNF Setup: Removed dashboard widget: {$widget_id}");
            }
        }, 999);
    }

    /**
     * Add Custom Dashboard Widget
     *
     * Adds a custom welcome widget to dashboard.
     */
    public function add_welcome_widget() {
        add_action('wp_dashboard_setup', function() {
            wp_add_dashboard_widget(
                'cnf_welcome_widget',
                'Welcome to CNF Mini Dumpers',
                array($this, 'render_welcome_widget')
            );
        });
    }

    /**
     * Render Welcome Widget
     *
     * Renders content for welcome dashboard widget.
     */
    public function render_welcome_widget() {
        ?>
        <div class="cnf-welcome-widget">
            <p><strong>Welcome to CNF Mini Dumpers WordPress Admin!</strong></p>
            <p>This site is managed via a headless CMS architecture. Content is served to the React frontend via REST API.</p>
            <ul>
                <li>✓ Custom post types created via Pods</li>
                <li>✓ Media library populated</li>
                <li>✓ Navigation menus configured</li>
                <li>✓ Dashboard customized</li>
            </ul>
            <p><a href="<?php echo admin_url('tools.php?page=cnf-setup'); ?>" class="button button-primary">View Setup Status</a></p>
            <p><a href="<?php echo rest_url('app/v1/bootstrap'); ?>" class="button button-secondary" target="_blank">View Bootstrap API</a></p>
        </div>
        <style>
            .cnf-welcome-widget ul {
                list-style: none;
                padding-left: 0;
            }
            .cnf-welcome-widget li {
                padding: 5px 0;
            }
        </style>
        <?php
    }
}
