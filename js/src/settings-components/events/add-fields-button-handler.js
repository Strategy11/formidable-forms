/**
 * Redirects to "Add Fields" tab when the "Add Fields" button is clicked.
 *
 * When users view the Field Options tab with no fields, they see an "Add Fields" button.
 * Clicking this button should take them to the "Add Fields" tab for field selection.
 */

/**
 * Initializes the Add Fields button click handler.
 */
const initAddFieldsButtonHandler = () => {
	document.getElementById( 'frm-form-add-field' )?.addEventListener( 'click', event => {
		event.preventDefault();
		document.querySelector( '.frm-settings-panel .frm-tabs-navs ul > li:first-child' )?.click();
	} );
};

export default initAddFieldsButtonHandler;
