( function() {
	'use strict';

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

	function handleClickFormsListSettingsApplyBtn( event ) {
		// Update the screen options form inputs.
		const screenOptionsForm = document.getElementById( 'adv-settings' );
		if ( ! screenOptionsForm ) {
			// This page may not support screen options.
			return;
		}

		document.querySelectorAll( '#frm-forms-list-settings [data-screen-option-id]' ).forEach( input => {
			const screenOptionInput = document.getElementById( input.dataset.screenOptionId );
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
		container.querySelector( '.frm-collapsible-box__content' ).classList.toggle( 'frm-collapsible-box__content--collapsed' );
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

	documentOn( 'click', '#frm-save-forms-list-settings-btn', handleClickFormsListSettingsApplyBtn );

	documentOn( 'click', '.frm-collapsible-box__btn', handleClickCollapsibleBtn );
}() );
