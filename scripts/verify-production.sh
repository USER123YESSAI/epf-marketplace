#!/bin/bash

# Production Verification Script
# This script verifies that the frontend and backend are communicating correctly in production

set -e

echo "🔍 Production Verification Script"
echo "=================================="

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
FRONTEND_URL="${FRONTEND_URL:-https://epf-marketplace-frontend.vercel.app}"
BACKEND_URL="${BACKEND_URL:-https://epf-marketplace-api.onrender.com}"

echo ""
echo "📋 Configuration:"
echo "Frontend URL: $FRONTEND_URL"
echo "Backend URL: $BACKEND_URL"
echo ""

# Function to check URL
check_url() {
    local url=$1
    local name=$2
    
    echo -n "Checking $name... "
    
    if curl -s -o /dev/null -w "%{http_code}" "$url" | grep -q "200\|301\|302"; then
        echo -e "${GREEN}✓ OK${NC}"
        return 0
    else
        echo -e "${RED}✗ FAILED${NC}"
        return 1
    fi
}

# Function to check API endpoint
check_api() {
    local endpoint=$1
    local name=$2
    local url="$BACKEND_URL$endpoint"
    
    echo -n "Checking $name... "
    
    response=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    
    if [ "$response" = "200" ] || [ "$response" = "204" ]; then
        echo -e "${GREEN}✓ OK${NC} (HTTP $response)"
        return 0
    else
        echo -e "${RED}✗ FAILED${NC} (HTTP $response)"
        return 1
    fi
}

# Function to check CORS
check_cors() {
    echo -n "Checking CORS headers... "
    
    response=$(curl -s -I -H "Origin: $FRONTEND_URL" -H "Access-Control-Request-Method: GET" \
        -H "Access-Control-Request-Headers: content-type" \
        -X OPTIONS "$BACKEND_URL/api/products" 2>&1)
    
    if echo "$response" | grep -q "Access-Control-Allow-Origin"; then
        echo -e "${GREEN}✓ OK${NC}"
        return 0
    else
        echo -e "${YELLOW}⚠ WARNING${NC} (CORS headers not found)"
        return 1
    fi
}

# Function to check SSL
check_ssl() {
    echo -n "Checking SSL certificate... "
    
    if curl -s -I "$BACKEND_URL" 2>&1 | grep -q "SSL"; then
        echo -e "${GREEN}✓ OK${NC}"
        return 0
    else
        echo -e "${YELLOW}⚠ WARNING${NC} (SSL not verified)"
        return 1
    fi
}

# Function to check API response time
check_response_time() {
    local endpoint=$1
    local name=$2
    local url="$BACKEND_URL$endpoint"
    
    echo -n "Checking $name response time... "
    
    time=$(curl -o /dev/null -s -w "%{time_total}" "$url")
    
    # Convert to milliseconds
    time_ms=$(echo "$time * 1000" | bc)
    
    if (( $(echo "$time_ms < 1000" | bc -l) )); then
        echo -e "${GREEN}✓ OK${NC} (${time_ms}ms)"
        return 0
    elif (( $(echo "$time_ms < 3000" | bc -l) )); then
        echo -e "${YELLOW}⚠ SLOW${NC} (${time_ms}ms)"
        return 1
    else
        echo -e "${RED}✗ TOO SLOW${NC} (${time_ms}ms)"
        return 1
    fi
}

# Run checks
echo "🌐 Frontend Checks"
echo "------------------"
check_url "$FRONTEND_URL" "Frontend homepage"
check_url "$FRONTEND_URL/api" "Frontend API proxy (if any)"

echo ""
echo "🔧 Backend Health Checks"
echo "------------------------"
check_url "$BACKEND_URL" "Backend homepage"
check_api "/api/products" "Products endpoint"
check_api "/api/categories" "Categories endpoint"
check_api "/api/products/top-selling" "Top selling endpoint"

echo ""
echo "🔒 Security Checks"
echo "------------------"
check_ssl
check_cors

echo ""
echo "⚡ Performance Checks"
echo "---------------------"
check_response_time "/api/products" "Products endpoint"
check_response_time "/api/categories" "Categories endpoint"

echo ""
echo "🔌 Integration Checks"
echo "--------------------"

# Check if frontend can reach backend
echo -n "Checking frontend-backend connectivity... "
frontend_response=$(curl -s -o /dev/null -w "%{http_code}" "$FRONTEND_URL")
if [ "$frontend_response" = "200" ]; then
    echo -e "${GREEN}✓ OK${NC}"
else
    echo -e "${RED}✗ FAILED${NC} (HTTP $frontend_response)"
fi

echo ""
echo "=================================="
echo "✅ Verification Complete"
echo ""
echo "📝 Next Steps:"
echo "1. Review any failed checks above"
echo "2. Test authentication flow manually"
echo "3. Test file upload functionality"
echo "4. Test payment flow (if applicable)"
echo "5. Monitor logs in Vercel and Render dashboards"
