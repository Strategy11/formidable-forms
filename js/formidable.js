jQuery(document).ready(function($){
$('.frm_ajax_loading').css('visibility', 'hidden');

$(document).on('click', '.frm_trigger', frmToggleSection);
if($('.frm_blank_field').length){
	$('.frm_blank_field').closest('.frm_toggle_container').prev('.frm_trigger').click();
}

if($.isFunction($.fn.placeholder)){
	$('.frm-show-form input, .frm-show-form textarea').placeholder();
}else{
	jQuery('.frm-show-form input[onblur], .frm-show-form textarea[onblur]').each(function(){
		if(jQuery(this).val() === '' ){
			jQuery(this).blur();
		}
	});
}

$(document).on('click', '.frm-show-form input[name^="item_meta"][type="radio"], .frm-show-form input[name^="item_meta"][type="checkbox"]', frmMaybeCheckDependent);
$(document).on('change', '.frm-show-form input[name^="item_meta"]:not([type=radio], [type=checkbox]), .frm-show-form select[name^="item_meta"], .frm-show-form textarea[name^="item_meta"]', frmMaybeCheckDependent);
$(document).on('click', '.frm-show-form input[type="submit"], .frm-show-form input[name="frm_prev_page"], .frm-show-form .frm_save_draft', frmSetNextPage);
$(document).on('click', '.frm_remove_link', frmRemoveDiv);
});

function frmSetNextPage(e){
	if(jQuery(this).attr('type') != 'submit')
		e.preventDefault();
	
	var f = jQuery(this).parents('form:first');
	var v = '';
	var d = '';
	
	if(jQuery(this).attr('name') == 'frm_prev_page' || jQuery(this).hasClass('frm_prev_page')){
		v = jQuery(f).find('.frm_next_page').attr('id').replace('frm_next_p_', '');
	}else if(jQuery(this).attr('name') == 'frm_save_draft' || jQuery(this).hasClass('frm_save_draft')){
		d = 1;
	}
	
	jQuery('.frm_next_page').val(v);
	jQuery('.frm_saving_draft').val(d);
	
	if(jQuery(this).attr('type') != 'submit')
		f.trigger('submit');
}

function frmToggleSection(){
jQuery(this).next('.frm_toggle_container').slideToggle('fast');
jQuery(this).toggleClass('active').children('.ui-icon-triangle-1-e, .ui-icon-triangle-1-s').toggleClass('ui-icon-triangle-1-s ui-icon-triangle-1-e');
}

function frmClearDefault(default_value,thefield){
var v = default_value.replace(/(\n|\r\n)/g, '\r');
var this_val=jQuery(thefield).val().replace(/(\n|\r\n)/g, '\r');
if(this_val == v){
	jQuery(thefield).removeClass('frm_default').val('');
}
}

function frmReplaceDefault(default_value,thefield){
var v = default_value.replace(/(\n|\r\n)/g, '\r');
if(jQuery(thefield).val() === ''){
	jQuery(thefield).addClass('frm_default').val(v);
}
}

function frmMaybeCheckDependent(){
	var field_id = jQuery(this).attr('name').replace('item_meta[', '').split(']')[0];
	if(field_id){
		frmCheckDependent('und',field_id);
	}
}

function frmCheckDependent(selected,field_id,rec){
if(typeof(__FRMRULES) == 'undefined'){
	return;
}
var all_rules=__FRMRULES;
var rules = all_rules[field_id];
if(typeof(rules)=='undefined') return;

if (typeof(rec) == 'undefined' || rec === null) {
	//stop recursion?
	rec = 'go';
}
	
var this_opts = [];
for(var i=0;i<rules.length;i++){
    var rule=rules[i];
    if(typeof(rule)!='undefined'){
        for(var j=0;j<rule.Conditions.length;j++){
			var c=rule.Conditions[j];
			c.HideField=rule.Setting.FieldName;
			c.MatchType=rule.MatchType;
			c.Show=rule.Show;
            this_opts.push(c);
        }
    }
}
var show_fields = [];
var hide_later = [];	
var len=this_opts.length;
for(i=0; i<len; i++){
  (function(i){
	var f=this_opts[i];	
	if(typeof(show_fields[f.HideField]) == 'undefined') 
		show_fields[f.HideField] = [];

	if(f.FieldName!=field_id || typeof(selected)=='undefined' || selected=='und'){
		var prevSel=selected;
		if(f.Type=='radio' || f.Type=='data-radio'){
			selected=jQuery("input[name='item_meta["+f.FieldName+"]']:checked, input[type='hidden'][name='item_meta["+f.FieldName+"]']").val();
		}else if(f.Type=='select' || f.Type=='data-select'){
			selected=jQuery("select[name^='item_meta["+f.FieldName+"]'], input[type='hidden'][name^='item_meta["+f.FieldName+"]']").val();
			if(jQuery("input[type='hidden'][name^='item_meta["+f.FieldName+"]']").length){
				selected = [];
				jQuery("input[type='hidden'][name^='item_meta["+f.FieldName+"]']").each(function(){
					selected.push(jQuery(this).val());
				});
			}
		}else if(f.Type !='checkbox' && f.Type !='data-checkbox'){
			selected=jQuery("input[name^='item_meta["+f.FieldName+"]']").val();
		}
	}

	if(typeof(selected)=='undefined'){
		selected=jQuery("input[type=hidden][name^='item_meta["+f.FieldName+"]']").val();
		if(typeof(selected)=='undefined') selected='';
	}

    if(f.Type=='checkbox' || (f.Type=='data-checkbox' && typeof(f.LinkedField)=='undefined')){
        show_fields[f.HideField][i]=false;
        jQuery("input[name='item_meta["+f.FieldName+"][]']:checked, input[type='hidden'][name^='item_meta["+f.FieldName+"]']").each(function(){
			var match=frmOperators(f.Condition,f.Value,jQuery(this).val());
			if(show_fields[f.HideField][i] === false && match)
				show_fields[f.HideField][i] = true;
		});
    }else if(f.Type=='data-radio'){
		if(typeof(f.DataType) == 'undefined' || f.DataType === '' || f.DataType === 'data'){
			if(selected === ''){	
				show_fields[f.HideField][i]=false;
				jQuery('#frm_field_'+f.HideField+'_container').fadeOut('slow');
				jQuery('#frm_data_field_'+f.HideField+'_container').html('');
			}else{
				if(typeof(f.DataType)=='undefined') show_fields[f.HideField][i]=frmOperators(f.Condition,f.Value,selected);	
				else show_fields[f.HideField][i]={'funcName':'frmGetData','f':f,'sel':selected};
			}
		}else{
			if(selected === ''){
				show_fields[f.HideField][i]=false;
			}else{
				show_fields[f.HideField][i]={'funcName':'frmGetDataOpts','f':f,'sel':selected};
			}
		}
    }else if(f.Type=='data-checkbox' && typeof(f.LinkedField)!='undefined'){
		var checked_vals = [];
		jQuery("input[name='item_meta["+f.FieldName+"][]']:checked, input[type='hidden'][name='item_meta["+f.FieldName+"][]']").each(function(){checked_vals.push(jQuery(this).val());});
		if(typeof(f.DataType) == 'undefined' || f.DataType === '' || f.DataType === 'data'){
			if(checked_vals.length){
				show_fields[f.HideField][i]=true;
				jQuery('#frm_data_field_'+f.HideField+'_container').html('');
				frmGetData(f,checked_vals,1);
				//jQuery.each(checked_vals, function(ckey,cval){frmGetData(f,cval,1); });
			}else{
				show_fields[f.HideField][i]=false;
				jQuery('#frm_field_'+f.HideField+'_container').fadeOut('slow');
				jQuery('#frm_data_field_'+f.HideField+'_container').html('');
			}
		}else{
			if(checked_vals.length){
				show_fields[f.HideField][i] = {'funcName':'frmGetDataOpts','f':f,'sel':checked_vals};
			}else{
				show_fields[f.HideField][i] = false;
			}
        }
    }else if(f.Type=='data-select' && typeof(f.LinkedField)!='undefined'){
		if(f.DataType === '' || f.DataType == 'data'){
            if(selected === ''){
				show_fields[f.HideField][i]=false; jQuery('#frm_data_field_'+f.HideField+'_container').html('');
			}else if(selected && jQuery.isArray(selected)){
				show_fields[f.HideField][i]=true;
				jQuery('#frm_data_field_'+f.HideField+'_container').html('');
				frmGetData(f,selected,1);
			}else{
				show_fields[f.HideField][i]={'funcName':'frmGetData','f':f,'sel':selected};
			}
        }else{
            if(selected === ''){
				show_fields[f.HideField][i]=false;
			}else{
				show_fields[f.HideField][i]={'funcName':'frmGetDataOpts','f':f,'sel':selected};
			}
        }
    }else{
		if(typeof(f.Value)=='undefined' && f.Type.indexOf('data') === 0){
			if(selected === '') f.Value='1';
			else f.Value=selected;
			show_fields[f.HideField][i]=frmOperators(f.Condition,f.Value,selected);
			f.Value=undefined;
		}else{
			show_fields[f.HideField][i]=frmOperators(f.Condition,f.Value,selected);
		}
    }

	if(f.FieldName!=field_id){
		selected = prevSel;
	}
	if(f.MatchType=='any'){
		if(show_fields[f.HideField][i] !== false){
			if(f.Show=='show'){
				if(show_fields[f.HideField][i] !== true){
					frmShowField(show_fields[f.HideField][i],f.FieldName,rec);
				}else{
					jQuery('#frm_field_'+f.HideField+'_container').show();
				}
			}else{
				jQuery('#frm_field_'+f.HideField+'_container').hide();
			}
		}else{
			hide_later.push({'result':show_fields[f.HideField][i],'show':f.Show,'match':'any','fname':f.FieldName,'fkey':f.HideField});
		}
	}else if(f.MatchType=='all'){
		hide_later.push({'result':show_fields[f.HideField][i],'show':f.Show,'match':'all','fname':f.FieldName,'fkey':f.HideField});
	}
	
	if(i==(len-1)){
		jQuery.each(hide_later, function(hkey,hvalue){ 
			if(typeof(hvalue)!='undefined' && typeof(hvalue.result)!='undefined'){
				if((hvalue.match=='any' && (jQuery.inArray(true, show_fields[hvalue.fkey]) == -1)) || (hvalue.match=='all' && (jQuery.inArray(false, show_fields[hvalue.fkey]) > -1))){
					if(hvalue.show=='show'){
						jQuery('#frm_field_'+hvalue.fkey+'_container:hidden').hide();
						jQuery('#frm_field_'+hvalue.fkey+'_container').hide();
					}else{
						jQuery('#frm_field_'+hvalue.fkey+'_container').show();
					}
				}else{
					if(hvalue.show=='show'){
						jQuery('#frm_field_'+hvalue.fkey+'_container').show();
					}else{
						jQuery('#frm_field_'+hvalue.fkey+'_container:hidden').hide();
						jQuery('#frm_field_'+hvalue.fkey+'_container').hide();
					}
				}
				if(typeof(hvalue.result) !== false && typeof(hvalue.result) !==true){
					frmShowField(hvalue.result,hvalue.fname,rec);
				}
				delete hide_later[hkey];
			}
		});
	}
  })(i);
}
}

function frmOperators(op,a,b){
	if(typeof(b) == 'undefined') b='';
	if(jQuery.isArray(b) && jQuery.inArray(a,b) > -1) b = a;
	if(String(a).search(/^\s*(\+|-)?((\d+(\.\d+)?)|(\.\d+))\s*$/) != -1){
		a=parseFloat(a);
		b=parseFloat(b);
	}
	if(String(a).indexOf('&quot;') != '-1' && frmOperators(op,a.replace('&quot;', '"'),b))
		return true;
	var operators = {
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
	return operators[op](a,b);
}

function frmShowField(funcInfo,field_id,rec){
if(funcInfo.funcName=='frmGetDataOpts'){frmGetDataOpts(funcInfo.f,funcInfo.sel,field_id,rec);}
else if(funcInfo.funcName=='frmGetData'){frmGetData(funcInfo.f,funcInfo.sel,0);}
}

function frmGetData(f,selected,append){
	if(!append)
		jQuery('#frm_data_field_'+f.HideField+'_container').html('<span class="frm-loading-img"></span>');
	jQuery.ajax({
		type:"POST",url:frm_js.ajax_url,
		data:"action=frm_fields_ajax_get_data&entry_id="+selected+"&field_id="+f.LinkedField+"&current_field="+f.HideField,
		success:function(html){
			if(html !== '') jQuery('#frm_field_'+f.HideField+'_container').show(); 
			if(append){jQuery('#frm_data_field_'+f.HideField+'_container').append(html);}
			else{
				jQuery('#frm_data_field_'+f.HideField+'_container').html(html);
				var val=jQuery('#frm_data_field_'+f.HideField+'_container').children('input').val();
				if(html === '' || val === '') jQuery('#frm_field_'+f.HideField+'_container').hide();
				frmCheckDependent(selected,f.HideField);
			}
			return true;
		}
	});
}

function frmGetDataOpts(f,selected,field_id,rec){
	if(typeof(frm_checked_dep) == 'undefined'){
		frm_checked_dep = [];
	}
	
	//don't check the same field twice when more than a 2-level dependency, and parent is not on this page
	if(rec == 'stop' && (jQuery.inArray(f.HideField, frm_checked_dep) > -1) && jQuery("input[type='hidden'][name^='item_meta["+field_id+"]']").length){
		return;
	}
		
	var prev = [];
	if(f.DataType=='checkbox' || f.DataType=='radio'){
		jQuery("input[name^='item_meta["+f.HideField+"]']:checked, input[type='hidden'][name^='item_meta["+f.HideField+"]']").each(function(){prev.push(jQuery(this).val());});
	}else if(f.DataType=='select'){
		if(jQuery("input[type='hidden'][name^='item_meta["+f.HideField+"]']").length){
			jQuery("input[type='hidden'][name^='item_meta["+f.HideField+"]']").each(function(){
				prev.push(jQuery(this).val());
			});
		}else if(jQuery("select[name^='item_meta["+f.HideField+"]']").length){
			prev = jQuery("select[name^='item_meta["+f.HideField+"]']").val();
		}else if((rec == 'stop' || jQuery('#frm_data_field_'+f.HideField+'_container .frm-loading-img').length) && (jQuery.inArray(f.HideField, frm_checked_dep) > -1)){
			return;
		}
	}else{
		prev.push(jQuery("input[name^='item_meta["+f.HideField+"]']").val());
	}
	if(prev === null || prev.length === 0) prev = '';
	
	frm_checked_dep.push(f.HideField);
	
	//don't get values for fields that are to remain hidden on the page
	if(!jQuery('#frm_data_field_'+f.HideField+'_container').length && jQuery("input[type='hidden'][name^='item_meta["+f.HideField+"]']").length){
		frmCheckDependent(prev,f.HideField,'stop');
		return false;
	}
	
	jQuery('#frm_data_field_'+f.HideField+'_container').html('<span class="frm-loading-img" style="visibility:visible;display:inline;"></span>');

	jQuery.ajax({
		type:"POST",url:frm_js.ajax_url,
		data:"action=frm_fields_ajax_data_options&hide_field="+field_id+"&entry_id="+selected+"&selected_field_id="+f.LinkedField+"&field_id="+f.HideField,
		success:function(html){
			if(html === ''){
				jQuery('#frm_field_'+f.HideField+'_container').hide();
				prev='';
			}else if(f.MatchType!='all'){
				jQuery('#frm_field_'+f.HideField+'_container').show();
			}
			jQuery('#frm_data_field_'+f.HideField+'_container').html(html);
			
			if(html !== '' && prev !== ''){
				if(!jQuery.isArray(prev)){
					var new_prev = [];
					new_prev.push(prev);
					prev = new_prev;
				}
				
				//select options that were selected previously			
				jQuery.each(prev, function(ckey,cval){
					if(typeof(cval) != 'undefined'){
						if(f.DataType == 'checkbox' || f.DataType == 'radio'){
							jQuery("#field_"+f.HideField+"-"+cval).attr('checked','checked');
						}else if(f.DataType == 'select'){
							if(jQuery("select[name^='item_meta["+f.HideField+"]'] option[value="+cval+"]").length){
								jQuery("select[name^='item_meta["+f.HideField+"]'] option[value="+cval+"]").prop('selected', true);
							}else{
								prev.splice(ckey,1); //remove options that no longer exist
							}
						}else{
							jQuery("input[name^='item_meta["+f.HideField+"]']").val(cval);
						}
					}
				});
			}
			if(jQuery(html).hasClass('frm_chzn') && jQuery().chosen){
				jQuery('.frm_chzn').chosen({allow_single_deselect:true});
			}
			
			frmCheckDependent(prev,f.HideField,'stop');
		}
	});
}

function frmOnSubmit(e){
	e.preventDefault();
	if(jQuery(this).find('.wp-editor-wrap').length && typeof(tinyMCE) != 'undefined'){
		tinyMCE.triggerSave();
	}
	frmGetFormErrors(this);
}

function frmGetFormErrors(object){
	jQuery(object).find('input[type="submit"], input[type="button"]').attr('disabled','disabled');
	jQuery(object).find('.frm_ajax_loading').css('visibility', 'visible');
	frm_checked_dep = [];
	var jump = '';
	var newPos = 0;
	var cOff = 0;
	
	jQuery.ajax({
		type:"POST",url:frm_js.ajax_url,
		data:jQuery(object).serialize()+"&action=frm_entries_"+jQuery(object).find('input[name="frm_action"]').val()+"&_ajax_nonce=1",
		success:function(errObj){
			errObj=errObj.replace(/^\s+|\s+$/g,'');
			if(errObj.indexOf('{') === 0)
				errObj = jQuery.parseJSON(errObj);
			if(errObj === '' || !errObj || errObj === '0' || (typeof(errObj) != 'object' && errObj.indexOf('<!DOCTYPE') === 0)){
				if(jQuery('#frm_loading').length){
					var file_val=jQuery(object).find('input[type=file]').val();
					if(typeof(file_val) != 'undefined' && file_val !== ''){
						setTimeout(function(){jQuery('#frm_loading').fadeIn('slow');},2000);
					}
				}
				if(jQuery(object).find('#recaptcha_area').length && (jQuery(object).find('.frm_next_page').length < 1 || jQuery(object).find('.frm_next_page').val() < 1))
					jQuery(object).find('#recaptcha_area').replaceWith('');
				
				object.submit();
			}else if(typeof(errObj) != 'object'){
				jQuery(object).find('.frm_ajax_loading').css('visibility', 'hidden');
				jump=jQuery(object).closest('#frm_form_'+jQuery(object).find('input[name="form_id"]').val()+'_container');
				newPos=jump.offset().top;
				jump.replaceWith(errObj);
				cOff = document.documentElement.scrollTop || document.body.scrollTop;
				if(newPos && newPos > frm_js.offset && cOff > newPos){
					jQuery(window).scrollTop(newPos-frm_js.offset);
				}
				if(typeof(frmThemeOverride_frmAfterSubmit) == 'function'){
					var fin=jQuery(errObj).find('input[name="form_id"]').val();
					var p = '';
					if(fin) p = jQuery('input[name="frm_page_order_'+fin+'"]').val();
					frmThemeOverride_frmAfterSubmit(fin,p,errObj,object);
				}
				if(jQuery(object).find('input[name="id"]').length){
					var eid = jQuery(object).find('input[name="id"]').val();
					jQuery('#frm_edit_'+eid).find('a').addClass('frm_ajax_edited').click();
				}
			}else{
				jQuery(object).find('input[type="submit"], input[type="button"]').removeAttr('disabled');
				jQuery(object).find('.frm_ajax_loading').css('visibility', 'hidden');
				
				//show errors
				var cont_submit=true;
				jQuery('.form-field').removeClass('frm_blank_field');
				jQuery('.form-field .frm_error').replaceWith('');
				jump = '';
				var show_captcha = false;
				for (var key in errObj){
					if(jQuery(object).find('#frm_field_'+key+'_container').length && jQuery('#frm_field_'+key+'_container').is(":visible")){
						cont_submit=false;
						if(jump === ''){
							frmScrollMsg(key, object);
							jump='#frm_field_'+key+'_container';
						}
						if(jQuery(object).find('#frm_field_'+key+'_container #recaptcha_area').length){
							show_captcha = true;
							Recaptcha.reload();
						}
						jQuery(object).find('#frm_field_'+key+'_container').addClass('frm_blank_field');
						if(typeof(frmThemeOverride_frmPlaceError) == 'function'){frmThemeOverride_frmPlaceError(key,errObj);}
						else{jQuery(object).find('#frm_field_'+key+'_container').append('<div class="frm_error">'+errObj[key]+'</div>');}
					}else if(key == 'redirect'){
						window.location=errObj[key];
						return;
					}
				}
				if(show_captcha !== true) jQuery(object).find('#recaptcha_area').replaceWith('');
				if(cont_submit) object.submit();
			}
		},
		error:function(){
			jQuery(object).find('input[type="submit"], input[type="button"]').removeAttr('disabled');object.submit();
		}
	});
}

function frmEditEntry(entry_id,prefix,post_id,form_id,cancel,hclass){
	var label=jQuery('#frm_edit_'+entry_id).html();
	var orig=jQuery('#'+prefix+entry_id).html();
	jQuery('#'+prefix+entry_id).html('<span class="frm-loading-img" id="'+prefix+entry_id+'"></span><div class="frm_orig_content" style="display:none">'+orig+'</div>');
	jQuery.ajax({
		type:"POST",url:frm_js.ajax_url,dataType:"html",
		data:"action=frm_entries_edit_entry_ajax&post_id="+post_id+"&entry_id="+entry_id+"&id="+form_id,
		success:function(html){
			jQuery('#'+prefix+entry_id).children('.frm-loading-img').replaceWith(html);
			jQuery('#frm_edit_'+entry_id).replaceWith('<span id="frm_edit_'+entry_id+'"><a onclick="frmCancelEdit('+entry_id+',\''+prefix+'\',\''+frm_escape_html(label)+'\','+post_id+','+form_id+',\''+hclass+'\')" class="'+hclass+'">'+cancel+'</a></span>');
		}
	});
}

function frmCancelEdit(entry_id,prefix,label,post_id,form_id,hclass){
	var cancel=jQuery('#frm_edit_'+entry_id+' a').html();
	if(!jQuery('#frm_edit_'+entry_id).find('a').hasClass('frm_ajax_edited')){
		jQuery('#'+prefix+entry_id).children('.frm_forms').replaceWith('');
		jQuery('#'+prefix+entry_id).children('.frm_orig_content').fadeIn('slow').removeClass('frm_orig_content');
	}
	jQuery('#frm_edit_'+entry_id).replaceWith('<a id="frm_edit_'+entry_id+'" class="frm_edit_link '+hclass+'" href="javascript:frmEditEntry('+entry_id+',\''+prefix+'\','+post_id+','+form_id+',\''+frm_escape_html(cancel)+'\',\''+hclass+'\')">'+label+'</a>');
}

function frmUpdateField(entry_id,field_id,value,message,num){
	jQuery('#frm_update_field_'+entry_id+'_'+field_id).html('<span class="frm-loading-img"></span>');
	jQuery.ajax({
		type:"POST",url:frm_js.ajax_url,
		data:"action=frm_entries_update_field_ajax&entry_id="+entry_id+"&field_id="+field_id+"&value="+value,
		success:function(){
			if(message.replace(/^\s+|\s+$/g,'') === ''){
				jQuery('#frm_update_field_'+entry_id+'_'+field_id+'_'+num).fadeOut('slow');
			}else{
				jQuery('#frm_update_field_'+entry_id+'_'+field_id+'_'+num).replaceWith(message);
			}
		}
	});
}

function frmDeleteEntry(entry_id,prefix){	
	jQuery('#frm_delete_'+entry_id).replaceWith('<span class="frm-loading-img" id="frm_delete_'+entry_id+'"></span>');
	jQuery.ajax({
		type:"POST",url:frm_js.ajax_url,
		data:"action=frm_entries_destroy&entry="+entry_id,
		success:function(html){
			if(html.replace(/^\s+|\s+$/g,'') == 'success')
				jQuery('#'+prefix+entry_id).fadeOut('slow');
			else
				jQuery('#frm_delete_'+entry_id).replaceWith(html);
			
		}
	});
}

function frmRemoveDiv(){
jQuery(this).parent('.frm_uploaded_files').fadeOut('slow').replaceWith('');	
}

function frmNextUpload(obj,id){
	obj.wrap('<div class="frm_file_names frm_uploaded_files">');
	var files = obj.get(0).files;
	for (var i = 0; i < files.length; i++){
		if(files.length == 1){
			obj.after(files[i].name+' <a href="#" onclick="frmClearFile(jQuery(this));return false;">'+frm_js.remove+'</a>');
		} else {
			obj.after(files[i].name +'<br/>');
		}
	}
	obj.hide(); 
	jQuery('#frm_field_'+id+'_container .frm_uploaded_files:last').after('<input name="file'+id+'[]" multiple="multiple" type="file" onchange="frmNextUpload(jQuery(this),'+id+')"/>');
}

function frmClearFile(file){
file.parent('.frm_file_names').replaceWith('');
return false;
}

function frm_resend_email(entry_id,form_id){
	jQuery('#frm_resend_email').replaceWith('<img id="frm_resend_email" src="'+ frm_js.images_url +'/wpspin_light.gif" alt="'+ frm_js.loading +'" />');
	jQuery.ajax({
		type:"POST",url:frm_js.ajax_url,
		data:"action=frm_entries_send_email&entry_id="+entry_id+"&form_id="+form_id+"&type=email",
		success:function(msg){ jQuery('#frm_resend_email').replaceWith(msg);}
	});
}

function frmScrollMsg(id, object){
	if(typeof(object) == 'undefined'){
		var newPos = jQuery('#frm_form_'+id+'_container').offset().top;
	}else{
		var newPos = jQuery(object).find('#frm_field_'+id+'_container').offset().top;
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
	
	cOff = document.documentElement.scrollTop || document.body.scrollTop;
	if(newPos && (!cOff || cOff > newPos)){
		jQuery(window).scrollTop(newPos);
	}
}

function frm_escape_html(text){
  return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}

jQuery.fn.frmVisible = function() {
    return this.css('visibility', 'visible');
};

jQuery.fn.frmInvisible = function() {
    return this.css('visibility', 'hidden');
};

jQuery.fn.frmVisibilityToggle = function() {
    return this.css('visibility', function(i, visibility) {
        return (visibility == 'visible') ? 'hidden' : 'visible';
    });
};
