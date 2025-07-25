/* exported frmRecaptcha, frmAfterRecaptcha */
/* eslint-disable prefer-const */

function frmFrontFormJS() {
	'use strict';

	/*global jQuery:false, frm_js, grecaptcha, hcaptcha, turnstile, frmProForm, tinyMCE */
	/*global frmThemeOverride_jsErrors, frmThemeOverride_frmPlaceError, frmThemeOverride_frmAfterSubmit */

	let jsErrors = [];

	/**
	 * Triggers custom JS event.
	 *
	 * @since 5.5.3
	 *
	 * @param {HTMLElement} el        The HTML element.
	 * @param {string}      eventName Event name.
	 * @param {mixed}       data      The passed data.
	 */
	function triggerCustomEvent( el, eventName, data ) {
		if ( typeof window.CustomEvent !== 'function' ) {
			return;
		}

		const event = new CustomEvent( eventName );
		event.frmData = data;

		el.dispatchEvent( event );
	}

	/**
	 * Get the ID of the field that changed.
	 *
	 * @param {HTMLElement|jQuery} field
	 * @param {boolean}            fullID
	 * @return {string|number} Field ID.
	 */
	function getFieldId( field, fullID ) {
		let nameParts, fieldId,
			isRepeating = false,
			fieldName = '';
		if ( field instanceof jQuery ) {
			fieldName = field.attr( 'name' );
		} else {
			fieldName = field.name;
		}

		if ( typeof fieldName === 'undefined' ) {
			fieldName = '';
		}

		if ( fieldName === '' ) {
			if ( field instanceof jQuery ) {
				fieldName = field.data( 'name' );
			} else {
				fieldName = field.getAttribute( 'data-name' );
			}

			if ( typeof fieldName === 'undefined' ) {
				fieldName = '';
			}

			if ( fieldName !== '' && fieldName ) {
				return fieldName;
			}
			return 0;
		}

		nameParts = fieldName.replace( 'item_meta[', '' ).replace( '[]', '' ).split( ']' );
		//TODO: Fix this for checkboxes and address fields
		if ( nameParts.length < 1 ) {
			return 0;
		}
		nameParts = nameParts.filter( function( n ) {
			return n !== '';
		});

		fieldId = nameParts[0];

		if ( nameParts.length === 1 ) {
			return fieldId;
		}

		if ( nameParts[1] === '[form' || nameParts[1] === '[row_ids' ) {
			return 0;
		}

		// Check if 'this' is in a repeating section
		if ( document.querySelector( 'input[name="item_meta[' + fieldId + '][form]"]' ) ) {

			// this is a repeatable section with name: item_meta[repeating-section-id][row-id][field-id]
			fieldId = nameParts[2].replace( '[', '' );
			isRepeating = true;
		}

		// Check if 'this' is an other text field and get field ID for it
		if ( 'other' === fieldId ) {
			if ( isRepeating ) {
				// name for other fields: item_meta[370][0][other][414]
				fieldId = nameParts[3].replace( '[', '' );
			} else {
				// Other field name: item_meta[other][370]
				fieldId = nameParts[1].replace( '[', '' );
			}
		}

		if ( fullID === true ) {
			// For use in the container div id
			if ( fieldId === nameParts[0]) {
				fieldId = fieldId + '-' + nameParts[1].replace( '[', '' );
			} else {
				fieldId = fieldId + '-' + nameParts[0] + '-' + nameParts[1].replace( '[', '' );
			}
		}

		return fieldId;
	}

	/**
	 * Disable the submit button for a given jQuery form object
	 *
	 * @since 2.03.02
	 *
	 * @param {Object} $form
	 */
	function disableSubmitButton( $form ) {
		$form.find( 'input[type="submit"], input[type="button"], button[type="submit"], button.frm_save_draft' ).attr( 'disabled', 'disabled' );
	}

	/**
	 * Enable the submit button for a given jQuery form object
	 *
	 * @since 2.03.02
	 *
	 * @param {Object} $form
	 */
	function enableSubmitButton( $form ) {
		$form.find( 'input[type="submit"], input[type="button"], button[type="submit"]' ).prop( 'disabled', false );
	}

	/**
	 * Disable the save draft link for a given jQuery form object
	 *
	 * @since 4.04.03
	 *
	 * @param {Object} $form
	 */
	function disableSaveDraft( $form ) {
		$form.find( 'a.frm_save_draft' ).css( 'pointer-events', 'none' );
	}

	/**
	 * Enable the save draft link for a given jQuery form object
	 *
	 * @since 4.04.03
	 *
	 * @param {jQuery} $form
	 */
	function enableSaveDraft( $form ) {
		if ( ! $form.length ) {
			return;
		}
		$form[0].querySelectorAll( '.frm_save_draft' ).forEach( saveDraftButton => {
			saveDraftButton.disabled            = false;
			saveDraftButton.style.pointerEvents = '';
		});
	}

	/**
	 * Validate form with JS.
	 *
	 * @param {HTMLElement|jQuery} object
	 * @return {Array} Errors.
	 */
	function validateForm( object ) {
		let errors = [];

		const vanillaJsObject = 'function' === typeof object.get ? object.get( 0 ) : object;

		// Required field validation.
		vanillaJsObject?.querySelectorAll( '.frm_required_field' ).forEach(
			requiredField => {
				const isVisible = requiredField.offsetParent !== null;
				if ( ! isVisible ) {
					return;
				}

				requiredField.querySelectorAll( 'input, select, textarea' ).forEach(
					requiredInput => {
						if ( hasClass( requiredInput, 'frm_optional' ) || hasClass( requiredInput, 'ed_button' ) ) {
							// skip rich text field buttons.
							return;
						}

						errors = checkRequiredField( requiredInput, errors );
					}
				);
			}
		);

		vanillaJsObject?.querySelectorAll( 'input,select,textarea' ).forEach(
			field => {
				if ( '' === field.value ) {
					if ( 'number' === field.type ) {
						// A number field will return an empty string when it is invalid.
						checkValidity( field, errors );
					}

					const isConfirmationField = field.name && 0 === field.name.indexOf( 'item_meta[conf_' );
					if ( ! isConfirmationField ) {
						// Allow a blank confirmation field to still call validateFieldValue.
						// If we continue for a confirmation field there are issues with forms submitting with a blank confirmation field.
						return;
					}
				}

				validateFieldValue( field, errors, true );
				checkValidity( field, errors );
			}
		);

		// Invisible captchas are processed after validation.
		// We only want to validate a visible captcha on submit.
		if ( ! hasInvisibleRecaptcha( object ) ) {
			errors = validateRecaptcha( object, errors );
		}

		return errors;
	}

	/**
	 * Check the ValidityState interface for the field.
	 * If it is invalid, show an error for it.
	 *
	 * @param {HTMLElement} field
	 * @param {Array}       errors
	 * @return {void}
	 */
	function checkValidity( field, errors ) {
		let fieldID;
		if ( 'object' !== typeof field.validity || false !== field.validity.valid ) {
			return;
		}

		fieldID = getFieldId( field, true );
		if ( 'undefined' === typeof errors[ fieldID ]) {
			errors[ fieldID ] = getFieldValidationMessage( field, 'data-invmsg' );
		}

		if ( 'function' === typeof field.reportValidity ) {
			// This triggers an error pop up.
			field.reportValidity();
		}
	}

	/**
	 * @since 5.0.10
	 *
	 * @param {Object} element
	 * @param {string} targetClass
	 * @return {boolean} True if the element has the target class.
	 */
	function hasClass( element, targetClass ) {
		return element.classList && element.classList.contains( targetClass );
	}

	/**
	 * @param {HTMLElement} field
	 */
	function maybeValidateChange( field ) {
		if ( field.type === 'url' ) {
			maybeAddHttpsToUrl( field );
		}
		const form = field.closest( 'form' );
		if ( form && hasClass( form, 'frm_js_validate' ) ) {
			validateField( field );
		}
	}

	/**
	 * @param {HTMLElement} field
	 */
	function maybeAddHttpsToUrl( field ) {
		const url = field.value;
		const matches = url.match( /^(https?|ftps?|mailto|news|feed|telnet):/ );
		if ( field.value !== '' && matches === null ) {
			field.value = 'https://' + url;
		}
	}

	/**
	 * Validate a field with JS.
	 *
	 * @param {HTMLElement} field
	 *
	 * @return {void}
	 */
	function validateField( field ) {
		let errors, key;

		errors               = [];
		const fieldContainer = field.closest( '.frm_form_field' );

		if ( ! fieldContainer ) {
			// Hidden fields do not have a field container and do not require JS validation.
			return;
		}

		if ( hasClass( fieldContainer, 'frm_required_field' ) && ! hasClass( field, 'frm_optional' ) ) {
			errors = checkRequiredField( field, errors );
		}

		if ( errors.length < 1 ) {
			validateFieldValue( field, errors, false );
		}

		const $fieldCont = jQuery( fieldContainer );
		removeFieldError( $fieldCont );
		if ( Object.keys( errors ).length > 0 ) {
			for ( key in errors ) {
				addFieldError( $fieldCont, key, errors );
			}
		}
	}

	/**
	 * Validates a field value.
	 *
	 * @since 6.15 Added `onSubmit` parameter.
	 *
	 * @param {HTMLElement} field    Field input.
	 * @param {Object}      errors   Errors data.
	 * @param {boolean}     onSubmit Is `true` if the form is being submitted.
	 */
	function validateFieldValue( field, errors, onSubmit ) {
		if ( field.type === 'hidden' ) {
			// don't validate
		} else if ( field.type === 'number' ) {
			checkNumberField( field, errors );
		} else if ( field.type === 'email' ) {
			checkEmailField( field, errors, onSubmit );
		} else if ( field.type === 'password' ) {
			checkPasswordField( field, errors, onSubmit );
		} else if ( field.type === 'url' ) {
			checkUrlField( field, errors );
		} else if ( field.pattern !== null ) {
			checkPatternField( field, errors );
		}

		/**
		 * @since 6.15 Added `onSubmit` to the data.
		 */
		triggerCustomEvent( document, 'frm_validate_field_value', {
			field: field,
			errors: errors,
			onSubmit: onSubmit
		});
	}

	/**
	 * @param {HTMLElement} field
	 * @param {Array}       errors
	 * @return {Array} Errors
	 */
	function checkRequiredField( field, errors ) {
		let tempVal, i, placeholder,
			val = '',
			fieldID = '',
			fileID = field.getAttribute( 'data-frmfile' );

		if ( field.type === 'hidden' && fileID === null && ! isAppointmentField( field ) && ! isInlineDatepickerField( field ) ) {
			return errors;
		}

		if ( field.type === 'checkbox' || field.type === 'radio' ) {
			document.querySelectorAll( 'input[name="' + field.name + '"]' ).forEach( function( input ) {
				const requiredField = input.closest( '.frm_required_field' );
				if ( ! requiredField ) {
					return;
				}

				const checkedInputs = requiredField.querySelectorAll( 'input:checked' );
				checkedInputs.forEach( function( checkedInput ) {
					val = checkedInput.value;
				} );
			} );
		} else if ( field.type === 'file' || fileID ) {
			if ( typeof fileID === 'undefined' ) {
				fileID = getFieldId( field, true );
				fileID = fileID.replace( 'file', '' );
			}

			if ( typeof errors[ fileID ] === 'undefined' ) {
				val = getFileVals( fileID );
			}
			fieldID = fileID;
		} else {
			if ( hasClass( field, 'frm_pos_none' ) ) {
				// skip hidden other fields
				return errors;
			}

			val = jQuery( field ).val();
			if ( val === null ) {
				val = '';
			} else if ( typeof val !== 'string' ) {
				tempVal = val;
				val = '';
				for ( i = 0; i < tempVal.length; i++ ) {
					if ( tempVal[i] !== '' ) {
						val = tempVal[i];
					}
				}
			}

			if ( hasClass( field, 'frm_other_input' ) ) {
				fieldID = getFieldId( field, false );

				if ( val === '' ) {
					field = document.getElementById( field.id.replace( '-otext', '' ) );
				}
			} else {
				fieldID = getFieldId( field, true );
			}

			// Make sure fieldID is a string.
			// fieldID may be a number which doesn't include a .replace function.
			if ( 'function' !== typeof fieldID.replace ) {
				fieldID = fieldID.toString();
			}

			if ( hasClass( field, 'frm_time_select' ) ) {
				// set id for time field
				fieldID = fieldID.replace( '-H', '' ).replace( '-m', '' );
			} else if ( isSignatureField( field ) ) {
				if ( val === '' ) {
					val = jQuery( field ).closest( '.frm_form_field' ).find( '[name="' + field.getAttribute( 'name' ).replace( '[typed]', '[output]' ) + '"]' ).val();
				}
				fieldID = fieldID.replace( '-typed', '' );
			}

			placeholder = field.getAttribute( 'data-frmplaceholder' );
			if ( placeholder !== null && val === placeholder ) {
				val = '';
			}
		}

		if ( val === '' ) {
			if ( fieldID === '' ) {
				fieldID = getFieldId( field, true );
			}
			if ( ! ( fieldID in errors ) ) {
				errors[ fieldID ] = getFieldValidationMessage( field, 'data-reqmsg' );
			}
		}

		return errors;
	}

	/**
	 * @param {HTMLElement} field
	 * @return {boolean} True if the input is a typed signature input.
	 */
	function isSignatureField( field ) {
		const name = field.getAttribute( 'name' );
		return 'string' === typeof name && '[typed]' === name.substr( -7 );
	}

	/**
	 * @param {HTMLElement} field
	 * @return {boolean} True if the field is a SSA appointment field.
	 */
	function isAppointmentField( field ) {
		return hasClass( field, 'ssa_appointment_form_field_appointment_id' );
	}

	/**
	 * @param {HTMLElement} field
	 * @return {boolean} True if the field is inline datepicker field.
	 */
	function isInlineDatepickerField( field ) {
		return 'hidden' === field.type && '_alt' === field.id.substr( -4 ) && hasClass( field.nextElementSibling, 'frm_date_inline' );
	}

	/**
	 * @param {string|number} fileID
	 * @return {string} File input value.
	 */
	function getFileVals( fileID ) {
		let val = '',
			fileFields = jQuery( 'input[name="file' + fileID + '"], input[name="file' + fileID + '[]"], input[name^="item_meta[' + fileID + ']"]' );

		fileFields.each( function() {
			if ( val === '' ) {
				val = this.value;
			}
		});
		return val;
	}

	/**
	 * @param {HTMLElement} field
	 * @param {Array}       errors
	 * @return {void}
	 */
	function checkUrlField( field, errors ) {
		let fieldID,
			url = field.value;

		if ( url !== '' && ! /^http(s)?:\/\/(?:localhost|(?:[\da-z\.-]+\.[\da-z\.-]+))/i.test( url ) ) {
			fieldID = getFieldId( field, true );
			if ( ! ( fieldID in errors ) ) {
				errors[ fieldID ] = getFieldValidationMessage( field, 'data-invmsg' );
			}
		}
	}

	/**
	 * Checks if the confirm field should be checked.
	 *
	 * @since 6.15
	 *
	 * @param {HTMLElement} field    Field input.
	 * @param {boolean}     onSubmit Is `true` if the form is being submitted.
	 * @return {boolean} True if we should confirm the field.
	 */
	function shouldCheckConfirmField( field, onSubmit ) {
		if ( onSubmit ) {
			// Always check on submitting.
			return true;
		}

		if ( 0 === field.id.indexOf( 'field_conf_' ) ) {
			// Always check if it's the confirm field.
			return true;
		}

		return false;
	}

	/**
	 * Check the email field for errors.
	 *
	 * @since 6.15 Added `onSubmit` parameter.
	 *
	 * @param {HTMLElement} field    Field input.
	 * @param {Object}      errors   Errors data.
	 * @param {boolean}     onSubmit Is `true` if the form is being submitted.
	 */
	function checkEmailField( field, errors, onSubmit ) {
		const fieldID = getFieldId( field, true ),
			pattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;

		// validate the current field we're editing first
		if ( '' !== field.value && pattern.test( field.value ) === false ) {
			errors[ fieldID ] = getFieldValidationMessage( field, 'data-invmsg' );
		}

		if ( shouldCheckConfirmField( field, onSubmit ) ) {
			confirmField( field, errors );
		}
	}

	/**
	 * Check the password field for errors.
	 *
	 * @since 6.15 Added `onSubmit` parameter.
	 *
	 * @param {HTMLElement} field    Field input.
	 * @param {Object}      errors   Errors data.
	 * @param {boolean}     onSubmit Is `true` if the form is being submitted.
	 */
	function checkPasswordField( field, errors, onSubmit ) {
		if ( shouldCheckConfirmField( field, onSubmit ) ) {
			confirmField( field, errors );
		}
	}

	/**
	 * @param {HTMLElement} field
	 * @param {Array}       errors
	 * @return {void}
	 */
	function confirmField( field, errors ) {
		let value, confirmValue, firstField,
			fieldID = getFieldId( field, true ),
			strippedId = field.id.replace( 'conf_', '' ),
			strippedFieldID = fieldID.replace( 'conf_', '' ),
			confirmField = document.getElementById( strippedId.replace( 'field_', 'field_conf_' ) );

		if ( confirmField === null || typeof errors[ 'conf_' + strippedFieldID ] !== 'undefined' ) {
			return;
		}

		if ( fieldID !== strippedFieldID ) {
			firstField = document.getElementById( strippedId );
			value = firstField.value;
			confirmValue = confirmField.value;
			if ( value !== confirmValue ) {
				errors[ 'conf_' + strippedFieldID ] = getFieldValidationMessage( confirmField, 'data-confmsg' );
			}
		} else {
			validateField( confirmField );
		}
	}

	/**
	 * @param {HTMLElement} field
	 * @param {Array}       errors
	 * @return {void}
	 */
	function checkNumberField( field, errors ) {
		let fieldID,
			number = field.value;

		if ( number !== '' && isNaN( number / 1 ) !== false ) {
			fieldID = getFieldId( field, true );
			if ( ! ( fieldID in errors ) ) {
				errors[ fieldID ] = getFieldValidationMessage( field, 'data-invmsg' );
			}
		}
	}

	/**
	 * @param {HTMLElement} field
	 * @param {Array}       errors
	 * @return {void}
	 */
	function checkPatternField( field, errors ) {
		let fieldID,
			text = field.value,
			format = getFieldValidationMessage( field, 'pattern' );

		if ( format !== '' && text !== '' ) {
			fieldID = getFieldId( field, true );
			if ( ! ( fieldID in errors ) ) {
				if ( 'object' === typeof window.frmProForm && 'function' === typeof window.frmProForm.isIntlPhoneInput && window.frmProForm.isIntlPhoneInput( field ) ) {
					if ( ! window.frmProForm.validateIntlPhoneInput( field ) ) {
						errors[ fieldID ] = getFieldValidationMessage( field, 'data-invmsg' );
					}
				} else {
					format = new RegExp( '^' + format + '$', 'i' );
					if ( format.test( text ) === false ) {
						errors[ fieldID ] = getFieldValidationMessage( field, 'data-invmsg' );
					}
				}
			}
		}
	}

	/**
	 * Set color for select placeholders.
	 *
	 * @since 6.5.1
	 */
	function setSelectPlaceholderColor() {
		let selects = document.querySelectorAll( '.form-field select' ),
			styleElement = document.querySelector( '.with_frm_style' ),
			textColorDisabled = styleElement ? getComputedStyle( styleElement ).getPropertyValue( '--text-color-disabled' ).trim() : '',
			changeSelectColor;

		// Exit if there are no select elements or the textColorDisabled property is missing
		if ( ! selects.length || ! textColorDisabled ) {
			return;
		}

		// Function to change the color of a select element
		changeSelectColor = function( select ) {
			if ( select.options[select.selectedIndex] && hasClass( select.options[select.selectedIndex], 'frm-select-placeholder' ) ) {
				select.style.setProperty( 'color', textColorDisabled, 'important' );
			} else {
				select.style.color = '';
			}
		};

		// Use a loop to iterate through each select element
		Array.prototype.forEach.call( selects, function( select ) {
			// Apply the color change to each select element
			changeSelectColor( select );

			// Add an event listener for future changes
			select.addEventListener( 'change', function() {
				changeSelectColor( select );
			});
		});
	}

	/**
	 * @param {HTMLElement|jQuery} object
	 * @return {boolean} True if there is an invisible recaptcha.
	 */
	function hasInvisibleRecaptcha( object ) {
		let recaptcha, recaptchaID, alreadyChecked;

		if ( isGoingToPrevPage( object ) ) {
			return false;
		}

		recaptcha = jQuery( object ).find( '.frm-g-recaptcha[data-size="invisible"], .g-recaptcha[data-size="invisible"]' );
		if ( recaptcha.length ) {
			recaptchaID = recaptcha.data( 'rid' );
			alreadyChecked = grecaptcha.getResponse( recaptchaID );
			if ( alreadyChecked.length === 0 ) {
				return recaptcha;
			}
		}
		return false;
	}

	/**
	 * @param {jQuery} invisibleRecaptcha
	 */
	function executeInvisibleRecaptcha( invisibleRecaptcha ) {
		const recaptchaID = invisibleRecaptcha.data( 'rid' );
		grecaptcha.reset( recaptchaID );
		grecaptcha.execute( recaptchaID );
	}

	function validateRecaptcha( form, errors ) {
		let response;

		const $recaptcha = jQuery( form ).find( '.frm-g-recaptcha' );
		if ( ! $recaptcha.length ) {
			return errors;
		}

		const recaptchaID = $recaptcha.data( 'rid' );

		try {
			response = grecaptcha.getResponse( recaptchaID );
		} catch ( e ) {
			if ( jQuery( form ).find( 'input[name="recaptcha_checked"]' ).length ) {
				return errors;
			}
			response = '';
		}

		if ( response.length === 0 ) {
			const fieldContainer = $recaptcha.closest( '.frm_form_field' );
			const fieldID        = fieldContainer.attr( 'id' ).replace( 'frm_field_', '' ).replace( '_container', '' );
			errors[ fieldID ] = '';
		}

		return errors;
	}

	/**
	 * @param {HTMLElement} field
	 * @param {string}      messageType
	 * @return {string} The error message to display.
	 */
	function getFieldValidationMessage( field, messageType ) {
		let msg = field.getAttribute( messageType );
		if ( null === msg ) {
			msg = '';
		}

		if ( '' !== msg && shouldWrapErrorHtmlAroundMessageType( messageType ) ) {
			msg = wrapErrorHtml( msg, field );
		}

		return msg;
	}

	/**
	 * @param {string}      msg
	 * @param {HTMLElement} field
	 * @return {string} The error HTML to use.
	 */
	function wrapErrorHtml( msg, field ) {
		let errorHtml = field.getAttribute( 'data-error-html' );
		if ( null === errorHtml ) {
			return msg;
		}

		errorHtml          = errorHtml.replace( /\+/g, '%20' );
		msg                = decodeURIComponent( errorHtml ).replace( '[error]', msg );
		const fieldId      = getFieldId( field, false );
		const split        = fieldId.split( '-' );
		const fieldIdParts = field.id.split( '_' );
		fieldIdParts.shift(); // Drop the "field" value from the front.
		split[0]       = fieldIdParts.join( '_' );
		const errorKey = split.join( '-' );
		return msg.replace( '[key]', errorKey );
	}

	function shouldWrapErrorHtmlAroundMessageType( type ) {
		return 'pattern' !== type;
	}

	/**
	 * Check if JS validation should happen.
	 *
	 * @param {HTMLElement|Object} object Form object.
	 * @return {boolean} True if validation is enabled and we are not saving a draft or going to a previous page.
	 */
	function shouldJSValidate( object ) {
		if ( 'function' === typeof object.get ) {
			// Get the HTMLElement from a jQuery object.
			object = object.get( 0 );
		}
		let validate = hasClass( object, 'frm_js_validate' );
		if ( validate && typeof frmProForm !== 'undefined' && ( frmProForm.savingDraft( object ) || frmProForm.goingToPreviousPage( object ) ) ) {
			validate = false;
		}

		return validate;
	}

	/**
	 * @param {HTMLElement} object
	 * @param {string}      action
	 * @return {void}
	 */
	function getFormErrors( object, action ) {
		let fieldset, data, success, error, shouldTriggerEvent;

		fieldset = jQuery( object ).find( '.frm_form_field' );
		fieldset.addClass( 'frm_doing_ajax' );

		data               = jQuery( object ).serialize() + '&action=frm_entries_' + action + '&nonce=' + frm_js.nonce; // eslint-disable-line camelcase
		shouldTriggerEvent = object.classList.contains( 'frm_trigger_event_on_submit' );

		const doRedirect = response => {
			jQuery( document ).trigger( 'frmBeforeFormRedirect', [ object, response ]);

			if ( ! response.openInNewTab ) {
				// We return here because we're redirecting there is no need to update content.
				window.location = response.redirect;
				return;
			}

			// We don't return here because we're opening in a new tab, the old tab will still update.
			const newTab = window.open( response.redirect, '_blank' );
			if ( ! newTab && response.fallbackMsg && response.content ) {
				response.content = response.content.trim().replace( /(<\/div><\/div>)$/, ' ' + response.fallbackMsg + '</div></div>' );
			}
		};

		success = function( response ) {
			let defaultResponse, formID, replaceContent, pageOrder, formReturned, contSubmit, delay,
				$fieldCont, key, inCollapsedSection, frmTrigger;

			defaultResponse = {
				content: '',
				errors: {},
				pass: false
			};

			if ( response === null ) {
				response = defaultResponse;
			} else {
				// Response is a string. Convert it to an object.
				response = response.replace( /^\s+|\s+$/g, '' );
				if ( response.indexOf( '{' ) === 0 ) {
					response = JSON.parse( response );
				} else {
					response = defaultResponse;
				}
			}

			if ( typeof response.redirect !== 'undefined' ) {
				if ( shouldTriggerEvent ) {
					triggerCustomEvent( object, 'frmSubmitEvent' );
					return;
				}

				if ( response.delay ) {
					setTimeout( function() {
						doRedirect( response );
					}, 1000 * response.delay );
				} else {
					doRedirect( response );
				}
			}

			if ( 'string' === typeof response.content && response.content !== '' ) {
				// the form or success message was returned

				if ( shouldTriggerEvent ) {
					triggerCustomEvent( object, 'frmSubmitEvent', { content: response.content });
					return;
				}

				removeSubmitLoading( jQuery( object ) );
				if ( frm_js.offset != -1 ) { // eslint-disable-line camelcase
					frmFrontForm.scrollMsg( jQuery( object ), false );
				}

				formID = jQuery( object ).find( 'input[name="form_id"]' ).val();
				response.content = response.content.replace( / frm_pro_form /g, ' frm_pro_form frm_no_hide ' );
				replaceContent = jQuery( object ).closest( '.frm_forms' );
				removeAddedScripts( replaceContent, formID );
				delay = maybeSlideOut( replaceContent, response.content );

				setTimeout(
					function() {
						let container, input, previousInput;

						afterFormSubmittedBeforeReplace( object, response );

						replaceContent.replaceWith( response.content );

						addUrlParam( response );

						if ( typeof frmThemeOverride_frmAfterSubmit === 'function' ) { // eslint-disable-line camelcase
							pageOrder = jQuery( 'input[name="frm_page_order_' + formID + '"]' ).val();
							formReturned = jQuery( response.content ).find( 'input[name="form_id"]' ).val();
							frmThemeOverride_frmAfterSubmit( formReturned, pageOrder, response.content, object );
						}

						if ( typeof response.recaptcha !== 'undefined' ) {
							container = jQuery( '#frm_form_' + formID + '_container' ).find( '.frm_fields_container' );
							input = '<input type="hidden" name="recaptcha_checked" value="' + response.recaptcha + '">';
							previousInput = container.find( 'input[name="recaptcha_checked"]' );

							if ( previousInput.length ) {
								previousInput.replaceWith( input );
							} else {
								container.append( input );
							}
						}

						afterFormSubmitted( object, response );
					},
					delay
				);
			} else if ( Object.keys( response.errors ).length ) {
				// errors were returned
				removeSubmitLoading( jQuery( object ), 'enable' );

				//show errors
				contSubmit = true;
				removeAllErrors();

				$fieldCont = null;

				for ( key in response.errors ) {
					$fieldCont = jQuery( object ).find( '#frm_field_' + key + '_container' );

					if ( $fieldCont.length ) {
						if ( ! $fieldCont.is( ':visible' ) ) {
							inCollapsedSection = $fieldCont.closest( '.frm_toggle_container' );
							if ( inCollapsedSection.length ) {
								frmTrigger = inCollapsedSection.prev();
								if ( ! frmTrigger.hasClass( 'frm_trigger' ) ) {
									// If the frmTrigger object is the section description, check to see if the previous element is the trigger
									frmTrigger = frmTrigger.prev( '.frm_trigger' );
								}
								frmTrigger.trigger( 'click' );
							}
						}

						if ( $fieldCont.is( ':visible' ) ) {
							addFieldError( $fieldCont, key, response.errors );
							contSubmit = false;
						}
					}
				}

				jQuery( object ).find( '.frm-g-recaptcha, .g-recaptcha, .h-captcha' ).each( function() {
					const $recaptcha  = jQuery( this ),
						recaptchaID = $recaptcha.data( 'rid' );

					if ( typeof grecaptcha !== 'undefined' && grecaptcha ) {
						if ( recaptchaID ) {
							grecaptcha.reset( recaptchaID );
						} else {
							grecaptcha.reset();
						}
					}
					if ( typeof hcaptcha !== 'undefined' && hcaptcha ) {
						hcaptcha.reset();
					}
				});

				if ( window.turnstile ) {
					object.querySelectorAll( '.cf-turnstile' ).forEach(
						turnstileField => turnstileField.dataset.rid && turnstile.reset( turnstileField.dataset.rid )
					);
				}

				jQuery( document ).trigger( 'frmFormErrors', [ object, response ]);

				fieldset.removeClass( 'frm_doing_ajax' );
				scrollToFirstField( object );

				if ( contSubmit ) {
					object.submit();
				} else {
					object.insertAdjacentHTML( 'afterbegin', response.error_message );
					checkForErrorsAndMaybeSetFocus();
				}
			} else {
				// there may have been a plugin conflict, or the form is not set to submit with ajax

				showFileLoading( object );

				object.submit();
			}
		};

		error = function() {
			jQuery( object ).find( 'input[type="submit"], input[type="button"]' ).prop( 'disabled', false );
			object.submit();
		};

		postToAjaxUrl( object, data, success, error );
	}

	function postToAjaxUrl( form, data, success, error ) {
		let ajaxUrl, action, ajaxParams;

		ajaxUrl = frm_js.ajax_url; // eslint-disable-line camelcase
		action = form.getAttribute( 'action' );

		if ( 'string' === typeof action && -1 !== action.indexOf( '?action=frm_forms_preview' ) ) {
			ajaxUrl = action.split( '?action=frm_forms_preview' )[0];
		}

		ajaxParams = {
			type: 'POST',
			url: ajaxUrl,
			data: data,
			success: success
		};

		if ( 'function' === typeof error ) {
			ajaxParams.error = error;
		}

		jQuery.ajax( ajaxParams );
	}

	function afterFormSubmitted( object, response ) {
		const formCompleted = jQuery( response.content ).find( '.frm_message' );
		if ( formCompleted.length ) {
			jQuery( document ).trigger( 'frmFormComplete', [ object, response ]);
		} else {
			jQuery( document ).trigger( 'frmPageChanged', [ object, response ]);
		}
	}

	/**
	 * Trigger an event before the form is replaced with a success message.
	 *
	 * @since 6.9
	 *
	 * @param {HTMLElement} object   The form.
	 * @param {Object}      response The response from submitting the form with AJAX.
	 * @return {void}
	 */
	function afterFormSubmittedBeforeReplace( object, response ) {
		const formCompleted = jQuery( response.content ).find( '.frm_message' );
		if ( formCompleted.length ) {
			triggerCustomEvent( document, 'frmFormCompleteBeforeReplace', { object, response });
		}
	}

	function removeAddedScripts( formContainer, formID ) {
		const endReplace = jQuery( '.frm_end_ajax_' + formID );
		if ( endReplace.length ) {
			formContainer.nextUntil( '.frm_end_ajax_' + formID ).remove();
			endReplace.remove();
		}
	}

	function maybeSlideOut( oldContent, newContent ) {
		let c,
			newClass = 'frm_slideout';
		if ( newContent.indexOf( ' frm_slide' ) !== -1 ) {
			c = oldContent.children();
			if ( newContent.indexOf( ' frm_going_back' ) !== -1 ) {
				newClass += ' frm_going_back';
			}
			c.removeClass( 'frm_going_back' );
			c.addClass( newClass );
			return 300;
		}
		return 0;
	}

	function addUrlParam( response ) {
		let url;
		if ( history.pushState && typeof response.page !== 'undefined' ) {
			url = addQueryVar( 'frm_page', response.page );
			window.history.pushState({ 'html': response.html }, '', '?' + url );
		}
	}

	function addQueryVar( key, value ) {
		let kvp, i, x;

		key = encodeURI( key );
		value = encodeURI( value );

		kvp = document.location.search.substr( 1 ).split( '&' );

		i = kvp.length;
		while ( i-- ) {
			x = kvp[i].split( '=' );

			if ( x[0] == key ) {
				x[1] = value;
				kvp[i] = x.join( '=' );
				break;
			}
		}

		if ( i < 0 ) {
			kvp[ kvp.length ] = [ key, value ].join( '=' );
		}

		return kvp.join( '&' );
	}

	function addFieldError( $fieldCont, key, jsErrors ) {
		let input, id, describedBy, roleString;
		if ( $fieldCont.length && $fieldCont.is( ':visible' ) ) {
			$fieldCont.addClass( 'frm_blank_field' );
			input = $fieldCont.find( 'input, select, textarea' );
			id = getErrorElementId( key, input.get( 0 ) );

			describedBy = input.attr( 'aria-describedby' );

			if ( typeof frmThemeOverride_frmPlaceError === 'function' ) { // eslint-disable-line camelcase
				frmThemeOverride_frmPlaceError( key, jsErrors );
			} else {
				if ( -1 !== jsErrors[key].indexOf( '<div' ) ) {
					$fieldCont.append(
						jsErrors[key]
					);
				} else {
					roleString = frm_js.include_alert_role ? 'role="alert"' : ''; // eslint-disable-line camelcase
					$fieldCont.append( '<div class="frm_error" ' + roleString + ' id="' + id + '">' + jsErrors[key] + '</div>' );
				}

				if ( typeof describedBy === 'undefined' ) {
					describedBy = id;
				} else if ( describedBy.indexOf( id ) === -1 && describedBy.indexOf( 'frm_error_field_' ) === -1 ) {
					if ( input.data( 'error-first' ) === 0 ) {
						describedBy = describedBy + ' ' + id;
					} else {
						describedBy = id + ' ' + describedBy;
					}
				}

				input.attr( 'aria-describedby', describedBy );
			}
			input.attr( 'aria-invalid', true );

			jQuery( document ).trigger( 'frmAddFieldError', [ $fieldCont, key, jsErrors ]);
		}
	}

	/**
	 * Get the ID to use for an error element added when submitting with AJAX.
	 *
	 * @param {string}      key
	 * @param {HTMLElement} input
	 * @return {string} The ID to use for the error element.
	 */
	function getErrorElementId( key, input ) {
		if ( isNaN( key ) || ! input || ! input.id ) {
			// If key isn't a number, assume it's already in the right format.
			return 'frm_error_field_' + key;
		}
		return 'frm_error_' + input.id;
	}

	/**
	 * Removes errors before validating with JS.
	 * This prevents issues with stale errors that has since been fixed.
	 *
	 * @param {Object} $fieldCont jQuery object.
	 * @return {void}
	 */
	function removeFieldError( $fieldCont ) {
		const errorMessage = $fieldCont.find( '.frm_error' );
		const errorId      = errorMessage.attr( 'id' );
		const input        = $fieldCont.find( 'input, select, textarea' );
		let describedBy    = input.attr( 'aria-describedby' );

		const fieldContainer = $fieldCont.get( 0 );
		if ( fieldContainer && fieldContainer.classList ) {
			fieldContainer.classList.remove( 'frm_blank_field', 'has-error' );
		}

		errorMessage.remove();
		input.attr( 'aria-invalid', false );
		input.removeAttr( 'aria-describedby' );

		if ( typeof describedBy !== 'undefined' ) {
			describedBy = describedBy.replace( errorId, '' );
			input.attr( 'aria-describedby', describedBy );
		}
	}

	function removeAllErrors() {
		jQuery( '.form-field' ).removeClass( 'frm_blank_field has-error' );
		jQuery( '.form-field .frm_error' ).replaceWith( '' );
		jQuery( '.frm_error_style' ).remove();
	}

	/**
	 * @param {HTMLElement|Object} object Form object.
	 * @return {void}
	 */
	function scrollToFirstField( object ) {
		if ( 'function' === typeof object.get ) {
			// Get the HTMLElement from a jQuery object.
			object = object.get( 0 );
		}
		const field = object.querySelector( '.frm_blank_field' );
		if ( field ) {
			frmFrontForm.scrollMsg( jQuery( field ), object, true );
		}
	}

	function showSubmitLoading( $object ) {
		showLoadingIndicator( $object );
		disableSubmitButton( $object );
		disableSaveDraft( $object );
	}

	function showLoadingIndicator( $object ) {
		if ( ! $object.hasClass( 'frm_loading_form' ) && ! $object.hasClass( 'frm_loading_prev' ) ) {
			addLoadingClass( $object );
			$object.trigger( 'frmStartFormLoading' );
		}
	}

	function addLoadingClass( $object ) {
		const loadingClass = isGoingToPrevPage( $object ) ? 'frm_loading_prev' : 'frm_loading_form';

		$object.addClass( loadingClass );
	}

	function isGoingToPrevPage( $object ) {
		return ( typeof frmProForm !== 'undefined' && frmProForm.goingToPreviousPage( $object ) );
	}

	function removeSubmitLoading( _, enable, processesRunning ) {
		let loadingForm;

		if ( processesRunning > 0 ) {
			return;
		}

		loadingForm = jQuery( '.frm_loading_form' );
		loadingForm.removeClass( 'frm_loading_form' );
		loadingForm.removeClass( 'frm_loading_prev' );

		loadingForm.trigger( 'frmEndFormLoading' );

		if ( enable === 'enable' ) {
			enableSubmitButton( loadingForm );
			enableSaveDraft( loadingForm );
		}
	}

	function showFileLoading( object ) {
		let fileval,
			loading = document.getElementById( 'frm_loading' );
		if ( loading !== null ) {
			fileval = jQuery( object ).find( 'input[type=file]' ).val();
			if ( typeof fileval !== 'undefined' && fileval !== '' ) {
				setTimeout( function() {
					jQuery( loading ).fadeIn( 'slow' );
				}, 2000 );
			}
		}
	}

	/**********************************************
	 * General Helpers
	 *********************************************/

	function confirmClick() {
		/*jshint validthis:true */
		const message = jQuery( this ).data( 'frmconfirm' );
		return confirm( message );
	}

	/**
	 * Check for -webkit-box-shadow css value for input:-webkit-autofill selector.
	 * If this is a match, the User is autofilling the input on a Webkit browser.
	 * We want to delete the Honeypot field, otherwise it will get triggered as spam on autocomplete.
	 */
	function onHoneypotFieldChange() {
		const css = jQuery( this ).css( 'box-shadow' );
		if ( css.match( /inset/ ) ) {
			this.parentNode.removeChild( this );
		}
	}

	/**
	 * Focus on the first sub field when clicking to the primary label of combo field.
	 *
	 * @since 4.10.02
	 */
	function changeFocusWhenClickComboFieldLabel() {
		let label;

		const comboInputsContainer = document.querySelectorAll( '.frm_combo_inputs_container' );
		comboInputsContainer.forEach( function( inputsContainer ) {
			if ( ! inputsContainer.closest( '.frm_form_field' ) ) {
				return;
			}

			label = inputsContainer.closest( '.frm_form_field' ).querySelector( '.frm_primary_label' );
			if ( ! label ) {
				return;
			}

			label.addEventListener( 'click', function() {
				inputsContainer.querySelector( '.frm_form_field:first-child input, .frm_form_field:first-child select, .frm_form_field:first-child textarea' ).focus();
			});
		});
	}

	/**
	 * Sets focus on a the first subfield of a combo field that has an error.
	 *
	 * @since 6.16.3
	 *
	 * @param {HTMLElement} element
	 * @return {boolean} True if the focus was set on a combo field.
	 */
	function maybeFocusOnComboSubField( element ) {
		if ( 'FIELDSET' !== element.nodeName ) {
			return false;
		}
		if ( ! element.querySelector( '.frm_combo_inputs_container' ) ) {
			return false;
		}
		const comboSubfield = element.querySelector( '[aria-invalid="true"]' );
		if ( comboSubfield ) {
			focusInput( comboSubfield );
			return true;
		}
		return false;
	}

	function checkForErrorsAndMaybeSetFocus() {
		let errors, element, timeoutCallback;

		if ( ! frm_js.focus_first_error ) { // eslint-disable-line camelcase
			return;
		}

		errors = document.querySelectorAll( '.frm_form_field .frm_error' );
		if ( ! errors.length ) {
			return;
		}

		element = errors[0];
		do {
			element = element.previousSibling;
			if ( -1 !== [ 'input', 'select', 'textarea' ].indexOf( element.nodeName.toLowerCase() ) ) {
				focusInput( element );
				break;
			}

			if ( maybeFocusOnComboSubField( element ) ) {
				break;
			}

			if ( 'undefined' !== typeof element.classList ) {
				if ( element.classList.contains( 'html-active' ) ) {
					timeoutCallback = function() {
						const textarea = element.querySelector( 'textarea' );
						if ( null !== textarea ) {
							textarea.focus();
						}
					};
				} else if ( element.classList.contains( 'tmce-active' ) ) {
					timeoutCallback = function() {
						tinyMCE.activeEditor.focus();
					};
				} else if ( element.classList.contains( 'frm_opt_container' ) ) {
					const firstInput = element.querySelector( 'input' );
					if ( firstInput ) {
						focusInput( firstInput );
						break;
					}
				}

				if ( 'function' === typeof timeoutCallback ) {
					setTimeout( timeoutCallback, 0 );
					break;
				}
			}
		} while ( element.previousSibling );
	}

	/**
	 * Focus a visible input, or possibly delay the focus event until the form has faded in.
	 *
	 * @since 6.16.3
	 *
	 * @param {HTMLElement} input
	 * @return {void}
	 */
	function focusInput( input ) {
		if ( input.offsetParent !== null ) {
			input.focus();
		} else {
			triggerCustomEvent( document, 'frmMaybeDelayFocus', { input });
		}
	}

	/**
	 * Does the same as jQuery( document ).on( 'event', 'selector', handler ).
	 *
	 * @since 5.4
	 *
	 * @param {string}           event    Event name.
	 * @param {string}           selector Selector.
	 * @param {Function}         handler  Handler.
	 * @param {boolean | Object} options  Options to be added to `addEventListener()` method. Default is `false`.
	 */
	function documentOn( event, selector, handler, options ) {
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

	function initFloatingLabels() {
		let checkFloatLabel, checkDropdownLabel, runOnLoad, selector, floatClass;

		selector   = '.frm-show-form .frm_inside_container input, .frm-show-form .frm_inside_container select, .frm-show-form .frm_inside_container textarea';
		floatClass = 'frm_label_float_top';

		checkFloatLabel = function( input ) {
			let container, shouldFloatTop, firstOpt;

			container = input.closest( '.frm_inside_container' );
			if ( ! container ) {
				return;
			}

			shouldFloatTop = input.value || document.activeElement === input;

			container.classList.toggle( floatClass, shouldFloatTop );

			if ( 'SELECT' === input.tagName ) {
				firstOpt = input.querySelector( 'option:first-child' );

				if ( shouldFloatTop ) {
					if ( firstOpt.hasAttribute( 'data-label' ) ) {
						firstOpt.textContent = firstOpt.getAttribute( 'data-label' );
						firstOpt.removeAttribute( 'data-label' );
					}
				} else if ( firstOpt.textContent ) {
					firstOpt.setAttribute( 'data-label', firstOpt.textContent );
					firstOpt.textContent = '';
				}
			}
		};

		checkDropdownLabel = function() {
			document.querySelectorAll( '.frm-show-form .frm_inside_container:not(.' + floatClass + ') select' ).forEach( function( input ) {
				const firstOpt = input.querySelector( 'option:first-child' );

				if ( firstOpt.textContent ) {
					firstOpt.setAttribute( 'data-label', firstOpt.textContent );
					firstOpt.textContent = '';
				}
			});
		};

		[ 'focus', 'blur', 'change' ].forEach( function( eventName ) {
			documentOn(
				eventName,
				selector,
				function( event ) {
					checkFloatLabel( event.target );
				},
				true
			);
		});

		jQuery( document ).on( 'change', selector, function( event ) {
			checkFloatLabel( event.target );
		});

		runOnLoad = function( firstLoad ) {
			if ( firstLoad && document.activeElement && -1 !== [ 'INPUT', 'SELECT', 'TEXTAREA' ].indexOf( document.activeElement.tagName ) ) {
				checkFloatLabel( document.activeElement );
			} else if ( firstLoad ) {
				document.querySelectorAll( '.frm_inside_container' ).forEach(
					function( container ) {
						const input = container.querySelector( 'input, select, textarea' );
						if ( input && '' !== input.value ) {
							checkFloatLabel( input );
						}
					}
				);
			}

			checkDropdownLabel();
		};

		runOnLoad( true );

		jQuery( document ).on( 'frmPageChanged', function( event ) {
			runOnLoad();
		});

		document.addEventListener( 'frm_after_start_over', function( event ) {
			runOnLoad();
		});
	}

	function shouldUpdateValidityMessage( target ) {
		if ( 'INPUT' !== target.nodeName ) {
			return false;
		}

		if ( ! target.dataset.invmsg ) {
			return false;
		}

		if ( 'text' !== target.getAttribute( 'type' ) ) {
			return false;
		}

		if ( target.classList.contains( 'frm_verify' ) ) {
			return false;
		}

		return true;
	}

	function maybeClearCustomValidityMessage( event, field ) {
		let key,
			isInvalid = false;

		if ( ! shouldUpdateValidityMessage( field ) ) {
			return;
		}

		for ( key in field.validity ) {
			if ( 'customError' === key ) {
				continue;
			}
			if ( 'valid' !== key && field.validity[ key ] === true ) {
				isInvalid = true;
				break;
			}
		};

		if ( ! isInvalid ) {
			field.setCustomValidity( '' );
		}
	}

	function maybeShowNewTabFallbackMessage() {
		let messageEl;

		if ( ! window.frmShowNewTabFallback ) {
			return;
		}

		messageEl = document.querySelector( '#frm_form_' + frmShowNewTabFallback.formId + '_container .frm_message' );
		if ( ! messageEl ) {
			return;
		}

		messageEl.insertAdjacentHTML( 'beforeend', ' ' + frmShowNewTabFallback.message );
	}

	function setCustomValidityMessage() {
		let forms, length, index;

		forms  = document.getElementsByClassName( 'frm-show-form' );
		length = forms.length;

		for ( index = 0; index < length; ++index ) {
			forms[ index ].addEventListener(
				'invalid',
				function( event ) {
					const target = event.target;

					if ( shouldUpdateValidityMessage( target ) ) {
						target.setCustomValidity( target.dataset.invmsg );
					}
				},
				true
			);
		}
	}

	function enableSubmitButtonOnBackButtonPress() {
		window.addEventListener( 'pageshow', function( event ) {
			if ( event.persisted ) {
				document.querySelectorAll( '.frm_loading_form' ).forEach(
					function( form ) {
						enableSubmitButton( jQuery( form ) );
					}
				);
				removeSubmitLoading();
			}
		});
	}

	/**
	 * Destroys the formidable generated global hcaptcha object since it wouldn't otherwise render.
	 */
	function destroyhCaptcha() {
		if ( ! window.hasOwnProperty( 'hcaptcha' ) || ! document.querySelector( '.frm-show-form .h-captcha' ) ) {
			return;
		}
		window.hcaptcha = null;
	}

	/**
	 * @since 6.16.3
	 *
	 * @return {string} Unique key, used for duplicate checks.
	 */
	function getUniqueKey() {
		const uniqueKey = Array.from( window.crypto.getRandomValues( new Uint8Array( 8 ) ) )
			.map( b => b.toString( 16 ).padStart( 2, '0' ) )
			.join( '' );
		const timestamp = Date.now().toString( 16 );
		return uniqueKey + '-' + timestamp;
	}

	/**
	 * Animates the scroll position of the document.
	 *
	 * @since 6.20
	 *
	 * @param {number} start
	 * @param {number} end
	 * @param {number} duration
	 * @return {void}
	 */
	function animateScroll( start, end, duration ) {
		if ( ! window.hasOwnProperty( 'performance' ) || ! window.hasOwnProperty( 'requestAnimationFrame' ) ) {
			document.documentElement.scrollTop = end;
			return;
		}

		/* eslint-disable compat/compat */
		const startTime = performance.now();
		const step      = ( currentTime ) => {
			const progress = Math.min( ( currentTime - startTime ) / duration, 1 );
			document.documentElement.scrollTop = start + ( end - start ) * progress;
			if ( progress < 1 ) {
				requestAnimationFrame( step );
			}
		};
		requestAnimationFrame( step );
		/* eslint-enable compat/compat */
	}

	return {
		init: function() {
			jQuery( document ).off( 'submit.formidable', '.frm-show-form' );
			jQuery( document ).on( 'submit.formidable', '.frm-show-form', frmFrontForm.submitForm );

			jQuery( '.frm-show-form input[onblur], .frm-show-form textarea[onblur]' ).each( function() {
				if ( jQuery( this ).val() === '' ) {
					jQuery( this ).trigger( 'blur' );
				}
			});

			jQuery( document ).on( 'change', '.frm-show-form input[name^="item_meta"], .frm-show-form select[name^="item_meta"], .frm-show-form textarea[name^="item_meta"]', frmFrontForm.fieldValueChanged );

			jQuery( document ).on( 'change', '.frm_verify[id^=field_]', onHoneypotFieldChange );

			jQuery( document ).on( 'click', 'a[data-frmconfirm]', confirmClick );

			checkForErrorsAndMaybeSetFocus();

			// Focus on the first sub field when clicking to the primary label of combo field.
			changeFocusWhenClickComboFieldLabel();

			initFloatingLabels();
			maybeShowNewTabFallbackMessage();

			jQuery( document ).on( 'frmAfterAddRow', setCustomValidityMessage );
			setCustomValidityMessage();
			jQuery( document ).on( 'frmFieldChanged', maybeClearCustomValidityMessage );

			setSelectPlaceholderColor();

			// Elementor popup show event. Fix Elementor Popup && FF Captcha field conflicts
			jQuery( document ).on( 'elementor/popup/show', frmRecaptcha );

			enableSubmitButtonOnBackButtonPress();
			jQuery( document ).on(
				'frmPageChanged',
				destroyhCaptcha
			);
		},

		getFieldId,

		/**
		 * Render a captcha field.
		 *
		 * @param {HTMLElement} captcha
		 * @param {string}      captchaSelector
		 * @return {void}
		 */
		renderCaptcha: function( captcha, captchaSelector ) {
			const rendered = captcha.getAttribute( 'data-rid' ) !== null;
			if ( rendered ) {
				return;
			}

			const size   = captcha.getAttribute( 'data-size' );
			const params = {
				sitekey: captcha.getAttribute( 'data-sitekey' ),
				size: size,
				theme: captcha.getAttribute( 'data-theme' )
			};

			if ( size === 'invisible' ) {
				const formID = captcha.closest( 'form' )?.querySelector( 'input[name="form_id"]' )?.value;

				const captchaLabel = captcha.closest( '.frm_form_field' )?.querySelector( '.frm_primary_label' );
				if ( captchaLabel ) {
					captchaLabel.style.display = 'none';
				}

				params.callback = function( token ) {
					frmFrontForm.afterRecaptcha( token, formID );
				};
			}

			const activeCaptcha    = getSelectedCaptcha( captchaSelector );
			const captchaContainer = typeof turnstile !== 'undefined' && turnstile === activeCaptcha ? '#' + captcha.id : captcha.id;
			const captchaID        = activeCaptcha.render( captchaContainer, params );

			captcha.setAttribute( 'data-rid', captchaID );
		},

		afterSingleRecaptcha: function() {
			const object = jQuery( '.frm-show-form .g-recaptcha' ).closest( 'form' )[0];
			frmFrontForm.submitFormNow( object );
		},

		afterRecaptcha: function( _, formID ) {
			const object = jQuery( '#frm_form_' + formID + '_container form' )[0];
			frmFrontForm.submitFormNow( object );
		},

		submitForm: function( e ) {
			frmFrontForm.submitFormManual( e, this );
		},

		/**
		 * @param {Event}       e
		 * @param {HTMLElement} object The form object that is being submitted.
		 * @return {void}
		 */
		submitFormManual: function( e, object ) {
			let isPro, errors,
				invisibleRecaptcha = hasInvisibleRecaptcha( object ),
				classList = object.className.trim().split( /\s+/gi );

			if ( classList && invisibleRecaptcha.length < 1 ) {
				isPro = classList.indexOf( 'frm_pro_form' ) > -1;
				if ( ! isPro ) {
					return;
				}
			}

			if ( jQuery( 'body' ).hasClass( 'wp-admin' ) && jQuery( object ).closest( '.frmapi-form' ).length < 1 ) {
				return;
			}

			e.preventDefault();

			if ( typeof frmProForm !== 'undefined' && typeof frmProForm.submitAllowed === 'function' && ! frmProForm.submitAllowed( object ) ) {
				return;
			}

			errors = frmFrontForm.validateFormSubmit( object );
			if ( Object.keys( errors ).length !== 0 ) {
				return;
			}

			if ( invisibleRecaptcha.length ) {
				showLoadingIndicator( jQuery( object ) );
				executeInvisibleRecaptcha( invisibleRecaptcha );
			} else {

				showSubmitLoading( jQuery( object ) );

				frmFrontForm.submitFormNow( object, classList );
			}
		},

		submitFormNow: function( object ) {
			let hasFileFields, antispamInput,
				classList = object.className.trim().split( /\s+/gi );

			if ( object.hasAttribute( 'data-token' ) && null === object.querySelector( '[name="antispam_token"]' ) ) {
				// include the antispam token on form submit.
				antispamInput = document.createElement( 'input' );
				antispamInput.type = 'hidden';
				antispamInput.name = 'antispam_token';
				antispamInput.value = object.getAttribute( 'data-token' );
				object.appendChild( antispamInput );
			}

			// Add a unique ID, used for duplicate checks.
			const uniqueIDInput = document.createElement( 'input' );
			uniqueIDInput.type  = 'hidden';
			uniqueIDInput.name  = 'unique_id';
			uniqueIDInput.value = getUniqueKey();
			object.appendChild( uniqueIDInput );

			if ( classList.indexOf( 'frm_ajax_submit' ) > -1 ) {
				hasFileFields = jQuery( object ).find( 'input[type="file"]' ).filter( function() {
					return !! this.value;
				}).length;
				if ( hasFileFields < 1 ) {
					const action = jQuery( object ).find( 'input[name="frm_action"]' ).val();
					frmFrontForm.checkFormErrors( object, action );
				} else {
					object.submit();
				}
			} else {
				object.submit();
			}
		},

		/**
		 * @param {HTMLElement|Object} object Form object. This might be a jQuery object.
		 *
		 * @return {Array} List of errors.
		 */
		validateFormSubmit: function( object ) {
			if ( typeof tinyMCE !== 'undefined' && jQuery( object ).find( '.wp-editor-wrap' ).length ) {
				tinyMCE.triggerSave();
			}

			jsErrors = [];

			if ( shouldJSValidate( object ) ) {
				frmFrontForm.getAjaxFormErrors( object );

				if ( Object.keys( jsErrors ).length ) {
					frmFrontForm.addAjaxFormErrors( object );
				}
			}

			return jsErrors;
		},

		/**
		 * @param {HTMLElement|Object} object Form object. This might be a jQuery object.
		 * @return {Array} List of errors.
		 */
		getAjaxFormErrors: function( object ) {
			let customErrors, key;

			jsErrors = validateForm( object );
			if ( typeof frmThemeOverride_jsErrors === 'function' ) { // eslint-disable-line camelcase
				const action = jQuery( object ).find( 'input[name="frm_action"]' ).val();
				customErrors = frmThemeOverride_jsErrors( action, object );
				if ( Object.keys( customErrors ).length  ) {
					for ( key in customErrors ) {
						jsErrors[ key ] = customErrors[ key ];
					}
				}
			}

			triggerCustomEvent( document, 'frm_get_ajax_form_errors', {
				formEl: object,
				errors: jsErrors
			});

			return jsErrors;
		},

		/**
		 * @param {HTMLElement|Object} object Form object. This might be a jQuery object.
		 * @return {void}
		 */
		addAjaxFormErrors: function( object ) {
			let key, $fieldCont;
			removeAllErrors();

			for ( key in jsErrors ) {
				$fieldCont = jQuery( object ).find( '#frm_field_' + key + '_container' );

				if ( $fieldCont.length ) {
					addFieldError( $fieldCont, key, jsErrors );
				} else {
					// we are unable to show the error, so remove it
					delete jsErrors[ key ];
				}
			}

			scrollToFirstField( object );
			checkForErrorsAndMaybeSetFocus();
		},

		checkFormErrors: getFormErrors,
		checkRequiredField,
		showSubmitLoading,
		removeSubmitLoading,

		scrollToID: function( id ) {
			const object = jQuery( document.getElementById( id ) );
			frmFrontForm.scrollMsg( object, false );
		},

		scrollMsg: function( id, object, animate ) {
			let newPos, m, b, screenTop, screenBottom,
				scrollObj = '';
			if ( typeof object === 'undefined' ) {
				scrollObj = jQuery( document.getElementById( 'frm_form_' + id + '_container' ) );
				if ( scrollObj.length < 1 ) {
					return;
				}
			} else if ( typeof id === 'string' ) {
				scrollObj = jQuery( object ).find( '#frm_field_' + id + '_container' );
			} else {
				scrollObj = id;
			}

			jQuery( scrollObj ).trigger( 'focus' );
			newPos = scrollObj.offset().top;
			if ( ! newPos || frm_js.offset === '-1' ) { // eslint-disable-line camelcase
				return;
			}
			newPos = newPos - frm_js.offset; // eslint-disable-line camelcase

			m = jQuery( 'html' ).css( 'margin-top' );
			b = jQuery( 'body' ).css( 'margin-top' );
			if ( m || b ) {
				newPos = newPos - parseInt( m ) - parseInt( b );
			}

			if ( newPos && window.innerHeight ) {
				screenTop = document.documentElement.scrollTop || document.body.scrollTop;
				screenBottom = screenTop + window.innerHeight;

				if ( newPos > screenBottom || newPos < screenTop ) {
					// Not in view
					if ( typeof animate === 'undefined' ) {
						document.documentElement.scrollTop = newPos;
					} else {
						animateScroll( screenTop, newPos, 500 );
					}
					return false;
				}
			}
		},

		fieldValueChanged: function( e ) {
			/*jshint validthis:true */

			const fieldId = frmFrontForm.getFieldId( this, false );
			if ( ! fieldId || typeof fieldId === 'undefined' ) {
				return;
			}

			if ( e.frmTriggered && e.frmTriggered == fieldId ) {
				return;
			}

			jQuery( document ).trigger( 'frmFieldChanged', [ this, fieldId, e ]);

			if ( e.selfTriggered !== true ) {
				maybeValidateChange( this );
			}
		},

		escapeHtml: function( text ) {
			console.warn( 'DEPRECATED: function frmFrontForm.escapeHtml in v6.17' );
			return text
				.replace( /&/g, '&amp;' )
				.replace( /</g, '&lt;' )
				.replace( />/g, '&gt;' )
				.replace( /"/g, '&quot;' )
				.replace( /'/g, '&#039;' );
		},

		/**
		 * This function was used in old back end code in v2.0.
		 *
		 * @param {string} classes
		 * @return {void}
		 */
		invisible: function( classes ) {
			console.warn( 'DEPRECATED: function frmFrontForm.invisible in v6.16.3' );
			jQuery( classes ).css( 'visibility', 'hidden' );
		},

		/**
		 * This function was used in old back end code in v2.0.
		 *
		 * @param {string} classes
		 * @return {void}
		 */
		visible: function( classes ) {
			console.warn( 'DEPRECATED: function frmFrontForm.visible in v6.16.3' );
			jQuery( classes ).css( 'visibility', 'visible' );
		},

		triggerCustomEvent: triggerCustomEvent,
		documentOn
	};
}

window.frmFrontForm = frmFrontFormJS();

jQuery( document ).ready( function() {
	frmFrontForm.init();
});

function frmRecaptcha() {
	frmCaptcha( '.frm-g-recaptcha' );
}

function frmTurnstile() {
	frmCaptcha( '.cf-turnstile' );
}

function frmCaptcha( captchaSelector ) {
	let c;
	const captchas = document.querySelectorAll( captchaSelector );
	const cl       = captchas.length;
	for ( c = 0; c < cl; c++ ) {
		const closestForm   = captchas[c].closest( 'form' );
		const formIsVisible = closestForm && closestForm.offsetParent !== null;
		const captcha       = captchas[c];
		if ( ! formIsVisible ) {
			// If the form is not visible, try again later in 400ms.
			// This fixes issues where the form fades visible on page load.
			// Or whne the form is inside of a modal.
			const interval = setInterval(
				function() {
					if ( closestForm && closestForm.offsetParent !== null ) {
						frmFrontForm.renderCaptcha( captcha, captchaSelector );
						clearInterval( interval );
					}
				},
				400
			);
			continue;
		}
		frmFrontForm.renderCaptcha( captcha, captchaSelector );
	}
}

function getSelectedCaptcha( captchaSelector ) {
	if ( captchaSelector === '.frm-g-recaptcha' ) {
		return grecaptcha;
	}
	if ( document.querySelector( '.cf-turnstile' ) ) {
		return turnstile;
	}
	return hcaptcha;
}

function frmAfterRecaptcha( token ) {
	frmFrontForm.afterSingleRecaptcha( token );
}
