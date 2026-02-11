#!/bin/bash

# Quick endpoint status checker (no SSH required)

echo "========================================================================"
echo "CNF Bootstrap Endpoint - Quick Status Check"
echo "========================================================================"
echo ""

BASE_URL="https://westgategroup.wpengine.com"

echo "Testing REST API endpoints..."
echo ""

# Test 1: Working endpoint (theme-options)
echo "[1/3] Testing /wp-json/cnf/v1/theme-options (known working)..."
THEME_RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" "$BASE_URL/wp-json/cnf/v1/theme-options" -H "Accept: application/json")
THEME_CODE=$(echo "$THEME_RESPONSE" | grep "HTTP_CODE:" | cut -d: -f2)
THEME_BODY=$(echo "$THEME_RESPONSE" | sed '/HTTP_CODE:/d')

if [ "$THEME_CODE" = "200" ]; then
    echo "   ✓ Status: 200 OK"
    echo "   Response type: $(echo "$THEME_BODY" | head -c 1)"
    OPTION_COUNT=$(echo "$THEME_BODY" | grep -o '"' | wc -l)
    echo "   Appears to be: JSON (character count: ${#THEME_BODY})"
else
    echo "   ✗ Status: $THEME_CODE"
fi
echo ""

# Test 2: Bootstrap endpoint (failing)
echo "[2/3] Testing /wp-json/cnf/v1/bootstrap (problematic)..."
BOOTSTRAP_RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" "$BASE_URL/wp-json/cnf/v1/bootstrap" -H "Accept: application/json")
BOOTSTRAP_CODE=$(echo "$BOOTSTRAP_RESPONSE" | grep "HTTP_CODE:" | cut -d: -f2)
BOOTSTRAP_BODY=$(echo "$BOOTSTRAP_RESPONSE" | sed '/HTTP_CODE:/d')

if [ "$BOOTSTRAP_CODE" = "200" ]; then
    # Check if it's actually JSON or HTML
    if echo "$BOOTSTRAP_BODY" | grep -q "<!DOCTYPE\|<html"; then
        echo "   ✗ Status: 200 but returning HTML (error page)"
        echo "   First 200 chars:"
        echo "$BOOTSTRAP_BODY" | head -c 200
        echo "..."
        echo ""
        
        # Try to extract error message
        if echo "$BOOTSTRAP_BODY" | grep -q "Fatal error\|Parse error\|Warning"; then
            echo "   Error detected in HTML:"
            echo "$BOOTSTRAP_BODY" | grep -E "Fatal error|Parse error|Warning" | head -3
        fi
    else
        echo "   ✓ Status: 200 OK (JSON response)"
        echo "   Response size: ${#BOOTSTRAP_BODY} bytes"
    fi
else
    echo "   ✗ Status: $BOOTSTRAP_CODE"
fi
echo ""

# Test 3: Check if REST API is accessible
echo "[3/3] Testing WordPress REST API root..."
REST_RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" "$BASE_URL/wp-json/" -H "Accept: application/json")
REST_CODE=$(echo "$REST_RESPONSE" | grep "HTTP_CODE:" | cut -d: -f2)

if [ "$REST_CODE" = "200" ]; then
    echo "   ✓ WordPress REST API is accessible"
else
    echo "   ✗ Status: $REST_CODE"
fi
echo ""

echo "========================================================================"
echo "Summary:"
echo "--------"
echo "Theme Options endpoint: $([ "$THEME_CODE" = "200" ] && echo "✓ Working" || echo "✗ Failed")"

if echo "$BOOTSTRAP_BODY" | grep -q "<!DOCTYPE\|<html"; then
    echo "Bootstrap endpoint:     ✗ Returning HTML error page instead of JSON"
    echo ""
    echo "This confirms the endpoint is triggering a PHP error."
    echo "Run the full diagnostic script via SSH to identify the exact function failing."
else
    echo "Bootstrap endpoint:     $([ "$BOOTSTRAP_CODE" = "200" ] && echo "✓ Working" || echo "✗ Failed ($BOOTSTRAP_CODE)")"
fi
echo "========================================================================"
echo ""
echo "To run full diagnostics:"
echo "  ./run-diagnostics.sh"
echo ""
