<?php
/**
 * Test bootstrap endpoint directly via WP-CLI (bypasses WP Engine cache)
 */

// Call the function directly
if (function_exists('cnf_get_bootstrap_data_fresh')) {
    $data = cnf_get_bootstrap_data_fresh();
    $options_count = isset($data['options']) ? count($data['options']) : 0;
    WP_CLI::success("bootstrap-fresh returns $options_count options");
} else {
    WP_CLI::error("cnf_get_bootstrap_data_fresh() not found!");
}

// Also test regular bootstrap
if (function_exists('cnf_get_bootstrap_data')) {
    $data = cnf_get_bootstrap_data();
    $options_count = isset($data['options']) ? count($data['options']) : 0;
    WP_CLI::success("bootstrap returns $options_count options");
} else {
    WP_CLI::error("cnf_get_bootstrap_data() not found!");
}
