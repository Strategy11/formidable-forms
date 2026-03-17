'use strict';

( function() {
	const { documentOn } = frmDom.util;

	function handleClickFormsListSettings( event ) {
		event.preventDefault();
		const btn = 'A' === event.target.tagName ? event.target : event.target.closest( 'a' );
		if ( ! btn ) {
			return;
		}

		// If dropdown is already moved here, toggle it.
		if ( btn.nextElementSibling && 'frm-forms-list-settings' === btn.nextElementSibling.id && ! btn.nextElementSibling.classList.contains( 'frm_hidden' ) ) {
			btn.nextElementSibling.classList.add( 'frm_hidden' );
			return;
		}

		// Move the dropdown to after the button (HTML structure) and use CSS for positioning.
		const dropdownWrapper = document.getElementById( 'frm-forms-list-settings' );
		if ( ! dropdownWrapper ) {
			return;
		}

		// Get the button's position relative to the viewport.
		const btnRect = btn.getBoundingClientRect();
		const viewportHeight = window.innerHeight;
		const spaceAbove = btnRect.top;
		const spaceBelow = viewportHeight - btnRect.bottom;

		// Always insert after the button, but use CSS classes for positioning.
		btn.after( dropdownWrapper );

		// Remove existing position classes.
		dropdownWrapper.classList.remove( 'frm-dropdown-above', 'frm-dropdown-below' );

		// Add position class based on available space.
		if ( spaceAbove > spaceBelow ) {
			// Position above the button (more space above).
			dropdownWrapper.classList.add( 'frm-dropdown-above' );
		} else {
			// Position below the button (more space below).
			dropdownWrapper.classList.add( 'frm-dropdown-below' );
		}

		// Hide dropdown when clicking outside.
		const handleOutsideClick = event => {
			const dropdown = document.getElementById( 'frm-forms-list-settings' );
			if ( dropdown && ! dropdown.contains( event.target ) && ! btn.contains( event.target ) ) {
				dropdown.classList.add( 'frm_hidden' );
				document.removeEventListener( 'click', handleOutsideClick );
			}
		};

		// Add outside click listener.
		document.addEventListener( 'click', handleOutsideClick );

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

	function handleClickApplyBtn() {
		// Update the screen options form inputs.
		const screenOptionsForm = document.querySelector( 'form#adv-settings' );
		if ( ! screenOptionsForm ) {
			// This page may not support screen options.
			applySettingsWithoutScreenOptions();
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

	function applySettingsWithoutScreenOptions() {
		const perPageInput = frmDom.tag( 'input' );
		perPageInput.type = 'hidden';
		perPageInput.name = 'wp_screen_options[value]';
		perPageInput.value = document.getElementById( 'frm-forms-list-per-page' ).value;

		const perPageOptionNameInput = frmDom.tag( 'input' );
		perPageOptionNameInput.type = 'hidden';
		perPageOptionNameInput.name = 'wp_screen_options[option]';
		perPageOptionNameInput.value = 'formidable_page_formidable_per_page';

		const showDescInput = frmDom.tag( 'input' );
		showDescInput.type = 'checkbox';
		showDescInput.name = 'frm_forms_show_desc';
		showDescInput.value = '1';
		showDescInput.checked = document.getElementById( 'frm-forms-list-show-desc' ).checked;

		const nonceInput = frmDom.tag( 'input' );
		nonceInput.type = 'hidden';
		nonceInput.name = 'screenoptionnonce';
		nonceInput.value = document.getElementById( 'screenoptionnonce' ).value;

		const form = frmDom.tag( 'form', {
			className: 'frm_hidden',
			children: [ perPageInput, perPageOptionNameInput, showDescInput, nonceInput ],
		});
		form.method = 'post';
		document.body.append( form );
		form.submit();
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
