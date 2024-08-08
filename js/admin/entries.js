/**
 * Entries Page Script.
 *
 * Handles UI interactions on the Entries admin page.
 */

wp.domReady( () => {

	/**
	 * Internal dependencies
	 */
	const { applyZebraStriping } = window.frmAdminBuild;
	const { onClickPreventDefault } = frmDom.util;

	/**
	 * Applies zebra striping to the entry view page table.
	 */
	applyZebraStriping( '.frm-alt-table', 'frm-empty-row' );

	/**
	 * Manages the behavior of the 'Show Empty Fields' button.
	 *
	 * Handles the initialization and event binding for the button. It toggles
	 * the button's state between showing and hiding empty fields in the table and adjusts
	 * the zebra striping accordingly.
	 */
	manageShowEmptyFieldsButton();

	function manageShowEmptyFieldsButton() {
		const showEmptyFieldsButton = document.getElementById( 'frm-entry-show-empty-fields' );

		// Early return if the button is not found in the DOM.
		if ( ! showEmptyFieldsButton ) {
			return;
		}

		if ( ! showEmptyFieldsButton.dataset.show ) {
			showEmptyFieldsButton.dataset.show = 'false';
		}

		onClickPreventDefault( showEmptyFieldsButton, () => {
			// Toggle button state and update table striping
			const newShowState = showEmptyFieldsButton.dataset.show === 'true' ? 'false' : 'true';
			showEmptyFieldsButton.dataset.show = newShowState;

			setTimeout( () => {
				applyZebraStriping( '.frm-alt-table', newShowState === 'true' ? '' : 'frm-empty-row' );
			}, newShowState === 'true' ? 0 : 200 );
		});
	}
});
