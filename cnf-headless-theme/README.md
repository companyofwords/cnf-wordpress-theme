# CNF Headless WordPress Theme

A headless WordPress theme that serves content via REST API to a React Router 7 frontend.

## Features

- ✅ **Bootstrap API Endpoint** - `/wp-json/app/v1/bootstrap` returns all site data in one request
- ✅ **CORS Configured** - Allows React app access from localhost and production domains
- ✅ **Pods Integration** - Automatic Pods field serialization in API responses
- ✅ **Security Hardened** - Disabled file editing, XML-RPC, unnecessary endpoints
- ✅ **Flat Upload Structure** - No date-based folders (uploads/image.jpg not uploads/2024/12/image.jpg)
- ✅ **Custom Admin Branding** - Minimal WordPress dashboard customization

## File Structure

```
cnf-headless-theme/
├── style.css                     # Theme header (required)
├── functions.php                 # Main theme functions
├── index.php                     # Fallback template (shows "headless" message)
├── inc/
│   └── rest-api/
│       └── bootstrap.php         # Bootstrap API endpoint
└── README.md                     # This file
```

## Installation

### Option 1: Git Push to WP Engine (Recommended)

```bash
# From wp-theme folder
cd /Users/neil/Documents/Wordsco/cnf/wp-theme

# Initialize git (if not already done)
git init

# Add WP Engine git remote
git remote add wpengine git@git.wpengine.com:production/westgategroup.git

# Commit theme files
git add .
git commit -m "Initial WordPress theme"

# Push to WP Engine
git push wpengine main

# WP Engine will automatically deploy to:
# /sites/westgategroup/wp-content/themes/cnf-headless/
```

### Option 2: SCP Upload

```bash
# From wp-theme folder
cd /Users/neil/Documents/Wordsco/cnf/wp-theme/cnf-headless-theme

# Upload to WP Engine
scp -r * westgategroup@westgategroup.ssh.wpengine.net:sites/westgategroup/wp-content/themes/cnf-headless/
```

### Option 3: SFTP Upload

Use FileZilla or Cyberduck to upload to:
- Host: `westgategroup.ssh.wpengine.net`
- Path: `/wp-content/themes/cnf-headless/`

## Activation

### Via WP-CLI (SSH)

```bash
ssh westgategroup@westgategroup.ssh.wpengine.net

wp theme activate cnf-headless
wp theme list
```

### Via WP Admin

1. Go to `https://westgategroup.wpenginepowered.com/wp-admin/`
2. Navigate to **Appearance → Themes**
3. Click **Activate** on **CNF Headless**

## API Endpoints

### Bootstrap Endpoint (Primary)

```
GET https://westgategroup.wpenginepowered.com/wp-json/app/v1/bootstrap
```

**Returns:**
```json
{
  "siteSettings": { ... },
  "menus": { "primary": [...], "footer": [...] },
  "pages": { "home": {...}, "about": {...}, ... },
  "cnfMachines": [ {...}, {...}, {...}, {...} ],
  "cnfUses": [ {...}, {...}, ... ],
  "faqs": [ {...}, {...}, ... ]
}
```

### Individual Endpoints

```
GET /wp-json/wp/v2/cnf_machine        # All machines
GET /wp-json/wp/v2/cnf_machine/{id}   # Single machine
GET /wp-json/wp/v2/cnf_use            # All use cases
GET /wp-json/wp/v2/faq                # All FAQs
GET /wp-json/wp/v2/pages              # All pages
```

## Configuration

### CORS Origins

Edit `functions.php` to add/remove allowed origins:

```php
$allowed_origins = array(
    'http://localhost:5173',           // Vite dev
    'http://localhost:3000',           // Alternative dev
    'https://cnfminidumper.co.uk',    // Production
);
```

### Disable Date-Based Uploads

Already configured in setup guide. Verify with:

```bash
wp option get uploads_use_yearmonth_folders
# Should return: 0
```

## React App Integration

Update your React app to use the WordPress API:

```typescript
// app/root.tsx
const dataService = await DataService.getInstance({
  source: DataSourceType.API,
  apiUrl: "https://westgategroup.wpenginepowered.com/wp-json/app/v1/bootstrap",
  cacheTimeout: 5 * 60 * 1000,
});
```

## Troubleshooting

### Bootstrap endpoint returns 404

```bash
# Flush permalinks
wp rewrite flush
```

### CORS errors

Check allowed origins in `functions.php` and ensure your React app origin is listed.

### Empty Pods fields

Ensure Pods plugin is installed and custom post types are created:

```bash
wp plugin list | grep pods
wp post-type list
```

### Images not loading

Verify flat upload structure:

```bash
wp option get uploads_use_yearmonth_folders  # Should be 0
ls wp-content/uploads/                       # Should show images directly
```

## Version

**1.0.0** - Initial release

## Author

Built by [Wordsco](https://wordsco.uk)

## License

MIT License
