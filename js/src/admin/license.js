window.FrmFormsConnect = window.FrmFormsConnect || ( function( document, window, $ ) {
	const el = {
		messageBox: null,
		reset: null,

		setElements() {
			el.messageBox = document.querySelector( '.frm_pro_license_msg' );
			el.reset = document.getElementById( 'frm_reconnect_link' );
		}
	};

	/**
	 * Public functions and properties.
	 *
	 * @since 4.03
	 *
	 * @type {Object}
	 */
	const app = {

		/**
		 * Register connect button event.
		 *
		 * @since 4.03
		 */
		init() {
			el.setElements();

			$( document.getElementById( 'frm_deauthorize_link' ) ).on( 'click', app.deauthorize );
			$( '.frm_authorize_link' ).on( 'click', app.authorize );
			// Handles FF dashboard Authorize & Reauthorize events.
			// Attach click event to parent as #frm_deauthorize_link & #frm_reconnect_link dynamically recreated by bootstrap.setupBootstrapDropdowns in dom.js
			$( '.frm-dashboard-license-options' ).on( 'click', '#frm_deauthorize_link', app.deauthorize );
			$( '.frm-dashboard-license-options' ).on( 'click', '#frm_reconnect_link', app.reauthorize );

			if ( el.reset !== null ) {
				$( el.reset ).on( 'click', app.reauthorize );
			}
		},

		/* Manual license authorization */
		authorize() {
			/*jshint validthis:true */
			const button = this;
			const pluginSlug = this.getAttribute( 'data-plugin' );
			const input = document.getElementById( `edd_${ pluginSlug }_license_key` );
			const license = input.value;
			let wpmu = document.getElementById( 'proplug-wpmu' );
			this.classList.add( 'frm_loading_button' );
			if ( ! wpmu ) {
				wpmu = 0;
			} else if ( wpmu.checked ) {
				wpmu = 1;
			} else {
				wpmu = 0;
			}

			$.ajax( {
				type: 'POST', url: ajaxurl, dataType: 'json',
				data: {
					action: 'frm_addon_activate',
					license,
					plugin: pluginSlug,
					wpmu,
					nonce: frmGlobal.nonce
				},
				success( msg ) {
					app.afterAuthorize( msg, input );
					button.classList.remove( 'frm_loading_button' );
				}
			} );
		},

		afterAuthorize( msg, input ) {
			if ( msg.success === true ) {
				input.value = '•••••••••••••••••••';
			}

			wp.hooks.doAction( 'frm_after_authorize', msg );
			app.showMessage( msg );
		},

		showProgress( msg ) {
			if ( el.messageBox === null ) {
				// In case the message box was added after page load.
				el.setElements();
			}

			const { messageBox } = el;
			if ( messageBox === null ) {
				return;
			}

			if ( msg.success === true ) {
				messageBox.classList.remove( 'frm_error_style' );
				messageBox.classList.add( 'frm_message', 'frm_updated_message' );
			} else {
				messageBox.classList.add( 'frm_error_style' );
				messageBox.classList.remove( 'frm_message', 'frm_updated_message' );
			}
			messageBox.classList.remove( 'frm_hidden' );
			messageBox.innerHTML = msg.message;
		},

		showMessage( msg ) {
			if ( el.messageBox === null ) {
				// In case the message box was added after page load.
				el.setElements();
			}
			const { messageBox } = el;

			if ( msg.success === true ) {
				app.showAuthorized( true );
				app.showLicenseType( msg );
				app.showInlineSuccess();

				/**
				 * Triggers the after license is authorized action for a confirmation/success modal.
				 *
				 * @param {Object} msg An object containing message data received from Authorize request.
				 */
				wp.hooks.doAction( 'frmAdmin.afterLicenseAuthorizeSuccess', { msg } );
			}
			app.showProgress( msg );

			if ( msg.message !== '' ) {
				setTimeout( function() {
					messageBox.innerHTML = '';
					messageBox.classList.add( 'frm_hidden' );
					messageBox.classList.remove( 'frm_error_style', 'frm_message', 'frm_updated_message' );
				}, 10000 );
				const refreshPage = document.querySelector( '.frm-admin-page-dashboard' );
				if ( refreshPage ) {
					setTimeout( function() {
						window.location.reload();
					}, 1000 );
				}
			}
		},

		showAuthorized( show ) {
			const from = show ? 'unauthorized' : 'authorized';
			const to = show ? 'authorized' : 'unauthorized';
			const container = document.querySelectorAll( `.frm_${ from }_box` );
			if ( container.length ) {
				// Replace all authorized boxes with unauthorized boxes.
				container.forEach( function( box ) {
					box.className = box.className.replace( `frm_${ from }_box`, `frm_${ to }_box` );
				} );
			}
		},

		/**
		 * Update the license type message with the license that was just activated.
		 * The message is printed before the license is known, so it would otherwise
		 * keep showing the Lite copy until the page is reloaded.
		 *
		 * @since 7.0
		 *
		 * @param {Object} msg The response from the authorize request.
		 * @return {void}
		 */
		showLicenseType( msg ) {
			if ( ! msg.license_type_info ) {
				return;
			}

			document.querySelectorAll( '.frm_license_type_info' ).forEach( function( element ) {
				element.textContent = msg.license_type_info;
			} );

			if ( msg.license_type !== 'Elite' ) {
				return;
			}

			// There is nothing left to upgrade to.
			document.querySelectorAll( '.frm_license_upgrade_cta' ).forEach( function( element ) {
				element.remove();
			} );
		},

		/**
		 * Use the data-success element to replace the element content.
		 */
		showInlineSuccess() {
			const successElement = document.querySelectorAll( '.frm-confirm-msg [data-success]' );
			if ( successElement.length ) {
				successElement.forEach( function( element ) {
					element.innerHTML = frmAdminBuild.purifyHtml( element.getAttribute( 'data-success' ) );
				} );
			}
		},

		/* Clear the site license cache */
		reauthorize() {
			/*jshint validthis:true */
			this.innerHTML = '<span class="frm-wait frm_spinner" style="visibility:visible;float:none"></span>';

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				dataType: 'json',
				data: {
					action: 'frm_reset_cache',
					plugin: 'formidable_pro',
					nonce: frmGlobal.nonce
				},
				success( msg ) {
					el.reset.textContent = msg.message;
					if ( el.reset.getAttribute( 'data-refresh' ) === '1' ) {
						window.location.reload();
					}
				}
			} );
			return false;
		},

		deauthorize() {
			/*jshint validthis:true */
			if ( ! confirm( frmGlobal.deauthorize ) ) {
				return false;
			}
			const pluginSlug = this.getAttribute( 'data-plugin' );
			const input = document.getElementById( `edd_${ pluginSlug }_license_key` );
			const license = input.value;
			const link = this;

			this.innerHTML = '<span class="frm-wait frm_spinner" style="visibility:visible;"></span>';

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'frm_addon_deactivate',
					license,
					plugin: pluginSlug,
					nonce: frmGlobal.nonce
				},
				success() {
					app.showAuthorized( false );
					input.value = '';
					link.replaceWith( 'Disconnected' );

					/**
					 * Triggers the after license is deauthorized sruccess action.
					 */
					wp.hooks.doAction( 'frmAdmin.afterLicenseDeauthorizeSuccess', {} );
				}
			} );
			return false;
		}
	};

	// Provide access to public functions/properties.
	return app;
}( document, window, jQuery ) );
