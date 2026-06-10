#!/bin/bash

# AI Fixer Script
# Runs dry-run checks and uses AI to fix remaining issues
# This script skips the initial fixer phase for faster iteration

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
    echo "   or: $0 --skip-ai (to run dry-run checks only without AI)"
    exit 1
fi

# Function to print section headers
print_header() {
    echo -e "\n${BLUE}=== $1 ===${NC}\n"
}

# Function to run a command and check result
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

# Function to get file content
get_file_content() {
    local file="$1"
    if [ -f "$file" ]; then
        cat "$file"
    fi
}

# Function to fix a specific check with AI
fix_with_ai() {
    local check_name="$1"
    local check_command="$2"

    print_header "AI Fixing: $check_name"

    echo "API Key present: $([ -n "$API_KEY" ] && echo "Yes" || echo "No")"

    # Run the check to get error output
    local error_output
    error_output=$(eval "$check_command 2>&1" || true)

    if [ -z "$error_output" ]; then
        echo -e "${GREEN}No errors found for $check_name${NC}"
        return 0
    fi

    echo "Error output captured (${#error_output} characters)"

    # Extract affected files from error output
    local affected_files
    affected_files=$(echo "$error_output" | grep -oE '[a-zA-Z0-9_/]+\.(php|js|jsx|ts|tsx|css|scss|toml|neon|xml)' | sort -u || true)

    if [ -z "$affected_files" ]; then
        echo "Could not extract affected files from error output"
        echo "Error output:"
        echo "$error_output"
        return 1
    fi

    echo "Affected files:"
    echo "$affected_files"

    # Determine the appropriate config file based on check type
    local config_file=""
    case $check_name in
        PHPCS)
            config_file="phpcs.xml"
            ;;
        PHPStan)
            config_file="phpstan.neon"
            ;;
        Psalm)
            config_file="psalm.xml"
            ;;
        PHP*)
            config_file="phpcs.xml"
            ;;
        ESLint|Oxlint)
            config_file=".oxlintrc.json"
            ;;
        Stylelint)
            config_file=".stylelintrc.json"
            ;;
        Typos)
            config_file="_typos.toml"
            ;;
        *)
            config_file=""
            ;;
    esac

    # Process each file
    while IFS= read -r file; do
        if [ -z "$file" ]; then
            continue
        fi

        if [ ! -f "$file" ]; then
            echo "File not found: $file"
            continue
        fi

        echo "Processing file: $file"

        local file_content
        file_content=$(get_file_content "$file")

        # Filter error output to only include errors for this specific file
        local file_errors
        file_errors=$(echo "$error_output" | grep -E "$file" || true)

        if [ -z "$file_errors" ]; then
            echo "No errors found for this file in error output"
            continue
        fi

        echo "Errors for this file:"
        echo "$file_errors"

        # Build the prompt for Claude
        local prompt
        prompt="You are a code analysis assistant. DO NOT modify the actual code logic. Instead, add ignore comments or config exceptions to suppress the errors.

Check tool: $check_name
File: $file

Error output (only for this file):
$file_errors

File content:
\`\`\`
$file_content
\`\`\`

Instructions:
1. DO NOT change the actual code logic or functionality
2. Add ignore comments or modify config files to suppress the errors
3. Use the SMALLEST scope possible for ignore comments (e.g., ignore specific variable/function, not entire lines)
4. Avoid broad comments like \"ignore next line\" when possible - prefer specific ignore directives
5. If a config file exists ($config_file), prefer adding exceptions there over inline comments
6. For PHPStan/Psalm: add to ignoreErrors in config with specific paths if possible
7. For PHPCS: use // phpcs:ignore with specific rule codes
8. For ESLint/Oxlint: use // eslint-disable-next-line with specific rule names
9. For Stylelint: use /* stylelint-disable-next-line */ with specific rule names
10. For Typos: add to _typos.toml config

Return ONLY the complete modified file content (or config file content if modifying config), nothing else. Do not include explanations, markdown code blocks, or any additional text. Just the raw file content."

        echo "Calling Claude API..."
        # Call Claude API
        local response
        response=$(curl -s https://api.anthropic.com/v1/messages \
            --header "x-api-key: $API_KEY" \
            --header "anthropic-version: 2023-06-01" \
            --header "content-type: application/json" \
            --data "{
                \"model\": \"claude-3-5-sonnet-20241022\",
                \"max_tokens\": 8192,
                \"messages\": [
                    {
                        \"role\": \"user\",
                        \"content\": \"$prompt\"
                    }
                ]
            }")

        echo "API response received (${#response} characters)"

        # Extract the content from response
        local fixed_content
        fixed_content=$(echo "$response" | jq -r '.content[0].text' 2>/dev/null || echo "")

        if [ -z "$fixed_content" ]; then
            echo "Failed to get response from Claude API"
            echo "Response: $response"
            continue
        fi

        echo "Fixed content received (${#fixed_content} characters)"

        # Remove markdown code blocks if present
        fixed_content=$(echo "$fixed_content" | sed 's/^```[a-z]*$//g' | sed 's/^```$//g')

        # Check if content actually changed
        if [ "$fixed_content" = "$file_content" ]; then
            echo "AI did not modify the file content, skipping write"
            continue
        fi

        # Check if content is suspiciously short or just "null"
        if [ ${#fixed_content} -lt 10 ] || [ "$fixed_content" = "null" ] || [ -z "$fixed_content" ]; then
            echo -e "${RED}✗ AI returned suspicious content (${#fixed_content} chars), skipping write to prevent file corruption${NC}"
            echo "Content: $fixed_content"
            continue
        fi

        # Write the fixed content back to the file
        echo "$fixed_content" > "$file"
        echo -e "${GREEN}✓ Added ignore directives to: $file${NC}"
    done <<< "$affected_files"

    # Re-run the check to see if it passes now
    if eval "$check_command"; then
        echo -e "${GREEN}✓ $check_name now passes${NC}"
        return 0
    else
        echo -e "${RED}✗ $check_name still has issues${NC}"
        return 1
    fi
}

# Change to the directory where the script was called from (not script location)
cd "$(pwd -P)"

# Load nvm for Node commands
if [ -f "$HOME/.nvm/nvm.sh" ]; then
    echo "Loading nvm and using Node 22..."
    export NVM_DIR="$HOME/.nvm"
    [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
    nvm use 22 2>/dev/null || nvm install 22
fi

# ============================================================================
# PHASE 1: Run Dry-Run Checks
# ============================================================================

print_header "PHASE 1: Running Dry-Run Checks"

# Store results of each check (use space-separated list for zsh compatibility)
FAILED_CHECKS=""

# PHP Checks
if ! run_command "./vendor/bin/php-cs-fixer fix --dry-run --allow-risky=yes" "PHP CS Fixer (dry-run)"; then
    FAILED_CHECKS="$FAILED_CHECKS phpcs_fixer"
fi

# Register custom PHPCS sniffs
CURRENT_PATHS=$(./vendor/bin/phpcs --config-show 2>/dev/null | grep 'installed_paths' | cut -d' ' -f2 || echo "")
if [ -n "$CURRENT_PATHS" ]; then
    ./vendor/bin/phpcs --config-set installed_paths "$CURRENT_PATHS,../../../phpcs-sniffs" 2>/dev/null || true
fi

if ! run_command "./vendor/bin/phpcs --parallel=10 ./" "PHPCS"; then
    FAILED_CHECKS="$FAILED_CHECKS phpcs"
fi

if ! run_command "./vendor/bin/phpstan analyze ./ --memory-limit=2G" "PHPStan"; then
    FAILED_CHECKS="$FAILED_CHECKS phpstan"
fi

if [ -f "./vendor/bin/psalm" ]; then
    if ! run_command "./vendor/bin/psalm" "Psalm"; then
        FAILED_CHECKS="$FAILED_CHECKS psalm"
    fi
else
    echo -e "${YELLOW}Psalm binary not found, skipping check${NC}"
fi

if ! run_command "./vendor/bin/rector process --dry-run" "Rector (dry-run)"; then
    FAILED_CHECKS="$FAILED_CHECKS rector"
fi

if ! run_command "./vendor/bin/mago lint" "Mago Lint"; then
    FAILED_CHECKS="$FAILED_CHECKS mago_lint"
fi

if ! run_command "./vendor/bin/mago analyze" "Mago Analyze"; then
    FAILED_CHECKS="$FAILED_CHECKS mago_analyze"
fi

# JS Checks
if ! run_command "./node_modules/.bin/eslint ." "ESLint (dry-run)"; then
    FAILED_CHECKS="$FAILED_CHECKS eslint"
fi

if ! run_command "./node_modules/.bin/oxlint . --format unix" "Oxlint"; then
    FAILED_CHECKS="$FAILED_CHECKS oxlint"
fi

# Typos check
if [ -f "_typos.toml" ]; then
    if command -v typos >/dev/null 2>&1; then
        if ! run_command "typos" "Typos"; then
            FAILED_CHECKS="$FAILED_CHECKS typos"
        fi
    else
        echo -e "${YELLOW}Typos not installed, skipping check${NC}"
    fi
fi

# Stylelint check
if [ -f ".stylelintrc.json" ] || [ -f ".stylelintrc" ]; then
    if [ -f "./node_modules/.bin/stylelint" ]; then
        if ! run_command "./node_modules/.bin/stylelint \"**/*.{css,scss}\"" "Stylelint"; then
            FAILED_CHECKS="$FAILED_CHECKS stylelint"
        fi
    else
        echo -e "${YELLOW}Stylelint binary not found, skipping check${NC}"
    fi
fi

# ============================================================================
# PHASE 2: AI Fixing (if needed)
# ============================================================================

echo ""
echo "Check results summary:"
if [ -z "$FAILED_CHECKS" ]; then
    echo "  All checks passed"
else
    echo "  Failed checks: $FAILED_CHECKS"
fi
echo ""

if [ -z "$FAILED_CHECKS" ]; then
    print_header "SUCCESS: All checks passed!"
    exit 0
fi

if [ "$SKIP_AI" = true ]; then
    print_header "Some checks failed, but AI fixing is skipped"
    echo "Failed checks: $FAILED_CHECKS"
    exit 1
fi

print_header "PHASE 2: AI Fixing for Failed Checks"
echo "Failed checks: $FAILED_CHECKS"
echo ""
echo "Attempting to fix with Claude AI..."

# Attempt to fix each failed check
echo ""
echo "Starting AI fixing loop"
for check in $FAILED_CHECKS; do
    echo ""
    echo "========================================"
    echo "Attempting to fix: $check"
    echo "========================================"
    case $check in
        phpcs_fixer)
            fix_with_ai "PHP CS Fixer" "./vendor/bin/php-cs-fixer fix --dry-run --allow-risky=yes"
            ;;
        phpcs)
            fix_with_ai "PHPCS" "./vendor/bin/phpcs --parallel=10 ./"
            ;;
        phpstan)
            fix_with_ai "PHPStan" "./vendor/bin/phpstan analyze ./ --memory-limit=2G"
            ;;
        psalm)
            fix_with_ai "Psalm" "./vendor/bin/psalm"
            ;;
        rector)
            fix_with_ai "Rector" "./vendor/bin/rector process --dry-run"
            ;;
        mago_lint)
            fix_with_ai "Mago Lint" "./vendor/bin/mago lint"
            ;;
        mago_analyze)
            fix_with_ai "Mago Analyze" "./vendor/bin/mago analyze"
            ;;
        eslint)
            fix_with_ai "ESLint" "./node_modules/.bin/eslint ."
            ;;
        oxlint)
            fix_with_ai "Oxlint" "./node_modules/.bin/oxlint . --format unix"
            ;;
        typos)
            fix_with_ai "Typos" "typos"
            ;;
        stylelint)
            fix_with_ai "Stylelint" "./node_modules/.bin/stylelint \"**/*.{css,scss}\""
            ;;
        *)
            echo "Unknown check: $check"
            ;;
    esac
    echo "Finished attempting to fix: $check"
done
echo ""
echo "AI fixing loop completed"

# ============================================================================
# PHASE 3: Final Verification
# ============================================================================

print_header "PHASE 3: Final Verification"

ALL_PASSED=true
FAILED_COUNT=0

# Re-run all checks
if ! ./vendor/bin/php-cs-fixer fix --dry-run --allow-risky=yes; then
    echo -e "${RED}✗ PHP CS Fixer still failing${NC}"
    ALL_PASSED=false
    ((FAILED_COUNT++))
fi

if ! ./vendor/bin/phpcs --parallel=10 ./; then
    echo -e "${RED}✗ PHPCS still failing${NC}"
    ALL_PASSED=false
    ((FAILED_COUNT++))
fi

if ! ./vendor/bin/phpstan analyze ./ --memory-limit=2G; then
    echo -e "${RED}✗ PHPStan still failing${NC}"
    ALL_PASSED=false
    ((FAILED_COUNT++))
fi

if ! ./vendor/bin/psalm; then
    echo -e "${RED}✗ Psalm still failing${NC}"
    ALL_PASSED=false
    ((FAILED_COUNT++))
fi

if ! ./vendor/bin/rector process --dry-run; then
    echo -e "${RED}✗ Rector still failing${NC}"
    ALL_PASSED=false
    ((FAILED_COUNT++))
fi

if ! ./vendor/bin/mago lint; then
    echo -e "${RED}✗ Mago Lint still failing${NC}"
    ALL_PASSED=false
    ((FAILED_COUNT++))
fi

if ! ./vendor/bin/mago analyze; then
    echo -e "${RED}✗ Mago Analyze still failing${NC}"
    ALL_PASSED=false
    ((FAILED_COUNT++))
fi

if ! ./node_modules/.bin/eslint .; then
    echo -e "${RED}✗ ESLint still failing${NC}"
    ALL_PASSED=false
    ((FAILED_COUNT++))
fi

if ! ./node_modules/.bin/oxlint . --format unix; then
    echo -e "${RED}✗ Oxlint still failing${NC}"
    ALL_PASSED=false
    ((FAILED_COUNT++))
fi

# Typos final check
if [ -f "_typos.toml" ] && command -v typos >/dev/null 2>&1; then
    if ! typos; then
        echo -e "${RED}✗ Typos still failing${NC}"
        ALL_PASSED=false
        ((FAILED_COUNT++))
    fi
fi

# Stylelint final check
if [ -f ".stylelintrc.json" ] || [ -f ".stylelintrc" ]; then
    if [ -f "./node_modules/.bin/stylelint" ]; then
        if ! ./node_modules/.bin/stylelint "**/*.{css,scss}"; then
            echo -e "${RED}✗ Stylelint still failing${NC}"
            ALL_PASSED=false
            ((FAILED_COUNT++))
        fi
    else
        echo -e "${YELLOW}Stylelint binary not found, skipping final check${NC}"
    fi
fi

echo ""
echo "Final verification: $FAILED_COUNT checks failed"

if [ "$ALL_PASSED" = true ]; then
    print_header "SUCCESS: All checks passed after AI fixing!"
    exit 0
else
    print_header "Some checks still failed after AI fixing"
    echo "Manual intervention may be required"
    exit 1
fi
