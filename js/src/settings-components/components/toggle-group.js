/**
 * Group Toggle Component
 *
 * Handles toggling visibility and enabled state of related form elements
 */

/**
 * Class names for group toggle component
 *
 * @private
 */
const CLASS_NAMES = {
	GROUP_TOGGLE: 'frm-toggle-group',
	TOGGLE_BLOCK: 'frm_toggle_block',
	HIDDEN: 'frm_hidden',
	DISABLED: 'frm_disabled'
};

/**
 * Data attributes for group toggle component
 *
 * @private
 */
const DATA_ATTRIBUTES = {
	GROUP_NAME: 'data-group-name',
	FIELD_ID: 'data-field-id',
	SHOW: 'data-show',
	DISABLE: 'data-disable'
};

/**
 * Initialize all group toggle components on the page
 *
 * @return {void}
 */
function initToggleGroupComponents() {
	// Initialize for existing toggles
	addEventListeners();

	// Initialize for newly added fields
	document.addEventListener( 'frm_added_field', addEventListeners );
}

/**
 * Add event listeners to toggle buttons in a group toggle component
 *
 * @private
 * @return {void}
 */
function addEventListeners() {
	const toggleButtons = document.querySelectorAll( `.${ CLASS_NAMES.GROUP_TOGGLE } [${ DATA_ATTRIBUTES.GROUP_NAME }]` );

	if ( ! toggleButtons.length ) {
		return;
	}

	toggleButtons.forEach( toggleButton => toggleButton.addEventListener( 'click', handleToggleClick ) );
}

/**
 * Handle click events on toggle buttons
 *
 * @private
 * @param {Event} event The click event
 * @return {void}
 */
function handleToggleClick( event ) {
	const toggleButton = event.currentTarget;
	const toggleGroup = toggleButton.closest( `.${ CLASS_NAMES.GROUP_TOGGLE }` );

	if ( ! toggleGroup ) {
		return;
	}

	const fieldId = toggleGroup.getAttribute( DATA_ATTRIBUTES.FIELD_ID );
	const isChecked = toggleButton.checked;

	let showSelectors = toggleButton.getAttribute( DATA_ATTRIBUTES.SHOW );
	if ( showSelectors ) {
		showSelectors = showSelectors.replace( /{id}/g, fieldId );
		document.querySelectorAll( showSelectors )?.forEach( element => element.classList.toggle( CLASS_NAMES.HIDDEN, ! isChecked ) );
	}

	let disableSelectors = toggleButton.getAttribute( DATA_ATTRIBUTES.DISABLE );
	if ( disableSelectors ) {
		disableSelectors = disableSelectors.replace( /{id}/g, fieldId );
		document.querySelectorAll( disableSelectors )?.forEach( element => element.classList.toggle( CLASS_NAMES.DISABLED, isChecked ) );
	}

	// Toggle disabled state for all other toggle blocks within the group
	const currentToggleBlock = toggleButton.closest( `.${ CLASS_NAMES.TOGGLE_BLOCK }` );
	Array.from( toggleGroup.querySelectorAll( `.${ CLASS_NAMES.TOGGLE_BLOCK }` ) )
		.filter( toggleBlock => toggleBlock !== currentToggleBlock )
		.forEach( toggleBlock => toggleBlock.classList.toggle( CLASS_NAMES.DISABLED, isChecked ) );
}

export { initToggleGroupComponents };
