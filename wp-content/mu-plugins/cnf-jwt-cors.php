<?php
/**
 * Plugin Name: CNF JWT CORS Configuration
 * Description: Enable CORS for JWT authentication and REST API access from React frontend
 * Version: 1.0.0
 * Author: Wordsco
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Only load if WordPress REST API is available
if (!function_exists('rest_api_init')) {
    return;
}

/**
 * Enable CORS for WordPress REST API
 * Allows React frontend to make authenticated requests
 */
add_action('rest_api_init', function() {
    // Get the origin from the request
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    // List of allowed origins
    $allowed_origins = array(
        'http://localhost:5173',           // Vite dev server
        'http://localhost:3000',           // Alternative dev server
        'https://cnfminidumper.co.uk',    // Production React app
        'https://www.cnfminidumper.co.uk', // Production with www
    );

    // Check if origin is allowed
    if (in_array($origin, $allowed_origins)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce');
    }
}, 10);

/**
 * Handle preflight OPTIONS requests
 */
add_action('init', function() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

        $allowed_origins = array(
            'http://localhost:5173',
            'http://localhost:3000',
            'https://cnfminidumper.co.uk',
            'https://www.cnfminidumper.co.uk',
        );

        if (in_array($origin, $allowed_origins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce');
            header('Access-Control-Max-Age: 86400');
            exit(0);
        }
    }
});

/**
 * Expose WordPress REST API endpoints info
 */
add_filter('rest_authentication_errors', function($result) {
    // If a previous authentication check was applied, respect that
    if (true === $result || is_wp_error($result)) {
        return $result;
    }

    // Allow unauthenticated access to public endpoints
    return $result;
});

/**
 * Log JWT errors for debugging
 */
add_filter('jwt_auth_token_before_dispatch', function($data, $user) {
    error_log('CNF JWT: Token issued for user ' . $user->user_login);
    return $data;
}, 10, 2);

add_filter('jwt_auth_valid_token_response', function($response, $user, $token) {
    error_log('CNF JWT: Valid token for user ' . $user->user_login);
    return $response;
}, 10, 3);
