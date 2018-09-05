function frmFrontFormJS(){
	'use strict';

    /*global jQuery:false, frm_js */

	var action = '';
	var jsErrors = [];

	function maybeShowLabel(){
		/*jshint validthis:true */
		var $field = jQuery(this);
		var $label = $field.closest('.frm_inside_container').find('label.frm_primary_label');

		if ( $field.val().length > 0 ) {
			$label.addClass('frm_visible');
		} else {
			$label.removeClass('frm_visible');
		}
	}

	/* Get the ID of the field that changed*/
	function getFieldId( field, fullID ) {
		var fieldName = '';
		if ( field instanceof jQuery ) {
			fieldName = field.attr('name');
		} else {
			fieldName = field.name;
		}

		if ( fieldName === '' ) {
			if ( field instanceof jQuery ) {
				fieldName = field.data('name');
			} else {
				fieldName = field.getAttribute('data-name');
			}

			if ( fieldName !== '' && fieldName ) {
				return fieldName;
			}
			return 0;
		}

		var nameParts = fieldName.replace('item_meta[', '').replace('[]', '').split(']');
		//TODO: Fix this for checkboxes and address fields
		if ( nameParts.length < 1 ) {
			return 0;
		}
		nameParts = nameParts.filter(function(n){ return n !== ''; });

		var field_id = nameParts[0];
		var isRepeating = false;

		if ( nameParts.length === 1 ) {
			return field_id;
		}

		if ( nameParts[1] === '[form' || nameParts[1] === '[row_ids' ) {
			return 0;
		}


		// Check if 'this' is in a repeating section
		if ( jQuery('input[name="item_meta['+ field_id +'][form]"]').length ) {

			// this is a repeatable section with name: item_meta[repeating-section-id][row-id][field-id]
			field_id = nameParts[2].replace('[', '');
			isRepeating = true;
		}

		// Check if 'this' is an other text field and get field ID for it
		if ( 'other' === field_id ) {
			if ( isRepeating ) {
				// name for other fields: item_meta[370][0][other][414]
				field_id = nameParts[3].replace('[', '');
			} else {
				// Other field name: item_meta[other][370]
				field_id = nameParts[1].replace('[', '');
			}
		}

		if ( fullID === true ) {
			// For use in the container div id
			if ( field_id === nameParts[0] ) {
				field_id = field_id +'-'+ nameParts[1].replace('[', '');
			} else {
				field_id = field_id +'-'+ nameParts[0] +'-'+ nameParts[1].replace('[', '');
			}
		}

		return field_id;
	}

	/**
	 * Disable the submit button for a given jQuery form object
	 *
	 * @since 2.03.02
	 *
	 * @param {object} $form
     */
	function disableSubmitButton( $form ) {
		$form.find('input[type="submit"], input[type="button"], button[type="submit"]').attr('disabled','disabled');
	}

	/**
	 * Enable the submit button for a given jQuery form object
	 *
	 * @since 2.03.02
	 *
	 * @param {object} $form
     */
	function enableSubmitButton( $form ) {
		$form.find( 'input[type="submit"], input[type="button"], button[type="submit"]' ).removeAttr( 'disabled' );
	}

	function validateForm( object ) {
		var errors = [];

		// Make sure required text field is filled in
		var requiredFields = jQuery(object).find(
			'.frm_required_field:visible input, .frm_required_field:visible select, .frm_required_field:visible textarea'
		).filter(':not(.frm_optional)');
		if ( requiredFields.length ) {
			for ( var r = 0, rl = requiredFields.length; r < rl; r++ ) {
				errors = checkRequiredField( requiredFields[r], errors );
			}
		}

		var emailFields = jQuery(object).find('input[type=email]').filter(':visible');
		var fields = jQuery(object).find('input,select,textarea');
		if ( fields.length ) {
			for ( var n = 0, nl = fields.length; n < nl; n++ ) {
				var field = fields[n];
				var value = field.value;
				if ( value !== '' ) {
					if ( field.type === 'hidden' ) {
						// don't validate
					} else if ( field.type === 'number' ) {
						errors = checkNumberField( field, errors );
					} else if ( field.type === 'email' ) {
						errors = checkEmailField( field, errors, emailFields );
					} else if (field.type === 'password') {
						errors = checkPasswordField(field, errors);
					} else if ( field.pattern !== null ) {
						errors = checkPatternField( field, errors );
					}
				}
			}
		}

		errors = validateRecaptcha( object, errors );

		return errors;
	}

	function maybeValidateChange( field_id, field ) {
		if ( jQuery(field).closest('form').hasClass('frm_js_validate') ) {
			validateField( field_id, field );
		}
	}

	function validateField( fieldId, field ) {
		var errors = [];

		var $fieldCont = jQuery(field).closest('.frm_form_field');
		if ( $fieldCont.hasClass('frm_required_field') && ! jQuery(field).hasClass('frm_optional') ) {
			errors = checkRequiredField( field, errors );
		}

		if ( errors.length < 1 ) {
			if ( field.type === 'email' ) {
				var emailFields = jQuery(field).closest('form').find('input[type=email]');
				errors = checkEmailField( field, errors, emailFields );
			} else if ( field.type === 'number' ) {
				errors = checkNumberField( field, errors );
			} else if (field.type === 'password') {
				errors = checkPasswordField( field, errors );
			} else if ( field.pattern !== null ) {
				errors = checkPatternField( field, errors );
			}
		}

		removeFieldError( $fieldCont );
		if (  Object.keys(errors).length > 0 ) {
			for ( var key in errors ) {
				addFieldError( $fieldCont, key, errors );
			}
		}
	}

	function checkRequiredField( field, errors ) {
		var fileID = field.getAttribute('data-frmfile');
		if ( field.type === 'hidden' && fileID === null ) {
			return errors;
		}

		var val = '';
		var fieldID = '';
		if ( field.type === 'checkbox' || field.type === 'radio' ) {
			var checkGroup = jQuery('input[name="'+field.name+'"]').closest('.frm_required_field').find('input:checked');
			jQuery(checkGroup).each(function() {
			    val = this.value;
			});
		} else if ( field.type === 'file' || fileID ) {
			if ( typeof fileID === 'undefined' ) {
				fileID = getFieldId( field, true );
				fileID = fileID.replace('file', '');
			}

			if ( typeof errors[ fileID ] === 'undefined' ) {
				val = getFileVals( fileID );
			}
			fieldID = fileID;
		} else {
			var fieldClasses = field.className;
			if ( fieldClasses.indexOf('frm_pos_none') !== -1 ) {
				// skip hidden other fields
				return errors;
			}

			val = jQuery(field).val();
			if ( val === null ) {
				val = '';
			} else if ( typeof val !== 'string' ) {
				var tempVal = val;
				val = '';
				for ( var i = 0; i < tempVal.length; i++ ) {
					if ( tempVal[i] !== '' ) {
						val = tempVal[i];
					}
				}
			}

			if ( fieldClasses.indexOf('frm_other_input') === -1 ) {
				fieldID = getFieldId( field, true );
			} else {
				fieldID = getFieldId( field, false );
			}

			if ( fieldClasses.indexOf('frm_time_select') !== -1 ) {
				// set id for time field
				fieldID = fieldID.replace('-H', '').replace('-m', '');
			}

			var placeholder = field.getAttribute('data-frmplaceholder');
			if ( placeholder !== null && val === placeholder ) {
				val = '';
			}
		}

		if ( val === '' ) {
			if ( fieldID === '' ) {
				fieldID = getFieldId( field, true );
			}
			if ( !(fieldID in errors) ) {
				errors[ fieldID ] = getFieldValidationMessage( field, 'data-reqmsg' );
			}
		}

		return errors;
	}

	function getFileVals( fileID ) {
		var val = '';
		var fileFields = jQuery('input[name="file'+ fileID +'"], input[name="file'+ fileID +'[]"], input[name^="item_meta['+ fileID +']"]');
		fileFields.each(function(){
			if ( val === '' ) {
				val = this.value;
			}
		});
		return val;
	}

	function checkEmailField( field, errors, emailFields ) {
		var emailAddress = field.value;
		var fieldID = getFieldId( field, true );
		if ( fieldID in errors ) {
			return errors;
		}

		var isConf = (fieldID.indexOf('conf_') === 0);
		if ( emailAddress !== '' || isConf ) {
			var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;
			var invalidMsg = getFieldValidationMessage( field, 'data-invmsg' );
			if ( emailAddress !== '' && re.test( emailAddress ) === false ) {
				errors[ fieldID ] = invalidMsg;
				if ( isConf ) {
					errors[ fieldID.replace('conf_', '') ] = '';
				}
			} else if ( isConf ) {
				var confName = field.name.replace('conf_', '');
				var match = emailFields.filter('[name="'+ confName +'"]').val();
				if ( match !== emailAddress ) {
					errors[ fieldID ] = '';
					errors[ fieldID.replace('conf_', '') ] = '';
				}
			}
		}
		return errors;
	}

	function checkNumberField( field, errors ) {
		var number = field.value;
		if ( number !== '' && isNaN(number / 1) !== false ) {
			var fieldID = getFieldId( field, true );
			if ( !(fieldID in errors) ) {
				errors[ fieldID ] = getFieldValidationMessage( field, 'data-invmsg' );
			}
		}
		return errors;
	}

	function checkPatternField( field, errors ) {
		var text = field.value;
		var format = getFieldValidationMessage( field, 'pattern' );

		if ( format !== '' && text !== '' ) {
			var fieldID = getFieldId( field, true );
			if ( !(fieldID in errors) ) {
				format = new RegExp( '^'+ format +'$', 'i' );
				if ( format.test( text ) === false ) {
					errors[ fieldID ] = getFieldValidationMessage( field, 'data-invmsg' );
				}
			}
		}
		return errors;
	}

	function checkPasswordField( field, errors ) {
		var classes = field.className;

		if (classes.indexOf('frm_strong_pass') < 0) {
			return errors;
		}

		var text = field.value;
		var regEx = /^(?=.*?[a-z])(?=.*?[A-Z])(?=.*[^a-zA-Z0-9])(?=.*?[0-9]).{8,}$/;
		var matches = regEx.test(text); //true if matches format, false otherwise

		if (!matches) {
			var fieldID = getFieldId( field, true );
			errors[ fieldID ] = getFieldValidationMessage( field, 'data-invmsg' );
		}

		return errors;
	}

	function hasInvisibleRecaptcha( object ) {
		if ( typeof frmProForm !== 'undefined' && frmProForm.goingToPreviousPage( object ) ) {
			return false;
		}

		var recaptcha = jQuery(object).find('.frm-g-recaptcha[data-size="invisible"], .g-recaptcha[data-size="invisible"]');
		if ( recaptcha.length ) {
			var recaptchaID = recaptcha.data('rid');
			var alreadyChecked = grecaptcha.getResponse( recaptchaID );
			if ( alreadyChecked.length === 0 ) {
				return recaptcha;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function executeInvisibleRecaptcha( invisibleRecaptcha ) {
		var recaptchaID = invisibleRecaptcha.data('rid');
		grecaptcha.reset( recaptchaID );
		grecaptcha.execute( recaptchaID );
	}

	function validateRecaptcha( form, errors ) {
		var $recaptcha = jQuery(form).find('.frm-g-recaptcha');
		if ( $recaptcha.length ) {
			var recaptchaID = $recaptcha.data('rid');
			var response = grecaptcha.getResponse( recaptchaID );

			if ( response.length === 0 ) {
				var fieldContainer = $recaptcha.closest('.frm_form_field');
				var fieldID = fieldContainer.attr('id').replace('frm_field_', '').replace('_container', '');
				errors[ fieldID ] = '';
			}
		}
		return errors;
	}

	function getFieldValidationMessage( field, messageType ) {
		var msg = field.getAttribute(messageType);
		if ( msg === null ) {
			msg = '';
		}
		return msg;
	}

	function shouldJSValidate( object ) {
		var validate = jQuery(object).hasClass('frm_js_validate');
		if ( validate && typeof frmProForm !== 'undefined' && ( frmProForm.savingDraft( object ) || frmProForm.goingToPreviousPage( object ) ) ) {
			validate = false;
		}

		return validate;
	}

	function getFormErrors(object, action){
		if(typeof action === 'undefined'){
			jQuery(object).find('input[name="frm_action"]').val();
		}

		var fieldset = jQuery(object).find('.frm_form_field');
		fieldset.addClass('frm_doing_ajax');
		jQuery.ajax({
			type:'POST',url:frm_js.ajax_url,
			data:jQuery(object).serialize() +'&action=frm_entries_'+ action +'&nonce='+frm_js.nonce,
			success:function(response){
				var defaultResponse = {'content':'', 'errors':{}, 'pass':false };
				if ( response === null ) {
					response = defaultResponse;
				}

				response = response.replace(/^\s+|\s+$/g,'');
				if ( response.indexOf('{') === 0 ) {
					response = jQuery.parseJSON(response);
				}else{
					response = defaultResponse;
				}

				if ( typeof response.redirect !== 'undefined' ) {
					jQuery(document).trigger( 'frmBeforeFormRedirect', [ object, response ] );
					window.location = response.redirect;
				} else if ( response.content !== '' ) {
					// the form or success message was returned

					removeSubmitLoading( jQuery(object) );
					if ( frm_js.offset != -1 ) {
						frmFrontForm.scrollMsg( jQuery(object), false );
					}
					var formID = jQuery(object).find('input[name="form_id"]').val();
					response.content = response.content.replace(/ frm_pro_form /g, ' frm_pro_form frm_no_hide ');
					var replaceContent = jQuery( object ).closest( '.frm_forms' );
					removeAddedScripts( replaceContent, formID );
					replaceContent.replaceWith( response.content );

					addUrlParam(response);

					if(typeof(frmThemeOverride_frmAfterSubmit) === 'function'){
						var pageOrder = jQuery('input[name="frm_page_order_'+ formID +'"]').val();
						var formReturned = jQuery(response.content).find('input[name="form_id"]').val();
						frmThemeOverride_frmAfterSubmit(formReturned, pageOrder, response.content, object);
					}

					afterFormSubmitted( object, response );

				} else if ( Object.keys(response.errors).length ) {
					// errors were returned

					removeSubmitLoading( jQuery(object), 'enable' );

					//show errors
					var cont_submit = true;
					removeAllErrors();

					var show_captcha = false;
					var $fieldCont = null;

					for ( var key in response.errors ) {
						$fieldCont = jQuery(object).find('#frm_field_'+key+'_container');

						if ( $fieldCont.length ) {
							if ( ! $fieldCont.is(':visible') ) {
								var inCollapsedSection = $fieldCont.closest('.frm_toggle_container');
								if ( inCollapsedSection.length ) {
									var frmTrigger = inCollapsedSection.prev();
									if ( ! frmTrigger.hasClass('frm_trigger') ) {
										// If the frmTrigger object is the section description, check to see if the previous element is the trigger
										frmTrigger = frmTrigger.prev('.frm_trigger');
									}
									frmTrigger.click();
								}
							}

							if ( $fieldCont.is(':visible') ) {
								addFieldError( $fieldCont, key, response.errors );

								cont_submit = false;

								var $recaptcha = jQuery(object).find('#frm_field_'+key+'_container .frm-g-recaptcha, #frm_field_'+key+'_container .g-recaptcha');
								if ( $recaptcha.length ) {
									show_captcha = true;
									var recaptchaID = $recaptcha.data('rid');
									if ( jQuery().grecaptcha ) {
										if ( recaptchaID ) {
											grecaptcha.reset( recaptchaID );
										} else {
											grecaptcha.reset();
										}
									}
								}
							}
						}
					}

					jQuery(document).trigger( 'frmFormErrors', [ object, response ] );

					fieldset.removeClass('frm_doing_ajax');
					scrollToFirstField( object );

					if(show_captcha !== true){
						replaceCheckedRecaptcha( object, false );
					}

					if(cont_submit){
						object.submit();
					}else{
						jQuery(object).prepend(response.error_message);
					}
				} else {
					// there may have been a plugin conflict, or the form is not set to submit with ajax

					showFileLoading( object );
					replaceCheckedRecaptcha( object, true );

					object.submit();
				}
			},
			error:function(){
				jQuery(object).find('input[type="submit"], input[type="button"]').removeAttr('disabled');
				object.submit();
			}
		});
	}

	function afterFormSubmitted( object, response ) {
		var formCompleted = jQuery(response.content).find('.frm_message');
		if ( formCompleted.length ) {
			jQuery(document).trigger( 'frmFormComplete', [ object, response ] );
		} else {
			jQuery(document).trigger( 'frmPageChanged', [ object, response ] );
		}
	}

	function removeAddedScripts( formContainer, formID ) {
		var endReplace = jQuery( '.frm_end_ajax_' + formID );
		if ( endReplace.length ) {
			formContainer.nextUntil( '.frm_end_ajax_' + formID ).remove();
			endReplace.remove();
		}
	}

	function addUrlParam(response){
		if ( history.pushState && typeof response.page !== 'undefined' ) {
			var url = addQueryVar('frm_page', response.page);
			window.history.pushState({"html":response.html}, '', '?'+ url);
		}
	}

	function addQueryVar(key, value) {
		key = encodeURI(key);
		value = encodeURI(value);

		var kvp = document.location.search.substr(1).split('&');

		var i=kvp.length; var x; while(i--) {
			x = kvp[i].split('=');

			if (x[0]==key) {
				x[1] = value;
				kvp[i] = x.join('=');
				break;
			}
		}

		if (i<0) {
			kvp[kvp.length] = [key,value].join('=');
		}

		return kvp.join('&');
	}

	function addFieldError( $fieldCont, key, jsErrors ) {
		if ( $fieldCont.length && $fieldCont.is(':visible') ) {
			$fieldCont.addClass('frm_blank_field');
			if ( typeof frmThemeOverride_frmPlaceError === 'function' ) {
				frmThemeOverride_frmPlaceError( key, jsErrors );
			} else {
				$fieldCont.append( '<div class="frm_error">'+ jsErrors[key] +'</div>' );
			}
			jQuery(document).trigger('frmAddFieldError', [ $fieldCont, key, jsErrors ] );
		}
	}

	function removeFieldError( $fieldCont ) {
		$fieldCont.removeClass('frm_blank_field has-error');
		$fieldCont.find('.frm_error').remove();
	}

	function removeAllErrors() {
		jQuery('.form-field').removeClass('frm_blank_field has-error');
		jQuery('.form-field .frm_error').replaceWith('');
		jQuery('.frm_error_style').remove();
	}

	function scrollToFirstField( object ) {
		var field = jQuery(object).find('.frm_blank_field:first');
		if ( field.length ) {
			frmFrontForm.scrollMsg( field, object, true );
		}
	}

	function showSubmitLoading( $object ) {
		if ( !$object.hasClass('frm_loading_form') ) {
			$object.addClass('frm_loading_form');

			$object.trigger( 'frmStartFormLoading' );
		}

		disableSubmitButton( $object );
	}

	function removeSubmitLoading( $object, enable, processesRunning ) {
		if ( processesRunning > 0 ) {
			return;
		}

		$object.removeClass('frm_loading_form');

		$object.trigger( 'frmEndFormLoading' );

		if ( enable === 'enable' ) {
			enableSubmitButton( $object );
		}
	}

	function showFileLoading( object ) {
		var loading = document.getElementById('frm_loading');
		if ( loading !== null ) {
			var file_val = jQuery(object).find('input[type=file]').val();
			if ( typeof file_val !== 'undefined' && file_val !== '' ) {
				setTimeout(function(){
					jQuery(loading).fadeIn('slow');
				},2000);
			}
		}
	}

	function replaceCheckedRecaptcha( object, checkPage ) {
		var $recapField = jQuery(object).find('.frm-g-recaptcha, .g-recaptcha');
		if($recapField.length ){
			if ( checkPage ) {
				var morePages = jQuery(object).find('.frm_next_page').length < 1 || jQuery(object).find('.frm_next_page').val() < 1;
				if ( ! morePages ) {
					return;
				}
			}
			$recapField.closest('.frm_form_field').replaceWith('<input type="hidden" name="recaptcha_checked" value="'+ frm_js.nonce +'">');
		}
	}

	function clearDefault(){
		/*jshint validthis:true */
		toggleDefault(jQuery(this), 'clear');
	}

	function replaceDefault(){
		/*jshint validthis:true */
		toggleDefault(jQuery(this), 'replace');
	}
	
	function toggleDefault($thisField, e){
		// TODO: Fix this for a default value that is a number or array
		var v = $thisField.data('frmval').replace(/(\n|\r\n)/g, '\r');
		if ( v === '' || typeof v === 'undefined' ) {
			return false;
		}
		var thisVal = $thisField.val().replace(/(\n|\r\n)/g, '\r');
		
		if ( 'replace' == e ) {
			if ( thisVal === '' ) {
				$thisField.addClass('frm_default').val(v);
			}
		} else if ( thisVal == v ) {
			$thisField.removeClass('frm_default').val('');
		}
	}

	function resendEmail(){
		/*jshint validthis:true */
		var $link = jQuery(this);
		var entry_id = $link.data('eid');
		var form_id = $link.data('fid');
		$link.append('<span class="spinner" style="display:inline"></span>');
		jQuery.ajax({
			type:'POST',url:frm_js.ajax_url,
			data:{action:'frm_entries_send_email', entry_id:entry_id, form_id:form_id, nonce:frm_js.nonce},
			success:function(msg){
				$link.replaceWith(msg);
			}
		});
		return false;
	}

	/**********************************************
	 * General Helpers
	 *********************************************/

	function confirmClick() {
		/*jshint validthis:true */
		var message = jQuery(this).data('frmconfirm');
		return confirm(message);
	}

	function toggleDiv(){
		/*jshint validthis:true */
		var div = jQuery( this ).data( 'frmtoggle' );
		if ( jQuery( div ).is( ':visible' ) ) {
			jQuery( div ).slideUp( 'fast' );
		} else {
			jQuery( div ).slideDown( 'fast' );
		}
		return false;
	}

	/**********************************************
	 * Fallback functions
	 *********************************************/

	function addIndexOfFallbackForIE8() {
		if ( !Array.prototype.indexOf ) {
			Array.prototype.indexOf = function(elt /*, from*/) {
				var len = this.length >>> 0;

				var from = Number(arguments[1]) || 0;
				from = (from < 0) ? Math.ceil(from) : Math.floor(from);
				if (from < 0) {
					from += len;
				}

				for (; from < len; from++) {
					if ( from in this && this[from] === elt ) {
						return from;
					}
				}
				return -1;
			};
		}
	}

	function addTrimFallbackForIE8(){
		if ( typeof String.prototype.trim !== 'function' ) {
			String.prototype.trim = function() {
				return this.replace(/^\s+|\s+$/g, '');
			};
		}
	}

	function addFilterFallbackForIE8(){
		if ( !Array.prototype.filter ) {

			Array.prototype.filter = function(fun /*, thisp */) {

				if ( this === void 0 || this === null ) {
					throw new TypeError();
				}

				var t = Object( this );
				var len = t.length >>> 0;
				if ( typeof fun !== 'function' ) {
					throw new TypeError();
				}

				var res = [];
				var thisp = arguments[1];
				for (var i = 0; i < len; i++) {
					if ( i in t ) {
						var val = t[i]; // in case fun mutates this
						if (fun.call(thisp, val, i, t))
							res.push(val);
					}
				}

				return res;
			};
		}
	}

	function addKeysFallbackForIE8(){
		if ( !Object.keys ) {
		  Object.keys = function(obj) {
		    var keys = [];

		    for (var i in obj) {
		      if (obj.hasOwnProperty(i)) {
		        keys.push(i);
		      }
		    }

		    return keys;
		  };
		}
	}

	return{
		init: function(){
			jQuery(document).off('submit.formidable','.frm-show-form');
			jQuery(document).on('submit.formidable','.frm-show-form', frmFrontForm.submitForm);

			jQuery( '.frm-show-form input[onblur], .frm-show-form textarea[onblur]' ).each( function() {
				if ( jQuery( this ).val() === '' ) {
					jQuery( this ).blur();
				}
			} );
			
			jQuery(document).on('focus', '.frm_toggle_default', clearDefault);
			jQuery(document).on('blur', '.frm_toggle_default', replaceDefault);
			jQuery('.frm_toggle_default').blur();

			jQuery(document.getElementById('frm_resend_email')).click(resendEmail);

			jQuery(document).on('change', '.frm-show-form input[name^="item_meta"], .frm-show-form select[name^="item_meta"], .frm-show-form textarea[name^="item_meta"]', frmFrontForm.fieldValueChanged );
			jQuery(document).on('change keyup', '.frm-show-form .frm_inside_container input, .frm-show-form .frm_inside_container select, .frm-show-form .frm_inside_container textarea', maybeShowLabel);

			jQuery(document).on('click', 'a[data-frmconfirm]', confirmClick);
			jQuery('a[data-frmtoggle]').click(toggleDiv);

			// Add fallbacks for the beloved IE8
			addIndexOfFallbackForIE8();
			addTrimFallbackForIE8();
			addFilterFallbackForIE8();
			addKeysFallbackForIE8();
		},

		getFieldId: function( field, fullID ) {
			return getFieldId( field, fullID );
		},

		renderRecaptcha: function( captcha ) {
			var size = captcha.getAttribute('data-size');
			var params = {
				'sitekey': captcha.getAttribute('data-sitekey'),
				'size': size,
				'theme': captcha.getAttribute('data-theme')
			};
			if ( size === 'invisible' ) {
				var formID = jQuery(captcha).closest('form').find('input[name="form_id"]').val();
				params.callback = function(token) {
					frmFrontForm.afterRecaptcha(token, formID);
				};
			}

			var recaptchaID = grecaptcha.render( captcha.id, params );

			captcha.setAttribute('data-rid', recaptchaID);
		},

		afterSingleRecaptcha: function(token){
			var object = jQuery('.frm-show-form .g-recaptcha').closest('form')[0];
			frmFrontForm.submitFormNow( object );
		},

		afterRecaptcha: function(token, formID){
			var object = jQuery('#frm_form_'+ formID +'_container form')[0];
			frmFrontForm.submitFormNow( object );
		},

		submitForm: function(e){
			frmFrontForm.submitFormManual( e, this );
		},

		submitFormManual: function(e, object){
			var invisibleRecaptcha = hasInvisibleRecaptcha(object);

			var classList = object.className.trim().split(/\s+/gi);
			if ( classList && invisibleRecaptcha.length < 1 ) {
				var isPro = classList.indexOf('frm_pro_form') > -1;
				if ( ! isPro ) {
					return;
				}
			}

			if ( jQuery('body').hasClass('wp-admin') ) {
				return;
			}

			e.preventDefault();

			if ( typeof frmProForm !== 'undefined' && typeof frmProForm.submitAllowed === 'function' ) {
				if ( ! frmProForm.submitAllowed( object ) ) {
					return;
				}
			}

			if ( invisibleRecaptcha.length ) {
				showSubmitLoading( jQuery(object) );
				executeInvisibleRecaptcha( invisibleRecaptcha );
			} else {

				var errors = frmFrontForm.validateFormSubmit( object );

				if ( Object.keys(errors).length === 0 ) {
					showSubmitLoading( jQuery(object) );

					frmFrontForm.submitFormNow( object, classList );
				}
			}
		},

		submitFormNow: function(object) {
			var classList = object.className.trim().split(/\s+/gi);
			if ( classList.indexOf('frm_ajax_submit') > -1 ) {
				var hasFileFields = jQuery(object).find('input[type="file"]').filter(function () {
					return !!this.value;
				}).length;
				if ( hasFileFields < 1 ) {
					action = jQuery(object).find('input[name="frm_action"]').val();
					frmFrontForm.checkFormErrors( object, action );
				} else {
					object.submit();
				}
			} else {
				object.submit();
			}
		},

		validateFormSubmit: function( object ){
			if ( typeof tinyMCE !== 'undefined' && jQuery(object).find('.wp-editor-wrap').length ) {
				tinyMCE.triggerSave();
			}

			jsErrors = [];

			if ( shouldJSValidate( object ) ) {
				frmFrontForm.getAjaxFormErrors( object );

				if ( Object.keys(jsErrors).length ) {
					frmFrontForm.addAjaxFormErrors( object );
				}
			}

			return jsErrors;
		},

		getAjaxFormErrors: function( object ) {
			jsErrors = validateForm( object );
			if ( typeof frmThemeOverride_jsErrors === 'function' ) {
				action = jQuery(object).find('input[name="frm_action"]').val();
				var customErrors = frmThemeOverride_jsErrors( action, object );
				if ( Object.keys(customErrors).length  ) {
					for ( var key in customErrors ) {
						jsErrors[ key ] = customErrors[ key ];
					}
				}
			}

			return jsErrors;
		},

		addAjaxFormErrors: function( object ) {
			removeAllErrors();

			for ( var key in jsErrors ) {
				var $fieldCont = jQuery(object).find('#frm_field_'+key+'_container');

				if ( $fieldCont.length ) {
					addFieldError( $fieldCont, key, jsErrors );
				} else {
					// we are unable to show the error, so remove it
					delete jsErrors[ key ];
				}
			}

			scrollToFirstField( object );
		},

		checkFormErrors: function(object, action){
			getFormErrors( object, action );
		},

		checkRequiredField: function( field, errors ){
			return checkRequiredField( field, errors );
		},

		showSubmitLoading: function( $object ){
			showSubmitLoading( $object );
		},

		removeSubmitLoading: function( $object, enable, processesRunning ){
			removeSubmitLoading( $object, enable, processesRunning );
		},

        scrollToID: function(id){
            var object = jQuery(document.getElementById(id));
            frmFrontForm.scrollMsg( object, false );
        },

		scrollMsg: function( id, object, animate ) {
			var scrollObj = '';
			if(typeof(object) === 'undefined'){
				scrollObj = jQuery(document.getElementById('frm_form_'+id+'_container'));
				if(scrollObj.length < 1 ){
					return;
				}
			} else if ( typeof id === 'string' ) {
				scrollObj = jQuery(object).find('#frm_field_'+id+'_container');
			} else {
				scrollObj = id;
			}

			var newPos = scrollObj.offset().top;
			if ( !newPos ){
				return;
			}
			newPos = newPos-frm_js.offset;

			var m=jQuery('html').css('margin-top');
			var b=jQuery('body').css('margin-top');
			if(m || b){
				newPos = newPos - parseInt(m) - parseInt(b);
			}

			if ( newPos && window.innerHeight ) {
				var screenTop = document.documentElement.scrollTop || document.body.scrollTop;
				var screenBottom = screenTop + window.innerHeight;

				if( newPos > screenBottom || newPos < screenTop ) {
					// Not in view
					if ( typeof animate === 'undefined' ) {
						jQuery(window).scrollTop(newPos);
					}else{
						jQuery('html,body').animate({scrollTop: newPos}, 500);
					}
					return false;
				}
			}
		},

		fieldValueChanged: function(e){
			/*jshint validthis:true */

			var field_id = frmFrontForm.getFieldId( this, false );
			if ( ! field_id || typeof field_id === 'undefined' ) {
				return;
			}

			if ( e.frmTriggered && e.frmTriggered == field_id ) {
				return;
			}

			jQuery(document).trigger( 'frmFieldChanged', [ this, field_id, e ] );

			if ( e.selfTriggered !== true ) {
				maybeValidateChange( field_id, this );
			}
		},

		savingDraft: function(object){
			console.warn('DEPRECATED: function frmFrontForm.savingDraft in v3.0 use frmProForm.savingDraft');
			if ( typeof frmProForm !== 'undefined' ) {
				return frmProForm.savingDraft(object);
			}
		},

		goingToPreviousPage: function(object){
			console.warn('DEPRECATED: function frmFrontForm.goingToPreviousPage in v3.0 use frmProForm.goingToPreviousPage');
			if ( typeof frmProForm !== 'undefined' ) {
				return frmProForm.goingToPreviousPage(object);
			}
		},

		hideOrShowFields: function(ids, event ){
			console.warn('DEPRECATED: function frmFrontForm.hideOrShowFields in v3.0 use frmProForm.hideOrShowFields');
			if ( typeof frmProForm !== 'undefined' ) {
				frmProForm.hideOrShowFields();
			}
		},

		hidePreviouslyHiddenFields: function(){
			console.warn('DEPRECATED: function frmFrontForm.hidePreviouslyHiddenFields in v3.0 use frmProForm.hidePreviouslyHiddenFields');
			if ( typeof frmProForm !== 'undefined' ) {
				frmProForm.hidePreviouslyHiddenFields();
			}
		},

		checkDependentDynamicFields: function(ids){
			console.warn('DEPRECATED: function frmFrontForm.checkDependentDynamicFields in v3.0 use frmProForm.checkDependentDynamicFields');
			if ( typeof frmProForm !== 'undefined' ) {
				frmProForm.checkDependentDynamicFields(ids);
			}
		},

		checkDependentLookupFields: function(ids){
			console.warn('DEPRECATED: function frmFrontForm.checkDependentLookupFields in v3.0 use frmProForm.checkDependentLookupFields');
			if ( typeof frmProForm !== 'undefined' ) {
				frmProForm.checkDependentLookupFields(ids);
			}
		},

		loadGoogle: function(){
			console.warn('DEPRECATED: function frmFrontForm.loadGoogle in v3.0 use frmProForm.loadGoogle');
			frmProForm.loadGoogle();
		},

		removeUsedTimes: function( obj, timeField ) {
			console.warn('DEPRECATED: function frmFrontForm.removeUsedTimes in v3.0 use frmProForm.removeUsedTimes');
			if ( typeof frmProForm !== 'undefined' ) {
				frmProForm.removeUsedTimes();
			}
		},
		
		escapeHtml: function(text){
			return text
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#039;');
		},

		invisible: function(classes) {
			jQuery(classes).css('visibility', 'hidden');
		},
		
		visible: function(classes) {
			jQuery(classes).css('visibility', 'visible');
		}
	};
}
var frmFrontForm = frmFrontFormJS();

jQuery(document).ready(function($){
	frmFrontForm.init();
});

function frmRecaptcha() {
	var captchas = jQuery('.frm-g-recaptcha');
	for ( var c = 0, cl = captchas.length; c < cl; c++ ) {
		frmFrontForm.renderRecaptcha( captchas[c] );
	}
}

function frmAfterRecaptcha(token){
	frmFrontForm.afterSingleRecaptcha(token);
}

function frmUpdateField(entry_id,field_id,value,message,num){
	jQuery(document.getElementById('frm_update_field_'+entry_id+'_'+field_id+'_'+num)).html('<span class="frm-loading-img"></span>');
	jQuery.ajax({
		type:'POST',url:frm_js.ajax_url,
		data:{action:'frm_entries_update_field_ajax', entry_id:entry_id, field_id:field_id, value:value, nonce:frm_js.nonce},
		success:function(){
			if(message.replace(/^\s+|\s+$/g,'') === ''){
				jQuery(document.getElementById('frm_update_field_'+entry_id+'_'+field_id+'_'+num)).fadeOut('slow');
			}else{
				jQuery(document.getElementById('frm_update_field_'+entry_id+'_'+field_id+'_'+num)).replaceWith(message);
			}
		}
	});
}

function frmDeleteEntry(entry_id,prefix){
	console.warn('DEPRECATED: function frmDeleteEntry in v2.0.13 use frmFrontForm.deleteEntry');
	jQuery(document.getElementById('frm_delete_'+entry_id)).replaceWith('<span class="frm-loading-img" id="frm_delete_'+entry_id+'"></span>');
	jQuery.ajax({
		type:'POST',url:frm_js.ajax_url,
		data:{action:'frm_entries_destroy', entry:entry_id, nonce:frm_js.nonce},
		success:function(html){
			if(html.replace(/^\s+|\s+$/g,'') === 'success')
				jQuery(document.getElementById(prefix+entry_id)).fadeOut('slow');
			else
				jQuery(document.getElementById('frm_delete_'+entry_id)).replaceWith(html);
			
		}
	});
}

function frmOnSubmit(e){
	console.warn('DEPRECATED: function frmOnSubmit in v2.0 use frmFrontForm.submitForm'); 
	frmFrontForm.submitForm(e, this);
}

function frm_resend_email(entry_id,form_id){
	console.warn('DEPRECATED: function frm_resend_email in v2.0'); 
	var $link = jQuery(document.getElementById('frm_resend_email'));
	$link.append('<span class="spinner" style="display:inline"></span>');
	jQuery.ajax({
		type:'POST',url:frm_js.ajax_url,
		data:{action:'frm_entries_send_email', entry_id:entry_id, form_id:form_id, nonce:frm_js.nonce},
		success:function(msg){
			$link.replaceWith(msg);
		}
	});
}
