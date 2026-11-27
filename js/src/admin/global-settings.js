wp.hooks.addAction( 'frmAdminGlobalSettingsInit', 'formidable', function( {
	adminState,
	confirmLinkClick,
	fieldUpdated,
	initTablistKeyboard,
	initiateMultiselect,
	installNewForm,
	syncTablistState,
} ) {
	/**
	 * Track changes made in the Custom CSS CodeMirror editor for the unsaved changes pop up.
	 * CodeMirror replaces the textarea, so edits there never fire a native change event.
	 * The editor initializes on document ready, so retry a few times until it exists.
	 *
	 * @since 7.0
	 *
	 * @param {number} retryCount The number of times this function has run while waiting for CodeMirror to initialize.
	 * @return {void}
	 */
	function addCustomCSSEditorChangeListener( retryCount = 0 ) {
		const retryLimit = 5;
		const retryInterval = 500;
		const editor = window.frm_codemirror_box_wp_editor;

		if ( editor === undefined || editor.codemirror === undefined ) {
			if ( retryCount < retryLimit ) {
				setTimeout( () => addCustomCSSEditorChangeListener( retryCount + 1 ), retryInterval );
			}
			return;
		}

		editor.codemirror.on( 'change', fieldUpdated );
	}

	function uninstallNow() {
		/*jshint validthis:true */
		if ( confirmLinkClick( this ) === true ) {
			jQuery( '.frm_uninstall .frm-wait' ).css( 'visibility', 'visible' );
			jQuery.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: `action=frm_uninstall&nonce=${ frmGlobal.nonce }`,
				success( msg ) {
					jQuery( '.frm_uninstall' ).fadeOut( 'slow' );
					window.location = msg;
				}
			} );
		}
		return false;
	}

	function saveAddonLicense() {
		/*jshint validthis:true */
		const button = jQuery( this );
		const buttonName = this.name;
		const pluginSlug = this.getAttribute( 'data-plugin' );
		const action = buttonName.replace( `edd_${ pluginSlug }_license_`, '' );
		let license = document.getElementById( `edd_${ pluginSlug }_license_key` ).value;
		button.get( 0 ).disabled = true;
		jQuery.ajax( {
			type: 'POST', url: ajaxurl, dataType: 'json',
			data: { action: `frm_addon_${ action }`, license, plugin: pluginSlug, nonce: frmGlobal.nonce },
			success( msg ) {
				button.get( 0 ).disabled = false;
				const thisRow = button.closest( '.edd_frm_license_row' );
				if ( action === 'deactivate' ) {
					license = '';
					document.getElementById( `edd_${ pluginSlug }_license_key` ).value = '';
				}
				thisRow.find( '.edd_frm_license' ).html( license );
				const eddWrapper = button.get( 0 ).closest( '.frm_form_field' );
				const actionIsSuccess = msg.success === true;
				eddWrapper.querySelector( '.frm_icon_font.frm_action_success' ).classList.toggle( 'frm_hidden', ! actionIsSuccess || action === 'deactivate' );
				eddWrapper.querySelector( '.frm_icon_font.frm_action_error' ).classList.toggle( 'frm_hidden', actionIsSuccess );

				const messageBox = thisRow.find( '.frm_license_msg' );
				messageBox.html( msg.message );
				if ( msg.message !== '' ) {
					setTimeout( function() {
						messageBox.html( '' );
						thisRow.find( '.frm_icon_font' ).addClass( 'frm_hidden' );
						if ( actionIsSuccess ) {
							const actionIsActivate = action === 'activate';
							thisRow.get( 0 ).querySelector( '.edd_frm_unauthorized' ).classList.toggle( 'frm_hidden', actionIsActivate );
							thisRow.get( 0 ).querySelector( '.edd_frm_authorized' ).classList.toggle( 'frm_hidden', ! actionIsActivate );
						}
					}, 2000 );
				}
			}
		} );
	}

	function installTemplateFieldset( e ) {
		/*jshint validthis:true */
		const fieldset = this.parentNode.parentNode;
		const action = fieldset.elements.type.value;
		const button = this;
		e.preventDefault();
		button.classList.add( 'frm_loading_button' );
		installNewForm( fieldset, action, button );
	}

	function handleCaptchaTypeChange( e ) {
		const thresholdContainer = document.getElementById( 'frm_captcha_threshold_container' );
		if ( thresholdContainer ) {
			thresholdContainer.classList.toggle( 'frm_hidden', 'v3' !== e.target.value );
		}
	}

	jQuery( document ).on( 'click', '[data-frmuninstall]', uninstallNow );

	initiateMultiselect();

	// activate addon licenses
	const licenseTab = document.getElementById( 'licenses_settings' );
	if ( licenseTab ) {
		jQuery( licenseTab ).on( 'click', '.edd_frm_save_license', saveAddonLicense );
	}

	// Solution install page
	jQuery( document ).on( 'click', '#frm-new-template button', installTemplateFieldset );

	jQuery( '#frm-dismissable-cta .dismiss' ).on( 'click', function( event ) {
		event.preventDefault();
		jQuery.post(
			ajaxurl,
			{
				action: 'frm_lite_settings_upgrade',
				nonce: frmGlobal.nonce
			}
		);
		jQuery( '.settings-lite-cta' ).remove();
	} );

	const captchaType = document.getElementById( 'frm_re_type' );
	if ( captchaType ) {
		captchaType.addEventListener( 'change', handleCaptchaTypeChange );
	}

	const captchas = document.querySelector( '.frm_captchas' );
	captchas.addEventListener( 'change', function( event ) {
		const captchaValueOnLoad = document.querySelector( '.frm_captchas input[checked="checked"]' )?.value;
		const showNote = event.target.value !== captchaValueOnLoad;
		document.querySelector( '.captcha_settings .frm_note_style' ).classList.toggle( 'frm_hidden', ! showNote );

		syncTablistState( captchas );
	} );
	initTablistKeyboard( captchas );

	const spamHandling = document.querySelector( '.frm-spam-handling-settings' );
	spamHandling?.addEventListener( 'change', event => {
		const { target } = event;

		// A check's select only shows while its checkbox is on.
		if ( target.dataset.spamCheck ) {
			const select = document.getElementById( target.dataset.spamCheck );
			select.disabled = ! target.checked;
			select.classList.toggle( 'frm_hidden', ! target.checked );

			if ( 'frm_denylist_check' === target.name ) {
				document.querySelector( '.frm-denylist-settings' )?.classList.toggle( 'frm_hidden', ! target.checked );
			}
		}
	} );

	// Set fieldsUpdated to 0 to avoid the unsaved changes pop up.
	frmDom.util.documentOn( 'submit', '.frm_settings_form', () => {
		adminState.fieldsUpdated = 0;
	} );

	// The uninstall checkbox only reveals its action, license keys save separately, and payment section inputs act as tabs.
	// This is delegated from the wrap element, not the document, because hideShowItem returns false and stops change events from reaching the document.
	jQuery( '#form_global_settings' ).on( 'change', 'input:not(#frm-uninstall-box):not(.frm-search-input):not(.frm_addon_license_key):not([name^="proplug-"]):not([name="frm_payment_section"]), select, textarea', fieldUpdated );

	addCustomCSSEditorChangeListener();

	const manageStyleSettings = document.getElementById( 'manage_styles_settings' );
	if ( manageStyleSettings ) {
		manageStyleSettings.addEventListener(
			'change',
			event => {
				const { target } = event;
				if ( 'SELECT' !== target.nodeName || ! target.dataset.name || target.getAttribute( 'name' ) ) {
					return;
				}

				target.setAttribute( 'name', target.dataset.name );
			}
		);
	}

	const paymentsSettings = document.getElementById( 'payments_settings' );
	const paymentSettingsTabs = paymentsSettings?.querySelectorAll( '[name="frm_payment_section"]' );
	if ( paymentSettingsTabs?.length ) {
		const paymentTablist = paymentSettingsTabs[ 0 ].closest( '[role="tablist"]' );
		paymentSettingsTabs.forEach(
			element => element.addEventListener( 'change', () => syncTablistState( paymentTablist ) )
		);
		initTablistKeyboard( paymentTablist );
	}
} );
