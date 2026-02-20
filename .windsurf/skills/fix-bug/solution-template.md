# Solution Comparison Template

## Problem Summary

**Issue:** [Brief description of the bug]

**Expected:** [What should happen]

**Actual:** [What is happening]

**Root Cause:** [Why this is happening]

---

## Proposed Solutions

### Solution A: [Name]

**Location:** `path/to/file.php`

**Change Flow:**

```text
formidable/
↓
classes/controllers/FrmEntriesController.php
↓
process_entry()
↓
FrmEntryValidate::validate()
↓
validate_field()  ← fix here
```

**Change:**

```php
// Proposed code change
```

| Pros         | Cons         |
|--------------|--------------|
| [Benefit 1]  | [Drawback 1] |
| [Benefit 2]  | [Drawback 2] |

**Risk Level:** Low / Medium / High

---

### Solution B: [Name]

**Location:** `path/to/file.php`

**Change Flow:**

```text
formidable/
↓
classes/models/FrmEntry.php
↓
FrmEntry::create()
↓
set_meta_value()
↓
has_post_field
YES → sync_post_meta()  ← fix here
NO  → save_to_meta()
```

**Change:**

```php
// Proposed code change
```

| Pros         | Cons         |
|--------------|--------------|
| [Benefit 1]  | [Drawback 1] |
| [Benefit 2]  | [Drawback 2] |

**Risk Level:** Low / Medium / High

---

### Solution C: [Name] (if applicable)

**Location:** `path/to/file.php`

**Change Flow:**

```text
formidable/
↓
classes/helpers/FrmFieldsHelper.php
↓
FrmFieldsHelper::prepare_value()  ← fix here
```

**Change:**

```php
// Proposed code change
```

| Pros         | Cons         |
|--------------|--------------|
| [Benefit 1]  | [Drawback 1] |
| [Benefit 2]  | [Drawback 2] |

**Risk Level:** Low / Medium / High

---

## Recommendation

**Selected Solution:** [A/B/C]

**Rationale:**

- Minimal scope at most specific location
- Closest to root cause
- Follows existing patterns
- Prefers safety checks over refactoring
- No method signature, return type, or data structure changes
- Maintains backward compatibility
- Works with Pro active AND inactive
- Not overkill if affecting multiple areas
