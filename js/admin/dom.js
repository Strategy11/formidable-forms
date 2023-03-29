( function() {
	/** globals frmGlobal */

	let __;

	if ( 'undefined' === typeof wp || 'undefined' === typeof wp.i18n || 'function' !== typeof wp.i18n.__ ) {
		__ = text => text;
	} else {
		__ = wp.i18n.__;
	}

	const modal = {
		maybeCreateModal: ( id, { title, content, footer, width } = {}) => {
			let modal = document.getElementById( id );

			if ( ! modal ) {
				modal = createEmptyModal( id );

				const titleElement = div({
					className: 'frm-modal-title'
				});

				if ( 'string' === typeof title ) {
					titleElement.textContent = title;
				}

				const a = tag(
					'a',
					{
						child: svg({ href: '#frm_close_icon' }),
						className: 'dismiss'
					}
				);
				const postbox = modal.querySelector( '.postbox' );

				postbox.appendChild(
					div({
						className: 'frm_modal_top',
						children: [
							titleElement,
							div({ child: a })
						]
					})
				);
				postbox.appendChild(
					div({ className: 'frm_modal_content' })
				);

				if ( footer ) {
					postbox.appendChild(
						div({ className: 'frm_modal_footer' })
					);
				}
			} else if ( 'string' === typeof title ) {
				const titleElement = modal.querySelector( '.frm-modal-title' );
				titleElement.textContent = title;
			}

			if ( ! content && ! footer ) {
				makeModalIntoADialogAndOpen( modal, { width });
				return modal;
			}

			const postbox = modal.querySelector( '.postbox' );
			const modalHelper = getModalHelper( modal, postbox );

			if ( content ) {
				modalHelper( content, 'frm_modal_content' );
			}

			if ( footer ) {
				modalHelper( footer, 'frm_modal_footer' );
			}

			makeModalIntoADialogAndOpen( modal );
			return modal;
		},
		footerButton: args => {
			const output = a( args );
			output.setAttribute( 'role', 'button' );
			output.setAttribute( 'tabindex', 0 );
			if ( args.buttonType ) {
				output.classList.add( 'button' );

				if ( ! args.noDismiss && -1 !== [ 'red', 'primary' ].indexOf( args.buttonType ) ) {
					// Primary and red buttons close modals by default on click.
					// To disable this default behaviour you can use the noDismiss: 1 arg.
					output.classList.add( 'dismiss' );
				}

				switch ( args.buttonType ) {
					case 'red':
						output.classList.add( 'frm-button-red', 'frm-button-primary' );
						break;
					case 'primary':
						output.classList.add( 'button-primary', 'frm-button-primary' );
						break;
					case 'secondary':
						output.classList.add( 'button-secondary', 'frm-button-secondary' );
						output.style.marginRight = '10px';
						break;
					case 'cancel':
						output.classList.add( 'button-secondary', 'frm-modal-cancel' );
						break;
				}
			}
			return output;
		}
	};

	const ajax = {
		doJsonFetch: async function( action ) {
			let targetUrl = ajaxurl + '?action=frm_' + action;
			if ( -1 === targetUrl.indexOf( 'nonce=' ) ) {
				targetUrl += '&nonce=' + frmGlobal.nonce;
			}
			const response = await fetch( targetUrl );
			const json = await response.json();
			if ( ! json.success ) {
				return Promise.reject( json.data || 'JSON result is not successful' );
			}
			return Promise.resolve( json.data );
		},
		doJsonPost: async function( action, formData, { signal } = {}) {
			formData.append( 'nonce', frmGlobal.nonce );
			const init = {
				method: 'POST',
				body: formData
			};
			if ( signal ) {
				init.signal = signal;
			}
			const response = await fetch( ajaxurl + '?action=frm_' + action, init );
			const json     = await response.json();
			if ( ! json.success ) {
				return Promise.reject( json.data || 'JSON result is not successful' );
			}
			return Promise.resolve( 'undefined' !== typeof json.data ? json.data : json );
		}
	};

	const multiselect = {
		init: function() {
			const $select = jQuery( this );
			const id = $select.is( '[id]' ) ? $select.attr( 'id' ).replace( '[]', '' ) : false;

			let labelledBy = id ? jQuery( '#for_' + id ) : false;
			labelledBy     = id && labelledBy.length ? 'aria-labelledby="' + labelledBy.attr( 'id' ) + '"' : '';

			$select.multiselect({
				templates: {
					popupContainer: '<div class="multiselect-container frm-dropdown-menu"></div>',
					option: '<button type="button" class="multiselect-option dropdown-item frm_no_style_button"></button>',
					button: '<button type="button" class="multiselect dropdown-toggle btn" data-toggle="dropdown" ' + labelledBy + '><span class="multiselect-selected-text"></span> <b class="caret"></b></button>'
				},
				buttonContainer: '<div class="btn-group frm-btn-group dropdown" />',
				nonSelectedText: '',
				onDropdownShown: function( event ) {
					const action = jQuery( event.currentTarget.closest( '.frm_form_action_settings, #frm-show-fields' ) );
					if ( action.length ) {
						jQuery( '#wpcontent' ).on( 'click', function() {
							if ( jQuery( '.multiselect-container.frm-dropdown-menu' ).is( ':visible' ) ) {
								jQuery( event.currentTarget ).removeClass( 'open' );
							}
						});
					}

					const $dropdown = $select.next( '.frm-btn-group.dropdown' );
					$dropdown.find( '.dropdown-item' ).each(
						function() {
							const option         = this;
							const dropdownInput  = option.querySelector( 'input[type="checkbox"], input[type="radio"]' );
							if ( dropdownInput ) {
								option.setAttribute( 'role', 'checkbox' );
								option.setAttribute( 'aria-checked', dropdownInput.checked ? 'true' : 'false' );
							}
						}
					);
				},
				onChange: function( $option, checked ) {
					$select.trigger( 'frm-multiselect-changed', $option, checked );

					const $dropdown     = $select.next( '.frm-btn-group.dropdown' );
					const optionValue   = $option.val();
					const $dropdownItem = $dropdown.find( 'input[value="' + optionValue + '"]' ).closest( 'button.dropdown-item' );
					if ( $dropdownItem.length ) {
						$dropdownItem.attr( 'aria-checked', checked ? 'true' : 'false' );

						// Delay a focus event so the screen reader reads the option value again.
						// Without this, and without the setTimeout, it only reads "checked" or "unchecked".
						setTimeout( () => $dropdownItem.get( 0 ).focus(), 0 );
					}
				}
			});
		}
	};

	const bootstrap = {
		setupBootstrapDropdowns( callback ) {
			if ( ! window.bootstrap || ! window.bootstrap.Dropdown ) {
				return;
			}

			window.bootstrap.Dropdown._getParentFromElement = getParentFromElement;
			window.bootstrap.Dropdown.prototype._getParentFromElement = getParentFromElement;

			function getParentFromElement( element ) {
				let parent;
				const selector = window.bootstrap.Util.getSelectorFromElement( element );

				if ( selector ) {
					parent = document.querySelector( selector );
				}

				const result = parent || element.parentNode;
				const frmDropdownMenu = result.querySelector( '.frm-dropdown-menu' );

				if ( ! frmDropdownMenu ) {
					// Not a formidable dropdown, treat like Bootstrap does normally.
					return result;
				}

				// Temporarily add dropdown-menu class so bootstrap can initialize.
				frmDropdownMenu.classList.add( 'dropdown-menu' );
				setTimeout(
					function() {
						frmDropdownMenu.classList.remove( 'dropdown-menu' );
					},
					0
				);

				if ( 'function' === typeof callback ) {
					callback( frmDropdownMenu );
				}

				return result;
			}
		},
		multiselect
	};

	const autocomplete = {
		initSelectionAutocomplete: function() {
			if ( jQuery.fn.autocomplete ) {
				autocomplete.initAutocomplete( 'page' );
				autocomplete.initAutocomplete( 'user' );
			}
		},
		/**
		 * Init autocomplete.
		 *
		 * @since 4.10.01 Add container param to init autocomplete elements inside an element.
		 *
		 * @param {String} type Type of data. Accepts `page` or `user`.
		 * @param {String|Object} container Container class or element. Default is null.
		 */
		initAutocomplete: function( type, container ) {
			const basedUrlParams = '?action=frm_' + type + '_search&nonce=' + frmGlobal.nonce;
			const elements       = ! container ? jQuery( '.frm-' + type + '-search' ) : jQuery( container ).find( '.frm-' + type + '-search' );

			elements.each( initAutocompleteForElement );

			function initAutocompleteForElement() {
				let urlParams = basedUrlParams;
				const element = jQuery( this );

				// Check if a custom post type is specific.
				if ( element.attr( 'data-post-type' ) ) {
					urlParams += '&post_type=' + element.attr( 'data-post-type' );
				}

				element.autocomplete({
					delay: 100,
					minLength: 0,
					source: ajaxurl + urlParams,
					change: autocomplete.selectBlank,
					select: autocomplete.completeSelectFromResults,
					focus: () => false,
					position: {
						my: 'left top',
						at: 'left bottom',
						collision: 'flip'
					},
					response: function( event, ui ) {
						if ( ! ui.content.length ) {
							const noResult = {
								value: '',
								label: frm_admin_js.no_items_found
							};
							ui.content.push( noResult );
						}
					},
					create: function() {
						let $container = jQuery( this ).parent();

						if ( $container.length === 0 ) {
							$container = 'body';
						}

						jQuery( this ).autocomplete( 'option', 'appendTo', $container );
					}
				})
				.on( 'focus', function() {
					// Show options on click to make it work more like a dropdown.
					if ( this.value === '' || this.nextElementSibling.value < 1 ) {
						jQuery( this ).autocomplete( 'search', this.value );
					}
				})
				.data( 'ui-autocomplete' )._renderItem = function( ul, item ) {
					return jQuery( '<li>' )
					.attr( 'aria-label', item.label )
					.append( jQuery( '<div>' ).text( item.label ) )
					.appendTo( ul );
				};
			}
		},

		selectBlank: function( e, ui ) {
			if ( ui.item === null ) {
				this.nextElementSibling.value = '';
			}
		},

		completeSelectFromResults: function( e, ui ) {
			e.preventDefault();
			this.value = ui.item.value === '' ? '' : ui.item.label;
			this.nextElementSibling.value = ui.item.value;
		}
	};

	const search = {
		wrapInput: ( searchInput, labelText ) => {
			const label = tag(
				'label',
				{
					className: 'screen-reader-text',
					text: labelText
				}
			);
			label.setAttribute( 'for', searchInput.id );
			return tag(
				'p',
				{
					className: 'frm-search',
					children: [
						label,
						span({ className: 'frmfont frm_search_icon' }),
						searchInput
					]
				}
			);
		},
		newSearchInput: ( id, placeholder, targetClassName, args = {}) => {
			const input = getAutoSearchInput( id, placeholder );
			const wrappedSearch = search.wrapInput( input, placeholder );
			search.init( input, targetClassName, args );

			function getAutoSearchInput( id, placeholder ) {
				const className = 'frm-search-input frm-auto-search frm-w-full';
				const inputArgs = { id, className };
				const input = tag( 'input', inputArgs );
				input.setAttribute( 'placeholder', placeholder );
				return input;
			}

			return wrappedSearch;
		},
		init: ( input, targetClassName, { handleSearchResult } = {}) => {
			input.setAttribute( 'type', 'search' );
			input.setAttribute( 'autocomplete', 'off' );

			input.addEventListener( 'input', handleSearch );
			input.addEventListener( 'search', handleSearch );
			input.addEventListener( 'change', handleSearch );

			function handleSearch() {
				const searchText = input.value.toLowerCase();
				const notEmptySearchText = searchText !== '';
				const items = Array.from( document.getElementsByClassName( targetClassName ) );

				let foundSomething = false;
				items.forEach( toggleSearchClassesForItem );
				if ( 'function' === typeof handleSearchResult ) {
					handleSearchResult({ foundSomething, notEmptySearchText });
				}

				function toggleSearchClassesForItem( item ) {
					let itemText;

					if ( item.hasAttribute( 'frm-search-text' ) ) {
						itemText = item.getAttribute( 'frm-search-text' );
					} else {
						itemText = item.innerText.toLowerCase();
						item.setAttribute( 'frm-search-text', itemText );
					}

					const hide = notEmptySearchText && -1 === itemText.indexOf( searchText );
					item.classList.toggle( 'frm_hidden', hide );

					const isSearchResult = ! hide && notEmptySearchText;
					if ( isSearchResult ) {
						foundSomething = true;
					}
					item.classList.toggle( 'frm-search-result', isSearchResult );
				}
			}
		}
	};

	const util = {
		debounce: ( func, wait = 100 ) => {
			let timeout;
			return function( ...args ) {
				clearTimeout( timeout );
				timeout = setTimeout(
					() => func.apply( this, args ),
					wait
				);
			};
		},
		onClickPreventDefault: ( element, callback ) => {
			const listener = event => {
				event.preventDefault();
				callback( event );
			};
			element.addEventListener( 'click', listener );
		},

		/**
		 * Does the same as jQuery( document ).on( 'event', 'selector', handler ).
		 *
		 * @since 6.0
		 *
		 * @param {String}         event    Event name.
		 * @param {String}         selector Selector.
		 * @param {Function}       handler  Handler.
		 * @param {Boolean|Object} options  Options to be added to `addEventListener()` method. Default is `false`.
		 */
		documentOn: ( event, selector, handler, options ) => {
			if ( 'undefined' === typeof options ) {
				options = false;
			}

			document.addEventListener( event, function( e ) {
				let target;

				// loop parent nodes from the target to the delegation node.
				for ( target = e.target; target && target != this; target = target.parentNode ) {
					if ( target && target.matches && target.matches( selector ) ) {
						handler.call( target, e );
						break;
					}
				}
			}, options );
		}
	};

	const wysiwyg = {
		init( editor, { setupCallback, height, addFocusEvents } = {}) {
			if ( isTinyMceActive() ) {
				setTimeout( resetTinyMce, 0 );
			} else {
				initQuickTagsButtons();
			}

			setUpTinyMceVisualButtonListener();
			setUpTinyMceHtmlButtonListener();

			function initQuickTagsButtons() {
				if ( 'function' !== typeof window.quicktags || typeof window.QTags.instances[ editor.id ] !== 'undefined' ) {
					return;
				}

				const id = editor.id;
				window.quicktags({
					name: 'qt_' + id,
					id: id,
					canvas: editor,
					settings: { id },
					toolbar: document.getElementById( 'qt_' + id + '_toolbar' ),
					theButtons: {}
				});
			}

			function initRichText() {
				const key = Object.keys( tinyMCEPreInit.mceInit )[0];
				const orgSettings = tinyMCEPreInit.mceInit[ key ];

				const settings = Object.assign(
					{},
					orgSettings,
					{
						selector: '#' + editor.id,
						body_class: orgSettings.body_class.replace( key, editor.id )
					}
				);

				settings.setup = editor => {
					if ( addFocusEvents ) {
						function focusInCallback() {
							jQuery( editor.targetElm ).trigger( 'focusin' );
							editor.off( 'focusin', '**' );
						}

						editor.on( 'focusin', focusInCallback );

						editor.on( 'focusout', function() {
							editor.on( 'focusin', focusInCallback );
						});
					}
					if ( setupCallback ) {
						setupCallback( editor );
					}
				};

				if ( height ) {
					settings.height = height;
				}

				tinymce.init( settings );
			}

			function removeRichText() {
				tinymce.EditorManager.execCommand( 'mceRemoveEditor', true, editor.id );
			}

			function resetTinyMce() {
				removeRichText();
				initRichText();
			}

			function isTinyMceActive() {
				const id = editor.id;
				const wrapper = document.getElementById( 'wp-' + id + '-wrap' );
				return null !== wrapper && wrapper.classList.contains( 'tmce-active' );
			}

			function setUpTinyMceVisualButtonListener() {
				jQuery( document ).on(
					'click', '#' + editor.id + '-html',
					function() {
						editor.style.visibility = 'visible';
						initQuickTagsButtons( editor );
					}
				);
			}

			function setUpTinyMceHtmlButtonListener() {
				jQuery( '#' + editor.id + '-tmce' ).on( 'click', handleTinyMceHtmlButtonClick );
			}

			function handleTinyMceHtmlButtonClick() {
				if ( isTinyMceActive() ) {
					resetTinyMce();
				} else {
					initRichText();
				}

				const wrap = document.getElementById( 'wp-' + editor.id + '-wrap' );
				wrap.classList.add( 'tmce-active' );
				wrap.classList.remove( 'html-active' );
			}
		}
	};

	function getModalHelper( modal, appendTo ) {
		return function( child, uniqueClassName ) {
			let element = modal.querySelector( '.' + uniqueClassName );
			if ( null === element ) {
				element = div({
					child: child,
					className: uniqueClassName
				});
				appendTo.appendChild( element );
			} else {
				redraw( element, child );
			}
		};
	}

	function createEmptyModal( id ) {
		const modal = div({ id, className: 'frm-modal' });
		const postbox = div({ className: 'postbox' });
		const metaboxHolder = div({ className: 'metabox-holder', child: postbox });
		modal.appendChild( metaboxHolder );
		document.body.appendChild( modal );
		return modal;
	}

	function makeModalIntoADialogAndOpen( modal, { width } = {}) {
		const bodyWithModalClassName = 'frm-body-with-open-modal';

		const $modal = jQuery( modal );
		if ( ! $modal.hasClass( 'frm-dialog' ) ) {
			$modal.dialog({
				dialogClass: 'frm-dialog',
				modal: true,
				autoOpen: false,
				closeOnEscape: true,
				width: width || '550px',
				resizable: false,
				draggable: false,
				open: function() {
					jQuery( '.ui-dialog-titlebar' ).addClass( 'frm_hidden' ).removeClass( 'ui-helper-clearfix' );
					jQuery( '#wpwrap' ).addClass( 'frm_overlay' );
					jQuery( '.frm-dialog' ).removeClass( 'ui-widget ui-widget-content ui-corner-all' );

					modal.classList.remove( 'ui-dialog-content', 'ui-widget-content' );

					$modal.on( 'click', 'a.dismiss', function( event ) {
						event.preventDefault();
						$modal.dialog( 'close' );
					});

					const overlay = document.querySelector( '.ui-widget-overlay' );
					if ( overlay ) {
						overlay.addEventListener(
							'click',
							function( event ) {
								event.preventDefault();
								$modal.dialog( 'close' );
							}
						);
					}
				},
				close: function() {
					document.body.classList.remove( bodyWithModalClassName );
					jQuery( '#wpwrap' ).removeClass( 'frm_overlay' );
					jQuery( '.spinner' ).css( 'visibility', 'hidden' );
				}
			});
		}

		document.body.classList.add( bodyWithModalClassName );

		$modal.dialog( 'open' );
		return $modal;
	}

	function div( args ) {
		return tag( 'div', args );
	}

	function span( args ) {
		return tag( 'span', args );
	}

	function a( args = {}) {
		const anchor = tag( 'a', args );
		anchor.setAttribute( 'href', 'string' === typeof args.href ? args.href : '#' );
		if ( 'string' === typeof args.target ) {
			anchor.target = args.target;
		}
		return anchor;
	}

	function img( args = {}) {
		const output = tag( 'img', args );
		if ( 'string' === typeof args.src ) {
			output.setAttribute( 'src', args.src );
		}
		return output;
	}

	/**
	 * Get a labelled text input and a matching label.
	 *
	 * @since 6.0
	 *
	 * @param {String} inputId
	 * @param {String} labelText
	 * @param {String} inputName
	 * @returns {Element}
	 */
	function labelledTextInput( inputId, labelText, inputName ) {
		const label = tag( 'label', labelText );
		label.setAttribute( 'for', inputId );

		const input = tag(
			'input',
			{
				id: inputId,
				className: 'frm_long_input'
			}
		);
		input.type = 'text';
		input.setAttribute( 'name', inputName );

		return div({ children: [ label, input ] });
	}

	function tag( type, args = {}) {
		const output = document.createElement( type );

		if ( 'string' === typeof args ) {
			// Support passing just a string to a tag for simple text elements.
			output.textContent = args;
			return output;
		}

		const { id, className, children, child, text, data } = args;

		if ( id ) {
			output.id = id;
		}
		if ( className ) {
			output.className = className;
		}
		if ( children ) {
			children.forEach( child => output.appendChild( child ) );
		} else if ( child ) {
			output.appendChild( child );
		} else if ( text ) {
			output.textContent = text;
		}
		if ( data ) {
			Object.keys( data ).forEach( function( dataKey ) {
				output.setAttribute( 'data-' + dataKey, data[dataKey] );
			});
		}
		return output;
	}

	function svg({ href, classList } = {}) {
		const namespace = 'http://www.w3.org/2000/svg';
		const output = document.createElementNS( namespace, 'svg' );
		if ( classList ) {
			output.classList.add( ...classList );
		}

		if ( href ) {
			const use = document.createElementNS( namespace, 'use' );
			use.setAttribute( 'href', href );
			output.appendChild( use );
			output.classList.add( 'frmsvg' );
		}
		return output;
	}

	/**
	 * Pop up a success message in the lower right corner.
	 * It then fades out and gets deleted automatically.
	 *
	 * @param {HTMLElement|String} content
	 * @returns {void}
	 */
	function success( content ) {
		const container           = document.getElementById( 'wpbody' );
		const notice              = div({
			className: 'notice notice-info frm-review-notice frm_updated_message frm-floating-success-message',
			child: div({
				className: 'frm-satisfied',
				child: 'string' === typeof content ? document.createTextNode( content ) : content
			})
		});
		container.appendChild( notice );

		setTimeout(
			() => jQuery( notice ).fadeOut( () => notice.remove() ),
			2000
		);
	}

	function setAttributes( element, attrs ) {
		Object.entries( attrs ).forEach(
			([ key, value ]) => element.setAttribute( key, value )
		);
	}

	function redraw( element, child ) {
		element.innerHTML = '';
		element.appendChild( child );
	}

	window.frmDom = { tag, div, span, a, img, labelledTextInput, svg, setAttributes, success, modal, ajax, bootstrap, autocomplete, search, util, wysiwyg };
}() );
