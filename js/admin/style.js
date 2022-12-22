( function() {
	/* globals wp, frmDom, frmAdminBuild */
	'use strict';

	const { __ } = wp.i18n;
	const state = {
		showingSampleForm: false
	};
	const { div, a, tag, svg } = frmDom;
	const { onClickPreventDefault } = frmDom.util;
	const { maybeCreateModal, footerButton } = frmDom.modal;
	const { doJsonPost } = frmDom.ajax;

	const isListPage = document.getElementsByClassName( 'frm-style-card' ).length > 0;
	if ( isListPage ) {
		initListPage();
	}

	initCommonEventListeners();
	initPreview();

	/**
	 * These are shared events for both the edit/list views like the sample form toggle.
	 *
	 * @returns {void}
	 */
	function initCommonEventListeners() {
		document.addEventListener( 'click', handleCommonClickEvents );
		disablePreviewSubmitButtons();
	}

	function initPreview() {
		initFloatingLabels();
	}

	/**
	 * @returns {void}
	 */
	function initListPage() {
		document.addEventListener( 'click', handleClickEventsForListPage );
		setTimeout( addHamburgMenusToCards, 0 ); // Add a timeout so Pro has a chance to add a filter first.
		initDatepickerSample();

		const enableToggle = document.getElementById( 'frm_enable_styling' );
		enableToggle.addEventListener( 'change', handleEnableStylingToggleChange );

		// TODO I need to track label position for each style card.
		// This whay when I change between cards to preview, the container classes get synced.

		// Update label position in preview on list page.
		// On the edit page this is handled with the initPosClass function instead.
		// TODO do not just always use inside either. Get the active card and use its dataset value.
//		const input = tag( 'input' );
//		input.value = 'inside';
//		setPosClass.bind( input )();
	}

	/**
	 * When Formidable styling is disabled, the list of styles fades out.
	 * The style ID value associated with the selected style card gets cleared.
	 * This is because disabling styles is linked to the custom_style option as well.
	 *
	 * @param {Event} event
	 *
	 * @returns {void}
	 */
	function handleEnableStylingToggleChange( event ) {
		const cardWrapper   = document.getElementById( 'frm_style_cards_wrapper' );
		const styleIdInput  = getStyleIdInput();
		const stylesEnabled = event.target.checked;
		
		cardWrapper.classList.toggle( 'frm-styles-enabled', stylesEnabled );

		if ( ! stylesEnabled ) {
			styleIdInput.value = '0';

			toggleFormidableStylingInPreviewForms( false );
			return;
		}

		const card         = document.querySelector( '.frm-active-style-card' );
		styleIdInput.value = card.dataset.styleId;

		toggleFormidableStylingInPreviewForms( true );
	}

	/**
	 * @param {bool} on
	 * @returns {void}
	 */
	function toggleFormidableStylingInPreviewForms( on ) {
		const preview    = document.getElementById( 'frm_style_preview' );
		const activeCard = document.querySelector( '.frm-active-style-card' );

		let selector = '.frm_forms';
		if ( ! on ) {
			selector += '.with_frm_style';
		}

		preview.querySelectorAll( selector ).forEach(
			formParent => {
				formParent.classList.toggle( 'with_frm_style', on );
				formParent.classList.toggle( activeCard.dataset.classname, on );
			}
		);
	}

	/**
	 * @returns {HTMLElement}
	 */
	function getStyleIdInput() {
		return document.getElementById( 'frm_style_list_form' ).querySelector( '[name="style_id"]' );
	}

	/**
	 * @param {Event} event
	 * @returns {void}
	 */
	function handleCommonClickEvents( event ) {
		const target = event.target;

		if ( 'frm_toggle_sample_form' === target.id || target.closest( '#frm_toggle_sample_form' ) ) {
			toggleSampleForm();
			return;
		}

		if ( 'frm_submit_side_top' === target.id || target.closest( '#frm_submit_side_top' ) ) {
			handleUpdateClick();
			return;
		}
	}

	/**
	 * @returns {void}
	 */
	function disablePreviewSubmitButtons() {
		const preview = document.getElementById( 'frm_style_preview' );
		preview.querySelectorAll( 'form' ).forEach(
			form => form.addEventListener(
				'submit',
				/**
				 * Prevent form submit event.
				 *
				 * @param {Event} event
				 * @returns {false}
				 */
				event => {
					event.preventDefault();
					event.stopPropagation();
					return false;
				}
			)
		);
	}

	/**
	 * @param {Event} event
	 * @returns {void}
	 */
	function handleClickEventsForListPage( event ) {
		const target = event.target;

		if ( target.classList.contains( 'frm-style-card' ) || target.closest( '.frm-style-card' ) ) {
			handleStyleCardClick( event );
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
		const styleIdInput = getStyleIdInput();

		disableLabelTransitions();

		activeCard.classList.remove( 'frm-active-style-card' );
		card.classList.add( 'frm-active-style-card' );
		form.parentNode.classList.remove( activeCard.dataset.classname );
		form.parentNode.classList.add( card.dataset.classname );
		sampleForm.classList.remove( activeCard.dataset.classname );
		sampleForm.classList.add( card.dataset.classname );
		styleIdInput.value = card.dataset.styleId;

		setTimeout( enableLabelTransitions, 1 );

		// We want to toggle the edit button so you can only leave the page to edit the style if it's active (to avoid unsaved changes).
		// TODO: We should prompt for unsaved changes before redirecting.
		const editButton     = document.getElementById( 'frm_edit_style' );
		const showEditButton = null !== card.querySelector( '.frm-selected-style-tag' );
		editButton.classList.toggle( 'frm_hidden', ! showEditButton );
	}

	/**
	 * Floating labels have a transition style. Turn it off temporarily when switching between cards to avoid a transition between two different style classes.
	 *
	 * @returns {void}
	 */
	function disableLabelTransitions() {
		setLabelTransitionStyle( 'none' );
	}

	/**
	 * @returns {void}
	 */
	function enableLabelTransitions() {
		setLabelTransitionStyle( '' );
	}

	/**
	 * @param {String} value
	 * @returns {void}
	 */
	function setLabelTransitionStyle( value ) {
		document.getElementById( 'frm_style_preview' ).querySelectorAll( '.frm_inside_container' ).forEach(
			container => container.querySelector( 'label' ).style.transition = value
		);
	}

	/**
	 * @returns {void}
	 */
	function toggleSampleForm() {
		state.showingSampleForm = ! state.showingSampleForm;

		document.getElementById( 'frm_active_style_form' ).classList.toggle( 'frm_hidden', state.showingSampleForm );
		document.getElementById( 'frm_sample_form' ).classList.toggle( 'frm_hidden', ! state.showingSampleForm );
		document.getElementById( 'frm_toggle_sample_form' ).querySelector( 'span' ).textContent = state.showingSampleForm ? __( 'View my form', 'formidable' ) : __( 'View sample form', 'formidable' );
	}

	/**
	 * @returns {void}
	 */
	function handleUpdateClick() {
		const form = document.getElementById( 'frm_styling_form' );
		if ( form ) {
			// Submitting for an "edit" view.
			form.submit();
			return;
		}

		// Submit the "list" view (assign a style to a form).
		document.getElementById( 'frm_style_list_form' ).submit();
	}

	/**
	 * @returns {void}
	 */
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
	 * Get a dropdown and the "hamburger" stacked dot menu trigger for a single style card.
	 *
	 * @param {Object} data {
	 *     @type {String} editUrl
	 *     @type {String} styleId
	 * }
	 * @returns {HTMLElement}
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

	/**
	 * @param {HTMLElement} option
	 * @param {String} iconId
	 * @returns {void}
	 */
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
	 * @returns {HTMLElement}
	 */
	function getResetStyleModalContent() {
		const content = div( __( 'Reset this style back to the default?', 'formidable' ) );
		content.style.padding = '20px';
		return content;
	}

	/**
	 * @param {String} styleId
	 * @returns {HTMLElement}
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

	/**
	 * @param {String} styleId
	 * @returns {void}
	 */
	function resetStyle( styleId ) {
		const formData = new FormData();
		formData.append( 'styleId', styleId );
		doJsonPost( 'settings_reset', formData ).then( reloadAfterStyleReset );
	}

	/**
	 * Just reload the page after a card is reset for now as it's easier than trying to load all of the default rules.
	 *
	 * @returns {void}
	 */
	function reloadAfterStyleReset() {
		// TODO a success message would be useful.
		window.location.reload();
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
		const { debounce }           = frmDom.util;
		const debouncedPreviewUpdate = debounce( () => { console.log( 'Change event triggered' ); console.trace(); changeStyling(); }, 100 );

		initPosClass(); // It's important that this gets called before we add event listeners because it triggers change events.

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

		// This is really only necessary for Pro. But if Pro is not up to date to initialize the datepicker in the sample form, it should still work because it's initialized here.
		initDatepickerSample();

		/**
		 * Sends an AJAX request for new CSS to use for the preview.
		 * This is called whenever a style setting is changed, generally using debouncedPreviewUpdate to avoid simultaneous requests.
		 *
		 * @returns {void}
		 */
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

		/**
		 * Possibly pop up with a warning that "text will not display correctly if the field height is too small relative to the field padding and text size".
		 * This can be triggered when modifying font size, height, and padding.
		 *
		 * @returns {void}
		 */
		function textSquishCheck() {
			const size           = document.getElementById( 'frm_field_font_size' ).value.replace( /\D/g, '' );
			const height         = document.getElementById( 'frm_field_height' ).value.replace( /\D/g, '' );
			const paddingEntered = document.getElementById( 'frm_field_pad' ).value.split( ' ' );
			const paddingCount   = paddingEntered.length;

			// If too many or too few padding entries, leave now
			if ( paddingCount === 0 || paddingCount > 4 || height === '' ) {
				return;
			}

			// Get the top and bottom padding from entered values
			const paddingTop    = paddingEntered[0].replace( /\D/g, '' );
			const paddingBottom = paddingTop;
			if ( paddingCount >= 3 ) {
				paddingBottom = paddingEntered[2].replace( /\D/g, '' );
			}

			// Check if there is enough space for text
			const textSpace = height - size - paddingTop - paddingBottom - 3;
			if ( textSpace < 0 ) {
				frmAdminBuild.infoModal( frm_admin_js.css_invalid_size );
			}
		}

		/**
		 * When the Collapse icons are updated, sync the dropdown.
		 * Otherwise the previously selected value will still appear as the selected value.
		 *
		 * @returns {void}
		 */
		jQuery( document ).on( 'change', '.frm-dropdown-menu input[type="radio"]', function() {
			const radio  = this;
			const btnGrp = this.closest( '.btn-group' );
			const btnId  = btnGrp.getAttribute( 'id' );

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

	/**
	 * @returns {void}
	 */
	function initPosClass() {
		const positionSetting = document.getElementById( 'frm_position' );

		jQuery( positionSetting ).on( 'change', setPosClass );

		// Trigger label position option on load.
		const changeEvent = document.createEvent( 'HTMLEvents' );
		changeEvent.initEvent( 'change', true, false );
		positionSetting.dispatchEvent( changeEvent );
	}

	/**
	 * Update label container classes when the label "Position" setting is changed.
	 *
	 * @todo This doesn't work yet with the "My form" preview.
	 *
	 * @returns {void}
	 */
	function setPosClass() {
		/*jshint validthis:true */
		let value = this.value;
		if ( value === 'none' ) {
			value = 'top';
		} else if ( value === 'no_label' ) {
			value = 'none';
		}

		document.getElementById( 'frm_style_preview' ).querySelectorAll( '.frm_form_field' ).forEach( container => {
			// Fields that support floating label should have a directly child input/textarea/select.
			const input = container.querySelector( ':scope > input, :scope > select, :scope > textarea' );

			let currentValue = value;

			if ( 'inside' === currentValue && ! input ) {
				currentValue = 'top';
			}

			container.classList.remove( 'frm_top_container', 'frm_left_container', 'frm_right_container', 'frm_none_container', 'frm_inside_container' );
			container.classList.add( 'frm_' + currentValue + '_container' );

			if ( 'inside' === currentValue ) {
				checkFloatingLabelsForStyles( input, container );
			}
		});
	}

	/**
	 * Technically this isn't required as the form preview is loading JavaScript.
	 *
	 * @returns {void}
	 */
	function initFloatingLabels() {
		[ 'focus', 'blur', 'change' ].forEach(
			eventName => documentOn(
				eventName,
				'#frm_style_preview .frm_inside_container > input, #frm_style_preview .frm_inside_container > textarea, #frm_style_preview .frm_inside_container > select',
				event => checkFloatingLabelsForStyles( event.target ),
				true
			)
		);
	}

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
	 * Enable the datepicker in the sample form preview.
	 *
	 * @returns {void}
	 */
	function initDatepickerSample() {
		jQuery( '#datepicker_sample' ).datepicker({ changeMonth: true, changeYear: true });
	}

	wp.hooks.addAction( 'frm_style_editor_init', 'formidable', onStyleEditorInit );

	if ( 'undefined' !== typeof window.__FRMSIG && 'undefined' === typeof window.frmFrontForm ) {
		// The signature add on expects that validateFormSubmit is callable.
		// Without this, drawing in a signature field triggers a "Uncaught ReferenceError: frmFrontForm is not defined" error.
		// We don't want the validation to actually triggr, so just fill in an empty function.
		window.frmFrontForm = { validateFormSubmit: () => {} };
	}
}() );
