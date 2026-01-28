# Namespaces & Imports

**Impact: MEDIUM**

Modern PHP namespace and import conventions for WordPress plugins and themes.

## Namespace Declarations

**Impact: MEDIUM (organization)**

Each part of a namespace name should consist of capitalized words separated by underscores.

**Incorrect:**

```php
namespace prefix\admin\domainUrl\subDomain;  // camelCase not allowed
namespace Foo {
    // Code - curly brace syntax not allowed
}
namespace {
    // Global namespace declaration not allowed
}
```

**Correct:**

```php
namespace Prefix\Admin\Domain_URL\Sub_Domain\Event;
```

**Rules:**
- One blank line before the declaration, at least one blank line after
- Only one namespace declaration per file, at the top
- No curly brace syntax
- No global namespace declarations
- Use unique, long prefixes like `Vendor\Project_Name` to prevent conflicts
- Do NOT use `wp` or `WordPress` as namespace prefixes

**Note:** Namespaces are encouraged for plugins/themes but not yet used in WordPress Core.

---

## Import Use Statements

**Impact: MEDIUM (code organization)**

Import `use` statements should be at the top of the file after the namespace declaration.

**Order of imports:**
1. Namespaces, classes, interfaces, traits, enums
2. Functions
3. Constants

**Incorrect:**

```php
namespace Project_Name\Feature;

use const Project_Name\Sub_Feature\CONSTANT_A;  // Constants before classes
use function Project_Name\Sub_Feature\function_a;  // Functions before classes
use \Project_Name\Sub_Feature\Class_C as aliased_class_c;  // Leading backslash, wrong alias naming

class Foo {
    // Code.
}

use Project_Name\Another_Class;  // Import after class definition - not allowed
```

**Correct:**

```php
namespace Project_Name\Feature;

use Project_Name\Sub_Feature\Class_A;
use Project_Name\Sub_Feature\Class_C as Aliased_Class_C;
use Project_Name\Sub_Feature\{
    Class_D,
    Class_E as Aliased_Class_E,
}

use function Project_Name\Sub_Feature\function_a;
use function Project_Name\Sub_Feature\function_b as aliased_function;

use const Project_Name\Sub_Feature\CONSTANT_A;
use const Project_Name\Sub_Feature\CONSTANT_D as ALIASED_CONSTANT;

// Rest of the code.
```

**Rules:**
- No leading backslash in imports
- Aliases must follow WordPress naming conventions (capitalized words with underscores for classes, lowercase with underscores for functions)
- Don't combine different import types in one statement
- All imports before any class/function definitions

**Note:** Import `use` statements are discouraged in WordPress Core for now.

---

## Trait Use Statements

**Impact: MEDIUM (OOP organization)**

Trait `use` statements should be at the top of a class with proper spacing.

**Incorrect:**

```php
class Foo {
    // No blank line before trait use statement
    use Bar_Trait;

    use Foo_Trait, Bazinga_Trait{Bar_Trait::method_name insteadof Foo_Trait;  // Wrong formatting
    Bazinga_Trait::method_name as bazinga_method;
    };

    public $baz = true;  // Missing blank line after trait import
}
```

**Correct:**

```php
class Foo {

    use Bar_Trait;

    use Foo_Trait, Bazinga_Trait {
        Bar_Trait::method_name insteadof Foo_Trait;
        Bazinga_Trait::method_name as bazinga_method;
    }

    use Loopy_Trait {
        eat as protected;
    }

    public $baz = true;

    // Rest of class...
}
```

**Rules:**
- One blank line before the first `use` statement
- At least one blank line after the last `use` statement (exception: if class only contains trait imports)
- Each aliasing/conflict resolution on its own line
- Proper indentation inside curly braces
