#!/bin/bash

# Workflow Fixer Script
# Orchestrates running fixers and AI fixing

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Parse arguments
API_KEY=""
SKIP_AI=false

while [[ "$#" -gt 0 ]]; do
    case $1 in
        --api-key=*) API_KEY="${1#*=}" ;;
        --skip-ai) SKIP_AI=true ;;
        *) echo "Unknown parameter passed: $1"; exit 1 ;;
    esac
    shift
done

if [ -z "$API_KEY" ] && [ "$SKIP_AI" = false ]; then
    echo -e "${RED}Error: --api-key is required unless --skip-ai is set${NC}"
    echo "Usage: $0 --api-key=YOUR_ANTHROPIC_API_KEY"
    echo "   or: $0 --skip-ai (to run fixers only without AI)"
    exit 1
fi

# Function to print section headers
print_header() {
    echo -e "\n${BLUE}=== $1 ===${NC}\n"
}

# Change to the directory where the script was called from (not script location)
cd "$(pwd -P)"

# ============================================================================
# PHASE 1: Run Fixers
# ============================================================================

print_header "PHASE 1: Running Fixers"

# Run the fixers script
if [ -f "./bin/run-fixers.sh" ]; then
    bash ./bin/run-fixers.sh
else
    echo -e "${RED}Error: run-fixers.sh not found${NC}"
    exit 1
fi

# ============================================================================
# PHASE 2: Run AI Fixing
# ============================================================================

if [ "$SKIP_AI" = true ]; then
    print_header "Skipping AI fixing"
    exit 0
fi

print_header "PHASE 2: Running AI Fixing"

# Run the AI fixer script
if [ -f "./bin/fix-ai.sh" ]; then
    bash ./bin/fix-ai.sh --api-key="$API_KEY"
else
    echo -e "${RED}Error: fix-ai.sh not found${NC}"
    exit 1
fi

print_header "Workflow Complete"
