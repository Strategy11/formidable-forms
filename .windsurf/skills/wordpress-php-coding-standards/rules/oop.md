# Object-Oriented Programming

**Priority: MEDIUM**  
**Impact: OOP consistency and maintainability**

---

## One Structure Per File

Each class, interface, trait, or enum should be in its own file.

---

## Visibility

Always declare visibility (`public`, `protected`, `private`).

**Incorrect:**

```php
class Foo {
    var $bar;        // Old syntax
    function baz() {} // No visibility
}
```

**Correct:**

```php
class Foo {
    public $bar;

    public function baz() {}

    protected function qux() {}

    private function quux() {}
}
```

---

## Modifier Order

```php
final public static function foo() {}
abstract protected function bar();
```

---

## Object Instantiation

Always use parentheses, even without arguments.

**Incorrect:**

```php
$obj = new Foo;
```

**Correct:**

```php
$obj = new Foo();
$obj = new Foo( $arg );
```

---

## Magic Constants

Use uppercase for magic constants.

**Correct:**

```php
__DIR__
__FILE__
__CLASS__
__FUNCTION__
```

---

## Spread Operator

Space after `...` when used to spread. No space when collecting.

```php
// Spreading
function_call( ...$array );
$merged = array( ...$array1, ...$array2 );

// Collecting (variadic)
function variadic_function( ...$args ) {}
```
