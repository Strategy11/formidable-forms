function frmFrontFormJS(){
	'use strict';
	var show_fields = [];
	var hide_later = [];
	var hidden_fields = [];
    var frm_checked_dep = [];
	var addingRow = '';
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

		checkDependentField('und', field_id, null, jQuery(this), reset);
		doCalculation(field_id, jQuery(this));
		//validateField( field_id, this );
	}

	/* Get the ID of the field that changed*/
	function getFieldId( field, fullID ) {
		var fieldName = '';
		if ( field instanceof jQuery ) {
			fieldName = field.attr('name');
		} else {
			fieldName = field.name;
		}
		var nameParts = fieldName.replace('item_meta[', '').replace('[]', '').split(']');
		nameParts = nameParts.filter(function(n){ return n !== ''; });
		var field_id = nameParts[0];
		var isRepeating = false;

		if ( nameParts.length === 1 ) {
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

	function checkDependentField(selected, field_id, rec, parentField, reset){
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
			hidden_fields = [];
        }

		var isRepeat = maybeSetRowId( parentField );

		var len = rules.length;
		for ( var i = 0, l = len; i < l; i++ ) {
			if ( rules[i].FieldName === field_id ) {
				hideOrShowField(i, rules[i], field_id, selected, rec, parentField);
			} else {
				hideOrShowField(i, rules[i], field_id, selected, rec);
			}

			if ( i === ( len - 1 ) ) {
				hideFieldLater(rec);
				if ( isRepeat ) {
					addingRow = '';
				}
			}
		}
	}

	function maybeSetRowId( parentField ) {
		var isRepeat = false;
		if ( addingRow === '' && typeof parentField !== 'undefined' && parentField !== null ) {
			if ( parentField.length > 1 ) {
				parentField = parentField.eq(0);
			}
			isRepeat = parentField.closest('.frm_repeat_sec, .frm_repeat_inline, .frm_repeat_grid');
			if ( typeof isRepeat !== 'undefined' ) {
				addingRow = isRepeat.attr('id');
				isRepeat = true;
			} else {
				isRepeat = false;
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
                    this_opts.push(c);
                }
            }
        }
		return this_opts;
	}

	/**
	* Track whether fields should hide or show in show_fields variable
	*/
	function hideOrShowField(i, f, triggerFieldId, selected, rec, parentField){
		// Instantiate variables
		f.inputName = 'item_meta['+ f.FieldName +']';
		f.hiddenName = 'item_meta['+ f.HideField +']';
		f.containerID = 'frm_field_'+ f.FieldName +'_container';
		f.hideContainerID = 'frm_field_'+ f.HideField +'_container';
		f.hideBy = '#';
		var getRepeat = false;

		if ( typeof parentField !== 'undefined' && parentField !== null ) {
			parentField = maybeGetFirstElement( parentField );

			if ( typeof parentField.attr('name') === 'undefined' ) {
				return;
			}

			updateObjectForRepeatingSection( parentField, f );
		} else {
			getRepeat = true;
			parentField = jQuery('input[name^="'+ f.inputName +'"], textarea[name^="'+ f.inputName +'"], select[name^="'+ f.inputName +'"]');

			// If in repeating section
			if ( parentField.length < 1 ) {
				checkRepeatingFields( i, f, triggerFieldId, selected, rec );
				return;
			}

			parentField = maybeGetFirstElement( parentField );
		}

		setEmptyKeyInArray(f);

		maybeUpdateHideBy( f );

		selected = getEnteredValue( i, f, triggerFieldId, selected, rec, parentField, getRepeat );
		if ( selected === false ) {
			return;
		}

		updateShowFields( i, f, selected );

		adjustShowFieldsForRepeat(f, i);

		hideFieldNow(i, f, rec);
	}

	function maybeGetFirstElement( parentField ) {
        if ( parentField.length > 1 ) {
            parentField = parentField.eq(0);
        }
		return parentField;
	}

	function updateObjectForRepeatingSection( parentField, f ) {
        var container = parentField.closest('.frm_repeat_sec, .frm_repeat_inline, .frm_repeat_grid');
        if ( container.length ) {
            var repeatInput = container.find('.frm_field_'+ f.FieldName +'_container');
            f.containerID = repeatInput.attr('id');
            f.hideContainerID = f.containerID.replace(f.FieldName, f.HideField);
            f.hiddenName = f.inputName.replace('['+ f.FieldName +']', '['+ f.HideField +']');
        }
	}

	/**
	* If field in logic is repeating, loop through each repeating field
	*/
	function checkRepeatingFields( i, f, triggerFieldId, selected, rec ) {
		// Get class for repeating field
		var repeatingFieldClass = '.'+ f.containerID;
		if ( addingRow !== '' && addingRow != undefined ) {
			repeatingFieldClass = '#' + addingRow +' '+ repeatingFieldClass;
		}

		// Get all repeating field divs
		var repeatingFieldDivs = jQuery(repeatingFieldClass);
		if ( repeatingFieldDivs.length ) {
			var repeatingFields = repeatingFieldDivs.find('input, textarea, select');

			// If non-hidden fields exist in the repeating field divs
			if ( repeatingFields.length ) {
				if ( addingRow === '' || addingRow === undefined ) {
					var lastId = '';

					// Loop through each input/select/textarea in repeating fields
					repeatingFields.each(function(){
						var thisId = jQuery(this).closest('.frm_form_field').attr('id');
						if ( thisId != lastId ) { // don't trigger radio/checkbox multiple times
							hideOrShowField(i, f, f.FieldName, selected, rec, jQuery(this));
						}
						lastId = thisId;
					});
				} else {
					hideOrShowField(i, f, triggerFieldId, selected, rec, repeatingFields);
				}
			} else {
				setEmptyKeyInArray(f);
				show_fields[f.hideContainerID][i] = false;
				hideFieldNow(i, f, rec);
			}
		}
	}

	/**
	* Check if only the dependent field is in a repeating section
	*/
	function maybeUpdateHideBy( f ) {
		var hideContainer = document.getElementById(f.hideContainerID);
		if ( hideContainer === null ) {
		// it is a repeating section, use the class
			f.hideBy = '.';
		}
	}

	/**
	* Get the entered/selected value in the current field
	*/
	function getEnteredValue( i, f, triggerFieldId, selected, rec, parentField, getRepeat ) {
		if ( f.FieldName !== triggerFieldId || typeof selected === 'undefined' || selected === 'und' ) {
			if ( ( f.Type === 'radio' || f.Type === 'data-radio' ) && parentField.attr('type') === 'radio' ) {
				selected = jQuery('input[name="'+ f.inputName +'"]:checked').val();
				if ( typeof selected === 'undefined' ) {
					selected = '';
				}
			} else if ( f.Type === 'select' || f.Type === 'time' || f.Type === 'data-select' || ( f.Type !== 'checkbox' && f.Type !== 'data-checkbox' ) ) {
				selected = parentField.val();
			}
		}

		if ( typeof selected === 'undefined' ) {
			if ( parentField.length === 0 ) {
				return false; // the parent field is currently getting processed
			}
			selected = parentField.val();
		}

		if ( typeof selected === 'undefined' ) {
			// check for repeating/embedded field
			if ( getRepeat === true ) {
				var repeat = jQuery('.'+ f.containerID +' input, .'+ f.containerID +' select, .'+ f.containerID +' textarea');
				if ( repeat.length ) {
					repeat.each(function(){
						hideOrShowField(i, f, f.FieldName, selected, rec, jQuery(this));
					});
					return false;
				}
			}
			selected = '';
		}

		// get selected checkbox values
		var checkVals = [];
		if ( f.Type === 'checkbox' || f.Type === 'data-checkbox' ) {
			checkVals = getCheckedVal(f.containerID, f.inputName);

			if ( checkVals.length ) {
				selected = checkVals;
			}else{
				selected = '';
			}
		}

		return selected;
	}

	/**
	* Add values to the show_fields array
	*/
	function updateShowFields( i, f, selected ) {
		if ( selected === null || selected === '' || selected.length < 1 ) {
			show_fields[f.hideContainerID][i] = false;
		} else {
			show_fields[f.hideContainerID][i] = {'funcName':'getDataOpts', 'f':f, 'sel':selected};
		}

		if ( f.Type === 'checkbox' || (f.Type === 'data-checkbox' && typeof f.LinkedField === 'undefined') ) {
			show_fields[f.hideContainerID][i] = false;

			var match = false;
			if ( selected !== '') {
				if ( f.Condition === '!=' ) {
					show_fields[f.hideContainerID][i] = true;
				}
				for ( var b = 0; b<selected.length; b++ ) {
					match = operators(f.Condition, f.Value, selected[b]);
					if ( f.Condition === '!=' ) {
						if ( show_fields[f.hideContainerID][i] === true && match === false ) {
							show_fields[f.hideContainerID][i] = false;
						}
					} else if(show_fields[f.hideContainerID][i] === false && match){
						show_fields[f.hideContainerID][i] = true;
					}
				}
			} else {
				match = operators(f.Condition, f.Value, '');
				if(show_fields[f.hideContainerID][i] === false && match){
					show_fields[f.hideContainerID][i] = true;
				}
			}
		} else if ( typeof f.LinkedField !== 'undefined' && f.Type.indexOf('data-') === 0 ) {
			if ( typeof f.DataType === 'undefined' || f.DataType === 'data' ) {
				if ( selected === '' ) {
					hideAndClearDynamicField( f.hideContainerID, f.hideBy, f.HideField, 'hide' );
				} else if ( f.Type === 'data-radio' ) {
					if ( typeof f.DataType === 'undefined' ) {
						show_fields[f.hideContainerID][i] = operators(f.Condition, f.Value, selected);
					} else {
						show_fields[f.hideContainerID][i] = {'funcName':'getData','f':f,'sel':selected};
					}
				} else if ( f.Type === 'data-checkbox' || ( f.Type === 'data-select' && isNotEmptyArray( selected ) ) ) {
					hideAndClearDynamicField( f.hideContainerID, f.hideBy, f.HideField, 'show' );
					show_fields[f.hideContainerID][i] = true;
					getData(f, selected, 1);
				} else if ( f.Type === 'data-select' ) {
					show_fields[f.hideContainerID][i] = {'funcName':'getData','f':f,'sel':selected};
				}
			}
		}else if ( typeof f.Value === 'undefined' && f.Type.indexOf('data') === 0 ) {
			if ( selected === '' ) {
				f.Value = '1';
			} else {
				f.Value = selected;
			}
			show_fields[f.hideContainerID][i] = operators(f.Condition, f.Value, selected);
			f.Value = undefined;
		}else{
			show_fields[f.hideContainerID][i] = operators(f.Condition, f.Value, selected);
		}
	}

	function setEmptyKeyInArray(f) {
		if ( typeof show_fields[f.hideContainerID] === 'undefined' ) {
			show_fields[f.hideContainerID] = [];
		}
	}

	/**
	* If a dependent field is in a repeating section, adjust the show_fields array so it includes every repeating field individually
	*/
	function adjustShowFieldsForRepeat(f, i){
		var hideFieldRepeatContainer = jQuery( '.' + f.hideContainerID ).closest('.frm_repeat_sec, .frm_repeat_inline, .frm_repeat_grid');

		if ( hideFieldRepeatContainer.length ) {
			//f.hideContainerID is in repeating section
			var result = show_fields[f.hideContainerID][i];
			delete show_fields[f.hideContainerID];

			var fCopy = f;
			var originalId = f.hideContainerID;
			var repeatId;
			jQuery.each(hideFieldRepeatContainer, function(key,val){
				repeatId = '-' + val.id.replace( 'frm_section_', '' ) + '_container';
				repeatId = originalId.replace( '_container', repeatId );
				fCopy.hideContainerID = repeatId;

				setEmptyKeyInArray(fCopy);
				show_fields[repeatId][i] = result;
			});
		}
	}

	function hideAndClearDynamicField(hideContainer, hideBy, field_id, hide){
		if ( jQuery.inArray(hideContainer, hidden_fields) === -1 ) {
			hidden_fields[ field_id ] = hideContainer;
			if(hideBy === '.'){
				hideContainer = jQuery('.'+hideContainer);
			}else{
				hideContainer = jQuery(document.getElementById(hideContainer));
			}
			if ( hide === 'hide' ) {
				hideContainer.hide();
			}
			hideContainer.find('.frm_data_field_container').empty();
		}
    }

	function hideAndClearField( container, f ) {
		container.hide();
		if ( jQuery.inArray(container.attr('id'), hidden_fields) === -1 ) {
			var field_id = f.HideField;
			hidden_fields[ field_id ] = container.attr('id');

			var inputs = getInputsInContainer( container );
			if ( inputs.length ){
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
		}
	}

	function getInputsInContainer( container ) {
		return container.find('select[name^="item_meta"], textarea[name^="item_meta"], input[name^="item_meta"]:not([type=hidden])');
	}

	function showFieldAndSetValue( container, f ) {
		var inputs = getInputsInContainer( container );
		setDefaultValue( inputs );

		if ( inputs.length > 1 ) {
			for ( var i = 0; i < inputs.length; i++ ) {
				doCalcForSingleField( f.HideField, jQuery( inputs[i] ) );
			}
		} else {
			doCalcForSingleField( f.HideField, inputs );
		}

		container.show();
	}

	function setDefaultValue( input ) {
		var inputLenth = input.length;

		// If the field already has a value (i.e. when form is loaded for editing an entry), don't get the default value
		if ( input.is(':checkbox, :radio') ) {
			if ( input.is(':checked') ) {
				return;
			}
		} else if ( input.val() ) {
			return;
		}

		if ( inputLenth ) {
			for ( var i = 0, l = inputLenth; i < l; i++ ) {
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

	function triggerChange( input, fieldKey ) {
		if ( typeof fieldKey === 'undefined' ) {
			fieldKey = 'dependent';
		}

		if ( input.length > 1 ) {
			input = input.eq(0);
		}

		input.trigger({ type:'change', selfTriggered:true, frmTriggered:fieldKey });
	}

	function hideFieldNow(i, f, rec){
		if ( f.MatchType === 'all' || show_fields[f.hideContainerID][i] === false ) {
			hide_later.push({
				'result':show_fields[f.hideContainerID][i], 'show':f.Show,
				'match':f.MatchType, 'FieldName':f.FieldName, 'HideField':f.HideField,
				'hideContainerID':f.hideContainerID, 'hideBy':f.hideBy
			});
			return;
		}

		var display = 'none';
		if ( f.Show === 'show' ) {
			if ( show_fields[f.hideContainerID][i] !== true ) {
				showField(show_fields[f.hideContainerID][i], f.FieldName, rec);
				return;
			}
			display = '';
		}

		var hideClass;
		if(f.hideBy === '.'){
			hideClass = jQuery('.'+f.hideContainerID);
		}else{
			hideClass = jQuery( document.getElementById(f.hideContainerID) );
		}

		if(hideClass.length){
			if ( display === 'none' ) {
				hideAndClearField( hideClass, f );
			} else {
				showFieldAndSetValue( hideClass, f );
			}
		}
	}

	function hideFieldLater(rec){
		jQuery.each(hide_later, function(hkey,hvalue){
			delete hide_later[hkey];
            if ( typeof hvalue === 'undefined' || typeof hvalue.result === 'undefined' ) {
                return;
            }

			var container = jQuery(hvalue.hideBy + hvalue.hideContainerID);
            var hideField = hvalue.show;
            if ( container.length ) {
				if ( ( hvalue.match === 'any' && (jQuery.inArray(true, show_fields[hvalue.hideContainerID]) === -1) ) ||
					( hvalue.match === 'all' && (jQuery.inArray(false, show_fields[hvalue.hideContainerID]) > -1) ) ) {
                    if ( hvalue.show === 'show' ) {
                        hideField = 'hide';
                    } else {
                        hideField = 'show';
                    }
                }

                if ( hideField === 'show' ) {
					showFieldAndSetValue( container, hvalue );
					if ( typeof hvalue.result !== false && typeof hvalue.result !== true ) {
						showField( hvalue.result, hvalue.FieldName, rec );
					}
                } else {
					hideAndClearField(container, hvalue);
                }
            }
		});
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
					return 0;
				}
				return d.indexOf(c) != -1;
			},
			'not LIKE': function(c,d){
				if(!d){
					/* If no value, then assume no match */
					return 1;
				}
				return d.indexOf(c) == -1;
			}
		};
		return theOperators[op](a, b);
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
		var cont = jQuery(fcont).find('.frm_data_field_container');
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

		var hiddenInput = jQuery('input[name^="'+ f.hiddenName +'"], select[name^="'+ f.hiddenName +'"]:not(":disabled"), textarea[name^="'+ f.hiddenName +'"]');

		var prev = [];
		hiddenInput.each(function(){
            if ( this.type === 'radio' || this.type === 'checkbox' ) {
                if ( this.checked === true ) {
                    prev.push(jQuery(this).val());
                }
            } else {
                prev.push(jQuery(this).val());
            }
		});

        if(f.DataType == 'select'){
			if((rec == 'stop' || jQuery('#'+ f.hideContainerID +' .frm-loading-img').length) && (jQuery.inArray(f.HideField, frm_checked_dep) > -1)){
				return;
			}
		}
		
		if(prev.length === 0){
            prev = '';
        }

		frm_checked_dep.push(f.HideField);

        var fcont = document.getElementById(f.hideContainerID);
		//don't get values for fields that are to remain hidden on the page
		var $dataField = jQuery(fcont).find('.frm_data_field_container');
        if($dataField.length === 0 && hiddenInput.length ){
		    checkDependentField(prev, f.HideField, 'stop', hiddenInput);
            return false;
		}

		if ( f.Value !== '' ) {
			var match = operators(f.Condition, f.Value, selected);
			if ( !match ) {
				fcont.style.display = 'none';
				$dataField.html('');
				checkDependentField('', f.HideField, 'stop', hiddenInput);
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
                action:'frm_fields_ajax_data_options', hide_field:field_id,
                entry_id:selected, selected_field_id:f.LinkedField, field_id:f.HideField,
                hide_id:f.hideContainerID, nonce:frm_js.nonce
            },
			success:function(html){
				$dataField.html(html);
				var parentField = $dataField.find('select, input, textarea');
				var val = 1;
				if ( parentField.attr('type') == 'hidden' ) {
					val = parentField.val();
				}

				if ( html === '' || val === '' ) {
					fcont.style.display = 'none';
					prev = '';
				}else if(f.MatchType != 'all'){
					fcont.style.display = '';
				}

				if(html !== '' && prev !== ''){
					if(!jQuery.isArray(prev)){
						var new_prev = [];
						new_prev.push(prev);
						prev = new_prev;
					}

					//select options that were selected previously
					jQuery.each(prev, function(ckey,cval){
                        if ( typeof(cval) === 'undefined' || cval === '' ) {
                            return;
                        }
						if ( dataType == 'checkbox' || dataType == 'radio' ) {
                            if ( parentField.length > 1 ) {
                                parentField.filter('[value="' + cval+ '"]').attr('checked','checked');
                            } else if ( parentField.val() == cval ){
                                parentField.attr('checked','checked');
                            }
						} else if ( dataType == 'select' ) {
							var selOpt = parentField.children('option[value="'+ cval +'"]');
							if(selOpt.length){
								selOpt.prop('selected', true);
							}else{
                                //remove options that no longer exist
								prev.splice(ckey, 1);
							}
						}else{
							parentField.val(cval);
						}
					});
				}

				if(parentField.hasClass('frm_chzn') && jQuery().chosen){
					jQuery('.frm_chzn').chosen({allow_single_deselect:true});
				}

				triggerChange( parentField );
			}
		});
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
			if ( fieldIsConditionallyHidden( all_calcs, triggerField, keys[i] ) ) {
				continue;
			}

			doSingleCalculation( all_calcs, keys[i], vals, triggerField );
		}
	}

	/**
	* If field is hidden with conditional logic, don't do the calc
	*/
	function fieldIsConditionallyHidden( all_calcs, triggerField, field_key ) {
		var totalFieldId = all_calcs.calc[ field_key ].field_id;
		var t = document.getElementById( 'frm_field_' + totalFieldId + '_container' );
		if ( t !== null ) {
			if ( t.offsetHeight === 0 ) {
				// Conditionally hidden field
				return true;
			} else {
				// Regular, visible field
				return false;
			}
		}

		// Check if we're dealing with a conditionally hidden repeating field
		var container = triggerField.closest('.frm_repeat_sec, .frm_repeat_inline, .frm_repeat_grid');
		if ( container.length ) {
			var idPart = container[0].id.replace( 'frm_section_', '' );
			var totalField = document.getElementById( 'frm_field_' + totalFieldId + '-' + idPart + '_container' );
			if ( totalField !== null && totalField.offsetHeight === 0 ) {
				// Conditionally hidden field (repeating)
				return true;
			} else {
				// Regular, visible field or hidden field (repeating)
				return false;
			}
		} else {
			// Hidden field
			return false;
		}
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

	function doCalcForSingleField( field_id, triggerField ) {
		if ( typeof __FRMCALC === 'undefined' ) {
			// there are no calculations on this page
			return;
		}
		var all_calcs = __FRMCALC;
		var field_key = all_calcs.fieldsWithCalc[ field_id ];
		if ( typeof field_key === 'undefined' ) {
			// this field has no calculation
			return;
		}

		var vals = [];
		doSingleCalculation( all_calcs, field_key, vals, triggerField );
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
                var d = jQuery.datepicker.parseDate(all_calcs.date, thisVal);
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

	function validateForm( action, object ) {
		var errors = [];

		// Make sure required text field is filled in
		var requiredFields = jQuery(object).find('.frm_required_field input, .frm_required_field select, .frm_required_field textarea');
		if ( requiredFields.length ) {
			for ( var r = 0, rl = requiredFields.length; r < rl; r++ ) {
				// this won't work with radio/checkbox
				errors = checkRequiredField( requiredFields[r], errors );
			}
		}

		// Make sure required email field is filled in
		var emailFields = jQuery(object).find('input[type=email]');
		if ( emailFields.length ) {
			for ( var e = 0, el = emailFields.length; e < el; e++ ) {
				errors = checkEmailField( emailFields[e], errors, emailFields );
			}
		}

		var numberFields = jQuery(object).find('input[type=number]');
		if ( numberFields.length ) {
			for ( var n = 0, nl = numberFields.length; n < nl; n++ ) {
				errors = checkNumberField( numberFields[n], errors );
			}
		}

		return errors;
	}

	function validateField( fieldId, field ) {
		var errors = [];
		var $fieldCont = jQuery(field).closest('.frm_form_field');
		if ( $fieldCont.hasClass('.frm_required_field') ) {
			errors = checkRequiredField( field, errors );
		}

		if ( errors.length < 1 ) {
			if ( field.type == 'email' ) {
				var emailFields = jQuery(field).closest('form').find('input[type=email]');
				errors = checkEmailField( field, errors, emailFields );
			}
		}

		if (  Object.keys(errors).length > 0 ) {
			for ( var key in errors ) {
				addFieldError( $fieldCont, key, errors );
			}
		} else {
			removeFieldError( $fieldCont );
		}
	}

	function checkRequiredField( field, errors, rFieldID ) {
		if ( jQuery(field).val() === '' ) {
			rFieldID = getFieldId( field, true );
			errors[ rFieldID ] = '';
		}
		return errors;
	}

	function checkEmailField( field, errors, emailFields ) {
		var emailAddress = field.value;
		var fieldID = getFieldId( field, true );
		var isConf = (fieldID.indexOf('conf_') === 0);
		if ( emailAddress !== '' || isConf ) {
			var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
			if ( re.test( emailAddress ) === false ) {
				errors[ fieldID ] = '';
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
		if ( isNaN(number / 1) !== false ) {
			var fieldID = getFieldId( field, true );
			errors[ fieldID ] = '';
		}
		return errors;
	}

	function checkDateField( field, errors ) {
		return errors;
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
						$fieldCont = jQuery(object).find(jQuery(document.getElementById('frm_field_'+key+'_container')));

						if ( $fieldCont.length ) {
							if ( ! $fieldCont.is(':visible') ) {
								var inCollapsedSection = $fieldCont.closest('.frm_toggle_container');
								if ( inCollapsedSection.length ) {
									inCollapsedSection.prev('.frm_trigger').click();
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
                    	data.setCell(row, col,'<a href="'+ entry.deleteLink +'" class="frm_delete_link" onclick="return confirm('+ opts.options.confirm +')">'+ opts.options.delete_link +'</a>');
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
					var fieldID = this.name.replace('item_meta[', '').split(']')[2].replace('[', '');
					doCalculation(fieldID, jQuery(this));
				}
			});
		});

		return false;
	}

	function addRow(){
		/*jshint validthis:true */
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
                var fieldID;
                var reset = 'reset';
				addingRow = item.attr('id');

                // hide fields with conditional logic
                jQuery(html).find('input, select, textarea').each(function(){
					if ( this.type != 'file' ) {
						fieldID = this.name.replace('item_meta[', '').split(']')[2].replace('[', '');
						if ( jQuery.inArray(fieldID, checked ) == -1 ) {
							checked.push(fieldID);
							checkDependentField('und', fieldID, null, jQuery(this), reset);
							doCalculation(fieldID, jQuery(this));
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
							checkDependentField('und', r.logic.check[f], null, null, reset);
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
			}
		});


		return false;
	}

	/* General Helpers */
	function fadeOut($remove){
		$remove.fadeOut('slow', function(){
			$remove.remove();
		});
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

			jQuery('a[data-frmtoggle]').click(toggleDiv);

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
		},

		submitForm: function(e){
			e.preventDefault();
			if(jQuery(this).find('.wp-editor-wrap').length && typeof(tinyMCE) != 'undefined'){
				tinyMCE.triggerSave();
			}

			var object = this;
			action = jQuery(object).find('input[name="frm_action"]').val();
			jsErrors = [];
			frmFrontForm.getAjaxFormErrors( object );

			if ( Object.keys(jsErrors).length === 0 ) {
				getFormErrors( object, action );
			} else {
				// Remove all previous errors
				jQuery('.form-field').removeClass('frm_blank_field');
				jQuery('.form-field .frm_error').replaceWith('');

				for ( var key in jsErrors ) {
					var $fieldCont = jQuery(object).find(jQuery('#frm_field_'+key+'_container'));
					addFieldError( $fieldCont, key, jsErrors );
				}
			}
		},

		getAjaxFormErrors: function( object ) {
			if ( typeof frmThemeOverride_jsErrors == 'function' ) {
				jsErrors = frmThemeOverride_jsErrors( action, object );
			} else {
				//jsErrors = validateForm( action, object );
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
				newPos = jQuery(object).find(document.getElementById('frm_field_'+id+'_container')).offset().top;
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
				checkDependentField('und', ids[i], null, null, reset);
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

function frmEditEntry(entry_id,prefix,post_id,form_id,cancel,hclass){
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

function frmDeleteEntry(entry_id,prefix){	
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
