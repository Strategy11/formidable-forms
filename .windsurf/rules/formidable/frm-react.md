---
trigger: glob
globs: ["**/*.jsx", "**/blocks/**/*.js"]
description: Formidable Forms React patterns and best practices for WordPress blocks and admin pages. Auto-applies to JSX files and block editor code.
---

# Formidable Forms React Patterns

React-specific patterns for WordPress blocks, admin pages, and other React components in Formidable Forms.

---

## Functional Components Only

Never use class components. Use functional components with hooks.

```jsx
// CORRECT
function MyComponent( { title, onSave } ) {
	const [ isLoading, setIsLoading ] = useState( false );

	return <div>{ title }</div>;
}

// INCORRECT
class MyComponent extends Component {
	render() {
		return <div>{ this.props.title }</div>;
	}
}
```

---

## Hooks Rules

Here’s a cleaner, more concise version:

---

## Hook & WordPress Packages Rules

1. Call hooks only at the top level of React functions.
2. Use hooks only inside React function components or custom hooks.
3. Import React hooks from `@wordpress/element`.
4. Use `@wordpress/i18n` for internationalization.
5. Use `@wordpress/api-fetch` for REST API requests.
6. Use `@wordpress/components` for WordPress-styled UI components.
7. Use `@wordpress/data` for state and data management.
8. Use `@wordpress/blocks` for block utilities.
9. Use `@wordpress/editor` for Block Editor functionality.

```jsx
import { useState, useEffect, useCallback, useMemo } from '@wordpress/element';

function MyComponent( { items } ) {
	// CORRECT: Hooks at top level
	const [ selected, setSelected ] = useState( null );

	// INCORRECT: Conditional hook
	if ( items.length ) {
		const [ count, setCount ] = useState( 0 ); // Error!
	}
}
```

---

## Memoization

Use `useMemo` for expensive computations and `useCallback` for stable function references.

```jsx
import { useMemo, useCallback } from '@wordpress/element';

function ItemList( { items, onSelect } ) {
	// Memoize expensive computation
	const processedItems = useMemo( () => {
		return items.map( ( item ) => expensiveProcess( item ) );
	}, [ items ] );

	// Stable callback reference
	const handleSelect = useCallback(
		( id ) => {
			onSelect( id );
		},
		[ onSelect ],
	);

	return (
		<ul>
			{ processedItems.map( ( item ) => (
				<li key={ item.id } onClick={ () => handleSelect( item.id ) }>
					{ item.name }
				</li>
			) ) }
		</ul>
	);
}
```

---

## Avoid Unnecessary Re-renders

```jsx
// INCORRECT: Creates new object every render
<Child style={ { color: 'red' } } />
<Child items={ items.filter( ( i ) => i.active ) } />
<Child onClick={ () => handleClick() } />

// CORRECT: Stable references
const style = useMemo( () => ( { color: 'red' } ), [] );
const activeItems = useMemo( () => items.filter( ( i ) => i.active ), [ items ] );
const handleClick = useCallback( () => { /* ... */ }, [] );

<Child style={ style } />
<Child items={ activeItems } />
<Child onClick={ handleClick } />
```

---

## Derived State

Derive values during render instead of syncing with effects.

```jsx
// CORRECT: Derived during render
function FilteredList( { items, filter } ) {
	const filteredItems = items.filter( ( item ) => item.type === filter );

	return <List items={ filteredItems } />;
}

// INCORRECT: Unnecessary state and effect
function FilteredList( { items, filter } ) {
	const [ filteredItems, setFilteredItems ] = useState( [] );

	useEffect( () => {
		setFilteredItems( items.filter( ( item ) => item.type === filter ) );
	}, [ items, filter ] );

	return <List items={ filteredItems } />;
}
```

---

## State Initialization

Use lazy initialization for expensive values.

```jsx
// CORRECT: Lazy initialization (function only called once)
const [ state, setState ] = useState( () => computeExpensiveValue( props ) );

// INCORRECT: Runs on every render
const [ state, setState ] = useState( computeExpensiveValue( props ) );
```

---

## Functional setState

Use functional updates when new state depends on previous state.

```jsx
// CORRECT: Functional update
setCount( ( prevCount ) => prevCount + 1 );
setItems( ( prevItems ) => [ ...prevItems, newItem ] );

// INCORRECT: May use stale state
setCount( count + 1 );
```

---

## useRef for Transient Values

Use refs for values that change frequently but do not need re-renders.

```jsx
function Draggable() {
	const positionRef = useRef( { x: 0, y: 0 } );

	const handleMouseMove = ( event ) => {
		// Update ref without re-render
		positionRef.current = { x: event.clientX, y: event.clientY };
	};

	return <div onMouseMove={ handleMouseMove } />;
}
```

---

## Early Returns in Components

```jsx
function UserProfile( { user, isLoading } ) {
	if ( isLoading ) {
		return <Spinner />;
	}

	if ( ! user ) {
		return <EmptyState message="No user found" />;
	}

	return (
		<div>
			<h1>{ user.name }</h1>
			{ /* Rest of component */ }
		</div>
	);
}
```

---

## Composition Patterns

### Compound Components

Instead of boolean props, use composition.

```jsx
// INCORRECT: Boolean prop proliferation
<Card
	showHeader
	showFooter
	showImage
	headerTitle="Title"
	footerAction={ handleAction }
/>

// CORRECT: Compound components
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
// CORRECT: Children for composition
<Modal>
	<Modal.Header>Title</Modal.Header>
	<Modal.Content>{ content }</Modal.Content>
</Modal>

// AVOID: Render props when children work
<Modal
	renderHeader={ () => <div>Title</div> }
	renderContent={ () => content }
/>
```

### Context for Shared State

```jsx
import { createContext, useContext, useState } from '@wordpress/element';

const FormContext = createContext();

function FormProvider( { children } ) {
	const [ values, setValues ] = useState( {} );
	const [ errors, setErrors ] = useState( {} );

	const setValue = ( name, value ) => {
		setValues( ( prev ) => ( { ...prev, [ name ]: value } ) );
	};

	return (
		<FormContext.Provider value={ { values, errors, setValue } }>
			{ children }
		</FormContext.Provider>
	);
}

function useForm() {
	const context = useContext( FormContext );
	if ( ! context ) {
		throw new Error( 'useForm must be used within FormProvider' );
	}
	return context;
}
```

---

## WordPress Data Layer (wp.data)

The `@wordpress/data` package provides centralized state management for WordPress applications, inspired by Redux but tailored for the WordPress ecosystem.

### Creating Custom Data Stores

Use `createReduxStore` and `register` to define and register custom stores:

```jsx
import { createReduxStore, register } from '@wordpress/data';

const DEFAULT_STATE = {
	items: [],
	isLoading: false,
};

const actions = {
	addItem( item ) {
		return { type: 'ADD_ITEM', item };
	},
	setLoading( isLoading ) {
		return { type: 'SET_LOADING', isLoading };
	},
};

function reducer( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case 'ADD_ITEM':
			return { ...state, items: [ ...state.items, action.item ] };
		case 'SET_LOADING':
			return { ...state, isLoading: action.isLoading };
		default:
			return state;
	}
}

const selectors = {
	getItems( state ) {
		return state.items;
	},
	isLoading( state ) {
		return state.isLoading;
	},
};

const store = createReduxStore( 'formidable/my-feature', {
	reducer,
	actions,
	selectors,
} );

register( store );
```

### Using Selectors with useSelect

```jsx
import { useSelect } from '@wordpress/data';

function ItemList() {
	const { items, isLoading } = useSelect( ( select ) => ( {
		items: select( 'formidable/my-feature' ).getItems(),
		isLoading: select( 'formidable/my-feature' ).isLoading(),
	} ), [] );

	if ( isLoading ) {
		return <Spinner />;
	}

	return (
		<ul>
			{ items.map( ( item ) => (
				<li key={ item.id }>{ item.name }</li>
			) ) }
		</ul>
	);
}
```

### Dispatching Actions with useDispatch

```jsx
import { useDispatch } from '@wordpress/data';

function AddItemButton() {
	const { addItem } = useDispatch( 'formidable/my-feature' );

	const handleClick = () => {
		addItem( { id: Date.now(), name: 'New Item' } );
	};

	return <Button onClick={ handleClick }>Add Item</Button>;
}
```

### Resolvers for Async Data

Resolvers fetch data asynchronously when a selector is first called:

```jsx
const resolvers = {
	getItems() {
		return async ( { dispatch } ) => {
			dispatch( actions.setLoading( true ) );
			try {
				const items = await apiFetch( { path: '/formidable/v1/items' } );
				dispatch( { type: 'SET_ITEMS', items } );
			} finally {
				dispatch( actions.setLoading( false ) );
			}
		};
	},
};

const store = createReduxStore( 'formidable/my-feature', {
	reducer,
	actions,
	selectors,
	resolvers,
} );
```

### Thunks for Complex Actions

Use thunks for actions that need async logic or access to other actions:

```jsx
const actions = {
	fetchAndProcessItems() {
		return async ( { dispatch, select } ) => {
			dispatch( { type: 'FETCH_START' } );
			try {
				const response = await apiFetch( { path: '/api/items' } );
				const items = response.json();
				dispatch( { type: 'FETCH_SUCCESS', items } );
			} catch ( error ) {
				dispatch( { type: 'FETCH_ERROR', error: error.message } );
			}
		};
	},
};
```

### Batching Dispatches

Use `registry.batch()` to avoid unnecessary re-renders when dispatching multiple actions:

```jsx
import { useRegistry } from '@wordpress/data';

function BulkActions() {
	const registry = useRegistry();

	const handleBulkUpdate = () => {
		registry.batch( () => {
			registry.dispatch( 'formidable/my-feature' ).setLoading( true );
			registry.dispatch( 'formidable/my-feature' ).clearItems();
			registry.dispatch( 'formidable/my-feature' ).setLoading( false );
		} );
	};

	return <Button onClick={ handleBulkUpdate }>Bulk Update</Button>;
}
```

### Store Naming Convention

Use namespaced store names following the pattern `formidable/{feature-name}`:

- `formidable/form-builder`
- `formidable/entries`
- `formidable/templates`
