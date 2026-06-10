#!/bin/bash

# Fixers Script
# Runs all code fixers in optimal order

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print section headers
print_header() {
    echo -e "\n${BLUE}=== $1 ===${NC}\n"
}

# Function to run command and check result
run_command() {
    local cmd="$1"
    local description="$2"
    
    print_header "$description"
    echo "Running: $cmd"
    
    if eval "$cmd"; then
        echo -e "${GREEN}✓ $description passed${NC}"
        return 0
    else
        echo -e "${RED}✗ $description failed${NC}"
        return 1
    fi
}

# Change to the directory where the script was called from (not script location)
cd "$(pwd -P)"

print_header "Running Fixers"

# PHP Fixers
# Run Rector first (major refactoring)
run_command "./vendor/bin/rector process" "Rector"

# Run PHP CS Fixer after Rector (style fixes)
run_command "./vendor/bin/php-cs-fixer fix --allow-risky=yes" "PHP CS Fixer"

# Re-run PHP CS Fixer in case Rector introduced style issues
run_command "./vendor/bin/php-cs-fixer fix --allow-risky=yes" "PHP CS Fixer (second pass)"

# Run PHPCBF for additional fixes
run_command "./vendor/bin/phpcbf --parallel=10 ./" "PHPCBF"

# JS Fixers
# Check if node_modules exists, if not install dependencies
if [ ! -d "node_modules" ]; then
    print_header "Installing Node dependencies"
    # Try to use nvm if available
    if [ -f "$HOME/.nvm/nvm.sh" ]; then
        echo "Loading nvm and using Node 22..."
        export NVM_DIR="$HOME/.nvm"
        [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
        nvm use 22 2>/dev/null || nvm install 22
    fi
    npm ci --include=dev --legacy-peer-deps
fi

# Ensure composer dependencies are up to date
if [ -f "composer.json" ]; then
    print_header "Ensuring composer dependencies are up to date"
    # Delete lock file to ensure fresh install with compatible versions
    if [ -f "composer.lock" ]; then
        echo "Removing composer.lock for fresh install..."
        rm composer.lock
    fi
    composer update --dev --prefer-dist --no-progress
fi

# Load nvm for Node commands
if [ -f "$HOME/.nvm/nvm.sh" ]; then
    echo "Loading nvm and using Node 22..."
    export NVM_DIR="$HOME/.nvm"
    [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
    nvm use 22 2>/dev/null || nvm install 22
fi

run_command "./node_modules/.bin/eslint . --fix" "ESLint"

# Install oxlint if not already installed
if [ ! -f "node_modules/.bin/oxlint" ]; then
    print_header "Installing Oxlint"
    npm install oxlint@1.59.0 --no-package-lock
fi

run_command "./node_modules/.bin/oxlint . --fix" "Oxlint"

print_header "Fixers Complete"
