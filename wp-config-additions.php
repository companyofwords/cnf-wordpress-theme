<?php
/**
 * JWT Authentication Configuration
 *
 * INSTRUCTIONS:
 * Copy the lines below (starting from line 11) and paste them into your wp-config.php file
 * on WP Engine, BEFORE the line that says: /* That's all, stop editing! Happy publishing. */
 *
 * HOW TO EDIT wp-config.php ON WP ENGINE:
 * 1. Log into WP Engine dashboard: https://my.wpengine.com/
 * 2. Select your site (westgategroup)
 * 3. Click "SFTP/SSH" or "File Manager"
 * 4. Open wp-config.php
 * 5. Scroll to the bottom, find: /* That's all, stop editing! Happy publishing. */
 * 6. PASTE THE LINES BELOW (lines 11-23) ABOVE that line
 * 7. Save the file
 *
 * SECURITY NOTE: This file is for reference only and should NOT be committed to Git
 * The actual wp-config.php on the server contains sensitive credentials
 */

// ===========================================================================
// JWT AUTHENTICATION CONFIGURATION
// ===========================================================================

/**
 * JWT Secret Key for Token Generation
 * This key is used to sign and verify JWT tokens for API authentication
 * KEEP THIS SECRET - Never share or commit to public repositories
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
