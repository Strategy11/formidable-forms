( function() {
	/* globals wp, frmDom */

	const { __ } = wp.i18n;
	const state  = {
		showingSampleForm: false
	};

	const { div, a, tag, svg } = frmDom;

	document.addEventListener( 'click', handleClickEvents );
	addHamburgMenusToCards();

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

		// TODO this does nothing.
		const editOption = a({ text: __( 'Edit', 'formidable' ) });
		editOption.setAttribute( 'href', data.editUrl );

		// TODO this does nothing.
		const resetOption = a({ text: __( 'Reset', 'formidable' ) });
		resetOption.setAttribute( 'href', '#' );

		// TODO this does nothing.
		const renameOption = a({ text: __( 'Rename', 'formidable' ) });
		renameOption.setAttribute( 'href', '#' );

		const dropdownMenu = div({
			className: 'frm-dropdown-menu',
			children: [ editOption/*, resetOption, renameOption*/ ].map( wrapDropdownItem )
		});
		dropdownMenu.setAttribute( 'role', 'menu' );

		function wrapDropdownItem( anchor ) {
			return div({
				className: 'dropdown-item',
				child: anchor
			});
		}

		return div({
			className: 'dropdown',
			children: [ hamburgerMenu, dropdownMenu ]
		});
	}
}() );
