# CNF Automated Setup (MU-Plugin)

**Must-Use Plugin** for automated WordPress setup from TypeScript schema.

## What It Does

This MU-plugin automates the entire WordPress setup process:

✅ **Creates Pods** - All custom post types, taxonomies, and fields
✅ **Seeds Content** - Populates WordPress with posts/pages
✅ **Uploads Media** - Bulk uploads from `public/uploads/` folder
✅ **Creates Menus** - Navigation menus with hierarchical structure
✅ **Customizes Dashboard** - Removes unwanted items, adds branding

## Installation

### On WP Engine (via Git Push)

```bash
# From wp-theme folder
git push wpengine main

# MU-plugin automatically deployed to:
# /sites/westgategroup/wp-content/mu-plugins/cnf-setup/
```

### Manual Installation

```bash
# SSH into WP Engine
ssh westgategroup@westgategroup.ssh.wpengine.net

# Upload MU-plugin folder
scp -r mu-plugins/cnf-setup/ westgategroup@westgategroup.ssh.wpengine.net:sites/westgategroup/wp-content/mu-plugins/
```

## Prerequisites

1. **Pods Framework** must be installed and activated:
   ```bash
   wp plugin install pods --activate
   ```

2. **Schema file** must exist at `mu-plugins/cnf-setup/schema.json`:
   ```bash
   npm run build:schema
   ```

3. **Media files** must be in `/public/uploads/` folder

## How It Works

### 1. Define Schema (TypeScript)

Create `wp-schema.ts` with your content structure:

```typescript
export const pods = [
  {
    name: 'cnf_machine',
    label: 'CNF Machines',
    type: 'post_type',
    fields: [
      { name: 'title', label: 'Marketing Title', type: 'text' },
      { name: 'description', label: 'Description', type: 'wysiwyg' },
      // ... more fields
    ]
  }
];

export const content = [
  {
    post_type: 'cnf_machine',
    title: 'T50 - 500kg Mini Dumper',
    slug: 't50',
    content: 'Description...',
    fields: {
      title: 'COMPACT POWER',
      description: 'Perfect for tight spaces...',
    }
  }
];
```

### 2. Compile Schema

```bash
npm run build:schema
# Outputs: mu-plugins/cnf-setup/schema.json
```

### 3. Auto-Setup Runs

The MU-plugin automatically runs when WordPress loads and:

1. Reads `schema.json`
2. Creates all Pods (post types + fields)
3. Seeds content (posts/pages with Pods data)
4. Uploads media files
5. Creates navigation menus
6. Customizes dashboard

**Setup runs once** - marked as completed after first successful run.

## File Structure

```
mu-plugins/cnf-setup/
├── cnf-setup.php                  # Main plugin file (orchestrator)
├── schema.json                    # Compiled schema (from wp-schema.ts)
├── setup.log                      # Setup log file
├── README.md                      # This file
└── includes/
    ├── schema-reader.php          # Reads and validates schema.json
    ├── pods-builder.php           # Creates Pods from schema
    ├── content-seeder.php         # Seeds posts/pages
    ├── media-uploader.php         # Uploads media files
    └── dashboard-customizer.php   # Customizes WordPress admin
```

## Admin Interface

Access the setup interface at:

**WordPress Admin → Tools → CNF Setup**

From here you can:
- View setup status
- Check prerequisites (schema file, Pods plugin)
- Manually trigger setup
- Reset setup (to run again)
- View setup log

## Schema Structure

The `schema.json` file (compiled from `wp-schema.ts`) must have these sections:

```json
{
  "pods": [],               // Pod definitions (post types, fields)
  "content": [],            // Content items (posts/pages)
  "media": [],              // Media files to upload
  "menus": [],              // Navigation menus
  "siteSettings": {},       // WordPress options
  "dashboardCustomization": {}  // Dashboard customizations
}
```

### Example: Pod Definition

```json
{
  "pods": [
    {
      "name": "cnf_machine",
      "label": "CNF Machines",
      "type": "post_type",
      "options": {
        "public": true,
        "has_archive": true,
        "supports": ["title", "editor", "thumbnail"]
      },
      "fields": [
        {
          "name": "title",
          "label": "Marketing Title",
          "type": "text",
          "required": false
        },
        {
          "name": "selling_points",
          "label": "Selling Points",
          "type": "paragraph",
          "repeatable": true
        }
      ]
    }
  ]
}
```

### Example: Content Item

```json
{
  "content": [
    {
      "post_type": "cnf_machine",
      "title": "T50 - 500kg Mini Dumper",
      "slug": "t50",
      "status": "publish",
      "content": "<p>Machine description...</p>",
      "featured_image": "cnf-t50-1.webp",
      "fields": {
        "title": "COMPACT POWER",
        "selling_points": [
          "Outstanding performance",
          "Perfect for tight spaces"
        ]
      }
    }
  ]
}
```

### Example: Media Item

```json
{
  "media": [
    {
      "filename": "cnf-t50-1.webp",
      "title": "T50 500kg Mini Dumper",
      "alt_text": "T50 tracked mini dumper compact design",
      "caption": "T50 in action",
      "description": "Main product image for T50"
    }
  ]
}
```

## Troubleshooting

### Setup Not Running

**Check:**
1. Is schema.json present?
   ```bash
   ls mu-plugins/cnf-setup/schema.json
   ```

2. Is Pods plugin active?
   ```bash
   wp plugin list | grep pods
   ```

3. Check setup log:
   ```bash
   cat mu-plugins/cnf-setup/setup.log
   ```

### Pods Not Created

**Error:** "Pods API not available"

**Solution:** Install and activate Pods Framework:
```bash
wp plugin install pods --activate
```

### Media Not Uploaded

**Error:** "Media directory not found"

**Solution:** Ensure media files are in correct location:
```bash
ls public/uploads/
```

The media uploader looks for files at: `../../../public/uploads/` (relative to MU-plugin)

### Schema Parse Error

**Error:** "Invalid JSON in schema file"

**Solution:** Validate your schema:
```bash
npm run build:schema
cat mu-plugins/cnf-setup/schema.json | jq .
```

### Reset Setup

To run setup again:

1. **Via Admin:** Tools → CNF Setup → Reset Setup
2. **Via WP-CLI:**
   ```bash
   wp option delete cnf_setup_completed
   ```

Then refresh any WordPress admin page to trigger setup.

## Development Workflow

1. **Edit wp-schema.ts** - Define your content structure
2. **Compile:** `npm run build:schema`
3. **Upload to WP Engine:** `git push wpengine main`
4. **Setup runs automatically** on next WordPress admin page load
5. **Check log:** View setup log in admin or via SSH

## Performance

**Setup Time:** ~2-5 minutes depending on content volume
- Creating 4 Pods with 20+ fields: ~30 seconds
- Seeding 20-50 content items: ~1-2 minutes
- Uploading 200+ media files: ~2-3 minutes

## Security

- Setup only runs once (prevents accidental re-runs)
- All inputs sanitized via WordPress functions
- File uploads use WordPress native functions
- Log file stored in plugin directory (not web-accessible)

## Extending

### Add Custom Setup Step

Edit `cnf-setup.php` and add to `run_setup()` method:

```php
public function run_setup() {
    // ... existing steps ...

    // Add your custom step
    $this->my_custom_setup($schema);

    // ... rest of setup ...
}

private function my_custom_setup($schema) {
    $this->log('Running custom setup...');
    // Your custom logic here
}
```

### Modify Pod Creation

Edit `includes/pods-builder.php` and customize `create_pod()` method.

### Custom Dashboard Widgets

Edit `includes/dashboard-customizer.php` and add widgets in `apply_customizations()` method.

## Support

**Issues:** Create an issue in the GitHub repository
**Logs:** Check `mu-plugins/cnf-setup/setup.log`
**Admin Page:** WordPress Admin → Tools → CNF Setup

## Version

**1.0.0** - Initial release

## Author

Built by [Wordsco](https://wordsco.uk)

## License

MIT License
