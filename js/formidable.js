function frmFrontFormJS(){
	'use strict';
	var show_fields = [];
	var hide_later = {};
	var globalHiddenFields = [];
    var frm_checked_dep = [];
	var addingRow = '';
	var currentlyAddingRow = false;
	var action = '';
	var jsErrors = [];

	function setNextPage(e){
		/*jshint validthis:true */
		var $thisObj = jQuery(this);
		var thisType = $thisObj.attr('type');
		if ( thisType !== 'submit' ) {
			e.preventDefault();
		}

		var f = $thisObj.parents('form:first');
		var v = '';
		var d = '';
		var thisName = this.name;
		if ( thisName === 'frm_prev_page' || this.className.indexOf('frm_prev_page') !== -1 ) {
			v = jQuery(f).find('.frm_next_page').attr('id').replace('frm_next_p_', '');
		} else if ( thisName === 'frm_save_draft' || this.className.indexOf('frm_save_draft') !== -1 ) {
			d = 1;
		}

		jQuery('.frm_next_page').val(v);
		jQuery('.frm_saving_draft').val(d);

		if ( thisType !== 'submit' ) {
			f.trigger('submit');
		}
	}
	
	function toggleSection(){
		/*jshint validthis:true */
		jQuery(this).parent().children('.frm_toggle_container').slideToggle('fast');
		jQuery(this).toggleClass('active').children('.ui-icon-triangle-1-e, .ui-icon-triangle-1-s')
			.toggleClass('ui-icon-triangle-1-s ui-icon-triangle-1-e');
	}

	// Remove the frm_transparent class from a single file upload field when it changes
	// Hide the old file when a new file is uploaded
	function showFileUploadText(){
		/*jshint validthis:true */
		this.className = this.className.replace( 'frm_transparent', '');
		var currentClass = this.parentNode.getElementsByTagName('a')[0].className;
		if ( currentClass.indexOf('frm_clear_file_link') == -1 ) {
			currentClass += ' frm_hidden';
		}
	}

	/**
	 * Show "Other" text box when Other item is checked/selected
	 * Hide and clear text box when item is unchecked/unselected
	 */
	function showOtherText(){
        /*jshint validthis:true */
        var type = this.type;
        var other = false;
        var select = false;

        // Dropdowns
        if ( type === 'select-one' ) {
            select = true;
            var curOpt = this.options[this.selectedIndex];
            if ( curOpt.className === 'frm_other_trigger' ) {
                other = true;
            }
        } else if ( type === 'select-multiple' ) {
            select = true;
            var allOpts = this.options;
            other = false;
            for ( var i = 0; i < allOpts.length; i++ ) {
                if ( allOpts[i].className === 'frm_other_trigger' ) {
                    if ( allOpts[i].selected ) {
                        other = true;
                        break;
                    }
                }
            }
        }
        if ( select ) {
			var otherField = jQuery(this).parent().children('.frm_other_input');

			if ( otherField.length ) {
				if ( other ) {
					// Remove frm_pos_none
					otherField[0].className = otherField[0].className.replace( 'frm_pos_none', '' );
				} else {
					// Add frm_pos_none
					if ( otherField[0].className.indexOf( 'frm_pos_none' ) < 1 ) {
						otherField[0].className = otherField[0].className + ' frm_pos_none';
					}
					otherField[0].value = '';
				}
			}

        // Radio
        } else if ( type === 'radio' ) {
			if ( jQuery(this).is(':checked' ) ) {
				jQuery(this).closest('.frm_radio').children('.frm_other_input').removeClass('frm_pos_none');
				jQuery(this).closest('.frm_radio').siblings().children('.frm_other_input').addClass('frm_pos_none').val('');
			}
        // Checkboxes
        } else if ( type === 'checkbox' ) {
            if ( this.checked ) {
                jQuery(this).closest('.frm_checkbox').children('.frm_other_input').removeClass('frm_pos_none'); 
            } else {
                jQuery(this).closest('.frm_checkbox').children('.frm_other_input').addClass('frm_pos_none').val('');
            }
        }
	}

	function maybeCheckDependent(e){
		/*jshint validthis:true */

		var field_id = getFieldId( this );
		if ( ! field_id || typeof field_id === 'undefined' ) {
			return;
		}

		var reset = 'reset';
		if ( e.frmTriggered ) {
			if ( e.frmTriggered == field_id ) {
				return;
			}
			reset = 'persist';
		}

		checkDependentField(field_id, null, jQuery(this), reset);
		doCalculation(field_id, jQuery(this));
		validateField( field_id, this );
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
			return 0;
		}

		var nameParts = fieldName.replace('item_meta[', '').replace('[]', '').split(']');
		if ( nameParts.length < 1 ) {
			return 0;
		}
		nameParts = nameParts.filter(function(n){ return n !== ''; });

		var field_id = nameParts[0];
		var isRepeating = false;

		if ( nameParts.length === 1 || nameParts[1] == '[form' || nameParts[1] == '[id' ) {
			return field_id;
		}

		// Check if 'this' is in a repeating section
		if ( jQuery('input[name="item_meta['+ field_id +'][form]"]').length ) {
			// this is a repeatable section with name: item_meta[370][0][414]
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
			field_id = field_id +'-'+ nameParts[0] +'-'+ nameParts[1].replace('[', '');
		}

		return field_id;
	}


	/* Conditional Logic Functions */

	/**
	*
	* Check all the logic on each field that has conditional logic dependent on a given field ID
	*
	* field_id = the field ID that is triggering the changes
	* rec = null or go (maybe remove this variable)
	* changedInput = the input that changed or null on initial page load
	* reset = reset or persist
	*/
	function checkDependentField(field_id, rec, changedInput, reset){
		var rules = getRulesForField( field_id );
		if ( typeof rules === 'undefined' ) {
			return;
		}

		if ( typeof(rec) === 'undefined' || rec === null ) {
			//stop recursion?
			rec = 'go';
		}

		if ( reset !== 'persist' ) {
            show_fields = []; // reset this variable after each click
        }

		var isRepeat = maybeSetRowId( changedInput );
		var currentRuleIndex = {};
		var hideField;

		for ( var i = 0, len = rules.length; i < len; i++ ) {
			hideField = rules[i].HideField;
			setCurrentRuleIndex( hideField, currentRuleIndex );

			if ( rules[i].FieldName === field_id ) {
				// Field in logic is the same field that triggered the change
				hideOrShowField( currentRuleIndex[ hideField ], rules[i], rec, changedInput);
			} else {
				// Field in logic is different from the field that triggered the change
				hideOrShowField( currentRuleIndex[ hideField ], rules[i], rec);
			}

			if ( i === ( len - 1 ) ) {
				hideFieldLater(rec);
				if ( isRepeat ) {
					addingRow = '';
				}
			}
		}
	}

	function setCurrentRuleIndex( hideField, currentRuleIndex ) {
		if ( ! ( hideField in currentRuleIndex ) ) {
			currentRuleIndex[ hideField ] = 0;
		} else {
			currentRuleIndex[ hideField ] += 1;
		}
	}

	/*
	* Check if changed field is a repeating field. If so, set addingRow to the HTML id of the repeating section div
	*/
	function maybeSetRowId( changedInput ) {
		var isRepeat = false;
		if ( addingRow === '' && typeof changedInput !== 'undefined' && changedInput !== null ) {
			changedInput = maybeGetFirstElement( changedInput );
			var repeatSecObj = changedInput.closest('.frm_repeat_sec, .frm_repeat_inline, .frm_repeat_grid');
			if ( typeof repeatSecObj !== 'undefined' && typeof repeatSecObj.attr('id') !== 'undefined' ) {
				addingRow = repeatSecObj.attr('id');
				isRepeat = true;
			}
		}
		return isRepeat;
	}

	function getRulesForField( field_id ) {
		if ( typeof __FRMRULES  === 'undefined' || typeof __FRMRULES[field_id] === 'undefined' ) {
			return;
		}

		var rules = compileRules( __FRMRULES[field_id] );
		return rules;
	}

	function compileRules( rules ) {
		var this_opts = [];
		for ( var i = 0, l = rules.length; i < l; i++ ) {
            var rule = rules[i];
            if ( typeof rule !== 'undefined' ) {
                for ( var j = 0, rcl = rule.Conditions.length; j < rcl; j++ ) {
					var c = rule.Conditions[j];
					c.HideField = rule.Setting.FieldName;
					c.MatchType = rule.MatchType;
					c.Show = rule.Show;
					c.FormId = rule.FormId;
                    this_opts.push(c);
                }
            }
        }
		return this_opts;
	}

	// Hide or show a field with conditional logic
	// Track whether fields should hide or show in show_fields variable
	function hideOrShowField(i, logicRules, rec, changedInput){
		// Instantiate variables
		logicRules.inputName = 'item_meta['+ logicRules.FieldName +']';
		logicRules.hiddenName = 'item_meta['+ logicRules.HideField +']';
		logicRules.containerID = 'frm_field_'+ logicRules.FieldName +'_container';
		logicRules.hideContainerID = 'frm_field_'+ logicRules.HideField +'_container';
		logicRules.Value = logicRules.Value.trim();

		// If the trigger field is a repeating field, only check single row of repeating section
		if ( addingRow !== '' ) {
			checkRepeatingFieldInSingleRow( i, logicRules, rec, addingRow );
			return;
		}

		// Conditional logic is being checked on initial page load or the logic field being checked isn't the field that changed
		if ( typeof changedInput === 'undefined' || changedInput === null ) {
			changedInput = jQuery('input[name^="'+ logicRules.inputName +'"], textarea[name^="'+ logicRules.inputName +'"], select[name^="'+ logicRules.inputName +'"]');

			// Current logic field is repeating (which means hide/show field is repeating as well)
			if ( changedInput.length < 1 ) {
				checkLogicForTwoRepeatingFields( i, logicRules, rec );
				return;
			}
		}

		// Get the value from the logic field (not repeating)
		var fieldValue = getBasicEnteredValue( logicRules );

		// If field to hide/show is a repeating field and the logic field is not repeating, loop through each one of the repeating fields with that field ID
		if ( isRepeatingFieldById( logicRules.HideField ) ) {
			checkLogicForRepeatingHideField( i, logicRules, fieldValue, rec );
			return;
		}

		// By this point, only non-repeating fields should be getting checked so proceed normally
		setEmptyKeyInShowFieldsArray( logicRules );
		updateShowFields( i, logicRules, fieldValue );
		hideFieldNow( i, logicRules, rec );
	}

	// Check conditional logic when the trigger field is repeating and the current logic field may or may not be repeating
	// Hide/show field is repeating
	function checkRepeatingFieldInSingleRow( i, logicRules, rec, repeatSecHtmlId ) {
		// If logic field is a repeating field, update inputName accordingly
		if ( isRepeatingFieldById( logicRules.FieldName ) ) {
			logicRules.inputName = getRepeatingFieldName( logicRules.FieldName, repeatSecHtmlId );
			logicRules.containerID = getRepeatingFieldHtmlId( logicRules.FieldName, repeatSecHtmlId );
		}
		// Updat hideContainerID for repeating fields
		logicRules.hideContainerID = getRepeatingFieldHtmlId( logicRules.HideField, repeatSecHtmlId );

		// Get the value in the logic field
		var fieldValue = getBasicEnteredValue( logicRules );

		setEmptyKeyInShowFieldsArray(logicRules);
		updateShowFields( i, logicRules, fieldValue );
		hideFieldNow(i, logicRules, rec);
	}

	// If the trigger field is NOT repeating, but the current logic field and the show/hide field are repeating
	function checkLogicForTwoRepeatingFields( i, logicRules, rec ) {
		var allRepeatFields = document.getElementsByClassName('frm_field_' + logicRules.FieldName + '_container');
		for ( var r = 0; r < allRepeatFields.length; r++ ) {
			logicRules.inputName = getRepeatingFieldName( logicRules.FieldName, allRepeatFields[r].id );
			logicRules.containerID = allRepeatFields[r].id;
			logicRules.hideContainerID = allRepeatFields[r].id.replace( logicRules.FieldName, logicRules.HideField );

			// Get the value in the logic field
			var fieldValue = getBasicEnteredValue( logicRules );

			setEmptyKeyInShowFieldsArray(logicRules);
			updateShowFields( i, logicRules, fieldValue );
			hideFieldNow(i, logicRules, rec);
		}
	 }

	// If the hide/show field is repeating, loop through each field in column to check it
	function checkLogicForRepeatingHideField( i, logicRules, fieldValue, rec ){
		var allRepeatFields = document.getElementsByClassName('frm_field_' + logicRules.HideField + '_container');
		for ( var r = 0; r < allRepeatFields.length; r++ ) {
		    logicRules.hideContainerID = allRepeatFields[r].id;

			setEmptyKeyInShowFieldsArray(logicRules);
			updateShowFields( i, logicRules, fieldValue );
			hideFieldNow(i, logicRules, rec);
		}
	}

	function getBasicEnteredValue( f ){
		var fieldValue = '';

		// If field is a checkbox field
		if ( f.Type === 'checkbox' || f.Type === 'data-checkbox' ) {
			var checkVals = getCheckedVal(f.containerID, f.inputName);

			if ( checkVals.length ) {
				fieldValue = checkVals;
			}else{
				fieldValue = '';
			}

			return fieldValue;
		}

		fieldValue = jQuery('input[name="'+ f.inputName +'"][type="hidden"]').val();

		if ( typeof fieldValue !== 'undefined' ) {
			// If field is on another page, read-only, or visibility setting is hiding it

		} else if ( f.Type == 'radio' || f.Type === 'data-radio' ) {
			// If radio field on the current page
			fieldValue = jQuery('input[name="'+ f.inputName +'"]:checked').val();

		} else if ( f.Type === 'select' || f.Type === 'data-select' ) {
			// If dropdown field on the current page
			fieldValue = jQuery('select[name^="'+ f.inputName +'"]').val();

		} else {
			// If text field on the current page
			fieldValue = jQuery('input[name="'+ f.inputName +'"]').val();
		}

		if ( typeof fieldValue === 'undefined' ) {
			fieldValue = '';
		}

		if ( typeof fieldValue === 'string' ) {
			fieldValue = fieldValue.trim();
		}

		return fieldValue;
	}

	function setEmptyKeyInShowFieldsArray(f) {
		if ( typeof show_fields[f.hideContainerID] === 'undefined' ) {
			show_fields[f.hideContainerID] = [];
		}
	}

	// Add values to the show_fields array
	function updateShowFields( i, logicRules, fieldValue ) {
		if ( fieldValue === null || fieldValue === '' || fieldValue.length < 1 ) {
			show_fields[logicRules.hideContainerID][i] = false;
		} else {
			show_fields[logicRules.hideContainerID][i] = {'funcName':'getDataOpts', 'f':logicRules, 'sel':fieldValue};
		}

		if ( logicRules.Type === 'checkbox' || (logicRules.Type === 'data-checkbox' && typeof logicRules.LinkedField === 'undefined') ) {

			updateShowFieldsForCheckbox( i, logicRules, fieldValue );

		} else if ( typeof logicRules.LinkedField !== 'undefined' && logicRules.Type.indexOf('data-') === 0 ) {

			updateShowFieldsForDynamicField( i, logicRules, fieldValue );

		}else if ( typeof logicRules.Value === 'undefined' && logicRules.Type.indexOf('data') === 0 ) {
			if ( fieldValue === '' ) {
				logicRules.Value = '1';
			} else {
				logicRules.Value = fieldValue;
			}
			show_fields[logicRules.hideContainerID][i] = operators(logicRules.Condition, logicRules.Value, fieldValue);
			logicRules.Value = undefined;
		} else {
			show_fields[logicRules.hideContainerID][i] = operators(logicRules.Condition, logicRules.Value, fieldValue);
		}
	}

	function updateShowFieldsForCheckbox( i, logicRules, fieldValue ) {
		show_fields[logicRules.hideContainerID][i] = false;

		var match = false;
		if ( fieldValue !== '') {
			if ( logicRules.Condition === '!=' ) {
				show_fields[logicRules.hideContainerID][i] = true;
			}
			for ( var b = 0; b<fieldValue.length; b++ ) {
				match = operators(logicRules.Condition, logicRules.Value, fieldValue[b]);
				if ( logicRules.Condition === '!=' ) {
					if ( show_fields[logicRules.hideContainerID][i] === true && match === false ) {
						show_fields[logicRules.hideContainerID][i] = false;
					}
				} else if(show_fields[logicRules.hideContainerID][i] === false && match){
					show_fields[logicRules.hideContainerID][i] = true;
				}
			}
		} else {
			match = operators(logicRules.Condition, logicRules.Value, '');
			if(show_fields[logicRules.hideContainerID][i] === false && match){
				show_fields[logicRules.hideContainerID][i] = true;
			}
		}
	}

	function updateShowFieldsForDynamicField( i, logicRules, fieldValue ) {
		if ( typeof logicRules.DataType === 'undefined' || logicRules.DataType === 'data' ) {
			if ( fieldValue === '' ) {
				hideAndClearDynamicField( logicRules, 'hide' );
			} else if ( logicRules.Type === 'data-radio' ) {
				if ( typeof logicRules.DataType === 'undefined' ) {
					show_fields[logicRules.hideContainerID][i] = operators(logicRules.Condition, logicRules.Value, fieldValue);
				} else {
					show_fields[logicRules.hideContainerID][i] = {'funcName':'getData','f':logicRules,'sel':fieldValue};
				}
			} else if ( logicRules.Type === 'data-checkbox' || ( logicRules.Type === 'data-select' && isNotEmptyArray( fieldValue ) ) ) {
				hideAndClearDynamicField( logicRules, 'show' );
				show_fields[logicRules.hideContainerID][i] = true;
				getData(logicRules, fieldValue, 0);
			} else if ( logicRules.Type === 'data-select' ) {
				show_fields[logicRules.hideContainerID][i] = {'funcName':'getData','f':logicRules,'sel':fieldValue};
			}
		}
	}

	function hideFieldNow(i, f, rec){
		if ( f.MatchType === 'all' || show_fields[f.hideContainerID][i] === false ) {

			if ( !( f.hideContainerID in hide_later ) ) {

				hide_later[ f.hideContainerID ] = {
					'show':f.Show,
					'match':f.MatchType,
					'FieldName':f.FieldName,
					'HideField':f.HideField,
					'hideContainerID':f.hideContainerID,
					'FormId':f.FormId,
					'DynamicInfoIndices':[]
				};
			}
			maybeAddDynamicInfoIndex( f.hideContainerID, i );

			return;
		}


		var hideFieldContainer = jQuery( document.getElementById(f.hideContainerID) );

		if ( f.Show === 'show' ) {
			if ( show_fields[f.hideContainerID][i] !== true ) {
				showField(show_fields[f.hideContainerID][i], f.FieldName, rec);
			} else {
				// Show the field
				routeToShowFieldAndSetVal( hideFieldContainer, f);
			}
		} else {
			// Hide the field
			routeToHideFieldAndClearVal( hideFieldContainer, f);
		}
	}

	function maybeAddDynamicInfoIndex( hideContainerID, i ) {
		var dynamicInfoIndex = false;

		if ( show_fields[ hideContainerID ][ i ] !== false && show_fields[ hideContainerID ][ i ] !== true ) {
			dynamicInfoIndex = i;
		}

		if ( dynamicInfoIndex !== false ) {
			hide_later[ hideContainerID ].DynamicInfoIndices.push( dynamicInfoIndex );
		}
	}

	function hideFieldLater(rec){
		var hvalue;
		for ( var key in hide_later) {
			hvalue = hide_later[key];
			delete hide_later[key];

			if ( typeof hvalue === 'undefined' ) {
				return;
			}

			var container = jQuery('#' + hvalue.hideContainerID);
			var hideField = hvalue.show;
			if ( ( hvalue.match === 'any' && (jQuery.inArray(true, show_fields[hvalue.hideContainerID]) === -1) ) ||
			( hvalue.match === 'all' && (jQuery.inArray(false, show_fields[hvalue.hideContainerID]) > -1) ) ) {
				if ( hvalue.show === 'show' ) {
					hideField = 'hide';
				} else {
					hideField = 'show';
				}
			}

			if ( hideField === 'show' ) {
				routeToShowFieldAndSetVal( container, hvalue );
				maybeGetDynamicFieldData( hvalue, rec );
			} else {
				routeToHideFieldAndClearVal( container, hvalue );
			}
		}
	}

	/* Hide Field Functions */
	function routeToHideFieldAndClearVal( hideFieldContainer, f ) {
		if ( hideFieldContainer.length ) {
			// Field is not type=hidden
			hideFieldAndClearValue( hideFieldContainer, f );
		} else {
			// Field is type=hidden
			var fieldName = getFieldName( f.HideField, f.hideContainerID );
			var inputs = jQuery( 'input[name^="' + fieldName + '"]' );
			clearValueForInputs( inputs );
		}
		addToHideFields( f.hideContainerID, f.FormId );
	}

	function hideFieldAndClearValue( container, f ) {
		container.hide();

		var inputs = getInputsInContainer( container );
		if ( inputs.length ){
			clearValueForInputs( inputs );
		}
	}

	function clearValueForInputs( inputs ) {
		inputs.prop('checked', false).prop('selectedIndex', 0);
		inputs.not(':checkbox, :radio, select').val('');
		var i = false;
		inputs.each(function(){
			if ( this.tagName == 'SELECT' ) {
				var autocomplete = document.getElementById( this.id + '_chosen' );
				if ( autocomplete !== null ) {
					jQuery(this).trigger('chosen:updated');
				}
			}

			if ( i === false || ["checkbox","radio"].indexOf( this.type ) < 0 ) {
				triggerChange( jQuery(this) );
			}
			i = true;
		});
	}

	function addToHideFields( htmlFieldId, formId ) {
		// Get all currently hidden fields
		var hiddenFields = getHiddenFields( formId );

		// If field id is already in the array, move on
		if ( hiddenFields.indexOf( htmlFieldId ) > -1 ) {
			return;
		} else {
			// Add new conditionally hidden field to array
			hiddenFields.push( htmlFieldId );

			// Copy hiddenFields to global variable
			globalHiddenFields[ 'form_' + formId ] = hiddenFields;

			// Set the hiddenFields value in the frm_hide_field_formID input
			hiddenFields = JSON.stringify( hiddenFields );
			var frmHideFieldsInput = document.getElementById('frm_hide_fields_' + formId);
			frmHideFieldsInput.value = hiddenFields;
		}
	}

	function getHiddenFields( formId ) {
		var hiddenFields = [];
		if ( typeof( globalHiddenFields[ 'form_' + formId ] ) !== 'undefined' ) {
			// If global value is already set, get it from there to save time
			hiddenFields = globalHiddenFields[ 'form_' + formId ];
		} else {
			// Fetch the hidden fields from the frm_hide_fields_formId input
			var frmHideFieldsInput = document.getElementById('frm_hide_fields_' + formId);
			hiddenFields = frmHideFieldsInput.value;
			if ( hiddenFields ) {
				hiddenFields = JSON.parse( hiddenFields );
			} else {
				hiddenFields = [];
			}
			// Set the global HiddenFields variable
			globalHiddenFields[ 'form_' + formId ] = hiddenFields;
		}
		return hiddenFields;
	}

	function hideAndClearDynamicField(logicRules, hideOrShow){
		var hiddenFields = getHiddenFields( logicRules.FormId );

		if ( hiddenFields.indexOf( logicRules.hideContainerID ) === -1 ) {
			var hideContainer = jQuery( document.getElementById( logicRules.hideContainerID ) );
			if ( hideOrShow === 'hide' ) {
				hideContainer.hide();
				addToHideFields( logicRules.hideContainerID, logicRules.FormId );
			}
			hideContainer.find('.frm_opt_container').empty();
		}
    }

	/* Show Field Functions */
	function routeToShowFieldAndSetVal( hideFieldContainer, f ) {
		var inSection = isAContainerField( hideFieldContainer );
		var inputAtts = {inSection:inSection, formId:f.FormId};

		removeFromHideFields( f.hideContainerID, f.FormId );
		if ( hideFieldContainer.length ) {
			// Field is not type=hidden
			showFieldAndSetValue( hideFieldContainer, inputAtts );
		} else {
			// Set field value (don't show it)
			var fieldName = getFieldName( f.HideField, f.hideContainerID );
			var inputs = jQuery( 'input[name^="' + fieldName + '"]' );
			setValForInputs( inputs, inputAtts );
		}
	}

	function showFieldAndSetValue( container, inputAtts ) {
		var inputs = getInputsInContainer( container );

		setValForInputs( inputs, inputAtts );

		container.show();
	}

	function setValForInputs( inputs, fieldAtts ){
		if ( inputs.length ) {
			fieldAtts.valSet = false;
			fieldAtts.isHidden = false;

			for ( var i = 0; i < inputs.length; i++ ) {

				if ( skipThisInput( inputs, i, fieldAtts ) === true ) {
					continue;
				}

				setDefaultValue( jQuery( inputs[i] ) );
				maybeDoCalcForSingleField( inputs[i] );
			}
		}
	}

	function skipThisInput( inputs, i, fieldAtts ) {
		var goToNextIteration = false;

		if ( i === 0 || inputs[i-1].name != inputs[i].name ) {
			// This field hasn't been checked yet

			if ( fieldAtts.inSection && isInputConditionallyHidden( inputs[i], fieldAtts ) ) {
				fieldAtts.isHidden = true;
				fieldAtts.valSet = false;
			} else {
				fieldAtts.isHidden = false;
				fieldAtts.valSet = isValueSet( inputs[i] );
			}
		}

		if ( fieldAtts.valSet || fieldAtts.isHidden ) {
			// If the value is already set or the field should remain hidden, move on
			goToNextIteration = true;
		}

		return goToNextIteration;
	}

	// Check if a field already has a value set
	// input is not a jQuery object
	function isValueSet( input ) {
		var valueSet = false;

		if ( input.type == 'checkbox' || input.type == 'radio' ) {

			var radioVals = document.getElementsByName( input.name );
			var l = radioVals.length;
			for ( var i=0; i<l; i++ ) {
				if ( radioVals[i].checked ) {
					valueSet = true;
					break;
				}
			}
		} else if ( input.value ) {
			valueSet = true;
		}

		return valueSet;
	}

	function setDefaultValue( input ) {
		var inputLength = input.length;

		if ( inputLength ) {
			for ( var i = 0, l = inputLength; i < l; i++ ) {
				var field = jQuery(input[i]);
				var defaultValue = field.data('frmval');
				if ( typeof defaultValue !== 'undefined' ) {
					if ( ! field.is(':checkbox, :radio') ) {
						field.val( defaultValue );
						triggerChange( field );
					} else if ( field.val() == defaultValue || ( jQuery.isArray(defaultValue) && jQuery.inArray(field.val(), defaultValue) !== -1 ) ) {
						field.prop('checked', true);
						triggerChange( field );
					}
				}
			}
		}
	}

	function removeFromHideFields( htmlFieldId, formId ) {
		// Get all currently hidden fields
		var hiddenFields = getHiddenFields( formId );

		// If field id is in the array, delete it
		var item_index = hiddenFields.indexOf( htmlFieldId );
		if ( item_index > -1 ) {
			// Remove field from the hiddenFields array
			hiddenFields.splice(item_index, 1);

			// Save the hiddenFields array as a global variable
			globalHiddenFields[ 'form_' + formId ] = hiddenFields;

			// Update frm_hide_fields_formId input
			hiddenFields = JSON.stringify( hiddenFields );
			var frmHideFieldsInput = document.getElementById('frm_hide_fields_' + formId);
			frmHideFieldsInput.value = hiddenFields;
		}
	}

	// Check if dynamic data needs to be retrieved
	function maybeGetDynamicFieldData( hvalue, rec ) {
		if ( hvalue.DynamicInfoIndices.length > 0 ) {
			var dynamicIndex;
			var parentField;
			for ( var t = 0; t < hvalue.DynamicInfoIndices.length; t++ ) {
				dynamicIndex = hvalue.DynamicInfoIndices[ t ];
				parentField = show_fields[ hvalue.hideContainerID ][ dynamicIndex ].f.FieldName;
				showField( show_fields[ hvalue.hideContainerID ][ dynamicIndex ], parentField, rec );
			}
		}
	}

	function triggerChange( input, fieldKey ) {
		if ( typeof fieldKey === 'undefined' ) {
			fieldKey = 'dependent';
		}

		if ( input.length > 1 ) {
			input = input.eq(0);
		}

		input.trigger({ type:'change', selfTriggered:true, frmTriggered:fieldKey });
	}

	function operators(op, a, b){
		if ( typeof b === 'undefined' ) {
			b = '';
		}
		if(jQuery.isArray(b) && jQuery.inArray(a,b) > -1){
			b = a;
		}
		if(String(a).search(/^\s*(\+|-)?((\d+(\.\d+)?)|(\.\d+))\s*$/) !== -1){
			a = parseFloat(a);
			b = parseFloat(b);
		}
		if ( String(a).indexOf('&quot;') != '-1' && operators(op, a.replace('&quot;', '"'), b) ) {
			return true;
		}

		var theOperators = {
			'==': function(c,d){ return c == d; },
			'!=': function(c,d){ return c != d; },
			'<': function(c,d){ return c > d; },
			'>': function(c,d){ return c < d; },
			'LIKE': function(c,d){
				if(!d){
					/* If no value, then assume no match */
					return false;
				}

				d = prepareEnteredValueForLikeComparison( d );
				c = prepareLogicValueForLikeComparison( c );

				return d.indexOf( c ) != -1;
			},
			'not LIKE': function(c,d){
				if(!d){
					/* If no value, then assume no match */
					return true;
				}

				d = prepareEnteredValueForLikeComparison( d );
				c = prepareLogicValueForLikeComparison( c );

				return d.indexOf( c ) == -1;
			}
		};
		return theOperators[op](a, b);
	}

	function prepareEnteredValueForLikeComparison( d ) {
		if ( typeof d === 'string' ) {
			d = d.toLowerCase();
		} else if ( typeof d === 'number' ) {
			d = d.toString();
		}
		return d;
	}

	function prepareLogicValueForLikeComparison( c ) {
		if ( typeof c === 'string' ) {
			c = c.toLowerCase();
		}
		return c;
	}

	function showField(funcInfo, field_id, rec){
		if ( funcInfo.funcName == 'getDataOpts' ) {
			getDataOpts(funcInfo.f, funcInfo.sel, field_id, rec);
		} else if ( funcInfo.funcName == 'getData' ) {
			getData(funcInfo.f, funcInfo.sel, 0);
		}
	}

	function getData(f,selected,append){
        var fcont = document.getElementById(f.hideContainerID);
		var cont = jQuery(fcont).find('.frm_opt_container');
		if ( cont.length === 0 ) {
			return true;
		}

		if ( !append ) {
			cont.html('<span class="frm-loading-img"></span>');
		}

		jQuery.ajax({
			type:'POST',url:frm_js.ajax_url,
			data:{
                action:'frm_fields_ajax_get_data', entry_id:selected,
                field_id:f.LinkedField, current_field:f.HideField,
                hide_id:f.hideContainerID, nonce:frm_js.nonce
            },
			success:function(html){
				if ( append ) {
					cont.append(html);
				} else {
					cont.html(html);
				}

				var parentField = cont.children('input');
				var val = parentField.val();
				if ( ( html === '' && ! append ) || val === '' ) {
					fcont.style.display = 'none';
				} else {
					fcont.style.display = '';
				}

				triggerChange( parentField );

				return true;
			}
		});
	}

	function getDataOpts(f,selected,field_id,rec) {
		//don't check the same field twice when more than a 2-level dependency, and parent is not on this page
		if(rec == 'stop' && (jQuery.inArray(f.HideField, frm_checked_dep) > -1) && f.parentField && f.parentField.attr('type') == 'hidden'){
			return;
		}

		var hiddenInput = jQuery( '#' + f.hideContainerID ).find('select[name^="item_meta"], textarea[name^="item_meta"], input[name^="item_meta"]');

		// Get the previously selected field value
		var prev_val = getPrevFieldValue( hiddenInput );

		// Get default value
		var defaultValue = hiddenInput.data('frmval');

        if(f.DataType == 'select'){
			if((rec == 'stop' || jQuery('#'+ f.hideContainerID +' .frm-loading-img').length) && (jQuery.inArray(f.HideField, frm_checked_dep) > -1)){
				return;
			}
		}

		frm_checked_dep.push(f.HideField);

        var fcont = document.getElementById(f.hideContainerID);

		// If field is on a different page or hidden with visibility option, don't retrieve new options
		if ( fcont === null ) {
			return;
		}

		var $dataField = jQuery(fcont).find('.frm_opt_container');
        if($dataField.length === 0 && hiddenInput.length ){
		    checkDependentField(f.HideField, 'stop', hiddenInput);
            return false;
		}

		if ( f.Value !== '' ) {
			var match = operators(f.Condition, f.Value, selected);
			if ( !match ) {
				fcont.style.display = 'none';
				$dataField.html('');
				checkDependentField(f.HideField, 'stop', hiddenInput);
				return false;
			}
		}

		$dataField.html('<span class="frm-loading-img" style="visibility:visible;display:inline;"></span>');

        // save the current f value for use after ajax
        var hiddenName = f.hiddenName;
        var dataType = f.DataType;

		jQuery.ajax({
			type:'POST',
            url:frm_js.ajax_url,
			data:{
				action:'frm_fields_ajax_data_options', trigger_field_id:field_id,
				entry_id:selected, linked_field_id:f.LinkedField, field_id:f.HideField,
				default_value:defaultValue, container_id:f.hideContainerID, prev_val:prev_val,
				nonce:frm_js.nonce
            },
			success:function(html){
				$dataField.html(html);
				var $dynamicFieldInputs = $dataField.find('select, input, textarea');

				if ( html === '' || ( $dynamicFieldInputs.length == 1 && $dynamicFieldInputs.attr('type') == 'hidden' ) ) {
					// Hide the Dynamic field
					fcont.style.display = 'none';
				} else if ( f.MatchType != 'all' ) {
					// Show the Dynamic field
					fcont.style.display = '';
				}

				if( $dynamicFieldInputs.hasClass('frm_chzn') && jQuery().chosen){
					jQuery('.frm_chzn').chosen({allow_single_deselect:true});
				}

				triggerChange( $dynamicFieldInputs );
			}
		});
	}

	function getPrevFieldValue( inputs ) {
		var prev = [];
		var thisVal = '';
		inputs.each(function(){
			thisVal = this.value;
			if ( this.type === 'radio' || this.type === 'checkbox' ) {
				if ( this.checked === true ) {
					prev.push( thisVal );
				}
			} else {
				if ( thisVal !== '' ) {
					prev.push( thisVal );
				}
			}
		});

		if ( prev.length === 0 ) {
			prev = '';
		}

		return prev;
	}

	function doCalculation(field_id, triggerField){
		if ( typeof __FRMCALC === 'undefined' ) {
			// there are no calculations on this page
			return;
		}

		var all_calcs = __FRMCALC;
		var calc = all_calcs.fields[field_id];
		if ( typeof calc === 'undefined' ) {
			// this field is not used in a calculation
			return;
		}

		var keys = calc.total;
		var len = keys.length;
		var vals = [];

		// loop through each calculation this field is used in
		for ( var i = 0, l = len; i < l; i++ ) {

			// Stop calculation if total field is conditionally hidden
			if ( fieldIsConditionallyHidden( all_calcs.calc[ keys[i] ], triggerField.attr('name') ) ) {
				continue;
			}

			doSingleCalculation( all_calcs, keys[i], vals, triggerField );
		}
	}

	/**
	* Check if field (or its HTML parent) is hidden with conditional logic
	*/
	function fieldIsConditionallyHidden( calcDetails, triggerFieldName ) {
		var field_id = calcDetails.field_id;
		var form_id = calcDetails.form_id;
		var hiddenFields = document.getElementById( 'frm_hide_fields_' + form_id).value;
		if ( hiddenFields ) {
			hiddenFields = JSON.parse( hiddenFields );
		} else {
			return false;
		}

		var checkFieldId = field_id;

		// If triggerField is repeating, assume total field is also repeating
		if ( isRepeatingFieldByName( triggerFieldName ) ) {
			var triggerFieldParts = triggerFieldName.replace('item_meta', '').replace( /\[/g, '').split( ']' );
			checkFieldId = field_id + '-' + triggerFieldParts[0] + '-' + triggerFieldParts[1];
		}

		// If total field is a conditionally hidden (could be repeating or non-repeating)
		if ( hiddenFields.indexOf( 'frm_field_' + checkFieldId + '_container' ) > -1 ) {
			return true;
		}

		// If field is inside of section/embedded form which is hidden with conditional logic
		var helpers = getHelpers( form_id );
		if ( helpers && helpers[ field_id ] !== null && hiddenFields.indexOf( 'frm_field_' + helpers[ field_id ] + '_container' ) > -1 ) {
			return true;
		}

		return false;
	}

	function isRepeatingFieldByName( fieldName ) {
		var isRepeating = false;
		var fieldNameParts = fieldName.split( '[' );
		if ( fieldNameParts.length >= 4 ) {
			isRepeating = true;
		}

		return isRepeating;
	}

	function doSingleCalculation( all_calcs, field_key, vals, triggerField ) {
		var thisCalc = all_calcs.calc[ field_key ];
		var thisFullCalc = thisCalc.calc;

		var totalField = jQuery( document.getElementById('field_'+ field_key) );
		var fieldInfo = { 'triggerField': triggerField, 'inSection': false, 'thisFieldCall': 'input[id^="field_'+ field_key+'-"]' };
		if ( totalField.length < 1 && typeof triggerField !== 'undefined' ) {
			// check if the total field is inside of a repeating/embedded form
			fieldInfo.inSection = true;
			fieldInfo.thisFieldId = objectSearch( all_calcs.fieldsWithCalc, field_key );
			totalField = getSiblingField( fieldInfo );
		}

		// loop through the fields in this calculation
		thisFullCalc = getValsForSingleCalc( thisCalc, thisFullCalc, all_calcs, vals, fieldInfo );

		// Set the number of decimal places
		var dec = thisCalc.calc_dec;

		// allow .toFixed for reverse compatability
		if ( thisFullCalc.indexOf(').toFixed(') ) {
		var calcParts = thisFullCalc.split(').toFixed(');
			if ( isNumeric(calcParts[1]) ) {
				dec = calcParts[1];
				thisFullCalc = thisFullCalc.replace(').toFixed(' + dec, '');
			}
		}

		var total = parseFloat(eval(thisFullCalc));

		if ( typeof total === 'undefined' ) {
			total = 0;
		}

		// Set decimal points
		if ( isNumeric( dec ) ) {
			total = total.toFixed(dec);
		}

		if ( totalField.val() != total ) {
			totalField.val(total);
			triggerChange( totalField, field_key );
		}
	}

	function getValsForSingleCalc( thisCalc, thisFullCalc, all_calcs, vals, fieldInfo ) {
		var fCount = thisCalc.fields.length;
		for ( var f = 0, c = fCount; f < c; f++ ) {
			var field = {
				'triggerField': fieldInfo.triggerField, 'thisFieldId': thisCalc.fields[f],
				'inSection': fieldInfo.inSection,
				'valKey': fieldInfo.inSection +''+ thisCalc.fields[f],
				'thisField': all_calcs.fields[ thisCalc.fields[f] ],
				'thisFieldCall': 'input'+ all_calcs.fieldKeys[ thisCalc.fields[f] ]
			};

			field = getCallForField( field, all_calcs );
			vals = getCalcFieldId(field, all_calcs, vals);

			if ( typeof vals[field.valKey] === 'undefined' || isNaN(vals[field.valKey]) ) {
				vals[field.valKey] = 0;
			}

			var findVar = '['+ field.thisFieldId +']';
			findVar = findVar.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
			thisFullCalc = thisFullCalc.replace(new RegExp(findVar, 'g'), vals[field.valKey]);
		}
		return thisFullCalc;
	}

	function getCallForField( field, all_calcs ) {
		if ( field.thisField.type == 'checkbox' || field.thisField.type == 'select' ) {
			field.thisFieldCall = field.thisFieldCall +':checked,select'+ all_calcs.fieldKeys[field.thisFieldId] +' option:selected,'+ field.thisFieldCall+'[type=hidden]';
		} else if ( field.thisField.type == 'radio' || field.thisField.type == 'scale' ) {
			field.thisFieldCall = field.thisFieldCall +':checked,'+ field.thisFieldCall +'[type=hidden]';
		} else if ( field.thisField.type == 'textarea' ) {
			field.thisFieldCall = field.thisFieldCall + ',textarea'+ all_calcs.fieldKeys[field.thisFieldId];
		}
		return field;
	}

	function maybeDoCalcForSingleField( field_input ) {
		if ( typeof __FRMCALC === 'undefined' ) {
			// there are no calculations on this page
			return;
		}

		// Exit now if field is a type that can't do calculations
		if ( ! fieldCanDoCalc( field_input.type ) ) {
			return;
		}

		var all_calcs = __FRMCALC;
		var field_key = getFieldKey( field_input.id, field_input.name );
		var triggerField = maybeGetTriggerField( field_input );

		if ( all_calcs.calc[ field_key ] === undefined ) {
			// This field doesn't have any calculations
			return;
		}

		var vals = [];
		doSingleCalculation( all_calcs, field_key, vals, triggerField );
	}

	function fieldCanDoCalc( fieldType ) {
		var canDoCalc = false;

		if ( fieldType == 'text' || fieldType == 'hidden' || fieldType == 'number' ) {
			canDoCalc = true;
		}

		return canDoCalc;
	}

	function getFieldKey( fieldHtmlId, fieldName ) {
		var field_key = fieldHtmlId.replace( 'field_', '' );

		if ( isRepeatingFieldByName( fieldName ) ) {
			var fieldKeyParts = field_key.split('-');
			var newFieldKey = '';
			for ( var i=0; i<fieldKeyParts.length-1; i++ ){
				if ( newFieldKey === '' ) {
					newFieldKey = fieldKeyParts[i];
				} else {
					newFieldKey = newFieldKey + '-' + fieldKeyParts[i];
				}
			}
			field_key = newFieldKey;
		}

		return field_key;
	}

	function maybeGetTriggerField( fieldInput ) {
		var triggerField = null;
		if ( isRepeatingFieldByName( fieldInput.name ) ) {
			if ( fieldInput.type != 'hidden' ) {
				triggerField = jQuery( fieldInput ).closest('.frm_form_field');
			} else {
				triggerField = jQuery( fieldInput );
			}
		}

		return triggerField;
	}

	function getCalcFieldId( field, all_calcs, vals ) {
		if ( typeof vals[field.valKey] !== 'undefined' && vals[field.valKey] !== 0 ) {
			return vals;
		}

		vals[field.valKey] = 0;

		var calcField;
		if ( field.inSection === false ) {
			calcField = jQuery(field.thisFieldCall);
		} else {
			calcField = getSiblingField( field );
			if ( calcField === null || typeof calcField === 'undefined' ) {
				calcField = jQuery(field.thisFieldCall);
			}
		}

		if ( calcField === null || typeof calcField === 'undefined' || calcField.length < 1 ) {
			return vals;
		}

		calcField.each(function(){
			var thisVal = getOptionValue( field.thisField, this );

			if ( field.thisField.type == 'date' ) {
				var d = getDateFieldValue( all_calcs.date, thisVal );
                if ( d !== null ) {
					vals[field.valKey] = Math.ceil(d/(1000*60*60*24));
                }
			} else {
				var n = thisVal;

				if ( n !== '' && n !== 0 ) {
					n = n.trim();
					n = parseFloat(n.replace(/,/g,'').match(/-?[\d\.]+$/));
				}

				if ( typeof n === 'undefined' || isNaN(n) || n === '' ) {
					n = 0;
				}
				vals[field.valKey] += n;
			}

		});

		return vals;
    }

	/**
	* Get the value from a date field regardless of whether datepicker is defined for it
	* Limitations: If using a format with a 2-digit date, '20' will be added to the front if the year is prior to 70
	*/
	function getDateFieldValue( dateFormat, thisVal ) {
		var d = 0;

		if ( ! thisVal ) {
			// If no value was selected in date field, use 0
		} else if ( typeof jQuery.datepicker === 'undefined' ) {
			// If date field is not on the current page

			var splitAt = '-';
			if ( dateFormat.indexOf( '/' ) > -1 ) {
				splitAt = '/';
			}

			var formatPieces = dateFormat.split( splitAt );
			var datePieces = thisVal.split( splitAt );

			var year, month, day;
			year = month = day = '';

			for ( var i = 0; i < formatPieces.length; i++ ) {
				if ( formatPieces[ i ] == 'y' ) {
					var currentYear = new Date().getFullYear() + 15;
					var currentYearPlusFifteen = currentYear.toString().substr(2,2);

					if ( datePieces[ i ] > currentYearPlusFifteen ) {
						year = '19' + datePieces[ i ];
					} else {
						year = '20' + datePieces[ i ];
					}
				} else if ( formatPieces[ i ] == 'yy' ) {
					year = datePieces[ i ];
				} else if ( formatPieces[ i ] == 'm' || formatPieces[ i ] == 'mm' ) {
					month = datePieces[ i ];
					if ( month.length < 2 ) {
						month = '0' + month;
					}
				} else if ( formatPieces[ i ] == 'd' || formatPieces[ i ] == 'dd' ) {
					day = datePieces[ i ];
					if ( day.length < 2 ) {
						day = '0' + day;
					}
				}
			}

			d = Date.parse( year + '-' + month + '-' + day );

		} else {
		    d = jQuery.datepicker.parseDate(dateFormat, thisVal);
		}
		return d;
	}

	function getSiblingField( field ) {
		if ( typeof field.triggerField === 'undefined' ) {
			return null;
		}

		var container = field.triggerField.closest('.frm_repeat_sec, .frm_repeat_inline, .frm_repeat_grid');
		if ( container.length ) {
			var siblingFieldCall = field.thisFieldCall.replace('[id=', '[id^=');

			return container.find(siblingFieldCall);
		}
		return null;
	}

	function getOptionValue( thisField, currentOpt ) {
		var thisVal;

		// If current option is an other option, get other value
		if ( isOtherOption( thisField, currentOpt ) ) {
			thisVal = getOtherValueAnyField( thisField, currentOpt );
		} else if ( (currentOpt.type === 'checkbox' || currentOpt.type === 'radio') && currentOpt.checked ) {
			thisVal = currentOpt.value;
		} else {
			thisVal = jQuery(currentOpt).val();
		}

		if ( typeof thisVal === 'undefined' ) {
			thisVal = '';
		}
		return thisVal;
	}

	/* Check if current option is an "Other" option (not an Other text field) */
	function isOtherOption( thisField, currentOpt ) {
		var isOtherOpt = false;

		// If hidden, check for a value
		if ( currentOpt.type == 'hidden' ) {
			if ( getOtherValueLimited( currentOpt ) !== '' ) {
				isOtherOpt = true;
			}
		} else if ( thisField.type == 'select' ) {
			// If a visible dropdown field
			var optClass = currentOpt.className;
			if ( optClass && optClass.indexOf( 'frm_other_trigger' ) > -1 ) {
				isOtherOpt = true;
			}
		} else if ( thisField.type == 'checkbox' || thisField.type == 'radio' ) {
			// If visible checkbox/radio field
			if ( currentOpt.id.indexOf( '-other_' ) > -1 && currentOpt.id.indexOf( '-otext' ) < 0 ) {
				isOtherOpt = true;
			}
		}

		return isOtherOpt;
	}

	/* Get the value from an "Other" text field */
	/* Does NOT work for visible select fields */
	function getOtherValueLimited( currentOpt ){
		var otherVal = '';
		var otherText = document.getElementById( currentOpt.id + '-otext' );
		if ( otherText !== null && otherText.value !== '' ) {
			otherVal = otherText.value;
		}
		return otherVal;
	}

	/* Get value from Other text field */
	function getOtherValueAnyField( thisField, currentOpt ) {
		var otherVal = 0;

		if ( thisField.type == 'select' ) {
			if ( currentOpt.type == 'hidden' ) {
				if ( isCurrentOptRepeating( currentOpt ) ) {
					// Do nothing because regular doCalculation code takes care of it
				} else {
					otherVal = getOtherValueLimited( currentOpt );
				}
			} else {
				otherVal = getOtherSelectValue( currentOpt );
			}
		} else if ( thisField.type == 'checkbox' || thisField.type == 'radio' ) {
			if ( currentOpt.type == 'hidden' ) {
				// Do nothing because regular doCalculation code takes care of it
			} else {
				otherVal = getOtherValueLimited( currentOpt );
			}
		}

		return otherVal;
	}

	/* Check if current option is in a repeating section */
	function isCurrentOptRepeating( currentOpt ) {
		var isRepeating = false;
		var parts = currentOpt.name.split( '[' );
		if ( parts.length > 2 ) {
			isRepeating = true;
		}
		return isRepeating;
	}

	/* Get value from Other text field in a visible dropdown field */
	function getOtherSelectValue( currentOpt ) {
		return jQuery(currentOpt).closest('.frm_other_container').find('.frm_other_input').val();
	}

	function validateForm( object ) {
		var errors = [];
		return errors; // TODO: remove this line when ready to release

		// Make sure required text field is filled in
		var requiredFields = jQuery(object).find('.frm_required_field input, .frm_required_field select, .frm_required_field textarea');
		if ( requiredFields.length ) {
			for ( var r = 0, rl = requiredFields.length; r < rl; r++ ) {
				// this won't work with radio/checkbox
				errors = checkRequiredField( requiredFields[r], errors );
			}
		}

		var emailFields = jQuery(object).find('input[type=email]');
		var fields = jQuery(object).find('input,select,textarea');
		if ( fields.length ) {
			for ( var n = 0, nl = fields.length; n < nl; n++ ) {
				var field = fields[n];
				var value = field.value;
				if ( value !== '' ) {
					if ( field.type == 'hidden' ) {
						// don't vaidate
					} else if ( field.type == 'number' ) {
						errors = checkNumberField( field, errors );
					} else if ( field.type == 'email' ) {
						errors = checkEmailField( field, errors, emailFields );
					} else if ( field.pattern !== null ) {
						errors = checkPatternField( field, errors );
					}
				}
			}
		}

		return errors;
	}

	function validateField( fieldId, field ) {
		var errors = [];
		return errors; // TODO: remove this line when ready to release

		var $fieldCont = jQuery(field).closest('.frm_form_field');
		if ( $fieldCont.hasClass('.frm_required_field') ) {
			errors = checkRequiredField( field, errors );
		}

		if ( errors.length < 1 ) {
			if ( field.type == 'email' ) {
				var emailFields = jQuery(field).closest('form').find('input[type=email]');
				errors = checkEmailField( field, errors, emailFields );
			} else if ( field.type == 'number' ) {
				errors = checkNumberField( field, errors );
			} else if ( field.pattern !== null ) {
				errors = checkPatternField( field, errors );
			}
		}

		if (  Object.keys(errors).length > 0 ) {
			for ( var key in errors ) {
				removeFieldError( $fieldCont );
				addFieldError( $fieldCont, key, errors );
			}
		} else {
			removeFieldError( $fieldCont );
		}
	}

	function checkRequiredField( field, errors ) {
		var val = '';
		if ( field.type == 'checkbox' || field.type == 'radio' ) {
			var checked = document.querySelector('input[name="'+field.name+'"]:checked');
			if ( checked !== null ) {
				val = checked.value;
			}
		} else {
			val = jQuery(field).val();
			if ( typeof val !== 'string' ) {
				var tempVal = val;
				val = '';
				for ( var i = 0; i < tempVal.length; i++ ) {
					if ( tempVal[i] !== '' ) {
						val = tempVal[i];
					}
				}
			}
		}

		if ( val === '' ) {
			var fieldID = getFieldId( field, true );
			if ( !(fieldID in errors) ) {
				errors[ fieldID ] = getFieldValidationMessage( field, 'data-reqmsg' );
			}
		}
		return errors;
	}

	function checkEmailField( field, errors, emailFields ) {
		var emailAddress = field.value;
		var fieldID = getFieldId( field, true );
		if ( fieldID in errors ) {
			return errors;
		}

		var isConf = (fieldID.indexOf('conf_') === 0);
		if ( emailAddress !== '' || isConf ) {
			var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
			if ( emailAddress !== '' && re.test( emailAddress ) === false ) {
				errors[ fieldID ] = '';
				if ( isConf ) {
					errors[ fieldID.replace('conf_', '') ] = '';
				}
			} else if ( isConf ) {
				var confName = field.name.replace('conf_', '');
				var match = emailFields.filter('[name="'+ confName +'"]').val();
				if ( match !== emailAddress ) {
					errors[ fieldID ] = getFieldValidationMessage( field, 'data-invmsg' );
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

	function getFieldValidationMessage( field, messageType ) {
		var msg = field.getAttribute(messageType);
		if ( msg === null ) {
			msg = '';
		}
		return msg;
	}

	function getFormErrors(object, action){
		jQuery(object).find('input[type="submit"], input[type="button"]').attr('disabled','disabled');
		jQuery(object).find('.frm_ajax_loading').addClass('frm_loading_now');

		if(typeof action == 'undefined'){
			jQuery(object).find('input[name="frm_action"]').val();
		}

		jQuery.ajax({
			type:'POST',url:frm_js.ajax_url,
			data:jQuery(object).serialize() +'&action=frm_entries_'+ action +'&nonce='+frm_js.nonce,
			success:function(errObj){
				errObj = errObj.replace(/^\s+|\s+$/g,'');
				if(errObj.indexOf('{') === 0){
					errObj = jQuery.parseJSON(errObj);
				}
				if(errObj === '' || !errObj || errObj === '0' || (typeof(errObj) != 'object' && errObj.indexOf('<!DOCTYPE') === 0)){
					var $loading = document.getElementById('frm_loading');
					if($loading !== null){
						var file_val=jQuery(object).find('input[type=file]').val();
						if(typeof(file_val) != 'undefined' && file_val !== ''){
							setTimeout(function(){
								jQuery($loading).fadeIn('slow');
							},2000);
						}
					}
					var $recapField = jQuery(object).find('.g-recaptcha');
					if($recapField.length && (jQuery(object).find('.frm_next_page').length < 1 || jQuery(object).find('.frm_next_page').val() < 1)){
                        $recapField.closest('.frm_form_field').replaceWith('<input type="hidden" name="recaptcha_checked" value="'+ frm_js.nonce +'">');
					}

					object.submit();
				}else if(typeof errObj != 'object'){
					jQuery(object).find('.frm_ajax_loading').removeClass('frm_loading_now');
					var formID = jQuery(object).find('input[name="form_id"]').val();
					jQuery(object).closest( '#frm_form_'+ formID +'_container' ).replaceWith(errObj);
					frmFrontForm.scrollMsg( formID );

					if(typeof(frmThemeOverride_frmAfterSubmit) == 'function'){
						var pageOrder = jQuery('input[name="frm_page_order_'+ formID +'"]').val();
						var formReturned = jQuery(errObj).find('input[name="form_id"]').val();
						frmThemeOverride_frmAfterSubmit(formReturned, pageOrder, errObj, object);
					}

					var entryIdField = jQuery(object).find('input[name="id"]');
					if(entryIdField.length){
						jQuery(document.getElementById('frm_edit_'+ entryIdField.val())).find('a').addClass('frm_ajax_edited').click();
					}
				}else{
					jQuery(object).find('input[type="submit"], input[type="button"]').removeAttr('disabled');
					jQuery(object).find('.frm_ajax_loading').removeClass('frm_loading_now');

					//show errors
					var cont_submit=true;
					jQuery('.form-field').removeClass('frm_blank_field');
					jQuery('.form-field .frm_error').replaceWith('');
					var jump = '';
					var show_captcha = false;
                    var $fieldCont = null;
					for (var key in errObj){
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
								cont_submit = false;
								if ( jump === '' ) {
									frmFrontForm.scrollMsg( key, object, true );
									jump = '#frm_field_'+key+'_container';
								}
								var $recapcha = jQuery(object).find('#frm_field_'+key+'_container .g-recaptcha');
								if ( $recapcha.length ) {
									show_captcha = true;
									grecaptcha.reset();
								}

								addFieldError( $fieldCont, key, errObj );
							}
						}else if(key == 'redirect'){
							window.location = errObj[key];
							return;
						}
					}
					if(show_captcha !== true){
						jQuery(object).find('.g-recaptcha').closest('.frm_form_field').replaceWith('<input type="hidden" name="recaptcha_checked" value="'+ frm_js.nonce +'">');
					}
					if(cont_submit){
						object.submit();
					}
				}
			},
			error:function(){
				jQuery(object).find('input[type="submit"], input[type="button"]').removeAttr('disabled');object.submit();
			}
		});
	}

	function addFieldError( $fieldCont, key, jsErrors ) {
		if ( $fieldCont.length && $fieldCont.is(':visible') ) {
			$fieldCont.addClass('frm_blank_field');
			if ( typeof frmThemeOverride_frmPlaceError == 'function' ) {
				frmThemeOverride_frmPlaceError( key, jsErrors );
			} else {
				$fieldCont.append( '<div class="frm_error">'+ jsErrors[key] +'</div>' );
			}
		}
	}

	function removeFieldError( $fieldCont ) {
		$fieldCont.removeClass('frm_blank_field');
		$fieldCont.find('.frm_error').remove();
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
		var v = $thisField.data('frmval').replace(/(\n|\r\n)/g, '\r');
		if ( v === '' || typeof v == 'undefined' ) {
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

    /* Google Tables */

	function prepareGraphTypes( graphs, graphType ) {
		for ( var num = 0; num < graphs.length; num++ ) {
			prepareGraphs( graphs[num], graphType );
		}
	}

	function prepareGraphs( opts, type ) {
		google.load('visualization', '1.0', {'packages':[type], 'callback': function(){
			if ( type == 'table' ) {
				compileGoogleTable( opts );
			} else {
				compileGraph( opts );
			}
		}});
	}

    function compileGoogleTable(opts){
        var data = new google.visualization.DataTable();

        var showID = false;
        if ( jQuery.inArray('id', opts.options.fields) !== -1 ) {
            showID = true;
            data.addColumn('number',frm_js.id);
        }

        var colCount = opts.fields.length;
        var type = 'string';
        for ( var i = 0, l = colCount; i < l; i++ ) {
            var thisCol = opts.fields[i];
            type = getGraphType(thisCol);

            data.addColumn(type, thisCol.name);
        }

        var showEdit = false;
        if ( opts.options.edit_link ) {
            showEdit = true;
            data.addColumn('string', opts.options.edit_link);
        }

        var showDelete = false;
        if ( opts.options.delete_link ) {
            showDelete = true;
            data.addColumn('string', opts.options.delete_link);
        }

        var col = 0;
        if ( opts.entries !== null ) {
            var entryCount = opts.entries.length;
            data.addRows(entryCount);

            var row = 0;

            for ( var e = 0, len = entryCount; e < len; e++ ) {
                col = 0;
                var entry = opts.entries[e];
                if ( showID ) {
                    data.setCell(row, col, entry.id);
                    col++;
                }

                for ( var field = 0, fieldCount = colCount; field < fieldCount; field++ ) {
                    var thisEntryCol = opts.fields[field];
                    type = getGraphType(thisEntryCol);

                    var fieldVal = entry.metas[thisEntryCol.id];
                    if ( type == 'number' && ( fieldVal === null || fieldVal === '' ) ) {
                        fieldVal = 0;
                    } else if ( type == 'boolean' ) {
                        if ( fieldVal === null || fieldVal == 'false' || fieldVal === false ) {
                            fieldVal = false;
                        } else {
                            fieldVal = true;
                        }
                    }

                    data.setCell(row, col, fieldVal);

                    col++;
                }

                if ( showEdit ) {
					if ( typeof entry.editLink !== 'undefined' ) {
                    	data.setCell(row, col, '<a href="'+ entry.editLink +'">'+ opts.options.edit_link +'</a>');
					} else {
						data.setCell(row, col, '');
					}
         		    col++;
        	    }

                if ( showDelete ) {
					if ( typeof entry.deleteLink !== 'undefined' ) {
                    	data.setCell(row, col,'<a href="'+ entry.deleteLink +'" class="frm_delete_link" data-frmconfirm="'+ opts.options.confirm +'">'+ opts.options.delete_link +'</a>');
					} else {
						data.setCell(row, col, '');
					}
                }

                row++;
            }
        } else {
            data.addRows(1);
            col = 0;

            for ( i = 0, l = colCount; i < l; i++ ) {
                if ( col > 0 ) {
                    data.setCell(0, col, '');
                } else {
                    data.setCell(0, col, opts.options.no_entries);
                }
                col++;
            }
        }

        var chart = new google.visualization.Table(document.getElementById('frm_google_table_'+ opts.options.form_id));
        chart.draw( data, opts.graphOpts );
    }

    function getGraphType(field){
        var type = 'string';
        if ( field.type == 'number' ){
            type = 'number';
        } else if ( field.type == 'checkbox' || field.type == 'select' ) {
            var optCount = field.options.length;
            if ( field.type == 'select' && field.options[0] === '' ) {
                if ( field.field_options.post_field == 'post_status' ) {
                    optCount = 3;
                } else {
                    optCount = optCount - 1;
                }
            }
            if ( optCount == 1 ) {
                type = 'boolean';
            }
        }
        return type;
    }

    function compileGraph(opts){
        var data = new google.visualization.DataTable();
        var useSepCol = false;
		var useTooltip = false;

        // add the rows
        var rowCount = opts.rows.length;
        if ( rowCount > 0 ) {
            if ( opts.type == 'table' ) {
                useSepCol = true;
                var lastRow = opts.rows[rowCount - 1];
                var count = lastRow[0] + 1;
                data.addRows(count);

                for ( var r = 0, len = rowCount; r < len; r++ ) {
                    data.setCell( opts.rows[r] ); //data.setCell(0, 0, 'Mike');
                }
            }else{
                var firstRow = opts.rows[0];
                if ( typeof firstRow.tooltip != 'undefined' ) {
                    useSepCol = true;
					useTooltip = true;

					// reset the tooltip key to numeric
					for ( var row = 0, rc = rowCount; row < rc; row++ ) {
						var tooltip = opts.rows[row].tooltip;
						delete opts.rows[row].tooltip;

						var rowArray = Object.keys(opts.rows[row]).map( function(k){
							return opts.rows[row][k];
						} );

						opts.rows[row] = rowArray;
						opts.rows[row].push(tooltip);
					}
                }
            }
        }

        // add the columns
        var colCount = opts.cols.length;
        if ( useSepCol ) {
            if ( colCount > 0 ) {
                for ( var i = 0, l = colCount; i < l; i++ ) {
                    var col = opts.cols[i];
                    data.addColumn(col.type, col.name);
                }
            }
			if ( useTooltip ) {
				data.addColumn({type:'string',role:'tooltip'});
				data.addRows(opts.rows);
			}
        } else {
            var graphData = [];
            graphData[0] = [];
            for ( var c = 0, cur = colCount; c < cur; c++ ) {
                graphData[0].push(opts.cols[c].name);
            }
            graphData = graphData.concat(opts.rows);
            data = google.visualization.arrayToDataTable(graphData);
        }

        var type = (opts.type.charAt(0).toUpperCase() + opts.type.slice(1)) + 'Chart';
        var chart = new google.visualization[type](document.getElementById('chart_'+ opts.graph_id));

        chart.draw(data, opts.options);
    }

	/* File Fields */
	function nextUpload(){
		/*jshint validthis:true */
		var obj = jQuery(this);
		var id = obj.data('fid');
		obj.wrap('<div class="frm_file_names frm_uploaded_files">');
		var files = obj.get(0).files;
		for ( var i = 0; i < files.length; i++ ) {
			if ( 0 === i ) {
				obj.after(files[i].name+' <a href="#" class="frm_clear_file_link">'+frm_js.remove+'</a>');
			} else {
				obj.after(files[i].name +'<br/>');
			}
		}

        obj.hide();

        var fileName = 'file'+ id;
        var fname = obj.attr('name');
        if ( fname != 'item_meta['+ id +'][]' ) {
            // this is a repeatable field
            var nameParts = fname.replace('item_meta[', '').replace('[]', '').split('][');
            if ( nameParts.length == 3 ) {
                fileName = fileName +'-'+ nameParts[1];
            }
        }

        obj.closest('.frm_form_field').find('.frm_uploaded_files:last').after('<input name="'+ fname +'" data-fid="'+ id +'"class="frm_transparent frm_multiple_file" multiple="multiple" type="file" />');
	}

	function removeDiv(){
		/*jshint validthis:true */
		fadeOut(jQuery(this).parent('.frm_uploaded_files'));
	}
	
	function clearFile(){
		/*jshint validthis:true */
		jQuery(this).parent('.frm_file_names').replaceWith('');
		return false;
	}
	
	/* Repeating Fields */
	function removeRow(){
		/*jshint validthis:true */
		var id = 'frm_section_'+ jQuery(this).data('parent') +'-'+ jQuery(this).data('key');
		var thisRow = jQuery(document.getElementById(id));
		var fields = thisRow.find('input, select, textarea');

		thisRow.fadeOut('slow', function(){
			thisRow.remove();

			fields.each(function(){
				/* update calculations when a row is removed */
				if ( this.type != 'file' ) {
					var fieldID = getFieldId( this, false );
					doCalculation(fieldID, jQuery(this));
				}
			});

			if(typeof(frmThemeOverride_frmRemoveRow) == 'function'){
				frmThemeOverride_frmRemoveRow(id, thisRow);
			}
		});

		return false;
	}

	function addRow(){
		/*jshint validthis:true */

		// If row is currently being added, leave now
		if ( currentlyAddingRow === true ) {
			return false;
		}

		// Indicate that a row is being added (so double clicking Add button doesn't cause problems)
		currentlyAddingRow = true;

		var id = jQuery(this).data('parent');
		var i = 0;
		if ( jQuery('.frm_repeat_'+id).length > 0 ) {
			i = 1 + parseInt(jQuery('.frm_repeat_'+ id +':last').attr('id').replace('frm_section_'+ id +'-', ''));
			if ( typeof i == 'undefined' ) {
				i = 1;
			}
		}

		jQuery.ajax({
			type:'POST',url:frm_js.ajax_url,
			dataType: 'json',
			data:{action:'frm_add_form_row', field_id:id, i:i, nonce:frm_js.nonce},
			success:function(r){
				var html = r.html;
				var item = jQuery(html).hide().fadeIn('slow');
				jQuery('.frm_repeat_'+ id +':last').after(item);

                var checked = ['other'];
                var fieldID, fieldObject;
                var reset = 'reset';
				addingRow = item.attr('id');

                // hide fields with conditional logic
                jQuery(html).find('input, select, textarea').each(function(){
					if ( this.type != 'file' ) {

						// Readonly dropdown fields won't have a name attribute
						if ( this.name === '' ) {
							return true;
						}
						fieldID = this.name.replace('item_meta[', '').split(']')[2].replace('[', '');
						if ( jQuery.inArray(fieldID, checked ) == -1 ) {
							if ( this.id == false ) {
								return;
							}
							fieldObject = jQuery( '#' + this.id );
							checked.push(fieldID);
							checkDependentField(fieldID, null, fieldObject, reset);
							doCalculation(fieldID, fieldObject);
							reset = 'persist';
						}
					}
                });
				addingRow = '';

				// check logic on fields outside of this section
				var checkLen = r.logic.check.length;
				for ( var f = 0, l = checkLen; f < l; f++ ) {
					if ( jQuery.inArray(r.logic.check[f], checked ) == -1 ) {
						if(jQuery(html).find('.frm_field_'+r.logic.check[f]+'_container').length < 1){
							checkDependentField(r.logic.check[f], null, null, reset);
	                		reset = 'persist';
						}
					}
				}

                var star = jQuery(html).find('.star');
                if ( star.length > 0 ) {
                    // trigger star fields
                    jQuery('.star').rating();
                }

                var autocomplete = jQuery(html).find('.frm_chzn');
				if ( autocomplete.length > 0 && jQuery().chosen ) {
                    // trigger autocomplete
					jQuery('.frm_chzn').chosen({allow_single_deselect:true});
				}

				if(typeof(frmThemeOverride_frmAddRow) == 'function'){
					frmThemeOverride_frmAddRow(id, r);
				}

				currentlyAddingRow = false;
			},
			error: function() {
				currentlyAddingRow = false;
			}
		});

		return false;
	}

	/* In-place edit */
	function editEntry(){
		/*jshint validthis:true */
		var $edit = jQuery(this);
		var entry_id = $edit.data('entryid');
		var prefix = $edit.data('prefix');
		var post_id = $edit.data('pageid');
		var form_id = $edit.data('formid');
		var cancel = $edit.data('cancel');
		var fields = $edit.data('fields');
		var exclude_fields = $edit.data('excludefields');

		var $cont = jQuery(document.getElementById(prefix+entry_id));
		var orig = $cont.html();
		$cont.html('<span class="frm-loading-img" id="'+prefix+entry_id+'"></span><div class="frm_orig_content" style="display:none">'+orig+'</div>');
		jQuery.ajax({
			type:'POST',url:frm_js.ajax_url,dataType:'html',
			data:{
				action:'frm_entries_edit_entry_ajax', post_id:post_id,
				entry_id:entry_id, id:form_id, nonce:frm_js.nonce,
				fields:fields, exclude_fields:exclude_fields
			},
			success:function(html){
				$cont.children('.frm-loading-img').replaceWith(html);
				$edit.removeClass('frm_inplace_edit').addClass('frm_cancel_edit');
				$edit.html(cancel);
			}
		});
		return false;
	}

	function cancelEdit(){
		/*jshint validthis:true */
		var $edit = jQuery(this);
		var entry_id = $edit.data('entryid');
		var prefix = $edit.data('prefix');
		var label = $edit.data('edit');

		if(!$edit.hasClass('frm_ajax_edited')){
			var $cont = jQuery(document.getElementById(prefix+entry_id));
			$cont.children('.frm_forms').replaceWith('');
			$cont.children('.frm_orig_content').fadeIn('slow').removeClass('frm_orig_content');
		}
		$edit.removeClass('frm_cancel_edit').addClass('frm_inplace_edit');
		$edit.html(label);
		return false;
	}

	function deleteEntry(){
		/*jshint validthis:true */
		var $link = jQuery(this);
		var confirmText = $link.data('deleteconfirm');
		if ( confirm( confirmText ) ) {
			var entry_id = $link.data('entryid');
			var prefix = $link.data('prefix');

			$link.replaceWith('<span class="frm-loading-img" id="frm_delete_'+entry_id+'"></span>');
			jQuery.ajax({
				type:'POST',url:frm_js.ajax_url,
				data:{action:'frm_entries_destroy', entry:entry_id, nonce:frm_js.nonce},
				success:function(html){
					if(html.replace(/^\s+|\s+$/g,'') == 'success'){
						jQuery(document.getElementById(prefix+entry_id)).fadeOut('slow');
						jQuery(document.getElementById('frm_delete_'+entry_id)).fadeOut('slow');
					}else{
						jQuery(document.getElementById('frm_delete_'+entry_id)).replaceWith(html);
					}
				}
			});
		}
		return false;
	}

	/* General Helpers */
	function fadeOut($remove){
		$remove.fadeOut('slow', function(){
			$remove.remove();
		});
	}

	function confirmClick() {
		/*jshint validthis:true */
		var message = jQuery(this).data('frmconfirm');
		return confirm(message);
	}

	function toggleDiv(){
		/*jshint validthis:true */
		var div=jQuery(this).data('frmtoggle');
		if(jQuery(div).is(':visible')){
			jQuery(div).slideUp('fast');
		}else{
			jQuery(div).slideDown('fast');
		}
		return false;
	}

	function empty($obj) {
		if ( $obj !== null ) {
			while ( $obj.firstChild ) {
				$obj.removeChild($obj.firstChild);
			}
		}
	}

	function objectSearch( array, value ) {
		for( var prop in array ) {
			if( array.hasOwnProperty( prop ) ) {
				if( array[ prop ] === value ) {
					return prop;
				}
			}
		}
		return null;
	}

	function isNotEmptyArray( value ) {
		return jQuery.isArray( value ) && ( value.length > 1 || value[0] !== '' );
	}

	function isNumeric( obj ) {
		return !jQuery.isArray( obj ) && (obj - parseFloat( obj ) + 1) >= 0;
	}

	function getInputsInContainer( container ) {
		return container.find('select[name^="item_meta"], textarea[name^="item_meta"], input[name^="item_meta"]');
	}

	// Get the beginning part of the name for a given field
	function getFieldName( fieldId, fieldDivId ){
		var fieldName = 'item_meta[' + fieldId + ']';

		// If field is repeating
		if ( isRepeatingFieldById( fieldId ) ) {
			fieldName = getRepeatingFieldName( fieldId, fieldDivId );
		}

		return fieldName;
	}

	function getHelpers( form_id ) {
		var helpers = document.getElementById( 'frm_helpers_' + form_id ).value;
		if ( helpers ) {
			helpers = JSON.parse( helpers );
		} else {
			helpers = [];
		}

		return helpers;
	}

	// Get the input name of a specific field in a given row of a repeating section
	function getRepeatingFieldName( fieldId, parentHtmlId ) {
		var repeatFieldName = '';
		if ( parentHtmlId.indexOf( 'frm_section' ) > -1 ) {
			// The HTML id provided is the frm_section HTML id
			var repeatSecParts = parentHtmlId.replace( 'frm_section_', '' ).split( '-' );
			repeatFieldName = 'item_meta[' + repeatSecParts[0] + '][' + repeatSecParts[1] + '][' + fieldId +']';
		} else {
			// The HTML id provided is the field div HTML id
			var fieldDivParts = parentHtmlId.replace( 'frm_field_', '').replace( '_container', '').split('-');
			repeatFieldName = 'item_meta[' + fieldDivParts[1] + '][' + fieldDivParts[2] + '][' + fieldId +']';
		}

		return repeatFieldName;
	}

	// Get the HTML id for a given repeating field
	function getRepeatingFieldHtmlId( fieldId, repeatSecHtmlId ){
		var repeatSecParts = repeatSecHtmlId.replace( 'frm_section_', '' ).split( '-' );
		var repeatFieldHtmlId = 'frm_field_' + fieldId + '-' + repeatSecParts[0] + '-' + repeatSecParts[1] + '_container';
		return repeatFieldHtmlId;
	}

	function maybeGetFirstElement( jQueryObj ) {
        if ( jQueryObj.length > 1 ) {
            jQueryObj = jQueryObj.eq(0);
        }
		return jQueryObj;
	}

	// Check if a given field is repeating
	function isRepeatingFieldById( fieldId ){
		// Check field div first
		var fieldDiv = document.getElementById( 'frm_field_' + fieldId + '_container' );
		if ( typeof fieldDiv !== 'undefined' && fieldDiv !== null ) {
			return false;
		}

		// Check input next so type=hidden fields don't get marked as repeating
		var fieldInput = jQuery( 'input[name^="item_meta[' + fieldId + ']"],select[name^="item_meta[' + fieldId + ']"], textarea[name^="item_meta[' + fieldId + ']"]' );
		if ( fieldInput.length < 1 ) {
			// TODO: Change this so Section (on diff page), HTML (on diff page), reCaptcha (on diff page), and page break fields don't return true
			return true;
		} else {
			return false;
		}
	}

	// Check if field is a section or embedded form
	function isAContainerField( hideFieldContainer ) {
		var inSection = false;
		if ( hideFieldContainer.hasClass( 'frm_section_heading' ) || hideFieldContainer.hasClass( 'frm_embed_form_container' ) ) {
			inSection = true;
		}

		return inSection;
	}

	function isInputConditionallyHidden( input, fieldAtts ) {
		var isHidden = false;
		if ( typeof input.name !== 'undefined' ) {
			var containerHtmlId;
			var nameParts = input.name.replace( /\]/g, '' ).split( '[' );
			if ( nameParts.length < 4 ) {
				if ( nameParts.length == 3 && nameParts[2] == 'form' ) {
					return true;
				}

				// Non-repeating input
				containerHtmlId = 'frm_field_' + nameParts[1] + '_container';

			} else {
				if ( nameParts[3] == 0 ) {
					return true;
				}

				// Repeating or embedded form inputs
				containerHtmlId = 'frm_field_' + nameParts[3] + '-' + nameParts[1] + '-' + nameParts[2] + '_container';
			}

			isHidden = isContainerConditionallyHidden( containerHtmlId, fieldAtts );

		} else {
			isHidden = true;
		}

		return isHidden;
	}

	function isContainerConditionallyHidden( containerHtmlId, fieldAtts ) {
		var isHidden = false;
		var hiddenFields;

		if ( typeof fieldAtts.hiddenFields !== 'undefined' ) {
			hiddenFields = fieldAtts.hiddenFields;
		} else {
			var frmHideFieldsInput = document.getElementById('frm_hide_fields_' + fieldAtts.formId);
			hiddenFields = frmHideFieldsInput.value;
			fieldAtts.hiddenFields = hiddenFields;
		}

		if ( hiddenFields ) {
			hiddenFields = JSON.parse( hiddenFields );
			if ( hiddenFields.indexOf( containerHtmlId ) > -1 ) {
				isHidden = true;
			}
		}

		return isHidden;
	}

	/* Fallback functions */
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

    /* Get checked values with IE8 fallback */
    function getCheckedVal(containerID, inputName) {
        var checkVals = [];
        if ( typeof document.querySelector == 'undefined') {
            var ieVals = jQuery('#'+ containerID +' input[type=checkbox]:checked, input[type=hidden][name^="'+ inputName +'"]');
            ieVals.each(function(){
                checkVals.push( this.value );
            });
        } else {
            var checkboxes = document.querySelectorAll('#'+ containerID +' input[type=checkbox], input[type=hidden][name^="'+ inputName +'"]');
            for ( var b = 0; b < checkboxes.length; b++ ) {
                if (( checkboxes[b].type == 'checkbox' && checkboxes[b].checked ) || checkboxes[b].type == 'hidden' ){
                    checkVals.push( checkboxes[b].value );
                }
            }
        }

        return checkVals;
    }

	return{
		init: function(){
			jQuery(document).on('click', '.frm_trigger', toggleSection);
			var $blankField = jQuery('.frm_blank_field');
			if ( $blankField.length ) {
				$blankField.closest('.frm_toggle_container').prev('.frm_trigger').click();
			}

			if ( jQuery.isFunction(jQuery.fn.placeholder) ) {
				jQuery('.frm-show-form input, .frm-show-form textarea').placeholder();
			} else {
				jQuery('.frm-show-form input[onblur], .frm-show-form textarea[onblur]').each(function(){
					if(jQuery(this).val() === '' ){
						jQuery(this).blur();
					}
				});
			}
			
			jQuery(document).on('focus', '.frm_toggle_default', clearDefault);
			jQuery(document).on('blur', '.frm_toggle_default', replaceDefault);
			jQuery('.frm_toggle_default').blur();

			jQuery(document.getElementById('frm_resend_email')).click(resendEmail);

			jQuery(document).on('change', '.frm_multiple_file', nextUpload);
			jQuery(document).on('click', '.frm_clear_file_link', clearFile);
			jQuery(document).on('click', '.frm_remove_link', removeDiv);

			jQuery(document).on('focusin', 'input[data-frmmask]', function(){
				jQuery(this).mask( jQuery(this).data('frmmask').toString() );
			});

			jQuery(document).on('change', '.frm-show-form input[name^="item_meta"], .frm-show-form select[name^="item_meta"], .frm-show-form textarea[name^="item_meta"]', maybeCheckDependent);
			
			jQuery(document).on('click', '.frm-show-form input[type="submit"], .frm-show-form input[name="frm_prev_page"], .frm-show-form .frm_save_draft', setNextPage);
            
            jQuery(document).on('change', '.frm_other_container input[type="checkbox"], .frm_other_container input[type="radio"], .frm_other_container select', showOtherText);
			
			jQuery(document).on('change', 'input[type=file].frm_transparent', showFileUploadText);

			jQuery(document).on('click', '.frm_remove_form_row', removeRow);
			jQuery(document).on('click', '.frm_add_form_row', addRow);

			jQuery(document).on('click', 'a[data-frmconfirm]', confirmClick);
			jQuery('a[data-frmtoggle]').click(toggleDiv);

			// In place edit
			jQuery('.frm_edit_link_container').on('click', 'a.frm_inplace_edit', editEntry);
			jQuery('.frm_edit_link_container').on('click', 'a.frm_cancel_edit', cancelEdit);
			jQuery(document).on('click', '.frm_ajax_delete', deleteEntry);

			// toggle collapsible entries shortcode
			jQuery('.frm_month_heading, .frm_year_heading').click( function(){
				var content = jQuery(this).children('.ui-icon-triangle-1-e, .ui-icon-triangle-1-s');
				if ( content.hasClass('ui-icon-triangle-1-e') ) {
					content.addClass('ui-icon-triangle-1-s').removeClass('ui-icon-triangle-1-e');
					jQuery(this).next('.frm_toggle_container').fadeIn('slow');
				} else {
					content.addClass('ui-icon-triangle-1-e').removeClass('ui-icon-triangle-1-s');
					jQuery(this).next('.frm_toggle_container').hide();
				}
			});

			// Add fallbacks for the beloved IE8
			addIndexOfFallbackForIE8();
			addTrimFallbackForIE8();
			addFilterFallbackForIE8();
			addKeysFallbackForIE8();
		},

		submitForm: function(e){
			e.preventDefault();
			var object = this;
			var errors = frmFrontForm.validateFormSubmit( object );

			if ( Object.keys(errors).length === 0 ) {
				frmFrontForm.checkFormErrors( object, action );
			}
		},

		validateFormSubmit: function( object ){
			if(jQuery(this).find('.wp-editor-wrap').length && typeof(tinyMCE) != 'undefined'){
				tinyMCE.triggerSave();
			}

			action = jQuery(object).find('input[name="frm_action"]').val();
			jsErrors = [];
			frmFrontForm.getAjaxFormErrors( object );

			if ( Object.keys(jsErrors).length ) {
				frmFrontForm.addAjaxFormErrors( object );
			}

			return jsErrors;
		},

		getAjaxFormErrors: function( object ) {
			jsErrors = validateForm( object );
			if ( typeof frmThemeOverride_jsErrors == 'function' ) {
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
			// Remove all previous errors
			jQuery('.form-field').removeClass('frm_blank_field');
			jQuery('.form-field .frm_error').replaceWith('');

			for ( var key in jsErrors ) {
				var $fieldCont = jQuery(object).find(jQuery('#frm_field_'+key+'_container'));
				addFieldError( $fieldCont, key, jsErrors );
			}
		},

		checkFormErrors: function(object, action){
			getFormErrors( object, action );
		},

        scrollToID: function(id){
            var frm_pos = jQuery(document.getElementById(id).offset());
            window.scrollTo(frm_pos.left, frm_pos.top);
        },

		scrollMsg: function( id, object, animate ) {
			var newPos = '';
			if(typeof(object) == 'undefined'){
				newPos = jQuery(document.getElementById('frm_form_'+id+'_container')).offset().top;
			}else{
				newPos = jQuery(object).find('#frm_field_'+id+'_container').offset().top;
			}

			if(!newPos){
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

		hideCondFields: function(ids){
			ids = JSON.parse(ids);
			var len = ids.length;
			for ( var i = 0, l = len; i < l; i++ ) {
                var container = document.getElementById('frm_field_'+ ids[i] +'_container');
                if ( container !== null ) {
                    container.style.display = 'none';
                } else {
                    // repeating or embedded fields
                    jQuery('.frm_field_'+ ids[i] +'_container').hide();
                }
			}
		},

		checkDependent: function(ids){
			ids = JSON.parse(ids);
			var len = ids.length;
            var reset = 'reset';
			for ( var i = 0, l = len; i < l; i++ ) {
				checkDependentField(ids[i], null, null, reset);
                reset = 'persist';
			}
		},

		loadGoogle: function(){
			if ( typeof google !== 'undefined' && google && google.load ) {
				var graphs = __FRMTABLES;
				var packages = Object.keys( graphs );
				//google.load('visualization', '1.0', {'packages':packages});
				for ( var i = 0; i < packages.length; i++ ) {
					prepareGraphTypes( graphs[ packages[i] ], packages[i] );
				}
			} else {
				setTimeout( frmFrontForm.loadGoogle, 30 );
			}
		},
		
		/* Time fields */
		removeUsedTimes: function(obj, timeField){
			var e = jQuery(obj).parents('form:first').find('input[name="id"]');
			jQuery.ajax({
				type:'POST',
				url:frm_js.ajax_url,
				dataType:'json',
				data:{
					action:'frm_fields_ajax_time_options',
					time_field:timeField, date_field:obj.id,
					entry_id: (e ? e.val() : ''), date: jQuery(obj).val(),
					nonce:frm_js.nonce
				},
				success:function(opts){
					var $timeField = jQuery(document.getElementById(timeField));
					$timeField.find('option').removeAttr('disabled');
					if(opts && opts !== ''){
						for(var opt in opts){
							$timeField.find('option[value="'+opt+'"]').attr('disabled', 'disabled');
						}
					}
				}
			});
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

function frmUpdateField(entry_id,field_id,value,message,num){
	jQuery(document.getElementById('frm_update_field_'+entry_id+'_'+field_id)).html('<span class="frm-loading-img"></span>');
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

function frmEditEntry(entry_id,prefix,post_id,form_id,cancel,hclass){
	console.warn('DEPRECATED: function frmEditEntry in v2.0.13 use frmFrontForm.editEntry');
	var $edit = jQuery(document.getElementById('frm_edit_'+entry_id));
	var label = $edit.html();
	var $cont = jQuery(document.getElementById(prefix+entry_id));
	var orig = $cont.html();
	$cont.html('<span class="frm-loading-img" id="'+prefix+entry_id+'"></span><div class="frm_orig_content" style="display:none">'+orig+'</div>');
	jQuery.ajax({
		type:'POST',url:frm_js.ajax_url,dataType:'html',
		data:{action:'frm_entries_edit_entry_ajax', post_id:post_id, entry_id:entry_id, id:form_id, nonce:frm_js.nonce},
		success:function(html){
			$cont.children('.frm-loading-img').replaceWith(html);
			$edit.replaceWith('<span id="frm_edit_'+entry_id+'"><a onclick="frmCancelEdit('+entry_id+',\''+prefix+'\',\''+ frmFrontForm.escapeHtml(label) +'\','+post_id+','+form_id+',\''+hclass+'\')" class="'+hclass+'">'+cancel+'</a></span>');
		}
	});
}

function frmCancelEdit(entry_id,prefix,label,post_id,form_id,hclass){
	console.warn('DEPRECATED: function frmCancelEdit in v2.0.13 use frmFrontForm.cancelEdit');
	var $edit = jQuery(document.getElementById('frm_edit_'+entry_id));
	var $link = $edit.find('a');
	var cancel = $link.html();
	
	if(!$link.hasClass('frm_ajax_edited')){
		var $cont = jQuery(document.getElementById(prefix+entry_id));
		$cont.children('.frm_forms').replaceWith('');
		$cont.children('.frm_orig_content').fadeIn('slow').removeClass('frm_orig_content');
	}
	$edit.replaceWith('<a id="frm_edit_'+entry_id+'" class="frm_edit_link '+hclass+'" href="javascript:frmEditEntry('+entry_id+',\''+prefix+'\','+post_id+','+form_id+',\''+ frmFrontForm.escapeHtml(cancel) +'\',\''+hclass+'\')">'+label+'</a>');
}

function frmDeleteEntry(entry_id,prefix){
	console.warn('DEPRECATED: function frmDeleteEntry in v2.0.13 use frmFrontForm.deleteEntry');
	jQuery(document.getElementById('frm_delete_'+entry_id)).replaceWith('<span class="frm-loading-img" id="frm_delete_'+entry_id+'"></span>');
	jQuery.ajax({
		type:'POST',url:frm_js.ajax_url,
		data:{action:'frm_entries_destroy', entry:entry_id, nonce:frm_js.nonce},
		success:function(html){
			if(html.replace(/^\s+|\s+$/g,'') == 'success')
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
	$link = jQuery(document.getElementById('frm_resend_email'));
	$link.append('<span class="spinner" style="display:inline"></span>');
	jQuery.ajax({
		type:'POST',url:frm_js.ajax_url,
		data:{action:'frm_entries_send_email', entry_id:entry_id, form_id:form_id, nonce:frm_js.nonce},
		success:function(msg){
			$link.replaceWith(msg);
		}
	});
}
