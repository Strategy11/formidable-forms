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

	// This is used for the "Show empty fields" toggle.
	jQuery( 'a[data-frmtoggle]' ).on( 'click', toggleDiv );

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

	/**
	 * Toggle a target element open or closed.
	 *
	 * @return {false}
	 */
	function toggleDiv() {
		/*jshint validthis:true */
		const div = jQuery( this ).data( 'frmtoggle' );
		if ( jQuery( div ).is( ':visible' ) ) {
			jQuery( div ).slideUp( 'fast' );
		} else {
			jQuery( div ).slideDown( 'fast' );
		}
		return false;
	}

});
