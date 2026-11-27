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
	maybeInitEntriesListPage();

	/**
	 * Opens the "Not spam" modal on a spam entry.
	 */
	manageNotSpamModal();

	/**
	 * Enforce the maximum number of entries list columns dynamically.
	 *
	 * @since 6.24
	 *
	 * @return {void}
	 */
	function maybeInitEntriesListPage() {
		if ( ! document.body.classList.contains( 'frm-admin-page-entries' ) ) {
			return;
		}

		const screenOptionsWrapper = document.getElementById( 'screen-options-wrap' );
		if ( ! screenOptionsWrapper ) {
			return;
		}

		const maxSelectionsNote = frmDom.div( {
			className: 'frm_warning_style',
			text: wp.i18n.__( 'Only 10 columns can be selected at a time.', 'formidable' ),
		} );
		maxSelectionsNote.style.margin = 0;

		const legend = screenOptionsWrapper.querySelector( 'legend' );
		legend.parentNode.insertBefore( maxSelectionsNote, legend.nextElementSibling );

		const checkboxes = Array.from( screenOptionsWrapper.querySelectorAll( 'input[type="checkbox"]' ) );
		const maximumColumns = 10;
		const getSelectedCount = () => {
			return checkboxes.reduce( ( count, checkbox ) => {
				return checkbox.checked ? count + 1 : count;
			}, 0 );
		};
		const disableCheckboxesIfAtMax = () => {
			if ( getSelectedCount() >= maximumColumns ) {
				maxSelectionsNote.classList.remove( 'frm_hidden' );
				checkboxes.forEach( checkbox => {
					if ( ! checkbox.checked ) {
						checkbox.parentNode.classList.add( 'frm_noallow' );
						checkbox.disabled = true;
					}
				} );
			} else {
				maxSelectionsNote.classList.add( 'frm_hidden' );
			}
		};
		const addCheckboxListeners = () => {
			checkboxes.forEach(
				checkbox => {
					checkbox.addEventListener(
						'change',
						event => {
							if ( event.target.checked ) {
								disableCheckboxesIfAtMax();
							} else {
								maxSelectionsNote.classList.add( 'frm_hidden' );

								// Enable all checkboxes when a checkbox is unchecked.
								checkboxes.forEach(
									checkbox => {
										checkbox.parentNode.classList.remove( 'frm_noallow' );
										checkbox.disabled = false;
									}
								);
							}
						}
					);
				}
			);
		};

		disableCheckboxesIfAtMax();
		addCheckboxListeners();
	}

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
