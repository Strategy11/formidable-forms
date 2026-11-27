/* exported frm_add_logic_row, frm_remove_tag, frm_show_div, frmCheckAll, frmCheckAllLevel */

/**
 * Internal dependencies
 */
const { checkActiveAction, isAtLimitForActionType } = require( './formActions' );
const { validateField } = require( './settings/validateField' );
const { getRangeSettingsDefaults, validateNumberRangeSetting, validateStepSetting, validateRangeSettings } = require( './settings/validateRangeSettings' );
const { initShowBoxIconSwap } = require( './showBoxIconSwap' );
const { hydrateBuilderSelect, hydrateBuilderSelectsIn } = require( './sharedSelectOptions' );
const { renderDropdownPreview } = require( './fieldPreview' );

// Fields that are still waiting for their preview, either over ajax or from page data.
const { UNRENDERED_FIELD_SELECTOR } = require( './fieldPreviewSelectors' );

require( './license' );

window.frmAdminBuildJS = function() {
	const frmAdminJs = frm_admin_js;
	const builderScreen = wp.hooks.applyFilters( 'frmAdminBuilder', null );
	const { div, span } = frmDom;
	const { onClickPreventDefault } = frmDom.util;
	const { doJsonPost } = frmDom.ajax;

	const fieldsContainer = document.getElementById( 'frm-show-fields' );
	const $newFields = jQuery( fieldsContainer );
	const builderForm = document.getElementById( 'new_fields' );
	const adminState = {
		fieldsUpdated: 0,
		thisFormId: 0,
		activeSettingsPanel: null,
	};
	const thisForm = document.getElementById( 'form_id' );

	const { __ } = wp.i18n;

	if ( thisForm ) {
		adminState.thisFormId = thisForm.value;
	}

	const currentURL = new URL( window.location.href );
	const urlParams = currentURL.searchParams;
	const builderPage = document.getElementById( 'frm_builder_page' );

	const {
		getClassForBlock,
		getEvenClassForSize,
		getFieldsInRow,
		getLayoutClasses,
		syncLayoutClasses,
		updateFieldGroupControls,
	} = require( './fieldLayout' )( { UNRENDERED_FIELD_SELECTOR, adminState, builderPage, ensureFieldSettings, makeTabbable, moveFieldSettings } );
	const {
		getFieldList,
		popAllProductFields,
		popCalcFields,
		popProductFields,
	} = require( './calculationFields' )( { ensureFieldSettings } );
	const {
		adjustConditionalLogicOptionOrders,
		fillDropdownOpts,
		getImageLabel,
		getMultipleOpts,
		imagesAsOptions,
		purifyHtml,
		showingLabelWithImage,
		usingSeparateValues,
	} = require( './fieldChoices' )( { builderPage, ensureFieldSettings, frmAdminJs, getChoiceNewValueAndLabel, isProductField, builderScreen, prepareLoadedFieldMarkup, renderDropdownPreview } );
	const {
		initModal,
		initUpgradeModal,
		populateUpgradeTab,
		showBasicModal,
		showInputIcon,
	} = require( './upgradeDialogs' )( { maybeAddFieldSelection } );
	const {
		checkContextualShortcode,
		getContextualShortcodes,
		getInputForIcon,
		getSVGHref,
		hideShortcodes,
		insertCode,
		insertFieldCode,
		maybeHideShortcodes,
		maybeShowModal,
		resetLogicBuilder,
		setLogicExample,
		showShortcodes,
		updateShortcodesPopupPosition,
	} = require( './shortcodes' )( { builderPage, frmAdminJs, triggerChange, triggerEvent } );
	const searchContent = require( './search' )( checkContextualShortcode );

	frmAdminJs.contextualShortcodes = getContextualShortcodes();

	function showElement( element ) {
		if ( ! element[ 0 ] ) {
			return;
		}
		element[ 0 ].style.display = '';
	}

	function confirmClick( e ) {
		/*jshint validthis:true */
		e.stopPropagation();
		e.preventDefault();
		confirmLinkClick( this );
	}

	function confirmLinkClick( link ) {
		const message = link.getAttribute( 'data-frmverify' );
		const loadedFrom = link.getAttribute( 'data-loaded-from' );

		if ( message === null || link.id === 'frm-confirmed-click' ) {
			return true;
		}

		if ( 'entries-list' === loadedFrom ) {
			return wp.hooks.applyFilters( 'frm_on_multiple_entries_delete', { link, initModal } );
		}

		return confirmModal( link );
	}

	function confirmModal( link ) {
		const $info = initModal( '#frm_confirm_modal', '400px' );
		const continueButton = document.getElementById( 'frm-confirmed-click' );

		if ( $info === false ) {
			return false;
		}

		// The confirm button is hidden when showLimitModal is called,
		// so make sure it is visible every time we open the modal.
		if ( continueButton ) {
			continueButton.style.display = 'block';
		}

		const verify = link.getAttribute( 'data-frmverify' );
		const btnClass = verify ? link.getAttribute( 'data-frmverify-btn' ) : '';
		const $confirmMessage = jQuery( '.frm-confirm-msg' );
		$confirmMessage.empty();

		if ( verify ) {
			$confirmMessage.append( document.createTextNode( verify ) );
			if ( btnClass ) {
				continueButton?.classList.add( btnClass );
			}
		}

		const dataAtts = link.dataset;

		if ( continueButton ) {
			for ( const i in continueButton.dataset ) {
				continueButton.removeAttribute( `data-${ i }` );
			}

			for ( const i in dataAtts ) {
				if ( i !== 'frmverify' ) {
					continueButton.setAttribute( `data-${ i }`, dataAtts[ i ] );
				}
			}
		}

		/**
		 * Triggers the pre-open action for a confirmation modal. This action passes
		 * relevant modal information and associated link to any listening hooks.
		 *
		 * @param {Object}      options       An object containing modal elements and data.
		 * @param {HTMLElement} options.$info The HTML element containing modal information.
		 * @param {string}      options.link  The link associated with the modal action.
		 */
		wp.hooks.doAction( 'frmAdmin.beforeOpenConfirmModal', { $info, link } );

		$info.dialog( 'open' );
		continueButton?.setAttribute( 'href', link.getAttribute( 'href' ) || link.getAttribute( 'data-href' ) );
		return false;
	}

	/**
	 * Initializes an info modal.
	 *
	 * @since 6.31 The first param can be an array of args.
	 *
	 * @param {Array|string}     msg   The message or the modal data (title, msg, actionUrl, actionText, closeText).
	 * @param {string|undefined} width The width (include the unit) of the modal. This is optional. Default is `400px`.
	 */
	function infoModal( msg, width ) {
		const $info = initModal( '#frm_info_modal', width || '400px' );

		if ( $info === false ) {
			return false;
		}

		if ( 'string' === typeof msg ) {
			msg = { msg };
		}

		msg = Object.assign( {
			title: '',
			msg: __( 'Are you sure?', 'formidable' ),
			img: '',
			closeText: __( 'Got it!', 'formidable' ),
			actionUrl: '',
			actionText: '',
			noCenter: false,
		}, msg );

		const titleEl = $info[ 0 ].querySelector( '.info-modal-title' );
		titleEl.textContent = msg.title || '';
		titleEl.classList.toggle( 'frm_hidden', ! msg.title );

		if ( msg.msg ) {
			$info[ 0 ].querySelector( '.frm-info-msg' ).innerHTML = purifyHtml( msg.msg );
		}

		$info[ 0 ].querySelector( '.info-modal-img' ).src = msg.img;
		$info[ 0 ].querySelector( '.info-modal-img-wrapper' ).classList.toggle( 'frm_hidden', ! msg.img );

		const closeBtn = document.getElementById( 'frm-info-click' );
		if ( msg.closeText ) {
			closeBtn.textContent = msg.closeText;
		}

		// Change the close button to primary or secondary.
		closeBtn.classList.toggle( 'button-primary', ! msg.actionUrl );
		closeBtn.classList.toggle( 'frm-button-primary', ! msg.actionUrl );
		closeBtn.classList.toggle( 'button-secondary', Boolean( msg.actionUrl ) );
		closeBtn.classList.toggle( 'frm-button-secondary', Boolean( msg.actionUrl ) );

		const actionBtn = $info[ 0 ].querySelector( '.info-modal-action-link' );

		if ( msg.actionUrl ) {
			actionBtn.href = msg.actionUrl;
			if ( msg.actionText ) {
				actionBtn.textContent = msg.actionText;
			}
		}

		// Show or hide the action btn.
		actionBtn.classList.toggle( 'frm_hidden', ! msg.actionUrl );

		// Handle alignment.
		$info[ 0 ].querySelector( '.info-modal-inside' ).classList.toggle( 'frmcenter', ! msg.noCenter );
		const buttonsWrapper = $info[ 0 ].querySelector( '.info-modal-buttons' );
		buttonsWrapper.classList.toggle( 'frmright', msg.noCenter );

		$info.dialog( 'open' );
		return false;
	}

	function toggleItem( e ) {
		/*jshint validthis:true */
		const toggle = this.getAttribute( 'data-frmtoggle' );
		const text = this.getAttribute( 'data-toggletext' );
		const $items = jQuery( toggle );

		e.preventDefault();

		$items.toggle();

		if ( text !== null && text !== '' ) {
			this.setAttribute( 'data-toggletext', this.innerHTML );
			this.textContent = text;
		}

		return false;
	}

	/**
	 * Toggle a class on target elements when an anchor is clicked, or when a radio or checkbox has been selected.
	 *
	 * @param {Event} e Event with either the change or click type.
	 * @return {false} Always returns false to prevent default behavior.
	 */
	function hideShowItem( e ) {
		/*jshint validthis:true */
		let hide = this.getAttribute( 'data-frmhide' );
		let show = this.getAttribute( 'data-frmshow' );
		const uncheckList = this.getAttribute( 'data-frmuncheck' );
		const uncheckListArray = uncheckList ? uncheckList.split( ',' ) : [];

		// Flip unchecked checkboxes so an off value undoes the on value.
		if ( isUncheckedCheckbox( this ) ) {
			if ( hide !== null ) {
				show = hide;
				hide = null;
			} else if ( show !== null ) {
				hide = show;
				show = null;
			}
		}

		e.preventDefault();

		const toggleClass = this.getAttribute( 'data-toggleclass' ) || 'frm_hidden';

		if ( hide !== null ) {
			jQuery( hide ).addClass( toggleClass );
		}

		if ( show !== null ) {
			jQuery( show ).removeClass( toggleClass );
		}

		const current = this.parentNode.querySelectorAll( 'a.current' );
		if ( current !== null ) {
			for ( let i = 0; i < current.length; i++ ) {
				current[ i ].classList.remove( 'current' );
			}
			this.classList.add( 'current' );
		}

		if ( uncheckListArray.length ) {
			uncheckListArray.forEach( function( uncheckItem ) {
				const uncheckItemElement = document.querySelector( uncheckItem );
				if ( uncheckItemElement ) {
					uncheckItemElement.checked = false;
				}
			} );
		}

		return false;
	}

	function isUncheckedCheckbox( element ) {
		return 'INPUT' === element.nodeName && 'checkbox' === element.type && ! element.checked;
	}

	/**
	 * Load a tooltip for a single element.
	 *
	 * @since 6.26
	 *
	 * @param {HTMLElement} element
	 * @param {boolean}     show
	 */
	function loadTooltip( element, show = false ) {
		let tooltipTarget = element;

		resolveDeferredTooltip( tooltipTarget );

		// Bootstrap 5 does not allow tooltips on dropdown triggers, so move the tooltip to the parent element.
		if ( tooltipTarget.hasAttribute( 'data-toggle' ) || tooltipTarget.hasAttribute( 'data-bs-toggle' ) ) {
			tooltipTarget.parentElement.setAttribute( 'title', tooltipTarget.getAttribute( 'title' ) );
			tooltipTarget.removeAttribute( 'title' );
			tooltipTarget.classList.remove( 'frm_bstooltip' );
			tooltipTarget.parentElement.classList.add( 'frm_bstooltip' );
			tooltipTarget = tooltipTarget.parentElement;
		}

		const tooltip = new bootstrap.Tooltip( tooltipTarget );

		if ( show ) {
			deleteTooltips();
			tooltip.show();
		}
	}

	/**
	 * Resolves a `data-tip-key` (set by `FrmAppHelper::get_tooltip_attr()` on the form builder
	 * page) into the element's real `title` attribute, looked up from `frm_admin_js.tooltips`.
	 * No-op for an element that already carries its own `title` (every other admin page).
	 *
	 * @param {HTMLElement} element
	 * @return {void}
	 */
	function resolveDeferredTooltip( element ) {
		if ( ! element.hasAttribute( 'data-tip-key' ) ) {
			return;
		}

		const key = element.getAttribute( 'data-tip-key' );
		element.removeAttribute( 'data-tip-key' );

		const text = window.frm_admin_js && frm_admin_js.tooltips && frm_admin_js.tooltips[ key ];
		if ( text ) {
			element.setAttribute( 'title', text );
		}
	}

	/**
	 * Adds keyboard support for form action widgets and their icons.
	 *
	 * @since 6.31
	 *
	 * @param {jQuery} wrapClass Delegated jQuery scope for the current page wrap.
	 * @return {void}
	 */
	function bindFormActionsKeyboardHandlers( wrapClass ) {
		// Expand/collapse the widget on Enter or Space when the header itself is focused.
		wrapClass.on( 'keydown', '.widget-top', event => {
			// Ignore keys bubbling up from focusable descendants (toggle, delete, duplicate, arrow button).
			if ( event.currentTarget !== event.target ) {
				return;
			}

			if ( event.key === 'Enter' || event.key === ' ' ) {
				event.preventDefault();
				clickWidget( event, event.currentTarget );
			}
		} );

		/**
		 * Builds a delegated keydown handler that forwards a specific key to a native click.
		 *
		 * @param {string} key The `KeyboardEvent.key` value that should activate the target.
		 * @return {Function} jQuery event handler.
		 */
		const activateOnKey = key => event => {
			if ( event.key !== key ) {
				return;
			}

			event.preventDefault();
			event.currentTarget.click();
		};

		// `<a>` icons activate on Enter natively, so only Space needs to be wired up.
		wrapClass.on( 'keydown', '.frm_form_action_settings .frm_duplicate_form_action, .frm_form_action_settings .frm_remove_form_action', activateOnKey( ' ' ) );

		// The post-status toggle's label already handles Space, so only Enter needs to be wired up.
		wrapClass.on( 'keydown', '.frm_form_action_settings .frm_toggle', activateOnKey( 'Enter' ) );
	}

	function loadTooltips() {
		const wrapClass = jQuery( '.wrap, .frm_wrap' );
		const confirmModal = document.getElementById( 'frm_confirm_modal' );
		let doAction = false;
		let confirmedBulkDelete = false;

		jQuery( confirmModal ).on( 'click', '[data-deletefield]', deleteFieldConfirmed );
		jQuery( confirmModal ).on( 'click', '[data-removeid]', removeThisTag );
		jQuery( confirmModal ).on( 'click', '[data-trashtemplate]', trashTemplate );

		wrapClass.on( 'click', '.frm_remove_tag, .frm_remove_form_action', removeThisTag );
		wrapClass.on( 'click', 'a[data-frmverify]', confirmClick );
		wrapClass.on( 'click', 'a[data-frmtoggle]', toggleItem );
		wrapClass.on( 'click', 'a[data-frmhide], a[data-frmshow]', hideShowItem );
		wrapClass.on( 'change', 'input[data-frmhide], input[data-frmshow]', hideShowItem );
		wrapClass.on( 'click', '.widget-top,a.widget-action', clickWidget );
		bindFormActionsKeyboardHandlers( wrapClass );

		// Resolve every tooltip trigger already in the DOM now, so an SVG-only icon carries a
		// real accessible name from page-ready instead of only from the first mouse hover.
		wrapClass.find( '[data-tip-key]' ).each( function() {
			resolveDeferredTooltip( this );
		} );

		wrapClass.on( 'mouseenter.frm', '.frm_bstooltip, .frm_help', function() {
			jQuery( this ).off( 'mouseenter.frm' );
			loadTooltip( this, true );
		} );

		jQuery( document ).on( 'click', '#doaction, #doaction2', function( event ) {
			const isTop = this.id === 'doaction';
			const suffix = isTop ? 'top' : 'bottom';
			const bulkActionSelector = document.getElementById( `bulk-action-selector-${ suffix }` );
			const confirmBulkDelete = document.getElementById( `confirm-bulk-delete-${ suffix }` );

			if ( bulkActionSelector && confirmBulkDelete ) {
				doAction = this;

				if ( ! confirmedBulkDelete && bulkActionSelector.value === 'bulk_delete' ) {
					event.preventDefault();
					confirmLinkClick( confirmBulkDelete );
					return false;
				}
			} else {
				doAction = false;
			}
		} );

		jQuery( document ).on( 'click', '#frm-confirmed-click', function( event ) {
			if ( doAction === false || event.target.classList.contains( 'frm-btn-inactive' ) ) {
				return;
			}

			if ( this.getAttribute( 'href' ) === 'confirm-bulk-delete' ) {
				event.preventDefault();
				confirmedBulkDelete = true;
				doAction.click();
				return false;
			}
		} );
	}

	function deleteTooltips() {
		document.querySelectorAll( '.tooltip' ).forEach(
			function( tooltip ) {
				tooltip.remove();
			}
		);
	}

	/**
	 * Uncheck any data-toggleclass toggle that controls the given hidden element.
	 *
	 * @since 6.31
	 *
	 * @param {string} hideSelector CSS selector for the element being removed.
	 * @return {void}
	 */
	function uncheckToggleOnElementRemoval( hideSelector ) {
		const hiddenEl = document.querySelector( hideSelector );
		if ( ! hiddenEl ) {
			return;
		}

		for ( const cls of hiddenEl.classList ) {
			const toggle = document.querySelector( `input[data-toggleclass="${ cls }"]` );
			if ( toggle?.checked ) {
				toggle.checked = false;
				return;
			}
		}
	}

	function removeThisTag() {
		/*jshint validthis:true */
		let show;
		let hide;
		let removeMore;

		if ( parseInt( this.getAttribute( 'data-skip-frm-js' ) ) || confirmLinkClick( this ) === false ) {
			return;
		}

		const deleteButton = jQuery( this );
		const id = deleteButton.attr( 'data-removeid' );

		show = deleteButton.attr( 'data-showlast' );
		if ( show === undefined ) {
			show = '';
		}

		hide = deleteButton.attr( 'data-hidelast' );
		if ( hide === undefined ) {
			hide = '';
		}

		removeMore = deleteButton.attr( 'data-removemore' );

		if ( show !== '' ) {
			if ( deleteButton.closest( '.frm_add_remove' ).find( '.frm_remove_tag:visible' ).length > 1 ) {
				show = '';
				hide = '';
			}
		} else if ( id.indexOf( 'frm_postmeta_' ) === 0 ) {
			if ( jQuery( '#frm_postmeta_rows .frm_postmeta_row' ).length < 2 ) {
				show = '.frm_add_postmeta_row.button';
			}
			if ( jQuery( '.frm_toggle_cf_opts' ).length && jQuery( `#frm_postmeta_rows .frm_postmeta_row:not(#${ id })` ).last().length ) {
				if ( show !== '' ) {
					show += ',';
				}
				show += `#${ jQuery( `#frm_postmeta_rows .frm_postmeta_row:not(#${ id })` ).last().attr( 'id' ) } .frm_toggle_cf_opts`;
			}
		}

		const fadeEle = document.getElementById( id );
		const $fadeEle = jQuery( fadeEle );
		const logicRowsContainer = fadeEle?.classList.contains( 'frm_logic_row' ) ? fadeEle.closest( '[id^="frm_logic_row_"]' ) : null;
		$fadeEle.fadeOut( 300, function() {
			$fadeEle.remove();
			fieldUpdated();

			if ( logicRowsContainer ) {
				triggerLogicRowEvent( 'frm_logic_row_removed', fadeEle, logicRowsContainer, deleteButton.get( 0 ) );
			}

			if ( hide !== '' ) {
				jQuery( hide ).hide();
				uncheckToggleOnElementRemoval( hide );
			}

			if ( show !== '' ) {
				jQuery( `${ show } a,${ show }` ).removeClass( 'frm_hidden' ).fadeIn( 'slow' );
			}

			if ( this.closest( '.frm_form_action_settings' ) ) {
				const type = this.closest( '.frm_form_action_settings' ).querySelector( '.frm_action_name' ).value;
				afterActionRemoved( type );
			}
			document.querySelector( '.tooltip' )?.remove();
		} );

		if ( removeMore !== undefined ) {
			removeMore = jQuery( removeMore );
			removeMore.fadeOut( 400, function() {
				removeMore.remove();
			} );
		}

		if ( show !== '' ) {
			jQuery( this ).closest( '.frm_logic_rows' ).fadeOut( 'slow' );
		}

		/**
		 * Fires after a tag element has been removed in the admin interface.
		 *
		 * @param {string}      id      The ID of the removed element
		 * @param {HTMLElement} fadeEle The removed element that was faded out
		 */
		wp.hooks.doAction( 'frm_admin_tag_removed', id, fadeEle );

		return false;
	}

	function afterActionRemoved( type ) {
		checkActiveAction( type );
		maybeEnableOtherPaymentActions( type );

		if ( ! document.querySelector( '.frm_form_action_settings' ) ) {
			document.querySelector( '.frm-no-actions-message' )?.classList.remove( 'frm_hidden' );
		}

		const hookName = 'frm_after_action_removed';
		const hookArgs = { type };
		wp.hooks.doAction( hookName, hookArgs );
	}

	/**
	 * @since 6.31
	 *
	 * @param {string} deletedType
	 *
	 * @return {void}
	 */
	function maybeEnableOtherPaymentActions( deletedType ) {
		if ( 'payment' !== deletedType ) {
			return;
		}

		[ 'stripe', 'square', 'paypal' ].forEach( action => checkActiveAction( action ) );
	}

	function clickWidget( event, b ) {
		/*jshint validthis:true */
		if ( b === undefined ) {
			b = this;
		}

		popCalcFields( b, false );

		const cont = jQuery( b ).closest( '.frm_form_action_settings' );
		const { target } = event;

		if ( cont.length && target !== undefined ) {
			const { className } = target.parentElement;
			if ( 'string' === typeof className ) {
				if ( className.includes( 'frm_email_icons' ) || className.includes( 'frm_toggle' ) ) {
					// clicking on delete icon shouldn't open it
					event.stopPropagation();
					return;
				}
			}
		}

		let inside = cont.children( '.widget-inside' );

		if ( cont.length && inside.find( 'p, div, table' ).length < 1 ) {
			const actionId = cont.find( 'input[name$="[ID]"]' ).val();
			const actionType = cont.find( 'input[name$="[post_excerpt]"]' ).val();
			if ( actionType ) {
				inside.html( '<span class="frm-wait frm_spinner"></span>' );
				cont.find( '.spinner' ).fadeIn( 'slow' );
				jQuery.ajax( {
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'frm_form_action_fill',
						action_id: actionId,
						action_type: actionType,
						nonce: frmGlobal.nonce
					},
					success( html ) {
						inside.html( html );
						initiateMultiselect();
						showInputIcon( `#${ cont.attr( 'id' ) }` );
						initAutocomplete( inside );
						jQuery( b ).trigger( 'frm-action-loaded' );

						/**
						 * Fires after filling form action content when opening.
						 *
						 * @since 5.5.4
						 *
						 * @param {Object} insideElement JQuery object of form action inside element.
						 */
						wp.hooks.doAction( 'frm_filled_form_action', inside );
					}
				} );
			}
		}

		jQuery( b ).closest( '.frm_field_box' ).siblings().find( '.widget-inside' ).slideUp( 'fast' );
		if ( ( b.className !== undefined && b.className.includes( 'widget-action' ) ) || jQuery( b ).closest( '.start_divider' ).length < 1 ) {
			return;
		}

		inside = jQuery( b ).closest( 'div.widget' ).children( '.widget-inside' );
		if ( inside.is( ':hidden' ) ) {
			inside.slideDown( 'fast' );
		} else {
			inside.slideUp( 'fast' );
		}
	}

	/**
	 * Hides the tab panels in a container, apart from the one the clicked tab points at.
	 * The active panel is matched by its literal id or class name so the tab href is never
	 * turned into a selector.
	 *
	 * @since 6.34
	 * @param {HTMLElement} container Element holding the tab panels as direct children.
	 * @param {string}      targetId  Tab anchor with the leading # removed.
	 * @return {void}
	 */
	function hideOtherTabPanels( container, targetId ) {
		Array.from( container.children ).forEach( child => {
			if ( child.classList.contains( 'tabs-panel' ) && child.id !== targetId && ! child.classList.contains( targetId ) ) {
				child.style.display = 'none';
			}
		} );
	}

	/**
	 * Shows a tab panel.
	 * Panels are also hidden by the frm_hidden class in the markup, so the inline display has to be
	 * set to a visible value. Clearing it would leave that class rule in charge and the panel empty.
	 *
	 * @since 6.34
	 * @param {HTMLElement} panel Tab panel to show.
	 * @return {void}
	 */
	function showTabPanel( panel ) {
		panel.style.display = 'block';
	}

	function clickNewTab() {
		/*jshint validthis:true */
		const href = this.getAttribute( 'href' );
		// A tab anchor always points at an in-page fragment (#anchor). Ignore anything else.
		if ( href === null || href.charAt( 0 ) !== '#' ) {
			return false;
		}

		const targetId = href.slice( 1 );

		const li = this.closest( 'li' );
		if ( li ) {
			li.classList.add( 'frm-tabs', 'active' );
			Array.from( li.parentNode.children ).forEach( sibling => {
				if ( sibling !== li && sibling.tagName === 'LI' ) {
					sibling.classList.remove( 'frm-tabs', 'active', 'starttab' );
				}
			} );
		}

		const container = this.closest( 'div' );
		if ( container ) {
			hideOtherTabPanels( container, targetId );
		}

		const tabContent = document.getElementById( targetId );
		if ( tabContent ) {
			showTabPanel( tabContent );
		}

		// clearSettingsBox would hide field settings when opening the fields modal and we want to skip it there.
		if ( this.id === 'frm_insert_fields_tab' && ! this.closest( '#frm_adv_info' ) ) {
			clearSettingsBox();
		}
		return false;
	}

	function clickTab( link, auto ) {
		link = jQuery( link );
		const href = link.attr( 'href' );
		// A tab anchor always points at an in-page fragment (#anchor). Ignore anything else.
		if ( href === undefined || href.charAt( 0 ) !== '#' ) {
			return;
		}

		const targetId = href.slice( 1 );

		const [ sectionLink ] = link;
		if ( sectionLink ) {
			sectionLink.closest( 'ul' )?.querySelectorAll( 'a[aria-current]' ).forEach( anchor => anchor.removeAttribute( 'aria-current' ) );
			sectionLink.setAttribute( 'aria-current', 'true' );
		}

		link.closest( 'li' ).addClass( 'frm-tabs active' ).siblings( 'li' ).removeClass( 'frm-tabs active starttab' );
		const [ container ] = link.closest( 'div' );
		if ( container?.querySelector( '.tabs-panel' ) ) {
			hideOtherTabPanels( container, targetId );
		} else if ( document.getElementById( 'form_global_settings' ) !== null ) {
			/* global settings */
			const ajax = link.data( 'frmajax' );
			link.closest( '.frm_wrap' ).find( '.tabs-panel, .hide_with_tabs' ).hide();
			if ( ajax !== undefined && ajax == '1' ) {
				loadSettingsTab( href );
			}
		} else {
			/* form settings page */
			jQuery( '#frm-categorydiv .tabs-panel, .hide_with_tabs' ).hide();
		}

		const targetEl = document.getElementById( targetId );
		if ( targetEl ) {
			showTabPanel( targetEl );
		}

		// A panel is not always given an id matching its tab anchor, so match on the class too.
		// getElementsByClassName takes a literal class name, never a selector built from href.
		Array.from( document.getElementsByClassName( targetId ) ).forEach( panel => showTabPanel( panel ) );

		hideShortcodes();

		if ( auto !== 'auto' ) {
			// Hide success message on tab change.
			jQuery( '.frm_updated_message' ).hide();
			jQuery( '.frm_warning_style:not(.frm_force_visible_warning)' ).hide();
		}

		if ( jQuery( link ).closest( '#frm_adv_info' ).length ) {
			return;
		}

		if ( jQuery( '.frm_form_settings' ).length ) {
			jQuery( '.frm_form_settings' ).attr( 'action', `?page=formidable&frm_action=settings&id=${ jQuery( '.frm_form_settings input[name="id"]' ).val() }&t=${ targetId }` );
		} else {
			jQuery( '.frm_settings_form' ).attr( 'action', `?page=formidable-settings&t=${ targetId }` );
		}
	}

	/**
	 * Prepare deferred settings before selection or another interaction reads their inputs.
	 *
	 * @since 7.0
	 * @param {string} fieldId Numeric field ID.
	 * @return {HTMLElement|null} The field settings panel, if the field has loaded.
	 */
	function ensureFieldSettings( fieldId ) {
		if ( builderScreen ) {
			return builderScreen.ensureFieldSettings( fieldId, prepareLoadedFieldMarkup );
		}
		return document.getElementById( `frm-single-settings-${ fieldId }` );
	}

	/**
	 * Resolve shared markup attributes for a field preview or its newly inserted settings.
	 *
	 * @since 7.0
	 * @param {HTMLElement} element The inserted preview or settings panel.
	 * @return {void}
	 */
	function prepareLoadedFieldMarkup( element ) {
		element.querySelectorAll( '[data-toggle]' ).forEach( toggle => toggle.setAttribute( 'data-bs-toggle', toggle.getAttribute( 'data-toggle' ) ) );
		element.querySelectorAll( '.frm-dropdown-menu' ).forEach( dropdownMenu => dropdownMenu.classList.add( 'dropdown-menu' ) );
		element.querySelectorAll( '[data-tip-key]' ).forEach( resolveDeferredTooltip );
	}

	function maybeHideQuantityProductFieldOption() {
		let hide = true;
		const opts = document.querySelectorAll( '.frmjs_prod_field_opt_cont' );

		if ( $newFields.find( 'li.edit_field_type_product' ).length > 1 ) {
			hide = false;
		}

		for ( let i = 0; i < opts.length; i++ ) {
			opts[ i ].classList.toggle( 'frm_hidden', hide );
		}
	}

	function clearSettingsBox( preventFieldGroups ) {
		hideActiveFieldSettings();
		adminState.defaultFieldSettings.classList.remove( 'frm_hidden' );
		deselectFields( preventFieldGroups );
	}

	function deselectFields( preventFieldGroups ) {
		fieldsContainer.querySelectorAll( 'li.ui-state-default.selected' ).forEach( field => field.classList.remove( 'selected' ) );
		builderPage.querySelectorAll( '.frm-show-field-settings.selected' ).forEach( field => field.classList.remove( 'selected' ) );
		if ( ! preventFieldGroups ) {
			unselectFieldGroups();
		}
	}

	function addImageToOption( event ) {
		const imagePreview = event.target.closest( '.frm_image_preview_wrapper' );
		if ( ! wp?.media || imagePreview?.dataset.upgrade ) {
			return;
		}

		event.preventDefault();

		wp.media.model.settings.post.id = 0;

		const fileFrame = wp.media.frames.file_frame = wp.media( {
			multiple: false,
			library: {
				type: [ 'image' ]
			}
		} );

		fileFrame.on( 'select', function() {
			const attachment = fileFrame.state().get( 'selection' ).first().toJSON();
			const img = imagePreview.querySelector( 'img' );

			img.setAttribute( 'src', attachment.url );
			img.classList.remove( 'frm_hidden' );
			img.removeAttribute( 'srcset' ); // Prevent the old image from sticking around.

			imagePreview.querySelector( '.frm_image_preview_frame' ).style.display = 'block';
			imagePreview.querySelector( '.frm_image_preview_title' ).textContent = attachment.filename;
			imagePreview.querySelector( '.frm_choose_image_box' ).style.display = 'none';

			const $imagePreview = jQuery( imagePreview );
			$imagePreview.siblings( 'input[name*="[label]"]' ).data( 'frmimgurl', attachment.url );
			$imagePreview.find( 'input.frm_image_id' ).val( attachment.id ).trigger( 'change' );
			wp.media.model.settings.post.id = 0;
		} );

		fileFrame.open();
	}

	function removeImageFromOption( event ) {
		const $this = jQuery( this );
		const previewWrapper = $this.closest( '.frm_image_preview_wrapper' );

		event.preventDefault();
		event.stopPropagation();

		previewWrapper.find( 'img' ).attr( 'src', '' );
		previewWrapper.find( '.frm_image_preview_frame' ).hide();
		previewWrapper.find( '.frm_choose_image_box' ).show();
		previewWrapper.find( 'input.frm_image_id' ).val( 0 ).trigger( 'change' );
	}

	/**
	 * Allow typing on form switcher click without an extra click to search.
	 */
	function focusSearchBox() {
		populateFormSwitcher();

		const searchBox = document.getElementById( 'dropform-search-input' );
		if ( searchBox ) {
			setTimeout( function() {
				searchBox.focus();
			}, 100 );
		}
	}

	/**
	 * Add switcher links after the menu is opened, yielding between batches.
	 *
	 * @since 7.0
	 *
	 * @return {void}
	 */
	function populateFormSwitcher() {
		const menu = document.querySelector( '#frm_bs_dropdown .frm-dropdown-menu' );
		const dataElement = document.getElementById( 'frm-form-switcher-data' );
		if ( ! menu || ! dataElement ) {
			return;
		}

		const forms = JSON.parse( dataElement.textContent );
		dataElement.remove();

		const searchBox = document.getElementById( 'dropform-search-input' );
		const INITIAL_FORM_COUNT = 20;
		const FORM_BATCH_SIZE = 200;
		let index = 0;

		function addBatch( batchSize ) {
			const fragment = document.createDocumentFragment();
			const end = Math.min( index + batchSize, forms.length );
			const searchText = searchBox ? searchBox.value.toLowerCase() : '';

			for ( ; index < end; index++ ) {
				const form = forms[ index ];
				const link = frmDom.a( {
					className: 'frm-justify-between',
					children: [
						form.name,
						frmDom.span( { text: menu.dataset.idLabel.replace( '%d', form.id ) } ),
						frmDom.span( { className: 'frm_hidden', text: form.key } )
					]
				} );
				link.href = form.url;
				link.tabIndex = -1;
				const item = frmDom.tag( 'li', { className: 'frm-dropdown-form', child: link } );
				if ( searchText && ! item.textContent.toLowerCase().includes( searchText ) ) {
					item.classList.add( 'frm_hidden' );
				}
				fragment.append( item );
			}

			menu.append( fragment );
			if ( index < forms.length ) {
				setTimeout( () => addBatch( FORM_BATCH_SIZE ), 0 );
			}
		}

		addBatch( INITIAL_FORM_COUNT );
	}

	/**
	 * Dismiss a warning message and send an AJAX request to update the dismissal state.
	 *
	 * @since 6.3
	 *
	 * @param {Event} event The event object associated with the click on the dismiss icon.
	 */
	function dismissWarningMessage( event ) {
		const { target } = event;

		const warningEl = target.closest( '.frm_warning_style' );
		jQuery( warningEl ).fadeOut( 400, () => warningEl.remove() );

		const { action } = target.dataset;
		const formData = new FormData();
		doJsonPost( action, formData );
	}

	function makeTabbable( element, ariaLabel ) {
		element.setAttribute( 'tabindex', 0 );
		element.setAttribute( 'role', 'button' );
		if ( ariaLabel !== undefined ) {
			element.setAttribute( 'aria-label', ariaLabel );
		}
	}

	function unselectFieldGroups( event ) {
		if ( event !== undefined ) {
			if ( null !== event.originalEvent.target.closest( '#frm-show-fields' ) ) {
				return;
			}
			if ( event.originalEvent.target.classList.contains( 'frm-merge-fields-into-row' ) ) {
				return;
			}
			if ( null !== event.originalEvent.target.closest( '.frm-merge-fields-into-row' ) ) {
				return;
			}
			if ( event.originalEvent.target.classList.contains( 'frm-custom-field-group-layout' ) ) {
				return;
			}
			if ( event.originalEvent.target.classList.contains( 'frm-cancel-custom-field-group-layout' ) ) {
				return;
			}
		}
		jQuery( fieldsContainer.querySelectorAll( '.frm-selected-field-group' ) ).removeClass( 'frm-selected-field-group' );
		jQuery( document ).off( 'click', unselectFieldGroups );
		maybeRemoveMultiselectPopup();
	}

	function maybeRemoveMultiselectPopup() {
		const popup = document.getElementById( 'frm_field_multiselect_popup' );
		if ( popup ) {
			popup.remove();
		}
	}

	function deleteAllSelectedFieldGroups( deleteFieldIds ) {
		deleteFieldIds.forEach(
			function( fieldId ) {
				deleteField( fieldId );
			}
		);
		toggleSectionHolder();
	}

	function deleteFieldConfirmed() {
		/*jshint validthis:true */
		deleteFields( this.getAttribute( 'data-deletefield' ) );
	}

	function deleteFields( fieldId ) {
		deleteAllSelectedFieldGroups( getFieldIdsToDelete( [ String( fieldId ) ] ) );
	}

	/**
	 * Gets all fields removed by a deletion, including section children and related fields.
	 *
	 * @since 7.0
	 *
	 * @param {string[]} fieldIds The selected field IDs.
	 * @return {string[]} The complete list of field IDs to delete.
	 */
	function getFieldIdsToDelete( fieldIds ) {
		const ids = new Set( fieldIds );
		fieldIds.forEach( fieldId => {
			const field = document.getElementById( `frm_field_id_${ fieldId }` );
			if ( field?.classList.contains( 'edit_field_type_divider' ) ) {
				field.querySelectorAll( 'li.frm_field_box[data-fid]' ).forEach( child => {
					ids.add( child.dataset.fid );
				} );
			}
		} );

		return [ ...new Set( wp.hooks.applyFilters( 'frm_delete_field_ids', [ ...ids ] ) ) ];
	}

	/**
	 * Checks if there is only submit field in the form builder.
	 *
	 * @return {boolean} True if only the submit field exists.
	 */
	function hasOnlySubmitField() {
		// If there are at least 2 rows, return false.
		if ( $newFields.get( 0 ).childElementCount > 1 ) {
			return false;
		}

		const childUl = $newFields.get( 0 ).firstElementChild.firstElementChild;

		// Use query instead of children because there might be a div inside this ul.
		const childLi = childUl.querySelectorAll( 'li.frm_field_box' );

		// If there are at least 2 items in the row, return false.
		if ( childLi.length > 1 ) {
			return false;
		}

		return childLi[ 0 ].classList.contains( 'edit_field_type_submit' );
	}

	/**
	 * Moves open modals out of the field options form.
	 *
	 * When a modal is open, it is moved in the DOM and appended to the parent element of the modal trigger input. That
	 * creates a problem since deleting the field also deletes the modal and this function fixes that problem.
	 *
	 * @since 6.22
	 *
	 * @param {Object} settings
	 * @return {void}
	 */
	function moveOpenModalsOutOfFieldOptions( settings ) {
		if ( ! settings.length ) {
			return;
		}

		const openModals = settings[ 0 ].querySelectorAll( '.frm-inline-modal[data-fills]' );
		if ( ! openModals.length ) {
			return;
		}
		openModals.forEach( modal => {
			modal.classList.add( 'frm_hidden' );
			modal.removeAttribute( 'data-fills' );
			modal.closest( 'form' ).append( modal );
		} );
	}

	function deleteField( fieldId ) {
		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_delete_field',
				field_id: fieldId,
				nonce: frmGlobal.nonce
			},
			success() {
				builderScreen?.forgetField( fieldId );
				const $thisField = jQuery( document.getElementById( `frm_field_id_${ fieldId }` ) );
				const settings = jQuery( `#frm-single-settings-${ fieldId }` );

				// Remove settings from sidebar.
				if ( settings.is( ':visible' ) ) {
					document.querySelector( '.frm-settings-panel .frm-tabs-navs ul > li:first-child' )?.click();
					adminState.defaultFieldSettings.classList.remove( 'frm_hidden' );
				}

				moveOpenModalsOutOfFieldOptions( settings );
				if ( adminState.activeSettingsPanel === settings[ 0 ] ) {
					adminState.activeSettingsPanel = null;
				}
				settings.remove();

				$thisField.fadeOut( 'slow', function() {
					const $section = $thisField.closest( '.start_divider' );
					const type = $thisField.data( 'type' );
					const $adjacentFields = $thisField.siblings( 'li.form-field' );
					let $liWrapper;

					if ( ! $adjacentFields.length ) {
						if ( $thisField.is( '.edit_field_type_end_divider' ) ) {
							$adjacentFields.length = $thisField.closest( 'li.form-field' ).siblings();
						} else {
							$liWrapper = $thisField.closest( 'ul.frm_sorting' ).parent();
						}
					}

					$thisField.remove();
					if ( type === 'break' ) {
						renumberPageBreaks();
					} else if ( type === 'product' ) {
						maybeHideQuantityProductFieldOption();
						// a product field attached to a quantity field earlier might be the one deleted, so re-populate
						popAllProductFields();
					}

					if ( $adjacentFields.length ) {
						syncLayoutClasses( $adjacentFields.first() );
					} else {
						$liWrapper.remove();
					}

					if ( jQuery( '#frm-show-fields li' ).length === 0 || hasOnlySubmitField() ) {
						adminState.formEditorContainer.classList.remove( 'frm-has-fields' );
						adminState.formEditorContainer.classList.add( 'frm-empty-fields' );
					} else if ( $section.length ) {
						toggleOneSectionHolder( $section );
					}

					// prevent "More Options" tooltips from staying around after their target field is deleted.
					deleteTooltips();
				} );

				if ( $thisField.length ) {
					maybeEnableFieldButtonAtLimit( $thisField.data( 'type' ) );
					wp.hooks.doAction( 'frm_after_delete_field', $thisField[ 0 ] );
				}
			}
		} );
	}

	/**
	 * @since 6.27
	 *
	 * @param {string} type
	 * @return {void}
	 */
	function maybeEnableFieldButtonAtLimit( type ) {
		const button = document.getElementById( type );

		if ( ! button || ! button.dataset.limit ) {
			return;
		}

		// Subtract one because the field has not really been removed from the page yet.
		const fieldTypeCount = countFieldTypeInForm( type ) - 1;
		if ( fieldTypeCount < button.dataset.limit ) {
			button.classList.remove( 'frm_at_limit' );
		}
	}

	function countFieldTypeInForm( type ) {
		return fieldsContainer.querySelectorAll( `li.form-field[data-ftype="${ type }"]` ).length;
	}

	/**
	 * Tell other scripts that a conditional logic row was added or removed.
	 *
	 * The event is dispatched on the document. Its frmData (also in detail) holds the row, the
	 * rows container (#frm_logic_row_{id}), the clicked element, the logic type ("field" or
	 * "action") and the ID of the field or form action the logic belongs to.
	 *
	 * @since 7.0
	 *
	 * @param {string}           eventName          "frm_logic_row_added" or "frm_logic_row_removed".
	 * @param {HTMLElement}      row                The condition row. It is detached from the page once removed.
	 * @param {HTMLElement}      logicRowsContainer The element that holds the condition rows.
	 * @param {HTMLElement|null} trigger            The element that was clicked to add or remove the row.
	 * @return {void}
	 */
	function triggerLogicRowEvent( eventName, row, logicRowsContainer, trigger ) {
		const frmData = {
			row,
			container: logicRowsContainer,
			trigger: trigger || null,
			type: logicRowsContainer.closest( '.frm_form_action_settings' ) ? 'action' : 'field',
			id: logicRowsContainer.id.replace( 'frm_logic_row_', '' )
		};
		const event = new CustomEvent( eventName, { detail: frmData } );
		event.frmData = frmData;
		document.dispatchEvent( event );
	}

	/**
	 * Get the condition row that was just appended to a rows container.
	 *
	 * The new row is the container's last child. A ".frm_logic_row:last-child" query would find the
	 * last row of the first group instead when an add-on wraps rows in groups.
	 *
	 * @since 7.0
	 *
	 * @param {HTMLElement} logicRowsContainer The element that holds the condition rows.
	 * @return {HTMLElement|null} The new row.
	 */
	function getAppendedLogicRow( logicRowsContainer ) {
		const lastChild = logicRowsContainer.lastElementChild;
		return lastChild?.classList.contains( 'frm_logic_row' ) ? lastChild : null;
	}

	function getNewRowId( rows, replace, defaultValue ) {
		// Rows are not always in number order, for example when an add-on moves them, so use the highest number.
		const numbers = Array.from( rows )
			.map( row => parseInt( row.id.replace( replace, '' ), 10 ) )
			.filter( number => ! Number.isNaN( number ) );

		if ( ! numbers.length ) {
			return defaultValue !== undefined ? defaultValue : 0;
		}
		return Math.max( ...numbers ) + 1;
	}

	/**
	 * Returns an object that has the new value and label, when a field choice is changed.
	 *
	 * @param {HTMLElement} choiceElement The changed choice element.
	 * @return {Object} Object with newValue and newLabel properties.
	 */
	function getChoiceNewValueAndLabel( choiceElement ) {
		const singleOptionContainer = choiceElement.closest( '.frm_single_option' );

		let newValue;
		let newLabel;

		if ( choiceElement.parentElement.classList.contains( 'frm_single_option' ) ) { // label changed
			newValue = singleOptionContainer.querySelector( '.frm_option_key input[type="text"]' ).value;
			newLabel = choiceElement.value;
			return { newValue, newLabel };
		}

		// saved value changed
		newLabel = singleOptionContainer.querySelector( 'input[type="text"]' ).value;
		newValue = choiceElement.value;
		return { newValue, newLabel };
	}

	// Number the pages and hide/show the first page as needed.
	function renumberPageBreaks() {
		let i;
		let containerClass;
		const pages = document.getElementsByClassName( 'frm-page-num' );

		if ( pages.length > 1 ) {
			document.getElementById( 'frm-fake-page' ).style.display = 'block';
			for ( i = 0; i < pages.length; i++ ) {
				containerClass = pages[ i ].parentNode.parentNode.parentNode.classList;
				if ( i === 1 ) {
					// Hide previous button on page 1
					containerClass.add( 'frm-first-page' );
				} else {
					containerClass.remove( 'frm-first-page' );
				}
				pages[ i ].textContent = i + 1;
			}
		} else {
			document.getElementById( 'frm-fake-page' ).style.display = 'none';
		}

		wp.hooks.doAction( 'frm_renumber_page_breaks', pages );
	}

	function toggleSectionHolder() {
		document.querySelectorAll( '.start_divider' ).forEach(
			function( divider ) {
				toggleOneSectionHolder( jQuery( divider ) );
			}
		);
	}

	function toggleOneSectionHolder( $section ) {
		if ( ! $section.length ) {
			return;
		}

		const $rows = $section.find( 'ul.frm_sorting' );
		let sectionHasFields = false;
		const { length } = $rows;
		for ( let index = 0; index < length; ++index ) {
			if ( 0 !== getFieldsInRow( jQuery( $rows.get( index ) ) ).length ) {
				sectionHasFields = true;
				break;
			}
		}

		const noSectionFields = $section.parent().children( '.frm_no_section_fields' ).get( 0 );
		noSectionFields.classList.toggle( 'frm_block', ! sectionHasFields );
	}

	function adjustVisibilityValuesForEveryoneValues( element, option ) {
		if ( '' === option.getAttribute( 'value' ) ) {
			onEveryoneOptionSelected( jQuery( this ) );
		} else {
			unselectEveryoneOptionIfSelected( jQuery( this ) );
		}
	}

	function onEveryoneOptionSelected( $select ) {
		$select.val( '' );
		$select.next( '.btn-group' ).find( '.multiselect-container input[value!=""]' ).prop( 'checked', false );
	}

	function unselectEveryoneOptionIfSelected( $select ) {
		const selectedValues = $select.val();

		if ( selectedValues === null ) {
			$select.next( '.btn-group' ).find( '.multiselect-container input[value=""]' ).prop( 'checked', true );
			onEveryoneOptionSelected( $select );
			return;
		}

		const index = selectedValues.indexOf( '' );
		if ( index >= 0 ) {
			selectedValues.splice( index, 1 );
			$select.val( selectedValues );
			$select.next( '.btn-group' ).find( '.multiselect-container input[value=""]' ).prop( 'checked', false );
		}
	}

	function triggerSubmit( form ) {
		const button = form.ownerDocument.createElement( 'input' );
		button.style.display = 'none';
		button.type = 'submit';
		form.append( button );
		button.click();
		button.remove();
	}

	function triggerChange( element ) {
		jQuery( element ).trigger( 'change' );
	}

	/**
	 * Display a modal dialog for naming a new form template, if applicable.
	 *
	 * @return {boolean} True if the modal is successfully initialized and displayed; false otherwise.
	 */
	function showNameYourFormModal() {
		// Exit early if the 'new_template' URL parameter is not set to 'true'
		if ( ! shouldShowNameYourFormNameModal() ) {
			return false;
		}

		const modalWidget = initModal( '#frm-form-templates-modal', '440px' );
		if ( ! modalWidget ) {
			return false;
		}

		// Set the vertical offset for the modal and open it
		offsetModalY( modalWidget, '72px' );
		modalWidget.dialog( 'open' );

		return true;
	}

	/**
	 * Returns true if 'Name Your Form' modal should be displayed.
	 *
	 * @return {boolean} True if the modal should be displayed.
	 */
	function shouldShowNameYourFormNameModal() {
		const formNameInput = document.getElementById( 'frm_form_name' );
		if ( formNameInput && formNameInput.value.trim() !== '' ) {
			return false;
		}

		return 'true' === urlParams.get( 'new_template' ) && document.querySelector( '#frm_top_bar #frm_bs_dropdown .frm_bstooltip' )?.textContent.trim() === frm_admin_js.noTitleText;
	}

	/**
	 * Manages event handling for the 'Name your form' modal.
	 *
	 * Attaches click and keydown event listeners to the save button and input field.
	 *
	 * @return {void}
	 */
	function addFormNameModalEvents() {
		const saveFormNameButton = document.getElementById( 'frm-save-form-name-button' );
		const newFormNameInput = document.getElementById( 'frm_new_form_name_input' );

		// Attach click event listener
		onClickPreventDefault( saveFormNameButton, onSaveFormNameButton );

		// Attach keydown event listener
		newFormNameInput.addEventListener( 'keydown', function( event ) {
			if ( event.key === 'Enter' ) {
				onSaveFormNameButton( event );
			}
		} );
	}

	/**
	 * Handles the click event on the save form name button.
	 *
	 * @param {Event} event The click event object.
	 * @return {void}
	 */
	const onSaveFormNameButton = event => {
		const newFormName = document.getElementById( 'frm_new_form_name_input' ).value.trim();

		// Prepare FormData for the POST request
		const formData = new FormData();
		formData.append( 'form_id', urlParams.get( 'id' ) );
		formData.append( 'form_name', newFormName );

		// Perform the POST request
		doJsonPost( 'rename_form', formData ).then( data => {
			// Remove the 'new_template' parameter from the URL and update the browser history
			urlParams.delete( 'new_template' );
			currentURL.search = urlParams.toString();
			history.replaceState( {}, '', currentURL.toString() );

			if ( null !== document.getElementById( 'frm_notification_settings' ) ) {
				document.getElementById( 'frm_form_name' ).value = newFormName;
				document.getElementById( 'frm_form_key' ).value = data.form_key;
			}

			// Trigger the 'Save' button click using jQuery
			jQuery( '#frm-publishing' ).find( '.frm_button_submit' ).trigger( 'click' );
		} );
	};

	function preFormSave( b ) {
		removeWPUnload();
		if ( jQuery( 'form.inplace_form' ).length ) {
			jQuery( '.inplace_save, .postbox' ).trigger( 'click' );
		}

		if ( b.classList.contains( 'frm_button_submit' ) ) {
			b.classList.add( 'frm_loading_form' );
		} else {
			b.classList.add( 'frm_loading_button' );
		}
		b.setAttribute( 'aria-busy', 'true' );

		adjustFormatInputBeforeSave();
	}

	/**
	 * Updates the format input based on the selected format type from dropdowns during the form save process.
	 *
	 * @since 6.9
	 *
	 * @return {void}
	 */
	function adjustFormatInputBeforeSave() {
		const formatTypes = document.querySelectorAll( '.frm_format_dropdown, .frm_phone_type_dropdown' );
		const valueMap = {
			none: '',
			international: 'international',
			currency: 'currency',
			number: 'number'
		};

		formatTypes.forEach( formatType => {
			const { value } = formatType;
			if ( value in valueMap ) {
				const formatInput = document.getElementById( `frm_format_${ formatType.dataset.fieldId }` );
				formatInput.value = valueMap[ value ];
			}
		} );
	}

	/**
	 * For reverse compatibility. Check for fields that were
	 * using the old sidebar.
	 * @param {string} parentClass The parent CSS class selector.
	 */
	function maybeAddFieldSelection( parentClass ) {
		let i;
		const missingClass = jQuery( `${ parentClass } :not(.frm_has_shortcodes) .frm_not_email_message, ${ parentClass } :not(.frm_has_shortcodes) .frm_not_email_to, ${ parentClass } :not(.frm_has_shortcodes) .frm_not_email_subject` );
		for ( i = 0; i < missingClass.length; i++ ) {
			missingClass[ i ].parentNode.classList.add( 'frm_has_shortcodes' );
		}
	}

	/**
	 * Hide the previous selection without searching every field's settings.
	 *
	 * @since 7.1
	 * @return {void}
	 */
	function hideActiveFieldSettings() {
		adminState.activeSettingsPanel?.classList.add( 'frm_hidden' );
		adminState.activeSettingsPanel = null;
		adminState.defaultFieldSettings.classList.add( 'frm_hidden' );
	}

	/**
	 * Move the settings to the sidebar the first time they are changed or selected.
	 * Keep the end marker at the end of the form.
	 *
	 * @param {HTMLElement|null|undefined} singleField The field settings element to move. Undefined when this is called like new moveFieldSettings().
	 */
	function moveFieldSettings( singleField ) {
		const self = this;

		if ( singleField === null ) {
			// The field may have not been loaded yet via ajax.
			return;
		}

		this.fragment = document.createDocumentFragment();

		this.initOnceInAllInstances = function() {
			if ( moveFieldSettings.prototype.endMarker !== undefined ) {
				return;
			}
			// perform a single search in the DOM and use it across all moveFieldSettings instances
			moveFieldSettings.prototype.endMarker = document.getElementById( 'frm-end-form-marker' );
		};

		this.append = function( field ) {
			const classname = field ? field.parentElement.classList : '';
			if ( ! field || ( ! classname.contains( 'frm_field_box' ) && ! classname.contains( 'divider_section_only' ) ) ) {
				return;
			}
			self.fragment.append( field );
		};

		this.moveFields = function() {
			builderForm.insertBefore( self.fragment, moveFieldSettings.prototype.endMarker );
		};

		this.initOnceInAllInstances();

		// Move the field if function is called as function with a singleField passed as arg.
		// In this particular case only 1 field is needed to be moved so the field will get instantly moved.
		// "singleField" may be undefined when it's called as a constructor instead of a function. Use the constructor to add multiple fields which are passed through "append" and move these all at once via "moveFields".
		if ( singleField !== undefined ) {
			this.append( singleField );
			this.moveFields();
			return;
		}

		return {
			append: this.append,
			moveFields: this.moveFields
		};
	}

	function toggleFormOpts() {
		/*jshint validthis:true */
		const changedOpt = jQuery( this );
		let val = changedOpt.val();
		if ( changedOpt.attr( 'type' ) === 'checkbox' ) {
			if ( this.checked === false ) {
				val = '';
			}
		}

		const toggleClass = changedOpt.data( 'toggleclass' );
		if ( val === '' ) {
			jQuery( `.${ toggleClass }` ).hide();
		} else {
			jQuery( `.${ toggleClass }` ).show();
			jQuery( `.hide_${ toggleClass }_${ val }` ).hide();
		}
	}

	/**
	 * Shows 'Save and Reload' modal with the given message.
	 *
	 * @since 6.16.3
	 *
	 * @param {string} message
	 * @return {void}
	 */
	function showSaveAndReloadModal( message ) {
		if ( message === undefined ) {
			message = __( 'You are changing the field type. Not all field settings will appear as expected until you reload the page. Would you like to reload the page now?', 'formidable' );
		}
		frmDom.modal.maybeCreateModal(
			'frmSaveAndReloadModal',
			{
				title: __( 'Save and Reload?', 'formidable' ),
				content: getModalContent(),
				footer: getModalFooter()
			}
		);

		function getModalContent() {
			const modalContent = div( message );
			modalContent.style.padding = 'var(--gap-md)';
			return modalContent;
		}

		function getModalFooter() {
			const continueButton = frmDom.modal.footerButton( {
				text: __( 'Save and Reload', 'formidable' ),
				buttonType: 'primary'
			} );

			onClickPreventDefault( continueButton, () => {
				saveAndReloadFormBuilder();
			} );

			const cancelButton = frmDom.modal.footerButton( {
				text: __( 'Cancel', 'formidable' ),
				buttonType: 'cancel'
			} );
			cancelButton.classList.add( 'dismiss' );

			return frmDom.div( {
				children: [ cancelButton, continueButton ]
			} );
		}
	}

	function fieldUpdated() {
		if ( ! adminState.fieldsUpdated ) {
			adminState.fieldsUpdated = 1;
			window.addEventListener( 'beforeunload', confirmExit );
		}
	}

	function saveAndReloadFormBuilder() {
		const submitButton = document.getElementById( 'frm_submit_side_top' );
		if ( submitButton.classList.contains( 'frm_submit_ajax' ) ) {
			submitButton.setAttribute( 'data-new-addon-installed', true );
		}
		submitButton.click();
	}

	function confirmExit( event ) {
		if ( adminState.fieldsUpdated ) {
			event.preventDefault();
			event.returnValue = '';
		}
	}

	function offsetModalY( $modal, amount ) {
		const position = {
			my: 'top',
			at: `top+${ amount }`,
			of: window
		};
		$modal.dialog( 'option', 'position', position );
	}

	function toggleAllowedHTML( input ) {
		let { id } = input;
		if ( id === undefined || id.includes( '-search-input' ) ) {
			return;
		}

		jQuery( '#frm-adv-info-tab' ).attr( 'data-fills', id.trim() );
		if ( input.classList.contains( 'field_custom_html' ) ) {
			id = 'field_custom_html';
		}

		const htmlFieldIds = [ 'after_html', 'before_html', 'submit_html', 'field_custom_html' ];
		// eslint-disable-next-line sonarjs/prefer-native-jquery-alternative -- jQuery API kept, not refactored
		if ( jQuery.inArray( id, htmlFieldIds ) >= 0 ) {
			jQuery( `.frm_code_list li:not(.show_${ id })` ).addClass( 'frm_hidden' );
			jQuery( `.frm_code_list li.show_${ id }` ).removeClass( 'frm_hidden' );
		}
	}

	function toggleKeyID( switchTo, e ) {
		e.stopPropagation();
		jQuery( '.frm_code_list .frmids, .frm_code_list .frmkeys' ).addClass( 'frm_hidden' );
		jQuery( `.frm_code_list .${ switchTo }` ).removeClass( 'frm_hidden' );
		jQuery( '.frmids, .frmkeys' ).removeClass( 'current' );
		jQuery( `.${ switchTo }` ).addClass( 'current' );
	}

	/* Global settings page */
	function loadSettingsTab( anchor ) {
		const holder = anchor.replace( '#', '' );
		const holderContainer = jQuery( `.frm_${ holder }_ajax` );
		if ( holderContainer.length ) {
			jQuery.ajax( {
				type: 'POST', url: ajaxurl,
				data: {
					action: 'frm_settings_tab',
					tab: holder.replace( '_settings', '' ),
					nonce: frmGlobal.nonce
				},
				success( html ) {
					holderContainer.replaceWith( html );
				}
			} );
		}
	}

	/**
	 * @param {Array|Element|jQuery} [container] Limit the set up to the multiselects inside this,
	 *                                           instead of every multiselect in the page.
	 */
	function initiateMultiselect( container ) {
		// A field's own settings panel (.frm-single-settings) stays hidden until it's clicked, so a
		// still-hidden panel's multiselect is skipped here and initiated later, when its panel is
		// shown (showFieldOptions) - regardless of whether this run is scoped to a container (e.g.
		// newly ajax-loaded fields) or the whole page.
		const $multiselect = ( container ? jQuery( container ).find( '.frm_multiselect' ) : jQuery( '.frm_multiselect' ) )
			.not( '.frm-single-settings.frm_hidden .frm_multiselect' );

		$multiselect.toArray().forEach( hydrateBuilderSelect );
		$multiselect.hide().each( frmDom.bootstrap.multiselect.init );
	}

	/* Addons page */
	function installMultipleAddons( e ) {
		e.preventDefault();
		toggleAddonState( this, 'frm_multiple_addons' );
	}

	function activateAddon( e ) {
		e.preventDefault();
		toggleAddonState( this, 'frm_activate_addon' );
	}

	function installAddon( e ) {
		e.preventDefault();
		toggleAddonState( this, 'frm_install_addon' );
	}

	function toggleAddonState( clicked, action ) {
		const addonState = require( './addon-state' );
		addonState.toggleAddonState( clicked, action );
	}

	/* Templates */
	function showActiveCampaignForm() {
		loadApiEmailForm();
	}

	function handleApiFormError( inputId, errorId, type, message ) {
		const $error = jQuery( errorId );
		$error.removeClass( 'frm_hidden' ).attr( 'frm-error', type );

		if ( message !== undefined ) {
			$error.find( `span[frm-error="${ type }"]` ).text( message );
		}

		jQuery( inputId ).one( 'keyup', function() {
			$error.addClass( 'frm_hidden' );
		} );
	}

	function handleEmailAddressError( type ) {
		handleApiFormError( '#frm_leave_email', '#frm_leave_email_error', type );
	}

	function loadApiEmailForm() {
		const formContainer = document.getElementById( 'frmapi-email-form' );
		jQuery.ajax( {
			dataType: 'json',
			url: formContainer.getAttribute( 'data-url' ),
			success( json ) {
				let form = json.renderedHtml;
				form = form.replace( /<link\b[^>]*(formidableforms.css|action=frmpro_css)[^>]*>/gi, '' );
				formContainer.innerHTML = form;
			}
		} );
	}

	function initAutocomplete( container ) {
		frmDom.autocomplete.initSelectionAutocomplete( container );
	}

	function nextInstallStep( thisStep ) {
		thisStep.classList.add( 'frm_grey' );
		thisStep.nextElementSibling.classList.remove( 'frm_grey' );
	}

	function installTemplate( e ) {
		/*jshint validthis:true */
		const action = this.elements.type.value;
		const button = this.querySelector( 'button' );
		e.preventDefault();
		button.classList.add( 'frm_loading_button' );
		installNewForm( this, action, button );
	}

	function installNewForm( form, action, button ) {
		const formData = formToData( form );
		const formName = formData.template_name;
		const formDesc = formData.template_desc;
		const link = form.elements.link.value;

		let data = {
			action,
			xml: link,
			name: formName,
			desc: formDesc,
			form: JSON.stringify( formData ),
			nonce: frmGlobal.nonce
		};

		const hookName = 'frm_before_install_new_form';
		const filterArgs = { formData };
		data = wp.hooks.applyFilters( hookName, data, filterArgs );

		postAjax( data, function( response ) {
			if ( response.redirect !== undefined ) {
				const { redirect } = response;
				if ( form.elements.redirect === undefined ) {
					window.location = redirect;
				} else {
					const href = document.getElementById( 'frm-redirect-link' );
					if ( link !== undefined && href ) {
						// Show the next installation step.
						href.setAttribute( 'href', redirect );
						href.classList.remove( 'frm_grey', 'disabled' );
						nextInstallStep( form.parentNode.parentNode );
						button.classList.add( 'frm_grey', 'disabled' );
					}
				}
			} else {
				jQuery( '.spinner' ).css( 'visibility', 'hidden' );

				// Show response.message
				if ( 'string' === typeof response.message ) {
					showInstallFormErrorModal( response.message );
				}
			}
			button.classList.remove( 'frm_loading_button' );
		} );
	}

	function showInstallFormErrorModal( message ) {
		const modalContent = div( message );
		modalContent.style.padding = '20px 40px';
		const modal = frmDom.modal.maybeCreateModal(
			'frmInstallFormErrorModal',
			{
				title: __( 'Unable to install template', 'formidable' ),
				content: modalContent
			}
		);
		modal.classList.add( 'frm_common_modal' );
	}

	/**
	 * Syncs aria-selected and the roving tabindex of a radio-backed tablist with its checked radio.
	 * The Tab stop is the checked tab, or the first visible tab when the checked one is hidden.
	 *
	 * @param {HTMLElement} tablist
	 * @return {void}
	 */
	function syncTablistState( tablist ) {
		const tabs = Array.from( tablist.querySelectorAll( 'label[role="tab"]' ) );
		const visibleTabs = tabs.filter( label => ! label.classList.contains( 'frm_hidden' ) );
		const tabStop = visibleTabs.find( label => label.control.checked ) || visibleTabs[ 0 ];

		tabs.forEach( label => {
			label.setAttribute( 'aria-selected', label.control.checked ? 'true' : 'false' );
			label.setAttribute( 'tabindex', label === tabStop ? '0' : '-1' );
		} );
	}

	/**
	 * Adds Enter, Space, arrow, Home and End key support to a tablist of radio labels.
	 *
	 * @param {HTMLElement} tablist
	 * @return {void}
	 */
	function initTablistKeyboard( tablist ) {
		tablist.addEventListener( 'keydown', function( event ) {
			const tabs = Array.from( tablist.querySelectorAll( 'label[role="tab"]:not(.frm_hidden)' ) );
			const index = tabs.indexOf( event.target.closest( 'label' ) );
			if ( -1 === index ) {
				return;
			}

			let target;
			switch ( event.key ) {
				case 'Enter':
				case ' ':
					target = tabs[ index ];
					break;
				case 'ArrowRight':
					target = tabs[ ( index + 1 ) % tabs.length ];
					break;
				case 'ArrowLeft':
					target = tabs[ ( index + tabs.length - 1 ) % tabs.length ];
					break;
				case 'Home':
					target = tabs[ 0 ];
					break;
				case 'End':
					target = tabs[ tabs.length - 1 ];
					break;
				default:
					return;
			}

			event.preventDefault();
			target.focus();
			target.click();
		} );
	}

	function trashTemplate( e ) {
		/*jshint validthis:true */
		const id = this.getAttribute( 'data-id' );
		e.preventDefault();

		const data = {
			action: 'frm_forms_trash',
			id,
			nonce: frmGlobal.nonce
		};
		postAjax( data, function() {
			const card = document.getElementById( `frm-template-custom-${ id }` );
			fadeOut( card, function() {
				card.remove();
			} );
		} );
	}

	function stopPropagation( e ) {
		e.stopPropagation();
	}

	function triggerEvent( element, event ) {
		const evt = document.createEvent( 'HTMLEvents' );
		evt.initEvent( event, false, true );
		element.dispatchEvent( evt );
	}

	function postAjax( data, success ) {
		let response;

		const xmlHttp = new XMLHttpRequest();
		const params = typeof data === 'string' ? data : Object.keys( data ).map(
			function( k ) {
				return `${ encodeURIComponent( k ) }=${ encodeURIComponent( data[ k ] ) }`;
			}
		).join( '&' );

		xmlHttp.open( 'post', ajaxurl, true );
		xmlHttp.onreadystatechange = function() {
			if ( xmlHttp.readyState > 3 && xmlHttp.status == 200 ) {
				response = xmlHttp.responseText;
				try {
					response = JSON.parse( response );
				} catch ( e ) {
					// The response may not be JSON, so just return it.
				}
				success( response );
			}
		};
		xmlHttp.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
		xmlHttp.setRequestHeader( 'Content-type', 'application/x-www-form-urlencoded' );
		xmlHttp.send( params );
		return xmlHttp;
	}

	function fadeOut( element, success ) {
		element.classList.add( 'frm-fade' );
		setTimeout( success, 1000 );
	}

	function removeWPUnload() {
		window.onbeforeunload = null;
		const $window = jQuery( window );
		$window.off( 'beforeunload.widgets' );
		$window.off( 'beforeunload.edit-post' );
	}

	function addMultiselectLabelListener() {
		const clickListener = e => {
			if ( 'LABEL' !== e.target.nodeName ) {
				return;
			}

			const labelFor = e.target.getAttribute( 'for' );
			if ( ! labelFor ) {
				return;
			}

			const input = document.getElementById( labelFor );
			if ( ! input || ! input.nextElementSibling ) {
				return;
			}

			const buttonToggle = input.nextElementSibling.querySelector( 'button.dropdown-toggle.multiselect' );
			if ( ! buttonToggle ) {
				return;
			}

			const triggerMultiselectClick = () => buttonToggle.click();
			setTimeout( triggerMultiselectClick, 0 );
		};
		document.addEventListener( 'click', clickListener );
	}

	/**
	 * @param {number | string} fieldId
	 * @return {boolean} True if the field is a product field.
	 */
	function isProductField( fieldId ) {
		const field = document.getElementById( `frm_field_id_${ fieldId }` );
		if ( ! field ) {
			return false;
		}
		return 'product' === field.getAttribute( 'data-type' );
	}

	/**
	 * Serialize form data with vanilla JS.
	 * @param {HTMLElement} form The form element to serialize.
	 */
	function formToData( form ) {
		let subKey;
		let i;
		const object = {};
		const formData = form.elements;

		for ( i = 0; i < formData.length; i++ ) {
			const input = formData[ i ];
			let key = input.name;
			const { value } = input;
			// eslint-disable-next-line sonarjs/super-linear-regex -- regex kept as-is, not refactored
			const names = key.match( /(.*)\[(.*)\]/ );

			if ( ( input.type === 'radio' || input.type === 'checkbox' ) && ! input.checked ) {
				continue;
			}

			if ( names !== null ) {
				key = names[ 1 ];
				subKey = names[ 2 ];
				if ( ! Reflect.has( object, key ) ) {
					object[ key ] = {};
				}
				object[ key ][ subKey ] = value;
				continue;
			}

			// Reflect.has in favor of: object.hasOwnProperty(key)
			if ( ! Reflect.has( object, key ) ) {
				object[ key ] = value;
				continue;
			}
			if ( ! Array.isArray( object[ key ] ) ) {
				object[ key ] = [ object[ key ] ];
			}
			object[ key ].push( value );
		}

		return object;
	}

	/**
	 * Listen for click events for an API-loaded email collection form.
	 *
	 * This is used for the Active Campaign sign-up form in the inbox page (when there are no messages).
	 */
	function initAddMyEmailAddress() {
		jQuery( document ).on(
			'click',
			'#frm-add-my-email-address',
			event => {
				event.preventDefault();
				addMyEmailAddress();
			}
		);

		const emptyInbox = document.getElementById( 'frm_empty_inbox' );
		const leaveEmailInput = document.getElementById( 'frm_leave_email' );

		if ( emptyInbox && leaveEmailInput ) {
			const leaveEmailModal = document.getElementById( 'frm-leave-email-modal' );
			leaveEmailModal.classList.remove( 'frm_hidden' );
			leaveEmailModal.querySelector( '.frm_modal_footer' ).classList.add( 'frm_hidden' );

			leaveEmailInput.addEventListener(
				'keyup',
				event => {
					if ( 'Enter' === event.key ) {
						const button = document.getElementById( 'frm-add-my-email-address' );
						if ( button ) {
							button.click();
						}
					}
				}
			);
		}
	}

	function addMyEmailAddress() {
		const email = document.getElementById( 'frm_leave_email' ).value.trim();
		if ( '' === email ) {
			handleEmailAddressError( 'empty' );
			return;
		}

		const regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;
		if ( regex.test( email ) === false ) {
			handleEmailAddressError( 'invalid' );
			return;
		}

		const $hiddenForm = jQuery( '#frmapi-email-form' ).find( 'form' );
		const $hiddenEmailField = $hiddenForm.find( '[type="email"]' ).not( '.frm_verify' );
		if ( ! $hiddenEmailField.length ) {
			return;
		}

		const emptyInbox = document.getElementById( 'frm_empty_inbox' );
		if ( emptyInbox ) {
			document.getElementById( 'frm-add-my-email-address' ).remove();

			const emailWrapper = document.getElementById( 'frm_leave_email_wrapper' );
			if ( emailWrapper ) {
				emailWrapper.classList.add( 'frm_hidden' );
				const spinner = span( { className: 'frm-wait frm_spinner' } );
				spinner.style.visibility = 'visible';
				spinner.style.float = 'none';
				spinner.style.width = 'unset';
				emailWrapper.parentElement.insertBefore(
					spinner,
					emailWrapper.nextElementSibling
				);
			}
		}

		$hiddenEmailField.val( email );
		jQuery.ajax( {
			type: 'POST',
			url: $hiddenForm.attr( 'action' ),
			data: `${ $hiddenForm.serialize() }&action=frm_forms_preview`
		} ).done( function( data ) {
			const message = jQuery( data ).find( '.frm_message' ).text().trim();
			if ( ! message.includes( 'Thanks!' ) ) {
				handleEmailAddressError( 'invalid' );
				return;
			}

			const apiForm = document.getElementById( 'frmapi-email-form' );
			const spinner = apiForm.parentElement.querySelector( '.frm_spinner' );
			if ( spinner ) {
				spinner.remove();
			}

			const showSuccessMessage = wp.hooks.applyFilters( 'frm_thank_you_on_signup', true );
			if ( showSuccessMessage ) {
				// Handle successful form submission.
				// handle the Active Campaign form on the inbox page.
				document.getElementById( 'frm_leave_email_wrapper' ).replaceWith(
					span( __( 'Thank you for signing up!', 'formidable' ) )
				);
			}
		} );
	}

	/**
	 * Adds footer links to the admin body content.
	 *
	 * @return {void}
	 */
	function addAdminFooterLinks() {
		const footerLinks = document.querySelector( '.frm-admin-footer-links' );
		const container = document.querySelector( '.frm_page_container' ) ?? document.getElementById( 'wpbody-content' );

		if ( ! footerLinks || ! container ) {
			return;
		}

		container.append( footerLinks );
		footerLinks.classList.remove( 'frm_hidden' );
	}

	/**
	 * Apply zebra striping to a table while ignoring empty rows.
	 *
	 * @param {string} tableSelector The CSS selector for the table.
	 * @param {string} emptyRowClass The class name used to identify empty rows.
	 */
	function applyZebraStriping( tableSelector, emptyRowClass ) {
		// Get all non-empty table rows within the specified table
		const rows = document.querySelectorAll( `${ tableSelector } tr${ emptyRowClass ? `:not(.${ emptyRowClass })` : '' }` );
		if ( rows.length < 1 ) {
			return;
		}

		let isOdd = true;
		rows.forEach( row => {
			// Clean old "frm-odd" or "frm-even" classes and add the appropriate new class
			row.classList.remove( 'frm-odd', 'frm-even' );
			row.classList.add( isOdd ? 'frm-odd' : 'frm-even' );

			isOdd = ! isOdd;
		} );

		const tables = document.querySelectorAll( tableSelector );
		tables.forEach( table => table.classList.add( 'frm-zebra-striping' ) );
	}

	/**
	 * Initializes and manages the visibility of dependent elements based on the selected options in dropdowns with the 'frm_select_with_dependency' class.
	 * It sets up initial visibility at page load and updates it on each dropdown change.
	 *
	 * @since 6.9
	 *
	 * @return {void}
	 */
	function initSelectDependencies() {
		const selects = document.querySelectorAll( 'select.frm_select_with_dependency' );

		/**
		 * Toggles the visibility of dependent elements associated with a select element based on its current selection.
		 *
		 * @since 6.9
		 *
		 * @param {HTMLElement} select The select element whose dependencies need to be managed.
		 * @return {void}
		 */
		function toggleDependencyVisibility( select ) {
			const selectedOption = select.options[ select.selectedIndex ];
			select.querySelectorAll( 'option[data-dependency]:not([data-dependency-skip])' ).forEach( option => {
				const dependencyElement = document.querySelector( option.dataset.dependency );
				dependencyElement?.classList.toggle( 'frm_hidden', selectedOption !== option );
			} );
		}

		// Initial setup: Show dependencies based on the current selection in each dropdown
		selects.forEach( toggleDependencyVisibility );

		// Update dependencies visibility on dropdown change
		frmDom.util.documentOn( 'change', 'select.frm_select_with_dependency', event => toggleDependencyVisibility( event.target ) );
	}

	return {
		...builderScreen?.createBuilder( {
			adminState,
			$newFields,
			addFormNameModalEvents,
			addImageToOption,
			adjustConditionalLogicOptionOrders,
			adjustVisibilityValuesForEveryoneValues,
			builderForm,
			builderPage,
			clearSettingsBox,
			confirmLinkClick,
			countFieldTypeInForm,
			deleteAllSelectedFieldGroups,
			deleteTooltips,
			deselectFields,
			ensureFieldSettings,
			fieldUpdated,
			fieldsContainer,
			fillDropdownOpts,
			frmAdminJs,
			getAppendedLogicRow,
			getChoiceNewValueAndLabel,
			getClassForBlock,
			getEvenClassForSize,
			getFieldIdsToDelete,
			getFieldList,
			getFieldsInRow,
			getImageLabel,
			getInputForIcon,
			getLayoutClasses,
			getMultipleOpts,
			getNewRowId,
			getSVGHref,
			hideActiveFieldSettings,
			hydrateBuilderSelect,
			hydrateBuilderSelectsIn,
			imagesAsOptions,
			infoModal,
			initModal,
			initiateMultiselect,
			isProductField,
			makeTabbable,
			maybeHideQuantityProductFieldOption,
			maybeHideShortcodes,
			maybeRemoveMultiselectPopup,
			moveFieldSettings,
			popAllProductFields,
			popCalcFields,
			popProductFields,
			preFormSave,
			prepareLoadedFieldMarkup,
			purifyHtml,
			removeImageFromOption,
			renumberPageBreaks,
			showNameYourFormModal,
			showSaveAndReloadModal,
			showingLabelWithImage,
			syncLayoutClasses,
			toggleOneSectionHolder,
			toggleSectionHolder,
			triggerLogicRowEvent,
			triggerSubmit,
			unselectFieldGroups,
			updateFieldGroupControls,
			updateShortcodesPopupPosition,
			usingSeparateValues,
			validateRangeSettings,
		} ),
		init() {
			initAddMyEmailAddress();
			addAdminFooterLinks();

			document.addEventListener( 'show.bs.dropdown', function() {
				// Fixes issues with tooltips lingering after a dropdown is shown.
				deleteTooltips();
			} );

			// Bootstrap dropdown button
			jQuery( '.wp-admin' ).on( 'click', function( e ) {
				const $target = jQuery( e.target );
				const $openDrop = jQuery( '.dropdown.open' );
				if ( $openDrop.length && ! $target.hasClass( 'dropdown' ) && ! $target.closest( '.dropdown' ).length ) {
					$openDrop.removeClass( 'open' );
				}
			} );
			jQuery( '#frm_bs_dropdown:not(.open) a' ).on( 'click', focusSearchBox );

			if ( adminState.thisFormId === undefined ) {
				adminState.thisFormId = jQuery( document.getElementById( 'form_id' ) ).val();
			}

			// Add event listener for dismissible warning messages.
			document.querySelectorAll( '.frm-warning-dismiss' ).forEach( dismissIcon => {
				onClickPreventDefault( dismissIcon, dismissWarningMessage );
			} );

			frmAdminBuild.inboxBannerInit();

			if ( $newFields.length > 0 ) {
				// only load this on the form builder page
				frmAdminBuild.buildInit();
			} else if ( document.getElementById( 'frm_notification_settings' ) !== null ) {
				// only load on form settings page
				frmAdminBuild.settingsInit();
			} else if ( document.getElementById( 'frm_styling_form' ) !== null ) {
				// load styling settings js
				frmAdminBuild.styleInit();
			} else if ( document.getElementById( 'form_global_settings' ) !== null ) {
				// global settings page
				frmAdminBuild.globalSettingsInit();
			} else if ( document.getElementById( 'frm_export_xml' ) !== null ) {
				// import/export page
				frmAdminBuild.exportInit();
			} else if ( null !== document.querySelector( '.frm-inbox-wrapper' ) ) {
				// Dashboard page inbox.
				frmAdminBuild.inboxInit();
			} else if ( document.getElementById( 'frm-welcome' ) !== null ) {
				// Solution install page
				frmAdminBuild.solutionInit();
			} else {
				initAutocomplete();

				jQuery( '[data-frmprint]' ).on( 'click', function() {
					window.print();
					return false;
				} );
			}

			jQuery( document ).on( 'change', 'select[data-toggleclass], input[data-toggleclass]', toggleFormOpts );
			initSelectDependencies();

			const $advInfo = jQuery( document.getElementById( 'frm_adv_info' ) );
			if ( $advInfo.length > 0 || jQuery( '.frm_field_list' ).length > 0 ) {
				// only load on the form, form settings, and view settings pages
				frmAdminBuild.panelInit();
			}

			loadTooltips();
			initUpgradeModal();
			frmDom.util.documentOn( 'click', '[data-modal-title]', showBasicModal );

			// used on build, form settings, and view settings
			const $shortCodeDiv = jQuery( document.getElementById( 'frm_shortcodediv' ) );
			if ( $shortCodeDiv.length > 0 ) {
				jQuery( 'a.edit-frm_shortcode' ).on( 'click', function() {
					if ( $shortCodeDiv.is( ':hidden' ) ) {
						$shortCodeDiv.slideDown( 'fast' );
						this.style.display = 'none';
					}
					return false;
				} );

				jQuery( '.cancel-frm_shortcode', '#frm_shortcodediv' ).on( 'click', function() {
					$shortCodeDiv.slideUp( 'fast' );
					$shortCodeDiv.siblings( 'a.edit-frm_shortcode' ).show();
					return false;
				} );
			}

			// tabs
			jQuery( document ).on( 'click', '#frm-nav-tabs a', clickNewTab );
			jQuery( '.post-type-frm_display .frm-nav-tabs a, .frm-category-tabs a' ).on( 'click', function() {
				const showUpgradeTab = this.classList.contains( 'frm_show_upgrade_tab' );
				if ( this.classList.contains( 'frm_noallow' ) && ! showUpgradeTab ) {
					return;
				}

				if ( showUpgradeTab && this.classList.contains( 'frm_show_expired_modal' ) ) {
					wp.hooks.doAction( 'frm_show_expired_modal', this );
					return false;
				}

				if ( showUpgradeTab ) {
					populateUpgradeTab( this );
				}

				clickTab( this );
				return false;
			} );
			clickTab( jQuery( '.frm-category-tabs .starttab a' ), 'auto' );

			// submit the search form with dropdown
			jQuery( document ).on( 'click', '#frm-fid-search-menu a', function() {
				const val = this.id.replace( 'fid-', '' );
				jQuery( 'select[name="fid"]' ).val( val );
				triggerSubmit( document.getElementById( 'posts-filter' ) );
				return false;
			} );

			jQuery( '.frm_select_box' ).on( 'click focus', function() {
				this.select();
			} );

			jQuery( document ).on( 'input search change', '.frm-auto-search:not(#frm-form-templates-page #template-search-input)', searchContent );
			jQuery( document ).on( 'focusin click', '.frm-auto-search', stopPropagation );
			const autoSearch = jQuery( '.frm-auto-search' );
			if ( autoSearch.val() !== '' ) {
				autoSearch.trigger( 'keyup' );
			}

			// Initialize Formidable Connection.
			FrmFormsConnect.init();

			jQuery( document ).on( 'click', '.frm-install-addon', installAddon );
			jQuery( document ).on( 'click', '.frm-activate-addon', activateAddon );
			jQuery( document ).on( 'click', '.frm-solution-multiple', installMultipleAddons );

			// prevent annoying confirmation message from WordPress
			jQuery( 'button, input[type=submit]' ).on( 'click', removeWPUnload );

			addMultiselectLabelListener();

			frmAdminBuild.hooks.addFilter(
				'frm_before_embed_modal',
				( ids, { element, type } ) => {
					if ( 'form' !== type ) {
						return ids;
					}

					let formId;
					let formKey;
					const row = element.closest( 'tr' );

					if ( row ) {
						// Embed icon on form index.
						formId = parseInt( row.querySelector( '.column-id' ).textContent );
						formKey = row.querySelector( '.column-form_key' ).textContent;
					} else {
						// Embed button in form builder / form settings.
						formId = document.getElementById( 'form_id' ).value;

						const formKeyInput = document.getElementById( 'frm_form_key' );
						if ( formKeyInput ) {
							formKey = formKeyInput.value;
						} else {
							const previewDrop = document.getElementById( 'frm-previewDrop' );
							if ( previewDrop ) {
								formKey = previewDrop.nextElementSibling.querySelector( '.dropdown-item a' ).getAttribute( 'href' ).split( 'form=' )[ 1 ];
							}
						}
					}

					return [ formId, formKey ];
				}
			);

			const smallScreenProceedButton = document.getElementById( 'frm_small_screen_proceed_button' );
			if ( smallScreenProceedButton ) {
				onClickPreventDefault( smallScreenProceedButton, () => {
					document.getElementById( 'frm_small_device_message_container' )?.remove();
					doJsonPost( 'small_screen_proceed', new FormData() );
				} );
			}

			const saleBanner = document.getElementById( 'frm_sale_banner' );
			const saleDismiss = saleBanner?.querySelector( '.dismiss' );
			if ( saleBanner ) {
				onClickPreventDefault( saleBanner, event => {
					const { target } = event;
					if ( target.closest( '.dismiss' ) ) {
						return;
					}
					window.location.href = saleBanner.getAttribute( 'data-url' );
				} );

				if ( saleDismiss ) {
					onClickPreventDefault( saleDismiss, () => {
						saleBanner.remove();

						const formData = new FormData();
						doJsonPost( 'sale_banner_dismiss', formData );
					} );
				}
			}
		},

		settingsInit() {
			wp.hooks.doAction( 'frmAdminFormSettingsInit', {
				addFormNameModalEvents,
				addImageToOption,
				adjustVisibilityValuesForEveryoneValues,
				adminState,
				checkActiveAction,
				clickTab,
				div,
				fieldUpdated,
				frmAdminJs,
				getAppendedLogicRow,
				getNewRowId,
				infoModal,
				initAutocomplete,
				initiateMultiselect,
				isAtLimitForActionType,
				maybeHideShortcodes,
				preFormSave,
				removeImageFromOption,
				showInputIcon,
				showNameYourFormModal,
				triggerChange,
				triggerLogicRowEvent,
				triggerSubmit,
			} );
		},

		panelInit() {
			jQuery( '.frm_wrap, #postbox-container-1' ).on( 'click', '.frm_insert_code', insertCode );
			jQuery( document ).on( 'change', '.frm_insert_val', function() {
				insertFieldCode( jQuery( this ).data( 'target' ), jQuery( this ).val() );
				jQuery( this ).val( '' );
			} );

			jQuery( document ).on( 'click change', '[name="frm-id-key-condition"]', resetLogicBuilder );
			jQuery( document ).on( 'keyup change', '.frm-build-logic', setLogicExample );

			showInputIcon();
			jQuery( document ).on( 'frmElementAdded', function( event, parentEle ) {
				/* This is here for add-ons to trigger */
				showInputIcon( parentEle );
			} );
			jQuery( document ).on( 'mousedown', '.frm-show-box', showShortcodes );

			const settingsPage = document.getElementById( 'form_settings_page' );
			const viewPage = document.body.classList.contains( 'post-type-frm_display' );
			const insertFieldsTab = document.getElementById( 'frm_insert_fields_box_tab' );

			if ( settingsPage || viewPage || builderPage ) {
				jQuery( document ).on( 'focusin', 'form input, form textarea', function( e ) {
					let htmlTab;
					e.stopPropagation();
					maybeShowModal( this );

					if ( jQuery( this ).is( ':not(:submit, input[type=button], .frm-search-input, input[type=checkbox])' ) ) {
						if ( jQuery( e.target ).closest( '#frm_adv_info' ).length ) {
						// Don't trigger for fields inside of the modal.
							return;
						}

						if ( settingsPage || builderPage ) {
						/* form settings page */
							htmlTab = jQuery( '#frm_html_tab' );
							if ( jQuery( this ).closest( '#html_settings' ).length > 0 ) {
								htmlTab.show();
								htmlTab.siblings().hide();
								jQuery( '#frm_html_tab a' ).trigger( 'click' );
								toggleAllowedHTML( this );
							} else {
								showElement( jQuery( '.frm-category-tabs li' ) );
								insertFieldsTab.click();
								htmlTab.hide();
								htmlTab.siblings().show();
							}
						} else if ( viewPage ) {
							const event = new CustomEvent( 'frm_legacy_views_handle_field_focus' );
							event.frmData = { idAttrValue: this.id };
							document.dispatchEvent( event );
						}
					}
				} );
			}

			jQuery( '.frm_wrap, #postbox-container-1' ).on( 'mousedown', '#frm_adv_info a, .frm_field_list a', function( e ) {
				e.preventDefault();
			} );

			const customPanel = jQuery( '#frm_adv_info' );
			customPanel.on( 'click', '.subsubsub a.frmids', function( e ) {
				toggleKeyID( 'frmids', e );
			} );
			customPanel.on( 'click', '.subsubsub a.frmkeys', function( e ) {
				toggleKeyID( 'frmkeys', e );
			} );
		},

		inboxInit() {
			jQuery( '.frm_inbox_dismiss' ).on( 'click', function( e ) {
				const message = this.parentNode.parentNode;
				const key = message.getAttribute( 'data-message' );
				const href = this.getAttribute( 'href' );
				const dismissedMessage = message.cloneNode( true );
				const dismissedMessagesWrapper = document.querySelector( '.frm-dismissed-inbox-messages' );

				if ( 'free_templates' === key && ! this.classList.contains( 'frm_inbox_dismiss' ) ) {
					return;
				}

				e.preventDefault();

				const data = {
					action: 'frm_inbox_dismiss',
					key,
					nonce: frmGlobal.nonce
				};

				const isInboxSlideIn = 'frm_inbox_slide_in' === message.id;
				if ( isInboxSlideIn ) {
					message.classList.remove( 's11-fadein' );
					message.classList.add( 's11-fadeout' );
					message.addEventListener( 'animationend', () => message.remove(), { once: true } );
				}

				postAjax(
					data,
					() => {
						if ( isInboxSlideIn ) {
							return;
						}

						if ( href !== '#' ) {
							window.location = href;
							return true;
						}

						fadeOut(
							message,
							() => {
								if ( dismissedMessagesWrapper ) {
									dismissedMessage.classList.remove( 'frm-fade' );
									dismissedMessage.querySelector( '.frm-inbox-message-heading .frm_inbox_dismiss' )?.remove();
									dismissedMessagesWrapper.append( dismissedMessage );
								}
								if ( 1 === message.parentNode.querySelectorAll( '.frm-inbox-message-container' ).length ) {
									document.getElementById( 'frm_empty_inbox' ).classList.remove( 'frm_hidden' );
									message.parentNode.closest( '.frm-active' ).classList.add( 'frm-empty-inbox' );
									showActiveCampaignForm();
								}
								message.remove();
							}
						);
					}
				);
			} );

			if ( false === document.getElementById( 'frm_empty_inbox' )?.classList.contains( 'frm_hidden' ) ) {
				showActiveCampaignForm();
			}
		},

		solutionInit() {
			jQuery( document ).on( 'submit', '#frm-new-template', installTemplate );
		},

		styleInit() {
			const $previewWrapper = jQuery( '.frm_image_preview_wrapper' );
			$previewWrapper.on( 'click', '.frm_choose_image_box', addImageToOption );
			$previewWrapper.on( 'click', '.frm_remove_image_option', removeImageFromOption );

			wp.hooks.doAction( 'frm_style_editor_init' );
		},

		customCSSInit() {
			console.warn( 'Calling frmAdminBuild.customCSSInit is deprecated.' );
		},

		globalSettingsInit() {
			wp.hooks.doAction( 'frmAdminGlobalSettingsInit', {
				adminState,
				confirmLinkClick,
				fieldUpdated,
				initTablistKeyboard,
				initiateMultiselect,
				installNewForm,
				syncTablistState,
			} );
		},

		exportInit() {
			wp.hooks.doAction( 'frmAdminImportExportInit', searchContent, initiateMultiselect );
		},

		inboxBannerInit() {
			const banner = document.getElementById( 'frm_banner' );
			if ( ! banner ) {
				return;
			}

			const dismissButton = banner.querySelector( '.frm-banner-dismiss' );
			document.addEventListener(
				'click',
				function( event ) {
					if ( event.target !== dismissButton ) {
						return;
					}

					const data = {
						action: 'frm_inbox_dismiss',
						key: banner.dataset.key,
						nonce: frmGlobal.nonce
					};
					postAjax(
						data,
						function() {
							jQuery( banner ).fadeOut(
								400,
								function() {
									banner.remove();
								}
							);
						}
					);
				}
			);
		},

		/* remove conditional logic if the field doesn't exist */
		triggerRemoveLogic( fieldID, metaName ) {
			jQuery( `#frm_logic_${ fieldID }_${ metaName } .frm_remove_tag` ).trigger( 'click' );
		},

		downloadXML( controller, ids, isTemplate ) {
			let url = `${ ajaxurl }?action=frm_${ controller }_xml&ids=${ ids }`;
			if ( isTemplate !== null ) {
				url = `${ url }&is_template=${ isTemplate }`;
			}
			location.href = url;
		},

		/**
		 * @since 5.0.04
		 */
		hooks: {
			applyFilters( hookName, ...args ) {
				return wp.hooks.applyFilters( hookName, ...args );
			},
			addFilter( hookName, callback, priority ) {
				return wp.hooks.addFilter( hookName, 'formidable', callback, priority );
			},
			doAction( hookName, ...args ) {
				return wp.hooks.doAction( hookName, ...args );
			},
			addAction( hookName, callback, priority ) {
				return wp.hooks.addAction( hookName, 'formidable', callback, priority );
			}
		},

		/**
		 * @since 6.32
		 */
		settings: {
			validate: {
				validateField,
				getRangeSettingsDefaults,
				validateNumberRangeSetting,
				validateStepSetting,
				validateRangeSettings,
			},
		},

		clearSettingsBox,
		deleteField,
		moveFieldSettings,
		syncLayoutClasses,
		ensureFieldPreview( fieldId ) {
			return builderScreen?.renderFieldPreviewNow( fieldId ) ?? null;
		},
		syncTablistState,
		initTablistKeyboard,
		applyZebraStriping,
		initModal,
		infoModal,
		offsetModalY,
		adjustConditionalLogicOptionOrders,
		installNewForm,
		toggleAddonState,
		purifyHtml,
		loadApiEmailForm,
		addMyEmailAddress,
		fillDropdownOpts,
		showSaveAndReloadModal,
		confirmLinkClick,
	};
};

window.frmAdminBuild = frmAdminBuildJS();

jQuery( document ).ready(
	() => {
		frmAdminBuild.init();

		initShowBoxIconSwap();

		document.querySelectorAll( '.frm-dropdown-menu' ).forEach( convertOldBootstrapDropdownsToBootstrap5 );
		document.querySelector( '.preview.dropdown .frm-dropdown-toggle' )?.setAttribute( 'data-bs-toggle', 'dropdown' );

		// Bootstrap 5 uses data-bs-toggle instead of data-toggle.
		document.querySelectorAll( '[data-toggle]' ).forEach( toggle => toggle.setAttribute( 'data-bs-toggle', toggle.getAttribute( 'data-toggle' ) ) );

		function convertOldBootstrapDropdownsToBootstrap5( frmDropdownMenu ) {
			frmDropdownMenu.classList.add( 'dropdown-menu' );

			const toggle = frmDropdownMenu.querySelector( '.frm-dropdown-toggle' );
			if ( toggle ) {
				if ( ! toggle.hasAttribute( 'role' ) ) {
					toggle.setAttribute( 'role', 'button' );
				}
				if ( ! toggle.hasAttribute( 'tabindex' ) ) {
					toggle.setAttribute( 'tabindex', 0 );
				}
			}

			// Convert <li> and <ul> tags.
			if ( 'UL' === frmDropdownMenu.tagName ) {
				convertBootstrapUl( frmDropdownMenu );
			}
		}

		function convertBootstrapUl( ul ) {
			let html = ul.outerHTML;
			html = html.replace( '<ul ', '<div ' );
			html = html.replace( '</ul>', '</div>' );
			html = html.replaceAll( '<li>', '<div class="dropdown-item">' );
			html = html.replaceAll( '<li class="', '<div class="dropdown-item ' );
			html = html.replaceAll( '</li>', '</div>' );
			ul.outerHTML = html;
		}
	}
);

window.frm_show_div = ( div, value, showIf, classId ) => {
	if ( value == showIf ) {
		jQuery( classId + div ).fadeIn( 'slow' ).css( 'visibility', 'visible' );
	} else {
		jQuery( classId + div ).fadeOut( 'slow' );
	}
};

window.frmCheckAll = ( checked, n ) => {
	jQuery( `input[name^="${ n }"]` ).prop( 'checked', Boolean( checked ) );
};

window.frmCheckAllLevel = ( checked, n, level ) => {
	const $kids = jQuery( `.frm_catlevel_${ level }` ).children( '.frm_checkbox' ).children( 'label' );
	$kids.children( `input[name^="${ n }"]` ).prop( 'checked', Boolean( checked ) );
};

window.frmGetFieldValues = ( fieldId, cur, rowNumber, fieldType, htmlName, callback ) => {
	if ( ! fieldId ) {
		return;
	}

	// Send the row's comparison, so the value input can match it.
	const comparison = document.querySelector( `#frm_logic_${ cur }_${ rowNumber } [name*="hide_field_cond"]` )?.value ?? '';

	jQuery.ajax( {
		type: 'POST', url: ajaxurl,
		data: `action=frm_get_field_values&current_field=${ cur }&field_id=${ encodeURIComponent( fieldId ) }&name=${ htmlName }&t=${ fieldType }&form_action=${ jQuery( 'input[name="frm_action"]' ).val() }&comparison=${ encodeURIComponent( comparison ) }&nonce=${ frmGlobal.nonce }`,
		success( msg ) {
			document.getElementById( `frm_show_selected_values_${ cur }_${ rowNumber }` ).innerHTML = msg;

			if ( 'function' === typeof callback ) {
				callback();
			}
		}
	} );
};
