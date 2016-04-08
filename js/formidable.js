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

	function loadUniqueTimeFields() {
		if ( typeof __frmUniqueTimes === 'undefined' ) {
			return;
		}

		var timeFields = __frmUniqueTimes;
		for ( var i = 0; i < timeFields.length; i++ ) {
			jQuery( document.getElementById( timeFields[i].dateID ) ).change( maybeTriggerUniqueTime );
		}
	}

	function maybeTriggerUniqueTime() {
		/*jshint validthis:true */
		var timeFields = __frmUniqueTimes;
		for ( var i = 0; i < timeFields.length; i++ ) {
			if ( timeFields[i].dateID == this.id ) {
				frmFrontForm.removeUsedTimes( this, timeFields[i].timeID );
			}
		}
	}

	function loadDateFields() {
		if ( typeof __frmDatepicker === 'undefined' ) {
			return;
		}

		var dateFields = __frmDatepicker;
		for ( var i = 0; i < dateFields.length; i++ ) {
			jQuery(document).on('focusin', dateFields[i].triggerID, triggerDateField );
		}

		loadUniqueTimeFields();
	}

	function triggerDateField() {
		/*jshint validthis:true */
		var dateFields = __frmDatepicker;
		var id = this.id;
		var idParts = id.split('-');
		var lastPart = idParts.pop();
		var altID = 'input[id^="'+ idParts.join('-') +'"]';

		jQuery.datepicker.setDefaults(jQuery.datepicker.regional['']);

		for ( var i = 0; i < dateFields.length; i++ ) {
			if ( dateFields[i].triggerID == '#' + id || dateFields[i].triggerID == altID ) {

				var opts = dateFields[i].options;
				jQuery(this).datepicker( jQuery.extend(
					jQuery.datepicker.regional[ dateFields[i].locale ],
					opts
				) );
			}
		}
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

		var field_id = getFieldId( this, false );
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

		checkFieldsWithConditionalLogicDependentOnThis( field_id, jQuery(this) );
		var originalEvent = getOriginalEvent( e );
		checkFieldsWatchingLookup( field_id, jQuery(this), originalEvent );
		doCalculation(field_id, jQuery(this));
		validateField( field_id, this );
	}

	function getOriginalEvent( e ) {
		var originalEvent;
		if ( typeof e.originalEvent !== 'undefined' ) {
			originalEvent = 'value changed';
		} else {
			originalEvent = 'other';
		}
		return originalEvent;
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

		if ( nameParts.length === 1 || nameParts[1] == '[form' || nameParts[1] == '[id' ) {
			return field_id;
		}

		// Check if 'this' is in a repeating section
		if ( jQuery('input[name="item_meta['+ field_id +'][form]"]').length ) {
			// If item_meta[370][0]
			if ( nameParts.length === 2 && nameParts[1] == '[0' ) {
				return 0;
			}

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
			if ( field_id === nameParts[0] ) {
				field_id = field_id +'-'+ nameParts[1].replace('[', '');
			} else {
				field_id = field_id +'-'+ nameParts[0] +'-'+ nameParts[1].replace('[', '');
			}
		}

		return field_id;
	}

	/*****************************************************
	 Conditional Logic Functions
	 ******************************************************/

	// Check if a changed field has other fields depending on it
	function checkFieldsWithConditionalLogicDependentOnThis( field_id, changedInput ){
		if ( typeof __FRMRULES  === 'undefined'
			|| typeof __FRMRULES[field_id] === 'undefined'
			|| __FRMRULES[field_id].dependents.length < 1
			|| changedInput === null
			|| typeof(changedInput) === 'undefined' ) {
			return;
		}

		var triggerFieldArgs = __FRMRULES[field_id];
		var repeatArgs = getRepeatArgsFromFieldName( changedInput[0].name );

		//TODO: make sure dependents are only added once
		for ( var i = 0, l = triggerFieldArgs.dependents.length; i < l; i++ ) {
			hideOrShowFieldById( triggerFieldArgs.dependents[ i ], repeatArgs );
		}
	}

	/**
	 * Hide or show all instances of a field using the field ID
	 *
	 * @param fieldId
	 * @param triggerFieldRepeatArgs
	 *
     */
	function hideOrShowFieldById( fieldId, triggerFieldRepeatArgs ) {
		var depFieldArgs = getRulesForSingleField( fieldId );

		if ( depFieldArgs === false || depFieldArgs.conditions.length < 1 ) {
			// If field has no logic on it, stop now
			return;
		}

		var childFieldDivs = getAllFieldDivs( depFieldArgs, triggerFieldRepeatArgs );

		var childFieldNum = childFieldDivs.length;
		for ( var i = 0; i<childFieldNum; i++ ) {
			depFieldArgs.containerId = childFieldDivs[i];
			addRepeatRow( depFieldArgs, childFieldDivs[i] );
			hideOrShowSingleField( depFieldArgs );
		}
	}

	/**
	 * Get all the field divs that should be hidden or shown, regardless of whether they're on the current page
	 *
	 * @param {Object} depFieldArgs
	 * @param {bool} depFieldArgs.isRepeating
	 * @param {string} depFieldArgs.fieldId
	 * @param {string} depFieldArgs.inSection
	 * @param {object} triggerFieldArgs
	 * @param {string} triggerFieldArgs.repeatingSection
	 * @param {string} triggerFieldArgs.repeatRow
	 * @returns {Array}
     */
	function getAllFieldDivs( depFieldArgs, triggerFieldArgs ) {
		var childFieldDivs = [];

		if ( depFieldArgs.isRepeating ) {
			if ( triggerFieldArgs.repeatingSection != "" ) {
				// If trigger field is repeating/embedded, use its section row in selector
				var container = 'frm_field_' + depFieldArgs.fieldId + '-';
				container += triggerFieldArgs.repeatingSection + '-' + triggerFieldArgs.repeatRow + '_container';
				childFieldDivs.push( container );
			} else {
				// If trigger field is not repeating/embedded, get all repeating field divs
				childFieldDivs = getAllRepeatingFieldDivs( depFieldArgs );
			}
		} else {
			childFieldDivs.push( 'frm_field_' + depFieldArgs.fieldId + '_container' );
		}

		return childFieldDivs;
	}

	/**
	 * Get all instances of a repeating field
	 *
	 * @since 2.0.26
	 * @param {Object} depFieldArgs
	 * @param {string} depFieldArgs.fieldId
     */
	function getAllRepeatingFieldDivs( depFieldArgs ) {
		var childFieldDivs = [];
		var containerFieldId = getContainerFieldId( depFieldArgs )
		var fieldDiv = 'frm_field_' + depFieldArgs.fieldId + '-' + containerFieldId + '-';
		var continueChecking = true;
		var rowCount = 0;
		var selector = '';

		// Always add first row
		childFieldDivs.push( fieldDiv + rowCount + '_container' );

		// Figure out how many additional rows are in the repeating section
		while ( continueChecking === true ) {
			rowCount++;

			selector = 'item_meta[' + containerFieldId + '][' + rowCount + ']';
			if ( document.querySelectorAll( '[name^="' + selector + '"]').length > 0 ) {
				childFieldDivs.push( fieldDiv + rowCount + '_container' );
			} else {
				continueChecking = false;
			}
		}
		// TODO: What if user creates a bunch of rows, removes rows, etc so it is not consecutive?
		// TODO: Maybe use frm_field_x_container class to fetch all repeating fields if they're on the current page

		// Check for rows that are being edited
		var editingRows = document.getElementsByName( 'item_meta[' + containerFieldId + '][id][]' );
		var rowId = '';
		for ( var i=0, l= editingRows.length; i<l; i++ ) {
			rowId = editingRows[i].value;
			childFieldDivs.push( fieldDiv + 'i' + rowId + '_container' );
		}

		return childFieldDivs;
	}

	/**
	 *
	 * @param {Object} depFieldArgs
	 * @param {string} depFieldArgs.inSection
	 * @param {string} depFieldArgs.inEmbedForm
	 * @returns {string}
     */
	function getContainerFieldId( depFieldArgs ){
		var containerFieldId = '';
		if ( depFieldArgs.inSection ) {
			containerFieldId = depFieldArgs.inSection;
		} else if ( depFieldArgs.inEmbedForm ) {
			containerFieldId = depFieldArgs.inEmbedForm;
		}

		return containerFieldId;
	}

	/**
	 *
	 * @param depFieldArgs
	 * @param {bool} depFieldArgs.isRepeating
	 * @param {string} depFieldArgs.inSection
	 * @param {string} depFieldArgs.fieldId
	 * @param {string} depFieldArgs.repeatRow
	 * @param childFieldDivId
     */
	function addRepeatRow( depFieldArgs, childFieldDivId ) {
		if ( depFieldArgs.isRepeating ) {
			var replace = 'frm_field_' + depFieldArgs.fieldId + '-' + depFieldArgs.inSection + '-';
			depFieldArgs.repeatRow = childFieldDivId.replace( replace, '' );
			depFieldArgs.repeatRow = depFieldArgs.repeatRow.replace( '_container', '' );
		} else {
			depFieldArgs.repeatRow = '';
		}
	}

	function hideOrShowSingleField( depFieldArgs ){
		var logicOutcomes = [];
		var len = depFieldArgs.conditions.length;
		for ( var i = 0; i < len; i++ ) {
			logicOutcomes.push( checkLogicCondition( depFieldArgs.conditions[ i ], depFieldArgs ) );
		}

		routeToHideOrShowField( depFieldArgs, logicOutcomes );
	}

	function getRulesForSingleField( fieldId ) {
		if ( typeof __FRMRULES  === 'undefined' || typeof __FRMRULES[fieldId] === 'undefined' ) {
			return false;
		}

		return __FRMRULES[fieldId];
	}

	/**
	 * @param {Array} logicCondition
	 * @param {string} logicCondition.fieldId
	 * @param depFieldArgs
	 * @returns {*}
     */
	function checkLogicCondition( logicCondition, depFieldArgs ) {
		var fieldId = logicCondition.fieldId;

		var logicFieldArgs = getRulesForSingleField( fieldId );

		var fieldValue = getFieldValue( logicFieldArgs, depFieldArgs );

		return getLogicConditionOutcome( logicCondition, fieldValue, depFieldArgs, logicFieldArgs );
	}

	/**
	 * Get the value from any field
	 *
	 * @param logicFieldArgs
	 * @param {string} logicFieldArgs.inputType
	 * @param depFieldArgs
	 * @param {string} depFieldArgs.inSection
	 * @param {string} depFieldArgs.repeatRow
	 * @returns {string}
     */
	function getFieldValue( logicFieldArgs, depFieldArgs ){
		var fieldValue = '';

		if ( logicFieldArgs.inputType == 'radio' || logicFieldArgs.inputType == 'checkbox' ) {
			fieldValue = getValueFromRadioOrCheckbox( logicFieldArgs, depFieldArgs );
		} else {
			fieldValue = getValueFromTextOrDropdown( logicFieldArgs, depFieldArgs );
		}

		fieldValue = cleanFinalFieldValue( fieldValue );

		return fieldValue;
	}

	/**
	 * Get the value from a Text or Dropdown field
	 *
	 * @param logicFieldArgs
	 * @param {string} logicFieldArgs.fieldKey
	 * @param {bool} logicFieldArgs.isRepeating
	 * @param {bool} logicFieldArgs.isMultiSelect
	 * @param depFieldArgs
	 * @param {string} depFieldArgs.repeatRow
 	 */
	function getValueFromTextOrDropdown( logicFieldArgs, depFieldArgs ) {
		var logicFieldValue = '';

		if ( logicFieldArgs.isMultiSelect == true ) {
			return getValueFromMultiSelectDropdown( logicFieldArgs, depFieldArgs );
		}

		var fieldCall = 'field_' + logicFieldArgs.fieldKey;
		if ( logicFieldArgs.isRepeating ) {
			// If trigger field is repeating, dependent field is repeating too
			fieldCall += '-' + depFieldArgs.repeatRow;
		}

		var logicFieldInput = document.getElementById( fieldCall );

		if ( logicFieldInput !== null ) {
			logicFieldValue = logicFieldInput.value;
		}

		return logicFieldValue;
	}

	function getValueFromMultiSelectDropdown( logicFieldArgs, depFieldArgs ) {
		var inputName = buildLogicFieldInputName( logicFieldArgs, depFieldArgs );
		var logicFieldInputs = document.querySelectorAll( '[name^="' + inputName + '"]' );
		var selectedVals = [];

		// TODO: What about if it's read-only?

		if ( logicFieldInputs.length == 1 && logicFieldInputs[0].type != 'hidden' ) {
			selectedVals = jQuery( '[name^="' + inputName + '"]' ).val();
		} else {
			selectedVals = getValuesFromCheckboxInputs( logicFieldInputs );
		}

		return selectedVals;
	}

	/**
	 * Get the value from a Radio or Checkbox field trigger field
	 *
	 * @param {Object} logicFieldArgs
	 * @param {string} logicFieldArgs.inputType
	 * @param {Object} depFieldArgs
	 * @returns {String|Array}
     */
	function getValueFromRadioOrCheckbox( logicFieldArgs, depFieldArgs ) {
		var inputName = buildLogicFieldInputName( logicFieldArgs, depFieldArgs );

		var logicFieldInputs = document.querySelectorAll( 'input[name^="' + inputName + '"]' );

		var logicFieldValue;
		if ( logicFieldArgs.inputType == 'checkbox' ) {
			logicFieldValue = getValuesFromCheckboxInputs( logicFieldInputs );
		} else {
			logicFieldValue = getValueFromRadioInputs( logicFieldInputs );
		}

		return logicFieldValue;
	}

	/**
	 * Build a logic field's input name
	 * Does not include full name for checkbox, address, or multi-select fields
	 *
	 * @param {object} logicFieldArgs
	 * @param {boolean} logicFieldArgs.isRepeating
	 * @param {string} logicFieldArgs.fieldId
	 * @param {object} depFieldArgs
	 * @param {string} depFieldArgs.inSection
	 * @param {string} depFieldArgs.repeatRow
	 * @returns {string}
     */
	function buildLogicFieldInputName( logicFieldArgs, depFieldArgs ) {
		var inputName = '';

		if ( logicFieldArgs.isRepeating ) {
			// If the trigger field is repeating, the child must be repeating as well
			var sectionId = depFieldArgs.inSection;
			var rowId = depFieldArgs.repeatRow;
			inputName = 'item_meta[' + sectionId + '][' + rowId + '][' + logicFieldArgs.fieldId + ']';
		} else {
			inputName = 'item_meta[' + logicFieldArgs.fieldId + ']';
		}

		return inputName;
	}

	function getValuesFromCheckboxInputs( inputs ) {
		var checkedVals = [];

		for ( var i = 0, l=inputs.length; i<l; i++ ) {
			if ( inputs[i].type == 'hidden' || inputs[i].checked ) {
				checkedVals.push( inputs[i].value );
			}
		}

		if ( checkedVals.length == 0 ) {
			checkedVals = '';
		}

		return checkedVals;
	}

	function cleanFinalFieldValue( fieldValue ) {
		if ( typeof fieldValue === 'undefined' ) {
			fieldValue = '';
		} else if ( typeof fieldValue === 'string' ) {
			fieldValue = fieldValue.trim();
		}

		return fieldValue;
	}

	/**
	 * Add values to the show_fields array
	 *
	 * @param {Array} logicCondition
	 * @param {operator:string} logicCondition.operator
	 * @param {value:string} logicCondition.value
	 * @param {String|Array} fieldValue
	 * @param {Object} depFieldArgs
	 * @param {string} depFieldArgs.fieldType
	 * @param {Object} logicFieldArgs
	 * @param {fieldType:string} logicFieldArgs.fieldType
     * @returns {Boolean}
     */
	function getLogicConditionOutcome( logicCondition, fieldValue, depFieldArgs, logicFieldArgs ) {
		var outcome;

		if ( depFieldArgs.fieldType == 'data' && logicFieldArgs.fieldType == 'data' ) {
			// If dep field is Dynamic and logic field is Dynamic
			outcome = getDynamicFieldLogicOutcome( logicCondition, fieldValue, depFieldArgs );
		} else {
			outcome = operators(logicCondition.operator, logicCondition.value, fieldValue);
		}

		return outcome;
	}

	/**
	 * @param {Array} logicCondition
	 * @param {string} logicCondition.operator
	 * @param {string} logicCondition.value
	 * @param {string|Array} fieldValue
	 * @param {object} depFieldArgs
	 * @param {Array} depFieldArgs.dataLogic
	 * @returns {boolean}
     */
	function getDynamicFieldLogicOutcome( logicCondition, fieldValue, depFieldArgs ) {
		var outcome = false;
		if ( logicCondition.value === '' ) {
			// Logic: "Dynamic field is equal to/not equal to anything"

			if ( fieldValue === '' || ( fieldValue.length == 1 && fieldValue[0] === '' ) ) {
				outcome = false;
			} else {
				outcome = true;
			}
		} else {
			// Logic: "Dynamic field is equal to/not equal to specific option"
			outcome = operators( logicCondition.operator, logicCondition.value, fieldValue );
		}
		depFieldArgs.dataLogic = logicCondition;
		depFieldArgs.dataLogic.actualValue = fieldValue;

		return outcome;
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

	/**
	 *
	 * @param {Object} depFieldArgs
	 * @param {string} depFieldArgs.containerId
	 * @param {string} depFieldArgs.fieldType
	 * @param logicOutcomes
     */
	function routeToHideOrShowField( depFieldArgs, logicOutcomes ) {
		var action = getHideOrShowAction( depFieldArgs, logicOutcomes );

		var onCurrentPage = isFieldDivOnPage( depFieldArgs.containerId );

		if ( action == 'show' ) {
			if ( depFieldArgs.fieldType == 'data' && depFieldArgs.hasOwnProperty('dataLogic') ) {
				// Only update dynamic field options/value if it is dependent on another Dynamic field
				updateDynamicField( depFieldArgs, onCurrentPage );
			} else {
				showFieldAndSetValue( depFieldArgs, onCurrentPage );
			}
		} else {
			hideFieldAndClearValue( depFieldArgs, onCurrentPage );
		}
	}

	function isFieldDivOnPage( containerId ) {
		var fieldDiv = document.getElementById( containerId );

		return fieldDiv !== null;
	}

	/**
	 * @param {Object} depFieldArgs
	 * @param {string} depFieldArgs.anyAll
	 * @param {string} depFieldArgs.showHide
	 * @param {Array} logicOutcomes
	 * @returns {string}
     */
	function getHideOrShowAction( depFieldArgs, logicOutcomes ) {
		if ( depFieldArgs.anyAll == 'any' ) {
			// If any of the following match logic
			if ( logicOutcomes.indexOf( true ) > -1 ) {
				action = depFieldArgs.showHide;
			} else {
				action = reverseAction( depFieldArgs.showHide );
			}
		} else {
			// If all of the following match logic
			if ( logicOutcomes.indexOf( false ) > -1 ) {
				action = reverseAction( depFieldArgs.showHide );
			} else {
				action = depFieldArgs.showHide;
			}
		}

		return action;
	}

	function reverseAction( action ) {
		if ( action == 'show' ) {
			action = 'hide';
		} else {
			action = 'show';
		}
		return action;
	}

	/**
	 * @param {Object} depFieldArgs
	 * @param {string} depFieldArgs.containerId
	 * @param {string} depFieldArgs.formId
	 * @param {bool} onCurrentPage
     */
	function showFieldAndSetValue( depFieldArgs, onCurrentPage ){
		if ( isFieldCurrentlyShown( depFieldArgs.containerId, depFieldArgs.formId ) ) {
			return;
		}

		removeFromHideFields(depFieldArgs.containerId, depFieldArgs.formId);
		if ( onCurrentPage ) {
			// Set value, then show field
			setValuesInsideFieldOnPage(depFieldArgs.containerId, depFieldArgs);
			showFieldContainer(depFieldArgs.containerId);
		} else {
			setValuesInsideFieldAcrossPage( depFieldArgs );
		}
	}

	/**
	 * Set the value for all inputs inside of a field div on the current page
	 *
	 * @param {string} container
	 * @param {Object} depFieldArgs
	 * @param {string} depFieldArgs.fieldType
	 * @param {string} depFieldArgs.formId
 	 */
	function setValuesInsideFieldOnPage( container, depFieldArgs ) {
		var inputs = getInputsInFieldOnPage( container );
		var inContainer = ( depFieldArgs.fieldType == 'divider' || depFieldArgs.fieldType == 'form' );

		setValueForInputs( inputs, inContainer, depFieldArgs.formId );
	}

	/**
	 * Set the value for all inputs inside of a field across a page
	 *
	 * @param {Object} depFieldArgs
	 * @param {string} depFieldArgs.fieldType
	 * @param {string} depFieldArgs.formId
	 */
	function setValuesInsideFieldAcrossPage( depFieldArgs ) {
		var inputs = getInputsInFieldAcrossPage( depFieldArgs );
		var inContainer = ( depFieldArgs.fieldType == 'divider' || depFieldArgs.fieldType == 'form' );

		setValueForInputs( inputs, inContainer, depFieldArgs.formId );
	}

	function getInputsInFieldOnPage( containerId ) {
		var container = document.getElementById( containerId );
		return container.querySelectorAll('select[name^="item_meta"], textarea[name^="item_meta"], input[name^="item_meta"]');
	}

	/**
	 * @param {Object} depFieldArgs
	 * @param {string} depFieldArgs.fieldType
	 * @returns {Array}
     */
	function getInputsInFieldAcrossPage( depFieldArgs ){
		var inputs = [];

		if ( depFieldArgs.fieldType == 'divider' ) {
			inputs = getInputsInHiddenSection(depFieldArgs);
		} else if ( depFieldArgs.fieldType == 'form' ) {
			inputs = getInputsInHiddenEmbeddedForm(depFieldArgs);
		} else {
			inputs = getHiddenInputs( depFieldArgs );
		}

		return inputs;
	}

	/**
	 * Get the inputs for a non-repeating field that is type=hidden
	 * @param {object} depFieldArgs
	 * @param {bool} depFieldArgs.isRepeating
	 * @param {string} depFieldArgs.inSection
	 * @param {string} depFieldArgs.repeatRow
	 * @param {string} depFieldArgs.fieldId
	 * @returns {NodeList}
     */
	function getHiddenInputs( depFieldArgs ) {
		var name = '';
		if ( depFieldArgs.isRepeating ) {
			//item_meta[section-id][row-id][field-id]
			name = 'item_meta[' + depFieldArgs.inSection +'][' + depFieldArgs.repeatRow + '][' + depFieldArgs.fieldId + ']';
		} else {
			// item_meta[field-id]
			name = 'item_meta[' + depFieldArgs.fieldId + ']';
		}
		return document.querySelectorAll( '[name^="' + name + '"]' );
	}

	function setValueForInputs( inputs, inContainer, formId ) {
		if ( inputs.length ) {

			var prevInput;
			var typeArray = ['checkbox','radio'];
			for ( var i = 0; i < inputs.length; i++ ) {
				// Don't loop through every input in a radio/checkbox field
				// TODO: Improve this for checkboxes and address fields
				if ( i > 0 && prevInput.name == inputs[i].name && typeArray.indexOf( prevInput.type ) > -1 ) {
					continue;
				}

				// Don't set the value if the field is in a section and it's conditionally hidden
				if ( inContainer && isChildInputConditionallyHidden( inputs[i], formId ) ) {
					continue;
				}

				setDefaultValue( inputs[i] );
				maybeSetLookupFieldValue( inputs[i] );
				maybeDoCalcForSingleField( inputs[i] );

				prevInput = inputs[i];
			}
		}
	}

	// Check if a field input inside of a section or embedded form is conditionally hidden
	function isChildInputConditionallyHidden( input, formId ) {
		var fieldDivPart = getFieldId( input, true );
		var fieldDivId = 'frm_field_' + fieldDivPart + '_container';

		return isFieldConditionallyHidden( fieldDivId, formId );
	}

	function showFieldContainer( containerId ) {
		jQuery( '#' + containerId ).show();
	}

	/**
	 * @param {Object} depFieldArgs
	 * @param {string} depFieldArgs.containerId
	 * @param {string} depFieldArgs.formId
	 * @param onCurrentPage
     */
	function hideFieldAndClearValue( depFieldArgs, onCurrentPage ){
		if ( isFieldConditionallyHidden( depFieldArgs.containerId, depFieldArgs.formId ) ) {
			return;
		}

		if ( onCurrentPage ) {
			hideFieldContainer( depFieldArgs.containerId );
			clearInputsInFieldOnPage( depFieldArgs.containerId );
		} else {
			clearInputsInFieldAcrossPage( depFieldArgs );
		}

		addToHideFields( depFieldArgs.containerId, depFieldArgs.formId );
	}

	function hideFieldContainer( containerId ) {
		jQuery( '#' + containerId ).hide();
	}

	function clearInputsInFieldOnPage( containerId ) {
		var inputs = getInputsInFieldOnPage( containerId );
		clearValueForInputs( inputs );
	}

	function clearInputsInFieldAcrossPage( depFieldArgs ) {
		var inputs = getInputsInFieldAcrossPage( depFieldArgs )
		clearValueForInputs(inputs);
	}

	/**
	 * Get all the child inputs in a hidden section (across a page)
	 *
	 * @param {Object} depFieldArgs
	 * @param {string} depFieldArgs.fieldType
	 * @param {string} depFieldArgs.fieldId
 	 */
	function getInputsInHiddenSection( depFieldArgs ) {
		// If a section, get all inputs with data attribute
		if ( depFieldArgs.fieldType == 'divider' ) {
			var inputs = document.querySelectorAll( '[data-sectionid="' + depFieldArgs.fieldId + '"]' );
		}

		return inputs;
	}

	// Get all the child inputs in a hidden embedded form (across a page)
	function getInputsInHiddenEmbeddedForm( depFieldArgs ) {
		// TODO: maybe remove form and [0]?
		// TODO: what if someone is using embeddedformfieldkey-moretext as their field key? Add data attribute for this
		return document.querySelectorAll( '[id^="field_' + depFieldArgs.fieldKey + '-"]' );
	}

	function clearValueForInputs( inputs ) {
		if ( inputs.length < 1 ){
			return;
		}

		var prevInput;
		for ( var i= 0, l=inputs.length; i<l; i++ ){
			if ( i>0 && prevInput.name != inputs[i].name ) {
				// Only trigger a change after all inputs in a field are cleared
				triggerChange( jQuery(prevInput) );
			}

			if ( inputs[i].type == 'radio' || inputs[i].type == 'checkbox' ) {
				inputs[i].checked = false;
			} else if ( inputs[i].type == 'select' ) {
				inputs[i].selectedIndex = '0';
			} else {
				inputs[i].value = '';
			}

			if ( inputs[i].tagName == 'SELECT' ) {
				var autocomplete = document.getElementById( inputs[i].id + '_chosen' );
				if ( autocomplete !== null ) {
					jQuery(inputs[i]).trigger('chosen:updated');
				}
			}

			prevInput = inputs[i];
		}

		// trigger a change for the final input in the loop
		triggerChange(jQuery(prevInput));
	}

	function isFieldCurrentlyShown( containerId, formId ){
		return isFieldConditionallyHidden( containerId, formId ) === false;
	}

	function isFieldConditionallyHidden( containerId, formId ) {
		var hidden = false;

		var hiddenFields = getHiddenFields( formId );

		if ( hiddenFields.indexOf( containerId ) > -1 ) {
			hidden = true;
		}

		return hidden;
	}

	function clearHideFields() {
		globalHiddenFields = [];
		var hideFieldInputs = document.querySelectorAll( '[id^="frm_hide_fields_"]' );
		clearValueForInputs( hideFieldInputs );
	}

	function addToHideFields( htmlFieldId, formId ) {
		// Get all currently hidden fields
		var hiddenFields = getHiddenFields( formId );

		if ( hiddenFields.indexOf( htmlFieldId ) > -1 ) {
			// If field id is already in the array, move on

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

	function getAllHiddenFields() {
		var hiddenFields = [];
		var hideFieldInputs = document.querySelectorAll('*[id^="frm_hide_fields_"]');
		var formTotal = hideFieldInputs.length;
		var formId;
		for ( var i=0; i<formTotal; i++ ) {
			formId = hideFieldInputs[ i].id.replace( 'frm_hide_fields_', '' );
			hiddenFields = hiddenFields.concat( getHiddenFields( formId ) );
		}

		return hiddenFields;
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

	function setDefaultValue( input ) {
		var $input = jQuery( input );
		var defaultValue = $input.data('frmval');

		if ( typeof defaultValue !== 'undefined' ) {

			if ( input.type == 'checkbox' || input.type == 'radio' ) {
				setCheckboxOrRadioDefaultValue( input.name, defaultValue );

			} else if ( input.name.indexOf( '[]' ) > -1 ) {
				// TODO: fix this for checkboxes, address, and multi-select fields
				setHiddenCheckboxDefaultValue( input.name, defaultValue );

			} else {
				input.value = defaultValue;
			}
			// TODO: Maybe don't trigger change if disabled?
			triggerChange( $input );
		}
	}

	function setCheckboxOrRadioDefaultValue( inputName, defaultValue ) {
		// Get all checkbox/radio inputs for this field
		var radioInputs = document.getElementsByName( inputName );

		// Loop through options and set the default value
		for ( var i = 0, l = radioInputs.length; i < l; i++ ) {
			if ( radioInputs[i].type == 'hidden' ) {
				// If field is read-only and there is a hidden input
				if ( jQuery.isArray(defaultValue) && defaultValue[i] !== null ) {
					radioInputs[i].value = defaultValue[i];
				} else {
					radioInputs[i].value = defaultValue;
				}
			} else if (radioInputs[i].value == defaultValue
				|| ( jQuery.isArray(defaultValue) && defaultValue.indexOf( radioInputs[i].value ) > -1 ) ) {
				// If input's value matches the default value, set checked to true

				radioInputs[i].checked = true;
				if ( radioInputs[i].type == 'radio') {
					break;
				}
			}
		}
	}

	// Set the default value for hidden checkbox or multi-select dropdown fields
	function setHiddenCheckboxDefaultValue( inputName, defaultValue ){
		// Get all the hidden inputs with the same name
		var hiddenInputs = document.getElementsByName( inputName );

		if ( jQuery.isArray(defaultValue) ) {
			for ( var i = 0, l = defaultValue.length; i < l; i++ ) {
				if ( i in hiddenInputs ) {
					hiddenInputs[i].value = defaultValue[i];
				} else {
					// TODO: accommodate for when there are multiple default values but the user has removed some
				}
			}

		} else {
			hiddenInputs[0].value = defaultValue;
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

	/*****************************************************
	 * Lookup Field Functions
	 ******************************************************/

	// Check all fields that are "watching" a lookup field that changed
	function checkFieldsWatchingLookup(field_id, changedInput, originalEvent ) {
		if ( typeof __FRMLOOKUP  === 'undefined' || typeof __FRMLOOKUP[field_id] === 'undefined' ) {
			return;
		}

		var triggerFieldArgs = __FRMLOOKUP[field_id];

		if ( changedInput === null || typeof(changedInput) === 'undefined' || changedInput.is('[disabled]') ) {
			return;
		}

		var parentRepeatId = maybeGetRepeatId( triggerFieldArgs, changedInput[0] );

		var currentChildId;
		for ( var i = 0, l = triggerFieldArgs.dependents.length; i < l; i++ ) {
			currentChildId = triggerFieldArgs.dependents[ i ];
			updateWatchingFieldById( currentChildId, parentRepeatId, originalEvent );
		}
	}

	// Get the repeating row index from a field element (could be an input, select, or frm_opt_container div)
	function maybeGetRepeatId( triggerFieldArgs, fieldElement ) {
		var repeatId = '';
		if ( triggerFieldArgs.lookupType == 'radio' ) {
			if ( fieldElement.type == 'radio' || fieldElement.type == 'hidden' ) {
				// If input[type=radio]
				repeatId = fieldElement.getAttribute('data-lookupref').replace( 'frm_lookup_' + triggerFieldArgs.fieldKey, '' );
			} else {
				// If div.frm_opt_container
				repeatId = fieldElement.id.replace( 'frm_lookup_cont_' + triggerFieldArgs.fieldKey, '' );
			}
		} else {
			// If input or select
			repeatId = fieldElement.id.replace( 'field_' + triggerFieldArgs.fieldKey, '' );
		}
		return repeatId;
	}

	// Set the value in a regular field that is watching a lookup field when it is conditionally shown
	function maybeSetLookupFieldValue( input ) {
		var fieldId = getFieldId( input, false );

		if ( typeof __FRMLOOKUP  === 'undefined' || typeof __FRMLOOKUP[fieldId] === 'undefined' ) {
			return;
		}

		var childFieldArgs = __FRMLOOKUP[fieldId];

		if ( childFieldArgs.fieldType != 'lookup' ) {
			updateSingleLookupField( childFieldArgs, input, 'value changed' );
		}
	}

	// Update the field options/value for a field ID that is watching a Lookup field
	function updateWatchingFieldById(field_id, parentRepeatId, originalEvent ) {
		if ( typeof __FRMLOOKUP  === 'undefined' || typeof __FRMLOOKUP[field_id] === 'undefined' ) {
			return;
		}

		var childFieldArgs = __FRMLOOKUP[field_id];

		// If lookup field has no parents, no need to update this field
		if ( childFieldArgs.parents.length < 1 ) {
			return;
		}

		var childFieldElements = [];
		if ( childFieldArgs.lookupType == 'radio' ) {
			childFieldElements = getAllRadioLookupFieldElements( childFieldArgs, parentRepeatId );

		} else if ( childFieldArgs.lookupType == 'select' ) {
			childFieldElements = getAllSelectLookupFieldElements( childFieldArgs, parentRepeatId );
		} else {
			childFieldElements = getAllTextFieldElements( childFieldArgs, parentRepeatId );
		}

		var childFieldNum = childFieldElements.length;
		for ( var i = 0; i<childFieldNum; i++ ) {
			updateSingleLookupField( childFieldArgs, childFieldElements[i], originalEvent );
		}
	}

	// Get all the opt container for all occurrences of a specific Radio Lookup field (current page only)
	function getAllRadioLookupFieldElements( childFieldArgs, parentRepeatId ) {
		var childFieldElements = [];
		if ( parentRepeatId ) {
			// If parent is in a repeating section, child must be repeating
			var childField = document.getElementById( 'frm_lookup_cont_' + childFieldArgs.fieldKey + parentRepeatId );
			childFieldElements.push( childField );

		} else {
			childFieldElements = document.querySelectorAll('*[id^="frm_lookup_cont_' + childFieldArgs.fieldKey + '"]');
		}
		return childFieldElements;
	}

	// Get all the occurrences of a specific Select Lookup field (on the current page only)
	function getAllSelectLookupFieldElements( childFieldArgs, parentRepeatId ) {
		var childFieldElements = [];
		if ( parentRepeatId ) {
			// If parent is in a repeating section, child must be repeating
			var childField = document.getElementById( 'field_' + childFieldArgs.fieldKey + parentRepeatId );
			if ( childField.tagName == 'SELECT' ) {
				childFieldElements.push( childField );
			}

		} else {
			childFieldElements = document.querySelectorAll('select[id^="field_' + childFieldArgs.fieldKey + '"]');
		}
		return childFieldElements;
	}

	// Get all the occurences of a specific Text field
	function getAllTextFieldElements( childFieldArgs, parentRepeatId ) {
		var childFieldElements = [];
		if ( parentRepeatId ) {
			// If parent is in a repeating section, child must be repeating
			var childField = document.getElementById( 'field_' + childFieldArgs.fieldKey + parentRepeatId );
			childFieldElements.push( childField );

		} else {
			childFieldElements = document.querySelectorAll('*[id^="field_' + childFieldArgs.fieldKey + '"]');
		}
		return childFieldElements;
	}

	// Update the field options for an individual lookup field
	function updateSingleLookupField( childFieldArgs, childElement, originalEvent ) {
		childFieldArgs.repeatId = maybeGetRepeatId( childFieldArgs, childElement );
		childFieldArgs.parentVals = getParentLookupFieldVals( childFieldArgs );

		if ( childFieldArgs.lookupType == 'select' ) {
			maybeReplaceSelectLookupFieldOptions( childFieldArgs, childElement );
		} else if ( childFieldArgs.lookupType == 'radio' ) {
			replaceRadioLookupFieldOptions( childFieldArgs, childElement );
		} else {
			maybeInsertTextLookupFieldValue( childFieldArgs, childElement, originalEvent );
		}
	}

	// Get the field values from all parents
	function getParentLookupFieldVals( childFieldArgs ) {
		var parentVals = [];
		var parentIds = childFieldArgs.parents;

		var currentParentArgs, currentParentId;
		var l = parentIds.length;
		var parentValue = false;
		for ( var i = 0; i < l; i++ ) {
			currentParentId = parentIds[i];
			currentParentArgs = __FRMLOOKUP[ currentParentId ];

			if ( currentParentArgs.lookupType == 'select' ) {
				parentValue = getValueFromSelectLookup( currentParentArgs, childFieldArgs );
			} else if ( currentParentArgs.lookupType == 'radio' ) {
				parentValue = getValueFromRadioLookup( currentParentArgs, childFieldArgs );
			} else if ( currentParentArgs.lookupType == 'text' ) {
				parentValue = getValueFromSelectLookup( currentParentArgs, childFieldArgs );
			}

			// If any parents have blank values, don't waste time looking for values
			if ( parentValue === '' || parentValue === false ) {
				parentVals = false;
				break;
			}

			parentVals[i] = parentValue;
		}

		return parentVals;
	}

	// Get the value from a Dropdown Lookup field
	function getValueFromSelectLookup( currentParentArgs, childFieldArgs ) {
		var parentValue = false;

		// If child is repeating, parent may be repeating as well
		var parentSelect = document.getElementById( 'field_' + currentParentArgs.fieldKey + childFieldArgs.repeatId );

		// If parent isn't repeating (but the child is)
		if ( parentSelect === null && childFieldArgs.repeatId ) {
			parentSelect = document.getElementById( 'field_' + currentParentArgs.fieldKey );
		}

		parentValue = parentSelect.value;

		return parentValue;
	}

	// Get the value from a Radio Lookup field
	function getValueFromRadioLookup( currentParentArgs, childFieldArgs ) {

		// If child is repeating, parent may be repeating as well
		var parentRadioInputs = document.querySelectorAll( '[data-lookupref="frm_lookup_' + currentParentArgs.fieldKey + childFieldArgs.repeatId + '"]' );

		// If child is repeating, but the parent is not
		if ( parentRadioInputs.length == 0 && childFieldArgs.repeatId ) {
			parentRadioInputs = document.querySelectorAll( '[data-lookupref="frm_lookup_' + currentParentArgs.fieldKey + '"]' );
		}

		var parentValue = getValueFromRadioInputs( parentRadioInputs );

		return parentValue;
	}

	// Get the value from array of radio inputs (could be type="hidden" or type="radio")
	function getValueFromRadioInputs( radioInputs ) {
		var radioValue = false;

		var l = radioInputs.length;
		for ( var i = 0; i<l; i++ ) {
			if ( radioInputs[i].type == 'hidden' || radioInputs[i].checked ) {
				radioValue = radioInputs[i].value;
				break;
			}
		}

		return radioValue;
	}

	// Get new options for a Dropdown Lookup field if all parents have a value
	function maybeReplaceSelectLookupFieldOptions( childFieldArgs, childSelect ) {
		if ( childSelect.type == 'hidden' ) {
			// Field is on a different page or is hidden with the visibility option
			return;
		}

		if ( childFieldArgs.parentVals === false  ) {
			// If any parents have blank values, don't waste time looking for values
			childSelect.options.length = 1;
			childSelect.value = '';
			maybeUpdateChosenOptions( childSelect )
			triggerChange( jQuery(childSelect), childFieldArgs.fieldKey );
		} else {
			// If all parents have values, check for updated options
			jQuery.ajax({
				type:'POST',
				url:frm_js.ajax_url,
				data:{
					action:'frm_replace_lookup_field_options', parent_fields:childFieldArgs.parents,
					parent_vals:childFieldArgs.parentVals, field_id:childFieldArgs.fieldId, nonce:frm_js.nonce
				},
				success:function(newOptions){
					replaceSelectLookupFieldOptions( childFieldArgs, childSelect, newOptions );
				}
			});
		}
	}

	// Update chosen options if autocomplete is enabled
	function maybeUpdateChosenOptions( childSelect ) {
		if ( childSelect.className.indexOf( 'frm_chzn' ) > -1 && jQuery().chosen ) {
			jQuery( childSelect ).trigger('chosen:updated');
		}
	}

	// Replace the options in a Lookup Field dropdown
	function replaceSelectLookupFieldOptions( childFieldArgs, childSelect, newOptions ) {
		var origVal = childSelect.value;

		newOptions = JSON.parse( newOptions );

		// Remove old options
		for ( var i = childSelect.options.length; i>0; i-- ) {
			childSelect.remove(i);
		}

		// Add new options
		var optsLength = newOptions.length;
		for ( i = 0; i<optsLength; i++ ) {
			childSelect.options[i+1]=new Option(newOptions[i], newOptions[i], false, false);
		}

		setLookupFieldVal( childSelect, origVal );

		maybeUpdateChosenOptions( childSelect );

		// Trigger a change if the new value is different from the old value
		if ( childSelect.value != origVal ) {
			triggerChange( jQuery(childSelect), childFieldArgs.fieldKey );
		}
	}

	// Set the value in a refreshed Lookup Field
	function setLookupFieldVal( childSelect, origVal ) {
		// Try setting the dropdown to the original value
		childSelect.value = origVal;
		if ( childSelect.value === '' ) {
			// If the original value is no longer present, try setting to default value
			var defaultValue = childSelect.getAttribute('data-frmval');
			if ( defaultValue !== null ) {
				childSelect.value = defaultValue;
			}
		}
	}

	// Get new value for a text field if all Lookup Field parents have a value
	function maybeInsertTextLookupFieldValue( childFieldArgs, childInput, originalEvent ) {
		var containerId = getFieldDivId( childFieldArgs );
		if ( isFieldConditionallyHidden( containerId, childFieldArgs.formId ) ) {
			// TODO: What if field is in conditionally hidden section?
			return;
		}

		if ( childFieldArgs.parentVals === false  ) {
			// If the original event was NOT triggered from a direct value change to the Lookup field,
			// do not update the text fields watching that Lookup field
			if ( originalEvent !== 'value changed' ) {
				return;
			}
			// If any parents have blank values, set the field value to the default value
			var newValue = childInput.getAttribute('data-frmval');
			if ( newValue === null ) {
				newValue = '';
			}
			insertTextLookupFieldValue ( childFieldArgs, childInput, newValue );
		} else {
			// If all parents have values, check for a new value
			jQuery.ajax({
				type:'POST',
				url:frm_js.ajax_url,
				data:{
					action:'frm_get_lookup_text_value', parent_fields:childFieldArgs.parents,
					parent_vals:childFieldArgs.parentVals, field_id:childFieldArgs.fieldId, nonce:frm_js.nonce
				},
				success:function(newValue){
					if ( childInput.value != newValue ) {
						insertTextLookupFieldValue( childFieldArgs, childInput, newValue );
					}
				}
			});
		}
	}

	/**
	 * Get the field div ID from a dependent Lookup field
	 *
	 * @since 2.01.0
	 * @param {Object} childFieldArgs
	 * @param {string} childFieldArgs.fieldId
	 * @param {string} childFieldArgs.inSection
	 * @param {string} childFieldArgs.repeatId
	 * @returns {string}
     */
	function getFieldDivId( childFieldArgs ){
		var divId = 'frm_field_' + childFieldArgs.fieldId;

		if ( childFieldArgs.repeatId != '' ) {
			divId += '-' + childFieldArgs.inSection + '-' + childFieldArgs.repeatId;
		}

		divId += '_container';

		return divId;
	}

	// Insert a new text field Lookup value
	function insertTextLookupFieldValue( childFieldArgs, childInput, newValue ) {
		childInput.value = newValue;
		triggerChange( jQuery( childInput ), childFieldArgs.fieldKey );
	}

	// Replace the options in a Radio Lookup field
	function replaceRadioLookupFieldOptions( childFieldArgs, radioCont ) {
		var repeatingFieldId = getRepeatingFieldIdForRadioLookup( radioCont );
		var radioInputs = radioCont.getElementsByTagName( 'input' );
		var currentValue = getValueFromRadioInputs( radioInputs )

		jQuery.ajax({
			type:'POST',
			url:frm_js.ajax_url,
			data:{
				action:'frm_replace_radio_lookup_options',
				parent_fields:childFieldArgs.parents,
				parent_vals:childFieldArgs.parentVals,
				field_id:childFieldArgs.fieldId,
				repeating_field_id:repeatingFieldId,
				row_index:childFieldArgs.repeatId,
				current_value:currentValue,
				nonce:frm_js.nonce
			},
			success:function(newHtml){
				radioCont.innerHTML = newHtml;
				triggerChange( jQuery( radioInputs[0] ), childFieldArgs.fieldKey );
			}
		});
	}

	// Get the repeating section field ID for a Radio Lookup field
	function getRepeatingFieldIdForRadioLookup( radioCont ) {
		var repeatingFieldId = 0;
		if ( radioCont.getAttribute( 'data-lookuprepeat' ) !== 'undefined' ) {
			repeatingFieldId = radioCont.getAttribute( 'data-lookuprepeat' );
		}
		return repeatingFieldId;
	}

	/*******************************************************
	 Dynamic Field Functions
	 *******************************************************/

	// Update a Dynamic field's data or options
	function updateDynamicField( depFieldArgs, onCurrentPage ) {
		// If field is not on current page, return
		if ( ! onCurrentPage ) {
			return;
		}

		var depFieldArgsCopy = cloneObjectForDynamicFields( depFieldArgs );

		if ( depFieldArgsCopy.inputType == 'data' ) {
			// TODO: Maybe make this work across pages
			updateDynamicListData( depFieldArgsCopy );
		} else {
			updateDynamicFieldOptions( depFieldArgsCopy );
		}
	}

	function getFieldElement( depFieldArgs ) {

	}

	// Clone the depFieldArgs object for use in ajax requests
	function cloneObjectForDynamicFields( depFieldArgs ){
		var dataLogic = {
			actualValue:depFieldArgs.dataLogic.actualValue,
			fieldId:depFieldArgs.dataLogic.fieldId
		};

		var dynamicFieldArgs = {
			fieldId:depFieldArgs.fieldId,
			formId:depFieldArgs.formId,
			containerId:depFieldArgs.containerId,
			dataLogic:dataLogic,
			children:'',
			inputType:depFieldArgs.inputType
		};

		return dynamicFieldArgs;
	}

	// Update a Dynamic List field
	function updateDynamicListData( depFieldArgs ){
		var $fieldDiv = jQuery( '#' + depFieldArgs.containerId);
		var $optContainer = $fieldDiv.find('.frm_opt_container');

		addLoadingIcon( $optContainer );

		jQuery.ajax({
			type:'POST',url:frm_js.ajax_url,
			data:{
				action:'frm_fields_ajax_get_data',
				entry_id:depFieldArgs.dataLogic.actualValue,
				current_field:depFieldArgs.fieldId,
				hide_id:depFieldArgs.containerId,
				nonce:frm_js.nonce
			},
			success:function(html){
				$optContainer.html(html);

				var $listInputs = $optContainer.children( 'input' );
				var listVal = $listInputs.val();
				if ( html === '' || listVal === '' ) {
					hideDynamicField( depFieldArgs );
				} else {
					showDynamicField( depFieldArgs, $fieldDiv, $listInputs )
				}
			}
		});
	}

	// Update a Dynamic dropdown, radio, or checkbox
	function updateDynamicFieldOptions( depFieldArgs, fieldElement ){
		var $fieldDiv = jQuery( '#' + depFieldArgs.containerId );
		var $optContainer = $fieldDiv.find('.frm_opt_container');

		var hiddenInput = $fieldDiv.find('select[name^="item_meta"], textarea[name^="item_meta"], input[name^="item_meta"]');
		var prevVal = getPrevFieldValue( hiddenInput );
		var defaultVal = hiddenInput.data('frmval');

		addLoadingIcon( $optContainer )

		jQuery.ajax({
			type:'POST',
			url:frm_js.ajax_url,
			data:{
				action:'frm_fields_ajax_data_options',
				trigger_field_id:depFieldArgs.dataLogic.fieldId,
				entry_id:depFieldArgs.dataLogic.actualValue,
				field_id:depFieldArgs.fieldId,
				default_value:defaultVal,
				container_id:depFieldArgs.containerId,
				prev_val:prevVal,
				nonce:frm_js.nonce
			},
			success:function(html){
				$optContainer.html(html);
				var $dynamicFieldInputs = $optContainer.find('select, input, textarea');

				if ( html === '' || ( $dynamicFieldInputs.length == 1 && $dynamicFieldInputs.attr('type') == 'hidden' ) ) {
					hideDynamicField( depFieldArgs );
				} else {
					showDynamicField( depFieldArgs, $fieldDiv, $dynamicFieldInputs );
				}
			}
		});

	}

	// Insert the loading icon
	function addLoadingIcon( $optContainer ) {
		$optContainer.html( '<span class="frm-loading-img"></span>' );
	}

	// Get the previous field value in a Dynamic field
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

	// Hide and clear a Dynamic Field
	function hideDynamicField( depFieldArgs ) {
		hideFieldAndClearValue( depFieldArgs, true );
	}

	// Show Dynamic field
	function showDynamicField( depFieldArgs, $fieldDiv, $fieldInputs ) {
		if ( isFieldConditionallyHidden( depFieldArgs.containerId, depFieldArgs.formId ) ) {
			removeFromHideFields( depFieldArgs.containerId, depFieldArgs.formId );
			$fieldDiv.show();
		}

		if( $fieldInputs.hasClass('frm_chzn') && jQuery().chosen){
			jQuery('.frm_chzn').chosen({allow_single_deselect:true});
		}

		triggerChange( $fieldInputs );
	}

	/*************************************************
	 Calculations
	 ************************************************/

	function triggerCalc(){
		if ( typeof __FRMCALC === 'undefined' ) {
			// there are no calculations on this page
			return;
		}

		var triggers = __FRMCALC.triggers;
		if ( triggers ) {
			jQuery(triggers.join()).trigger({type:'change',selfTriggered:true});
		}
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

			// Proceed with calculation if total field is not conditionally hidden
			if ( isTotalFieldConditionallyHidden( all_calcs.calc[ keys[i] ], triggerField.attr('name') ) === false ) {
				doSingleCalculation( all_calcs, keys[i], vals, triggerField );
			}
		}
	}

	// Check if a total field is conditionally hidden
	function isTotalFieldConditionallyHidden( calcDetails, triggerFieldName ) {
		var hidden = false;
		var fieldId = calcDetails.field_id;
		var formId = calcDetails.form_id;

		// Check if there are any conditionally hidden fields
		var hiddenFields = getHiddenFields( formId );
		if ( hiddenFields.length < 1 ) {
			return hidden;
		}

		if ( calcDetails.inSection === 0 && calcDetails.inEmbedForm === 0 ) {
			// Field is not in a section or embedded form
			hidden = isNonRepeatingFieldConditionallyHidden( fieldId, hiddenFields );

		} else {
			// Field is in a section or embedded form
			var repeatArgs = getRepeatArgsFromFieldName( triggerFieldName );

			if ( isNonRepeatingFieldConditionallyHidden( fieldId, hiddenFields ) ) {
				// Check standard field
				hidden = true;
			} else if ( isRepeatingFieldConditionallyHidden( fieldId, repeatArgs, hiddenFields ) ){
				// Check repeating field
				hidden = true;
			} else if ( calcDetails.inSection !== 0 && calcDetails.inEmbedForm !== 0 ) {
				// Check section in embedded form
				hidden = isRepeatingFieldConditionallyHidden( calcDetails.inSection, repeatArgs, hiddenFields );
			} else if ( calcDetails.inSection !== 0 ) {
				// Check section
				hidden = isNonRepeatingFieldConditionallyHidden( calcDetails.inSection, hiddenFields);
			} else if ( calcDetails.inEmbedForm !== 0 ) {
				// Check embedded form
				hidden = isNonRepeatingFieldConditionallyHidden( calcDetails.inEmbedForm, hiddenFields);
			}
		}

		return hidden;
	}

	// Check if a non-repeating field is conditionally hidden
	function isNonRepeatingFieldConditionallyHidden( fieldId, hiddenFields ) {
		var htmlID = 'frm_field_' + fieldId + '_container';
		return ( hiddenFields.indexOf( htmlID ) > -1 );
	}

	// Check if a repeating field is conditionally hidden
	function isRepeatingFieldConditionallyHidden( fieldId, repeatArgs, hiddenFields ) {
		var hidden = false;

		if ( repeatArgs.repeatingSection ) {
			var fieldRepeatId = 'frm_field_' + fieldId + '-' + repeatArgs.repeatingSection;
			fieldRepeatId += '-' + repeatArgs.repeatRow + '_container';
			hidden = ( hiddenFields.indexOf( fieldRepeatId ) > -1 );
		}

		return hidden;
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

	function isRepeatingFieldByName( fieldName ) {
		var fieldNameParts = fieldName.split( '][' );
		return fieldNameParts.length >= 3;
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

	function shouldJSValidate( object ) {
		var validate = jQuery(object).hasClass('frm_js_validate');
		if ( validate ) {
			if ( savingDraftEntry( object ) || goingToPrevPage( object ) ) {
				validate = false;
			}
		}

		return validate;
	}

	function savingDraftEntry( object ) {
		var isDraft = false;
		var savingDraft = jQuery(object).find('.frm_saving_draft');
		if ( savingDraft.length ) {
			isDraft = savingDraft.val();
		}
		return isDraft;
	}

	function goingToPrevPage( object ) {
		var goingBack = false;
		var nextPage = jQuery(object).find('.frm_next_page');
		if ( nextPage.length && nextPage.val() ) {
			var formID = jQuery(object).find('input[name="form_id"]').val();
			var prevPage = jQuery(object).find('input[name="frm_page_order_'+ formID +'"]');
			if ( prevPage.length ) {
				prevPage = prevPage.val();
			} else {
				prevPage = 0;
			}

			if ( ! prevPage || ( nextPage.val() < prevPage ) ) {
				goingBack = true;
			}
		}

		return goingBack;
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

		errors = validateRecaptcha( object, errors );

		return errors;
	}

	function validateField( fieldId, field ) {
		var errors = [];

		var $fieldCont = jQuery(field).closest('.frm_form_field');
		if ( $fieldCont.hasClass('.frm_required_field') && ! jQuery(field).hasClass('.frm_optional') ) {
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

		removeFieldError( $fieldCont );
		if (  Object.keys(errors).length > 0 ) {
			for ( var key in errors ) {
				addFieldError( $fieldCont, key, errors );
			}
		}
	}

	function checkRequiredField( field, errors ) {
		if ( field.type == 'hidden' ) {
			return errors;
		}

		var val = '';
		var fieldID = '';
		if ( field.type == 'checkbox' || field.type == 'radio' ) {
			var checkGroup = jQuery('input[name="'+field.name+'"]').closest('.frm_required_field').find('input:checked');
			jQuery(checkGroup).each(function() {
			    val = this.value;
			});
		} else if ( field.type == 'file' ) {
			var fileID = jQuery(field).data('fid');
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
			if ( typeof val !== 'string' ) {
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
			var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
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
					var $recapField = jQuery(object).find('.frm-g-recaptcha, .g-recaptcha');
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

					checkFieldsOnPage();
				}else{
					jQuery(object).find('input[type="submit"], input[type="button"]').removeAttr('disabled');
					jQuery(object).find('.frm_ajax_loading').removeClass('frm_loading_now');

					//show errors
					var cont_submit = true;
					jQuery('.form-field').removeClass('frm_blank_field');
					jQuery('.form-field .frm_error').replaceWith('');

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
								addFieldError( $fieldCont, key, errObj );

								cont_submit = false;

								var $recaptcha = jQuery(object).find('#frm_field_'+key+'_container .frm-g-recaptcha, #frm_field_'+key+'_container .g-recaptcha');
								if ( $recaptcha.length ) {
									show_captcha = true;
									var recaptchaID = $recaptcha.data('rid');
									if ( recaptchaID ) {
										grecaptcha.reset( recaptchaID );
									} else {
										grecaptcha.reset();
									}
								}
							}
						}else if(key == 'redirect'){
							window.location = errObj[key];
							return;
						}
					}

					scrollToFirstField( object );

					if(show_captcha !== true){
						jQuery(object).find('.frm-g-recaptcha, .g-recaptcha').closest('.frm_form_field').replaceWith('<input type="hidden" name="recaptcha_checked" value="'+ frm_js.nonce +'">');
					}

					if(cont_submit){
						object.submit();
					}
				}
			},
			error:function(){
				jQuery(object).find('input[type="submit"], input[type="button"]').removeAttr('disabled');
				object.submit();
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

	function scrollToFirstField( object ) {
		var field = jQuery(object).find('.frm_blank_field:first');
		if ( field.length ) {
			frmFrontForm.scrollMsg( field, object, true );
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
			var lastRowIndex = jQuery('.frm_repeat_'+ id +':last').attr('id').replace('frm_section_'+ id +'-', '');
			if ( lastRowIndex.indexOf( 'i' ) > -1 ) {
				i = 1;
			} else {
				i = 1 + parseInt( lastRowIndex );
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
				var repeatArgs = {
					repeatingSection: id.toString(),
					repeatRow: i.toString(),
				}

                // hide fields with conditional logic
                jQuery(html).find('input, select, textarea').each(function(){
					if ( this.type != 'file' ) {

						// Readonly dropdown fields won't have a name attribute
						if ( this.name === '' ) {
							return true;
						}
						fieldID = this.name.replace('item_meta[', '').split(']')[2].replace('[', '');
						if ( jQuery.inArray(fieldID, checked ) == -1 ) {
							if ( this.id === false || this.id === '' ) {
								return;
							}
							fieldObject = jQuery( '#' + this.id );
							checked.push(fieldID);
							hideOrShowFieldById( fieldID, repeatArgs );
							updateWatchingFieldById( fieldID, '-' + i, 'value changed' );
							// TODO: maybe trigger a change instead of running these three functions
							checkFieldsWithConditionalLogicDependentOnThis( fieldID, fieldObject );
							checkFieldsWatchingLookup( fieldID, fieldObject, 'value changed' );
							doCalculation(fieldID, fieldObject);
							reset = 'persist';
						}
					}
                });
				addingRow = '';

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

	/**********************************************
	 * General Helpers
	 *********************************************/

	function checkFieldsOnPage(){
		checkPreviouslyHiddenFields();
		loadDateFields();
		loadCustomInputMasks();
		loadChosen();
		checkDynamicFields();
		triggerCalc();
	}

	function checkPreviouslyHiddenFields() {
		if (typeof __frmHideFields !== 'undefined') {
			frmFrontForm.hidePreviouslyHiddenFields();
		}
	}

	function loadChosen() {
		if (typeof __frmChosen !== 'undefined') {
			jQuery('.frm_chzn').chosen(__frmChosen);
		}
	}

	function checkConditionalLogic() {
		if (typeof __frmHideOrShowFields !== 'undefined') {
			frmFrontForm.hideOrShowFields(__frmHideOrShowFields);
		}
	}

	function checkDynamicFields() {
		if (typeof __frmDepDynamicFields !== 'undefined') {
			frmFrontForm.checkDependentDynamicFields(__frmDepDynamicFields);
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

	function loadCustomInputMasks() {
		if ( typeof __frmMasks === 'undefined' ) {
			return;
		}

		var maskFields = __frmMasks;
		for ( var i = 0; i < maskFields.length; i++ ) {
			jQuery( maskFields[i].trigger ).attr( 'data-frmmask', maskFields[i].mask );
		}
	}

	// Get the section ID and repeat row from a field name
	function getRepeatArgsFromFieldName( fieldName ) {
		var repeatArgs = {repeatingSection:"", repeatRow:""};

		if ( typeof fieldName !== 'undefined' && isRepeatingFieldByName( fieldName ) ) {
			var inputNameParts = fieldName.split( '][' );
			repeatArgs.repeatingSection = inputNameParts[0].replace('item_meta[', '');
			repeatArgs.repeatRow = inputNameParts[1];
		}

		return repeatArgs;
	}

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

	function isNumeric( obj ) {
		return !jQuery.isArray( obj ) && (obj - parseFloat( obj ) + 1) >= 0;
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

			checkFieldsOnPage();
			checkConditionalLogic();

			// Add fallbacks for the beloved IE8
			addIndexOfFallbackForIE8();
			addTrimFallbackForIE8();
			addFilterFallbackForIE8();
			addKeysFallbackForIE8();
		},

		submitForm: function(e){
			var object = this;

			var classList = object.classList;
			if ( classList ) {
				var isPro = classList.contains('frm_pro_form');
				if ( ! isPro ) {
					return;
				}
			}

			if ( jQuery('body').hasClass('wp-admin') ) {
				return;
			}

			e.preventDefault();
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

        scrollToID: function(id){
            var frm_pos = jQuery(document.getElementById(id).offset());
            window.scrollTo(frm_pos.left, frm_pos.top);
        },

		scrollMsg: function( id, object, animate ) {
			var scrollObj = '';
			if(typeof(object) == 'undefined'){
				scrollObj = jQuery(document.getElementById('frm_form_'+id+'_container'));
			} else if ( typeof id == 'string' ) {
				scrollObj = jQuery(object).find('#frm_field_'+id+'_container');
			} else {
				scrollObj = id;
			}
			var newPos = scrollObj.offset().top;

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

		savingDraft: function(object){
			return savingDraftEntry(object);
		},

		goingToPreviousPage: function(object){
			return goingToPrevPage(object);
		},

		hideOrShowFields: function(ids){
			clearHideFields();
			var len = ids.length;
			var repeatArgs = { repeatingSection: '', repeatRow: '' };
			for ( var i = 0, l = len; i < l; i++ ) {
				hideOrShowFieldById( ids[i], repeatArgs );
			}
		},

		hidePreviouslyHiddenFields: function(){
			var hiddenFields = getAllHiddenFields();
			var len = hiddenFields.length;
			for ( var i = 0, l = len; i < l; i++ ) {
				var container = document.getElementById( hiddenFields[ i ] );
				if ( container !== null ) {
					container.style.display = 'none';
				}
			}
		},

		checkDependentDynamicFields: function(ids){
			var len = ids.length;
			var repeatArgs = { repeatingSection: '', repeatRow: '' };
			for ( var i = 0, l = len; i < l; i++ ) {
				hideOrShowFieldById( ids[i], repeatArgs );
			}
		},

		checkAllDependentLookupFields: function(ids){
			ids = JSON.parse(ids);

			var fieldId;
			var len = ids.length;
			for ( var i = 0, l = len; i < l; i++ ) {
				fieldId = ids[i];
				updateWatchingFieldById( fieldId, '', 'value changed' );
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
		removeUsedTimes: function( obj, timeField ) {
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

function frmRecaptcha() {
	var captchas = jQuery('.frm-g-recaptcha');
	for ( var c = 0, cl = captchas.length; c < cl; c++ ) {
		var recaptchaID = grecaptcha.render( captchas[c].id, {
			'sitekey': captchas[c].getAttribute('data-sitekey'),
			'size': captchas[c].getAttribute('data-size'),
			'theme': captchas[c].getAttribute('data-theme')
		} );
		captchas[c].setAttribute('data-rid', recaptchaID);
	}
}

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
