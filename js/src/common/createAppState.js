/**
 * Factory function to create a new application state management object.
 * Allows for custom initialization of the application state specific to each feature or package.
 *
 * @param {Function} initializer A function that provides custom initialization logic for the application state.
 * @throws {Error} Throws an error if the `initializer` is not a function.
 * @return {{initializeAppState: Function, getAppState: Function, setAppState: Function, getAppStateProperty: Function, setAppStateProperty: Function}}
 *         An object containing functions to manage and access the application state.
 */
export const createAppState = ( initializer ) => {
	let appState = null;

	/**
	 * Initializes the application state using the initializer provided.
	 * The initializer function is expected to return the initial state object.
	 *
	 * @throws {Error} Throws an error if `initializer` is not a function.
	 * @return {void}
	 */
	const initializeAppState = () => {
		if ( typeof initializer === 'function' ) {
			appState = initializer();
		} else {
			throw new Error( 'initializeAppState: Initializer must be a function' );
		}
	};

	/**
	 * Returns the current application state.
	 *
	 * @return {Object} The current state of the application.
	 */
	const getAppState = () => appState;

	/**
	 * Updates the application state with new values.
	 *
	 * @param {Object} newState The new values to update the state with.
	 */
	const setAppState = newState => {
		appState = { ...appState, ...newState };
	};

	/**
	 * Returns a specific property from the current application state.
	 *
	 * @param {string} propertyName The name of the property to retrieve.
	 * @return {*} The value of the specified property, or null if it doesn't exist.
	 */
	const getAppStateProperty = propertyName => Reflect.get( appState, propertyName ) ?? null;

	/**
	 * Updates a specific property in the application state with a new value.
	 *
	 * @param {string} propertyName The name of the property to update.
	 * @param {*}      value        The new value to set for the property.
	 * @return {void}
	 */
	const setAppStateProperty = ( propertyName, value ) => {
		if ( Reflect.has( appState, propertyName ) ) {
			Reflect.set( appState, propertyName, value );
		}
	};

	// Return an object containing all state management functions and the custom initializer
	return { initializeAppState, getAppState, setAppState, getAppStateProperty, setAppStateProperty };
};
