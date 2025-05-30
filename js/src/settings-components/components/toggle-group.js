/**
 * Group Toggle Component
 *
 * Handles toggling visibility and enabled state of related form elements
 */

/**
 * Internal dependencies
 */
import { HIDDEN_CLASS, DISABLED_CLASS, SINGLE_SETTINGS_CLASS } from 'core/constants';

/**
 * Class names for group toggle component
 *
 * @private
 */
const CLASS_NAMES = {
	GROUP_TOGGLE: 'frm-toggle-group',
	TOGGLE_BLOCK: 'frm_toggle_block',
};

/**
 * Data attributes for group toggle component
 *
 * @private
 */
const DATA_ATTRIBUTES = {
	GROUP_NAME: 'data-group-name',
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
	initializeToggleGroups();

	// Add event listeners for newly added fields
	document.addEventListener( 'frm_added_field', addEventListeners );
}

/**
 * Initialize toggle groups by adding event listeners and applying initial state
 *
 * @private
 * @return {void}
 */
function initializeToggleGroups() {
	applyInitialState();
	addEventListeners();
}

/**
 * Apply the initial state for all toggle buttons on the page
 *
 * @private
 * @return {void}
 */
function applyInitialState() {
	const toggleGroups = document.querySelectorAll( `.${ CLASS_NAMES.GROUP_TOGGLE }` );

	if ( ! toggleGroups.length ) {
		return;
	}

	toggleGroups.forEach( toggleGroup => {
		const toggleButton = toggleGroup.querySelector( `[${ DATA_ATTRIBUTES.GROUP_NAME }]:checked` );
		if ( ! toggleButton ) {
			return;
		}

		applyToggleState( toggleButton, toggleGroup );
	} );
}

/**
 * Add event listeners to toggle buttons in a group toggle component
 *
 * @private
 * @return {void}
 */
function addEventListeners() {
	// Toggle button click events
	document.querySelectorAll( `.${ CLASS_NAMES.GROUP_TOGGLE } [${ DATA_ATTRIBUTES.GROUP_NAME }]` )
		.forEach( toggleButton => toggleButton.addEventListener( 'click', handleToggleClick ) );
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

	applyToggleState( toggleButton, toggleGroup );
}

/**
 * Apply toggle state based on toggle button settings
 * Shared functionality used by both click handler and initial state
 *
 * @private
 * @param {HTMLElement} toggleButton The toggle button element
 * @param {HTMLElement} toggleGroup  The toggle group container element
 * @return {void}
 */
function applyToggleState( toggleButton, toggleGroup ) {
	const fieldId = toggleGroup.closest( `.${ SINGLE_SETTINGS_CLASS }` )?.dataset.fid
		|| toggleGroup.dataset.fid;

	const isChecked = toggleButton.checked;

	// Handle show/hide elements
	const showSelectors = toggleButton.getAttribute( DATA_ATTRIBUTES.SHOW );
	if ( showSelectors ) {
		document.querySelectorAll( normalizeSelector( showSelectors, fieldId ) )
			.forEach( element => element.classList.toggle( HIDDEN_CLASS, ! isChecked ) );
	}

	// Handle disable/enable elements
	const disableSelectors = toggleButton.getAttribute( DATA_ATTRIBUTES.DISABLE );
	if ( disableSelectors ) {
		document.querySelectorAll( normalizeSelector( disableSelectors, fieldId ) )
			.forEach( element => element.classList.toggle( DISABLED_CLASS, isChecked ) );
	}

	// Toggle disabled state for all other toggle blocks within the group
	const currentToggleBlock = toggleButton.closest( `.${ CLASS_NAMES.TOGGLE_BLOCK }` );
	Array.from( toggleGroup.querySelectorAll( `.${ CLASS_NAMES.TOGGLE_BLOCK }` ) )
		.filter( toggleBlock => toggleBlock !== currentToggleBlock )
		.forEach( toggleBlock => toggleBlock.classList.toggle( DISABLED_CLASS, isChecked ) );
}

/**
 * Normalize a selector by replacing {id} placeholders with the actual field ID
 *
 * @private
 * @param {string} selector The selector string with potential {id} placeholders
 * @param {string} fieldId  The field ID to replace placeholders with
 * @return {string} The normalized selector
 */
function normalizeSelector( selector, fieldId ) {
	return selector.replace( /{id}/g, fieldId );
}

export { initToggleGroupComponents };
