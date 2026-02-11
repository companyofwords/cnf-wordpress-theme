<?php
/**
 * Disable Caching for CNF REST API Endpoints
 *
 * Add this to wp-content/mu-plugins/cnf-rest-api.php
 * at the TOP after the opening PHP tag
 */

// Add no-cache headers to CNF API endpoints
add_filter('rest_post_dispatch', function($result, $server, $request) {
    $route = $request->get_route();

    // Only apply to CNF endpoints
    if (strpos($route, '/cnf/v1/') === 0) {
        $server->send_header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $server->send_header('Pragma', 'no-cache');
        $server->send_header('Expires', '0');
        $server->send_header('X-CNF-Cache', 'disabled');
    }

    return $result;
}, 10, 3);
