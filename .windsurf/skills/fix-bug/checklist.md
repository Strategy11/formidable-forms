# Bug Fix Verification Checklist

## Before Implementation

- [ ] Understood the complete issue
- [ ] Identified root cause (not just symptoms)
- [ ] Mapped all affected locations
- [ ] Checked Pro plugin requirement
- [ ] Found existing patterns for the fix
- [ ] Searched WordPress docs for best practices

## Solution Selection

- [ ] Proposed 2-3 solutions
- [ ] Documented trade-offs for each
- [ ] Selected minimal scope solution
- [ ] Verified fix is at most specific location
- [ ] Confirmed not overkill if affecting multiple areas

## Implementation

- [ ] Follows WordPress PHP coding standards
- [ ] Follows Formidable naming patterns
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
