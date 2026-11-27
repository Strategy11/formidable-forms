/**
 * Entries Page Script.
 *
 * Handles UI interactions on the Entries admin page.
 */

wp.domReady( () => {
	/**
	 * Internal dependencies
	 */
	const { applyZebraStriping, initModal } = window.frmAdminBuild;
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

	/**
	 * Opens the "Not spam" modal on a spam entry.
	 */
	manageNotSpamModal();

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
		} );
	}

	function manageNotSpamModal() {
		const modal = document.getElementById( 'frm-not-spam-modal' );

		if ( ! modal ) {
			return;
		}

		const $modal = initModal( '#frm-not-spam-modal', '440px' );

		if ( ! $modal ) {
			return;
		}

		document.querySelectorAll( '.frm-open-not-spam-modal' ).forEach( link => {
			onClickPreventDefault( link, () => $modal.dialog( 'open' ) );
		} );

		if ( modal.dataset.open ) {
			$modal.dialog( 'open' );
		}
	}
} );
