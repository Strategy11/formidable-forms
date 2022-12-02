( function() {
	/* globals wp, frmDom */
	'use strict';

	const { __ } = wp.i18n;
	const state = {
		showingSampleForm: false
	};
	const { div, a, tag, svg } = frmDom;
	const { onClickPreventDefault } = frmDom.util;
	const { maybeCreateModal, footerButton } = frmDom.modal;
	const { doJsonPost } = frmDom.ajax;

	document.addEventListener( 'click', handleClickEvents );
	setTimeout( addHamburgMenusToCards, 0 ); // Add a timeout so Pro has a chance to add a filter first.

	function handleClickEvents( event ) {
		const target = event.target;

		if ( target.classList.contains( 'frm-style-card' ) || target.closest( '.frm-style-card' ) ) {
			handleStyleCardClick( event );
			return;
		}

		if ( 'frm_toggle_sample_form' === target.id || target.closest( '#frm_toggle_sample_form' ) ) {
			toggleSampleForm();
			return;
		}

		if ( 'frm_submit_side_top' === target.id || target.closest( '#frm_submit_side_top' ) ) {
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
		document.getElementById( 'frm_toggle_sample_form' ).querySelector( 'span' ).textContent = state.showingSampleForm ? __( 'View my form', 'formidable' ) : __( 'View sample form', 'formidable' );
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
				wrapper.appendChild( getHamburgerMenu( card.dataset ) );
			}
		);
	}

	/**
	 * @param {Object} data {
	 *     @type {String} editUrl
	 *     @type {String} styleId
	 * }
	 * @returns {Element}
	 */
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
		addIconToOption( editOption, 'frm_pencil_icon' );
		const resetOption = a({
			text: __( 'Reset', 'formidable' )
		});
		addIconToOption( resetOption, 'frm_reset_icon' );
		onClickPreventDefault( resetOption, () => confirmResetStyle( data.styleId ) );

		const hookName            = 'frm_style_card_dropdown_options';
		const hookArgs            = { data, addIconToOption };
		const dropdownMenuOptions = wp.hooks.applyFilters( hookName, [{ anchor: editOption, type: 'edit' }, { anchor: resetOption, type: 'reset' }], hookArgs );
		const dropdownMenu        = div({
			// Use dropdown-menu-right to avoid an overlapping issue with the card to the right (where the # of forms would appear above the menu).
			className: 'frm-dropdown-menu dropdown-menu-right',
			children: dropdownMenuOptions.map( wrapDropdownItem )
		});

		dropdownMenu.setAttribute( 'role', 'menu' );

		return div({
			className: 'dropdown',
			children: [ hamburgerMenu, dropdownMenu ]
		});
	}

	function addIconToOption( option, iconId ) {
		const icon = frmDom.svg({ href: '#' + iconId });
		option.insertBefore( icon, option.firstChild );
	}

	/**
	 * @param {String} styleId
	 * @returns {void}
	 */
	function confirmResetStyle( styleId ) {
		const modal = maybeCreateModal(
			'frm_reset_style_modal',
			{
				title: __( 'Reset style', 'formidable' ),
				content: getResetStyleModalContent(),
				footer: getResetStyleModalFooter( styleId )
			}
		);
		modal.classList.add( 'frm_common_modal' );
	}

	/**
	 * @returns {Element}
	 */
	function getResetStyleModalContent() {
		const content = div( __( 'Reset this style back to the default?', 'formidable' ) );
		content.style.padding = '20px';
		return content;
	}

	/**
	 * @param {String} styleId
	 * @returns {Element}
	 */
	function getResetStyleModalFooter( styleId ) {
		const cancelButton = footerButton({
			text: __( 'Cancel', 'formidable' ),
			buttonType: 'cancel'
		});
		cancelButton.classList.add( 'dismiss' );
		const resetButton = footerButton({
			text: __( 'Reset style', 'formidable' ),
			buttonType: 'primary'
		});
		onClickPreventDefault( resetButton, () => resetStyle( styleId ) );
		return div({ children: [ cancelButton, resetButton ] });
	}

	function resetStyle( styleId ) {
		const formData = new FormData();
		formData.append( 'styleId', styleId );
		doJsonPost( 'settings_reset', formData ).then( setCardToDefaultStylesAfterReset );
	}

	function setCardToDefaultStylesAfterReset() {
		// TODO after it's reset we would want to switch the class to the default so the card syncs.
		// The preview would need to switch as well.
	}

	/**
	 * @param {Object} data {
	 *     @type {Element} anchor
	 *     @type {String} type
	 * }
	 * @returns {Element}
	 */
	function wrapDropdownItem({ anchor, type }) {
		return div({
			className: 'dropdown-item frm-' + type + '-style',
			child: anchor
		});
	}
}() );
