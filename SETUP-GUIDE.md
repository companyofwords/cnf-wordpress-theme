# WordPress Headless Setup Guide for CNF Mini Dumpers

## 🎯 Goal

Transform your React Router 7 app from using static data (`app/lib/constants/full-wp.ts`) to a fully functional WordPress headless CMS with **identical** data structure and API responses.

---

## 📊 Quick Reference

**Current State:** React app → Static TypeScript data (`full-wp.ts`)  
**Target State:** React app → WordPress REST API → WordPress database

**Critical Endpoint:** `/wp-json/app/v1/bootstrap`  
**Must Return:** Exact same JSON structure as exported from `full-wp.ts`

---

## Phase 1: WordPress Installation (30 minutes)

### Step 1.1: Install WordPress

```bash
# Option A: Using LocalWP (Recommended for development)
# Download from: https://localwp.com/
# Create new site: cnf-minidumpers
# PHP 8.0+, MySQL 5.7+

# Option B: Using WP-CLI
wp core download
wp config create --dbname=cnf_wp --dbuser=root --dbpass=password
wp core install \
  --url=http://cnf.local \
  --title="CNF Mini Dumpers" \
  --admin_user=admin \
  --admin_password=password \
  --admin_email=admin@example.com
```

### Step 1.2: Install Required Plugins

```bash
# Install Pods Framework (CRITICAL - for custom post types)
wp plugin install pods --activate

# Install Ninja Forms (for contact forms)
wp plugin install ninja-forms --activate

# Install JWT Authentication (for API security)
wp plugin install jwt-authentication-for-wp-rest-api --activate

# Optional: Yoast SEO (for meta tags)
wp plugin install wordpress-seo --activate
```

**Verify Installation:**
- Go to WordPress admin: `/wp-admin/`
- Check Plugins → Installed Plugins
- All should show "Active"

---

## Phase 2: Permalinks & Basic Settings (5 minutes)

### Step 2.1: Set Permalinks

**CRITICAL:** WordPress REST API requires pretty permalinks!

1. Go to **Settings → Permalinks**
2. Select **Post name** structure: `/%postname%/`
3. Click **Save Changes**

### Step 2.2: Enable REST API

```bash
# Test REST API is working
curl http://cnf.local/wp-json/
# Should return JSON with routes
```

If you get 404, flush permalinks:
```bash
wp rewrite flush
```

---

## Phase 3: Install Custom Theme (15 minutes)

### Step 3.1: Create Theme Directory

```bash
cd wp-content/themes/
mkdir cnf-headless
cd cnf-headless
```

### Step 3.2: Create Minimum Required Files

**style.css** (Theme header):
```css
/*
Theme Name: CNF Headless
Theme URI: https://cnfminidumper.co.uk
Description: Headless WordPress theme for CNF Mini Dumpers React app
Author: Wordsco
Version: 1.0.0
Text Domain: cnf-headless
*/

/* Theme is headless - no frontend styles needed */
```

**functions.php** (Bootstrap endpoint):
```php
<?php
/**
 * CNF Headless Theme
 * Custom REST API endpoints for React Router 7 frontend
 */

// Require custom REST API endpoints
require_once get_template_directory() . '/inc/rest-api/bootstrap.php';

// Theme setup
function cnf_theme_setup() {
    // Add theme support
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'cnf-headless'),
        'footer' => __('Footer Menu', 'cnf-headless'),
    ));
}
add_action('after_setup_theme', 'cnf_theme_setup');

// Enable CORS for API requests
function cnf_add_cors_http_header() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
}
add_action('init', 'cnf_add_cors_http_header');
```

**index.php** (Fallback template):
```php
<?php
/**
 * This theme is headless and does not render frontend pages.
 * All content is served via REST API to React Router 7 frontend.
 */

header('HTTP/1.1 404 Not Found');
echo '<h1>This is a headless WordPress installation</h1>';
echo '<p>Please access the React frontend application.</p>';
exit;
```

### Step 3.3: Activate Theme

```bash
wp theme activate cnf-headless
```

Verify: Go to **Appearance → Themes** - "CNF Headless" should be active.

---

## Phase 4: Create Custom Post Types with Pods (45 minutes)

### Step 4.1: Create cnf_machine Post Type

**Via WordPress Admin (Recommended for first time):**

1. Go to **Pods Admin → Add New**
2. **Content Type:** Post Type
3. **Name:** `cnf_machine`
4. **Label (plural):** `CNF Machines`
5. **Label (singular):** `CNF Machine`

**Advanced Options:**
- ✅ Public: Yes
- ✅ Has Archive: Yes
- ✅ Menu Icon:** `dashicons-admin-tools`
- ✅ Supports: Title, Editor, Thumbnail, Excerpt
- Slug: `machines` (URL: `/machines/t50`)

**REST API Settings:**
- ✅ Show in REST API: Yes
- REST Base: `cnf_machine`

Click **Save Pod**

### Step 4.2: Add Fields to cnf_machine

Now add these fields to the `cnf_machine` pod:

#### Basic Information Fields

| Field Name | Type | Required | Description |
|------------|------|----------|-------------|
| `title` | Plain Text | No | Marketing title (e.g., "COMPACT POWER") |
| `description` | Paragraph | No | Marketing description |
| `specifications` | WYSIWYG | No | General specifications text |
| `dimensions` | Plain Text | No | L x W x H |
| `weight` | Number | No | Weight in kg |
| `power_requirements` | Plain Text | No | Engine/power info |
| `capacity` | Plain Text | No | Load capacity |
| `load_capacity` | Plain Text | No | Detailed capacity (e.g., "500kg (1102 lbs)") |
| `operating_weight` | Plain Text | No | Operating weight |
| `engine` | Plain Text | No | Engine model |
| `track_width` | Plain Text | No | Track width measurement |
| `speed` | Plain Text | No | Speed range |
| `performance_metrics` | WYSIWYG | No | Performance details (HTML list) |

#### Array/Repeater Fields

| Field Name | Type | Settings | Description |
|------------|------|----------|-------------|
| `selling_points` | Paragraph (repeatable) | Multiple: Yes | List of selling points |
| `technical_specs` | Plain Text (repeatable) | Multiple: Yes | List of technical specifications |
| `actions` | Checkboxes | Options: high-tip, self-loading, swivel, high-tip-self-loading | Machine capabilities |

#### Gallery & Media Fields

| Field Name | Type | Settings | Description |
|------------|------|----------|-------------|
| `gallery_images` | File/Image (repeatable) | Multiple: Yes, File Type: Image | Multiple gallery images |
| `datasheet_pdf` | File | File Type: Document | Downloadable PDF |
| `brochure_url` | Website URL | | Link to brochure |
| `video_url` | Website URL | | YouTube/Vimeo link |
| `diagram` | File/Image | File Type: Image | Technical diagram image |

#### Machine Characteristics

| Field Name | Type | Description |
|------------|------|-------------|
| `machine_weight_kg` | Number | Weight for filtering |
| `machine_width_cm` | Number | Width for filtering |
| `bg_color` | Plain Text | "light" or "dark" for UI |
| `next_section_id` | Plain Text | ID of next machine section |

#### Complex JSON Fields

For complex nested data (like `floating_stats`), use **Paragraph Text** field and store JSON:

| Field Name | Type | Description |
|------------|------|-------------|
| `floating_stats` | Code (JSON) | Array of floating stat objects |
| `recommended_uses` | Pick (Relationship) | Related to `cnf_use` post type |

**Example JSON for floating_stats:**
```json
[
  {
    "type": "link",
    "position": "bottom-right",
    "bg_color": "red",
    "delay": 1.2,
    "text": "HIRE ME",
    "href": "https://fasthireuk.co.uk/",
    "external": true
  }
]
```

### Step 4.3: Create Taxonomies for cnf_machine

Create these taxonomies:

**machine_category** (Hierarchical):
1. Pods Admin → Add New
2. Content Type: Taxonomy
3. Name: `machine_category`
4. Associate with: `cnf_machine`
5. Hierarchical: Yes

**machine_industry** (Non-hierarchical - Tags):
1. Same process
2. Name: `machine_industry`
3. Hierarchical: No

### Step 4.4: Create cnf_use Post Type

Repeat the process from Step 4.1 for `cnf_use`:

**Fields for cnf_use:**

| Field Name | Type | Description |
|------------|------|-------------|
| `intro` | Paragraph | Introduction text |
| `applications` | Plain Text (repeatable) | List of applications |
| `case_study_title` | Plain Text | Case study title |
| `case_study_content` | WYSIWYG | Case study content |
| `case_study_results` | WYSIWYG | Results (HTML list) |
| `before_image` | File/Image | Before photo |
| `after_image` | File/Image | After photo |
| `client_testimonial` | Paragraph | Client quote |
| `related_machines` | Pick (Relationship) | Related to `cnf_machine` |
| `industry` | Plain Text | Industry name |
| `application_type` | Plain Text | Application category |

**Taxonomy:**
- `use_category` (hierarchical)

### Step 4.5: Create faq Post Type

**Fields for faq:**

| Field Name | Type | Description |
|------------|------|-------------|
| `question` | Plain Text | The question |
| `answer` | WYSIWYG | The answer (supports HTML) |
| `category` | Plain Text | Category name |
| `key_question` | Yes/No | Featured question |
| `order` | Number | Display order |

**Taxonomy:**
- `faq_category` (hierarchical)

---

## Phase 5: Create Content (2-3 hours)

### Step 5.1: Create Pages

Create these 8 pages via **Pages → Add New**:

1. **Home** (`/`)
   - Use default WordPress "Home" page
   - Set as static front page (Settings → Reading)
   
2. **About** (`/about`)
3. **Contact** (`/contact`)
4. **Dealers** (`/dealers`)
5. **Mini Dumpers** (`/minidumpers`)
6. **FAQs** (`/faqs`)
7. **Privacy Policy** (`/privacy-policy`)
8. **Terms & Conditions** (`/terms-and-conditions`)

**For each page:**
- Add title
- Add content (can copy from `full-wp.ts`)
- Add featured image if needed
- Set to "Published"

### Step 5.2: Create cnf_machine Posts

Create 4 machine posts via **CNF Machines → Add New**:

**T50 Machine:**
- **Post Title:** T50 - 500kg Tracked Mini Dumper
- **Slug:** `t50`
- **Featured Image:** Upload T50 main image
- Fill all Pods fields (refer to `full-wp.ts` lines 1635-1815)

**Important:** Keep track of the WordPress Post IDs!
- T50 will likely be ID: 31 (but could be different)
- Use slugs (`t50`) instead of IDs in API responses

Repeat for:
- **T70** (slug: `t70`)
- **T95** (slug: `t95`)
- **T150** (slug: `t150`)

### Step 5.3: Create cnf_use Posts

Create 8 use case posts (refer to `full-wp.ts` lines 2391-2844):
- Construction & Groundworks
- Landscaping & Gardening
- Agriculture & Farming
- Tool & Plant Hire Centres
- Property Development & Renovation
- Municipal & Local Authority Work
- Housebuilding & Extensions
- Demolition & Site Clearance

**Link Related Machines:**
Use the "Pick" relationship field to link machines to use cases.

### Step 5.4: Create FAQ Posts

Create 20+ FAQ posts (refer to `full-wp.ts` lines 2850-end):
- Set "order" field for display sequence
- Check "key_question" for featured FAQs
- Assign to appropriate `faq_category`

### Step 5.5: Upload Media

Upload all images to **Media Library**:
- Machine images (T50, T70, T95, T150)
- Use case images
- Diagrams
- PDFs (datasheets, brochures)

**Set proper Alt Text for all images!**

---

## Phase 6: Create Navigation Menus (15 minutes)

### Step 6.1: Primary Menu

1. Go to **Appearance → Menus**
2. Create new menu: "Primary Menu"
3. Assign to location: **Primary**

**Menu Structure:**
```
- About (/about)
- Mini Dumpers (/minidumpers)
  - T50 - 500kg (/minidumpers/t50)
  - T70 - 700kg (/minidumpers/t70)
  - T95 - 950kg (/minidumpers/t95)
  - T150 - 1500kg (/minidumpers/t150)
- Specialist Dealers (/dealers)
- News (/news)
```

### Step 6.2: Footer Menu

Create new menu: "Footer Menu"
Assign to location: **Footer**

**Menu Structure:**
```
- About Us (/about)
- Our Machines (/minidumpers)
- Applications (/applications)
- Contact (/contact)
- Privacy Policy (/privacy-policy)
- Terms & Conditions (/terms-and-conditions)
```

---

## Phase 7: Create Custom REST API Endpoint (60 minutes)

This is the most critical part - creating `/wp-json/app/v1/bootstrap` endpoint.

### Step 7.1: Create Bootstrap Endpoint File

Create: `wp-content/themes/cnf-headless/inc/rest-api/bootstrap.php`

```php
<?php
/**
 * Bootstrap REST API Endpoint
 * Returns all site data in one request - matches full-wp.ts structure
 */

add_action('rest_api_init', function () {
    register_rest_route('app/v1', '/bootstrap', array(
        'methods' => 'GET',
        'callback' => 'cnf_get_bootstrap_data',
        'permission_callback' => '__return_true',
    ));
});

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

function cnf_get_site_settings() {
    return array(
        'title' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'url' => get_site_url(),
        'email' => get_bloginfo('admin_email'),
        'timezone' => get_option('timezone_string'),
        'date_format' => get_option('date_format'),
        'time_format' => get_option('time_format'),
        'start_of_week' => get_option('start_of_week'),
        'language' => get_locale(),
        'use_smilies' => (bool) get_option('use_smilies'),
        'default_category' => get_option('default_category'),
        'default_post_format' => get_option('default_post_format'),
        'posts_per_page' => get_option('posts_per_page'),
        'default_ping_status' => get_option('default_ping_status'),
        'default_comment_status' => get_option('default_comment_status'),
        'show_words_logo' => true,
    );
}

function cnf_get_menus() {
    $menus = array();
    
    $locations = get_nav_menu_locations();
    
    foreach ($locations as $location => $menu_id) {
        $menu = wp_get_nav_menu_object($menu_id);
        if (!$menu) continue;
        
        $menu_items = wp_get_nav_menu_items($menu_id);
        
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

function cnf_format_menu_items($menu_items) {
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

function cnf_get_pages() {
    $pages_array = array();
    
    $page_slugs = array('home', 'about', 'contact', 'dealers', 'minidumpers', 'faqs', 'privacy-policy', 'terms-and-conditions');
    
    foreach ($page_slugs as $slug) {
        $page = get_page_by_path($slug);
        if (!$page && $slug === 'home') {
            $page = get_page_by_path('');
        }
        
        if ($page) {
            $pages_array[$slug] = cnf_format_page($page);
        }
    }
    
    return $pages_array;
}

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
                    'post_title' => $value['post_title'] ?? '',
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

function cnf_get_machines() {
    $machines = array();
    
    $args = array(
        'post_type' => 'cnf_machine',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
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

function cnf_format_machine($post) {
    $featured_image = get_the_post_thumbnail_url($post->ID, 'full');
    
    $machine = array(
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
    
    return $machine;
}

function cnf_get_uses() {
    $uses = array();
    
    $args = array(
        'post_type' => 'cnf_use',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
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
        '_embedded' => array(),
    );
}

function cnf_get_faqs() {
    $faqs = array();
    
    $args = array(
        'post_type' => 'faq',
        'posts_per_page' => -1,
        'orderby' => 'meta_value_num',
        'meta_key' => 'order',
        'order' => 'ASC',
    );
    
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
        'comment_status' => $post->post_status,
        'ping_status' => $post->ping_status,
        'template' => '',
        'meta' => array(),
        'pods' => cnf_get_pods_fields($post->ID),
        'faq_category' => wp_get_post_terms($post->ID, 'faq_category', array('fields' => 'ids')),
        '_embedded' => array(),
    );
}
```

---

## Phase 8: Test WordPress API (15 minutes)

### Step 8.1: Test Bootstrap Endpoint

Open in browser or use curl:

```bash
curl http://cnf.local/wp-json/app/v1/bootstrap
```

**Expected Response:**
```json
{
  "siteSettings": {
    "title": "CNF Mini Dumpers",
    "description": "...",
    "url": "http://cnf.local",
    ...
  },
  "menus": {
    "primary": {...},
    "footer": {...}
  },
  "pages": {
    "home": {...},
    "about": {...},
    ...
  },
  "cnfMachines": [...],
  "cnfUses": [...],
  "faqs": [...]
}
```

### Step 8.2: Validate Response Structure

Compare response to `full-wp.ts`:

1. Open `app/lib/constants/full-wp.ts`
2. Compare exported structure
3. Check for missing fields
4. Verify IDs match (or use slugs instead)

---

## Phase 9: Connect React App to WordPress (10 minutes)

### Step 9.1: Update DataService Config

Edit `app/root.tsx`:

```typescript
// Change from:
const dataService = await DataService.getInstance({
  source: DataSourceType.STATIC,
  cacheTimeout: 5 * 60 * 1000,
});

// To:
const dataService = await DataService.getInstance({
  source: DataSourceType.API,
  apiUrl: "http://cnf.local/wp-json/app/v1/bootstrap",
  cacheTimeout: 5 * 60 * 1000,
});
```

### Step 9.2: Start React App

```bash
cd /path/to/cnf/
pnpm dev
```

### Step 9.3: Test Frontend

1. Open http://localhost:5173
2. Check browser console for errors
3. Verify machines display correctly
4. Test navigation
5. Check images load

---

## Phase 10: Debugging & Troubleshooting (Ongoing)

### Common Issue 1: ID Mismatches

**Problem:** React app expects machine ID 31, but WordPress created ID 1.

**Solution A - Use Slugs (Recommended):**

Update React app to use slugs instead of IDs:

```typescript
// Instead of:
const machine = machines.find(m => m.id === 31);

// Use:
const machine = machines.find(m => m.slug === 't50');
```

**Solution B - ID Mapping:**

Add ID mapping to API response:

```php
function cnf_map_ids($wordpress_id, $expected_id) {
    // Map WordPress IDs to expected IDs
    $id_map = array(
        1 => 31,  // T50
        2 => 32,  // T70
        3 => 33,  // T95
        4 => 34,  // T150
    );
    return $id_map[$wordpress_id] ?? $wordpress_id;
}
```

### Common Issue 2: Missing Pods Fields

**Problem:** API returns empty `pods` object.

**Check:**
1. Is Pods plugin activated?
2. Are fields assigned to the post type?
3. Is REST API enabled for the pod?

**Debug:**
```php
// Add to bootstrap.php
error_log('Pod fields for post ' . $post->ID . ': ' . print_r($pod->fields(), true));
```

### Common Issue 3: Images Not Loading

**Problem:** Image paths don't match.

**Solution:**
Ensure API returns full URLs:

```php
$featured_image = get_the_post_thumbnail_url($post->ID, 'full');
// Returns: http://cnf.local/wp-content/uploads/2024/01/image.jpg
```

React app should handle both:
- `/uploads/image.jpg` (static)
- `http://cnf.local/wp-content/uploads/2024/01/image.jpg` (WordPress)

### Common Issue 4: CORS Errors

**Problem:** React app can't access WordPress API.

**Solution:**
Add CORS headers to `functions.php`:

```php
function cnf_add_cors_http_header() {
    // Allow React app origin
    header("Access-Control-Allow-Origin: http://localhost:5173");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
}
add_action('init', 'cnf_add_cors_http_header');
add_action('rest_api_init', 'cnf_add_cors_http_header');
```

### Common Issue 5: Array Fields Not Working

**Problem:** Repeater fields (selling_points, technical_specs) return wrong format.

**Solution:**
Handle Pods repeater fields correctly:

```php
// In cnf_get_pods_fields function
if ($field_data['type'] === 'paragraph' && $field_data['repeatable']) {
    // Get all repeater values
    $repeater_value = $pod->field($field_name);
    $pods_data[$field_name] = is_array($repeater_value) ? $repeater_value : array();
}
```

---

## Quick Checklist

**WordPress Setup:**
- [ ] WordPress installed and running
- [ ] Pods plugin activated
- [ ] Ninja Forms plugin activated
- [ ] JWT Auth plugin activated (optional)
- [ ] CNF Headless theme activated
- [ ] Permalinks set to "Post name"

**Content Created:**
- [ ] 4 cnf_machine posts (T50, T70, T95, T150)
- [ ] 8 cnf_use posts
- [ ] 20+ FAQ posts
- [ ] 8 pages (Home, About, Contact, etc.)
- [ ] Primary menu with hierarchy
- [ ] Footer menu

**Pods Configuration:**
- [ ] cnf_machine post type with all fields
- [ ] cnf_use post type with all fields
- [ ] faq post type with all fields
- [ ] machine_category taxonomy
- [ ] machine_industry taxonomy
- [ ] use_category taxonomy
- [ ] faq_category taxonomy

**API Endpoint:**
- [ ] `/wp-json/app/v1/bootstrap` returns valid JSON
- [ ] Response structure matches `full-wp.ts`
- [ ] All fields present in response
- [ ] Images return full URLs
- [ ] Taxonomies included

**React Integration:**
- [ ] DataService configured with WordPress API URL
- [ ] React app loads without errors
- [ ] All content displays correctly
- [ ] Navigation works
- [ ] Images load properly

---

## Estimated Time Investment

| Phase | Time | Difficulty |
|-------|------|------------|
| WordPress Installation | 30 min | Easy |
| Theme Setup | 15 min | Easy |
| Pods Configuration | 45 min | Medium |
| Content Creation | 2-3 hours | Easy but tedious |
| Menu Creation | 15 min | Easy |
| API Endpoint | 60 min | Hard |
| Testing | 15 min | Easy |
| React Integration | 10 min | Easy |
| Debugging | Variable | Medium-Hard |
| **TOTAL** | **5-6 hours** | **Medium** |

---

## Next Steps

1. **Follow this guide step-by-step**
2. **Document any ID mismatches** (keep a spreadsheet)
3. **Take screenshots** of Pods field configurations
4. **Export Pods configuration** (Pods Admin → Components → Export)
5. **Commit theme files to Git** for version control

---

## Need Help?

**Common Resources:**
- Pods Documentation: https://pods.io/docs/
- WP REST API: https://developer.wordpress.org/rest-api/
- React Router 7: https://reactrouter.com

**Debugging Steps:**
1. Check WordPress error log: `wp-content/debug.log`
2. Check browser console for JS errors
3. Use Postman to test API endpoint directly
4. Add `error_log()` statements to PHP code
5. Verify Pods plugin version (latest stable)

---

Built by [Wordsco](https://wordsco.uk) - Good luck! 🚀
