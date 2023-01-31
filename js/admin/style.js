/**
 * This script file handles style settings in the Lite plugin.
 * Pro-specific features are in the style-settings.js file in Pro.
 */
( function() {
	/* globals wp, frmDom, frmAdminBuild */
	'use strict';

	const { __ }                                                 = wp.i18n;
	const state                                                  = {
		showingSampleForm: false, // boolean
		unsavedChanges: false, // boolean
		autoId: 0, // Number
		// Track the value of the selected style ID on page (on the list page).
		// This is tracked to determine if there are actually unsaved changes.
		// This way when you switch back to the initial value it doesn't count as a change.
		initialSelectedStyleValue: false // String|false
	};
	const { div, span, a, labelledTextInput, tag, svg, success } = frmDom;
	const { onClickPreventDefault }                              = frmDom.util;
	const { maybeCreateModal, footerButton }                     = frmDom.modal;
	const { doJsonPost }                                         = frmDom.ajax;

	const isListPage = document.getElementsByClassName( 'frm-style-card' ).length > 0;
	if ( isListPage ) {
		initListPage();
	}

	initCommonEventListeners();
	initPreview();

	/**
	 * These are shared events for both the edit/list views like the sample form toggle.
	 * This includes preview events, but also the update button click event handling for both views.
	 *
	 * @returns {void}
	 */
	function initCommonEventListeners() {
		document.addEventListener( 'click', handleCommonClickEvents );
		window.addEventListener( 'beforeunload', maybeConfirmExit );
		disablePreviewSubmitButtons();
	}

	/**
	 * Initialize common functions required for the preview in both the edit and list views.
	 *
	 * @returns {void}
	 */
	function initPreview() {
		initFloatingLabels();
		fillMissingSignatureValidationFunction();

		// Remove .wp-core-ui from the body so the preview can avoid it.
		// Then add it back where we want to use admin styles (the sidebar, otherwise inputs appear short).
		document.body.classList.remove( 'wp-core-ui' );
		document.getElementById( 'frm_style_sidebar' ).classList.add( 'wp-core-ui' );
	}

	/**
	 * @returns {void}
	 */
	function initListPage() {
		document.addEventListener( 'click', handleClickEventsForListPage );
		setTimeout( addHamburgerMenusToCards, 0 ); // Add a timeout so Pro has a chance to add a filter first.
		initDatepickerSample();

		const enableToggle              = document.getElementById( 'frm_enable_styling' );
		const styleIdInput              = getStyleIdInput();
		state.initialSelectedStyleValue = styleIdInput.value;

		enableToggle.addEventListener( 'change', handleEnableStylingToggleChange );

		syncPreviewFormLabelPositionsWithActiveStyle();
		makeToggleAccessible();
		initStyleCardPagination();
	}

	/**
	 * Update label position in preview on list page.
	 * On the edit page this is handled with the initPosClass function instead.
	 */
	function syncPreviewFormLabelPositionsWithActiveStyle() {
		const activeCard = getActiveCard();
		if ( activeCard ) {
			changeLabelPositionsInPreview( activeCard.dataset.labelPosition );
		}
	}

	/**
	 * Toggle accessibility is handled for other pages in Pro.
	 * As the only toggle in Lite is currently for Enabling styles only, simpler code can be used and loaded just for the styler for now.
	 *
	 * @returns {void}
	 */
	function makeToggleAccessible() {
		const toggleCheckbox = document.getElementById( 'frm_enable_styling' );
		if ( ! toggleCheckbox ) {
			return;
		}

		const toggle = toggleCheckbox.nextElementSibling;

		toggleCheckbox.addEventListener( 'change', () => toggle.setAttribute( 'aria-checked', toggleCheckbox.checked ? 'true' : 'false' ) );

		toggle.addEventListener(
			'keydown',
			/**
			 * Toggle the enable styling toggle when the space key is pressed.
			 *
			 * @param {Event} e
			 * @returns {void}
			 */
			e => {
				if ( ' ' === e.key ) {
					e.preventDefault(); // Prevent the list from scrolling when you hit space.
					toggle.click();
				}
			}
		);
	}

	/**
	 * Handle pagination click events.
	 *
	 * @returns {void}
	 */
	function initStyleCardPagination() {
		document.querySelectorAll( '.frm-style-card-pagination' ).forEach(
			pagination => {
				const wrapper    = pagination.closest( '.frm-style-card-wrapper' );
				const prevAnchor = pagination.querySelector( '.frm-prev-style-page' );
				const nextAnchor = pagination.querySelector( '.frm-next-style-page' );
				let pageNumber   = 1;

				[ prevAnchor, nextAnchor ].forEach(
					anchor => onClickPreventDefault(
						anchor,
						() => {
							const disabledClass = 'frm-disabled-pagination-anchor';
							if ( anchor.classList.contains( disabledClass ) ) {
								return;
							}

							if ( anchor === prevAnchor ) {
								pageNumber--;
							} else {
								pageNumber++;
							}

							const numberOfPages = parseInt( pagination.dataset.numberOfPages );

							prevAnchor.classList.toggle( disabledClass, 1 === pageNumber );
							nextAnchor.classList.toggle( disabledClass, numberOfPages === pageNumber );
							showCardsForPage( wrapper, pageNumber );
						}
					)
				);
			}
		);
	}

	/**
	 * @param {HTMLElement} wrapper
	 * @param {Number} pageNumber
	 * @returns {void}
	 */
	function showCardsForPage( wrapper, pageNumber ) {
		const pageSize = 3;
		const minIndex = ( pageNumber - 1 ) * pageSize;
		const maxIndex = minIndex + pageSize - 1;

		wrapper.querySelectorAll( '.frm-style-card' ).forEach(
			( card, index ) => {
				const hidden = index < minIndex || index > maxIndex;
				card.classList.toggle( 'frm_hidden', hidden );
			}
		);
	}

	/**
	 * @param {String} labelPosition
	 * @returns {void}
	 */
	function changeLabelPositionsInPreview( labelPosition ) {
		const input = tag( 'input' );
		input.value = labelPosition;
		setPosClass.bind( input )();
	}

	/**
	 * @returns {HTMLElement}
	 */
	function getActiveCard() {
		return document.querySelector( '.frm-active-style-card' );
	}

	/**
	 * When Formidable styling is disabled, the list of styles fades out.
	 * The style ID value associated with the selected style card gets cleared.
	 * This is because disabling styles is linked to the custom_style option as well.
	 *
	 * @param {Event} event
	 * @returns {void}
	 */
	function handleEnableStylingToggleChange( event ) {
		const stylesEnabled = event.target.checked;

		document.querySelectorAll( '.frm-style-card-wrapper' ).forEach(
			cardWrapper => cardWrapper.classList.toggle( 'frm-styles-enabled', stylesEnabled )
		);

		if ( ! stylesEnabled ) {
			const styleIdInput = getStyleIdInput();
			styleIdInput.value = '0';
			trackListPageChange();
			toggleFormidableStylingInPreviewForms( false );
			return;
		}

		toggleFormidableStylingInPreviewForms( true );

		// Click the active card so the style id input properly syncs.
		// In Pro, templates use a templateKey attribute so we don't always want card.dataset.styleId
		// There is no need to call trackListPageChange as it happens in the click event.
		const card = document.querySelector( '.frm-active-style-card' );
		if ( card ) {
			card.click();
		}
	}

	/**
	 * Track unsaved changes on the list page.
	 * All settings on the list page are mapped to the value of the styleIdInput.
	 * We track the value on load with state.initialSelectedStyleValue.
	 * Only consider unsaved changes on the page when this variable is no longer set to the original value.
	 *
	 * @returns {void}
	 */
	function trackListPageChange() {
		const styleIdInput   = getStyleIdInput();
		state.unsavedChanges = styleIdInput.value !== state.initialSelectedStyleValue;
	}

	/**
	 * @param {boolean} on
	 * @returns {void}
	 */
	function toggleFormidableStylingInPreviewForms( on ) {
		const preview    = document.getElementById( 'frm_style_preview' );
		const activeCard = getActiveCard();

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
		const cardIsLocked = card.classList.contains( 'frm-locked-style' );

		if ( cardIsLocked ) {
			maybeCreateStyleTemplateModal( card );
			return; // Exit early as we're not actually selecting a locked template for preview.
		}

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

		if ( ! cardIsLocked ) {
			// Don't update the form when a locked card is clicked.
			styleIdInput.value = card.dataset.styleId;
			trackListPageChange();
		}

		setTimeout( enableLabelTransitions, 1 );

		// We want to toggle the edit button so you can only leave the page to edit the style if it's active (to avoid unsaved changes).
		const editButton     = document.getElementById( 'frm_edit_style' );
		const showEditButton = null !== card.querySelector( '.frm-style-card-info' ); // Only the "Applied style" has card info.
		editButton.classList.toggle( 'frm_hidden', ! showEditButton );

		changeLabelPositionsInPreview( card.dataset.labelPosition );

		// Trigger an action here so Pro can handle template preview updates on card click.
		const hookName      = 'frm_style_card_click';
		const hookArgs      = { card, styleIdInput };
		wp.hooks.doAction( hookName, hookArgs );
	}

	/**
	 * @param {HTMLElement} card
	 * @returns {HTMLElement}
	 */
	function maybeCreateStyleTemplateModal( card ) {
		const titleElement  = card.querySelector( '.frm-style-card-title' );
		const templateTitle = titleElement.textContent;
		const modal         = maybeCreateModal(
			'frm_style_template_modal',
			{
				content: getStyleTemplateModalContent( card ),
				footer: getStyleTemplateModalFooter( card )
			}
		);
		modal.querySelector( '.frm-modal-title' ).textContent = templateTitle;
		return modal;
	}

	/**
	 * @param {HTMLElement} card
	 * @returns {HTMLElement}
	 */
	function getStyleTemplateModalContent( card ) {
		const children = [];

		children.push(
			div({
				className: 'frm_warning_style',
				children: [
					span(
						/* translators: %s: The required license type (ie. Plus, Business, or Elite) */
						__( 'Access to this style requires the %s plan.', 'formidable' )
							.replace( '%s', card.dataset.requires )
					),
					a({
						text: getUpgradeNowText(),
						href: card.dataset.upgradeUrl,
						target: '_blank'
					})
				]
			})
		);

		children.push( getStyleTemplateModalSample( card ) );

		return div({ children });
	}

	/**
	 * Get a small example form for the upsell modal.
	 * This just includes a small text field and submit button.
	 *
	 * @param {HTMLElement} card
	 * @returns {HTMLElement}
	 */
	function getStyleTemplateModalSample( card ) {
		const input = tag( 'input' );
		input.setAttribute( 'type', 'text' );
		input.value = __( 'This is sample text', 'formidable' );

		const button = tag( 'input' );
		button.setAttribute( 'type', 'submit' );
		button.disabled = true;
		button.className = 'frm_full_opacity';
		button.value = __( 'Submit', 'formidable' );

		return div({
			className: 'frm-style-sample with_frm_style ' + card.dataset.classname,
			children: [
				div({
					className: 'frm_form_field form-field',
					children: [
						tag( 'label', { className: 'frm_primary_label', text: __( 'Text field', 'formidable' ) }),
						input
					]
				}),
				div({
					className: 'frm_submit',
					child: button
				})
			]
		});
	}

	/**
	 * @param {HTMLElement} card
	 * @returns {HTMLElement}
	 */
	function getStyleTemplateModalFooter( card ) {
		const viewDemoSiteButton = footerButton({
			text: __( 'Learn More', 'formidable' ),
			buttonType: 'secondary'
		});
		viewDemoSiteButton.href = card.dataset.upgradeUrl;
		viewDemoSiteButton.target = '_blank';

		let primaryActionButton = footerButton({
			text: getUpgradeNowText(),
			buttonType: 'primary'
		});

		primaryActionButton.classList.remove( 'dismiss' );
		primaryActionButton.setAttribute( 'href', card.dataset.upgradeUrl );
		primaryActionButton.target = '_blank';

		return div({
			children: [ viewDemoSiteButton, primaryActionButton ]
		});
	}

	/**
	 * @returns {String}
	 */
	function getUpgradeNowText() {
		return __( 'Upgrade Now', 'formidable' );
	}

	/**
	 * Track an unsaved change on the edit page.
	 * This is included in the frmStylerFunctions global so unsaved changes can be tracked in Pro as well.
	 *
	 * @returns {void}
	 */
	function trackUnsavedChange() {
		state.unsavedChanges = true;
	}

	/**
	 * Possibly prevent leaving the page if there are unsaved changes.
	 *
	 * @param {Event} event
	 * @returns {void}
	 */
	function maybeConfirmExit( event ) {
		if ( ! state.unsavedChanges ) {
			return;
		}

		event.preventDefault();
		event.returnValue = '';
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
		state.unsavedChanges = false; // Prevent the saved changes pop up from triggering when submitting the form.

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
	 * Add menu dropdowns to style cards dynamically on load.
	 *
	 * @returns {void}
	 */
	function addHamburgerMenusToCards() {
		const cards = Array.from( document.getElementsByClassName( 'frm-style-card' ) );
		cards.forEach( card => maybeAddMenuToCard( card ) );
	}

	/**
	 * @param {HTMLlement} card
	 * @returns {void}
	 */
	function maybeAddMenuToCard( card ) {
		if ( ! shouldAddMenuToCard( card ) ) {
			return;
		}

		card.appendChild( getHamburgerMenu( card.dataset ) );
	}

	/**
	 * Avoid adding a menu to an upsell card or a template card.
	 *
	 * @param {HTMLlement} card
	 * @returns {boolean}
	 */
	function shouldAddMenuToCard( card ) {
		return 'frm_template_style_cards_wrapper' !== card.parentNode.id;
	}

	/**
	 * @returns {void}
	 */
	function addHamburgerMenuForEditPage() {
		const styleName = document.getElementById( 'frm_style_name' );
		if ( ! styleName ) {
			return;
		}

		const styleId = document.getElementById( 'frm_styling_form' ).querySelector( 'input[name="ID"]' ).value;

		const hamburgerMenu = getHamburgerMenu({ styleId });
		hamburgerMenu.classList.add( 'alignright' );
		styleName.parentNode.insertBefore( hamburgerMenu, styleName );
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
		const hamburgerMenu = a({
			className: 'frm-dropdown-toggle dropdown-toggle',
			child: svg({ href: '#frm_thick_more_vert_icon' })
		});
		hamburgerMenu.setAttribute( 'data-toggle', 'dropdown' );
		hamburgerMenu.setAttribute( 'data-container', 'body' );
		hamburgerMenu.setAttribute( 'role', 'button' );
		hamburgerMenu.setAttribute( 'tabindex', 0 );

		let dropdownMenuOptions = [];

		if ( 'string' === typeof data.editUrl ) {
			// The Edit option is not included on the Edit page.
			const editOption = a({
				text: __( 'Edit', 'formidable' ),
				href: data.editUrl
			});
			addIconToOption( editOption, 'frm_pencil_icon' );
			dropdownMenuOptions.push({ anchor: editOption, type: 'edit' });
		}

		const resetOption = a({
			text: __( 'Reset to Defaults', 'formidable' )
		});
		addIconToOption( resetOption, 'frm_reset_icon' );
		onClickPreventDefault( resetOption, () => confirmResetStyle( data.styleId ) );

		dropdownMenuOptions.push(
			{ anchor: resetOption, type: 'reset' },
			{ anchor: getRenameOption( data.styleId ), type: 'rename' }
		);

		const hookName      = 'frm_style_card_dropdown_options';
		const hookArgs      = { data, addIconToOption };
		dropdownMenuOptions = wp.hooks.applyFilters( hookName, dropdownMenuOptions, hookArgs );

		const dropdownMenu  = div({
			// Use dropdown-menu-right to avoid an overlapping issue with the card to the right (where the # of forms would appear above the menu).
			className: 'frm-dropdown-menu dropdown-menu-right frm-style-options-menu frm-p-1',
			children: dropdownMenuOptions.map( wrapDropdownItem )
		});

		dropdownMenu.setAttribute( 'role', 'menu' );

		return div({
			className: 'dropdown frm_wrap', // The .frm_wrap class prevents a blue outline on the active dropdown trigger.
			children: [ hamburgerMenu, dropdownMenu ]
		});
	}

	/**
	 * @param {String} styleId
	 * @returns {HTMLElement}
	 */
	function getRenameOption( styleId ) {
		const renameOption = a( __( 'Rename', 'formidable-pro' ) );
		addIconToOption( renameOption, 'frm_rename_icon' );

		let titleTarget;

		// Depending on the page we're pulling the text from an existing element on the page.
		if ( isListPage ) {
			titleTarget = getCardByStyleId( styleId ).querySelector( '.frm-style-card-title' );
		} else {
			titleTarget = document.getElementById( 'frm_style_name' );
		}

		onClickPreventDefault(
			renameOption,
			() => {
				const styleName = titleTarget.textContent;
				stylerModal(
					'frm_rename_style_modal',
					{
						title: __( 'Rename style', 'formidable-pro' ),
						content: getStyleInputNameModalContent( 'rename', styleName ),
						footer: getRenameStyleModalFooter( styleId )
					}
				);
			}
		);

		return renameOption;
	}

	/**
	 * @param {String} id
	 * @param {Object} args
	 * @returns {HTMLElement}
	 */
	function stylerModal( id, args ) {
		const modal = maybeCreateModal( id, args );
		// Include both wp-core-ui and frm-white-body on the modal.
		// Without wp-core-ui, the vertical alignment of the primary button is wrong.
		// Without frm-white-body, cancel buttons in the modal do not get styled properly.
		modal.classList.add( 'frm_common_modal', 'wp-core-ui', 'frm-white-body' );
		return modal;
	}

	/**
	 * Get modal content with just a "Style Name" input.
	 * This is used for New style, Duplicate style, and for Rename style.
	 *
	 * @param {String} context
	 * @param {String|undefined} value
	 * @returns {HTMLElement}
	 */
	function getStyleInputNameModalContent( context, value ) {
		// Create a form so we can listen to Enter key presses that trigger a form submit event.
		const form = tag(
			'form',
			{
				child: labelledTextInput( 'frm_' + context + '_style_name_input', __( 'Style name', 'formidable-pro' ), 'style_name' )
			}
		);
		form.addEventListener(
			'submit',
			/**
			 * @param {Event} event
			 * @returns {false}
			 */
			event => {
				// Prevent the form in the modal from submitting and trigger the click button in the modal footer instead.
				event.preventDefault();

				const modal = form.closest( '.frm-dialog' );
				modal.querySelector( '.frm_modal_footer .frm-button-primary' ).click();

				return false;
			}
		);
		const content = div({ child: form });
		content.style.padding = '20px';
		content.querySelector( 'label' ).style.lineHeight = 1.5;

		const styleNameInput = content.querySelector( 'input' );
		styleNameInput.addEventListener(
			'input',
			() => {
				const footerSubmitButton = styleNameInput.closest( '.frm_modal_content' ).nextElementSibling.querySelector( '.frm-button-primary' );
				if ( '' === styleNameInput.value ) {
					footerSubmitButton.setAttribute( 'disabled', 'disabled' );
					footerSubmitButton.classList.remove( 'dismiss' );
				} else {
					footerSubmitButton.removeAttribute( 'disabled' );
					footerSubmitButton.classList.add( 'dismiss' );
				}
			}
		);

		if ( 'string' === typeof value ) {
			styleNameInput.value = value;
		}

		return content;
	}

	/**
	 * @param {String} styleId
	 * @returns {HTMLELement}
	 */
	 function getRenameStyleModalFooter( styleId ) {
		const cancelButton = footerButton({ text: __( 'Cancel', 'formidable-pro' ), buttonType: 'cancel' });
		cancelButton.classList.add( 'dismiss' );

		const renameButton = footerButton({ text: __( 'Rename style', 'formidable-pro' ), buttonType: 'primary' });
		onClickPreventDefault( renameButton, () => renameStyle( styleId ) );

		return div({
			children: [ cancelButton, renameButton ]
		});
	}

	/**
	 * Call frm_rename_style action when the rename style button is clicked in rename modal.
	 *
	 * @param {String} styleId
	 * @returns {void}
	 */
	function renameStyle( styleId ) {
		const styleNameInput = document.getElementById( 'frm_rename_style_name_input' );
		const newStyleName   = styleNameInput.value;

		if ( '' === newStyleName ) {
			// Avoid setting an empty name.
			// The button gets disabled on an input event when the name is empty.
			return;
		}

		const formData  = new FormData();
		formData.append( 'style_id', styleId );
		formData.append( 'style_name', newStyleName );
		doJsonPost( 'rename_style', formData ).then(
			/**
			 * Sync the page with the new name of renamed style after successfully making a POST request.
			 *
			 * If on the list page, update the style card after renaming a style.
			 * On the edit page, update the style name element instead.
			 *
			 * @returns {void}
			 */
			() => {
				success( __( 'Style has been renamed successfully', 'formidable' ) );

				if ( isListPage ) {
					updateStyleNameInCard( styleId, newStyleName );
					return;
				}

				const titleSpan = document.getElementById( 'frm_style_name' );
				titleSpan.textContent = newStyleName;
			}
		);
	}

	/**
	 * @param {String} styleId
	 * @param {String} newStyleName
	 * @returns {void}
	 */
	function updateStyleNameInCard( styleId, newStyleName ) {
		const card         = getCardByStyleId( styleId );
		const titleElement = card.querySelector( '.frm-style-card-title' );
		titleElement.textContent = newStyleName;
	}

	/**
	 * @param {String} styleId
	 * @returns {HTMLElement}
	 */
	function getCardByStyleId( styleId ) {
		const defaultCard = document.querySelector( '#frm_default_style_cards_wrapper > div[data-style-id="' + styleId + '"]' );
		if ( defaultCard ) {
			return defaultCard;
		}

		return Array.from( document.getElementById( 'frm_custom_style_cards_wrapper' ).children ).find( card => card.dataset.styleId === styleId );
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
		stylerModal(
			'frm_reset_style_modal',
			{
				title: __( 'Reset style', 'formidable' ),
				content: getResetStyleModalContent(),
				footer: getResetStyleModalFooter( styleId )
			}
		);
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
	 * Handle reset dropdown action.
	 * This function handles the front end routing for the reset action as reset works differently for edit and list views.
	 *
	 * @param {String} styleId
	 * @returns {void}
	 */
	function resetStyle( styleId ) {
		if ( isListPage ) {
			resetStyleOnListPage( styleId );
			return;
		}
		resetStyleOnEditPage();
	}

	/**
	 * Make a POST request to reset the style then reload the CSS and reset the card styles.
	 *
	 * @param {String} styleId
	 * @returns {void}
	 */
	function resetStyleOnListPage( styleId ) {
		const formData = new FormData();
		formData.append( 'style_id', styleId );
		doJsonPost( 'settings_reset', formData ).then(
			response => {
				if ( 'string' === typeof response.style ) {
					const card = getCardByStyleId( styleId );
					card.style = response.style;
				}
				reloadCSSAfterStyleReset();
				showStyleResetSuccessMessage();
			}
		);
	}

	function showStyleResetSuccessMessage() {
		success( __( 'Style has been reset successfully', 'formidable' ) );
	}

	/**
	 * Reset the style in-page (without actually updating it).
	 *
	 * @returns {void}
	 */
	function resetStyleOnEditPage() {
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_settings_reset',
				nonce: frmGlobal.nonce
			},
			success: syncEditPageAfterResetAction
		});
	}

	/**
	 * Handle reset success on edit page.
	 * This function sets all styling inputs to default values.
	 *
	 * @todo Stop triggering change events with jQuery. And remove the other jQuery as well.
	 *
	 * @param {Object} response
	 * @returns {void}
	 */
	function syncEditPageAfterResetAction( response ) {
		let defaultValues = response.replace( /^\s+|\s+$/g, '' );
		if ( defaultValues.indexOf( '{' ) === 0 ) {
			defaultValues = JSON.parse( defaultValues );
		}

		for ( const key in defaultValues ) {
			let targetInput = document.querySelector( 'input[name$="[' + key + ']"], select[name$="[' + key + ']"]' );
			if ( ! targetInput ) {
				continue;
			}

			if ( 'radio' === targetInput.getAttribute( 'type' ) ) {
				// Reset the repeater icon dropdown.
				targetInput = document.querySelector( 'input[name$="[' + key + ']"][value="' + defaultValues[ key ] + '"]' );
				if ( targetInput ) {
					targetInput.checked = true;
					jQuery( targetInput ).trigger( 'change' );
				}
				continue;
			}

			targetInput.value = defaultValues[ key ];

			if ( targetInput.classList.contains( 'wp-color-picker' ) ) {
				// Trigger a change event so the color pickers sync. Otherwise they stay the same color after reset.
				jQuery( targetInput ).trigger( 'change' );
			}
		}

		jQuery( '#frm_submit_style, #frm_auto_width' ).prop( 'checked', false );
		jQuery( document.getElementById( 'frm_fieldset' ) ).trigger( 'change' );
		showStyleResetSuccessMessage();
	}

	/**
	 * Reload Formidable CSS after a style is reset so the preview updates immediately without needing to reload the page.
	 *
	 * @returns {void}
	 */
	function reloadCSSAfterStyleReset() {
		const style = document.getElementById( 'frm-custom-theme-css' );
		if ( ! style ) {
			return;
		}

		const newStyle = document.createElement( 'link' );
		newStyle.rel   = 'stylesheet';
		newStyle.type  = 'text/css';
		newStyle.href  = style.href + '&key=' + getAutoId(); // Make the URL unique so the old stylesheet doesn't get picked up by cache.

		// Listen for the new style to load before removing the old style to avoid having no styles while the new style is loading.
		newStyle.addEventListener(
			'load',
			() => {
				style.parentNode.removeChild( style );
				newStyle.id = 'frm-custom-theme-css'; // Assign the old ID to the new style so it can be removed in the next reset action.
			}
		);

		const head = document.getElementsByTagName( 'HEAD' )[0];
		head.appendChild( newStyle );
	}

	/**
	 * @returns {Number}
	 */
	function getAutoId() {
		return ++state.autoId;
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
	function initEditPage() {
		const { debounce }           = frmDom.util;
		const debouncedPreviewUpdate = debounce( () => changeStyling(), 100 );

		initPosClass(); // It's important that this gets called before we add event listeners because it triggers change events.

		document.getElementById( 'frm_field_height' ).addEventListener( 'change', textSquishCheck );
		document.getElementById( 'frm_field_font_size' ).addEventListener( 'change', textSquishCheck );
		document.getElementById( 'frm_field_pad' ).addEventListener( 'change', textSquishCheck );

		jQuery( 'input.hex' ).wpColorPicker({
			change: function( event ) {
				trackUnsavedChange();

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

		addHamburgerMenuForEditPage();

		document.getElementById( 'frm_styling_form' ).querySelectorAll( 'input, select' ).forEach(
			input => input.addEventListener( 'change', () => trackUnsavedChange() )
		);

		/**
		 * Sends an AJAX POST request for new CSS to use for the preview.
		 * This is called whenever a style setting is changed, generally using debouncedPreviewUpdate to avoid simultaneous requests.
		 *
		 * @returns {void}
		 */
		function changeStyling() {
			const styleInputs = Array.from( document.getElementById( 'frm_style_sidebar' ).querySelectorAll( 'input, select, textarea' ) ).filter(
				input => 'style_name' === input.name || 0 === input.name.indexOf( 'frm_style_setting[post_content]' )
			);
			const locStr      = JSON.stringify( jQuery( styleInputs ).serializeArray() );

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'frm_change_styling',
					nonce: frmGlobal.nonce,
					frm_style_setting: locStr
				},
				success: css => document.getElementById( 'this_css' ).innerHTML = css
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
			trackUnsavedChange();

			const radio  = this;
			const btnGrp = radio.closest( '.btn-group' );
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

			radio.closest( '.dropdown-item' ).classList.add( 'active' );
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
			const input                 = container.querySelector( ':scope > input, :scope > select, :scope > textarea' ); // Fields that support floating label should have a directly child input/textarea/select.
			const shouldForceTopStyling = 'inside' === value && ( ! input || 'hidden' === input.type ); // We do not want file upload to use floating labels, or inline datepickers, which both use hidden inputs.
			const currentValue          = shouldForceTopStyling ? 'top' : value;

			container.classList.remove( 'frm_top_container', 'frm_left_container', 'frm_right_container', 'frm_none_container', 'frm_inside_container' );
			container.classList.add( 'frm_' + currentValue + '_container' );

			if ( 'inside' === currentValue ) {
				checkFloatingLabelsForStyles( input, container );
			}
		});
	}

	/**
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
	 * The signature add on expects that validateFormSubmit is callable.
	 * Without this, drawing in a signature field triggers a "Uncaught ReferenceError: frmFrontForm is not defined" error.
	 * We don't want the validation to actually triggr, so just fill in an empty function.
	 *
	 * @returns {void}
	 */
	function fillMissingSignatureValidationFunction() {
		if ( 'undefined' === typeof window.__FRMSIG || 'undefined' !== typeof window.frmFrontForm ) {
			return;
		}

		window.frmFrontForm = { validateFormSubmit: () => {} };
	}

	/**
	 * Does the same as jQuery( document ).on( 'event', 'selector', handler ).
	 *
	 * @since 5.4.2
	 *
	 * @param {String}         event    Event name.
	 * @param {String}         selector Selector.
	 * @param {Function}       handler  Handler.
	 * @param {boolean|Object} options  Options to be added to `addEventListener()` method. Default is `false`.
	 * @returns {void}
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

	// Hook into the styleInit function in formidable_admin.js
	wp.hooks.addAction( 'frm_style_editor_init', 'formidable', initEditPage );

	// Set a global object so these functions can be re-used in Pro.
	window.frmStylerFunctions = { getCardByStyleId, getStyleInputNameModalContent, trackUnsavedChange, stylerModal };
}() );
