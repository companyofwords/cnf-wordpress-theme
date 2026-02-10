# Emergency WordPress Fix Guide

If WordPress is showing a critical error and you can't access the admin, follow these steps:

---

## Option 1: Disable MU-Plugins Temporarily (Safest)

### Via WP Engine SFTP:

1. **Connect to SFTP:**
   - Host: `westgategroup.sftp.wpengine.com`
   - Port: 2222
   - Get credentials from WP Engine dashboard

2. **Rename the problematic plugin folder:**
   ```
   Navigate to: wp-content/mu-plugins/

   Rename folders (add .disabled):
   - cnf-rest-api.php → cnf-rest-api.php.disabled
   - cnf-jwt-cors.php → cnf-jwt-cors.php.disabled
   ```

3. **Try to access WordPress admin:**
   - Visit: https://westgategroup.wpengine.com/wp-admin/
   - If it loads, the issue is with one of those plugins

4. **Re-enable one at a time:**
   - Rename back (remove .disabled)
   - Test after each to find the culprit

---

## Option 2: Check WordPress Error Logs

### Via WP Engine Dashboard:

1. Go to: https://my.wpengine.com/
2. Select: **westgategroup**
3. Click: **"Logs"** in left sidebar
4. View: **"Error Logs"**
5. Look for the most recent error - copy the error message

**Common errors to look for:**
- `Fatal error: Call to undefined function`
- `Parse error: syntax error`
- `Cannot redeclare function`

---

## Option 3: Enable WordPress Debug Mode

### Via WP Engine File Manager:

1. Open `wp-config.php`
2. Find this line (near the top):
   ```php
   define('WP_DEBUG', false);
   ```

3. Change to:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

4. Save and reload WordPress
5. Check: `wp-content/debug.log` for detailed errors

---

## Option 4: Restore from WP Engine Backup

If nothing works:

1. WP Engine Dashboard > Your Site
2. Click **"Backup Points"**
3. Find a backup from before today
4. Click **"Restore"** (partial restore of files only)
5. Select: **"wp-content only"**

---

## Quick Diagnostic Commands

### Check if plugins are the issue:
```bash
# Via SSH (if you have access)
ssh westgategroup@westgategroup.ssh.wpengine.net

# List MU-plugins
ls -la wp-content/mu-plugins/

# Check for syntax errors
php -l wp-content/mu-plugins/cnf-rest-api.php
php -l wp-content/mu-plugins/cnf-jwt-cors.php
php -l wp-content/mu-plugins/cnf-setup/cnf-setup.php
```

---

## Most Likely Issues

Based on what we deployed:

### 1. REST API Plugin Issue
**Symptom:** "Fatal error" mentioning `pods()` or `register_rest_route`
**Fix:** Disable cnf-rest-api.php temporarily

### 2. CORS Plugin Issue
**Symptom:** Headers already sent errors
**Fix:** Disable cnf-jwt-cors.php temporarily

### 3. Theme Options Array Syntax
**Symptom:** Parse error in cnf-setup.php
**Fix:** Check populate_theme_options() method around line 236

---

## Safe Rollback

If you want to completely rollback the JWT changes:

### Via SFTP:
1. Delete these files:
   - `wp-content/mu-plugins/cnf-rest-api.php`
   - `wp-content/mu-plugins/cnf-jwt-cors.php`

2. WordPress should work normally (just without JWT/REST API)

3. You can re-add them later once we fix the issue

---

## Contact Me With

When you reach out, please provide:

1. **Exact error message** (screenshot or text)
2. **When it started** (before/after config change?)
3. **Error log contents** (from WP Engine logs)
4. **What you can access** (admin panel? frontend?)

This will help me pinpoint and fix the exact issue quickly!

---

## Quick Fix Commands

```bash
# Disable REST API plugin
mv wp-content/mu-plugins/cnf-rest-api.php wp-content/mu-plugins/cnf-rest-api.php.disabled

# Disable CORS plugin
mv wp-content/mu-plugins/cnf-jwt-cors.php wp-content/mu-plugins/cnf-jwt-cors.php.disabled

# Check error log
tail -50 wp-content/debug.log

# Re-enable after fix
mv wp-content/mu-plugins/cnf-rest-api.php.disabled wp-content/mu-plugins/cnf-rest-api.php
```
