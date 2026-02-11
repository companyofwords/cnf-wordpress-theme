#!/bin/bash

# CNF Bootstrap Diagnostics Runner
# This script helps you run the diagnostic test on WP Engine

echo "========================================================================"
echo "CNF Bootstrap Diagnostics - WP Engine Remote Execution"
echo "========================================================================"
echo ""

# Check if we're in the correct directory
if [ ! -f "wp-cli-diagnostics.php" ]; then
    echo "Error: wp-cli-diagnostics.php not found in current directory"
    echo "Please run this script from the /Users/neil/Documents/Wordsco/cnf directory"
    exit 1
fi

echo "Diagnostic script found: wp-cli-diagnostics.php"
echo ""
echo "OPTION 1: Run via SSH (Recommended)"
echo "------------------------------------"
echo "1. Upload the diagnostic script to WP Engine:"
echo "   scp wp-cli-diagnostics.php USERNAME@westgategroup.ssh.wpengine.net:/home/wpe-user/sites/westgategroup/"
echo ""
echo "2. SSH into WP Engine:"
echo "   ssh USERNAME@westgategroup.ssh.wpengine.net"
echo ""
echo "3. Run the diagnostic:"
echo "   cd sites/westgategroup"
echo "   wp eval-file wp-cli-diagnostics.php"
echo ""
echo ""
echo "OPTION 2: Quick Test via curl"
echo "------------------------------"
echo "Test if Pods plugin is active:"
echo ""

# Test if Pods plugin is active
echo "Testing Pods plugin status..."
curl -s "https://westgategroup.wpengine.com/wp-json/wp/v2/plugins" \
  -H "Accept: application/json" 2>/dev/null | grep -i "pods" || echo "Unable to query plugins (may need authentication)"

echo ""
echo ""
echo "OPTION 3: Simple inline test"
echo "-----------------------------"
echo "You can also test individual functions with wp eval:"
echo ""
echo "Example commands to run via SSH:"
echo "  wp eval 'var_dump(function_exists(\"pods\"));'"
echo "  wp eval 'var_dump(function_exists(\"cnf_get_machines\"));'"
echo "  wp eval '\$m = cnf_get_machines(); echo count(\$m) . \" machines\";'"
echo ""
echo "========================================================================"
echo ""
echo "Would you like me to try a simple inline test now? (requires SSH access)"
read -p "Enter WP Engine SSH username (or press Enter to skip): " WP_USERNAME

if [ -n "$WP_USERNAME" ]; then
    echo ""
    echo "Testing Pods availability..."
    ssh "$WP_USERNAME@westgategroup.ssh.wpengine.net" "cd sites/westgategroup && wp eval 'echo function_exists(\"pods\") ? \"Pods: ACTIVE\" : \"Pods: NOT FOUND\";'"
    
    echo ""
    echo "Testing cnf_get_machines function..."
    ssh "$WP_USERNAME@westgategroup.ssh.wpengine.net" "cd sites/westgategroup && wp eval 'echo function_exists(\"cnf_get_machines\") ? \"Function: EXISTS\" : \"Function: NOT FOUND\";'"
    
    echo ""
    echo "Attempting to call cnf_get_machines()..."
    ssh "$WP_USERNAME@westgategroup.ssh.wpengine.net" "cd sites/westgategroup && wp eval 'try { \$m = cnf_get_machines(); echo \"SUCCESS: \" . count(\$m) . \" machines\"; } catch (Exception \$e) { echo \"ERROR: \" . \$e->getMessage(); }'"
else
    echo "Skipping inline tests."
fi

echo ""
echo "========================================================================"
echo "Next Steps:"
echo "1. Use one of the options above to run the full diagnostic"
echo "2. The diagnostic will identify which specific function is failing"
echo "3. Report back with the results"
echo "========================================================================"
