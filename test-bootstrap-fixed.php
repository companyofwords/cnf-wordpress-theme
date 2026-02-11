<?php
/**
 * Test if bootstrap function works after adding $request parameter
 */

WP_CLI::line('');
WP_CLI::line('=== Testing Fixed Bootstrap Function ===');
WP_CLI::line('');

// Test calling bootstrap function directly (simulating REST API call)
try {
    WP_CLI::line('Calling cnf_get_bootstrap_data()...');
    $result = cnf_get_bootstrap_data(null);
    
    if (is_wp_error($result)) {
        WP_CLI::error('Function returned WP_Error: ' . $result->get_error_message());
    }
    
    // Extract data from WP_REST_Response
    if (is_object($result) && method_exists($result, 'get_data')) {
        $data = $result->get_data();
    } else {
        $data = $result;
    }
    
    WP_CLI::success('Function executed successfully!');
    WP_CLI::line('');
    WP_CLI::line('Response structure:');
    WP_CLI::line('  - Keys: ' . implode(', ', array_keys($data)));
    WP_CLI::line('  - Options count: ' . count($data['options']));
    WP_CLI::line('  - Machines count: ' . count($data['pods']['cnf_machines']));
    WP_CLI::line('  - Pages count: ' . count($data['pages']));
    WP_CLI::line('');
    
} catch (Exception $e) {
    WP_CLI::error('Exception: ' . $e->getMessage());
} catch (TypeError $e) {
    WP_CLI::error('TypeError: ' . $e->getMessage());
}

WP_CLI::success('Bootstrap function is working! The fix is successful.');
WP_CLI::line('');
WP_CLI::line('Next step: Purge WP Engine cache via dashboard to clear cached error pages.');
