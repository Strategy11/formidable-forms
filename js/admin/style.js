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
			// TODO if we're in edit view we want to save another form instead.

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
		const form = document.getElementById( 'frm_styling_form' );
		if ( form ) {
			form.submit();
			return;
		}

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

	/**
	 * This gets triggered through a hook called in frmAdminBuild.styleInit() from formidable_admin.js.
	 *
	 * @returns {void}
	 */
	function onStyleEditorInit() {
		collapseAllSections();

		const { debounce }           = frmDom.util;
		const debouncedPreviewUpdate = debounce( changeStyling, 100 );

		document.getElementById( 'frm_field_height' ).addEventListener( 'change', textSquishCheck );
		document.getElementById( 'frm_field_font_size' ).addEventListener( 'change', textSquishCheck );
		document.getElementById( 'frm_field_pad' ).addEventListener( 'change', textSquishCheck );

		jQuery( 'input.hex' ).wpColorPicker({
			change: function( event ) {
				if ( null !== event.target.getAttribute( 'data-alpha-color-type' ) ) {
					debouncedPreviewUpdate();
					return;
				}

				const hexcolor = jQuery( this ).wpColorPicker( 'color' );
				jQuery( event.target ).val( hexcolor ).trigger( 'change' );
			}
		});
		jQuery( '.wp-color-result-text' ).text( function( _, oldText ) {
			return oldText === 'Select Color' ? 'Select' : oldText;
		});
		jQuery( '#frm_styling_form .styling_settings' ).on( 'change', debouncedPreviewUpdate );

		jQuery( '.frm_pro_form #datepicker_sample' ).datepicker({ changeMonth: true, changeYear: true });
		jQuery( document.getElementById( 'frm_position' ) ).on( 'change', setPosClass );

		// Check floating label when focus or blur fields.
		const floatingLabelSelector = '.frm_inside_container > input, .frm_inside_container > textarea, .frm_inside_container > select';
		[ 'focus', 'blur', 'change' ].forEach( function( eventName ) {
			documentOn(
				eventName,
				floatingLabelSelector,
				function( event ) {
					checkFloatingLabelsForStyles( event.target );
				},
				true
			);
		});

		// Trigger label position option on load.
		const changeEvent = document.createEvent( 'HTMLEvents' );
		changeEvent.initEvent( 'change', true, false );
		document.getElementById( 'frm_position' ).dispatchEvent( changeEvent );

		function collapseAllSections() {
			jQuery( '.control-section.accordion-section.open' ).removeClass( 'open' );
		}

		function changeStyling() {
			let locStr = jQuery( 'input[name^="frm_style_setting[post_content]"], select[name^="frm_style_setting[post_content]"], textarea[name^="frm_style_setting[post_content]"], input[name="style_name"]' ).serializeArray();
			locStr     = JSON.stringify( locStr );
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'frm_change_styling',
					nonce: frmGlobal.nonce,
					frm_style_setting: locStr
				},
				success: function( css ) {
					document.getElementById( 'this_css' ).innerHTML = css;
				}
			});
		}

		function textSquishCheck() {
			const size = document.getElementById( 'frm_field_font_size' ).value.replace( /\D/g, '' );
			const height = document.getElementById( 'frm_field_height' ).value.replace( /\D/g, '' );
			const paddingEntered = document.getElementById( 'frm_field_pad' ).value.split( ' ' );
			const paddingCount = paddingEntered.length;

			// If too many or too few padding entries, leave now
			if ( paddingCount === 0 || paddingCount > 4 || height === '' ) {
				return;
			}

			// Get the top and bottom padding from entered values
			const paddingTop = paddingEntered[0].replace( /\D/g, '' );
			const paddingBottom = paddingTop;
			if ( paddingCount >= 3 ) {
				paddingBottom = paddingEntered[2].replace( /\D/g, '' );
			}

			// Check if there is enough space for text
			const textSpace = height - size - paddingTop - paddingBottom - 3;
			if ( textSpace < 0 ) {
				infoModal( frm_admin_js.css_invalid_size );
			}
		}

		function setPosClass() {
			/*jshint validthis:true */
			let value = this.value;
			if ( value === 'none' ) {
				value = 'top';
			} else if ( value === 'no_label' ) {
				value = 'none';
			}
	
			document.querySelectorAll( '.frm_pos_container' ).forEach( container => {
				// Fields that support floating label should have a directly child input/textarea/select.
				const input = container.querySelector( ':scope > input, :scope > select, :scope > textarea' );
	
				if ( 'inside' === value && ! input ) {
					value = 'top';
				}
	
				container.classList.remove( 'frm_top_container', 'frm_left_container', 'frm_right_container', 'frm_none_container', 'frm_inside_container' );
				container.classList.add( 'frm_' + value + '_container' );
	
				if ( 'inside' === value ) {
					checkFloatingLabelsForStyles( input, container );
				}
			});
		}

		jQuery( '#menu-settings-column' ).on(
			'click',
			function( e ) {
				const target = jQuery( e.target );

				if ( e.target.className.indexOf( 'nav-tab-link' ) === -1 ) {
					return;
				}

				const panelId = target.data( 'type' );
				const wrapper = target.parents( '.accordion-section-content' ).first();

				jQuery( '.tabs-panel-active', wrapper ).removeClass( 'tabs-panel-active' ).addClass( 'tabs-panel-inactive' );
				jQuery( '#' + panelId, wrapper ).removeClass( 'tabs-panel-inactive' ).addClass( 'tabs-panel-active' );

				jQuery( '.tabs', wrapper ).removeClass( 'tabs' );
				target.parent().addClass( 'tabs' );

				// select the search bar
				jQuery( '.quick-search', wrapper ).trigger( 'focus' );

				e.preventDefault();
			}
		);

		jQuery( document ).on( 'change', '.frm-dropdown-menu input[type="radio"]', function() {
			const radio = this;
			const btnGrp = this.closest( '.btn-group' );
			const btnId = btnGrp.getAttribute( 'id' );

			const select = document.getElementById( btnId.replace( '_select', '' ) );
			if ( select ) {
				select.value = radio.value;
			}

			jQuery( btnGrp ).children( 'button' ).html( radio.nextElementSibling.innerHTML + ' <b class="caret"></b>' );

			const activeItem = btnGrp.querySelector( '.dropdown-item.active' );
			if ( activeItem ) {
				activeItem.classList.remove( 'active' );
			}

			this.closest( '.dropdown-item' ).classList.add( 'active' );
		});

		/**
		 * Does the same as jQuery( document ).on( 'event', 'selector', handler ).
		 *
		 * @since 5.4.2
		 *
		 * @param {String}         event    Event name.
		 * @param {String}         selector Selector.
		 * @param {Function}       handler  Handler.
		 * @param {Boolean|Object} options  Options to be added to `addEventListener()` method. Default is `false`.
		 */
		function documentOn( event, selector, handler, options ) {
			if ( 'undefined' === typeof options ) {
				options = false;
			}

			document.addEventListener( event, function( e ) {
				let target;

				// loop parent nodes from the target to the delegation node.
				for ( target = e.target; target && target != this; target = target.parentNode ) {
					if ( target.matches( selector ) ) {
						handler.call( target, e );
						break;
					}
				}
			}, options );
		}

		/**
		 * @param {HTMLElement} input
		 * @param {HTMLElement} container
		 * @returns {void}
		 */
		function checkFloatingLabelsForStyles( input, container ) {
			if ( ! container ) {
				container = input.closest( '.frm_inside_container' );
			}

			const shouldFloatTop = input.value || document.activeElement === input;

			container.classList.toggle( 'frm_label_float_top', shouldFloatTop );

			if ( 'SELECT' !== input.tagName ) {
				return;
			}

			const firstOpt = input.querySelector( 'option:first-child' );

			if ( shouldFloatTop ) {
				if ( firstOpt.hasAttribute( 'data-label' ) ) {
					firstOpt.textContent = firstOpt.getAttribute( 'data-label' );
					firstOpt.removeAttribute( 'data-label' );
				}
			} else {
				if ( firstOpt.textContent ) {
					firstOpt.setAttribute( 'data-label', firstOpt.textContent );
					firstOpt.textContent = '';
				}
			}
		}
	}

	wp.hooks.addAction( 'frm_style_editor_init', 'formidable', onStyleEditorInit );
}() );
