function frmFrontFormJS(){
	'use strict';
	var show_fields = [];
	var hide_later = [];
    var frm_checked_dep = [];
    var frm_checked_logic = [];

	function setNextPage(e){
		/*jshint validthis:true */
		var $thisObj = jQuery(this);
		var thisType = $thisObj.attr('type');
		if ( thisType != 'submit' ) {
			e.preventDefault();
		}

		var f = $thisObj.parents('form:first');
		var v = '';
		var d = '';
		var thisName = this.name;
		if ( thisName == 'frm_prev_page' || this.className.indexOf('frm_prev_page') !== -1 ) {
			v = jQuery(f).find('.frm_next_page').attr('id').replace('frm_next_p_', '');
		} else if ( thisName == 'frm_save_draft' || this.className.indexOf('frm_save_draft') !== -1 ) {
			d = 1;
		}

		jQuery('.frm_next_page').val(v);
		jQuery('.frm_saving_draft').val(d);

		if ( thisType != 'submit' ) {
			f.trigger('submit');
		}
	}
	
	function toggleSection(){
		/*jshint validthis:true */
		jQuery(this).next('.frm_toggle_container').slideToggle('fast');
		jQuery(this).toggleClass('active').children('.ui-icon-triangle-1-e, .ui-icon-triangle-1-s')
			.toggleClass('ui-icon-triangle-1-s ui-icon-triangle-1-e');
	}
    
    // Show "Other" text box when Other item is checked/selected
    // Hide and clear text box when item is unchecked/unselected
	function showOtherText(){
        /*jshint validthis:true */
        var type = this.type;
        var other = false;
        var select = false;

        // Dropdowns
        if ( type == 'select-one' ) {
            select = true;
            var curOpt = this.options[this.selectedIndex];
            if ( curOpt.className == 'frm_other_trigger' ) {
                other = true;
            }
        } else if ( type == 'select-multiple' ) {
            select = true;
            var allOpts = this.options;
            other = false;
            for ( var i = 0; i < allOpts.length; i++ ) {
                if ( allOpts[i].className == 'frm_other_trigger' ) {
                    if ( allOpts[i].selected ) {
                        other = true;
                        break;
                    }
                }
            }
        }
        if ( select ) {
            var otherField = this.parentNode.getElementsByClassName('frm_other_input');
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

        // Radio
        } else if ( type == 'radio' ) {
            jQuery(this).closest('.frm_radio').children('.frm_other_input').removeClass('frm_pos_none');
            jQuery(this).closest('.frm_radio').siblings().children('.frm_other_input')
            .addClass('frm_pos_none').val('');
            
        // Checkboxes
        } else if ( type == 'checkbox' ) {
            if ( this.checked ) {
                jQuery(this).closest('.frm_checkbox').children('.frm_other_input').removeClass('frm_pos_none'); 
            } else {
                jQuery(this).closest('.frm_checkbox').children('.frm_other_input').addClass('frm_pos_none').val('');
            }
        }
	}

	function maybeCheckDependent(e){
		/*jshint validthis:true */
		var nameParts = this.name.replace('item_meta[', '').split(']');
		var field_id = nameParts[0];
		if ( ! field_id ) {
			return;
		}

		if ( jQuery('input[name="item_meta['+ field_id +'][form]"]').length ) {
			// this is a repeatable section with name: item_meta[370][0][414]
			field_id = nameParts[2].replace('[', '');
		}

		checkDependentField('und', field_id, null, jQuery(this));
		doCalculation(e, field_id);
	}
	
	function checkDependentField(selected, field_id, rec, parentField, reset){
		if(typeof(__FRMRULES) == 'undefined'){
			return;
		}

		var all_rules=__FRMRULES;
		var rules = all_rules[field_id];
		if ( typeof rules =='undefined'){
			return;
		}

		if ( typeof(rec) == 'undefined' || rec === null ) {
			//stop recursion?
			rec = 'go';
		}

        if ( reset != 'persist' ) {
            show_fields = []; // reset this variable after each click
            frm_checked_logic = [];
        }
		var this_opts = [];
		for ( var i = 0, l = rules.length; i < l; i++ ) {
            var rule = rules[i];
            if ( typeof rule != 'undefined' ) {
                for ( var j = 0, rcl = rule.Conditions.length; j < rcl; j++ ) {
					var c = rule.Conditions[j];
					c.HideField = rule.Setting.FieldName;
					c.MatchType = rule.MatchType;
					c.Show = rule.Show;
                    this_opts.push(c);
                }
            }
        }

		var len = this_opts.length;
		for ( i = 0, l = len; i < l; i++ ) {
            if ( this_opts[i].FieldName == field_id ) {
			    hideOrShowField(i, this_opts[i], field_id, selected, rec, parentField);
            } else {
                hideOrShowField(i, this_opts[i], field_id, selected, rec);
            }
			
			if ( i == (len-1) ) {
				hideFieldLater(rec);
			}
		}
	}

	function hideOrShowField(i, f, field_id, selected, rec, parentField){
		if ( typeof show_fields[f.HideField] == 'undefined' ) { 
			show_fields[f.HideField] = [];
		}

        f.inputName = 'item_meta['+ f.FieldName +']';
        f.hiddenName = 'item_meta['+ f.HideField +']';
        f.containerID = 'frm_field_'+ f.FieldName +'_container';
        f.hideContainerID = 'frm_field_'+ f.HideField +'_container';
        var getRepeat = false;

        if ( typeof parentField !== 'undefined' && parentField !== null ) {
            if ( parentField.length > 1 ) {
                parentField = parentField.eq(0);
            }

            if ( typeof parentField.attr('name') === 'undefined' ) {
                return;
            }
            // Accommodate for "other" options
            f.inputName = parentField.attr('name').replace( '[other]', '' ).replace('[]', '');

            var container = parentField.closest('.frm_repeat_sec');
            if ( container.length ) {
                var repeatInput = container.children('.frm_field_'+ f.FieldName +'_container');
                f.containerID = repeatInput.attr('id');
                f.hideContainerID = f.containerID.replace(f.FieldName, f.HideField);
                f.hiddenName = f.inputName.replace('['+ f.FieldName +']', '['+ f.HideField +']');
            }

            if ( jQuery.inArray(f.inputName +'-'+f.hiddenName, frm_checked_logic) > -1 ) {
                return;
            } else {
                frm_checked_logic.push(f.inputName +'-'+ f.hiddenName);
            }
        } else {
            if ( jQuery.inArray(f.inputName +'-'+f.hiddenName, frm_checked_logic) > -1 ) {
                return;
            } else {
                frm_checked_logic.push(f.inputName +'-'+ f.hiddenName);
            }

            getRepeat = true;
            parentField = jQuery('input[name^="'+ f.inputName +'"], textarea[name^="'+ f.inputName +'"], select[name^="'+ f.inputName +'"]');
            if ( parentField.length > 1 ) {
                parentField = parentField.eq(0);
            }
        }

		if ( f.FieldName != field_id || typeof selected == 'undefined' || selected == 'und' ) {
			if ( (f.Type == 'radio' || f.Type == 'data-radio') && parentField.attr('type') == 'radio') {
				selected = jQuery('input[name="'+ f.inputName +'"]:checked').val();
                if ( typeof selected == 'undefined' ) {
                    selected = '';
                }
			} else if ( f.Type == 'select' || f.Type == 'time' || f.Type == 'data-select' || (f.Type != 'checkbox' && f.Type != 'data-checkbox')) {
				selected = parentField.val();
			}
		}

		if ( typeof selected == 'undefined' ) {
			selected = parentField.val();
        }

		if ( typeof selected == 'undefined' ) {
            // check for repeating/embedded field
            if ( getRepeat === true ) {
                var repeat = jQuery('.'+ f.containerID +' input, .'+ f.containerID +' select, .'+ f.containerID +' textarea');
                if ( repeat.length ) {
                    repeat.each(function(){
                        hideOrShowField(i, f, f.FieldName, selected, rec, jQuery(this));
                    });
                    return;
                }
            }
			selected = '';
		}

        // get selected checkbox values
        var checkVals = [];
        if ( f.Type == 'checkbox' || f.Type == 'data-checkbox' ) {
            checkVals = getCheckedVal(f.containerID, f.inputName);

            if ( checkVals.length ) {
                selected = checkVals;
            }else{
                selected = '';
            }
        }

		if ( selected === '' ) {
			show_fields[f.HideField][i] = false;
		} else {
			show_fields[f.HideField][i] = {'funcName':'getDataOpts', 'f':f, 'sel':selected};
		}

        if ( f.Type == 'checkbox' || (f.Type == 'data-checkbox' && typeof(f.LinkedField) == 'undefined') ) {
            show_fields[f.HideField][i] = false;

            var match = false;
            if ( selected !== '') {
                if ( f.Condition == '!=' ) {
                    show_fields[f.HideField][i] = true;
                }
                for ( var b = 0; b<selected.length; b++ ) {
                    match = operators(f.Condition, f.Value, selected[b]);
                    if ( f.Condition == '!=' ) {
                        if ( show_fields[f.HideField][i] === true && match === false ) {
                            show_fields[f.HideField][i] = false;
                        }
                    } else if(show_fields[f.HideField][i] === false && match){
                        show_fields[f.HideField][i] = true;
                    }
                }
            } else {
                match = operators(f.Condition, f.Value, '');
                if(show_fields[f.HideField][i] === false && match){
                    show_fields[f.HideField][i] = true;
                }
            }
        } else if ( typeof f.LinkedField != 'undefined' && f.Type.indexOf('data-') === 0 ) {
			if ( typeof(f.DataType) == 'undefined' || f.DataType === 'data' ) {
                if ( selected === '' ) {
                    hideAndClearField(f.hideContainerID);
    			} else if ( f.Type == 'data-radio' ) {
                    if ( typeof f.DataType == 'undefined' ) {
                        show_fields[f.HideField][i] = operators(f.Condition, f.Value, selected);
                    } else {
                        show_fields[f.HideField][i] = {'funcName':'getData','f':f,'sel':selected};
                    }
                } else if ( f.Type == 'data-checkbox' || ( f.Type == 'data-select' && jQuery.isArray(selected) ) ) {
                    hideAndClearField(f.hideContainerID);
    				show_fields[f.HideField][i] = true;
    				getData(f, selected, 1);
                } else if ( f.Type == 'data-select' ) {
                    show_fields[f.HideField][i] = {'funcName':'getData','f':f,'sel':selected};
                }
            }
        }else if ( typeof(f.Value)=='undefined' && f.Type.indexOf('data') === 0 ) {
			if ( selected === '' ) {
				f.Value = '1';
			} else {
				f.Value = selected;
			}
			show_fields[f.HideField][i] = operators(f.Condition, f.Value, selected);
			f.Value = undefined;
		}else{
			show_fields[f.HideField][i] = operators(f.Condition, f.Value, selected);
        }

		hideFieldNow(i, f, rec);
	}

    function hideAndClearField(hideContainer){
        hideContainer = jQuery(document.getElementById(hideContainer));
		hideContainer.fadeOut('slow');
		hideContainer.find('.frm_data_field_container').empty();
    }

	function hideFieldNow(i, f, rec){
		if ( f.MatchType == 'all' || show_fields[f.HideField][i] === false ) {
			hide_later.push({
				'result':show_fields[f.HideField][i], 'show':f.Show,
                'match':f.MatchType, 'fname':f.FieldName, 'fkey':f.HideField,
                'hideContainerID':f.hideContainerID
			});
			return;
		}

		if ( f.Show == 'show' ) {
			if ( show_fields[f.HideField][i] !== true ) {
				showField(show_fields[f.HideField][i], f.FieldName, rec);
			}else{
				var hideMe = document.getElementById(f.hideContainerID);
				if ( hideMe !== null ) {
					hideMe.style.display = '';
				}
			}
		}else{
			document.getElementById(f.hideContainerID).style.display = 'none';
		}
	}

	function hideFieldLater(rec){
		jQuery.each(hide_later, function(hkey,hvalue){
            if ( typeof hvalue == 'undefined' || typeof hvalue.result == 'undefined' ) {
                return;
            }

            var container = document.getElementById(hvalue.hideContainerID);
            var hideField = hvalue.show;
            if ( container !== null ) {
                if ( ( hvalue.match == 'any' && (jQuery.inArray(true, show_fields[hvalue.fkey]) == -1) ) ||
                    ( hvalue.match == 'all' && (jQuery.inArray(false, show_fields[hvalue.fkey]) > -1) ) ) {
                    if ( hvalue.show == 'show' ) {
                        hideField = 'hide';
                    } else {
                        hideField = 'show';
                    }
                }

                if ( hideField == 'show' ) {
                    container.style.display = '';
                } else {
                    jQuery(container).filter(':hidden').hide();
                    container.style.display = 'none';
                }

                if ( typeof hvalue.result !== false && typeof hvalue.result !== true ) {
                    showField(hvalue.result,hvalue.fname,rec);
                }
            }

			delete hide_later[hkey];
		});
	}

	function operators(op, a, b){
		if(typeof(b) == 'undefined'){
			b='';
		}
		if(jQuery.isArray(b) && jQuery.inArray(a,b) > -1){
			b = a;
		}
		if(String(a).search(/^\s*(\+|-)?((\d+(\.\d+)?)|(\.\d+))\s*$/) != -1){
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
				if ( html !== '' ) {
					fcont.style.display = '';
				}
				
				if ( append ) {
					cont.append(html);
				} else {
					cont.html(html);
                    var parentField = cont.children('input');
					var val = parentField.val();
					if(html === '' || val === ''){
						fcont.style.display = 'none';
					}
					checkDependentField(selected, f.HideField, null, parentField);
				}
				return true;
			}
		});
	}

	function getDataOpts(f,selected,field_id,rec) {
		//don't check the same field twice when more than a 2-level dependency, and parent is not on this page
		if(rec == 'stop' && (jQuery.inArray(f.HideField, frm_checked_dep) > -1) && f.parentField.attr('type') == 'hidden'){
			return;
		}

		var hiddenInput = jQuery('input[name^="'+ f.hiddenName +'"], select[name^="'+ f.hiddenName +'"], textarea[name^="'+ f.hiddenName +'"]');

		var prev = [];
		hiddenInput.each(function(){
            if ( this.type == 'radio' || this.type == 'checkbox' ) {
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
				if(html === ''){
					fcont.style.display = 'none';
					prev = '';
				}else if(f.MatchType != 'all'){
					fcont.style.display = '';
				}
				
				$dataField.html(html);
                var parentField = $dataField.find('select, input, textarea');

				if(html !== '' && prev !== ''){
					if(!jQuery.isArray(prev)){
						var new_prev = [];
						new_prev.push(prev);
						prev = new_prev;
					}

					//select options that were selected previously
					jQuery.each(prev, function(ckey,cval){
                        if ( typeof(cval) == 'undefined' || cval === '' ) {
                            return;
                        }
						if ( dataType == 'checkbox' || dataType == 'radio' ) {
                            if ( parentField.length > 1 ) {
                                parentField.filter('[value="' + cval+ '"]').attr('checked','checked');
                            } else if ( parentField.val() == cval ){
                                parentField.attr('checked','checked');
                            }
						} else if ( dataType == 'select' ) {
							var selOpt = parentField.children('option[value='+ cval +']');
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

				checkDependentField(prev, f.HideField, 'stop', parentField);
			}
		});
	}

	function doCalculation(e, field_id){
		if ( typeof __FRMCALC == 'undefined' ) {
			// there are no calculations on this page
			return;
		}

		var all_calcs = __FRMCALC;
		var calc = all_calcs.fields[field_id];
		if ( typeof calc == 'undefined' ) {
			// this field is not used in a calculation
			return;
		}

		var keys = calc.total;
		if ( e.frmTriggered && e.frmTriggered == field_id ) {
			return false;
		}

		var vals = [];
		var len = keys.length;
        var fCount = 0;

		// loop through each calculation this field is used in
		for ( var i = 0, l = len; i < l; i++ ) {
			var thisCalc = all_calcs.calc[keys[i]];
			var thisFullCalc = thisCalc.calc;

			// loop through the fields in this calculation
			fCount = thisCalc.fields.length;
			for ( var f = 0, c = fCount; f < c; f++ ) {
				var thisFieldId = thisCalc.fields[f];
				var thisField = all_calcs.fields[thisFieldId];
				var thisFieldCall = 'input'+ all_calcs.fieldKeys[thisFieldId];

				if ( thisField.type == 'checkbox' || thisField.type == 'select' ) {
					thisFieldCall = thisFieldCall +':checked,select'+ all_calcs.fieldKeys[thisFieldId] +' option:selected,'+ thisFieldCall+'[type=hidden]';
				} else if ( thisField.type == 'radio' || thisField.type == 'scale' ) {
					thisFieldCall = thisFieldCall +':checked,'+ thisFieldCall +'[type=hidden]';
				} else if ( thisField.type == 'textarea' ) {
                    thisFieldCall = thisFieldCall + ',textarea'+ all_calcs.fieldKeys[thisFieldId];
				}

                vals[thisFieldId] = getCalcFieldId(thisFieldCall, thisFieldId, thisField, all_calcs, vals);

				if ( typeof vals[thisFieldId] === 'undefined' ) {
					vals[thisFieldId] = 0;
				}

				thisFullCalc = thisFullCalc.replace('['+thisFieldId+']', vals[thisFieldId]);
			}

			// allow .toFixed for reverse compatability
			if ( thisFullCalc.indexOf(').toFixed(') ) {
				var calcParts = thisFullCalc.split(').toFixed(');
				if ( isNumeric(calcParts[1]) ) {
					thisFullCalc = 'parseFloat('+ thisFullCalc +')';
				}
			}

			var total = parseFloat(eval(thisFullCalc));
			if ( typeof total === 'undefined' ) {
				total = 0;
			}

			jQuery(document.getElementById('field_'+ keys[i])).val(total).trigger({
				type:'change', frmTriggered:keys[i], selfTriggered:true
			});
		}
	}

    function getCalcFieldId(thisFieldCall, thisFieldId, thisField, all_calcs, vals){
        if ( typeof vals[thisFieldId] !== 'undefined' && vals[thisFieldId] !== 0 ) {
            return vals[thisFieldId];
        }

        jQuery(thisFieldCall).each(function(){
            if ( typeof vals[thisFieldId] === 'undefined' ) {
                vals[thisFieldId] = 0;
            }
            var thisVal = jQuery(this).val();

            if ( thisField.type == 'date' ) {
                d = jQuery.datepicker.parseDate(all_calcs.date, thisVal);
                if ( d !== null ) {
                    vals[thisFieldId] = Math.ceil(d/(1000*60*60*24));
                }
            }
            var n = thisVal;
            if ( n !== '' ){
                n = parseFloat(n.replace(/,/g,'').match(/-?[\d\.]+$/));
            }

			if ( typeof n === 'undefined' ) {
				n = 0;
			}
			vals[thisFieldId] += n;
		});

        return vals[thisFieldId];
    }

	function getFormErrors(object){
		jQuery(object).find('input[type="submit"], input[type="button"]').attr('disabled','disabled');
		jQuery(object).find('.frm_ajax_loading').addClass('frm_loading_now');

		var jump = '';
		var newPos = 0;
		var cOff = 0;

		jQuery.ajax({
			type:'POST',url:frm_js.ajax_url,
			data:jQuery(object).serialize() +'&action=frm_entries_'+ jQuery(object).find('input[name="frm_action"]').val()+'&nonce='+frm_js.nonce,
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
					jump=jQuery(object).closest(document.getElementById('frm_form_'+jQuery(object).find('input[name="form_id"]').val()+'_container'));
					newPos=jump.offset().top;
					jump.replaceWith(errObj);
					cOff = document.documentElement.scrollTop || document.body.scrollTop;
					if(newPos && newPos > frm_js.offset && cOff > newPos){
						jQuery(window).scrollTop(newPos-frm_js.offset);
					}
					if(typeof(frmThemeOverride_frmAfterSubmit) == 'function'){
						var fin = jQuery(errObj).find('input[name="form_id"]').val();
						var p = '';
						if(fin) p = jQuery('input[name="frm_page_order_'+fin+'"]').val();
						frmThemeOverride_frmAfterSubmit(fin,p,errObj,object);
					}
					if(jQuery(object).find('input[name="id"]').length){
						var eid = jQuery(object).find('input[name="id"]').val();
						jQuery(document.getElementById('frm_edit_'+eid)).find('a').addClass('frm_ajax_edited').click();
					}
				}else{
					jQuery(object).find('input[type="submit"], input[type="button"]').removeAttr('disabled');
					jQuery(object).find('.frm_ajax_loading').removeClass('frm_loading_now');

					//show errors
					var cont_submit=true;
					jQuery('.form-field').removeClass('frm_blank_field');
					jQuery('.form-field .frm_error').replaceWith('');
					jump = '';
					var show_captcha = false;
                    var $fieldCont = null;
					for (var key in errObj){
						$fieldCont = jQuery(object).find(jQuery(document.getElementById('frm_field_'+key+'_container')));
						if($fieldCont.length && $fieldCont.is(':visible')){
							cont_submit=false;
							if(jump === ''){
								frmFrontForm.scrollMsg(key, object);
								jump='#frm_field_'+key+'_container';
							}
                            var $recapcha = jQuery(object).find('#frm_field_'+key+'_container .g-recaptcha');
							if($recapcha.length){
								show_captcha = true;
                                grecaptcha.reset();
							}
							
							$fieldCont.addClass('frm_blank_field');
							if(typeof(frmThemeOverride_frmPlaceError) == 'function'){
								frmThemeOverride_frmPlaceError(key,errObj);
							}else{
								$fieldCont.append('<div class="frm_error">'+errObj[key]+'</div>');
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
    function compileGoogleTable(opts){
        var data = new google.visualization.DataTable();

        var showID = false;
        if ( jQuery.inArray('id', opts.options.fields) ) {
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
                            fieldVal = 'false';
                        } else {
                            fieldVal = 'true';
                        }
                    }

                    data.setCell(row, col, fieldVal);

                    col++;
                }

                if ( showEdit ) {
                    data.setCell(row, col, '<a href="'+ entry.editLink +'">'+ opts.options.edit_link +'</a>');
         		    col++;
        	    }

                if ( showDelete ) {
                    data.setCell(row, col,'<a href="'+ entry.deleteLink +'" class="frm_delete_link" onclick="return confirm('+ opts.options.confirm +')">'+ opts.options.delete_link +'</a>');
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
        chart.draw( data, JSON.stringify(opts.graphOpts) );
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
                    data.addColumn({type:'string',role:'tooltip'});

                    // remove the tooltip key from the array
                    for ( var row = 0, rc = rowCount; row < rc; row++ ) {
                        var tooltip = opts.rows[row].tooltip;
                        opts.rows[row].tooltip = null;
                        opts.rows[row].push(tooltip);
                    }

                    data.addRows(opts.rows);
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
			if ( files.length == 1 ) {
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

        obj.closest('.frm_form_field').find('.frm_uploaded_files:last').after('<input name="'+ fileName +'[]" data-fid="'+ id +'"class="frm_multiple_file" multiple="multiple" type="file" />');
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
		fadeOut(jQuery(document.getElementById(id)));
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
			data:{action:'frm_add_form_row', field_id:id, i:i, nonce:frm_js.nonce},
			success:function(html){
				var item = jQuery(html).hide().fadeIn('slow');
				jQuery('.frm_repeat_'+ id +':last').after(item);

                var checked = ['other'];
                var fieldID;
                var reset = 'reset';
                // hide fields with conditional logic
                jQuery(html).find('input, select, textarea').each(function(){
            		fieldID = this.name.replace('item_meta[', '').split(']')[2].replace('[', '');
                    if ( jQuery.inArray(fieldID, checked ) == -1 ) {
                        checked.push(fieldID);
                        checkDependentField('und', fieldID, null, jQuery(this), reset);
                        reset = 'persist';
                    }
                });

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
	
	function empty($obj) {
		if ( $obj !== null ) {
			while ( $obj.firstChild ) {
				$obj.removeChild($obj.firstChild);
			}
		}
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
			frmFrontForm.invisible('.frm_ajax_loading');

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

			jQuery(document).on('change', '.frm-show-form input[name^="item_meta"], .frm-show-form select[name^="item_meta"], .frm-show-form textarea[name^="item_meta"]', maybeCheckDependent);
			
			jQuery(document).on('click', '.frm-show-form input[type="submit"], .frm-show-form input[name="frm_prev_page"], .frm-show-form .frm_save_draft', setNextPage);
            
            jQuery(document).on('change', '.frm_other_container input[type="checkbox"], .frm_other_container input[type="radio"], .frm_other_container select', showOtherText);
			
			jQuery(document).on('click', '.frm_remove_form_row', removeRow);
			jQuery(document).on('click', '.frm_add_form_row', addRow);

			// toggle collapsible entries shortcode
			jQuery('.frm_month_heading, .frm_year_heading').toggle(
				function(){
					jQuery(this).children('.ui-icon-triangle-1-e, .ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-s').removeClass('ui-icon-triangle-1-e');
					jQuery(this).next('.frm_toggle_container').fadeIn('slow');
				},
				function(){
					jQuery(this).children('.ui-icon-triangle-1-s, .ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-e').removeClass('ui-icon-triangle-1-s');
					jQuery(this).next('.frm_toggle_container').hide();
				}
			);
		},

		submitForm: function(e){
			e.preventDefault();
			if(jQuery(this).find('.wp-editor-wrap').length && typeof(tinyMCE) != 'undefined'){
				tinyMCE.triggerSave();
			}
			getFormErrors(this);
		},

        scrollToID: function(id){
            var frm_pos = jQuery(document.getElementBtId(id).offset());
            window.scrollTo(frm_pos.left, frm_pos.top);
        },

		scrollMsg: function(id, object){
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
				newPos=newPos-parseInt(m)-parseInt(b);
			}

			var cOff = document.documentElement.scrollTop || document.body.scrollTop;
			if(newPos && (!cOff || cOff > newPos)){
				jQuery(window).scrollTop(newPos);
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

        generateGoogleTable: function(num, type){
            var graphs = __FRMTABLES;
    		if ( typeof graphs == 'undefined' ) {
    			// there are no tables on this page
    			return;
    		}

            if(type == 'table'){
                compileGoogleTable(graphs.table[num]);
            }else{
                compileGraph(graphs[type][num]);
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


