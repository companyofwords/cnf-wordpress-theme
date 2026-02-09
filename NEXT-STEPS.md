# Next Steps - WP Engine Setup

Your WordPress theme + MU-plugin have been deployed to WP Engine! 🎉

## Quick Start (3 steps)

### 1. SSH into WP Engine

```bash
ssh westgategroup@westgategroup.ssh.wpengine.net
```

### 2. Activate Theme & Install Pods

```bash
# Activate your theme
wp theme activate cnf-headless

# Install and activate Pods Framework
wp plugin install pods --activate

# Verify everything is loaded
wp theme list
wp plugin list --status=must-use
```

You should see:
- ✅ `cnf-headless` theme active
- ✅ `pods` plugin active
- ✅ `cnf-setup` in MU-plugins list

### 3. Trigger Automated Setup

**Option A: Visit WordPress Admin (Easiest)**
Go to: https://westgategroup.wpenginepowered.com/wp-admin/

The MU-plugin will automatically:
- ✅ Create all Pods (cnf_machine, cnf_use, faq)
- ✅ Create custom fields
- ✅ Create taxonomies
- ✅ Seed sample content (if you add it to schema)
- ✅ Customize dashboard

**Option B: Manual Trigger (if needed)**
```bash
wp option delete cnf_setup_completed
# Then visit /wp-admin/ or run:
wp eval 'do_action("admin_init");'
```

---

## 📋 Creating Your Content Schema

### 1. Create wp-schema.ts from example

```bash
cd /Users/neil/Documents/Wordsco/cnf-wordpress-theme
cp wp-schema.example.ts wp-schema.ts
```

### 2. Edit with your CNF Mini Dumpers content

Use data from `../cnf/app/lib/constants/full-wp.ts` as reference:

**Example - Add T50 Machine:**

```typescript
export const sampleContent: ContentItem[] = [
  {
    post_type: "cnf_machine",
    title: "T50 - 500kg Tracked Mini Dumper",
    content: "<p>The T50 is our compact 500kg capacity tracked mini dumper...</p>",
    status: "publish",
    featured_image: "cnf-t50-1.webp",
    fields: {
      title: "COMPACT POWER",
      description: "Perfect for tight spaces and residential projects...",
      specifications: "Compact design with impressive 500kg payload capacity",
      dimensions: "1850 x 750 x 1200 mm",
      weight: 650,
      load_capacity: "500kg (1102 lbs)",
      engine: "Honda GX240 or Yanmar L70",
      track_width: "680mm (26.7 inch)",
      speed: "2 speeds: 2.5-3.5 km/h",
      selling_points: [
        "Outstanding performance on rough or soft terrain",
        "Perfect for tight or restricted-access spaces",
        "Highly efficient for repetitive heavy lifting"
      ],
      machine_weight_kg: 500,
      machine_width_cm: 68,
      actions: ["high-tip-self-loading", "high-tip", "self-loading"],
      bg_color: "dark"
    }
  },
  // Add T70, T95, T150...
];
```

### 3. Compile schema

```bash
npm run build:schema
```

This creates `wp-content/mu-plugins/cnf-setup/schema.json`

### 4. Deploy updated schema

```bash
git add .
git commit -m "Add CNF Mini Dumpers content schema"
git push wpengine main

# Then reset setup to run again
ssh westgategroup@westgategroup.ssh.wpengine.net
wp option delete cnf_setup_completed
```

Visit `/wp-admin/` and setup will run automatically with your content!

---

## 🔍 Verify Everything Works

### Check Theme is Active
```bash
ssh westgategroup@westgategroup.ssh.wpengine.net
wp theme list
```

Should show:
```
cnf-headless    active
```

### Check MU-Plugin is Loaded
```bash
wp plugin list --status=must-use
```

Should show:
```
cnf-setup    Must Use
```

### Check Pods Plugin
```bash
wp plugin list | grep pods
```

Should show:
```
pods    active
```

### Test Bootstrap API Endpoint

Visit or curl:
```bash
curl https://westgategroup.wpenginepowered.com/wp-json/app/v1/bootstrap
```

Should return JSON with:
- siteSettings
- menus (primary, footer)
- pages
- cnfMachines (once you add content)
- cnfUses
- faqs

---

## 📝 Check Setup Log

```bash
ssh westgategroup@westgategroup.ssh.wpengine.net
cat wp-content/mu-plugins/cnf-setup/setup.log
```

Look for:
```
[2025-XX-XX XX:XX:XX] Starting CNF automated setup...
[2025-XX-XX XX:XX:XX] Pods Framework detected
[2025-XX-XX XX:XX:XX] Creating Pods...
[2025-XX-XX XX:XX:XX] Pods created successfully
[2025-XX-XX XX:XX:XX] CNF automated setup completed!
```

---

## 🔄 Development Workflow

### Making Changes to Theme

```bash
cd /Users/neil/Documents/Wordsco/cnf-wordpress-theme

# Edit files
vim wp-content/themes/cnf-headless/functions.php

# Commit and push
git add .
git commit -m "Update CORS origins"
git push wpengine main

# Changes deploy automatically!
```

### Adding New Content

```bash
# Edit schema
vim wp-schema.ts

# Compile
npm run build:schema

# Verify JSON is valid
cat wp-content/mu-plugins/cnf-setup/schema.json | head -20

# Deploy
git add .
git commit -m "Add T95 and T150 machines"
git push wpengine main

# Reset setup to run again
ssh westgategroup@westgategroup.ssh.wpengine.net
wp option delete cnf_setup_completed

# Visit /wp-admin/ to trigger setup
```

---

## 🎯 Test Checklist

- [ ] SSH access works
- [ ] Theme activated successfully
- [ ] Pods plugin installed and active
- [ ] MU-plugin shows in must-use list
- [ ] Visit /wp-admin/ triggers setup
- [ ] Check setup.log for success message
- [ ] Bootstrap API endpoint returns data
- [ ] Pods created (cnf_machine, cnf_use, faq)
- [ ] Custom fields visible in WordPress admin
- [ ] Sample content appears (if added to schema)

---

## 🆘 Troubleshooting

### Theme Not Showing
```bash
# Check files deployed correctly
ssh westgategroup@westgategroup.ssh.wpengine.net
ls -la wp-content/themes/cnf-headless/
```

### MU-Plugin Not Loading
```bash
# Verify file structure
ls -la wp-content/mu-plugins/cnf-setup/
# Main file must be at: wp-content/mu-plugins/cnf-setup/cnf-setup.php
```

### Setup Not Running
```bash
# Check prerequisites
wp plugin list | grep pods  # Must be active
ls wp-content/mu-plugins/cnf-setup/schema.json  # Must exist

# Force re-run
wp option delete cnf_setup_completed
```

### API Endpoint 404
Check `functions.php` line ~180:
```php
require_once CNF_THEME_DIR . '/inc/rest-api/bootstrap.php';
```

Verify file exists at:
```bash
ls wp-content/themes/cnf-headless/inc/rest-api/bootstrap.php
```

---

## 📚 Useful Commands

```bash
# View all posts of a custom post type
wp post list --post_type=cnf_machine

# Delete all posts of a type (careful!)
wp post delete $(wp post list --post_type=cnf_machine --format=ids) --force

# View all Pods
wp pods list

# View Pods cache
wp pods cache flush

# Check PHP errors
tail -f ~/logs/php/error_php74-fpm.log
```

---

## 🎉 You're Ready!

Your WordPress setup is complete. Now you can:
1. Create your content schema in `wp-schema.ts`
2. Push changes to WP Engine
3. Connect your React app to the Bootstrap API
4. Test everything works!

**Bootstrap API Endpoint:**
```
https://westgategroup.wpenginepowered.com/wp-json/app/v1/bootstrap
```

**Next:** Update your React app to fetch from this endpoint instead of `full-wp.ts`!
