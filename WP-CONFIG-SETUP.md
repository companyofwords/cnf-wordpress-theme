# wp-config.php JWT Configuration Guide

**Quick guide to add JWT authentication keys to WordPress via WP Engine**

---

## ⚠️ Important Notes

- **wp-config.php is NOT in Git** (this is correct for security)
- **wp-config.php lives on the WP Engine server only**
- You must edit it via WP Engine's interface (SFTP or File Manager)
- The configuration is already prepared in `wp-config-additions.php` (copy/paste ready)

---

## 📋 Step-by-Step Instructions

### Option 1: WP Engine File Manager (Easiest)

1. **Log into WP Engine:**
   - Go to: https://my.wpengine.com/
   - Sign in with your credentials

2. **Select Your Site:**
   - Click on **"westgategroup"** from your sites list

3. **Open File Manager:**
   - In the left sidebar, click **"File Manager"**
   - Or click the **"Manage Site"** button, then **"File Manager"**

4. **Find wp-config.php:**
   - Navigate to the root directory (you should already be there)
   - Locate and click on **`wp-config.php`**
   - Click **"Edit"** button

5. **Find the Insert Point:**
   - Scroll to the **bottom** of the file
   - Look for this line (usually around line 80-100):
   ```php
   /* That's all, stop editing! Happy publishing. */
   ```

6. **Insert JWT Configuration:**
   - Place your cursor **ABOVE** the "That's all" line
   - Add a blank line
   - Copy and paste the following:

   ```php
   // ===========================================================================
   // JWT AUTHENTICATION CONFIGURATION
   // ===========================================================================

   /**
    * JWT Secret Key for Token Generation
    * This key is used to sign and verify JWT tokens for API authentication
    */
   define('JWT_AUTH_SECRET_KEY', '+I9keMOre2M5MssEe6uft2WVo4mDbE+L4fWH0VRRtWZUpld1FiGsH/xd5vmExGfkd67ykucqKxz2LG3qJTg8Ug==');

   /**
    * Enable CORS for JWT Authentication
    * Allows React frontend to make authenticated API requests
    */
   define('JWT_AUTH_CORS_ENABLE', true);

   // ===========================================================================
   // END JWT AUTHENTICATION CONFIGURATION
   // ===========================================================================
   ```

7. **Save the File:**
   - Click **"Save Changes"** button
   - File Manager will save and close the editor

8. **Verify:**
   - Your wp-config.php now has JWT authentication enabled
   - The site should continue working normally

---

### Option 2: SFTP (For Advanced Users)

If you prefer using SFTP:

1. **Get SFTP Credentials:**
   - WP Engine Dashboard > Your Site > **"SFTP/SSH"**
   - Copy the credentials shown

2. **Connect via SFTP Client:**
   - Use FileZilla, Cyberduck, or Transmit
   - Host: `westgategroup.sftp.wpengine.com`
   - Username: `westgategroup-username`
   - Password: [from dashboard]
   - Port: 2222

3. **Edit wp-config.php:**
   - Navigate to site root
   - Download `wp-config.php` to edit locally
   - Add JWT configuration as shown above
   - Upload the modified file back
   - Overwrite when prompted

---

## 🎯 What This Configuration Does

**JWT_AUTH_SECRET_KEY:**
- 256-bit cryptographic key for signing JWT tokens
- Used to encrypt/decrypt authentication tokens
- Must remain secret and secure

**JWT_AUTH_CORS_ENABLE:**
- Enables Cross-Origin Resource Sharing (CORS)
- Allows your React frontend to call WordPress REST API
- Required for localhost development and production

---

## ✅ Verification

After adding the configuration:

### 1. Test WordPress Admin Still Works
```
Visit: https://westgategroup.wpengine.com/wp-admin/
Should load without errors ✓
```

### 2. Test JWT Token Endpoint
```bash
curl -X POST https://westgategroup.wpengine.com/wp-json/jwt-auth/v1/token \
  -H "Content-Type: application/json" \
  -d '{
    "username": "your-username",
    "password": "your-app-password"
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

### 3. Test CNF REST API Endpoints
```bash
# Bootstrap endpoint
curl https://westgategroup.wpengine.com/wp-json/cnf/v1/bootstrap

# Theme options endpoint
curl https://westgategroup.wpengine.com/wp-json/cnf/v1/theme-options
```

**Expected:** JSON data returns successfully (not errors)

---

## 🔒 Security Best Practices

1. **Never Commit wp-config.php to Git**
   - The file contains database credentials
   - JWT secret key should remain private
   - Already excluded via .gitignore

2. **Keep JWT Secret Secure**
   - Don't share the secret key
   - Don't commit to public repositories
   - Rotate the key every 6-12 months

3. **Use Strong Passwords**
   - WordPress Application Passwords for API access
   - Never use main admin password for API

4. **Monitor Access Logs**
   - Check WP Engine logs for suspicious activity
   - Review failed authentication attempts

---

## 🆘 Troubleshooting

### "Fatal error after editing wp-config.php"
- **Cause:** Syntax error in the file
- **Fix:** Check for missing semicolons, quotes, or parentheses
- **Solution:** Restore from WP Engine backup and re-edit carefully

### "JWT token returns 404"
- **Cause:** JWT plugin not installed or not active
- **Fix:** Install "JWT Authentication for WP REST API" plugin
- **Verify:** Go to Plugins > Installed Plugins > Check if active

### "CORS errors in browser console"
- **Cause:** CORS not properly configured
- **Fix:** Verify `JWT_AUTH_CORS_ENABLE` is set to `true`
- **Check:** Verify `cnf-jwt-cors.php` MU-plugin is active

### "Can't access File Manager"
- **Cause:** Permissions issue with WP Engine account
- **Fix:** Contact WP Engine support
- **Alternative:** Use SFTP method instead

---

## 📞 Support

**WP Engine Support:**
- Portal: https://my.wpengine.com/support
- Phone: +1 (877) 973-6446
- Live Chat: Available in dashboard

**Need Help?**
- Full JWT setup guide: `JWT-SETUP.md`
- MU-Plugins documentation: Check each plugin's header comments
- REST API endpoints: `cnf-rest-api.php`

---

## ✨ After Configuration Complete

Once JWT is configured, your setup will be:

✅ WordPress serves data via REST API
✅ React app can fetch data securely
✅ All 178 theme options accessible
✅ CMS editors can update content
✅ Changes appear on frontend instantly

**Your WordPress → React connection is complete!** 🎉
