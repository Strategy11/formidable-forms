/**
 * Creates a page elements manager.
 *
 * @param {Object} [initialElements={}] An object containing initial DOM elements.
 * @throws {Error} Throws an error if the `initialElements` is not an object.
 * @return {Object} An object with methods to get and add elements.
 */
export function createPageElements( initialElements = {} ) {
	if ( typeof initialElements !== 'object' || initialElements === null ) {
		throw new Error(
			'createPageElements: initialElements must be a non-null object'
		);
	}

	let elements = initialElements;

	/**
	 * Retrieve the initialized essential DOM elements.
	 *
	 * @return {Object} The initialized elements object.
	 */
	function getElements() {
		return elements;
	}

	/**
	 * Add new elements to the elements object.
	 *
	 * @param {Object} newElements An object containing new elements to be added.
	 * @throws {Error} Throws an error if the `newElements` is not a non-null object.
	 * @return {void} Updates the elements object by merging the new elements into it.
	 */
	function addElements( newElements ) {
		if ( typeof newElements !== 'object' || newElements === null ) {
			throw new Error(
				'addElements: newElements must be a non-null object'
			);
		}

		elements = { ...elements, ...newElements };
	}

	return { getElements, addElements };
}
