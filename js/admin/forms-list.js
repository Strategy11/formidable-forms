'use strict';

( function() {
	const { documentOn } = frmDom.util;

	function handleClickFormsListSettings( event ) {
		event.preventDefault();
		const btn = 'A' === event.target.tagName ? event.target : event.target.closest( 'a' );
		if ( ! btn ) {
			return;
		}

		// If the dropdown is already moved here, toggle it.
		if ( btn.nextElementSibling && 'frm-forms-list-settings' === btn.nextElementSibling.id ) {
			btn.nextElementSibling.classList.toggle( 'frm_hidden' );
			return;
		}

		// Move the dropdown to after the button.
		const dropdownWrapper = document.getElementById( 'frm-forms-list-settings' );
		if ( ! dropdownWrapper ) {
			return;
		}

		btn.after( dropdownWrapper );
		dropdownWrapper.classList.remove( 'frm_hidden' );
	}

	function handleChangeColumns( event ) {
		if ( ! event.target.dataset.wpColumnInputId ) {
			return;
		}

		const wpInput = document.getElementById( event.target.dataset.wpColumnInputId );
		if ( ! wpInput ) {
			return;
		}

		wpInput.checked = event.target.checked;
		wpInput.dispatchEvent( new Event( 'click' ) );
	}

	function handleClickApplyBtn( event ) {
		// Update the screen options form inputs.
		const screenOptionsForm = document.getElementById( 'adv-settings' );
		if ( ! screenOptionsForm ) {
			// This page may not support screen options.
			return;
		}

		document.querySelectorAll( '#frm-forms-list-settings [data-wp-screen-option-id]' ).forEach( input => {
			const screenOptionInput = document.getElementById( input.dataset.wpScreenOptionId );
			if ( ! screenOptionInput ) {
				return;
			}

			if ( 'INPUT' === input.tagName && 'checkbox' === input.type ) {
				screenOptionInput.checked = input.checked;
			} else {
				screenOptionInput.value = input.value;
			}
		} );

		screenOptionsForm.submit();
	}

	function handleClickCollapsibleBtn( event ) {
		event.preventDefault();
		const container = event.target.closest( '.frm-collapsible-box' );
		if ( ! container ) {
			return;
		}
		const content = container.querySelector( '.frm-collapsible-box__content' );
		if ( content ) {
			content.classList.toggle( 'frm-collapsible-box__content--collapsed' );
		}
		const svgUse = container.querySelector( '.frm-collapsible-box__btn use' );
		if ( ! svgUse ) {
			return;
		}

		if ( svgUse.href.baseVal.includes( 'down' ) ) {
			svgUse.href.baseVal = svgUse.href.baseVal.replace( 'down', 'up' );
		} else {
			svgUse.href.baseVal = svgUse.href.baseVal.replace( 'up', 'down' );
		}
	}

	// Click the gear icon.
	documentOn( 'click', '.frm-forms-list-settings-btn', handleClickFormsListSettings );

	documentOn( 'change', 'input[data-wp-column-input-id]', handleChangeColumns );
	documentOn( 'click', '#frm-save-forms-list-settings-btn', handleClickApplyBtn );

	documentOn( 'click', '.frm-collapsible-box__btn', handleClickCollapsibleBtn );
}() );
