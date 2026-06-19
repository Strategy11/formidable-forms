'use strict';

( function() {
	const { documentOn } = frmDom.util;
	const { __ } = wp.i18n;

	function closeSettingsPanel( panel ) {
		panel.classList.remove( 'frm-forms-list-settings--visible' );
		panel.addEventListener( 'transitionend', () => {
			panel.classList.add( 'frm_hidden' );
		}, { once: true } );
	}

	function handleClickFormsListSettings( event ) {
		event.preventDefault();
		const btn = event.target.closest( 'a' );
		if ( ! btn ) {
			return;
		}

		// If dropdown is already moved here, toggle it.
		if ( btn.nextElementSibling && 'frm-forms-list-settings' === btn.nextElementSibling.id && ! btn.nextElementSibling.classList.contains( 'frm_hidden' ) ) {
			closeSettingsPanel( btn.nextElementSibling );
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
				closeSettingsPanel( dropdown );
				document.removeEventListener( 'click', handleOutsideClick );
			}
		};

		// Add outside click listener.
		document.addEventListener( 'click', handleOutsideClick );

		dropdownWrapper.classList.remove( 'frm_hidden' );
		// Force reflow so the browser registers the element before adding the transition class.
		dropdownWrapper.getBoundingClientRect();
		dropdownWrapper.classList.add( 'frm-forms-list-settings--visible' );
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

	/**
	 * Update the 'mode' query param in the current URL based on the show description toggle.
	 * The screen-options redirect uses HTTP_REFERER, so the updated URL is preserved after submit.
	 *
	 * @since 6.32
	 *
	 * @return {void}
	 */
	function syncModeToUrl() {
		const showDescCheckbox = document.getElementById( 'frm-forms-list-show-desc' );
		if ( ! showDescCheckbox ) {
			return;
		}

		const url = new URL( window.location.href );
		if ( showDescCheckbox.checked ) {
			url.searchParams.set( 'mode', 'excerpt' );
		} else {
			url.searchParams.delete( 'mode' );
		}
		history.replaceState( null, '', url.toString() );
	}

	function handleClickApplyBtn() {
		syncModeToUrl();

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

		const nonceInput = frmDom.tag( 'input' );
		nonceInput.type = 'hidden';
		nonceInput.name = 'screenoptionnonce';
		nonceInput.value = document.getElementById( 'screenoptionnonce' ).value;

		const form = frmDom.tag( 'form', {
			className: 'frm_hidden',
			children: [ perPageInput, perPageOptionNameInput, nonceInput ],
		} );
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
		container.classList.toggle( 'frm-collapsible-box--collapsed' );
	}

	// Click the gear icon.
	documentOn( 'click', '.frm-forms-list-settings-btn', handleClickFormsListSettings );

	documentOn( 'change', 'input[data-wp-column-input-id]', handleChangeColumns );
	documentOn( 'click', '#frm-save-forms-list-settings-btn', handleClickApplyBtn );

	documentOn( 'click', '.frm-collapsible-box__btn', handleClickCollapsibleBtn );

	// Embeds toggle functionality
	const documentFragment = document.createDocumentFragment();

	documentOn( 'click', '.frm-forms-list-embeds-btn', event => {
		event.preventDefault();

		let btn = event.target;
		if ( ! event.target.classList.contains( 'frm-forms-list-embeds-btn' ) ) {
			btn = event.target.closest( '.frm-forms-list-embeds-btn' );
		}

		if ( ! btn.dataset.posts ) {
			return;
		}

		const posts = JSON.parse( btn.dataset.posts );
		if ( ! posts.length ) {
			return;
		}

		const btnOpenedClass = 'frm-forms-list-embeds-btn--opened';

		const rowEl = btn.closest( 'tr' );
		if ( rowEl.nextElementSibling?.id.startsWith( 'frm-forms-list-embeds-row-' ) ) {
			// Remove the extra row if it exists. Move it to fragment to reuse later.
			btn.classList.remove( btnOpenedClass );
			documentFragment.append( rowEl.nextElementSibling );
			return;
		}

		const id = rowEl.id.replace( 'item-action-', '' );
		const trInFragment = documentFragment.querySelector( `#frm-forms-list-embeds-row-${ id }` );
		if ( trInFragment ) {
			// Use the existing fragment row if it exists.
			btn.classList.add( btnOpenedClass );
			rowEl.after( trInFragment );
			return;
		}

		const columnsCount = rowEl.querySelectorAll( 'td:not(.hidden), th:not(.hidden)' ).length;
		const extraTdEl = frmDom.tag( 'td', {
			className: 'colspanchange',
			children: [
				frmDom.tag( 'h4', {
					text: __( 'Embed Locations', 'formidable' )
				} ),
				frmDom.div( {
					className: 'frm-forms-list-embeds-posts',
					children: posts.map( post => {
						const postLink = frmDom.a( {
							href: post.edit_link,
							target: '_blank',
							text: post.post_title || ( post.post_name ? `/${ post.post_name }` : __( '(no title)', 'formidable' ) ),
						} );
						if ( post.title_contains_html ) {
							postLink.innerHTML = frmAdminBuild.purifyHtml( post.post_title || ( post.post_name ? `/${ post.post_name }` : __( '(no title)', 'formidable' ) ) );
						}

						const leftChildren = [
							postLink,
							post.post_title && post.post_name && post.post_name !== '' ? frmDom.span( {
								text: `/${ post.post_name }`
							} ) : undefined
						].filter( Boolean );

						return frmDom.div( {
							className: 'frm-forms-list-embeds-post',
							children: [
								frmDom.div( {
									className: 'frm-forms-list-embeds-post__left',
									children: leftChildren,
								} ),
								frmDom.div( {
									className: 'frm-forms-list-embeds-post__right',
									child: frmDom.a( {
										href: post.permalink,
										target: '_blank',
										children: [
											__( 'View in new tab', 'formidable' ),
											frmDom.svg( {
												href: '#frm_arrowup8_icon',
												classList: [ 'frm-rotate-45' ],
											} )
										]
									} )
								} )
							]
						} );
					} )
				} )
			]
		} );

		extraTdEl.colSpan = columnsCount - 1;
		const extraRowEl = frmDom.tag( 'tr', {
			children: [
				frmDom.tag( 'td' ),
				extraTdEl,
			],
			id: `frm-forms-list-embeds-row-${ rowEl.id.replace( 'item-action-', '' ) }`,
			className: 'frm-forms-list-embeds-row',
		} );

		btn.classList.add( btnOpenedClass );
		documentFragment.append( extraRowEl );
		rowEl.after( extraRowEl );
	} );
}() );
