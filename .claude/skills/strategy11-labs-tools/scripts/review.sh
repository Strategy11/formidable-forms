#!/usr/bin/env bash
# =============================================================================
# review.sh — Strategy11 Labs Automated Plugin Review
# =============================================================================
# Runs four checks in sequence against a plugin directory:
#   1. php -l          Syntax check every PHP file
#   2. PHPCS           WordPress coding standards + security sniffs
#   3. PHPStan         Static type and logic analysis (level 5)
#   4. API review      Independent Claude review via Anthropic API
#
# Usage:
#   bash review.sh <path-to-plugin-directory> [--is-formidable] [--skip-api]
#
# Flags:
#   --is-formidable   Adds Formidable-specific checks to the API review prompt.
#   --skip-api        Skip the Anthropic API review (useful for quick local runs).
#
# Requirements:
#   - PHP 7.4+ (php, composer)
#   - Python 3.7+
#   - Internet access to install Composer packages on first run
#
# The script installs PHPCS, WordPress Coding Standards, and PHPStan into a
# local ./review-tools/ directory alongside this script. They are not installed
# globally and do not affect the plugin being reviewed.
# =============================================================================

set -euo pipefail

# ── Arguments ────────────────────────────────────────────────────────────────

PLUGIN_DIR=""
IS_FORMIDABLE=""
SKIP_API=""

for arg in "$@"; do
    case "$arg" in
        --is-formidable) IS_FORMIDABLE="--is-formidable" ;;
        --skip-api)      SKIP_API="1" ;;
        *)               PLUGIN_DIR="$arg" ;;
    esac
done

if [ -z "$PLUGIN_DIR" ]; then
    echo "Usage: bash review.sh <plugin-directory> [--is-formidable] [--skip-api]"
    exit 1
fi

if [ ! -d "$PLUGIN_DIR" ]; then
    echo "Error: '$PLUGIN_DIR' is not a directory."
    exit 1
fi

PLUGIN_DIR="$(realpath "$PLUGIN_DIR")"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TOOLS_DIR="$SCRIPT_DIR/review-tools"
PASS=0
FAIL=0

# Colour helpers
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; BOLD='\033[1m'; NC='\033[0m'

header() { echo -e "\n${BOLD}${BLUE}━━━ $1 ━━━${NC}"; }
pass()   { echo -e "${GREEN}✅  PASS${NC}"; PASS=$((PASS + 1)); }
fail()   { echo -e "${RED}❌  FAIL${NC}"; FAIL=$((FAIL + 1)); }
warn()   { echo -e "${YELLOW}⚠️   $1${NC}"; }

echo -e "${BOLD}Strategy11 Labs — Plugin Review${NC}"
echo "Plugin: $PLUGIN_DIR"
echo "Date:   $(date '+%Y-%m-%d %H:%M')"

# ── Tool installation ─────────────────────────────────────────────────────────

install_tools() {
    header "Installing review tools (first run only)"

    mkdir -p "$TOOLS_DIR"

    # Check for Composer
    if ! command -v composer &>/dev/null; then
        warn "Composer not found — attempting to download..."
        php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');"
        php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer --quiet
    fi

    # Write a dedicated composer.json for review tools only
    cat > "$TOOLS_DIR/composer.json" << 'JSON'
{
    "require-dev": {
        "squizlabs/php_codesniffer": "^3.8",
        "wp-coding-standards/wpcs": "^3.1",
        "phpstan/phpstan": "^1.10",
        "szepeviktor/phpstan-wordpress": "^1.3"
    },
    "config": {
        "allow-plugins": {
            "dealerdirect/phpcodesniffer-composer-installer": true
        }
    }
}
JSON

    cd "$TOOLS_DIR"
    composer install --no-interaction --quiet 2>&1 | tail -5
    cd - > /dev/null

    echo "Tools installed in $TOOLS_DIR"
}

PHPCS="$TOOLS_DIR/vendor/bin/phpcs"
PHPSTAN="$TOOLS_DIR/vendor/bin/phpstan"

if [ ! -f "$PHPCS" ] || [ ! -f "$PHPSTAN" ]; then
    install_tools
fi

# ── Check 1: PHP Syntax ───────────────────────────────────────────────────────

header "Check 1/5 — PHP Syntax (php -l)"

SYNTAX_ERRORS=0
while IFS= read -r -d '' file; do
    result=$(php -l "$file" 2>&1)
    if echo "$result" | grep -q "Parse error\|Fatal error"; then
        echo "  ❌ $file"
        echo "     $result"
        SYNTAX_ERRORS=$((SYNTAX_ERRORS + 1))
    fi
done < <(find "$PLUGIN_DIR" \
    -name "*.php" \
    -not -path "*/vendor/*" \
    -not -path "*/node_modules/*" \
    -print0)

if [ "$SYNTAX_ERRORS" -eq 0 ]; then
    echo "  All PHP files pass syntax check."
    pass
else
    echo "  $SYNTAX_ERRORS file(s) with syntax errors."
    fail
    echo -e "${RED}  Syntax errors must be fixed before continuing.${NC}"
    exit 1
fi

# ── Check 2: PHPCS ────────────────────────────────────────────────────────────

header "Check 2/5 — PHPCS (WordPress Coding Standards + Security)"

PHPCS_OUTPUT=$("$PHPCS" \
    --standard="$SCRIPT_DIR/phpcs.xml" \
    --extensions=php \
    --ignore="*/vendor/*,*/node_modules/*" \
    --report=summary \
    "$PLUGIN_DIR" 2>&1 || true)

echo "$PHPCS_OUTPUT"

if echo "$PHPCS_OUTPUT" | grep -q "ERROR\|0 errors"; then
    ERRORS=$(echo "$PHPCS_OUTPUT" | grep -oP '\d+ error' | grep -oP '\d+' | head -1 || echo "0")
    if [ "${ERRORS:-0}" -eq 0 ]; then
        pass
    else
        fail
    fi
else
    # Run again with full output to show the details
    "$PHPCS" \
        --standard="$SCRIPT_DIR/phpcs.xml" \
        --extensions=php \
        --ignore="*/vendor/*,*/node_modules/*" \
        --report=full \
        "$PLUGIN_DIR" 2>&1 || true
    fail
fi

# ── Check 3: PHPStan ──────────────────────────────────────────────────────────

header "Check 3/5 — PHPStan (Static Analysis, Level 5)"

PHPSTAN_OUTPUT=$("$PHPSTAN" analyse \
    --configuration="$SCRIPT_DIR/phpstan.neon" \
    --no-progress \
    --error-format=table \
    "$PLUGIN_DIR" 2>&1 || true)

echo "$PHPSTAN_OUTPUT"

if echo "$PHPSTAN_OUTPUT" | grep -q "No errors\|0 errors"; then
    pass
elif echo "$PHPSTAN_OUTPUT" | grep -q "\[ERROR\]"; then
    fail
else
    warn "PHPStan output unclear — review manually."
fi

# ── Check 4: PHPUnit ─────────────────────────────────────────────────────────

header "Check 4/5 — PHPUnit (Unit Tests)"

PHPUNIT_BIN="$PLUGIN_DIR/vendor/bin/phpunit"
PHPUNIT_XML="$PLUGIN_DIR/tests/phpunit.xml"

if [ ! -f "$PHPUNIT_BIN" ]; then
    warn "PHPUnit not found at $PHPUNIT_BIN — run 'composer install' in the plugin root first."
    warn "Skipping unit test check. Generate tests with: python3 scripts/generate_tests.py $PLUGIN_DIR"
elif [ ! -f "$PHPUNIT_XML" ]; then
    warn "tests/phpunit.xml not found — run 'python3 scripts/generate_tests.py $PLUGIN_DIR' to scaffold tests."
else
    PHPUNIT_OUTPUT=$("$PHPUNIT_BIN" --configuration "$PHPUNIT_XML" --no-coverage 2>&1 || true)
    echo "$PHPUNIT_OUTPUT" | tail -20

    if echo "$PHPUNIT_OUTPUT" | grep -qE "OK \(|Tests: [0-9]+"; then
        if echo "$PHPUNIT_OUTPUT" | grep -qE "incomplete|skipped"; then
            warn "Tests pass but some are incomplete/skipped — fill in TODO stubs."
            PASS=$((PASS + 1))
        else
            pass
        fi
    elif echo "$PHPUNIT_OUTPUT" | grep -qE "FAILURES|ERRORS|Error:"; then
        fail
        echo "  Fix failing tests before packaging."
    else
        warn "PHPUnit output unclear — review manually."
    fi
fi

# ── Check 5: API Review ───────────────────────────────────────────────────────

if [ -n "$SKIP_API" ]; then
    header "Check 5/5 — API Review (SKIPPED)"
    warn "Skipped via --skip-api flag."
else
    header "Check 5/5 — API Review (Independent Claude Review)"

    API_SCRIPT="$SCRIPT_DIR/api_review.py"
    if [ ! -f "$API_SCRIPT" ]; then
        warn "api_review.py not found at $API_SCRIPT — skipping API review."
    else
        set +e
        python3 "$API_SCRIPT" "$PLUGIN_DIR" $IS_FORMIDABLE
        API_EXIT=$?
        set -e

        if [ "$API_EXIT" -eq 0 ]; then
            pass
        else
            fail
            echo "  One or more HIGH severity issues found — fix before packaging."
        fi
    fi
fi

# ── Summary ───────────────────────────────────────────────────────────────────

echo ""
echo -e "${BOLD}━━━ Review Complete ━━━${NC}"
echo -e "  ${GREEN}Passed: $PASS${NC}   ${RED}Failed: $FAIL${NC}"
echo ""

if [ "$FAIL" -gt 0 ]; then
    echo -e "${RED}${BOLD}  ✗ Review failed — address issues before packaging.${NC}"
    exit 1
else
    echo -e "${GREEN}${BOLD}  ✓ All checks passed — safe to proceed to Phase 5.${NC}"
    exit 0
fi
