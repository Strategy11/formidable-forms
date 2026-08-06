#!/usr/bin/env python3
"""
api_review.py — Strategy11 Labs Plugin Self-Review
====================================================
Sends the plugin's PHP source to a fresh Claude instance for an independent
security and quality review. Designed to run AFTER static analysis tools
so it can focus on logic issues and architectural problems tools miss.

Usage:
    python3 api_review.py <path-to-plugin-directory> [--is-formidable]

Flags:
    --is-formidable   Include Formidable-specific checks in the review prompt.

Output:
    Prints a structured review report to stdout with severity ratings.
    Exits 0 if no high-severity issues found, 1 otherwise.
"""

import sys
import os
import json
import urllib.request
import urllib.error

# ── Configuration ────────────────────────────────────────────────────────────

MODEL          = "claude-sonnet-4-20250514"
MAX_TOKENS     = 2000
# Soft cap on total PHP source sent (characters). Keeps well inside context window.
MAX_SOURCE_LEN = 80_000

# ── Helpers ──────────────────────────────────────────────────────────────────

def collect_php_files(directory):
    """Return list of (relative_path, content) for all .php files, largest first."""
    files = []
    for root, dirs, filenames in os.walk(directory):
        # Skip vendor/node_modules
        dirs[:] = [d for d in dirs if d not in ('vendor', 'node_modules', '.git')]
        for name in filenames:
            if name.endswith('.php'):
                abs_path = os.path.join(root, name)
                rel_path = os.path.relpath(abs_path, directory)
                try:
                    with open(abs_path, 'r', encoding='utf-8', errors='replace') as f:
                        content = f.read()
                    files.append((rel_path, content))
                except OSError:
                    pass
    # Prioritise main plugin file and includes/ over assets
    def sort_key(item):
        path = item[0]
        if path.count(os.sep) == 0:
            return 0   # root-level files first
        if 'includes' in path or 'classes' in path:
            return 1
        return 2
    files.sort(key=sort_key)
    return files


def build_source_bundle(files):
    """Concatenate files up to MAX_SOURCE_LEN, annotating each with its path."""
    parts = []
    total = 0
    truncated = False
    for rel_path, content in files:
        chunk = f"\n\n// ── FILE: {rel_path} ──\n{content}"
        if total + len(chunk) > MAX_SOURCE_LEN:
            truncated = True
            break
        parts.append(chunk)
        total += len(chunk)
    return "".join(parts), truncated


def build_prompt(source_bundle, truncated, is_formidable):
    formidable_section = ""
    if is_formidable:
        formidable_section = """
**Formidable Forms–specific checks (flag any violation):**
- `FrmEntry::update()` must never be used for partial entry saves — it wipes all
  fields not included in `item_meta`. Only `FrmEntryMeta::update_entry_meta()` is safe.
- Raw queries against `frm_item_metas` must use `item_id`, never `entry_id` (that
  column does not exist).
- After programmatic entry saves, `do_action('frm_after_update_entry', $entry_id, $form_id)`
  must be fired to flush Formidable's cache; skipping it causes stale data in the
  Entries list.
- Action `post_content` settings must be decoded with `json_decode()`, not
  `maybe_unserialize()`.
- `FrmEntry::getOne()` requires `true` as the second argument to hydrate `->metas`;
  omitting it returns an entry with no field values.
- Request input must be read via `FrmAppHelper::get_param` / `get_post_param` /
  `simple_get`, never raw `$_GET`/`$_POST`; superglobals must never be assigned to.
- HTML attributes should be built with `FrmAppHelper::array_to_html_params()`,
  which already escapes — flag any `esc_attr()` applied to its inputs or output
  (double-escaping produces `&quot;`).
- Pro detection should call `FrmAppHelper::pro_is_installed()` inline, not be
  threaded through functions as a `$pro_is_installed` parameter.
- Logic shared with Pro should be overridden in the Pro subclass, not added to Lite.
"""

    truncation_note = ""
    if truncated:
        truncation_note = "\n**Note:** The source was truncated due to size. Review what is present.\n"

    return f"""You are performing an independent security and code quality review of a
WordPress plugin built for Strategy11 Labs. You have no prior context about this
plugin — review only what is in the source below.

{truncation_note}

**Your task:** Identify issues across these four categories. For each issue give:
- Severity: HIGH / MEDIUM / LOW
- File and approximate line (if identifiable)
- What the problem is
- How to fix it

**Categories to check:**

1. **Security**
   - Unescaped output (`echo $var` without `esc_html`, `esc_attr`, `esc_url`, `wp_kses`)
   - Unsanitized input (direct use of `$_POST`/`$_GET` without `wp_unslash` + sanitize)
   - Missing nonce verification on AJAX handlers or form processors
   - Missing capability checks (`current_user_can`) before privileged operations
   - Raw SQL without `$wpdb->prepare()`
   - Missing `wp_die()` after `wp_send_json_success/error()`
   - IDOR risks (using user-supplied IDs without ownership validation)

2. **Code quality**
   - Debug output left in code (`var_dump`, `print_r`, `error_log`, `die`)
   - PHP closing tag `?>` at end of files
   - Unprefixed functions, classes, constants, or hooks
   - Non-internationalized user-visible strings

3. **Strategy11 Labs header compliance**
   - Author must be `Strategy11 Labs`
   - Plugin URI must be `https://labs.formidableforms.com`
   - Version must be prefixed with `Alpha`
   - Description must start with "Proof of concept Formidable Labs creation"
   - Description must end with the bug report link to formidableforms.com/new-topic/
{formidable_section}
4. **Logic and architectural issues**
   - Functions that query the database inside a loop
   - The same data fetched more than once without caching
   - Hook priorities that could cause conflicts with WordPress or Formidable core

5. **Lead-reviewer conventions** (style rules enforced on every PR)
   - A ternary or `if`/`else` that leads with a negative (`!`/"double false");
     it should be flipped so the positive case comes first
   - Verbose boolean returns that could be `return <expression>;`
   - `// phpcs:ignore WordPress.Security.EscapeOutput...` added instead of
     actually escaping (especially in Lite) — flag every occurrence
   - `apply_filters()` results returned without a type cast (e.g. `(bool)`)
   - Inaccurate `@param`/`@var`/`@return` types (defaulting to `mixed` when the
     real type is narrower); JS docblocks using `@returns` or `{Void}`
   - jQuery used where a vanilla DOM API would do; the same DOM node queried twice
   - Misleading names (e.g. calling IDs "keys"), or needless single-use variables

**Response format:**

Return ONLY a JSON object. No markdown fences, no preamble. Structure:
{{
  "summary": "One sentence overall assessment.",
  "has_high_severity": true|false,
  "issues": [
    {{
      "severity": "HIGH|MEDIUM|LOW",
      "category": "Security|Code quality|Header compliance|Logic|Convention",
      "file": "relative/path.php or unknown",
      "description": "Clear description of the problem.",
      "fix": "Concrete fix."
    }}
  ]
}}

If no issues are found in a category, omit those entries. If the code is clean,
return an empty issues array.

**Plugin source:**
{source_bundle}
"""


def call_api(prompt):
    payload = json.dumps({
        "model":      MODEL,
        "max_tokens": MAX_TOKENS,
        "messages":   [{"role": "user", "content": prompt}],
    }).encode("utf-8")

    req = urllib.request.Request(
        "https://api.anthropic.com/v1/messages",
        data=payload,
        headers={"Content-Type": "application/json"},
        method="POST",
    )

    with urllib.request.urlopen(req) as resp:
        return json.loads(resp.read())


def parse_response(api_response):
    """Extract the text block from the API response."""
    for block in api_response.get("content", []):
        if block.get("type") == "text":
            return block["text"]
    return ""


def print_report(review):
    issues   = review.get("issues", [])
    summary  = review.get("summary", "No summary returned.")
    high     = [i for i in issues if i["severity"] == "HIGH"]
    medium   = [i for i in issues if i["severity"] == "MEDIUM"]
    low      = [i for i in issues if i["severity"] == "LOW"]

    print("\n" + "=" * 60)
    print("  STRATEGY11 LABS — API PLUGIN REVIEW")
    print("=" * 60)
    print(f"\nSummary: {summary}\n")

    if not issues:
        print("✅  No issues found.\n")
        return

    for severity, group, icon in [
        ("HIGH",   high,   "🔴"),
        ("MEDIUM", medium, "🟡"),
        ("LOW",    low,    "🔵"),
    ]:
        if not group:
            continue
        print(f"{icon}  {severity} ({len(group)} issue{'s' if len(group) != 1 else ''})")
        print("-" * 40)
        for issue in group:
            file_ref = issue.get("file", "unknown")
            print(f"  [{issue['category']}] {file_ref}")
            print(f"  Problem: {issue['description']}")
            print(f"  Fix:     {issue['fix']}")
            print()


# ── Main ─────────────────────────────────────────────────────────────────────

def main():
    args = sys.argv[1:]
    if not args:
        print("Usage: python3 api_review.py <plugin-directory> [--is-formidable]")
        sys.exit(1)

    plugin_dir   = args[0]
    is_formidable = "--is-formidable" in args

    if not os.path.isdir(plugin_dir):
        print(f"Error: '{plugin_dir}' is not a directory.")
        sys.exit(1)

    print(f"Collecting PHP files from {plugin_dir}...")
    files = collect_php_files(plugin_dir)
    if not files:
        print("No PHP files found.")
        sys.exit(0)

    print(f"Found {len(files)} PHP file(s). Building review bundle...")
    source_bundle, truncated = build_source_bundle(files)
    if truncated:
        print(f"⚠️  Source truncated at {MAX_SOURCE_LEN:,} chars — largest files reviewed first.")

    print("Sending to Claude for independent review...")
    prompt = build_prompt(source_bundle, truncated, is_formidable)

    try:
        raw_response = call_api(prompt)
    except urllib.error.URLError as e:
        print(f"API call failed: {e}")
        sys.exit(1)

    response_text = parse_response(raw_response)

    # Strip any accidental markdown fences
    clean = response_text.strip()
    if clean.startswith("```"):
        clean = "\n".join(clean.split("\n")[1:])
    if clean.endswith("```"):
        clean = "\n".join(clean.split("\n")[:-1])
    clean = clean.strip()

    try:
        review = json.loads(clean)
    except json.JSONDecodeError:
        print("Could not parse API response as JSON. Raw output:")
        print(response_text)
        sys.exit(1)

    print_report(review)

    # Exit 1 if any HIGH severity issues found
    if review.get("has_high_severity", False):
        sys.exit(1)


if __name__ == "__main__":
    main()
