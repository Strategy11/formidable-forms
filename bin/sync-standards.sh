#!/bin/bash

# Sync Standards Script
# Dynamically syncs add-on repos with Formidable Lite's coding standards
# This script reads Lite's current configuration and applies it to add-ons

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Parse arguments
ADDON_PATH=""
LITE_PATH=""

while [[ "$#" -gt 0 ]]; do
    case $1 in
        --addon-path=*) ADDON_PATH="${1#*=}" ;;
        --lite-path=*) LITE_PATH="${1#*=}" ;;
        --help)
            echo "Usage: $0 [--addon-path=/path/to/addon] [--lite-path=/path/to/lite]"
            echo ""
            echo "If --addon-path is not provided, the current directory will be used."
            echo "If --lite-path is not provided, it will be determined automatically"
            echo "based on the script's location (assumes it's in formidable/bin/)"
            exit 0
            ;;
        *) echo "Unknown parameter passed: $1"; exit 1 ;;
    esac
    shift
done

# Use current directory as addon path if not provided
if [ -z "$ADDON_PATH" ]; then
    ADDON_PATH="$(pwd -P)"
    echo "Using current directory as add-on path: $ADDON_PATH"
fi

# Determine Lite path if not provided
if [ -z "$LITE_PATH" ]; then
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    LITE_PATH="$(dirname "$SCRIPT_DIR")"
fi

# Function to print section headers
print_header() {
    echo -e "\n${BLUE}=== $1 ===${NC}\n"
}

# Function to check if a file exists
file_exists() {
    [ -f "$1" ]
}

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to read JSON value
read_json() {
    local file="$1"
    local key="$2"
    if command_exists jq; then
        jq -r "$key" "$file" 2>/dev/null || echo ""
    else
        echo "jq not found, cannot read JSON"
        return 1
    fi
}

# Function to merge composer require-dev dependencies
merge_composer_deps() {
    local lite_composer="$1"
    local addon_composer="$2"

    print_header "Merging composer require-dev dependencies"

    echo "Lite composer: $lite_composer"
    echo "Addon composer: $addon_composer"

    if ! file_exists "$lite_composer"; then
        echo -e "${RED}Lite composer.json not found at $lite_composer${NC}"
        return 1
    fi

    if ! file_exists "$addon_composer"; then
        echo -e "${RED}Addon composer.json not found at $addon_composer${NC}"
        return 1
    fi

    if ! command_exists jq; then
        echo -e "${RED}jq is required to merge composer.json files${NC}"
        echo "Install with: brew install jq (macOS) or apt-get install jq (Linux)"
        return 1
    fi

    # Get Lite's require-dev dependencies
    local lite_deps
    lite_deps=$(jq -r '.["require-dev"] | keys[]' "$lite_composer" 2>/dev/null || true)

    if [ -z "$lite_deps" ]; then
        echo "No require-dev dependencies found in Lite composer.json"
        return 0
    fi

    echo "Found dependencies in Lite:"
    echo "$lite_deps"

    # For each dependency in Lite, check if it exists in addon
    added_count=0
    existing_count=0
    while IFS= read -r dep; do
        if [ -z "$dep" ]; then
            continue
        fi

        # Get version from Lite
        version=$(jq -r ".[\"require-dev\"][\"$dep\"]" "$lite_composer" 2>/dev/null || echo "")

        if [ -z "$version" ]; then
            continue
        fi

        # Check if dependency exists in addon
        addon_version=$(jq -r ".[\"require-dev\"][\"$dep\"]" "$addon_composer" 2>/dev/null || echo "")

        if [ -z "$addon_version" ] || [ "$addon_version" = "null" ] || [ "$addon_version" != "$version" ]; then
            if [ -z "$addon_version" ] || [ "$addon_version" = "null" ]; then
                echo -e "${YELLOW}Adding missing dependency: $dep ($version)${NC}"
            else
                echo -e "${YELLOW}Updating dependency version: $dep ($addon_version -> $version)${NC}"
            fi
            # Add dependency to addon composer.json
            echo "Running: jq \".[\\\"require-dev\\\"][\\\"$dep\\\"] = \\\"$version\\\"\" \"$addon_composer\""
            if jq ".[\"require-dev\"][\"$dep\"] = \"$version\"" "$addon_composer" > "${addon_composer}.tmp"; then
                echo "jq command succeeded, moving temp file"
                if mv "${addon_composer}.tmp" "$addon_composer"; then
                    echo -e "${GREEN}✓ Successfully added: $dep${NC}"
                    ((added_count++))
                else
                    echo -e "${RED}✗ Failed to move temp file for: $dep${NC}"
                fi
            else
                echo -e "${RED}✗ jq command failed for: $dep${NC}"
                if [ -f "${addon_composer}.tmp" ]; then
                    echo "Temp file content:"
                    cat "${addon_composer}.tmp"
                fi
            fi
        else
            echo -e "${GREEN}Dependency already exists: $dep ($addon_version)${NC}"
            ((existing_count++))
        fi
    done <<< "$lite_deps"

    echo ""
    echo "Dependencies added: $added_count"
    echo "Dependencies already existed: $existing_count"

    # Merge config section if needed
    local lite_config
    lite_config=$(jq '.config' "$lite_composer" 2>/dev/null || echo "{}")

    if [ "$lite_config" != "{}" ]; then
        echo "Merging config section..."
        if jq ".config = $lite_config" "$addon_composer" > "${addon_composer}.tmp" && mv "${addon_composer}.tmp" "$addon_composer"; then
            echo -e "${GREEN}✓ Config section merged${NC}"
        else
            echo -e "${RED}✗ Failed to merge config section${NC}"
        fi
    fi

    echo -e "${GREEN}✓ Composer dependencies merged${NC}"
}

# Function to merge npm devDependencies
merge_npm_deps() {
    local lite_package="$1"
    local addon_package="$2"

    print_header "Merging npm devDependencies"

    echo "Lite package.json: $lite_package"
    echo "Addon package.json: $addon_package"

    if ! file_exists "$lite_package"; then
        echo -e "${RED}Lite package.json not found at $lite_package${NC}"
        return 1
    fi

    if ! file_exists "$addon_package"; then
        echo -e "${RED}Addon package.json not found at $addon_package${NC}"
        return 1
    fi

    if ! command -v jq >/dev/null 2>&1; then
        echo -e "${RED}jq is required to merge package.json files${NC}"
        echo "Install with: brew install jq (macOS) or apt-get install jq (Linux)"
        return 1
    fi

    # Get Lite's devDependencies
    local lite_deps
    lite_deps=$(jq -r '.["devDependencies"] | keys[]' "$lite_package" 2>/dev/null || true)

    if [ -z "$lite_deps" ]; then
        echo "No devDependencies found in Lite package.json"
        return 0
    fi

    echo "Found devDependencies in Lite:"
    echo "$lite_deps"

    # For each dependency in Lite, check if it exists in addon
    local added_count=0
    local existing_count=0
    while IFS= read -r dep; do
        if [ -z "$dep" ]; then
            continue
        fi

        # Get version from Lite
        local version
        version=$(jq -r ".[\"devDependencies\"][\"$dep\"]" "$lite_package" 2>/dev/null || echo "")

        if [ -z "$version" ]; then
            continue
        fi

        # Check if dependency exists in addon
        local addon_version
        addon_version=$(jq -r ".[\"devDependencies\"][\"$dep\"]" "$addon_package" 2>/dev/null || echo "")

        if [ -z "$addon_version" ] || [ "$addon_version" = "null" ] || [ "$addon_version" != "$version" ]; then
            if [ -z "$addon_version" ] || [ "$addon_version" = "null" ]; then
                echo -e "${YELLOW}Adding missing devDependency: $dep ($version)${NC}"
            else
                echo -e "${YELLOW}Updating devDependency version: $dep ($addon_version -> $version)${NC}"
            fi
            # Add dependency to addon package.json
            if jq ".[\"devDependencies\"][\"$dep\"] = \"$version\"" "$addon_package" > "${addon_package}.tmp" && mv "${addon_package}.tmp" "$addon_package"; then
                echo -e "${GREEN}✓ Successfully added: $dep${NC}"
                ((added_count++))
            else
                echo -e "${RED}✗ Failed to add: $dep${NC}"
            fi
        else
            echo -e "${GREEN}devDependency already exists: $dep ($addon_version)${NC}"
            ((existing_count++))
        fi
    done <<< "$lite_deps"

    echo ""
    echo "devDependencies added: $added_count"
    echo "devDependencies already existed: $existing_count"

    echo -e "${GREEN}✓ npm devDependencies merged${NC}"
}

# Function to copy config file
copy_config_file() {
    local source="$1"
    local dest="$2"
    local description="$3"

    if file_exists "$source"; then
        if file_exists "$dest"; then
            echo -e "${YELLOW}$description already exists at $dest${NC}"
            echo "Skipping to avoid overwriting"
        else
            echo -e "${GREEN}Copying $description to $dest${NC}"
            cp "$source" "$dest"
            # Adjust config paths for add-on structure
            adjust_config_paths "$dest"
        fi
    else
        echo -e "${RED}Source file not found: $source${NC}"
    fi
}

# Function to adjust config file paths to match add-on structure
adjust_config_paths() {
    local config_file="$1"
    local filename
    filename=$(basename "$config_file")

    case "$filename" in
        rector.php)
            adjust_rector_paths "$config_file"
            ;;
        mago.toml)
            adjust_mago_paths "$config_file"
            ;;
        phpstan.neon)
            adjust_phpstan_paths "$config_file"
            ;;
    esac
}

# Function to adjust Rector paths
adjust_rector_paths() {
    local config_file="$1"
    local addon_dir
    addon_dir=$(dirname "$config_file")

    # Check which directories exist in the add-on
    local existing_dirs=()
    for dir in classes stripe square paypal css tests; do
        if [ -d "$addon_dir/$dir" ]; then
            existing_dirs+=("$dir")
        fi
    done

    if [ ${#existing_dirs[@]} -eq 0 ]; then
        echo -e "${YELLOW}No expected directories found for Rector, keeping all paths${NC}"
        return
    fi

    echo "Adjusting Rector paths to include only existing directories: ${existing_dirs[*]}"

    # Build the new paths section
    local new_paths="	->withPaths(
		array("
    for dir in "${existing_dirs[@]}"; do
        new_paths="$new_paths
			__DIR__ . '/$dir',"
    done
    new_paths="$new_paths
		)
	)"

    # Use Python for reliable multi-line replacement
    if command -v python3 >/dev/null 2>&1; then
        python3 -c "
import re

with open('$config_file', 'r') as f:
    content = f.read()

# Find and replace the withPaths section by tracking parentheses
pattern = r'->withPaths\('
match = re.search(pattern, content)
if match:
    start = match.start()
    depth = 1
    i = start + len(pattern)
    while i < len(content) and depth > 0:
        if content[i] == '(':
            depth += 1
        elif content[i] == ')':
            depth -= 1
        i += 1
    if depth == 0:
        old_section = content[start:i]
        replacement = '''$new_paths'''
        content = content[:start] + replacement + content[i:]

with open('$config_file', 'w') as f:
    f.write(content)
"
        echo -e "${GREEN}✓ Rector paths adjusted${NC}"
    else
        echo -e "${YELLOW}Python3 not available, skipping Rector path adjustment${NC}"
    fi
}

# Function to adjust Mago paths
adjust_mago_paths() {
    local config_file="$1"
    local addon_dir
    addon_dir=$(dirname "$config_file")

    # Check which directories exist in the add-on
    local existing_dirs=()
    for dir in formidable.php classes stripe square paypal css tests; do
        if [ -d "$addon_dir/$dir" ] || [ -f "$addon_dir/$dir" ]; then
            existing_dirs+=("$dir")
        fi
    done

    if [ ${#existing_dirs[@]} -eq 0 ]; then
        echo -e "${YELLOW}No expected directories found for Mago, keeping all paths${NC}"
        return
    fi

    echo "Adjusting Mago paths to include only existing directories: ${existing_dirs[*]}"

    # Build the new paths section
    local new_paths="paths = ["
    for dir in "${existing_dirs[@]}"; do
        new_paths="$new_paths
    \"$dir\","
    done
    new_paths="$new_paths
]"

    # Use Python for reliable multi-line replacement
    if command -v python3 >/dev/null 2>&1; then
        python3 -c "
import re
with open('$config_file', 'r') as f:
    content = f.read()

# Find and replace the paths section
pattern = r'paths = \[[^\]]*\]'
replacement = '''$new_paths'''
content = re.sub(pattern, replacement, content, flags=re.DOTALL)

with open('$config_file', 'w') as f:
    f.write(content)
"
        echo -e "${GREEN}✓ Mago paths adjusted${NC}"
    else
        echo -e "${YELLOW}Python3 not available, skipping Mago path adjustment${NC}"
    fi
}

# Function to adjust PHPStan paths
adjust_phpstan_paths() {
    local config_file="$1"
    local addon_dir
    addon_dir=$(dirname "$config_file")

    # Check which directories exist in the add-on
    local existing_dirs=()
    for dir in classes stripe square paypal css tests bin images languages js vendor fonts; do
        if [ -d "$addon_dir/$dir" ]; then
            existing_dirs+=("$dir")
        fi
    done

    if [ ${#existing_dirs[@]} -eq 0 ]; then
        echo -e "${YELLOW}No expected directories found for PHPStan, keeping all paths${NC}"
        return
    fi

    echo "Adjusting PHPStan paths to include only existing directories: ${existing_dirs[*]}"

    # Build the new excludePaths section
    local new_exclude="excludePaths:
	["
    for dir in "${existing_dirs[@]}"; do
        new_exclude="$new_exclude
		- */$dir/*"
    done
    new_exclude="$new_exclude
		- */vendor/*
		- */phpcs-sniffs/*
	]"

    # Use Python for reliable multi-line replacement
    if command -v python3 >/dev/null 2>&1; then
        python3 -c "
import re
with open('$config_file', 'r') as f:
    content = f.read()

# Find and replace the excludePaths section
pattern = r'excludePaths:\s*\[[^\]]*\]'
replacement = '''$new_exclude'''
content = re.sub(pattern, replacement, content, flags=re.DOTALL)

with open('$config_file', 'w') as f:
    f.write(content)
"
        echo -e "${GREEN}✓ PHPStan paths adjusted${NC}"
    else
        echo -e "${YELLOW}Python3 not available, skipping PHPStan path adjustment${NC}"
    fi
}

# Function to copy directory
copy_directory() {
    local source="$1"
    local dest="$2"
    local description="$3"
    
    if [ -d "$source" ]; then
        if [ -d "$dest" ]; then
            echo -e "${YELLOW}$description already exists at $dest${NC}"
            echo "Skipping to avoid overwriting"
        else
            echo -e "${GREEN}Copying $description to $dest${NC}"
            cp -r "$source" "$dest"
        fi
    else
        echo -e "${RED}Source directory not found: $source${NC}"
    fi
}

# ============================================================================
# Main Script
# ============================================================================

print_header "Syncing Standards from Lite to Add-on"

echo "Lite path: $LITE_PATH"
echo "Add-on path: $ADDON_PATH"

# Validate paths
if [ ! -d "$LITE_PATH" ]; then
    echo -e "${RED}Error: Lite path does not exist: $LITE_PATH${NC}"
    exit 1
fi

if [ ! -d "$ADDON_PATH" ]; then
    echo -e "${RED}Error: Add-on path does not exist: $ADDON_PATH${NC}"
    exit 1
fi

# ============================================================================
# Sync Composer Dependencies
# ============================================================================

LITE_COMPOSER="$LITE_PATH/composer.json"
ADDON_COMPOSER="$ADDON_PATH/composer.json"

if file_exists "$LITE_COMPOSER" && file_exists "$ADDON_COMPOSER"; then
    merge_composer_deps "$LITE_COMPOSER" "$ADDON_COMPOSER"
else
    echo -e "${YELLOW}Skipping composer sync (composer.json not found in both locations)${NC}"
fi

# Merge npm devDependencies
LITE_PACKAGE="$LITE_PATH/package.json"
ADDON_PACKAGE="$ADDON_PATH/package.json"

if file_exists "$LITE_PACKAGE" && file_exists "$ADDON_PACKAGE"; then
    merge_npm_deps "$LITE_PACKAGE" "$ADDON_PACKAGE"
else
    echo -e "${YELLOW}Skipping npm sync (package.json not found in both locations)${NC}"
fi

# ============================================================================
# Sync PHP Config Files
# ============================================================================

print_header "Syncing PHP Config Files"

# PHP CS Fixer
copy_config_file "$LITE_PATH/.php-cs-fixer.php" "$ADDON_PATH/.php-cs-fixer.php" "PHP CS Fixer config"

# PHPStan
copy_config_file "$LITE_PATH/phpstan.neon" "$ADDON_PATH/phpstan.neon" "PHPStan config"

# Psalm
copy_config_file "$LITE_PATH/psalm.xml" "$ADDON_PATH/psalm.xml" "Psalm config"

# Rector
copy_config_file "$LITE_PATH/rector.php" "$ADDON_PATH/rector.php" "Rector config"

# Mago
copy_config_file "$LITE_PATH/mago.toml" "$ADDON_PATH/mago.toml" "Mago config"

# PHPCS
copy_config_file "$LITE_PATH/phpcs.xml" "$ADDON_PATH/phpcs.xml" "PHPCS config"

# Stubs - create blank file if it doesn't exist
# Check for both stubs.php and stubs (without .php extension)
if [ ! -f "$ADDON_PATH/stubs.php" ] && [ ! -f "$ADDON_PATH/stubs" ]; then
    echo -e "${GREEN}Creating blank stubs.php file${NC}"
    echo "<?php" > "$ADDON_PATH/stubs.php"
    echo "// Add custom stubs here for static analysis" >> "$ADDON_PATH/stubs.php"
fi

# Typos
copy_config_file "$LITE_PATH/_typos.toml" "$ADDON_PATH/_typos.toml" "Typos config"

# ============================================================================
# Sync JS Config Files
# ============================================================================

print_header "Syncing JS Config Files"

# Oxlint
copy_config_file "$LITE_PATH/.oxlintrc.json" "$ADDON_PATH/.oxlintrc.json" "Oxlint config"

# Stylelint
copy_config_file "$LITE_PATH/.stylelintrc.json" "$ADDON_PATH/.stylelintrc.json" "Stylelint config"

# ESLint config (could be .eslintrc.json or eslint.config.mjs)
if file_exists "$LITE_PATH/.eslintrc.json"; then
    copy_config_file "$LITE_PATH/.eslintrc.json" "$ADDON_PATH/.eslintrc.json" "ESLint config (JSON)"
elif file_exists "$LITE_PATH/eslint.config.mjs"; then
    copy_config_file "$LITE_PATH/eslint.config.mjs" "$ADDON_PATH/eslint.config.mjs" "ESLint config (MJS)"
fi

# ============================================================================
# Sync GitHub Workflows
# ============================================================================

print_header "Syncing GitHub Workflows"

LITE_WORKFLOWS="$LITE_PATH/.github/workflows"
ADDON_WORKFLOWS="$ADDON_PATH/.github/workflows"

echo "Lite workflows path: $LITE_WORKFLOWS"
echo "Addon workflows path: $ADDON_WORKFLOWS"

if [ -d "$LITE_WORKFLOWS" ]; then
    echo "Lite workflows directory found"
    echo "Available workflows in Lite:"
    ls -1 "$LITE_WORKFLOWS"/*.yml 2>/dev/null | xargs -n1 basename 2>/dev/null || echo "No .yml files found"

    # Create addon workflows directory if it doesn't exist
    if [ ! -d "$ADDON_WORKFLOWS" ]; then
        mkdir -p "$ADDON_WORKFLOWS"
        echo -e "${GREEN}Created .github/workflows directory${NC}"
    else
        echo "Existing workflows in addon:"
        ls -1 "$ADDON_WORKFLOWS"/*.yml 2>/dev/null | xargs -n1 basename 2>/dev/null || echo "No .yml files found"
    fi

    # Copy each workflow file
    copied_count=0
    skipped_count=0
    excluded_count=0

    # Workflows to exclude from copying
    exclude_workflows=("push-deploy.yml" "push-asset-readme-update.yml" "cypress.yml")

    for workflow in "$LITE_WORKFLOWS"/*.yml; do
        if [ -f "$workflow" ]; then
            filename=$(basename "$workflow")
            dest="$ADDON_WORKFLOWS/$filename"

            # Check if workflow is in exclusion list
            is_excluded=false
            for excluded in "${exclude_workflows[@]}"; do
                if [ "$filename" = "$excluded" ]; then
                    is_excluded=true
                    break
                fi
            done

            if [ "$is_excluded" = true ]; then
                echo -e "${YELLOW}Workflow excluded: $filename${NC}"
                ((excluded_count++))
                continue
            fi

            if [ -f "$dest" ]; then
                echo -e "${YELLOW}Workflow already exists: $filename${NC}"
                echo "Skipping to avoid overwriting"
                ((skipped_count++))
            else
                echo -e "${GREEN}Copying workflow: $filename${NC}"
                if cp "$workflow" "$dest"; then
                    echo -e "${GREEN}✓ Successfully copied: $filename${NC}"
                    ((copied_count++))
                else
                    echo -e "${RED}✗ Failed to copy: $filename${NC}"
                fi
            fi
        fi
    done
    echo ""
    echo "Workflows copied: $copied_count"
    echo "Workflows skipped (already exist): $skipped_count"
    echo "Workflows excluded: $excluded_count"
else
    echo -e "${RED}Lite workflows directory not found: $LITE_WORKFLOWS${NC}"
fi

# ============================================================================
# Install Dependencies
# ============================================================================

print_header "Installing Dependencies"

# Install composer dependencies
cd "$ADDON_PATH"
if file_exists "composer.json"; then
    echo "Installing composer dependencies..."
    # Delete lock file to ensure fresh install with updated composer.json
    if [ -f "composer.lock" ]; then
        echo "Removing composer.lock for fresh install..."
        rm composer.lock
    fi
    composer update --dev --prefer-dist --no-progress
    echo -e "${GREEN}✓ Composer dependencies installed${NC}"
else
    echo -e "${YELLOW}No composer.json found, skipping composer install${NC}"
fi

# Install npm dependencies if package.json exists
if file_exists "package.json"; then
    # Try to use nvm if available
    if [ -f "$HOME/.nvm/nvm.sh" ]; then
        echo "Loading nvm and using Node 22..."
        export NVM_DIR="$HOME/.nvm"
        [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
        nvm use 22 2>/dev/null || nvm install 22
    fi

    if command -v node >/dev/null 2>&1; then
        echo "Installing npm dependencies..."
        npm ci --include=dev --legacy-peer-deps
        echo -e "${GREEN}✓ NPM dependencies installed${NC}"
    else
        echo -e "${YELLOW}Node not found, skipping npm install${NC}"
    fi
else
    echo -e "${YELLOW}No package.json found, skipping npm install${NC}"
fi

# ============================================================================
# Summary
# ============================================================================

print_header "Sync Complete"

echo "The following has been synced from Lite to the add-on:"
echo "  - Composer require-dev dependencies"
echo "  - npm devDependencies"
echo "  - PHP CS Fixer config (.php-cs-fixer.php)"
echo "  - PHPStan config (phpstan.neon)"
echo "  - Psalm config (psalm.xml)"
echo "  - Rector config (rector.php)"
echo "  - Mago config (mago.toml)"
echo "  - PHPCS config (phpcs.xml)"
echo "  - Stubs file (stubs.php) - created blank if missing"
echo "  - Typos config (_typos.toml)"
echo "  - Oxlint config (.oxlintrc.json)"
echo "  - Stylelint config (.stylelintrc.json)"
echo "  - ESLint config"
echo "  - GitHub workflows (.github/workflows/*.yml)"
echo ""
echo "Note: The following are NOT copied as they should reference Lite:"
echo "  - eslint-rules and phpcs-sniffs (via git clone in workflows)"
echo ""
echo "Dependencies have been installed."
echo ""
echo -e "${GREEN}You can now run the fix-workflow.sh script in the add-on to fix any issues.${NC}"
