module.exports = function createShortcodes( { builderPage, frmAdminJs, triggerChange, triggerEvent } ) {
	/* Customization Panel */
	function insertCode( e ) {
		/*jshint validthis:true */
		e.preventDefault();
		insertFieldCode( jQuery( this ), this.getAttribute( 'data-code' ) );
		return false;
	}

	function insertFieldCode( element, variable ) {
		let rich = false;
		let elementId = element;
		if ( typeof element === 'object' ) {
			if ( element.hasClass( 'frm_noallow' ) ) {
				return;
			}

			elementId = jQuery( element ).closest( '[data-fills]' ).attr( 'data-fills' );
			if ( elementId === undefined ) {
				elementId = element.closest( 'div' ).attr( 'class' );
				if ( elementId !== undefined ) {
					elementId = elementId.split( ' ' )[ 1 ];
				}
			}
		}

		if ( elementId === undefined ) {
			let active = document.activeElement;
			if ( active.type === 'search' ) {
				// If the search field has focus, find the correct field.
				elementId = active.id.replace( '-search-input', '' );
				if ( elementId.match( /\d/gi ) === null ) {
					active = jQuery( `.frm-single-settings:visible .${ elementId }` );
					elementId = active.attr( 'id' );
				}
			} else {
				elementId = active.id;
			}
		}

		if ( elementId ) {
			rich = jQuery( `#wp-${ elementId }-wrap.wp-editor-wrap` ).length > 0;
		}

		const contentBox = jQuery( document.getElementById( elementId ) );
		if ( element.attr( 'data-shortcode' ) === undefined && ( ! contentBox.length || contentBox.attr( 'data-shortcode' ) === undefined ) ) {
			// this helps to exclude those that don't want shortcode-like inserted content e.g. frm-pro's summary field
			const doShortcode = element.parents( 'ul.frm_code_list' ).attr( 'data-shortcode' );
			if ( doShortcode === 'undefined' || doShortcode !== 'no' ) {
				variable = `[${ variable }]`;
			}
		}

		if ( rich ) {
			wpActiveEditor = elementId;
		}

		if ( ! contentBox.length ) {
			return false;
		}

		if ( variable === '[default-html]' || variable === '[default-plain]' ) {
			let plainText = 0;
			if ( variable === '[default-plain]' ) {
				plainText = 1;
			}
			jQuery.ajax( {
				type: 'POST', url: ajaxurl,
				data: {
					action: 'frm_get_default_html',
					form_id: jQuery( 'input[name="id"]' ).val(),
					plain_text: plainText,
					nonce: frmGlobal.nonce
				},
				elementId,
				success( msg ) {
					const codeTabActive = jQuery( `#wp-${ elementId }-wrap` ).hasClass( 'html-active' );
					if ( rich && ! codeTabActive ) {
						const paragraph = document.createElement( 'p' );
						paragraph.innerText = msg;
						send_to_editor( paragraph.innerHTML );
					} else {
						insertContent( contentBox, msg );
					}
				}
			} );
		} else {
			variable = maybeAddSanitizeUrlToShortcodeVariable( variable, element, contentBox );
			if ( rich ) {
				send_to_editor( variable );
			} else {
				insertContent( contentBox, variable );
			}
		}
		return false;
	}

	function maybeAddSanitizeUrlToShortcodeVariable( variable, element, contentBox ) {
		if ( 'object' !== typeof element || ! ( element instanceof jQuery ) || 0 !== contentBox[ 0 ].id.indexOf( 'success_url_' ) ) {
			return variable;
		}

		element = element[ 0 ];
		if ( ! element.closest( '#frm-insert-fields-box' ) ) {
			// Only add sanitize_url=1 to field shortcodes.
			return variable;
		}

		if ( ! element.parentNode.classList.contains( 'frm_insert_url' ) ) {
			variable = variable.replace( ']', ' sanitize_url=1]' );
		}

		return variable;
	}

	function insertContent( contentBox, variable ) {
		if ( document.selection ) {
			contentBox[ 0 ].focus();
			document.selection.createRange().text = variable;
		} else {
			const obj = contentBox[ 0 ];
			const { selectionEnd } = obj;

			variable = maybeFormatInsertedContent( contentBox, variable, obj.selectionStart, selectionEnd );

			obj.value = obj.value.substr( 0, obj.selectionStart ) + variable + obj.value.substr( obj.selectionEnd, obj.value.length );

			const cursorPos = selectionEnd + variable.length;

			maybeRemoveLayoutClasses( obj, variable );

			obj.focus();
			obj.setSelectionRange( cursorPos, cursorPos );
		}
		triggerChange( contentBox );
	}

	/**
	 * When a layout class is added, remove any previous layout classes to avoid conflicts.
	 * We only expect one layout class to exist for a given field.
	 * For example, if a field has frm_half and we set it to frm_third, frm_half will be removed.
	 *
	 * @since 6.11
	 *
	 * @param {HTMLElement} obj
	 * @param {string}      variable
	 * @return {void}
	 */
	function maybeRemoveLayoutClasses( obj, variable ) {
		if ( ! obj.classList.contains( 'frm_classes' ) || ! isALayoutClass( variable ) ) {
			return;
		}

		const removeClasses = obj.value.split( ' ' ).filter( isALayoutClass );
		if ( removeClasses.length ) {
			obj.value = maybeRemoveClasses( obj.value, removeClasses, variable.trim() );
		}
	}

	/**
	 * Check if a given class is a layout class.
	 *
	 * @since 6.11
	 *
	 * @param {string} className The class name to check.
	 * @return {boolean} True if the class is a layout class.
	 */
	function isALayoutClass( className ) {
		const layoutClasses = [ 'frm_half', 'frm_third', 'frm_two_thirds', 'frm_fourth', 'frm_three_fourths', 'frm_fifth', 'frm_sixth', 'frm2', 'frm3', 'frm4', 'frm6', 'frm8', 'frm9', 'frm10', 'frm12' ];
		return layoutClasses.includes( className.trim() );
	}

	/**
	 * @since 6.11
	 *
	 * @param {string} beforeValue   The original value string.
	 * @param {Array}  removeClasses Classes to remove.
	 * @param {string} variable      The CSS variable name.
	 * @return {string}              The value with specified classes removed.
	 */
	function maybeRemoveClasses( beforeValue, removeClasses, variable ) {
		const currentClasses = beforeValue.split( ' ' ).filter(
			currentClass => {
				currentClass = currentClass.trim();
				return currentClass.length && ! removeClasses.includes( currentClass );
			}
		);
		if ( ! currentClasses.includes( variable ) ) {
			currentClasses.push( variable );
		}
		return currentClasses.join( ' ' );
	}

	function maybeFormatInsertedContent( input, textToInsert, selectionStart, selectionEnd ) {
		const separator = input.data( 'sep' );
		if ( undefined === separator ) {
			return textToInsert;
		}

		const value = input.val();

		if ( ! value.trim().length ) {
			return textToInsert;
		}

		const startPattern = new RegExp( `${ separator }\\s*$` );
		const endPattern = new RegExp( `^\\s*${ separator }` );

		if ( value.substr( 0, selectionStart ).trim().length && false === startPattern.test( value.substr( 0, selectionStart ) ) ) {
			textToInsert = separator + textToInsert;
		}

		if ( value.substr( selectionEnd, value.length ).trim().length && false === endPattern.test( value.substr( selectionEnd, value.length ) ) ) {
			textToInsert += separator;
		}

		return textToInsert;
	}

	function resetLogicBuilder() {
		/*jshint validthis:true */
		const id = document.getElementById( 'frm-id-condition' );
		const key = document.getElementById( 'frm-key-condition' );

		if ( this.value === 'id' ) {
			id.classList.remove( 'frm_hidden' );
			key.classList.add( 'frm_hidden' );
			triggerEvent( key, 'change' );
		} else {
			id.classList.add( 'frm_hidden' );
			key.classList.remove( 'frm_hidden' );
			triggerEvent( id, 'change' );
		}
	}

	function setLogicExample() {
		let idKey = document.getElementById( 'frm-id-key-condition-id' ).checked ? 'frm-id-condition' : 'frm-key-condition';
		const is = document.getElementById( 'frm-is-condition' ).value;
		const text = document.getElementById( 'frm-text-condition' ).value;
		const result = document.getElementById( 'frm-insert-condition' );

		idKey = document.getElementById( idKey );
		const field = idKey.options[ idKey.selectedIndex ].value;
		const code = `if ${ field } ${ is }="${ text }"]`;
		result.setAttribute( 'data-code', `${ code + frmAdminJs.conditional_text }[/if ${ field }` );
		result.innerHTML = `[${ code }[/if ${ field }]`;
	}

	/**
	 * Gets data from href or xlink:href of the given element.
	 *
	 * @param {HTMLElement} element HTML element.
	 *
	 * @return {string} The href attribute value.
	 */
	function getSVGHref( element ) {
		return element.getAttribute( 'href' ) || element.getAttributeNS( 'http://www.w3.org/1999/xlink', 'href' );
	}

	function maybeShowModal( input ) {
		let moreIcon;
		if ( input.parentNode.parentNode.classList.contains( 'frm_has_shortcodes' ) ) {
			hideShortcodes();
			moreIcon = getIconForInput( input );
			if ( moreIcon.tagName === 'use' ) {
				moreIcon = moreIcon.firstElementChild;

				if ( ! getSVGHref( moreIcon ).includes( 'frm_close_icon' ) ) {
					showShortcodeBox( moreIcon, 'nofocus' );
				}
			} else if ( ! moreIcon.classList.contains( 'frm_close_icon' ) ) {
				showShortcodeBox( moreIcon, 'nofocus' );
			}
		}
	}

	function showShortcodes( e ) {
		/*jshint validthis:true */
		e.preventDefault();
		e.stopPropagation();

		showShortcodeBox( this );
	}

	function updateShortcodesPopupPosition( target ) {
		let moreIcon;
		if ( target instanceof Event ) {
			const useElements = document.querySelectorAll( '.frm-single-settings .frm-show-box.frmsvg use' );
			const openTrigger = Array.from( useElements ).find( use => use.getAttribute( 'href' ) === '#frm_close_icon' );
			if ( openTrigger === undefined ) {
				return;
			}
			moreIcon = openTrigger.parentElement;
		} else {
			moreIcon = target;
		}

		const moreIconPosition = moreIcon.getBoundingClientRect();
		const shortCodesPopup = document.getElementById( 'frm_adv_info' );
		const parentPos = shortCodesPopup.parentElement.getBoundingClientRect();

		shortCodesPopup.style.top = `${ moreIconPosition.top - parentPos.top + 32 }px`;
		shortCodesPopup.style.left = `${ moreIconPosition.left - parentPos.left - 280 }px`;
	}

	function showShortcodeBox( moreIcon, shouldFocus ) {
		const input = getInputForIcon( moreIcon );
		const box = document.getElementById( 'frm_adv_info' );
		let classes = moreIcon.className;

		if ( moreIcon.tagName === 'svg' ) {
			moreIcon = moreIcon.firstElementChild;
		}
		if ( moreIcon.tagName === 'use' ) {
			classes = getSVGHref( moreIcon );
		}

		if ( classes.includes( 'frm_close_icon' ) ) {
			hideShortcodes( box );
		} else {
			hydrateDeferredCodeListIcons( box );
			updateShortcodesPopupPosition( moreIcon );

			jQuery( '.frm_code_list a' ).removeClass( 'frm_noallow' );
			if ( input.classList.contains( 'frm_not_email_to' ) ) {
				jQuery( '#frm-insert-fields-box .frm_code_list li:not(.show_frm_not_email_to) a' ).addClass( 'frm_noallow' );
			} else if ( input.classList.contains( 'frm_not_email_subject' ) ) {
				jQuery( '.frm_code_list li.hide_frm_not_email_subject a' ).addClass( 'frm_noallow' );
			}

			box.setAttribute( 'data-fills', input.id );
			box.style.display = 'block';

			if ( moreIcon.tagName === 'use' ) {
				if ( moreIcon.hasAttributeNS( 'http://www.w3.org/1999/xlink', 'href' ) ) {
					moreIcon.setAttributeNS( 'http://www.w3.org/1999/xlink', 'href', '#frm_close_icon' );
				} else {
					const newMoreIcon = document.createElementNS( 'http://www.w3.org/2000/svg', 'use' );
					newMoreIcon.setAttributeNS( 'http://www.w3.org/1999/xlink', 'href', '#frm_close_icon' );
					moreIcon.parentNode.replaceChild( newMoreIcon, moreIcon );
				}
			} else {
				moreIcon.className = classes.replace( 'frm_more_horiz_solid_icon', 'frm_close_icon' );
			}

			if ( shouldFocus !== 'nofocus' ) {
				if ( 'none' !== input.style.display ) {
					input.focus();
				} else {
					jQuery( tinymce.get( input.id ) ).trigger( 'focus' );
				}
			}
			showOrHideContextualShortcodes( input );
		}
	}

	/**
	 * Add field shortcode icons when their code list is first shown.
	 *
	 * @since 7.0
	 * @param {HTMLElement} container The opened shortcode panel.
	 * @return {void}
	 */
	function hydrateDeferredCodeListIcons( container ) {
		const template = container.querySelector( 'template.frm-code-list-icons' );
		if ( ! template ) {
			return;
		}

		// FrmFormsHelper::print_deferred_code_list_icons() prints each field type's icon once.
		const icons = {};
		template.content.querySelectorAll( '[data-frm-icon-key]' ).forEach( wrapper => {
			icons[ wrapper.dataset.frmIconKey ] = wrapper.firstElementChild;
		} );

		container.querySelectorAll( '.frm_customize_field_list [data-frm-icon]' ).forEach( item => {
			const icon = icons[ item.dataset.frmIcon ];
			if ( icon ) {
				item.querySelectorAll( ':scope > a.frm_insert_code' ).forEach( anchor => {
					// eslint-disable-next-line formidable/prefer-document-fragment -- Each icon belongs to a different link.
					anchor.prepend( icon.cloneNode( true ) );
				} );
			}
			item.removeAttribute( 'data-frm-icon' );
		} );
	}

	/**
	 * Returns true if a shortcode could be shown in the search result.
	 *
	 * @since 6.16.3
	 *
	 * @param {HTMLElement} item
	 * @return {boolean} True if the shortcode can be shown.
	 */
	function checkContextualShortcode( item ) {
		if ( frmAdminJs.contextualShortcodes.length === 0 ) {
			return true;
		}
		return ! isContextualShortcode( item ) || canShowContextualShortcode( item );
	}

	/**
	 * Returns true if a shortcode is contextual to fields.
	 *
	 * @since 6.16.3
	 *
	 * @param {HTMLElement} item
	 * @return {boolean} True if the shortcode is contextual.
	 */
	function isContextualShortcode( item ) {
		const anchor = item.querySelector( 'a' );
		if ( ! anchor ) {
			return false;
		}

		const shortcode = anchor.dataset.code;
		return frmAdminJs.contextualShortcodes.address.includes( shortcode ) || frmAdminJs.contextualShortcodes.body.includes( shortcode );
	}

	/**
	 * @since 6.16.3
	 *
	 * @param {HTMLElement} item The shortcode list item.
	 * @return {boolean} True if the contextual shortcode can be shown.
	 */
	function canShowContextualShortcode( item ) {
		const shortcode = item.querySelector( 'a' ).dataset.code;
		const inputId = document.getElementById( 'frm_adv_info' ).dataset.fills;
		const input = document.getElementById( inputId );
		const { contextualShortcodes } = frmAdminJs;
		if ( contextualShortcodes.address.includes( shortcode ) ) {
			return input.matches( contextualShortcodes.addressSelector );
		}
		return input.matches( contextualShortcodes.bodySelector );
	}

	/**
	 * @since 6.16.3
	 *
	 * @param {HTMLElement} input
	 * @return {void}
	 */
	function showOrHideContextualShortcodes( input ) {
		[ 'address', 'body' ].forEach( type => {
			toggleContextualShortcodes( input, type );
		} );
	}

	/**
	 * @since 6.16.3
	 *
	 * @param {HTMLElement} input
	 * @param {string}      type
	 *
	 * @return {void}
	 */
	function toggleContextualShortcodes( input, type ) {
		const selector = frmAdminJs.contextualShortcodes[ `${ type }Selector` ];
		const contextualShortcodes = frmAdminJs.contextualShortcodes[ type ];
		const shouldShowShortcodes = input.matches( selector );
		for ( const shortcode of contextualShortcodes ) {
			const shortcodeLi = document.querySelector( `#frm-adv-info-tab .frm_code_list [data-code="${ shortcode }"]` )?.closest( 'li' );
			shortcodeLi?.classList.toggle( 'frm_hidden', ! shouldShowShortcodes );
		}
	}

	/**
	 * Returns shortcodes that are contextual to the current input field.
	 *
	 * @since 6.16.3
	 *
	 * @return {Array} Array of contextual shortcode data.
	 */
	function getContextualShortcodes() {
		let contextualShortcodes = document.getElementById( 'frm_adv_info' )?.dataset.contextualShortcodes;
		if ( ! contextualShortcodes ) {
			return [];
		}
		contextualShortcodes = JSON.parse( contextualShortcodes );
		contextualShortcodes.addressSelector = '[id^=email_to], [id^=from_], [id^=cc], [id^=bcc]';
		contextualShortcodes.bodySelector = '[id^=email_message_]';
		return contextualShortcodes;
	}

	/**
	 * Get the input box for the selected icon or calculation field.
	 *
	 * @param {Element} moreIcon The icon element
	 * @return {Element} The associated input or textarea
	 */
	function getInputForIcon( moreIcon ) {
		if ( moreIcon.classList.contains( 'frm-input-icon' ) ) {
			return moreIcon.previousElementSibling;
		}

		// For regular fields
		let input = moreIcon.nextElementSibling;
		while ( input && (
			( input.tagName !== 'INPUT' && input.tagName !== 'TEXTAREA' ) || input.classList.contains( 'frm-token-input-field' )
		) ) {
			input = getInputForIcon( input );
		}

		// For calculation fields
		if ( ! input ) {
			input = moreIcon.closest( '.frm-field-formula' )?.querySelector( '.frm-calc-field' );
		}

		return input;
	}

	/**
	 * Get the ... icon for the selected input box.
	 * @param {HTMLElement} input The input element.
	 */
	function getIconForInput( input ) {
		if ( input.nextElementSibling?.classList.contains( 'frm-input-icon' ) ) {
			return input.nextElementSibling;
		}

		let moreIcon = input.previousElementSibling;

		while ( moreIcon !== null && moreIcon.tagName !== 'I' && moreIcon.tagName !== 'svg' ) {
			moreIcon = getIconForInput( moreIcon );
		}

		return moreIcon;
	}

	function hideShortcodes( box ) {
		if ( box === undefined ) {
			box = document.getElementById( 'frm_adv_info' );
			if ( ! box ) {
				return;
			}
		}

		if ( document.getElementById( 'frm_dyncontent' ) !== null ) {
			// Don't run when in the sidebar.
			return;
		}

		box.style.display = 'none';

		const closeIcons = document.querySelectorAll( '.frm-show-box.frm_close_icon' );
		for ( let iconIndex = 0; iconIndex < closeIcons.length; iconIndex++ ) {
			closeIcons[ iconIndex ].classList.remove( 'frm_close_icon' );
			closeIcons[ iconIndex ].classList.add( 'frm_more_horiz_solid_icon' );
		}

		const closeSvg = document.querySelectorAll( '.frm_has_shortcodes use' );
		for ( let svgIndex = 0; svgIndex < closeSvg.length; svgIndex++ ) {
			if ( getSVGHref( closeSvg[ svgIndex ] ) === '#frm_close_icon' ) {
				if ( closeSvg[ svgIndex ].closest( '.frm_remove_field' ) ) {
					// Don't change the icon for the email fields remove button.
					continue;
				}
				closeSvg[ svgIndex ].setAttributeNS( 'http://www.w3.org/1999/xlink', 'href', '#frm_more_horiz_solid_icon' );
			}
		}
	}

	function maybeHideShortcodes( e ) {
		if ( ! builderPage ) {
			e.stopPropagation();
		}

		if ( e.target.classList.contains( 'frm-show-box' ) || e.target.parentElement?.classList.contains( 'frm-show-box' ) ) {
			return;
		}

		const sidebar = document.getElementById( 'frm_adv_info' );
		if ( ! sidebar ) {
			return;
		}

		if ( sidebar.dataset.fills === e.target.id && e.target.id !== undefined ) {
			return;
		}

		const isChild = e.target.closest( '#frm_adv_info' );

		if ( ! isChild && sidebar.style.display !== 'none' ) {
			hideShortcodes( sidebar );
		}
	}

	return {
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
	};
};
