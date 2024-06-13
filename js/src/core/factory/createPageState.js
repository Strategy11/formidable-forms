/**
 * Creates a page state manager.
 *
 * @param {Object} [initialState={}] An object containing the initial state.
 * @throws {Error} Throws an error if the `initialState` is not a plain object.
 * @return {Object} An object with methods to initialize, get, and set the page state.
 */
export function createPageState( initialState = {} ) {
	let state = null;

	/**
	 * Initializes the page state with the provided initial state.
	 *
	 * @throws {Error} Throws an error if `initialState` is not a plain object.
	 * @return {void}
	 */
	const initializePageState = () => {
		if ( typeof initialState !== 'object' || initialState === null ) {
			throw new Error(
				'initializePageState: initialState must be a non-null object'
			);
		}

		state = initialState;
	};

	/**
	 * Returns the current page state.
	 *
	 * @return {Object|null} The current state of the page or null if not initialized.
	 */
	const getState = () => state;

	/**
	 * Returns a specific property from the current page state.
	 *
	 * @param {string} propertyName The name of the property to retrieve.
	 * @return {*} The value of the specified property, or null if it doesn't exist.
	 */
	const getSingleState = ( propertyName ) =>
		Reflect.get( state, propertyName ) ?? null;

	/**
	 * Updates the page state with new values.
	 *
	 * @param {Object} newState The new values to update the state with.
	 * @throws {Error} Throws an error if `newState` is not a plain object.
	 * @return {void}
	 */
	const setState = ( newState ) => {
		if ( typeof newState !== 'object' || newState === null ) {
			throw new Error( 'setState: newState must be a non-null object' );
		}

		state = { ...state, ...newState };
	};

	/**
	 * Updates a specific property in the page state with a new value.
	 *
	 * @param {string} propertyName The name of the property to update.
	 * @param {*}      value        The new value to set for the property.
	 * @return {void}
	 */
	const setSingleState = ( propertyName, value ) => {
		if ( Reflect.has( state, propertyName ) ) {
			Reflect.set( state, propertyName, value );
		}
	};

	return {
		initializePageState,
		getState,
		getSingleState,
		setState,
		setSingleState,
	};
}
