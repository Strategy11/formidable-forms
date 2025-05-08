/**
 * Redirects to "Add Fields" tab when the "Add Fields" button is clicked.
 *
 * When users view the Field Options tab with no fields, they see an "Add Fields" button.
 * Clicking this button should take them to the "Add Fields" tab for field selection.
 */

/**
 * Handles the click on Add Fields button to redirect to the Add Fields tab.
 */
const navigateToAddFields = () => {
	const tabsWrapper = document.querySelector( '.frm-style-tabs-wrapper' );
	if ( ! tabsWrapper ) {
		return;
	}

	const firstTab = tabsWrapper.querySelector( '.frm-tabs-navs ul > li:first-child' );
	if ( ! firstTab ) {
		return;
	}

	const tabLink = firstTab.querySelector( 'a' );
	if ( tabLink ) {
		tabLink.click();
	}
};

/**
 * Initializes the Add Fields button click handler.
 */
const initAddFieldsButtonHandler = () => {
	const addFieldsButton = document.getElementById( 'frm-form-add-field' );
	if ( addFieldsButton ) {
		addFieldsButton.addEventListener( 'click', navigateToAddFields );
	}
};

export default initAddFieldsButtonHandler;
