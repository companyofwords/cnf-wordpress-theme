<?php
/**
 * Plugin Name: CNF Custom Post Types
 * Description: Registers custom post types for CNF (Dealers, Promotions)
 * Version: 1.0.0
 * Author: Wordsco
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Custom Post Types
 */
add_action('init', 'cnf_register_post_types');

function cnf_register_post_types() {

    // ========================================================================
    // CNF Dealer Post Type
    // ========================================================================
    register_post_type('cnf_dealer', array(
        'labels' => array(
            'name' => 'Dealers',
            'singular_name' => 'Dealer',
            'add_new' => 'Add New Dealer',
            'add_new_item' => 'Add New Dealer',
            'edit_item' => 'Edit Dealer',
            'new_item' => 'New Dealer',
            'view_item' => 'View Dealer',
            'search_items' => 'Search Dealers',
            'not_found' => 'No dealers found',
            'not_found_in_trash' => 'No dealers found in trash',
            'all_items' => 'All Dealers',
            'menu_name' => 'Dealers',
        ),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'rest_base' => 'dealers',
        'menu_position' => 20,
        'menu_icon' => 'dashicons-location',
        'supports' => array('title', 'editor', 'thumbnail'),
        'has_archive' => false,
        'rewrite' => array('slug' => 'dealer'),
        'capability_type' => 'post',
    ));

    // ========================================================================
    // CNF Promotion Post Type
    // ========================================================================
    register_post_type('cnf_promotion', array(
        'labels' => array(
            'name' => 'Promotions',
            'singular_name' => 'Promotion',
            'add_new' => 'Add New Promotion',
            'add_new_item' => 'Add New Promotion',
            'edit_item' => 'Edit Promotion',
            'new_item' => 'New Promotion',
            'view_item' => 'View Promotion',
            'search_items' => 'Search Promotions',
            'not_found' => 'No promotions found',
            'not_found_in_trash' => 'No promotions found in trash',
            'all_items' => 'All Promotions',
            'menu_name' => 'Promotions',
        ),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'rest_base' => 'promotions',
        'menu_position' => 21,
        'menu_icon' => 'dashicons-megaphone',
        'supports' => array('title', 'editor', 'thumbnail', 'page-attributes'),
        'has_archive' => false,
        'rewrite' => array('slug' => 'promotion'),
        'capability_type' => 'post',
    ));
}

/**
 * Add custom columns to Dealers admin list
 */
add_filter('manage_cnf_dealer_posts_columns', 'cnf_dealer_columns');
function cnf_dealer_columns($columns) {
    $new_columns = array(
        'cb' => $columns['cb'],
        'title' => 'Name',
        'sales_area' => 'Sales Area',
        'phone' => 'Phone',
        'website' => 'Website',
        'date' => 'Date',
    );
    return $new_columns;
}

add_action('manage_cnf_dealer_posts_custom_column', 'cnf_dealer_column_content', 10, 2);
function cnf_dealer_column_content($column, $post_id) {
    switch ($column) {
        case 'sales_area':
            echo esc_html(get_post_meta($post_id, 'sales_area', true));
            break;
        case 'phone':
            echo esc_html(get_post_meta($post_id, 'phone', true));
            break;
        case 'website':
            $website = get_post_meta($post_id, 'website', true);
            if ($website) {
                echo '<a href="https://' . esc_attr($website) . '" target="_blank">' . esc_html($website) . '</a>';
            }
            break;
    }
}

/**
 * Add custom columns to Promotions admin list
 */
add_filter('manage_cnf_promotion_posts_columns', 'cnf_promotion_columns');
function cnf_promotion_columns($columns) {
    $new_columns = array(
        'cb' => $columns['cb'],
        'title' => 'Title',
        'description' => 'Description',
        'icon' => 'Icon',
        'menu_order' => 'Order',
        'date' => 'Date',
    );
    return $new_columns;
}

add_action('manage_cnf_promotion_posts_custom_column', 'cnf_promotion_column_content', 10, 2);
function cnf_promotion_column_content($column, $post_id) {
    switch ($column) {
        case 'description':
            $desc = get_post_meta($post_id, 'description', true);
            echo esc_html(wp_trim_words($desc, 10));
            break;
        case 'icon':
            echo esc_html(get_post_meta($post_id, 'icon_name', true));
            break;
        case 'menu_order':
            $post = get_post($post_id);
            echo esc_html($post->menu_order);
            break;
    }
}

/**
 * Add meta boxes for Dealer fields
 */
add_action('add_meta_boxes', 'cnf_add_dealer_meta_boxes');
function cnf_add_dealer_meta_boxes() {
    add_meta_box(
        'cnf_dealer_details',
        'Dealer Details',
        'cnf_dealer_meta_box_callback',
        'cnf_dealer',
        'normal',
        'high'
    );
}

function cnf_dealer_meta_box_callback($post) {
    wp_nonce_field('cnf_dealer_meta_box', 'cnf_dealer_meta_box_nonce');

    $name = get_post_meta($post->ID, 'name', true);
    $address = get_post_meta($post->ID, 'address', true);
    $sales_area = get_post_meta($post->ID, 'sales_area', true);
    $phone = get_post_meta($post->ID, 'phone', true);
    $website = get_post_meta($post->ID, 'website', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="cnf_dealer_name">Name</label></th>
            <td><input type="text" id="cnf_dealer_name" name="cnf_dealer_name" value="<?php echo esc_attr($name); ?>" class="large-text" /></td>
        </tr>
        <tr>
            <th><label for="cnf_dealer_address">Address</label></th>
            <td><textarea id="cnf_dealer_address" name="cnf_dealer_address" rows="3" class="large-text"><?php echo esc_textarea($address); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="cnf_dealer_sales_area">Sales Area</label></th>
            <td><input type="text" id="cnf_dealer_sales_area" name="cnf_dealer_sales_area" value="<?php echo esc_attr($sales_area); ?>" class="large-text" /></td>
        </tr>
        <tr>
            <th><label for="cnf_dealer_phone">Phone</label></th>
            <td><input type="text" id="cnf_dealer_phone" name="cnf_dealer_phone" value="<?php echo esc_attr($phone); ?>" class="large-text" /></td>
        </tr>
        <tr>
            <th><label for="cnf_dealer_website">Website</label></th>
            <td><input type="text" id="cnf_dealer_website" name="cnf_dealer_website" value="<?php echo esc_attr($website); ?>" class="large-text" placeholder="example.com" /></td>
        </tr>
    </table>
    <?php
}

/**
 * Add meta boxes for Promotion fields
 */
add_action('add_meta_boxes', 'cnf_add_promotion_meta_boxes');
function cnf_add_promotion_meta_boxes() {
    add_meta_box(
        'cnf_promotion_details',
        'Promotion Details',
        'cnf_promotion_meta_box_callback',
        'cnf_promotion',
        'normal',
        'high'
    );
}

function cnf_promotion_meta_box_callback($post) {
    wp_nonce_field('cnf_promotion_meta_box', 'cnf_promotion_meta_box_nonce');

    $title = get_post_meta($post->ID, 'title', true);
    $description = get_post_meta($post->ID, 'description', true);
    $icon_name = get_post_meta($post->ID, 'icon_name', true);
    $background_image = get_post_meta($post->ID, 'background_image', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="cnf_promotion_title">Title</label></th>
            <td><input type="text" id="cnf_promotion_title" name="cnf_promotion_title" value="<?php echo esc_attr($title); ?>" class="large-text" /></td>
        </tr>
        <tr>
            <th><label for="cnf_promotion_description">Description</label></th>
            <td><textarea id="cnf_promotion_description" name="cnf_promotion_description" rows="3" class="large-text"><?php echo esc_textarea($description); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="cnf_promotion_icon">Icon Name</label></th>
            <td>
                <input type="text" id="cnf_promotion_icon" name="cnf_promotion_icon" value="<?php echo esc_attr($icon_name); ?>" class="regular-text" />
                <p class="description">e.g., shield, wrench, trophy, heart, star</p>
            </td>
        </tr>
        <tr>
            <th><label for="cnf_promotion_bg">Background Image</label></th>
            <td>
                <input type="text" id="cnf_promotion_bg" name="cnf_promotion_bg" value="<?php echo esc_attr($background_image); ?>" class="large-text" />
                <p class="description">Path to background image (e.g., /uploads/cnf-hero.webp)</p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Save dealer meta data
 */
add_action('save_post_cnf_dealer', 'cnf_save_dealer_meta');
function cnf_save_dealer_meta($post_id) {
    if (!isset($_POST['cnf_dealer_meta_box_nonce']) || !wp_verify_nonce($_POST['cnf_dealer_meta_box_nonce'], 'cnf_dealer_meta_box')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = array('name', 'address', 'sales_area', 'phone', 'website');
    foreach ($fields as $field) {
        $key = 'cnf_dealer_' . $field;
        if (isset($_POST[$key])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$key]));
        }
    }
}

/**
 * Save promotion meta data
 */
add_action('save_post_cnf_promotion', 'cnf_save_promotion_meta');
function cnf_save_promotion_meta($post_id) {
    if (!isset($_POST['cnf_promotion_meta_box_nonce']) || !wp_verify_nonce($_POST['cnf_promotion_meta_box_nonce'], 'cnf_promotion_meta_box')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['cnf_promotion_title'])) {
        update_post_meta($post_id, 'title', sanitize_text_field($_POST['cnf_promotion_title']));
    }
    if (isset($_POST['cnf_promotion_description'])) {
        update_post_meta($post_id, 'description', sanitize_textarea_field($_POST['cnf_promotion_description']));
    }
    if (isset($_POST['cnf_promotion_icon'])) {
        update_post_meta($post_id, 'icon_name', sanitize_text_field($_POST['cnf_promotion_icon']));
    }
    if (isset($_POST['cnf_promotion_bg'])) {
        update_post_meta($post_id, 'background_image', sanitize_text_field($_POST['cnf_promotion_bg']));
    }
}
