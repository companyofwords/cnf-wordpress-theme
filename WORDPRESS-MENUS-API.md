# WordPress Menus vs Navigation API Guide

## Overview

WordPress has two systems for managing navigation:

1. **Classic Menus** (Pre-WordPress 5.9) - Traditional menu system
2. **Navigation Blocks** (WordPress 5.9+) - Gutenberg block-based navigation

Both systems can coexist, and the CNF setup supports both.

---

## 🔴 CRITICAL: Classic Menu API Requires Custom Endpoint

**Important:** The WordPress REST API does NOT provide a default endpoint for classic menus!

The classic menu system (`/wp-json/wp/v2/menus`) requires either:
- A custom REST API endpoint
- A plugin like WP-REST-API V2 Menus
- Custom implementation in your theme/plugin

Our CNF headless theme includes a custom endpoint at:
```
GET /wp-json/app/v1/menus
GET /wp-json/app/v1/menus/{location}
```

---

## Classic Menus (Traditional System)

### Via WordPress REST API (Custom Endpoint)

**Our Custom Endpoint:**
```bash
# Get all menus
curl https://westgategroup.wpenginepowered.com/wp-json/app/v1/menus

# Get menu by location
curl https://westgategroup.wpenginepowered.com/wp-json/app/v1/menus/primary
curl https://westgategroup.wpenginepowered.com/wp-json/app/v1/menus/footer-1
```

**Response Format:**
```json
{
  "primary": {
    "id": 1,
    "name": "Main Navigation",
    "slug": "main-navigation",
    "items": [
      {
        "ID": 2,
        "title": "About",
        "url": "/about",
        "menu_order": 1,
        "menu_item_parent": "0",
        "type": "post_type",
        "object": "page",
        "children": []
      },
      {
        "ID": 3,
        "title": "Mini Dumpers",
        "url": "/minidumpers",
        "menu_order": 2,
        "menu_item_parent": "0",
        "type": "post_type",
        "object": "page",
        "children": [
          {
            "ID": 31,
            "title": "T50 - 500kg",
            "url": "/minidumpers/t50",
            "menu_order": 1,
            "menu_item_parent": "3",
            "type": "post_type",
            "object": "cnf_machine"
          }
        ]
      }
    ]
  }
}
```

### Via WP-CLI

**List all menus:**
```bash
ssh westgategroup@westgategroup.ssh.wpengine.net
wp menu list --path=/nas/content/live/westgategroup/
```

**Get menu items by location:**
```bash
# Get primary menu items
wp menu item list primary --path=/nas/content/live/westgategroup/ --format=json

# Get footer menu items
wp menu item list footer-1 --path=/nas/content/live/westgategroup/ --format=json
```

**Create a menu:**
```bash
wp menu create "Main Navigation" --path=/nas/content/live/westgategroup/
```

**Assign menu to location:**
```bash
wp menu location assign main-navigation primary --path=/nas/content/live/westgategroup/
```

**Add items to menu:**
```bash
# Add a page to menu
wp menu item add-post main-navigation 2 --title="About" --path=/nas/content/live/westgategroup/

# Add custom link
wp menu item add-custom main-navigation "Contact" /contact --path=/nas/content/live/westgategroup/

# Add as child item
wp menu item add-post main-navigation 31 --parent-id=3 --title="T50 - 500kg" --path=/nas/content/live/westgategroup/
```

---

## Navigation Blocks (Gutenberg System)

### Via WordPress REST API (Native)

**Default WordPress Endpoint:**
```bash
# Get all navigation blocks
curl https://westgategroup.wpenginepowered.com/wp-json/wp/v2/navigation

# Get specific navigation by ID
curl https://westgategroup.wpenginepowered.com/wp-json/wp/v2/navigation/123

# Get with full rendering
curl https://westgategroup.wpenginepowered.com/wp-json/wp/v2/navigation/123?context=edit
```

**Response Format:**
```json
{
  "id": 123,
  "title": {
    "rendered": "Main Navigation"
  },
  "content": {
    "rendered": "<!-- wp:navigation-link {\"label\":\"About\",\"url\":\"/about\"} /-->",
    "raw": "<!-- wp:navigation-link {\"label\":\"About\",\"url\":\"/about\"} /-->"
  },
  "status": "publish",
  "blocks": [
    {
      "blockName": "core/navigation-link",
      "attrs": {
        "label": "About",
        "url": "/about",
        "kind": "post-type",
        "id": 2,
        "type": "page"
      }
    }
  ]
}
```

### Via WP-CLI

**List navigation blocks:**
```bash
wp post list --post_type=wp_navigation --path=/nas/content/live/westgategroup/ --format=table
```

**Get navigation content:**
```bash
wp post get 123 --field=post_content --path=/nas/content/live/westgategroup/
```

**Create navigation block:**
```bash
wp post create --post_type=wp_navigation --post_title="Main Navigation" --post_status=publish --path=/nas/content/live/westgategroup/
```

---

## Our CNF Implementation

### CNF Headless Theme Endpoints

Our theme provides these custom endpoints in `wp-content/themes/cnf-headless/functions.php`:

```php
// Register custom menu endpoint
add_action('rest_api_init', function () {
  register_rest_route('app/v1', '/menus', [
    'methods' => 'GET',
    'callback' => 'cnf_get_menus',
    'permission_callback' => '__return_true',
  ]);

  register_rest_route('app/v1', '/menus/(?P<location>[a-zA-Z0-9_-]+)', [
    'methods' => 'GET',
    'callback' => 'cnf_get_menu_by_location',
    'permission_callback' => '__return_true',
  ]);
});

function cnf_get_menus() {
  $locations = get_nav_menu_locations();
  $menus = [];

  foreach ($locations as $location => $menu_id) {
    $menu_obj = wp_get_nav_menu_object($menu_id);
    if ($menu_obj) {
      $menus[$location] = [
        'id' => $menu_id,
        'name' => $menu_obj->name,
        'slug' => $menu_obj->slug,
        'items' => cnf_get_menu_items($menu_id),
      ];
    }
  }

  return $menus;
}

function cnf_get_menu_by_location($data) {
  $location = $data['location'];
  $locations = get_nav_menu_locations();

  if (!isset($locations[$location])) {
    return new WP_Error('menu_not_found', 'Menu location not found', ['status' => 404]);
  }

  $menu_id = $locations[$location];
  $menu_obj = wp_get_nav_menu_object($menu_id);

  return [
    'id' => $menu_id,
    'name' => $menu_obj->name,
    'slug' => $menu_obj->slug,
    'location' => $location,
    'items' => cnf_get_menu_items($menu_id),
  ];
}

function cnf_get_menu_items($menu_id) {
  $menu_items = wp_get_nav_menu_items($menu_id);
  $menu_tree = [];
  $menu_map = [];

  foreach ($menu_items as $item) {
    $menu_map[$item->ID] = [
      'ID' => $item->ID,
      'title' => $item->title,
      'url' => $item->url,
      'menu_order' => $item->menu_order,
      'menu_item_parent' => $item->menu_item_parent,
      'type' => $item->type,
      'object' => $item->object,
      'object_id' => $item->object_id,
      'target' => $item->target,
      'classes' => $item->classes,
      'children' => [],
    ];
  }

  // Build hierarchical structure
  foreach ($menu_map as $id => &$item) {
    if ($item['menu_item_parent'] == '0') {
      $menu_tree[] = &$item;
    } else {
      $menu_map[$item['menu_item_parent']]['children'][] = &$item;
    }
  }

  return $menu_tree;
}
```

### MU-Plugin Menu Creation

The CNF setup MU-plugin creates classic menus programmatically:

```php
// wp-content/mu-plugins/cnf-setup/includes/menus.php

function cnf_create_menus($schema) {
  $menus = $schema['menus'] ?? [];

  foreach ($menus as $menu_def) {
    $menu_name = $menu_def['name'];
    $location = $menu_def['location'];

    // Create or get menu
    $menu_exists = wp_get_nav_menu_object($menu_name);
    if (!$menu_exists) {
      $menu_id = wp_create_nav_menu($menu_name);
      cnf_log("Created menu: {$menu_name} (ID: {$menu_id})");
    } else {
      $menu_id = $menu_exists->term_id;
      cnf_log("Menu already exists: {$menu_name} (ID: {$menu_id})");
    }

    // Assign to location
    $locations = get_theme_mod('nav_menu_locations', []);
    $locations[$location] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);

    // Add menu items
    foreach ($menu_def['items'] as $item) {
      cnf_add_menu_item($menu_id, $item);
    }
  }
}

function cnf_add_menu_item($menu_id, $item, $parent_id = 0) {
  $args = [
    'menu-item-title' => $item['title'],
    'menu-item-url' => $item['url'],
    'menu-item-type' => $item['type'],
    'menu-item-status' => 'publish',
    'menu-item-parent-id' => $parent_id,
  ];

  $item_id = wp_update_nav_menu_item($menu_id, 0, $args);

  // Add children recursively
  if (isset($item['children'])) {
    foreach ($item['children'] as $child) {
      cnf_add_menu_item($menu_id, $child, $item_id);
    }
  }

  return $item_id;
}
```

---

## Accessing Menus in Your React App

### Using the Bootstrap Endpoint

The easiest way is to use our Bootstrap API which includes menus:

```typescript
// app/lib/api/wordpress.ts
const response = await fetch('https://westgategroup.wpenginepowered.com/wp-json/app/v1/bootstrap');
const data = await response.json();

console.log(data.menus.primary); // Main navigation
console.log(data.menus['footer-1']); // Footer menu
```

### Direct Menu Endpoint

```typescript
// Get all menus
const menusResponse = await fetch('https://westgategroup.wpenginepowered.com/wp-json/app/v1/menus');
const menus = await menusResponse.json();

// Get specific menu by location
const primaryMenuResponse = await fetch('https://westgategroup.wpenginepowered.com/wp-json/app/v1/menus/primary');
const primaryMenu = await primaryMenuResponse.json();
```

---

## Migration Strategy: Classic Menus → Navigation Blocks

If you want to migrate to Navigation Blocks in the future:

### 1. Export Classic Menu Structure

```bash
wp menu item list primary --format=json --path=/nas/content/live/westgategroup/ > menu-export.json
```

### 2. Create Navigation Block

```bash
# Create navigation post
wp post create \
  --post_type=wp_navigation \
  --post_title="Main Navigation" \
  --post_status=publish \
  --post_content='<!-- wp:navigation-link {"label":"About","url":"/about"} /--><!-- wp:navigation-link {"label":"Mini Dumpers","url":"/minidumpers"} /-->' \
  --path=/nas/content/live/westgategroup/
```

### 3. Support Both in Your Theme

```php
// Check for both navigation types
function get_site_navigation($location) {
  // Try classic menu first
  $locations = get_nav_menu_locations();
  if (isset($locations[$location])) {
    return get_classic_menu($locations[$location]);
  }

  // Fallback to navigation block
  return get_navigation_block($location);
}
```

---

## Troubleshooting

### Classic Menu Not Appearing

```bash
# Check if menu exists
wp menu list --path=/nas/content/live/westgategroup/

# Check menu locations
wp menu location list --path=/nas/content/live/westgategroup/

# Verify menu items
wp menu item list <menu-name> --path=/nas/content/live/westgategroup/
```

### Custom Endpoint Returns 404

```bash
# Check if theme is active
wp theme list --path=/nas/content/live/westgategroup/

# Flush rewrite rules
wp rewrite flush --path=/nas/content/live/westgategroup/

# Check if custom endpoint is registered
wp rest-api list-endpoints --path=/nas/content/live/westgategroup/ | grep menus
```

### Navigation Block Not Rendering

```bash
# Check if wp_navigation post type exists
wp post-type list --path=/nas/content/live/westgategroup/ | grep navigation

# List navigation blocks
wp post list --post_type=wp_navigation --path=/nas/content/live/westgategroup/
```

---

## Summary

**For CNF Project:**
- ✅ Use **Classic Menus** (already implemented in MU-plugin)
- ✅ Custom REST endpoint at `/wp-json/app/v1/menus`
- ✅ Bootstrap endpoint includes menus
- ✅ WP-CLI commands available for menu management
- 🔄 Navigation Blocks supported by WordPress but not required

**Best Practice:**
Classic menus work perfectly for headless WordPress. Navigation blocks are more suited for full-site editing (FSE) themes. Since CNF is headless, classic menus are the right choice.
