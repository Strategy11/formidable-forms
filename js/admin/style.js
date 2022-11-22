( function() {
	/* globals wp, frmDom */

	const { __ } = wp.i18n;
	const state  = {
		showingSampleForm: false
	};

	const { div, a, tag, svg } = frmDom;

	document.addEventListener( 'click', handleClickEvents );
	setTimeout( addHamburgMenusToCards, 0 ); // Add a timeout so Pro has a chance to add a filter first.

	function handleClickEvents( event ) {
		const target = event.target;

		if ( target.classList.contains( 'frm-style-card' ) || target.closest( '.frm-style-card' ) ) {
			handleStyleCardClick( event );
			return;
		}

		if ( 'frm_toggle_sample_form' === target.id ) {
			toggleSampleForm();
			return;
		}

		if ( 'frm_submit_side_top' === target.id ) {
			saveActiveStyle();
			return;
		}
	}

	/**
	 * When a style card is clicked, the preview is updated.
	 * If the Update button is clicked after selecting a style card, the active card will be saved as the target form's style.
	 *
	 * @param {Event} event
	 * @returns {void}
	 */
	function handleStyleCardClick( event ) {
		const target = event.target;

		if ( target.closest( '.dropdown' ) ) {
			// Ignore the hamburger menu inside of the card.
			return;
		}

		const card         = target.classList.contains( 'frm-style-card' ) ? target : target.closest( '.frm-style-card' );
		const sidebar      = document.getElementById( 'frm_style_sidebar' );
		const previewArea  = sidebar.nextElementSibling;
		const form         = previewArea.querySelector( 'form' );
		const activeCard   = document.querySelector( '.frm-active-style-card' );
		const sampleForm   = document.getElementById( 'frm_sample_form' ).querySelector( '.frm_forms' );
		const styleIdInput = document.getElementById( 'frm_style_form' ).querySelector( '[name="style_id"]' );

		activeCard.classList.remove( 'frm-active-style-card' );
		card.classList.add( 'frm-active-style-card' );
		form.parentNode.classList.remove( activeCard.dataset.classname );
		form.parentNode.classList.add( card.dataset.classname );
		sampleForm.classList.remove( activeCard.dataset.classname );
		sampleForm.classList.add( card.dataset.classname );
		styleIdInput.value = card.dataset.styleId;

		// We want to toggle the edit button so you can only leave the page to edit the style if it's active (to avoid unsaved changes).
		// TODO: If we want Edit in the hamburger (maybe we don't), we should prompt for unsaved changes before redirecting.
		const editButton     = document.getElementById( 'frm_edit_style' );
		const showEditButton = null !== card.querySelector( '.frm-selected-style-tag' );
		editButton.classList.toggle( 'frm_hidden', ! showEditButton );
	}

	function toggleSampleForm() {
		state.showingSampleForm = ! state.showingSampleForm;

		document.getElementById( 'frm_active_style_form' ).classList.toggle( 'frm_hidden', state.showingSampleForm );
		document.getElementById( 'frm_sample_form' ).classList.toggle( 'frm_hidden', ! state.showingSampleForm );
		document.getElementById( 'frm_toggle_sample_form' ).textContent = state.showingSampleForm ? __( 'Disable sample form', 'formidable' ) : __( 'View sample form', 'formidable' );
	}

	function saveActiveStyle() {
		document.getElementById( 'frm_style_form' ).submit();
	}

	function addHamburgMenusToCards() {
		const cards = Array.from( document.getElementsByClassName( 'frm-style-card' ) );
		cards.forEach(
			card => {
				const wrapper = card.querySelector( '.frm-style-card-preview' ).nextElementSibling;
				wrapper.style.position = 'relative';
				wrapper.appendChild(
					getHamburgerMenu({ editUrl: card.dataset.editUrl })
				);
			}
		);
	}

	function getHamburgerMenu( data ) {
		const hamburgerMenu = tag( 'a' );
		hamburgerMenu.className = 'frm-dropdown-toggle dropdown-toggle';
		hamburgerMenu.setAttribute( 'data-toggle', 'dropdown' );
		hamburgerMenu.setAttribute( 'data-container', 'body' );
		hamburgerMenu.setAttribute( 'role', 'button' );
		hamburgerMenu.setAttribute( 'tabindex', 0 );

		hamburgerMenu.appendChild( svg({ href: '#frm_thick_more_vert_icon' }) );

		const editOption = a({
			text: __( 'Edit', 'formidable' ),
			href: data.editUrl
		});

		const hookName            = 'frm_style_card_dropdown_options';
		const dropdownMenuOptions = wp.hooks.applyFilters( hookName, [ editOption ] );
		const dropdownMenu        = div({
			className: 'frm-dropdown-menu',
			children: dropdownMenuOptions.map( wrapDropdownItem )
		});

		dropdownMenu.setAttribute( 'role', 'menu' );

		return div({
			className: 'dropdown',
			children: [ hamburgerMenu, dropdownMenu ]
		});
	}

	function wrapDropdownItem( anchor ) {
		return div({
			className: 'dropdown-item',
			child: anchor
		});
	}
}() );
