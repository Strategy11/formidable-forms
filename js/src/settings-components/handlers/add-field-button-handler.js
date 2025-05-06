/**
 * Redirects to "Add Fields" tab when the "Add Field" button is clicked.
 *
 * When users view the Field Options tab with no fields, they see an "Add Field" button.
 * Clicking this button should take them to the "Add Fields" tab for field selection.
 */

/**
 * Handles the click on Add Field button to redirect to the first tab.
 */
const navigateToFirstTab = () => {
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
 * Initializes the Add Field button click handler.
 */
const initAddFieldButtonHandler = () => {
	const addFieldButton = document.getElementById( 'frm-form-add-field' );
	if ( addFieldButton ) {
		addFieldButton.addEventListener( 'click', navigateToFirstTab );
	}
};

export default initAddFieldButtonHandler;
