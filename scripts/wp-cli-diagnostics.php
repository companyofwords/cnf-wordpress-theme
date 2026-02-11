<?php
/**
 * WP-CLI Diagnostic Script for Bootstrap Endpoint
 * 
 * Usage: wp eval-file wp-cli-diagnostics.php
 * 
 * Tests each helper function individually to find the failing component
 */

// Color output helpers
function log_test($name) {
    WP_CLI::log(WP_CLI::colorize("%G[TEST]%n Testing: {$name}"));
}

function log_success($message) {
    WP_CLI::success($message);
}

function log_error($message) {
    WP_CLI::error($message, false); // Don't exit on error
}

function log_warning($message) {
    WP_CLI::warning($message);
}

function log_info($message) {
    WP_CLI::log(WP_CLI::colorize("%B[INFO]%n {$message}"));
}

// Test results storage
$results = [
    'passed' => [],
    'failed' => [],
    'warnings' => []
];

WP_CLI::log(WP_CLI::colorize("%Y" . str_repeat("=", 70) . "%n"));
WP_CLI::log(WP_CLI::colorize("%Y CNF Bootstrap Endpoint Diagnostics%n"));
WP_CLI::log(WP_CLI::colorize("%Y" . str_repeat("=", 70) . "%n"));
WP_CLI::log("");

// ============================================================================
// 1. Check WordPress Environment
// ============================================================================
log_test("WordPress Environment");
log_info("Site URL: " . get_site_url());
log_info("Home URL: " . home_url());
log_info("WordPress Version: " . get_bloginfo('version'));
log_info("PHP Version: " . PHP_VERSION);
log_success("Environment check complete");
echo "\n";

// ============================================================================
// 2. Check if Pods Framework is Available
// ============================================================================
log_test("Pods Framework");
if (function_exists('pods')) {
    log_success("Pods function exists");
    
    try {
        $test_pod = pods('machine', ['limit' => 1]);
        if ($test_pod) {
            log_success("Pods 'machine' CPT accessible (found " . $test_pod->total() . " records)");
            $results['passed'][] = "Pods Framework";
        } else {
            log_warning("Pods 'machine' CPT exists but returned null");
            $results['warnings'][] = "Pods machine CPT null response";
        }
    } catch (Exception $e) {
        log_error("Pods 'machine' error: " . $e->getMessage());
        $results['failed'][] = "Pods machine CPT: " . $e->getMessage();
    }
} else {
    log_error("Pods function does not exist - plugin may not be active");
    $results['failed'][] = "Pods Framework Not Available";
}
echo "\n";

// ============================================================================
// 3. Check if Plugin is Loaded
// ============================================================================
log_test("CNF REST API Plugin");
$plugin_file = WP_CONTENT_DIR . '/mu-plugins/cnf-rest-api.php';
if (file_exists($plugin_file)) {
    log_success("Plugin file exists: {$plugin_file}");
    
    // Check if functions are defined
    $functions_to_check = [
        'cnf_get_theme_options',
        'cnf_get_menus',
        'cnf_get_pages',
        'cnf_get_machines',
        'cnf_get_uses',
        'cnf_get_faqs',
        'cnf_get_news_posts',
        'cnf_get_bootstrap_data'
    ];
    
    foreach ($functions_to_check as $func) {
        if (function_exists($func)) {
            log_success("Function exists: {$func}");
        } else {
            log_error("Function missing: {$func}");
            $results['failed'][] = "Missing function: {$func}";
        }
    }
} else {
    log_error("Plugin file not found: {$plugin_file}");
    $results['failed'][] = "Plugin file not found";
}
echo "\n";

// ============================================================================
// 4. Test Individual Helper Functions
// ============================================================================

// Test 1: Theme Options (we know this works)
log_test("cnf_get_theme_options()");
try {
    $theme_options = cnf_get_theme_options();
    if (is_array($theme_options) && count($theme_options) > 0) {
        log_success("Returned " . count($theme_options) . " theme options");
        $results['passed'][] = "cnf_get_theme_options";
    } else {
        log_warning("Returned empty array");
        $results['warnings'][] = "cnf_get_theme_options: empty result";
    }
} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    $results['failed'][] = "cnf_get_theme_options: " . $e->getMessage();
}
echo "\n";

// Test 2: Menus
log_test("cnf_get_menus()");
try {
    $menus = cnf_get_menus();
    if (is_array($menus)) {
        log_success("Returned menus array with " . count($menus) . " menus");
        log_info("Menu locations: " . implode(", ", array_keys($menus)));
        $results['passed'][] = "cnf_get_menus";
    } else {
        log_warning("Did not return array");
        $results['warnings'][] = "cnf_get_menus: unexpected type";
    }
} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    $results['failed'][] = "cnf_get_menus: " . $e->getMessage();
}
echo "\n";

// Test 3: Pages
log_test("cnf_get_pages()");
try {
    $pages = cnf_get_pages();
    if (is_array($pages)) {
        log_success("Returned " . count($pages) . " pages");
        if (count($pages) > 0) {
            log_info("Sample page: " . (isset($pages[0]['title']) ? $pages[0]['title'] : 'N/A'));
        }
        $results['passed'][] = "cnf_get_pages";
    } else {
        log_warning("Did not return array");
        $results['warnings'][] = "cnf_get_pages: unexpected type";
    }
} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    $results['failed'][] = "cnf_get_pages: " . $e->getMessage();
}
echo "\n";

// Test 4: Machines (Pods)
log_test("cnf_get_machines()");
try {
    $machines = cnf_get_machines();
    if (is_array($machines)) {
        log_success("Returned " . count($machines) . " machines");
        if (count($machines) > 0) {
            log_info("Sample machine: " . (isset($machines[0]['title']) ? $machines[0]['title'] : 'N/A'));
        }
        $results['passed'][] = "cnf_get_machines";
    } else {
        log_warning("Did not return array");
        $results['warnings'][] = "cnf_get_machines: unexpected type";
    }
} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    log_error("Stack trace: " . $e->getTraceAsString());
    $results['failed'][] = "cnf_get_machines: " . $e->getMessage();
}
echo "\n";

// Test 5: Uses (Pods)
log_test("cnf_get_uses()");
try {
    $uses = cnf_get_uses();
    if (is_array($uses)) {
        log_success("Returned " . count($uses) . " uses");
        if (count($uses) > 0) {
            log_info("Sample use: " . (isset($uses[0]['title']) ? $uses[0]['title'] : 'N/A'));
        }
        $results['passed'][] = "cnf_get_uses";
    } else {
        log_warning("Did not return array");
        $results['warnings'][] = "cnf_get_uses: unexpected type";
    }
} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    log_error("Stack trace: " . $e->getTraceAsString());
    $results['failed'][] = "cnf_get_uses: " . $e->getMessage();
}
echo "\n";

// Test 6: FAQs (Pods)
log_test("cnf_get_faqs()");
try {
    $faqs = cnf_get_faqs();
    if (is_array($faqs)) {
        log_success("Returned FAQs with " . count($faqs) . " categories");
        if (count($faqs) > 0) {
            log_info("Sample category: " . (isset($faqs[0]['name']) ? $faqs[0]['name'] : 'N/A'));
        }
        $results['passed'][] = "cnf_get_faqs";
    } else {
        log_warning("Did not return array");
        $results['warnings'][] = "cnf_get_faqs: unexpected type";
    }
} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    log_error("Stack trace: " . $e->getTraceAsString());
    $results['failed'][] = "cnf_get_faqs: " . $e->getMessage();
}
echo "\n";

// Test 7: News Posts
log_test("cnf_get_news_posts()");
try {
    $news = cnf_get_news_posts();
    if (is_array($news)) {
        log_success("Returned " . count($news) . " news posts");
        if (count($news) > 0) {
            log_info("Sample post: " . (isset($news[0]['title']) ? $news[0]['title'] : 'N/A'));
        }
        $results['passed'][] = "cnf_get_news_posts";
    } else {
        log_warning("Did not return array");
        $results['warnings'][] = "cnf_get_news_posts: unexpected type";
    }
} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    log_error("Stack trace: " . $e->getTraceAsString());
    $results['failed'][] = "cnf_get_news_posts: " . $e->getMessage();
}
echo "\n";

// ============================================================================
// 5. Test Full Bootstrap Function
// ============================================================================
log_test("cnf_get_bootstrap_data() - Full Function");
try {
    $bootstrap = cnf_get_bootstrap_data();
    if (is_array($bootstrap)) {
        log_success("Bootstrap data returned successfully");
        log_info("Keys in bootstrap: " . implode(", ", array_keys($bootstrap)));
        
        // Check each expected key
        $expected_keys = ['theme_options', 'menus', 'pages', 'machines', 'uses', 'faqs', 'news'];
        foreach ($expected_keys as $key) {
            if (isset($bootstrap[$key])) {
                $count = is_array($bootstrap[$key]) ? count($bootstrap[$key]) : 'N/A';
                log_info("  - {$key}: {$count} items");
            } else {
                log_warning("  - {$key}: MISSING");
            }
        }
        $results['passed'][] = "cnf_get_bootstrap_data";
    } else {
        log_error("Did not return array");
        $results['failed'][] = "cnf_get_bootstrap_data: unexpected type";
    }
} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    log_error("Stack trace: " . $e->getTraceAsString());
    $results['failed'][] = "cnf_get_bootstrap_data: " . $e->getMessage();
}
echo "\n";

// ============================================================================
// 6. Test REST API Endpoint Directly
// ============================================================================
log_test("REST API Endpoint (internal request)");
try {
    $request = new WP_REST_Request('GET', '/cnf/v1/bootstrap');
    $response = rest_do_request($request);
    $data = $response->get_data();
    
    if ($response->is_error()) {
        log_error("REST API returned error: " . $response->get_error_message());
        $results['failed'][] = "REST API Endpoint: " . $response->get_error_message();
    } else {
        log_success("REST API endpoint returned successfully");
        log_info("Response status: " . $response->get_status());
        if (is_array($data)) {
            log_info("Response keys: " . implode(", ", array_keys($data)));
        }
        $results['passed'][] = "REST API Endpoint";
    }
} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    $results['failed'][] = "REST API Endpoint: " . $e->getMessage();
}
echo "\n";

// ============================================================================
// 7. Summary Report
// ============================================================================
WP_CLI::log(WP_CLI::colorize("%Y" . str_repeat("=", 70) . "%n"));
WP_CLI::log(WP_CLI::colorize("%Y DIAGNOSTIC SUMMARY%n"));
WP_CLI::log(WP_CLI::colorize("%Y" . str_repeat("=", 70) . "%n"));
echo "\n";

if (count($results['passed']) > 0) {
    WP_CLI::log(WP_CLI::colorize("%G✓ PASSED (" . count($results['passed']) . "):%n"));
    foreach ($results['passed'] as $test) {
        WP_CLI::log("  - {$test}");
    }
    echo "\n";
}

if (count($results['warnings']) > 0) {
    WP_CLI::log(WP_CLI::colorize("%Y⚠ WARNINGS (" . count($results['warnings']) . "):%n"));
    foreach ($results['warnings'] as $test) {
        WP_CLI::log("  - {$test}");
    }
    echo "\n";
}

if (count($results['failed']) > 0) {
    WP_CLI::log(WP_CLI::colorize("%R✗ FAILED (" . count($results['failed']) . "):%n"));
    foreach ($results['failed'] as $test) {
        WP_CLI::log("  - {$test}");
    }
    echo "\n";
}

// Final verdict
if (count($results['failed']) === 0) {
    WP_CLI::success("All tests passed! Bootstrap endpoint should be working.");
} else {
    WP_CLI::error("Some tests failed. See details above.", false);
    WP_CLI::log(WP_CLI::colorize("%RThe failing component(s) are causing the bootstrap endpoint to return an error page.%n"));
}

WP_CLI::log(WP_CLI::colorize("%Y" . str_repeat("=", 70) . "%n"));
