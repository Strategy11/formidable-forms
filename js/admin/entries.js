/**
 * entries.js File
 *
 * Contains scripts for the Entries admin page.
 */

/**
 * WordPress dependencies
 */
const { domReady } = wp;

/**
 * Internal dependencies
 */
const { applyZebraStriping } = window.frmAdminBuild;
const { onClickPreventDefault } = frmDom.util;

domReady( () => {

	/**
	 * Applies zebra striping to the entry view page table.
	 */
	applyZebraStriping( '.frm-alt-table', 'frm-empty-row' );

	/**
	 * Manages the 'Show Empty Fields' button.
	 *
	 * Clicking the button switches its state between showing and hiding empty table fields.
	 * It also toggles zebra striping on the table to reflect this change, making it easy
	 * for users to see or hide empty fields.
	 */
	const showEmptyFieldsButton = document.getElementById( 'frm-entry-show-empty-fields' );

	// Set initial 'data-show' attribute to 'false' if not set
	showEmptyFieldsButton.dataset.show = showEmptyFieldsButton.dataset.show || 'false';

	onClickPreventDefault( showEmptyFieldsButton, () => {
		// Change button state and update table striping
		const newShowState = showEmptyFieldsButton.dataset.show === 'true' ? 'false' : 'true';
		const isShowingEmptyFields = newShowState === 'true';

		showEmptyFieldsButton.dataset.show = newShowState;

		setTimeout(() => {
			applyZebraStriping( '.frm-alt-table', isShowingEmptyFields ? '' : 'frm-empty-row' );
		}, isShowingEmptyFields ? 0 : 200);
	});
});
