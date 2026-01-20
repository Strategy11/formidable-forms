# Formidable Forms Development Guidelines

This is an enterprise WordPress plugin ecosystem. All changes must follow strict quality standards.

## Project Structure

- **formidable-master** - Lite plugin (free version)
- **formidable-pro-master** - Pro plugin (premium features)
- **formidable-views** - Views add-on
- **formidable-\*-master** - Other add-ons (ai, dates, geo, signature, surveys, etc.)

## Architecture Overview

```
classes/
├── controllers/    # Handle requests, routing, admin pages
├── factories/      # Object creation (FrmFieldFactory)
├── helpers/        # Utility functions (FrmAppHelper, FrmFieldsHelper)
├── models/         # Data models (FrmForm, FrmField, FrmEntry)
│   └── fields/     # Field type classes (FrmFieldType, FrmProFieldVirtual)
├── views/          # PHP templates for rendering
└── widgets/        # WordPress widgets
```

## Key Classes

| Class             | Purpose                                  |
| ----------------- | ---------------------------------------- |
| `FrmAppHelper`    | Core utility methods, sanitization, URLs |
| `FrmFieldFactory` | Creates field type instances             |
| `FrmFieldType`    | Base class for all field types           |
| `FrmDb`           | Database operations wrapper              |
| `FrmForm`         | Form CRUD operations                     |
| `FrmField`        | Field CRUD operations                    |
| `FrmEntry`        | Entry CRUD operations                    |

## Development Rules

1. **Search before coding** - Always search codebase and WordPress docs first.
2. **Minimal changes** - Make smallest change that solves the problem.
3. **Pro compatibility** - Code must work with and without Pro active.
4. **Test coverage** - Add tests for new functionality.
5. **PHPDoc** - Document all public methods with proper tags.

## Testing

```bash
# Run all Lite tests
cd formidable-master && vendor/bin/phpunit

# Run all Pro tests
cd formidable-pro-master && vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/phpunit/fields/test_FrmFieldType.php

# Run specific test method
vendor/bin/phpunit --filter test_method_name
```

## Code Review Checklist

- [ ] Follows WordPress Coding Standards
- [ ] Follows WordPress VIP standards
- [ ] All user input sanitized
- [ ] All output escaped
- [ ] Database queries use prepare()
- [ ] AJAX handlers verify nonce and capabilities
- [ ] Works when Pro is inactive
- [ ] No PHP warnings or notices
- [ ] PHPDoc comments complete
- [ ] Tests added/updated
