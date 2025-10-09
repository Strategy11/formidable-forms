import { __ } from '@wordpress/i18n';

const { div } = frmDom;

/**
 * Toggles the state of an add-on (ie. enable or disable an add-on).
 *
 * @param {Element} clicked
 * @param {string}  action
 */
export function toggleAddonState( clicked, action ) {
	const ajaxurl = window.ajaxurl ?? frm_js.ajax_url; // eslint-disable-line camelcase

	// Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated.
	jQuery( '.frm-addon-error' ).remove();
	const button = jQuery( clicked );
	const plugin = button.attr( 'rel' );
	const el = button.parent();
	const message = el.parent().find( '.addon-status-label' );

	button.addClass( 'frm_loading_button' );

	// Process the Ajax to perform the activation.
	jQuery.ajax( {
		url: ajaxurl,
		type: 'POST',
		async: true,
		cache: false,
		dataType: 'json',
		data: {
			action: action,
			nonce: frmGlobal.nonce,
			plugin: plugin
		},
		success: function( response ) {
			response = response?.data ?? response;

			let saveAndReload;

			if ( 'string' !== typeof response && 'string' === typeof response.message ) {
				if ( 'undefined' !== typeof response.saveAndReload ) {
					saveAndReload = response.saveAndReload;
				}
				response = response.message;
			}

			const error = extractErrorFromAddOnResponse( response );
			if ( error ) {
				addonError( error, el, button );
				return;
			}

			afterAddonInstall( response, button, message, el, saveAndReload, action );

			/**
			 * Trigger an action after successfully toggling the addon state.
			 *
			 * @param {Object} response
			 */
			wp.hooks.doAction( 'frm_update_addon_state', response );
		},
		error: function() {
			button.removeClass( 'frm_loading_button' );
		}
	} );
}

export function extractErrorFromAddOnResponse( response ) {
	if ( typeof response !== 'string' ) {
		if ( typeof response.success !== 'undefined' && response.success ) {
			return false;
		}

		if ( response.form ) {
			if ( jQuery( response.form ).is( '#message' ) ) {
				return {
					message: jQuery( response.form ).find( 'p' ).html()
				};
			}
		}

		return response;
	}

	return false;
}

export function afterAddonInstall( response, button, message, el, saveAndReload, action = 'frm_activate_addon' ) {
	const frmAdminJs = frm_admin_js; // eslint-disable-line camelcase

	const addonStatuses = document.querySelectorAll( '.frm-addon-status' );
	addonStatuses.forEach(
		addonStatus => {
			addonStatus.textContent = response;
			addonStatus.style.display = 'block';
		}
	);

	// The Ajax request was successful, so let's update the output.
	button.css( { opacity: '0' } );

	document.querySelectorAll( '.frm-oneclick' ).forEach(
		oneClick => {
			oneClick.style.display = 'none';
		}
	);

	jQuery( '#frm_upgrade_modal h2' ).hide();
	jQuery( '#frm_upgrade_modal .frm_lock_icon' ).addClass( 'frm_lock_open_icon' );
	jQuery( '#frm_upgrade_modal .frm_lock_icon use' ).attr( 'xlink:href', '#frm_lock_open_icon' );

	// Proceed with CSS changes
	const actionMap = {
		frm_activate_addon: { class: 'frm-addon-active', message: frmAdminJs.active },
		frm_deactivate_addon: { class: 'frm-addon-installed', message: frmAdminJs.installed },
		frm_uninstall_addon: { class: 'frm-addon-not-installed', message: frmAdminJs.not_installed }
	};
	actionMap.frm_install_addon = actionMap.frm_activate_addon;

	const messageElement = message[ 0 ];
	if ( messageElement ) {
		messageElement.textContent = actionMap[ action ].message;
	}

	const parentElement = el[ 0 ].parentElement;
	parentElement.classList.remove( 'frm-addon-not-installed', 'frm-addon-installed', 'frm-addon-active' );
	parentElement.classList.add( actionMap[ action ].class );

	const buttonElement = button[ 0 ];
	buttonElement.classList.remove( 'frm_loading_button' );

	// Maybe refresh import and SMTP pages
	const refreshPage = document.querySelectorAll( '.frm-admin-page-import, #frm-admin-smtp, #frm-welcome' );
	if ( refreshPage.length > 0 ) {
		window.location.reload();
		return;
	}

	if ( [ 'settings', 'form_builder' ].includes( saveAndReload ) ) {
		addonStatuses.forEach(
			addonStatus => {
				const inModal = null !== addonStatus.closest( '#frm_upgrade_modal' );
				addonStatus.appendChild( getSaveAndReloadSettingsOptions( saveAndReload, inModal ) );
			}
		);
	}
}

export function addonError( response, el, button ) {
	if ( response.form ) {
		jQuery( '.frm-inline-error' ).remove();
		button.closest( '.frm-card' )
			.html( response.form )
			.css( { padding: 5 } )
			.find( '#upgrade' )
			.attr( 'rel', button.attr( 'rel' ) )
			.on( 'click', installAddonWithCreds );
	} else {
		el.append( '<div class="frm-addon-error frm_error_style"><p><strong>' + response.message + '</strong></p></div>' );
		button.removeClass( 'frm_loading_button' );
		jQuery( '.frm-addon-error' ).delay( 4000 ).fadeOut();
	}
}

function getSaveAndReloadSettingsOptions( saveAndReload, inModal ) {
	const className = 'frm-save-and-reload-options';
	const children = [ saveAndReloadSettingsButton( saveAndReload ) ];
	if ( inModal ) {
		children.push( closePopupButton() );
	}
	return div( { className, children } );
}

function saveAndReloadSettingsButton( saveAndReload ) {
	const button = document.createElement( 'button' );
	button.classList.add( 'frm-save-and-reload', 'button', 'button-primary', 'frm-button-primary' );
	button.textContent = __( 'Save and Reload', 'formidable' );
	button.addEventListener( 'click', () => {
		if ( saveAndReload === 'form_builder' ) {
			saveAndReloadFormBuilder();
		} else if ( saveAndReload === 'settings' ) {
			saveAndReloadSettings();
		}
	} );
	return button;
}

function saveAndReloadSettings() {
	const page = document.getElementById( 'form_settings_page' );
	if ( null !== page ) {
		const form = page.querySelector( 'form.frm_form_settings' );
		if ( null !== form ) {
			fieldsUpdated = 0;
			form.submit();
		}
	}
}

function closePopupButton() {
	const a = document.createElement( 'a' );
	a.setAttribute( 'href', '#' );
	a.classList.add( 'button', 'button-secondary', 'frm-button-secondary', 'dismiss' );
	a.textContent = __( 'Close', 'formidable' );
	return a;
}

function saveAndReloadFormBuilder() {
	const submitButton = document.getElementById( 'frm_submit_side_top' );
	if ( submitButton.classList.contains( 'frm_submit_ajax' ) ) {
		submitButton.setAttribute( 'data-new-addon-installed', true );
	}
	submitButton.click();
}
