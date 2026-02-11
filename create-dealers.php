<?php
/**
 * Create CNF Dealers
 *
 * Upload this file to your WordPress root and visit it in your browser:
 * https://westgategroup.wpengine.com/create-dealers.php
 *
 * This will create all 8 dealer posts with their metadata.
 * Delete this file after running it.
 */

// Load WordPress
require_once('wp-load.php');

// Security check - only allow in development or with specific key
if (!isset($_GET['key']) || $_GET['key'] !== 'create-dealers-2024') {
    die('Access denied. Add ?key=create-dealers-2024 to the URL to run this script.');
}

// Check if Pods is available
if (!function_exists('pods')) {
    die('Error: Pods Framework is not installed or activated.');
}

// Dealer data
$dealers = [
    [
        'name' => 'PARKWAY PLANT SALES LTD',
        'address' => "Bredbury Parkway, Bredbury,\nStockport, SK6 2SN",
        'sales_area' => 'Manchester, Stockport',
        'phone' => '0161 494 6000',
        'website' => 'parkwayplantsales.com',
    ],
    [
        'name' => 'GLYN LLOYD & SONS LTD',
        'address' => "Y Maes, Wern Bach, Abergele,\nConwy, LL22 9PY",
        'sales_area' => 'Flintshire, Wrexham, Denbighshire, Conwy, Gwynedd, Isle of Anglesey',
        'phone' => '01745 720286',
        'website' => 'glsplant.co.uk',
    ],
    [
        'name' => 'WESTPOINT PLANT SALES LTD',
        'address' => "UNIT E8 Formal Industrial Estate,\nTreswithian, Camborne,\nCornwall, TR14 0PY",
        'sales_area' => 'Cornwall & Devon',
        'phone' => '01209 808496',
        'website' => 'westpointplantsales.co.uk',
    ],
    [
        'name' => 'NORTHEDGE MACHINERY SALES',
        'address' => "Portrack Grange Road,\nStockton on Tees, TS18 2PH",
        'sales_area' => 'Northumberland, Durham, Yorkshire',
        'phone' => '01642 676698',
        'website' => 'www.northedgemachinery.com',
    ],
    [
        'name' => 'MFL PLANT MACHINERY LTD',
        'address' => "Unit 7 Sperrinview Business Park, Glen Road,\nMaghara, County Londonderry, BT46 5LT",
        'sales_area' => 'Northern Ireland',
        'phone' => '028 7964 5345',
        'website' => 'mflplantmachinery.com',
    ],
    [
        'name' => 'FTM MATERIALS HANDLING LTD',
        'address' => "North Road, Bridgend Industrial Estate,\nBridgend, CF31 3TP",
        'sales_area' => 'Monmouthshire, Glamorganshire',
        'phone' => '01656766200',
        'website' => 'ftmbridgend.co.uk',
    ],
    [
        'name' => 'LCF ENGINEERING LTD',
        'address' => "The Yard, Academy Street\nRiccarton, Kilmarnock, KA1 4DT",
        'sales_area' => 'Scotland',
        'phone' => '01355 222711',
        'website' => 'lcfengineering.co.uk',
    ],
    [
        'name' => 'GOLF & TURF EQUIPMENT LTD',
        'address' => "Pine Grove Farm, Mare Lane\nBeenham Heath, Shurlock Row\nReading, RG10 0QH",
        'sales_area' => 'Berkshire, Hampshire, Surrey, Oxfordshire, Buckinghamshire, South West London (South of Thames River)',
        'phone' => '0118 9340770',
        'website' => 'golfandturf.co.uk',
    ],
];

echo '<h1>Creating CNF Dealers</h1>';
echo '<pre>';

$created = 0;
$skipped = 0;
$errors = 0;

foreach ($dealers as $dealer_data) {
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "Processing: {$dealer_data['name']}\n";
    echo str_repeat('=', 60) . "\n";

    // Check if dealer already exists
    $existing = get_posts([
        'post_type' => 'cnf_dealer',
        'title' => $dealer_data['name'],
        'post_status' => 'any',
        'numberposts' => 1,
    ]);

    if (!empty($existing)) {
        echo "✓ Dealer already exists (ID: {$existing[0]->ID})\n";
        $skipped++;
        continue;
    }

    // Create the post
    $post_id = wp_insert_post([
        'post_title' => $dealer_data['name'],
        'post_type' => 'cnf_dealer',
        'post_status' => 'publish',
        'post_content' => '',
    ]);

    if (is_wp_error($post_id)) {
        echo "✗ Error creating dealer: " . $post_id->get_error_message() . "\n";
        $errors++;
        continue;
    }

    echo "✓ Created post (ID: {$post_id})\n";

    // Add Pods fields
    $pod = pods('cnf_dealer', $post_id);

    if ($pod) {
        $save_result = $pod->save([
            'name' => $dealer_data['name'],
            'address' => $dealer_data['address'],
            'sales_area' => $dealer_data['sales_area'],
            'phone' => $dealer_data['phone'],
            'website' => $dealer_data['website'],
        ]);

        if ($save_result) {
            echo "✓ Pods fields saved successfully\n";
            echo "  - Name: {$dealer_data['name']}\n";
            echo "  - Address: " . str_replace("\n", " ", $dealer_data['address']) . "\n";
            echo "  - Sales Area: {$dealer_data['sales_area']}\n";
            echo "  - Phone: {$dealer_data['phone']}\n";
            echo "  - Website: {$dealer_data['website']}\n";
            $created++;
        } else {
            echo "✗ Error saving Pods fields\n";
            $errors++;
        }
    } else {
        echo "✗ Error loading Pods object\n";
        $errors++;
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "SUMMARY\n";
echo str_repeat('=', 60) . "\n";
echo "Created: {$created}\n";
echo "Skipped (already exist): {$skipped}\n";
echo "Errors: {$errors}\n";
echo "Total: " . count($dealers) . "\n";

if ($created > 0) {
    echo "\n✓ SUCCESS! {$created} dealers were created.\n";
    echo "\nPlease delete this file (create-dealers.php) for security.\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "Verifying API endpoint...\n";
echo str_repeat('=', 60) . "\n";

// Test the cnf_get_dealers function
if (function_exists('cnf_get_dealers')) {
    $api_dealers = cnf_get_dealers();
    echo "✓ API function found\n";
    echo "  Dealers in API: " . count($api_dealers) . "\n";

    if (count($api_dealers) > 0) {
        echo "\nFirst dealer in API:\n";
        print_r($api_dealers[0]);
    }
} else {
    echo "✗ cnf_get_dealers() function not found\n";
    echo "  Make sure cnf-rest-api.php has been updated\n";
}

echo '</pre>';

// Clear WordPress cache
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo '<p>✓ WordPress cache cleared</p>';
}

echo '<p><strong>Done! <a href="/">Return to home</a></strong></p>';
echo '<p style="color: red;"><strong>IMPORTANT: Delete this file (create-dealers.php) for security.</strong></p>';
