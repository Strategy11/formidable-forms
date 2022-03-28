let frmDom;

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
						text: __( 'Cancel', 'formidable' ),
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
				postbox.appendChild(
					div({ className: 'frm_modal_footer' })
				);
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
			const output = tag( 'a', args );
			output.href = '#';
			if ( args.buttonType ) {
				output.classList.add( 'button' );
				switch ( args.buttonType ) {
					case 'primary':
						output.classList.add( 'button-primary', 'frm-button-primary', 'dismiss' );
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
			const response = await fetch( ajaxurl + '?action=frm_' + action );
			const json = await response.json();
			if ( ! json.success ) {
				return Promise.reject( 'JSON result is not successful' );
			}
			return Promise.resolve( json.data );
		},
		doJsonPost: async function( action, formData ) {
			formData.append( 'nonce', frmGlobal.nonce );
			const init = {
				method: 'POST',
				body: formData
			};
			const response = await fetch( ajaxurl + '?action=frm_' + action, init );
			const json = await response.json();
			if ( ! json.success ) {
				return Promise.reject( 'JSON result is not successful' );
			}
			return Promise.resolve( 'undefined' !== typeof json.data ? json.data : json );
		}
	};

	const multiselect = {
		init: function() {
			let $select, id, labelledBy;

			$select = jQuery( this );
			id = $select.is( '[id]' ) ? $select.attr( 'id' ).replace( '[]', '' ) : false;
			labelledBy = id ? jQuery( '#for_' + id ) : false;
			labelledBy = id && labelledBy.length ? 'aria-labelledby="' + labelledBy.attr( 'id' ) + '"' : '';

			$select.multiselect({
				templates: {
					popupContainer: '<div class="multiselect-container frm-dropdown-menu"></div>',
					option: '<button type="button" class="multiselect-option dropdown-item frm_no_style_button"></button>',
					button: '<button type="button" class="multiselect dropdown-toggle btn" data-toggle="dropdown" ' + labelledBy + '><span class="multiselect-selected-text"></span> <b class="caret"></b></button>'
				},
				buttonContainer: '<div class="btn-group frm-btn-group dropdown" />',
				nonSelectedText: '',
				onDropdownShown: function( event ) {
					var action = jQuery( event.currentTarget.closest( '.frm_form_action_settings, #frm-show-fields' ) );
					if ( action.length ) {
						jQuery( '#wpcontent' ).on( 'click', function() {
							if ( jQuery( '.multiselect-container.frm-dropdown-menu' ).is( ':visible' ) ) {
								jQuery( event.currentTarget ).removeClass( 'open' );
							}
						});
					}
				},
				onChange: function( element, option ) {
					$select.trigger( 'frm-multiselect-changed', element, option );
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
				frmDom.autocomplete.initAutocomplete( 'page' );
				frmDom.autocomplete.initAutocomplete( 'user' );
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

			elements.each( function() {
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
					change: autocomplete.autoCompleteSelectBlank,
					select: autocomplete.autoCompleteSelectFromResults,
					focus: autocomplete.autoCompleteFocus,
					position: {
						my: 'left top',
						at: 'left bottom',
						collision: 'flip'
					},
					response: function( event, ui ) {
						if ( ! ui.content.length ) {
							var noResult = { value: '', label: frm_admin_js.no_items_found };
							ui.content.push( noResult );
						}
					},
					create: function() {
						var $container = jQuery( this ).parent();

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
				});
			});
		},

		autoCompleteFocus: function() {
			return false;
		},

		autoCompleteSelectBlank: function( e, ui ) {
			if ( ui.item === null ) {
				this.nextElementSibling.value = '';
			}
		},

		autoCompleteSelectFromResults: function( e, ui ) {
			e.preventDefault();

			if ( ui.item.value === '' ) {
				this.value = '';
			} else {
				this.value = ui.item.label;
			}

			this.nextElementSibling.value = ui.item.value;
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
					jQuery( '#wpwrap' ).removeClass( 'frm_overlay' );
					jQuery( '.spinner' ).css( 'visibility', 'hidden' );
				}
			});
		}
		scrollToTop();
		$modal.dialog( 'open' );
		return $modal;
	}

	function scrollToTop() {
		if ( 'scrollRestoration' in history ) {
			history.scrollRestoration = 'manual';
		}
		window.scrollTo( 0, 0 );
	}

	function div( args ) {
		return tag( 'div', args );
	}

	function tag( type, { id, className, children, child, text } = {}) {
		const output = document.createElement( type );
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
		return output;
	}

	function redraw( element, child ) {
		element.innerHTML = '';
		element.appendChild( child );
	}

	frmDom = { div, tag, modal, ajax, bootstrap, autocomplete };
}() );
