#!/bin/bash

echo "Testing bootstrap endpoint with verbose error reporting..."
echo ""

# Try with various headers to see if we can get error details
curl -v "https://westgategroup.wpengine.com/wp-json/cnf/v1/bootstrap" \
  -H "Accept: application/json" \
  -H "X-WP-Debug: true" \
  2>&1 | grep -A 20 "< HTTP"

echo ""
echo "========================================================================"
echo ""

# Check if there's a REST API error response
echo "Attempting to get REST API error details..."
curl -s "https://westgategroup.wpengine.com/wp-json/cnf/v1/bootstrap" \
  -H "Accept: application/json" | python3 -m json.tool 2>/dev/null || echo "Not valid JSON response"

echo ""
echo "========================================================================"
