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

	frmDom = { div, tag, modal, ajax, bootstrap };
}() );
