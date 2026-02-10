---
trigger: glob
globs: ["**/*.js", "**/*.jsx", "**/*.mjs"]
description: WordPress JavaScript coding standards with modern ES6+ best practices. Auto-applies when working with JS files.
---

# WordPress JavaScript Coding Standards

Based on WordPress Core Official Standards and modern best practices from Google, Airbnb, and major tech companies.

**References:**

- [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- [Google JavaScript Style Guide](https://google.github.io/styleguide/jsguide.html)
- [Airbnb JavaScript Style Guide](https://github.com/airbnb/javascript)

---

## Critical Rules for New Code

### No jQuery

New code must NOT use jQuery. Use native DOM APIs and modern JavaScript.

```javascript
// INCORRECT - jQuery
$(".my-element").addClass("active");
$(".my-element").on("click", handler);
$.ajax({ url: "/api" });

// CORRECT - Native
document.querySelector(".my-element").classList.add("active");
document.querySelector(".my-element").addEventListener("click", handler);
fetch("/api");
```

### Functional Over Class-Based

Prefer functional and modular implementations over classes.

```javascript
// INCORRECT - Class-based
class TemplateManager {
  constructor() {
    this.templates = [];
  }

  addTemplate(template) {
    this.templates.push(template);
  }
}

// CORRECT - Functional/Modular
const createTemplateManager = () => {
  const templates = [];

  const addTemplate = (template) => {
    templates.push(template);
  };

  const getTemplates = () => [...templates];

  return { addTemplate, getTemplates };
};
```

### ES6+ Modern Syntax

All new code must use ES6+ features. Target stable ECMAScript features only.

---

## 1. Variables and Declarations

### Use const and let

Never use `var`. Use `const` by default. Use `let` only when reassignment is needed.

```javascript
// CORRECT
const MAX_COUNT = 100;
const config = { timeout: 30 };
let currentIndex = 0;

// INCORRECT
var count = 0;
```

### One Variable Per Declaration

Each variable gets its own declaration statement.

```javascript
// CORRECT
const a = 1;
const b = 2;
let c = 3;

// INCORRECT
const a = 1,
  b = 2;
let c = 3,
  d = 4;
```

### Declare Close to First Use

Declare variables close to where they are first used, not at the top of functions.

```javascript
// CORRECT
function processItems(items) {
  if (!items.length) {
    return [];
  }

  const processed = [];
  for (const item of items) {
    const result = transform(item);
    processed.push(result);
  }
  return processed;
}
```

---

## 2. Naming Conventions

| Type           | Convention             | Example                        |
| -------------- | ---------------------- | ------------------------------ |
| Variables      | camelCase              | `currentUser`, `itemCount`     |
| Functions      | camelCase              | `getUserData`, `handleClick`   |
| Constants      | SCREAMING_SNAKE_CASE   | `MAX_ITEMS`, `API_URL`         |
| Classes        | PascalCase             | `UserManager`, `FormValidator` |
| Private        | Leading underscore     | `_privateMethod`               |
| Boolean        | Prefix with is/has/can | `isActive`, `hasPermission`    |
| Event handlers | Prefix with on/handle  | `onClick`, `handleSubmit`      |

### Descriptive Names

Use clear, descriptive names. Avoid abbreviations except well-known ones.

```javascript
// CORRECT
const userAuthenticationToken = getToken();
const isFormValid = validateForm(formData);
const handleFormSubmit = (event) => {};

// INCORRECT
const uat = getToken();
const valid = validateForm(formData);
const submit = (e) => {};
```

---

## 3. Functions

### Prefer Arrow Functions

Use arrow functions for callbacks and short functions.

```javascript
// CORRECT
const numbers = [1, 2, 3];
const doubled = numbers.map((n) => n * 2);

elements.forEach((element) => {
  element.classList.add("active");
});

// Named function for complex logic
function processComplexData(data) {
  // Multiple statements
}
```

### Pure Functions

Prefer pure functions without side effects.

```javascript
// CORRECT - Pure function
const calculateTotal = (items) => {
  return items.reduce((sum, item) => sum + item.price, 0);
};

// INCORRECT - Impure function with side effects
let total = 0;
const calculateTotal = (items) => {
  items.forEach((item) => {
    total += item.price; // Mutates external state
  });
};
```

### Default Parameters

Use default parameters instead of conditional logic.

```javascript
// CORRECT
function createUser(name, role = "subscriber") {
  return { name, role };
}

// INCORRECT
function createUser(name, role) {
  role = role || "subscriber";
  return { name, role };
}
```

### Rest Parameters

Use rest parameters instead of `arguments` object.

```javascript
// CORRECT
function sum(...numbers) {
  return numbers.reduce((total, n) => total + n, 0);
}

// INCORRECT
function sum() {
  return Array.prototype.slice.call(arguments).reduce((t, n) => t + n, 0);
}
```

### Early Returns

Use early returns to avoid deep nesting.

```javascript
// CORRECT
function processUser(user) {
  if (!user) {
    return null;
  }

  if (!user.isActive) {
    return { error: "User inactive" };
  }

  return { data: user.profile };
}

// INCORRECT
function processUser(user) {
  if (user) {
    if (user.isActive) {
      return { data: user.profile };
    } else {
      return { error: "User inactive" };
    }
  } else {
    return null;
  }
}
```

---

## 4. Objects and Arrays

### Object Shorthand

Use shorthand property and method syntax.

```javascript
// CORRECT
const name = "John";
const age = 30;
const user = {
  name,
  age,
  greet() {
    return `Hello, ${this.name}`;
  },
};

// INCORRECT
const user = {
  name: name,
  age: age,
  greet: function () {
    return "Hello, " + this.name;
  },
};
```

### Destructuring

Use destructuring for objects and arrays.

```javascript
// CORRECT
const { name, email } = user;
const [first, second] = items;
const { data: userData } = response;

function processUser({ name, email, role = "user" }) {
  // Use destructured values
}

// INCORRECT
const name = user.name;
const email = user.email;
const first = items[0];
```

### Spread Operator

Use spread for copying and merging.

```javascript
// CORRECT
const newArray = [...oldArray, newItem];
const newObject = { ...oldObject, newProperty: value };
const merged = { ...defaults, ...options };

// INCORRECT
const newArray = oldArray.concat([newItem]);
const newObject = Object.assign({}, oldObject, { newProperty: value });
```

### Computed Property Names

Use computed property names when needed.

```javascript
// CORRECT
const key = "dynamicKey";
const obj = {
  [key]: value,
  [`prefix_${key}`]: otherValue,
};
```

---

## 5. Modules

### ES Modules Only

Use ES modules (import/export) for all new code.

```javascript
// CORRECT
import { getState, setState } from "./shared";
import defaultExport from "./module";
export const myFunction = () => {};
export default mainFunction;

// INCORRECT
const module = require("./module");
module.exports = myFunction;
```

### Named Exports Preferred

Prefer named exports over default exports for better refactoring.

```javascript
// CORRECT
export const processData = (data) => {};
export const validateData = (data) => {};

// Use
import { processData, validateData } from "./utils";
```

### Single Import Per Module

Import from a module only once per file.

```javascript
// CORRECT
import { foo, bar, baz } from "./utils";

// INCORRECT
import { foo } from "./utils";
import { bar } from "./utils";
```

### No Wildcard Imports

Avoid wildcard imports in production code.

```javascript
// CORRECT
import { specificFunction } from "./utils";

// INCORRECT
import * as utils from "./utils";
```

---

## 6. DOM Manipulation

### Query Selectors

Use native query selectors.

```javascript
// Single element
const element = document.querySelector(".my-class");
const byId = document.getElementById("my-id");

// Multiple elements
const elements = document.querySelectorAll(".my-class");

// Scoped query
const child = parent.querySelector(".child");
```

### Element Creation

```javascript
// Create element
const div = document.createElement("div");
div.className = "my-class";
div.textContent = "Hello";
div.dataset.id = "123";

// Append
container.appendChild(div);

// Insert HTML (use with caution, escape user input)
container.insertAdjacentHTML("beforeend", '<div class="item"></div>');
```

### Event Handling

```javascript
// Add listener
element.addEventListener("click", handleClick);

// Remove listener
element.removeEventListener("click", handleClick);

// Event delegation
container.addEventListener("click", (event) => {
  if (event.target.matches(".button")) {
    handleButtonClick(event);
  }
});

// Custom events
element.dispatchEvent(new CustomEvent("custom-event", { detail: data }));
```

### Class Manipulation

```javascript
element.classList.add("active");
element.classList.remove("active");
element.classList.toggle("active");
element.classList.contains("active");
element.classList.replace("old", "new");
```

---

## 7. Async Operations

### Fetch API

Use Fetch API instead of XMLHttpRequest or jQuery.ajax.

```javascript
// GET request
const response = await fetch("/api/data");
const data = await response.json();

// POST request
const response = await fetch("/api/submit", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
  },
  body: JSON.stringify(payload),
});
```

### Async/Await

Prefer async/await over Promise chains.

```javascript
// CORRECT
async function loadData() {
  try {
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error("Network error");
    }
    const data = await response.json();
    return processData(data);
  } catch (error) {
    handleError(error);
    return null;
  }
}

// INCORRECT - Promise chain for simple operations
function loadData() {
  return fetch(url)
    .then((response) => response.json())
    .then((data) => processData(data))
    .catch((error) => handleError(error));
}
```

### Parallel Async Operations

Use Promise.all for independent parallel operations.

```javascript
// CORRECT - Parallel execution
const [users, posts, comments] = await Promise.all([
  fetchUsers(),
  fetchPosts(),
  fetchComments(),
]);

// INCORRECT - Sequential when parallel is possible
const users = await fetchUsers();
const posts = await fetchPosts();
const comments = await fetchComments();
```

---

## 8. Error Handling

### Try-Catch for Async

Always wrap async operations in try-catch.

```javascript
async function fetchData() {
  try {
    const response = await fetch(url);
    return await response.json();
  } catch (error) {
    console.error("Fetch failed:", error.message);
    return null;
  }
}
```

### Meaningful Error Messages

Provide context in error messages.

```javascript
// CORRECT
throw new Error(`Failed to load template: ${templateId}`);

// INCORRECT
throw new Error("Error");
```

---

## 9. State Management

### Encapsulated State

Use closures or modules for state management.

```javascript
// CORRECT - Module pattern from codebase
import {
  getState,
  getSingleState,
  setState,
  setSingleState,
} from "core/page-skeleton";

// Initialize state
setState({
  currentView: "list",
  selectedItem: null,
  isLoading: false,
});

// Update state
setSingleState("isLoading", true);

// Read state
const { currentView, selectedItem } = getState();
```

### Immutable Updates

Never mutate state directly.

```javascript
// CORRECT
const newState = {
  ...state,
  items: [...state.items, newItem],
};

// INCORRECT
state.items.push(newItem);
```

---

## 10. Anti-Patterns to Avoid

### Global Variable Pollution

```javascript
// INCORRECT
myGlobalVar = "value";
window.myApp = {};

// CORRECT - Use modules
export const myValue = "value";
```

### Callback Hell

```javascript
// INCORRECT
getData((data) => {
  processData(data, (result) => {
    saveData(result, (response) => {
      // Deeply nested
    });
  });
});

// CORRECT
const data = await getData();
const result = await processData(data);
const response = await saveData(result);
```

### Modifying Built-in Prototypes

```javascript
// NEVER DO THIS
Array.prototype.customMethod = function () {};
String.prototype.myHelper = function () {};
```

### Using eval() or Function()

```javascript
// NEVER DO THIS
eval(userInput);
new Function(userInput)();
```

### Magic Numbers/Strings

```javascript
// INCORRECT
if (status === 3) {
}
element.style.width = "768px";

// CORRECT
const STATUS_COMPLETE = 3;
const TABLET_BREAKPOINT = "768px";

if (status === STATUS_COMPLETE) {
}
element.style.width = TABLET_BREAKPOINT;
```

### Excessive DOM Queries

```javascript
// INCORRECT
document.querySelector(".item").classList.add("active");
document.querySelector(".item").textContent = "Updated";
document.querySelector(".item").dataset.id = "123";

// CORRECT - Cache the reference
const item = document.querySelector(".item");
item.classList.add("active");
item.textContent = "Updated";
item.dataset.id = "123";
```

### DOM Manipulation in Loops

```javascript
// INCORRECT - Causes reflow on each iteration
items.forEach((item) => {
  container.appendChild(createItem(item));
});

// CORRECT - Use DocumentFragment
const fragment = document.createDocumentFragment();
items.forEach((item) => {
  fragment.appendChild(createItem(item));
});
container.appendChild(fragment);
```

---

## 11. Project Structure

Follow modular folder structure as in form-templates and onboarding-wizard.

```
module-name/
├── index.js              # Entry point, exports public API
├── initializeModule.js   # Initialization logic
├── elements/             # DOM element references
│   ├── elements.js
│   └── index.js
├── events/               # Event listeners
│   ├── clickListener.js
│   └── index.js
├── shared/               # Shared state and constants
│   ├── constants.js
│   ├── pageState.js
│   └── index.js
├── ui/                   # UI manipulation functions
│   ├── showModal.js
│   └── index.js
└── utils/                # Utility functions
    ├── validation.js
    └── index.js
```

---

## 12. WordPress Integration

### Using wp.hooks

```javascript
// Actions
wp.hooks.doAction("myPlugin.beforeInit", { getState, setState });
wp.hooks.addAction("myPlugin.afterSave", "myPlugin", handleAfterSave);

// Filters
const value = wp.hooks.applyFilters("myPlugin.filterValue", defaultValue);
wp.hooks.addFilter("myPlugin.filterValue", "myPlugin", modifyValue);
```

### Using @wordpress packages

```javascript
import domReady from "@wordpress/dom-ready";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";

domReady(() => {
  initializeModule();
});
```

---

## Formatting

### Spacing

- Use tabs for indentation
- Space inside parentheses for function calls with arguments
- Space after `!` negation operator
- Spaces around operators

```javascript
if (condition) {
  doSomething(arg1, arg2);
}

const isValid = !isEmpty && hasValue;
const total = a + b * c;
```

### Line Length

Keep lines under 100 characters. Break after operators.

```javascript
const result =
  veryLongVariableName + anotherLongVariableName + yetAnotherVariable;
```

### Trailing Commas

Use trailing commas in multiline structures.

```javascript
const obj = {
  property1: "value1",
  property2: "value2",
};

const arr = ["item1", "item2"];
```

---

## 13. React Best Practices

These patterns apply when writing React components for WordPress (blocks, admin pages, etc.).

### Functional Components Only

Never use class components. Use functional components with hooks.

```jsx
// CORRECT
function MyComponent({ title, onSave }) {
  const [isLoading, setIsLoading] = useState(false);

  return <div>{title}</div>;
}

// INCORRECT
class MyComponent extends Component {
  render() {
    return <div>{this.props.title}</div>;
  }
}
```

### Hooks Rules

1. Only call hooks at the top level
2. Only call hooks from React functions
3. Use `@wordpress/element` for React hooks in WordPress

```jsx
import { useState, useEffect, useCallback, useMemo } from "@wordpress/element";

function MyComponent({ items }) {
  // CORRECT - Hooks at top level
  const [selected, setSelected] = useState(null);

  // INCORRECT - Conditional hook
  if (items.length) {
    const [count, setCount] = useState(0); // Error!
  }
}
```

### Memoization

Use `useMemo` for expensive computations and `useCallback` for stable function references.

```jsx
import { useMemo, useCallback } from "@wordpress/element";

function ItemList({ items, onSelect }) {
  // Memoize expensive computation
  const processedItems = useMemo(() => {
    return items.map((item) => expensiveProcess(item));
  }, [items]);

  // Stable callback reference
  const handleSelect = useCallback(
    (id) => {
      onSelect(id);
    },
    [onSelect],
  );

  return (
    <ul>
      {processedItems.map((item) => (
        <li key={item.id} onClick={() => handleSelect(item.id)}>
          {item.name}
        </li>
      ))}
    </ul>
  );
}
```

### Avoid Unnecessary Re-renders

```jsx
// INCORRECT - Creates new object every render
<Child style={ { color: 'red' } } />
<Child items={ items.filter( ( i ) => i.active ) } />
<Child onClick={ () => handleClick() } />

// CORRECT - Stable references
const style = useMemo( () => ( { color: 'red' } ), [] );
const activeItems = useMemo( () => items.filter( ( i ) => i.active ), [ items ] );
const handleClick = useCallback( () => { /* ... */ }, [] );

<Child style={ style } />
<Child items={ activeItems } />
<Child onClick={ handleClick } />
```

### Derived State

Derive values during render instead of syncing with effects.

```jsx
// CORRECT - Derived during render
function FilteredList({ items, filter }) {
  const filteredItems = items.filter((item) => item.type === filter);

  return <List items={filteredItems} />;
}

// INCORRECT - Unnecessary state and effect
function FilteredList({ items, filter }) {
  const [filteredItems, setFilteredItems] = useState([]);

  useEffect(() => {
    setFilteredItems(items.filter((item) => item.type === filter));
  }, [items, filter]);

  return <List items={filteredItems} />;
}
```

### State Initialization

Use lazy initialization for expensive values.

```jsx
// CORRECT - Lazy initialization (function only called once)
const [state, setState] = useState(() => computeExpensiveValue(props));

// INCORRECT - Runs on every render
const [state, setState] = useState(computeExpensiveValue(props));
```

### Functional setState

Use functional updates when new state depends on previous state.

```jsx
// CORRECT - Functional update
setCount((prevCount) => prevCount + 1);
setItems((prevItems) => [...prevItems, newItem]);

// INCORRECT - May use stale state
setCount(count + 1);
```

### useRef for Transient Values

Use refs for values that change frequently but do not need re-renders.

```jsx
function Draggable() {
  const positionRef = useRef({ x: 0, y: 0 });

  const handleMouseMove = (event) => {
    // Update ref without re-render
    positionRef.current = { x: event.clientX, y: event.clientY };
  };

  return <div onMouseMove={handleMouseMove} />;
}
```

### Early Returns in Components

```jsx
function UserProfile({ user, isLoading }) {
  if (isLoading) {
    return <Spinner />;
  }

  if (!user) {
    return <EmptyState message="No user found" />;
  }

  return (
    <div>
      <h1>{user.name}</h1>
      {/* Rest of component */}
    </div>
  );
}
```

---

## 14. Performance Patterns

### Promise.all for Parallel Fetching

```jsx
// CORRECT - Parallel execution
const [users, posts] = await Promise.all([fetchUsers(), fetchPosts()]);

// INCORRECT - Sequential when parallel is possible
const users = await fetchUsers();
const posts = await fetchPosts();
```

### Cache Function Results

```jsx
// Module-level cache
const cache = new Map();

function expensiveOperation( key ) {
    if ( cache.has( key ) ) {
        return cache.get( key );
    }

    const result = /* expensive computation */;
    cache.set( key, result );
    return result;
}
```

### Use Set/Map for Lookups

```jsx
// CORRECT - O(1) lookup
const selectedIds = new Set(selectedItems.map((item) => item.id));
const isSelected = (id) => selectedIds.has(id);

// INCORRECT - O(n) lookup
const isSelected = (id) => selectedItems.some((item) => item.id === id);
```

### Batch DOM Operations

```jsx
// CORRECT - Single reflow
const fragment = document.createDocumentFragment();
items.forEach((item) => {
  const li = document.createElement("li");
  li.textContent = item.name;
  fragment.appendChild(li);
});
container.appendChild(fragment);

// INCORRECT - Multiple reflows
items.forEach((item) => {
  const li = document.createElement("li");
  li.textContent = item.name;
  container.appendChild(li); // Reflow on each iteration
});
```

### Content Visibility for Long Lists

```css
.long-list-item {
  content-visibility: auto;
  contain-intrinsic-size: 0 50px;
}
```

---

## 15. Composition Patterns

### Compound Components

Instead of boolean props, use composition.

```jsx
// INCORRECT - Boolean prop proliferation
<Card
    showHeader
    showFooter
    showImage
    headerTitle="Title"
    footerAction={ handleAction }
/>

// CORRECT - Compound components
<Card>
    <Card.Header>Title</Card.Header>
    <Card.Image src={ imageUrl } />
    <Card.Body>Content</Card.Body>
    <Card.Footer>
        <Button onClick={ handleAction }>Action</Button>
    </Card.Footer>
</Card>
```

### Children Over Render Props

```jsx
// CORRECT - Children for composition
<Modal>
    <Modal.Header>Title</Modal.Header>
    <Modal.Content>{ content }</Modal.Content>
</Modal>

// AVOID - Render props when children work
<Modal
    renderHeader={ () => <div>Title</div> }
    renderContent={ () => content }
/>
```

### Context for Shared State

```jsx
import { createContext, useContext, useState } from "@wordpress/element";

const FormContext = createContext();

function FormProvider({ children }) {
  const [values, setValues] = useState({});
  const [errors, setErrors] = useState({});

  const setValue = (name, value) => {
    setValues((prev) => ({ ...prev, [name]: value }));
  };

  return (
    <FormContext.Provider value={{ values, errors, setValue }}>
      {children}
    </FormContext.Provider>
  );
}

function useForm() {
  const context = useContext(FormContext);
  if (!context) {
    throw new Error("useForm must be used within FormProvider");
  }
  return context;
}
```

---

## Tooling

```bash
# Install ESLint with WordPress config
npm install --save-dev @wordpress/eslint-plugin

# .eslintrc.json
{
    "extends": [ "plugin:@wordpress/eslint-plugin/recommended" ],
    "rules": {
        "no-var": "error",
        "prefer-const": "error",
        "no-unused-vars": "error"
    }
}
```
