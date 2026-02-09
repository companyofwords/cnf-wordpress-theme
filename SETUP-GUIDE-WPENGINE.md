# WordPress Headless Setup Guide for CNF Mini Dumpers
## WP Engine Development Site

---

## 🎯 Goal

Transform your React Router 7 app from using static data (`app/lib/constants/full-wp.ts`) to a fully functional WordPress headless CMS hosted on **WP Engine** with **identical** data structure and API responses.

---

## 📊 Quick Reference

**Current State:** React app → Static TypeScript data (`full-wp.ts`)
**Target State:** React app → WP Engine WordPress REST API → WordPress database

**Development Site:** `westgategroup.wpenginepowered.com`
**Critical Endpoint:** `https://westgategroup.wpenginepowered.com/wp-json/app/v1/bootstrap`
**Must Return:** Exact same JSON structure as exported from `full-wp.ts`

---

## Prerequisites: Getting WP Engine Access

Before starting, you need SSH access to your WP Engine development site.

### Step 0.1: Request SSH Access from WP Engine

1. **Log in to WP Engine User Portal:** https://my.wpengine.com/
2. **Navigate to:** Your Site → `westgategroup` → **Environment** (Development)
3. **Get SSH Credentials:**
   - Click **"SSH Gateway"** or **"SFTP"** section
   - Note your **SSH hostname** (e.g., `westgategroup@westgategroup.ssh.wpengine.net`)
   - Copy your **SSH password** or set up **SSH key authentication** (recommended)

### Step 0.2: Set Up SSH Key Authentication (Recommended)

**Generate SSH key (if you don't have one):**
```bash
# Generate new SSH key
ssh-keygen -t ed25519 -C "your_email@example.com"

# Save to default location: ~/.ssh/id_ed25519
# Set a passphrase (or leave empty)

# Copy public key to clipboard
cat ~/.ssh/id_ed25519.pub | pbcopy
```

**Add SSH key to WP Engine:**
1. Go to **User Portal → User Profile → SSH Keys**
2. Click **"Add SSH Key"**
3. Paste your public key (`id_ed25519.pub` content)
4. Save

### Step 0.3: Test SSH Connection

```bash
# Test connection (replace with your actual SSH user)
ssh westgategroup@westgategroup.ssh.wpengine.net

# You should see:
# "Welcome to WP Engine!"
# Your shell will be in the WordPress root directory
```

**WP Engine WordPress Root:**
```
/home/wpe-user/sites/westgategroup/
```

---

## Phase 1: WP Engine Environment Overview (5 minutes)

### Understanding WP Engine Structure

WP Engine has **WordPress pre-installed** with the following structure:

```
/home/wpe-user/sites/westgategroup/
├── wp-admin/                    # WordPress admin (managed by WP Engine)
├── wp-includes/                 # WordPress core (managed by WP Engine)
├── wp-content/                  # Your customizations
│   ├── mu-plugins/              # Must-use plugins (WP Engine + yours)
│   ├── plugins/                 # Your plugins
│   ├── themes/                  # Your themes
│   └── uploads/                 # Media files
├── wp-config.php                # WordPress config (managed by WP Engine)
└── ...
```

**Important WP Engine Notes:**
- ✅ WordPress core is managed (auto-updates)
- ✅ PHP 8.0+ already configured
- ✅ MySQL database already set up
- ✅ SSL/HTTPS enabled by default
- ✅ Object caching (Redis) available
- ✅ WP-CLI pre-installed
- ⚠️ Can't edit `wp-config.php` directly
- ⚠️ Some plugins may be restricted for security

### Step 1.1: Connect to WP Engine via SSH

```bash
# Connect to WP Engine
ssh westgategroup@westgategroup.ssh.wpengine.net

# Navigate to WordPress root (you should already be here)
pwd
# Should show: /home/wpe-user/sites/westgategroup

# Check WordPress installation
wp core version
# Should show: 6.x.x
```

---

## Phase 2: Install Required Plugins via WP-CLI (15 minutes)

### Step 2.1: Install Pods Framework

```bash
# SSH into WP Engine
ssh westgategroup@westgategroup.ssh.wpengine.net

# Install Pods Framework (CRITICAL - for custom post types)
wp plugin install pods --activate

# Verify installation
wp plugin list | grep pods
# Should show: pods | active
```

### Step 2.2: Install Ninja Forms

```bash
# Install Ninja Forms (for contact forms)
wp plugin install ninja-forms --activate

# Verify installation
wp plugin list | grep ninja-forms
```

### Step 2.3: Install JWT Authentication (Optional)

```bash
# Install JWT Authentication (for API security - optional for now)
wp plugin install jwt-authentication-for-wp-rest-api --activate

# Verify installation
wp plugin list | grep jwt-authentication
```

### Step 2.4: Install Yoast SEO (Optional)

```bash
# Optional: Yoast SEO (for meta tags)
wp plugin install wordpress-seo --activate
```

### Step 2.5: Verify All Plugins Active

```bash
# List all active plugins
wp plugin list --status=active

# Should see:
# - pods
# - ninja-forms
# - jwt-authentication-for-wp-rest-api (optional)
# - wordpress-seo (optional)
```

**Alternative: Install via WP Admin**
If SSH doesn't work, install plugins via WP Admin:
1. Go to `https://westgategroup.wpenginepowered.com/wp-admin/`
2. Navigate to **Plugins → Add New**
3. Search and install: Pods, Ninja Forms, JWT Auth
4. Click **Activate** for each

---

## Phase 3: Configure WordPress Settings (5 minutes)

### Step 3.1: Set Permalinks (CRITICAL)

WordPress REST API requires pretty permalinks.

```bash
# Via WP-CLI
wp rewrite structure '/%postname%/'
wp rewrite flush

# Verify permalinks work
curl https://westgategroup.wpenginepowered.com/wp-json/
# Should return JSON with routes
```

**Alternative: Via WP Admin**
1. Go to **Settings → Permalinks**
2. Select **"Post name"** structure
3. Click **Save Changes**

### Step 3.2: Set Site Title and Description

```bash
# Set site title
wp option update blogname "CNF Mini Dumpers"

# Set site description
wp option update blogdescription "Premium Italian Tracked Mini Dumpers"

# Set admin email
wp option update admin_email "info@cnfminidumper.com"
```

### Step 3.3: Configure Reading Settings

```bash
# Set posts per page
wp option update posts_per_page 10

# Verify settings
wp option get blogname
wp option get blogdescription
```

---

## Phase 4: Deploy Custom Theme to WP Engine (20 minutes)

You have **3 options** for deploying your theme to WP Engine:

### Option A: SFTP Upload (Easiest for Development)

**Using FileZilla or Cyberduck:**
1. **Host:** `westgategroup.ssh.wpengine.net`
2. **Protocol:** SFTP
3. **Username:** `westgategroup`
4. **Password:** Your WP Engine SSH password
5. **Port:** 22

**Upload theme:**
1. Navigate to `/wp-content/themes/`
2. Create folder: `cnf-headless`
3. Upload theme files (we'll create these next)

### Option B: SCP via Terminal (Recommended)

```bash
# From your LOCAL machine (not SSH), navigate to wp-theme folder
cd /Users/neil/Documents/Wordsco/cnf/wp-theme

# Create temporary theme structure
mkdir -p cnf-headless-theme
cd cnf-headless-theme

# We'll create theme files here, then upload
```

### Option C: WP Engine Git Push (Advanced)

WP Engine supports Git push deployment. See: https://wpengine.com/support/git/

### Step 4.1: Create Theme Files Locally

**Create these files locally first:**

```bash
# On your LOCAL machine
cd /Users/neil/Documents/Wordsco/cnf/wp-theme
mkdir -p cnf-headless-theme
cd cnf-headless-theme
```

**style.css:**
```bash
cat > style.css << 'EOF'
/*
Theme Name: CNF Headless
Theme URI: https://cnfminidumper.co.uk
Description: Headless WordPress theme for CNF Mini Dumpers React app
Author: Wordsco
Version: 1.0.0
Text Domain: cnf-headless
Requires at least: 6.0
Requires PHP: 8.0
*/

/* Theme is headless - no frontend styles needed */
EOF
```

**index.php:**
```bash
cat > index.php << 'EOF'
<?php
/**
 * This theme is headless and does not render frontend pages.
 * All content is served via REST API to React Router 7 frontend.
 */

header('HTTP/1.1 404 Not Found');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Headless WordPress</title>
    <style>
        body { font-family: system-ui; max-width: 600px; margin: 100px auto; text-align: center; }
        h1 { color: #ee2742; }
    </style>
</head>
<body>
    <h1>This is a headless WordPress installation</h1>
    <p>Please access the React frontend application at:</p>
    <p><a href="https://cnfminidumper.co.uk">https://cnfminidumper.co.uk</a></p>
</body>
</html>
EOF
```

**functions.php:**
```bash
cat > functions.php << 'EOF'
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

// Enable CORS for API requests from React app
function cnf_add_cors_http_header() {
    // Allow React app origins (development + production)
    $allowed_origins = array(
        'http://localhost:5173',
        'http://localhost:3000',
        'https://cnfminidumper.co.uk',
    );

    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    if (in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: $origin");
    }

    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");

    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        status_header(200);
        exit();
    }
}
add_action('init', 'cnf_add_cors_http_header');
add_action('rest_api_init', 'cnf_add_cors_http_header');
EOF
```

### Step 4.2: Create Bootstrap API Endpoint File

```bash
# Create inc/rest-api directory
mkdir -p inc/rest-api

# Copy the complete bootstrap.php from SETUP-GUIDE.md
# This is the 430-line PHP file (lines 449-878 from SETUP-GUIDE.md)
```

**Create `inc/rest-api/bootstrap.php`:**

I'll create this as a separate step since it's the complete 430-line PHP file from the original guide.

```bash
cat > inc/rest-api/bootstrap.php << 'EOF'
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
        'comment_status' => $post->comment_status,
        'ping_status' => $post->ping_status,
        'template' => '',
        'meta' => array(),
        'pods' => cnf_get_pods_fields($post->ID),
        'faq_category' => wp_get_post_terms($post->ID, 'faq_category', array('fields' => 'ids')),
        '_embedded' => array(),
    );
}
EOF
```

### Step 4.3: Upload Theme to WP Engine

**Option 1: SCP Upload (Terminal)**
```bash
# From your LOCAL machine in the cnf-headless-theme directory
cd /Users/neil/Documents/Wordsco/cnf/wp-theme/cnf-headless-theme

# Upload entire theme folder to WP Engine
scp -r * westgategroup@westgategroup.ssh.wpengine.net:sites/westgategroup/wp-content/themes/cnf-headless/

# Enter your SSH password when prompted
```

**Option 2: SFTP via FileZilla**
1. Connect to `westgategroup.ssh.wpengine.net` via SFTP
2. Navigate to `/wp-content/themes/`
3. Create folder `cnf-headless`
4. Upload all theme files

### Step 4.4: Activate Theme via WP-CLI

```bash
# SSH into WP Engine
ssh westgategroup@westgategroup.ssh.wpengine.net

# Activate the theme
wp theme activate cnf-headless

# Verify theme is active
wp theme list
# Should show: cnf-headless | active
```

**Alternative: Activate via WP Admin**
1. Go to `https://westgategroup.wpenginepowered.com/wp-admin/`
2. Navigate to **Appearance → Themes**
3. Activate **CNF Headless**

---

## Phase 5: Create Custom Post Types with Pods (45 minutes)

**Reference:** Use the Pods configuration from `SETUP-GUIDE.md` Phase 4.

You have 2 options:

### Option A: Via WordPress Admin (Recommended First Time)

Follow the exact steps from `SETUP-GUIDE.md` Phase 4.1-4.5:
1. Go to `https://westgategroup.wpenginepowered.com/wp-admin/`
2. Navigate to **Pods Admin → Add New**
3. Create `cnf_machine` post type with all fields
4. Create `cnf_use` post type with all fields
5. Create `faq` post type with all fields
6. Create taxonomies

**Field Configuration Tables:** See `SETUP-GUIDE.md` lines 200-323 for complete field lists.

### Option B: Export/Import Pods Configuration (After First Setup)

Once you've configured Pods via admin:

```bash
# SSH into WP Engine
ssh westgategroup@westgategroup.ssh.wpengine.net

# Export Pods configuration for backup
wp pods export > ~/pods-config-backup.json

# Download to local machine
exit
scp westgategroup@westgategroup.ssh.wpengine.net:~/pods-config-backup.json ~/Desktop/
```

---

## Phase 6: Create Content (2-3 hours)

### Step 6.1: Create Pages via WP-CLI

```bash
# SSH into WP Engine
ssh westgategroup@westgategroup.ssh.wpengine.net

# Create Home page
wp post create --post_type=page --post_title='Home' --post_name='home' --post_status=publish

# Create About page
wp post create --post_type=page --post_title='About' --post_name='about' --post_status=publish

# Create Contact page
wp post create --post_type=page --post_title='Contact' --post_name='contact' --post_status=publish

# Create Dealers page
wp post create --post_type=page --post_title='Specialist Dealers' --post_name='dealers' --post_status=publish

# Create Mini Dumpers page
wp post create --post_type=page --post_title='Mini Dumpers' --post_name='minidumpers' --post_status=publish

# Create FAQs page
wp post create --post_type=page --post_title='FAQs' --post_name='faqs' --post_status=publish

# Create Privacy Policy page
wp post create --post_type=page --post_title='Privacy Policy' --post_name='privacy-policy' --post_status=publish

# Create Terms & Conditions page
wp post create --post_type=page --post_title='Terms & Conditions' --post_name='terms-and-conditions' --post_status=publish

# Set Home as front page
wp option update show_on_front page
wp option update page_on_front $(wp post list --post_type=page --name=home --field=ID --format=ids)
```

### Step 6.2: Create cnf_machine Posts

**Create machines via WP Admin** (recommended for complex Pods fields):
1. Go to `https://westgategroup.wpenginepowered.com/wp-admin/`
2. Navigate to **CNF Machines → Add New**
3. Create 4 machines:
   - **T50** (slug: `t50`)
   - **T70** (slug: `t70`)
   - **T95** (slug: `t95`)
   - **T150** (slug: `t150`)

**IMPORTANT:** Fill in ALL Pods fields matching `full-wp.ts` structure:
- Marketing fields (title, description)
- Specifications
- Selling points (repeater)
- Technical specs (repeater)
- Gallery images (multiple files)
- Floating stats (JSON)
- Relationships

**Alternative: Create basic structure via WP-CLI:**
```bash
# Create T50
wp post create \
  --post_type=cnf_machine \
  --post_title='T50 - 500kg Tracked Mini Dumper' \
  --post_name='t50' \
  --post_status=publish

# Create T70
wp post create \
  --post_type=cnf_machine \
  --post_title='T70 - 700kg Tracked Mini Dumper' \
  --post_name='t70' \
  --post_status=publish

# Create T95
wp post create \
  --post_type=cnf_machine \
  --post_title='T95 - 950kg Tracked Mini Dumper' \
  --post_name='t95' \
  --post_status=publish

# Create T150
wp post create \
  --post_type=cnf_machine \
  --post_title='T150 - 1500kg Tracked Mini Dumper' \
  --post_name='t150' \
  --post_status=publish

# Then edit via WP Admin to fill in Pods fields
```

### Step 6.3: Create cnf_use Posts

Create 8 use case posts via WP Admin:
- Construction & Groundworks
- Landscaping & Gardening
- Agriculture & Farming
- Tool & Plant Hire Centres
- Property Development & Renovation
- Municipal & Local Authority Work
- Housebuilding & Extensions
- Demolition & Site Clearance

**Reference:** `full-wp.ts` lines 2391-2844 for complete content.

### Step 6.4: Create FAQ Posts

Create 20+ FAQ posts via WP Admin.

**Set order field** for display sequence (1, 2, 3, etc.)

**Reference:** `full-wp.ts` lines 2850-end for complete FAQs.

### Step 6.5: Configure Flat Upload Structure (5 minutes)

**IMPORTANT:** By default, WordPress organizes uploads into date-based folders (e.g., `uploads/2024/12/image.jpg`). We want a flat structure (`uploads/image.jpg`) to match your local setup.

**Disable date-based upload folders:**

```bash
# SSH into WP Engine
ssh westgategroup@westgategroup.ssh.wpengine.net

# Disable year/month folder organization
wp option update uploads_use_yearmonth_folders 0

# Verify setting
wp option get uploads_use_yearmonth_folders
# Should return: 0
```

**Alternative: Via WP Admin**
1. Go to **Settings → Media**
2. **Uncheck:** "Organize my uploads into month- and year-based folders"
3. Click **Save Changes**

### Step 6.6: Bulk Upload Media Files from Local (10 minutes)

You have all your images in `/Users/neil/Documents/Wordsco/cnf/public/uploads/`. Let's upload them all to WP Engine in one command.

**Option 1: rsync (Recommended - Preserves structure and is resumable)**

```bash
# From your LOCAL machine (not SSH)
cd /Users/neil/Documents/Wordsco/cnf

# Bulk upload all files to WP Engine uploads directory
rsync -avz --progress public/uploads/ westgategroup@westgategroup.ssh.wpengine.net:sites/westgategroup/wp-content/uploads/

# Flags explained:
# -a = archive mode (preserves permissions, timestamps)
# -v = verbose (show files being transferred)
# -z = compress during transfer (faster)
# --progress = show progress bar
```

**What this does:**
- Uploads all 200+ files from `public/uploads/` to WP Engine's `wp-content/uploads/`
- Preserves file structure (flat, no date folders)
- Shows progress bar
- Can be resumed if interrupted

**Expected output:**
```
sending incremental file list
cnf-t50-1.jpg
          53,421 100%   1.2MB/s    0:00:00
cnf-t50-1.webp
          12,345 100%   800KB/s    0:00:00
...
sent 25.5M bytes  received 4.2K bytes  1.2MB/s
total size is 25.4M  speedup is 1.00
```

**Option 2: SCP (Alternative)**

```bash
# From your LOCAL machine
cd /Users/neil/Documents/Wordsco/cnf

# Upload all files (no progress bar)
scp -r public/uploads/* westgategroup@westgategroup.ssh.wpengine.net:sites/westgategroup/wp-content/uploads/
```

### Step 6.7: Import Media into WordPress Library (15 minutes)

Files are now on the server, but WordPress doesn't know about them yet. Let's import them into the Media Library so they can be attached to posts.

```bash
# SSH into WP Engine
ssh westgategroup@westgategroup.ssh.wpengine.net

# Navigate to uploads directory
cd sites/westgategroup/wp-content/uploads

# Import all images into WordPress media library
# This registers them in the database with proper metadata

# Method 1: Import all images at once
wp media import *.jpg *.png *.webp *.pdf --skip-copy

# --skip-copy flag tells WP-CLI files are already in uploads folder
# This just registers them in the database

# Method 2: Import specific machine images and set featured images
# (Do this after creating machine posts in Step 6.2)

# T50 images
wp media import cnf-t50-1.jpg --post_id=31 --title='T50 500kg Mini Dumper' --alt='T50 tracked mini dumper compact design' --featured_image --skip-copy

# T70 images
wp media import cnf-t70-1.jpg --post_id=32 --title='T70 700kg Mini Dumper' --alt='T70 tracked mini dumper with swivel' --featured_image --skip-copy

# T95 images
wp media import cnf-t95-1.jpg --post_id=33 --title='T95 950kg Mini Dumper' --alt='T95 tracked mini dumper professional grade' --featured_image --skip-copy

# T150 images
wp media import cnf-t150-1.jpg --post_id=34 --title='T150 1500kg Mini Dumper' --alt='T150 tracked mini dumper maximum capacity' --featured_image --skip-copy
```

**Verify media imported:**
```bash
# Check media library count
wp media list --format=count
# Should show 200+ items

# List recent media
wp media list --fields=ID,post_title,file --format=table
```

**Set Alt Text for All Images (Batch):**
```bash
# Set alt text for T50 images
wp post meta update 201 _wp_attachment_image_alt 'T50 500kg tracked mini dumper'
wp post meta update 202 _wp_attachment_image_alt 'T50 mini dumper side view'
# ... repeat for each image ID

# Or create a script (save as set-alt-text.sh)
#!/bin/bash
# Get all attachment IDs and set generic alt text
for id in $(wp post list --post_type=attachment --field=ID); do
  filename=$(wp post get $id --field=post_name)
  wp post meta update $id _wp_attachment_image_alt "CNF Mini Dumper - $filename"
done
```

**Result:**
- ✅ All images uploaded to WP Engine
- ✅ Flat structure: `uploads/cnf-t50-1.jpg` (not `uploads/2024/12/cnf-t50-1.jpg`)
- ✅ Registered in WordPress media library
- ✅ Alt text set for SEO
- ✅ Featured images attached to machine posts
- ✅ API will return: `https://westgategroup.wpenginepowered.com/wp-content/uploads/cnf-t50-1.jpg`

---

## Phase 7: Create Navigation Menus (15 minutes)

### Via WP-CLI

```bash
# SSH into WP Engine
ssh westgategroup@westgategroup.ssh.wpengine.net

# Create Primary Menu
wp menu create "Primary Menu"

# Add items to Primary Menu
wp menu item add-post primary-menu 2 --title="About"
wp menu item add-post primary-menu 5 --title="Mini Dumpers"
wp menu item add-post primary-menu 4 --title="Specialist Dealers"

# Add machine submenu items under Mini Dumpers
# (Requires getting menu item IDs - easier via WP Admin)
```

**Recommended: Create Menus via WP Admin**
1. Go to **Appearance → Menus**
2. Create "Primary Menu" and "Footer Menu"
3. Add pages and custom links
4. Set menu locations (Primary, Footer)

**Menu Structure Reference:** See `SETUP-GUIDE.md` Phase 6 for complete structure.

---

## Phase 8: Test WordPress API (10 minutes)

### Step 8.1: Test Bootstrap Endpoint

```bash
# From your LOCAL machine terminal
curl https://westgategroup.wpenginepowered.com/wp-json/app/v1/bootstrap

# Should return JSON with:
# - siteSettings
# - menus
# - pages
# - cnfMachines
# - cnfUses
# - faqs
```

**Open in Browser:**
Visit: `https://westgategroup.wpenginepowered.com/wp-json/app/v1/bootstrap`

### Step 8.2: Validate Response Structure

Compare API response to `full-wp.ts`:

```bash
# Download API response
curl https://westgategroup.wpenginepowered.com/wp-json/app/v1/bootstrap > wp-api-response.json

# Compare to static data
code --diff wp-api-response.json app/lib/constants/full-wp.ts
```

**Check for:**
- All machines present (T50, T70, T95, T150)
- All Pods fields populated
- Images have full URLs
- Menus have correct hierarchy
- All pages present

---

## Phase 9: Connect React App to WP Engine API (5 minutes)

### Step 9.1: Update DataService Config

Edit `app/root.tsx` (line ~217):

```typescript
// Change from:
const dataService = await DataService.getInstance({
  source: DataSourceType.STATIC,
  cacheTimeout: 5 * 60 * 1000,
});

// To:
const dataService = await DataService.getInstance({
  source: DataSourceType.API,
  apiUrl: "https://westgategroup.wpenginepowered.com/wp-json/app/v1/bootstrap",
  cacheTimeout: 5 * 60 * 1000,
});
```

### Step 9.2: Start React App Locally

```bash
# From your LOCAL machine
cd /Users/neil/Documents/Wordsco/cnf
pnpm dev
```

### Step 9.3: Test Frontend Connection

1. Open `http://localhost:5173`
2. **Check browser console** for errors
3. Verify machines display correctly
4. Check navigation works
5. Verify images load (should show WP Engine CDN URLs)

**Expected Image URLs:**
```
https://westgategroup.wpenginepowered.com/wp-content/uploads/2024/12/t50-main.webp
```

---

## Phase 10: WP Engine-Specific Optimizations

### Step 10.1: Enable Object Caching (Redis)

WP Engine provides Redis object caching by default.

```bash
# SSH into WP Engine
ssh westgategroup@westgategroup.ssh.wpengine.net

# Check if object caching is enabled
wp cache type
# Should show: redis or memcached

# Flush cache if needed
wp cache flush
```

### Step 10.2: CDN Configuration

WP Engine automatically serves media through their CDN.

**Image URLs will be:**
- Development: `https://westgategroup.wpenginepowered.com/wp-content/uploads/...`
- Production: `https://your-domain.com/wp-content/uploads/...` (via WP Engine CDN)

**No configuration needed** - WP Engine handles this automatically.

### Step 10.3: Environment-Specific Settings

Create `.env.local` for development:

```bash
# From your LOCAL machine
cd /Users/neil/Documents/Wordsco/cnf

cat > .env.local << 'EOF'
# Development - WP Engine
VITE_WP_API_URL=https://westgategroup.wpenginepowered.com/wp-json/app/v1/bootstrap

# Production - WP Engine (when ready)
# VITE_WP_API_URL=https://cnfminidumper.co.uk/wp-json/app/v1/bootstrap
EOF
```

Update `app/root.tsx` to use environment variable:

```typescript
const dataService = await DataService.getInstance({
  source: DataSourceType.API,
  apiUrl: import.meta.env.VITE_WP_API_URL || "https://westgategroup.wpenginepowered.com/wp-json/app/v1/bootstrap",
  cacheTimeout: 5 * 60 * 1000,
});
```

---

## WP Engine-Specific Troubleshooting

### Issue 1: SSH Connection Fails

**Error:** `Permission denied (publickey)`

**Solution:**
1. Check SSH key is added to WP Engine User Portal
2. Test with password authentication: `ssh -o PreferredAuthentications=password westgategroup@westgategroup.ssh.wpengine.net`
3. Verify username matches your install name

### Issue 2: Plugin Installation Restricted

**Error:** `Plugin cannot be installed due to security policies`

**Solution:**
Some plugins may be restricted by WP Engine. Install via WP Admin instead of WP-CLI.

**Blocked Plugins:**
WP Engine blocks certain plugins for security/performance. Check: https://wpengine.com/support/disallowed-plugins/

**Pods, Ninja Forms, JWT Auth** are all allowed.

### Issue 3: File Upload Limits

**Error:** `Maximum upload file size: 64MB`

**Solution:**
WP Engine sets upload limits. For larger files:
1. Upload via SFTP directly to `/wp-content/uploads/`
2. Or contact WP Engine support to increase limit

### Issue 4: API Rate Limiting

WP Engine may rate-limit API requests during development.

**Solution:**
- Use caching (already configured in DataService)
- For testing, temporarily increase cache timeout to 15 minutes

### Issue 5: Staging vs. Development

**WP Engine Environments:**
- **Development:** `westgategroup.wpenginepowered.com` (you're here)
- **Staging:** `westgategroup.wpengine.com` (separate environment)
- **Production:** Your custom domain (when ready)

Each environment is separate. Use **WP Engine's migration tools** to copy content between environments.

---

## Quick Checklist - WP Engine Specific

**Access & Setup:**
- [ ] SSH credentials obtained from WP Engine portal
- [ ] SSH key authentication configured
- [ ] SSH connection tested successfully
- [ ] WP-CLI access verified

**WordPress Configuration:**
- [ ] Pods plugin installed and activated via WP-CLI
- [ ] Ninja Forms plugin installed and activated
- [ ] JWT Auth plugin installed (optional)
- [ ] Permalinks set to "Post name"
- [ ] Site title and description configured

**Theme Deployment:**
- [ ] CNF Headless theme files created locally
- [ ] Theme uploaded to WP Engine via SCP/SFTP
- [ ] Theme activated via WP-CLI or WP Admin
- [ ] Bootstrap API endpoint accessible

**Content Created:**
- [ ] 8 pages created (Home, About, Contact, Dealers, Mini Dumpers, FAQs, Privacy, Terms)
- [ ] 4 cnf_machine posts (T50, T70, T95, T150) with Pods fields
- [ ] 8 cnf_use posts with Pods fields
- [ ] 20+ FAQ posts with Pods fields
- [ ] Primary menu with hierarchy
- [ ] Footer menu
- [ ] Media uploaded to WP Engine

**Pods Configuration:**
- [ ] cnf_machine post type with 25+ fields
- [ ] cnf_use post type with 10+ fields
- [ ] faq post type with 5 fields
- [ ] Taxonomies: machine_category, machine_industry, use_category, faq_category

**API Testing:**
- [ ] `/wp-json/app/v1/bootstrap` returns valid JSON
- [ ] Response structure matches `full-wp.ts`
- [ ] All Pods fields present in API response
- [ ] Images return full WP Engine URLs
- [ ] Taxonomies included
- [ ] No CORS errors (CORS configured in functions.php)

**React Integration:**
- [ ] `app/root.tsx` updated with WP Engine API URL
- [ ] Environment variable configured (`.env.local`)
- [ ] React app loads without errors
- [ ] All content displays correctly from WordPress
- [ ] Navigation works
- [ ] Images load from WP Engine CDN

**WP Engine Optimizations:**
- [ ] Object caching verified (Redis/Memcached)
- [ ] CDN serving media files
- [ ] HTTPS enabled (automatic on WP Engine)
- [ ] Cache flushed after theme/plugin changes

---

## Time Estimates - WP Engine

| Phase | Time | Notes |
|-------|------|-------|
| Getting SSH Access | 5 min | First time only |
| Plugin Installation (WP-CLI) | 10 min | Faster than manual |
| Theme Deployment (SCP) | 10 min | Upload + activate |
| Pods Configuration | 45 min | Same as generic |
| Content Creation | 2-3 hours | Same as generic |
| Menu Creation | 15 min | Via WP Admin |
| API Testing | 10 min | Test endpoint |
| React Integration | 5 min | Update API URL |
| **TOTAL** | **4-5 hours** | **Faster than local!** |

**Why faster than local?**
- WordPress pre-installed on WP Engine
- No database setup needed
- No local server configuration
- Fast SSH/WP-CLI access
- Reliable hosting environment

---

## Next Steps

1. **Get SSH Access** - Request credentials from WP Engine portal
2. **Follow this guide step-by-step** - Each phase builds on the previous
3. **Test API endpoint** - Verify JSON structure matches `full-wp.ts`
4. **Connect React app** - Update `app/root.tsx` with WP Engine URL
5. **Debug any mismatches** - Compare API response to static data

---

## Production Deployment (Future)

When ready to go live:

1. **Copy Development to Production:**
   - Use WP Engine's built-in migration tool
   - Or manually export/import via WP-CLI

2. **Update Domain:**
   - Point your domain (`cnfminidumper.co.uk`) to WP Engine
   - WP Engine handles SSL/HTTPS automatically

3. **Update React App:**
   - Change API URL to production domain
   - Deploy React build to Cloudflare Workers

4. **Test Production:**
   - Verify API endpoint works on production domain
   - Test all content loads correctly
   - Check image CDN URLs

---

## Resources

**WP Engine Documentation:**
- SSH Gateway: https://wpengine.com/support/ssh-gateway/
- WP-CLI on WP Engine: https://wpengine.com/support/wp-cli/
- Git Push: https://wpengine.com/support/git/
- SFTP: https://wpengine.com/support/sftp/

**General Resources:**
- Pods Documentation: https://pods.io/docs/
- WP REST API: https://developer.wordpress.org/rest-api/
- React Router 7: https://reactrouter.com

---

Built by [Wordsco](https://wordsco.uk) - Good luck with your WP Engine setup! 🚀
