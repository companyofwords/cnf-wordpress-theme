# JWT Authentication Setup Guide

Complete guide to configure JWT authentication between WordPress (backend) and React (frontend).

---

## Prerequisites

1. **JWT Authentication Plugin**
   - Install "JWT Authentication for WP REST API" plugin
   - Plugin URL: https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/
   - Or use "Simple JWT Login" plugin as alternative

2. **WordPress Application Password**
   - WordPress 5.6+ has built-in Application Passwords
   - Alternative: Use JWT plugin's username/password authentication

---

## Part 1: WordPress Configuration

### Step 1: Install JWT Plugin

Via WordPress Admin:
```
1. Go to Plugins > Add New
2. Search for "JWT Authentication for WP REST API"
3. Install and Activate
```

Via WP-CLI:
```bash
wp plugin install jwt-authentication-for-wp-rest-api --activate
```

### Step 2: Add JWT Secret to wp-config.php

**IMPORTANT:** Add this **BEFORE** the line `/* That's all, stop editing! */`

```php
// JWT Authentication Secret Key
define('JWT_AUTH_SECRET_KEY', '+I9keMOre2M5MssEe6uft2WVo4mDbE+L4fWH0VRRtWZUpld1FiGsH/xd5vmExGfkd67ykucqKxz2LG3qJTg8Ug==');
define('JWT_AUTH_CORS_ENABLE', true);
```

**On WP Engine:**
1. Go to your WP Engine dashboard
2. Navigate to your site > File Manager (or use SFTP)
3. Edit `wp-config.php`
4. Add the JWT configuration lines above
5. Save and close

### Step 3: Create WordPress Application Password

For React app to authenticate with WordPress:

```
1. Log into WordPress Admin
2. Go to Users > Profile (or Users > Your Username)
3. Scroll to "Application Passwords"
4. Enter name: "React Frontend"
5. Click "Add New Application Password"
6. COPY THE PASSWORD - you won't see it again!
7. Format will be: xxxx xxxx xxxx xxxx xxxx xxxx
```

### Step 4: Test JWT Authentication

Test the JWT endpoint:

```bash
# Test token generation
curl -X POST https://westgategroup.wpengine.com/wp-json/jwt-auth/v1/token \
  -H "Content-Type: application/json" \
  -d '{
    "username": "your-username",
    "password": "your-application-password"
  }'
```

**Expected Response:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user_email": "your@email.com",
  "user_nicename": "username",
  "user_display_name": "Display Name"
}
```

### Step 5: Test Custom REST API Endpoints

Test the bootstrap endpoint:

```bash
# Without authentication (public endpoint)
curl https://westgategroup.wpengine.com/wp-json/cnf/v1/bootstrap

# Should return site data, menus, pages, theme options, etc.
```

Test theme options endpoint:

```bash
curl https://westgategroup.wpengine.com/wp-json/cnf/v1/theme-options

# Should return all 178 theme option fields
```

---

## Part 2: React Frontend Configuration

### Step 1: Set Environment Variables

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Edit `.env` and add your credentials:

```env
# WordPress REST API Configuration
WORDPRESS_API_URL=https://westgategroup.wpengine.com/wp-json
WORDPRESS_API_USERNAME=your-wordpress-username
WORDPRESS_API_PASSWORD=xxxx xxxx xxxx xxxx xxxx xxxx

# Use WordPress API (set to true in production)
USE_WORDPRESS_API=false  # false = use static data (development)
```

### Step 2: Update React Router Root Loader

Edit `app/root.tsx` to fetch data from WordPress:

```typescript
import { fetchWordPressBootstrap } from "~/lib/api/wordpress.server";
import { mapWPBootstrapData } from "~/lib/utils/wp-mapper";

export async function loader() {
  // Fetch from WordPress (or use static data in dev)
  const wpData = await fetchWordPressBootstrap();

  // Map to React app format
  const appData = mapWPBootstrapData(wpData);

  return json({
    navigation: appData.navigation.primary,
    siteConfig: appData.site,
    themeOptions: appData.options,
    brandColors: mapWPOptionsToBrandColors(wpData),
  });
}
```

### Step 3: Test React App Connection

Start development server:

```bash
pnpm dev
```

Check browser console for:
- ✅ No API errors
- ✅ Data loaded successfully
- ✅ Theme options populated

---

## Part 3: Deployment to Production

### WordPress (WP Engine)

1. **Deploy MU-plugins:**
   ```bash
   cd /path/to/cnf-wordpress-theme
   git add wp-content/mu-plugins/cnf-jwt-cors.php
   git add wp-content/mu-plugins/cnf-rest-api.php
   git commit -m "Add JWT CORS and REST API endpoints"
   git push wpengine main
   ```

2. **Add JWT Secret to wp-config.php:**
   - Log into WP Engine dashboard
   - Edit wp-config.php via File Manager
   - Add JWT configuration (see Part 1, Step 2)
   - Save

3. **Verify endpoints are working:**
   ```bash
   curl https://westgategroup.wpengine.com/wp-json/cnf/v1/bootstrap
   curl https://westgategroup.wpengine.com/wp-json/cnf/v1/theme-options
   ```

### React Frontend (Cloudflare Workers)

1. **Set Production Environment Variables:**

   In Cloudflare Dashboard:
   ```
   1. Go to Workers & Pages
   2. Select your CNF worker
   3. Settings > Environment Variables
   4. Add:
      - WORDPRESS_API_URL=https://westgategroup.wpengine.com/wp-json
      - WORDPRESS_API_USERNAME=your-username
      - WORDPRESS_API_PASSWORD=xxxx xxxx xxxx xxxx
      - USE_WORDPRESS_API=true
   ```

2. **Deploy React App:**
   ```bash
   cd /path/to/cnf
   pnpm build
   pnpm deploy
   ```

---

## Troubleshooting

### Issue: "JWT token invalid" or "Authorization header missing"

**Solution:**
- Verify JWT_AUTH_SECRET_KEY is set in wp-config.php
- Check CORS headers are enabled (cnf-jwt-cors.php is active)
- Ensure Application Password is correct (no spaces)

### Issue: "WordPress API endpoints return 404"

**Solution:**
- Flush WordPress permalinks: Settings > Permalinks > Save Changes
- Verify cnf-rest-api.php is active (check Tools > CNF Setup)
- Check REST API is enabled: https://westgategroup.wpengine.com/wp-json/

### Issue: "CORS policy: No 'Access-Control-Allow-Origin' header"

**Solution:**
- Verify cnf-jwt-cors.php is active
- Add your React app URL to $allowed_origins array in cnf-jwt-cors.php
- Clear Varnish cache on WP Engine

### Issue: "Theme options return empty object"

**Solution:**
- Run WordPress setup: Tools > CNF Setup > Run Setup Now
- Verify options exist: Settings > CNF Theme Options
- Check database: `SELECT * FROM wp_options WHERE option_name LIKE 'cnf_theme_options_%' LIMIT 10`

---

## Security Notes

1. **Never commit .env files to Git** - They contain sensitive credentials
2. **Use Application Passwords** - Don't use main WordPress password
3. **Rotate secrets regularly** - Change JWT secret key every 6 months
4. **Limit CORS origins** - Only allow your production domain
5. **Use HTTPS only** - Never send JWT tokens over HTTP

---

## Next Steps

Once JWT is configured:

1. ✅ WordPress serves data via REST API
2. ✅ React app fetches data on page load
3. ✅ All 178 theme options available via API
4. ✅ wp-mapper.ts transforms WordPress data to React format
5. ✅ CMS editors can update content in WordPress
6. ✅ Changes appear instantly on React frontend

**Frontend is now fully connected to WordPress CMS!** 🎉
