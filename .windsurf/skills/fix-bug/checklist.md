# Bug Fix Verification Checklist

## Before Implementation

- [ ] Understood the complete issue
- [ ] Identified root cause (not just symptoms)
- [ ] Analyzed complete context of changed class/file
- [ ] Traced parent hierarchy to plugin root
- [ ] Mapped all affected locations
- [ ] Mapped dependencies (what calls this code, what it calls)
- [ ] Studied ALL places using the affected pattern
- [ ] Checked Pro plugin requirement
- [ ] Found existing patterns for the fix
- [ ] Searched WordPress/VIP docs for best practices
- [ ] Searched platform-specific docs (performance, security)
- [ ] **Cross-plugin**: Researched related code in Lite and Pro (and addons if relevant)
- [ ] **Cross-plugin**: Understood how data flows between plugins for this feature

## Solution Selection

- [ ] Proposed 2-3 solutions
- [ ] Documented trade-offs for each
- [ ] Selected minimal scope solution
- [ ] Verified fix is at most specific location
- [ ] Preferred safety checks over refactoring
- [ ] Confirmed not overkill if affecting multiple areas
- [ ] Verified approach matches existing codebase patterns
- [ ] Fix is testable without touching other code

## Implementation

- [ ] Read all applicable `.windsurf/rules/` for the file types being modified
- [ ] Makes the smallest change that solves the problem
- [ ] Method signatures, return types, and data structures unchanged
- [ ] No unrelated code refactored in the same commit
- [ ] Defensive checks added where data comes in
- [ ] Follows `.windsurf/rules/wordpress/php.md` (PHP files)
- [ ] Follows `.windsurf/rules/wordpress/javascript.md` (JS files)
- [ ] Follows `.windsurf/rules/formidable/frm-php.md` (PHP files)
- [ ] Follows `.windsurf/rules/wordpress-vip/wpvip-security.md` (PHP/JS files)
- [ ] Uses `@since x.x` in docblocks (never guess version numbers)
- [ ] Uses existing helper methods
- [ ] All input sanitized
- [ ] All output escaped
- [ ] Database queries use $wpdb->prepare()
- [ ] No forbidden functions (extract, eval, etc.)

## Testing

- [ ] Bug is fixed in reported scenario
- [ ] Works when Pro is ACTIVE
- [ ] Works when Pro is INACTIVE
- [ ] No PHP warnings or notices
- [ ] Empty data handled correctly
- [ ] Edge cases tested
- [ ] Backward compatible

## Cleanup

- [ ] Removed ALL error_log() statements
- [ ] Removed ALL debug comments
- [ ] No debug files created
- [ ] Code passes phpcs check

## Documentation

- [ ] PHPDoc added for new methods
- [ ] Complex logic has comments
- [ ] Root cause documented
- [ ] Solution documented
