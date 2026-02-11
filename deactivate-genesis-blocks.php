<?php
/**
 * Deactivate Genesis Blocks Plugin
 *
 * Visit: https://westgategroup.wpengine.com/deactivate-genesis-blocks.php?key=deactivate-2024
 * Delete this file after running.
 */

require_once('wp-load.php');

if (!isset($_GET['key']) || $_GET['key'] !== 'deactivate-2024') {
    die('Access denied. Add ?key=deactivate-2024');
}

echo '<h1>Deactivating Genesis Blocks</h1><pre>';

// Find Genesis Blocks plugin
$all_plugins = get_plugins();
$genesis_blocks_plugin = null;

foreach ($all_plugins as $plugin_path => $plugin_data) {
    if (stripos($plugin_data['Name'], 'Genesis Blocks') !== false ||
        stripos($plugin_path, 'genesis-blocks') !== false) {
        $genesis_blocks_plugin = $plugin_path;
        break;
    }
}

if ($genesis_blocks_plugin) {
    echo "Found Genesis Blocks: {$genesis_blocks_plugin}\n\n";

    if (is_plugin_active($genesis_blocks_plugin)) {
        deactivate_plugins($genesis_blocks_plugin);
        echo "✓ Genesis Blocks has been deactivated!\n\n";
    } else {
        echo "ℹ Genesis Blocks is already deactivated.\n\n";
    }
} else {
    echo "ℹ Genesis Blocks plugin not found.\n\n";
}

// Show all active plugins
echo "Currently active plugins:\n";
echo str_repeat('=', 60) . "\n";
$active_plugins = get_option('active_plugins');
foreach ($active_plugins as $plugin) {
    if (isset($all_plugins[$plugin])) {
        echo "- " . $all_plugins[$plugin]['Name'] . " (v" . $all_plugins[$plugin]['Version'] . ")\n";
    }
}

echo "\n</pre>";
echo '<p><strong>Done! <a href="/wp-admin">Return to Dashboard</a></strong></p>';
echo '<p style="color: red;"><strong>IMPORTANT: Delete this file (deactivate-genesis-blocks.php) for security.</strong></p>';
