function frmAdminBuildJS(){
    //'use strict';

    /*global jQuery:false, frm_admin_js, frmGlobal, ajaxurl */

	var $newFields = jQuery(document.getElementById('new_fields'));
	var this_form_id = jQuery(document.getElementById('form_id')).val();
	var cancelSort = false;
	
	function showElement(element){
		element[0].style.display = '';
	}

	function hideElement(element){
		element[0].style.display = 'none';
	}

	function empty($obj){
		if ( $obj !== null ) {
			while ( $obj.firstChild ) {
				$obj.removeChild($obj.firstChild);
			}
		}
	}

	function addClass($obj, className){
		if ($obj.classList){
			$obj.classList.add(className);
		}else{
			$obj.className += ' ' + className;
		}
	}

	function confirmClick( e ) {
		/*jshint validthis:true */
		var $link = jQuery(this);
		if ( $link.hasClass('frm_confirming') ) {
			return true;
		} else {
			e.stopPropagation();
			e.preventDefault();
			confirmLinkClick( $link );
		}
	}

	function confirmLinkClick( $link ) {
		var message = $link.data('frmverify');

		if ( typeof message === 'undefined' || $link.hasClass('frm_confirming') ) {
			return true;
		} else {
			$link.addClass('frm_confirming');

			var $label = $link.find('.frm_link_label');
			if ( $label.length < 1 ) {
				$label = $link;
			}

			var oldLabel = $label.html();

			$label.html(message);

			setTimeout( function(){
				$link.removeClass('frm_confirming');
				$label.html(oldLabel);
			}, 5000 );
			return false;
		}
	}

	function preventBodyScroll() {
		jQuery('#frm-fixed-panel').on( 'mousewheel DOMMouseScroll', function(e){
			var e0 = e.originalEvent,
				delta = e0.wheelDelta || -e0.detail;
			this.scrollTop += ( delta < 0 ? 1 : -1 ) * 30;
			e.preventDefault();
		});
	}

	function setupMenuOffset() {
		window.onscroll = document.documentElement.onscroll = setMenuOffset;
		setMenuOffset();

		// set height for scrolling sidebar
		var fixedBox = document.getElementById('frm-fixed-panel');
		if ( fixedBox !== null ) {
			var startPos = document.getElementById('frm_set_height_ele').getBoundingClientRect().top;
			var topSidebar = document.getElementById('frm-fixed').getBoundingClientRect().top;
			var totalHeight = window.innerHeight;
			fixedBox.style.maxHeight = ( totalHeight - ( startPos - topSidebar ) - 30 ) +'px';
		}
	}

	function setMenuOffset(){
		var offset = 455;
		var fields = document.getElementById('frm-fixed');

		if ( fields === null ) {
			fields = document.getElementById('frm_adv_info');
			if ( fields === null ) {
				return;
			}
		}

		var currentOffset = document.documentElement.scrollTop || document.body.scrollTop; // body for Safari
		if(currentOffset === 0){
			fields.style.top = '';
			return;
		}
		var posEle = jQuery(document.getElementById('frm_position_ele'));
		if(posEle.length>0){
			var eleOffset = posEle.offset();
			offset = eleOffset.top;
		}

		var desiredOffset = offset + 2 - currentOffset;
		if (desiredOffset < 35){
			desiredOffset = 35;
		}
		fields.style.top = desiredOffset + 'px';
	}

	function loadTooltips() {
		var tooltipOpts = {
			template:'<div class="frm_tooltip tooltip"><div class="tooltip-inner"></div></div>',
			placement:'bottom',
			container:'body'
		};

		var wrapClass = jQuery('.wrap, .frm_wrap');
		wrapClass.on('click', '.widget-top,a.widget-action', clickWidget);

		wrapClass.on('mouseenter.frm', '.frm_help', function(){
			jQuery(this).off('mouseenter.frm');
			jQuery('.frm_help').tooltip(tooltipOpts);
			jQuery(this).tooltip('show');
		});
		jQuery('.frm_help').tooltip(tooltipOpts);
		wrapClass.on('mouseenter.frm', '.frm_bstooltip', function(){
			jQuery(this).off('mouseenter.frm');
			jQuery('.frm_bstooltip').tooltip();
			jQuery(this).tooltip('show');
		});
		jQuery('.frm_bstooltip').tooltip();
	}

	function removeThisTag(){
		/*jshint validthis:true */
		var deleteButton = jQuery(this);
		var continueRemove = confirmLinkClick( deleteButton );
		if ( continueRemove === false ) {
			return;
		} else {
			var id = deleteButton.data('removeid');
			var show = deleteButton.data('showlast');
			if ( typeof show === 'undefined' ) {
				show = '';
			}
			var hide = deleteButton.data('hidelast');
			if ( typeof hide === 'undefined' ) {
				hide = '';
			}
		}

		if(show !== ''){
			if ( deleteButton.closest('.frm_add_remove').find('.frm_remove_tag').length > 1 ) {
				show = '';
				hide = '';
			}
		}else if(id.indexOf('frm_postmeta_') === 0){
			if(jQuery('#frm_postmeta_rows .frm_postmeta_row').length<2){
				show='.frm_add_postmeta_row.button';
			}
			if(jQuery('.frm_toggle_cf_opts').length && jQuery('#frm_postmeta_rows .frm_postmeta_row:not(#'+id+')').last().length){
				if(show !== '') {
					show += ',';
				}
				show += '#'+jQuery('#frm_postmeta_rows .frm_postmeta_row:not(#'+id+')').last().attr('id')+' .frm_toggle_cf_opts';
			}
		}

		var $fadeEle = jQuery(document.getElementById(id));
		$fadeEle.fadeOut('slow', function(){
			$fadeEle.remove();

			if ( hide !== '' ) {
				jQuery( hide ).hide();
			}

			if ( show !== '' ) {
				jQuery( show+' a,'+show ).removeClass('frm_hidden').fadeIn('slow');
			}

			var action = jQuery(this).closest('.frm_form_action_settings');
			if(typeof action !== 'undefined'){
				var type = jQuery(this).closest('.frm_form_action_settings').find('.frm_action_name').val();
				checkActiveAction(type);
			}
		});
		if(show !== '') {
			jQuery(this).closest('.frm_logic_rows').fadeOut('slow');
		}

		return false;
	}
	
	function clickWidget(b){
		/*jshint validthis:true */
		if(typeof b.target !== 'undefined'){
			b = this;
		}

		popCalcFields(b);

		var cont = jQuery(b).closest('.frm_form_action_settings');
		if ( cont.length && cont.find('.form-table').length < 1 ) {
			var action_id = cont.find('input[name$="[ID]"]').val();
			var action_type = cont.find('input[name$="[post_excerpt]"]').val();
			if ( action_type ) {
				cont.children('.widget-inside').html('<span class="spinner frm_spinner"></span>');
				cont.find('.spinner').fadeIn('slow');
				jQuery.ajax({
					type:'POST',url:ajaxurl,
					data:{action:'frm_form_action_fill', action_id:action_id, action_type:action_type, nonce:frmGlobal.nonce},
					success:function(html){
						cont.children('.widget-inside').html(html);
						initiateMultiselect();
					}
				});
			}
		}

		jQuery(b).closest('.frm_field_box').siblings().find('.widget-inside').slideUp('fast');
		if ( ( typeof b.className !== 'undefined' && b.className.indexOf('widget-action') !== -1 ) || jQuery(b).closest('.start_divider').length < 1){
			return;
		}

		var inside = jQuery(b).closest('div.widget').children('.widget-inside');
		if(inside.is(':hidden')){
			inside.slideDown('fast');
		}else{
			inside.slideUp('fast');
		}
	}

	function clickTab(link){
		link = jQuery(link);
		var t = link.attr('href');
		if(typeof t === 'undefined'){
			return;
		}

		var c = t.replace('#', '.');
		var pro = jQuery('#taxonomy-linkcategory .frm-category-tabs li').length > 2;
		link.closest('li').addClass('tabs active').siblings('li').removeClass('tabs active starttab');
		if(link.closest('div').find('.tabs-panel').length){
			link.closest('div').children('.tabs-panel').not(t).not(c).hide();
		}else{
			link.closest('div.inside').find('.tabs-panel, .hide_with_tabs').hide();
			if(link.closest('ul').hasClass('frm-form-setting-tabs')){
				if(t === '#html_settings'){
					if(pro){
						jQuery('#taxonomy-linkcategory .frm-category-tabs li').hide();
						document.getElementById('frm_html_tab').style.display = '';
					}
					jQuery(document.getElementById('frm_html_tags_tab')).click();
				}else if(jQuery(document.getElementById('frm_html_tags_tab')).is(':visible')){
					if(pro){
						showElement(jQuery('#taxonomy-linkcategory .frm-category-tabs li'));
						document.getElementById('frm_html_tab').style.display = 'none';
					}
					jQuery(document.getElementById('frm_insert_fields_tab')).click();
				}
			}else {
				/* global settings */
				var ajax = link.data('frmajax');
				if ( typeof ajax !== 'undefined' && ajax == '1' ) {
					loadSettingsTab(t);
				}
			}
		}
		jQuery(t).show();
		jQuery(c).show();

		if(jQuery(link).closest('#frm_adv_info').length){
			return;
		}

		if(jQuery('.frm_form_settings').length){
			jQuery('.frm_form_settings').attr('action', '?page=formidable&frm_action=settings&id='+jQuery('.frm_form_settings input[name="id"]').val()+'&t='+t.replace('#', ''));
		}else{
			jQuery('.frm_settings_form').attr('action', '?page=formidable-settings&t='+t.replace('#', ''));
		}
	}

	/* Form Builder */
	function setupSortable(sort){
		var startSort = false;
		var opts = {
			connectWith:'ul.frm_sorting',
			items: '> li.frm_field_box',
			placeholder:'sortable-placeholder',
			axis:'y',
			cursor:'move',
			opacity:0.65,
			cancel:'.widget,.frm_field_opts_list,input,textarea,select,.edit_field_type_end_divider,.frm_ipe_field_option,.frm_sortable_field_opts,.frm_noallow',
			accepts:'field_type_list',
			revert:true,
			forcePlaceholderSize:false,
			tolerance:'pointer',
			receive:function(event,ui){
				// Receive event occurs when an item in one sortable list is dragged into another sortable list

				if ( cancelSort ) {
					ui.item.addClass('frm_cancel_sort');
					return;
				}

				closeOpenDropdown( ui.item );

				if ( typeof ui.item.attr('id') !== 'undefined' ){
					if ( ui.item.attr('id').indexOf('frm_field_id') > -1 ) {
						// An existing field was dragged and dropped into, out of, or between sections
						updateFieldAfterMovingBetweenSections(ui.item);
					} else {
						// A new field was dragged into the form
						insertNewFieldByDragging(this, ui.item, opts);
					}
				}
			},
			change:function(event, ui){
				// don't allow some field types inside section
				if ( allowDrop(ui) ) {
					ui.placeholder.addClass('sortable-placeholder').removeClass('no-drop-placeholder');
					cancelSort = false;
				} else {
					ui.placeholder.addClass('no-drop-placeholder').removeClass('sortable-placeholder');
					cancelSort = true;
				}
			},
			update:function(){
				if ( cancelSort ) {
					return;
				}
				updateFieldOrder();
			},
			start: function( event, ui ) {
				if ( ui.item[0].offsetHeight > 120 ) {
					jQuery(sort).sortable( 'refreshPositions' );
				}
			},
			stop:function(event, ui){
				var moving = jQuery(this);
				if ( cancelSort ) {
					moving.sortable('cancel');
				}
				moving.children('.edit_field_type_end_divider').appendTo(this);
			},
			sort:function(event){
				jQuery( window ).scrollTop( function(i, v) {
					if ( startSort === false ) {
						startSort = event.clientY;
						return v;
					}

					var moved = event.clientY - startSort;
					var h = jQuery( window ).height();
					var y = event.clientY - h / 2;
					if ( event.clientY > ( h - 100 ) && moved > 5 ) {
						// scrolling down
						return v + y * 0.1;
					} else if ( event.clientY < 100 && moved < -5 ) {
						//scrolling up
						return v - Math.abs( y * 0.1 );
					}
				});
			}
		};

		jQuery(sort).sortable(opts);
	}

	// Close an open dropdown in the Fields panel
	function closeOpenDropdown( fieldButton ) {
		if ( fieldButton.hasClass('open') ) {
			fieldButton.click();
		}
	}

	// Get the section where a field is dropped
	function getSectionForFieldPlacement( currentItem ){
		var section = '';
		if ( typeof currentItem !== 'undefined' ) {
			section = currentItem.closest('.edit_field_type_divider');
		}

		return section;
	}

	// Get the form ID where a field is dropped
	function getFormIdForFieldPlacement( section ) {
		var form_id = '';

		if ( typeof section[0] !== 'undefined' ) {
			var sDivide = section.children('.start_divider');
			sDivide.children('.edit_field_type_end_divider').appendTo(sDivide);
			if (typeof section.data('formid') !== 'undefined') {
				form_id = section.find('input[name^="field_options[form_select_"]').val();
			}
		}

		if ( typeof form_id === 'undefined' || form_id === '' ){
			form_id = this_form_id;
		}

		return form_id;
	}

	// Get the section ID where a field is dropped
	function getSectionIdForFieldPlacement( section ) {
		var sectionId = 0;
		if ( typeof section[0] !== 'undefined' ){
			sectionId = section.attr('id').replace( 'frm_field_id_', '' );
		}

		return sectionId;
	}

	/**
	 * Update a field after it is dragged and dropped into, out of, or between sections
	 *
	 * @param {object} currentItem
	 */
	function updateFieldAfterMovingBetweenSections( currentItem ) {
		var fieldId = currentItem.attr('id').replace('frm_field_id_', '');
		var section = getSectionForFieldPlacement( currentItem );
		var formId = getFormIdForFieldPlacement( section );
		var sectionId = getSectionIdForFieldPlacement( section );

		jQuery.ajax({
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_update_field_after_move',
				form_id: formId,
				field: fieldId,
				section_id: sectionId,
				nonce: frmGlobal.nonce
			},
			success: function () {
				toggleSectionHolder();
				updateInSectionValue( fieldId,  sectionId );
			}
		});
	}

	// Update the in_section field value
	function updateInSectionValue( fieldId, sectionId ) {
		document.getElementById( 'frm_in_section_' + fieldId ).value = sectionId;
	}

	/**
	 * Add a new field by dragging and dropping it from the Fields sidebar
	 *
	 * @param {object} selectedItem
	 * @param {object} fieldButton
	 * @param {object} opts
     */
	function insertNewFieldByDragging( selectedItem, fieldButton, opts ) {
		var fieldType = fieldButton.attr('id');
		var currentItem = jQuery(selectedItem).data().uiSortable.currentItem;
		var section = getSectionForFieldPlacement( currentItem );
		var formId = getFormIdForFieldPlacement( section );
		var sectionId = getSectionIdForFieldPlacement( section );

		var loadingID = fieldType.replace('|', '-');
		currentItem.replaceWith('<span class="frm_visible_spinner spinner frmbutton_loadingnow" id="' + loadingID + '" ></span>');

		jQuery.ajax({
			type: 'POST', url: ajaxurl,
			data: {
				action: 'frm_insert_field',
				form_id: formId,
				field_type: fieldType,
				section_id: sectionId,
				nonce: frmGlobal.nonce
			},
			success: function (msg) {
				jQuery('.frm_no_fields').hide();
				jQuery('.frmbutton_loadingnow#' + loadingID).replaceWith(msg);

				var regex = /id="(\S+)"/;
				var match = regex.exec(msg);
				var $thisField = jQuery(document.getElementById(match[1]));
				$thisField.find('.frm_ipe_field_label').mouseover().click();

				updateFieldOrder();
				initiateMultiselect();

				var $thisSection = $thisField.find('ul.frm_sorting');
				if ($thisSection.length) {
					$thisSection.sortable(opts);
					$thisSection.parent('.frm_field_box').children('.frm_no_section_fields').show();
				} else {
					var $parentSection = $thisField.closest('ul.frm_sorting');
					toggleOneSectionHolder($parentSection);
				}
			}
		});
	}

	// don't allow page break, embed form, captcha, or section inside section field
	function allowDrop(ui){
		if ( ! ui.placeholder.parent().hasClass('start_divider') ) {
			return true;
		}

		// new field
		if ( ui.item.hasClass('frmbutton') ) {
			if ( ui.item.hasClass('frm_tbreak') || ui.item.hasClass('frm_tform') || ui.item.hasClass('frm_tdivider') ) {
				return false;
			}
			return true;
		}

		// moving an existing field
		return !(ui.item.hasClass('edit_field_type_break') || ui.item.hasClass('edit_field_type_form') ||
			ui.item.hasClass('edit_field_type_divider'));
	}

	function loadFields(field_id){
		var $thisField = jQuery(document.getElementById(field_id));
		var fields;
		if(jQuery.isFunction(jQuery.fn.addBack)){
			fields = $thisField.nextAll("*:lt(14)").addBack();
		}else{
			fields = $thisField.nextAll("*:lt(14)").andSelf();
		}
		fields.addClass('frm_load_now');

		var h = [];
		jQuery.each(fields, function(k,v){
			h.push(jQuery(v).find('.frm_hidden_fdata').html());
		});

		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_load_field', field:h, form_id:this_form_id, nonce:frmGlobal.nonce},
			success:function(html){
				html = html.replace(/^\s+|\s+$/g,'');
				if(html.indexOf('{') !== 0){
					jQuery('.frm_load_now').removeClass('.frm_load_now').html('Error');
					return;
				}
				html = jQuery.parseJSON(html);

				for(var key in html){
					jQuery('#frm_field_id_'+key).replaceWith(html[key]);
					setupSortable('#frm_field_id_'+key+'.edit_field_type_divider ul.frm_sorting');
				}

				var $nextSet = $thisField.nextAll('.frm_field_loading:not(.frm_load_now)');
				if($nextSet.length){
					loadFields($nextSet.attr('id'));
				}else{
					// go up a level
					$nextSet = jQuery(document.getElementById('new_fields')).find('.frm_field_loading:not(.frm_load_now)');
					if($nextSet.length){
						loadFields($nextSet.attr('id'));
					}
				}

				initiateMultiselect();
			}
		});
	}
	
	function addFieldClick(){
		/*jshint validthis:true */
		var $thisObj = jQuery(this);
		var field_type = $thisObj.closest('li').attr('id');
		var form_id = this_form_id;
		var $button = $thisObj.closest('.frmbutton');
		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{
				action:'frm_insert_field',
				form_id:form_id,
				field_type:field_type,
				section_id:0,
				nonce:frmGlobal.nonce
			},
			success:function(msg){
				jQuery('.frm_no_fields').hide();
				$newFields.append(msg);
				afterAddField( msg, true );
			}
		});
		return false;
	}

	function duplicateField(){
		/*jshint validthis:true */
		var thisField = jQuery(this).closest('li');
		var field_id = thisField.data('fid');
		var children = fieldsInSection(field_id);
		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_duplicate_field', field_id:field_id, form_id:this_form_id, children:children, nonce:frmGlobal.nonce},
			success:function(msg){
				thisField.after(msg);
				updateFieldOrder();
				afterAddField( msg, false );
			}
		});
		return false;
	}

	function afterAddField( msg, addFocus ){
		var regex = /id="(\S+)"/;
		var match = regex.exec(msg);
		section = '#'+match[1]+'.edit_field_type_divider ul.frm_sorting';
		setupSortable(section);
		if ( addFocus ) {
			jQuery('#'+match[1]+' .frm_ipe_field_label').mouseover().click();
			toggleOneSectionHolder(jQuery(section));
		}
		initiateMultiselect();
	}

	function checkCalculationCreatedByUser() {
		var calculation = this.value;
		var warningMessage = checkMatchingParens( calculation );
		warningMessage += checkShortcodes( calculation, this );

		if ( warningMessage !== '' ) {
			alert( calculation + "\n\n" + warningMessage );
		}
	}

	/**
	 * Checks a string for parens, brackets, and curly braces and returns a message if any unmatched are found.
	 * @param formula
	 * @returns {string}
	 */
	function checkMatchingParens( formula ) {

		var stack = [],
			formula_array = formula.split( '' ),
			length = formula_array.length,
			opening = [ "{", "[", "(" ],
			closing = {
				"}": "{",
				")": "(",
				"]": "[",
			},
			unmatchedClosing = [],
			msg = '',
			i, next, top;

		for ( i = 0; i < length; i++ ) {
			if ( opening.includes( formula_array[ i ] ) ) {
				stack.push( formula_array[ i ] );
				continue;
			}
			if ( closing.hasOwnProperty( formula_array[ i ] ) ) {
				top = stack.pop();
				if ( top !== closing[ formula_array[ i ] ] ) {
					unmatchedClosing.push( formula_array[ i ] )
				}
			}
		}

		if ( stack.length > 0 || unmatchedClosing.length > 0 ) {
			msg = frm_admin_js.unmatched_parens + '\n\n';
			return msg;
		}

		return '';
	}

	/**
	 * Checks a calculation for shortcodes that shouldn't be in it and returns a message if found.
	 * @param calculation
	 * @param inputElement
	 * @returns {string}
	 */
	function checkShortcodes( calculation, inputElement ) {
		var msg = checkNonNumericShortcodes( calculation, inputElement );
		msg += checkNonFormShortcodes( calculation );

		return msg;
	}

	/**
	 * Checks if a numeric calculation has shortcodes that output non-numeric strings and returns a message if found.
	 * @param calculation
	 *
	 * @param inputElement
	 * @returns {string}
	 */
	function checkNonNumericShortcodes( calculation, inputElement ) {

		var msg = '';

		if ( isTextCalculation( inputElement ) ) {
			return msg;
		}

		var nonNumericShortcodes = getNonNumericShortcodes();

		if ( nonNumericShortcodes.test( calculation ) ) {
			msg = frm_admin_js.text_shortcodes + "\n\n";
		}

		return msg;
	}

	/**
	 * Determines if the calculation input is from a text calculation.
	 *
	 * @param inputElement
	 */
	function isTextCalculation( inputElement ) {
		return jQuery( inputElement ).siblings( "label[for^='calc_type']" ).children( "input" ).prop( "checked" );
	}

	/**
	 * Returns a regular expression of shortcodes that can't be used in numeric calculations.
	 * @returns {RegExp}
	 */
	function getNonNumericShortcodes() {
		return /\[(date|time|email|ip)\]/;
	}

	/**
	 * Checks if a string has any shortcodes that do not belong in forms and returns a message if any are found.
	 * @param formula
	 * @returns {string}
	 */
	function checkNonFormShortcodes( formula ) {
		var nonFormShortcodes = getNonFormShortcodes(),
			msg = '';

		if ( nonFormShortcodes.test( formula ) ) {
			msg += frm_admin_js.view_shortcodes + "\n\n";
		}

		return msg;
	}

	/**
	 * Returns a regular expression of shortcodes that can't be used in forms but can be used in Views, Email
	 * Notifications, and other Formidable areas.
	 *
	 * @returns {RegExp}
	 */
	function getNonFormShortcodes() {
		return /\[(if\b|foreach|created-at|created-by|updated-at|updated-by)|((key|id)\])/;
	}

	function popCalcFields(v){
		/*jshint validthis:true */
		var p;
		if(!v.type){
			if(!jQuery(v).closest('div.widget').children('.widget-inside').is(':hidden')) {
				return;
			}
			p = jQuery(v).closest('.frm_field_box');
		}else{
			p = jQuery(this).closest('.frm_field_box');
		}

		if(!p.find('.use_calc').length || !p.find('.use_calc').is(':checked')){
			return;
		}

		var form_id=jQuery('input[name="id"]').val();
		var field_id=p.find('input[name="frm_fields_submitted[]"]').val();	
		jQuery.ajax({
			type:'POST',url:ajaxurl,
            data:{action:'frm_populate_calc_dropdown', field_id:field_id, form_id:form_id, nonce:frmGlobal.nonce},
			success:function(msg){p.find('.frm_shortcode_select').replaceWith(msg);}
		});	
	}

	function toggleInvalidMsg(){
		/*jshint validthis:true */
		var typeDropdown, fieldType,
			fieldId = this.id.replace('frm_format_', ''),
			hasValue = this.value !== '';

		typeDropdown = document.getElementsByName('field_options[type_'+ fieldId +']')[0];
		fieldType = typeDropdown.options[typeDropdown.selectedIndex].value;

		if ( fieldType === 'text' ) {
			toggleValidationBox( hasValue, '.frm_invalid_msg' + fieldId );
		}
	}

	function markRequired(){
		/*jshint validthis:true */
		var thisid = this.id.replace('frm_', '');
		var field_id = thisid.replace('req_field_', '');
		var checked = this.checked;

		toggleValidationBox( checked, '.frm_required_details' + field_id );

		var atitle = 'Click to Mark as Not Required';
		if(checked){
			var $reqBox = jQuery('input[name="field_options[required_indicator_'+field_id+']"]');
			if($reqBox.val() === '') {
				$reqBox.val('*');
			}
		}else{
			atitle='Click to Mark as Required';
		}
		jQuery(document.getElementById(thisid)).removeClass('frm_required0 frm_required1').addClass('frm_required'+(checked ? 1 : 0)).attr('title', atitle);
	}

	function toggleValidationBox( hasValue, messageClass ) {
		$msg = jQuery( messageClass );
		if ( hasValue ) {
			$msg.fadeIn('fast').closest('.frm_validation_msg').fadeIn('fast');
		}else{
			//Fade out validation options
			var v = $msg.fadeOut('fast').closest('.frm_validation_box').children(':not('+ messageClass +'):visible').length;
			if (v === 0) {
				$msg.closest('.frm_validation_msg').fadeOut('fast');
			}
		}
	}

	function clickRequired(){
		/*jshint validthis:true */
		jQuery(document.getElementById('frm_'+this.id)).click();
	}

	function markUnique(){
		/*jshint validthis:true */
		var field_id = jQuery(this).closest('li').data('fid');
		var $thisField = jQuery('.frm_unique_details'+field_id);
		if(this.checked){
			$thisField.fadeIn('fast').closest('.frm_validation_msg').fadeIn('fast');
			$unqDetail = jQuery('.frm_unique_details'+field_id+' input');
			if($unqDetail.val() === ''){
				$unqDetail.val(frm_admin_js.default_unique);
			}
		}else{
			var v=$thisField.fadeOut('fast').closest('.frm_validation_box').children(':not(.frm_unique_details'+field_id+'):visible').length;
			if(v === 0){
				$thisField.closest('.frm_validation_msg').fadeOut('fast');
			}
		}
	}

	//Fade confirmation field and validation option in or out
	function addConf(){
		/*jshint validthis:true */
		var field_id = jQuery(this).closest('li').data('fid');
		var val = jQuery(this).val();
		var $thisField = jQuery(document.getElementById('frm_field_id_'+field_id));

		toggleValidationBox( val !== '', '.frm_conf_details' + field_id );

		if(val !== ''){
			//Add default validation message if empty
			var valMsg = jQuery('.frm_validation_box .frm_conf_details'+field_id+' input');
			if(valMsg.val() === ''){
				valMsg.val(frm_admin_js.default_conf);
			}

			setConfirmationFieldDescriptions( field_id );

			//Add or remove class for confirmation field styling
			if(val === 'inline'){
				$thisField.removeClass('frm_conf_below').addClass('frm_conf_inline');
			}else if(val === 'below') {
				$thisField.removeClass('frm_conf_inline').addClass('frm_conf_below');
			}
		}else{
			setTimeout(function(){
				$thisField.removeClass('frm_conf_inline frm_conf_below');
			},200);
		}
	}

	function setConfirmationFieldDescriptions( field_id ) {
		var fieldType = document.getElementsByName( 'field_options[type_' + field_id + ']' )[0].value;

		var fieldDescription = document.getElementById( 'field_description_' + field_id );
		var hiddenDescName = 'field_options[description_' + field_id + ']';
		var newValue = frm_admin_js['enter_' + fieldType];
		maybeSetNewDescription( fieldDescription, hiddenDescName, newValue );

		var confFieldDescription = document.getElementById( 'conf_field_description_' + field_id );
		var hiddenConfName = 'field_options[conf_desc_' + field_id + ']';
		var newConfValue = frm_admin_js['confirm_' + fieldType];
		maybeSetNewDescription( confFieldDescription, hiddenConfName, newConfValue );
	}

	function maybeSetNewDescription( descriptionDiv, hiddenName, newValue ) {
		if ( descriptionDiv.innerHTML === frm_admin_js.desc ) {

			// Set the visible description value and the hidden description value
			descriptionDiv.innerHTML = newValue;
			document.getElementsByName( hiddenName )[0].value = newValue;
		}
	}

    //Add new option or "Other" option to radio/checkbox/dropdown
    function addFieldOption(){
        /*jshint validthis:true */

		// increment when the button is clicked too close together
		var clicks = parseInt( this.dataset.clicks );
		this.dataset.clicks = clicks + 1;

        var field_id = jQuery(this).closest('li').data('fid');
        var opt_type = jQuery(this).data('opttype');
		var opt_key = 1;
		var lastOpt = jQuery('#frm_field_'+ field_id +'_opts li:last');
		if(lastOpt.length){
			opt_key = lastOpt.attr('id').replace('frm_delete_field_'+ field_id +'-', '').replace('_container', '').replace('other_', '');
			opt_key = parseInt(opt_key) + 1 + clicks;
		}

        //Update hidden field
        if ( opt_type === 'other' ) {
            document.getElementById('other_input_' + field_id).value = 1;

            //Hide "Add Other" option now if this is radio field
            var ftype = jQuery(this).data('ftype');
            if ( ftype === 'radio' || ftype === 'select' ) {
                jQuery(this).fadeOut('slow');
            }
        }

		var data = {
			action:'frm_add_field_option', field_id:field_id,
			opt_key:opt_key,
			opt_type:opt_type, nonce:frmGlobal.nonce
		};
        jQuery.post(ajaxurl,data,function(msg){
            jQuery(document.getElementById('frm_field_'+field_id+'_opts')).append(msg);
			resetDropdownOpts(field_id);
        });
    }

	function toggleMultSel(){
		/*jshint validthis:true */
		var field_id = jQuery(this).closest('li.frm_field_box').data('fid');
		if(this.value === 'select'){
			jQuery(document.getElementById('frm_multiple_cont_'+field_id)).fadeIn('fast');
		}else{
			jQuery(document.getElementById('frm_multiple_cont_'+field_id)).fadeOut('fast');
		}
	}

	function toggleSepValues(){
		/*jshint validthis:true */
		var field_id = jQuery(this).closest('li').data('fid');
		toggle( jQuery('.field_'+field_id+'_option_key') );
		jQuery('.field_'+field_id+'_option').toggleClass('frm_with_key');
		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_update_ajax_option', field:field_id, separate_value:'1', nonce:frmGlobal.nonce}
		});
	}

	function toggleMultiselect() {
		/*jshint validthis:true */
		var dropdown = jQuery(this).closest('li').find('.frm_form_fields select');
		if( this.checked ){
			dropdown.attr('multiple', 'multiple');
		}else{
			dropdown.removeAttr('multiple');
		}
	}

	/**
	 * Toggle a default value icon
	 *
	 * @since 2.02.13
	 *
	 * @param {Object} event
	 * @param {string} event.data.iconType
	 */
	function toggleDefaultValueIcon( event ) {
		/*jshint validthis:true */
		var type = event.data.iconType;
		var messages = getTooltipMessages( type );
		if ( ! ( 'active' in messages ) ) {
			return;
		}

		var switch_to = '0';
		var tooltipMessage = messages.active;
		if ( this.className.indexOf( 'frm_inactive_icon' ) !== -1 ) {
			switch_to = '1';
			tooltipMessage = messages.inactive;
		}

		var $icon = jQuery(this);

		$icon.toggleClass( 'frm_inactive_icon' );

		changeBootstrapTooltipText( $icon, tooltipMessage );

		var field_id = $icon.closest( 'li.form-field') .data( 'fid' );
		jQuery('input[name="field_options[' + type + '_'+ field_id + ']"]').val( switch_to );
	}

	/**
	 * Get the tooltip messages for a specific icon
	 *
	 * @since 2.02.13
	 * @param {string} type
	 * @returns {Object}
	 */
	function getTooltipMessages( type ) {
		var messages = {};

		if( type === 'clear_on_focus' ) {
			messages.active = frm_admin_js.no_clear_default;
			messages.inactive = frm_admin_js.clear_default;
		} else if ( type === 'default_blank' ) {
			messages.active = frm_admin_js.valid_default;
			messages.inactive = frm_admin_js.no_valid_default;
		}

		return messages;
	}

	/**
	 * Change the text on a Bootstrap tooltip
	 *
	 * @since 2.02.13
	 * @param {Object} $element
	 * @param {string} newText
     */
	function changeBootstrapTooltipText( $element, newText ) {
		$element.attr('title', newText );
		$element.tooltip('fixTitle');
		$element.tooltip('show');
	}

	function deleteFieldOption(){
		/*jshint validthis:true */
		var parentLi = this.parentNode;
		var parentUl = parentLi.parentNode;
		var field_id = this.getAttribute('data-fid');

		jQuery(parentLi).fadeOut('slow', function(){
			jQuery(parentLi).remove();

			var hasOther = jQuery(parentUl).find('.frm_other_option');
			if ( hasOther.length < 1 ) {
				document.getElementById('other_input_' + field_id).value = 0;
				jQuery('#other_button_' + field_id).fadeIn('slow');
			}
		});
	}

	function clickDeleteField(){
		/*jshint validthis:true */
        var confirm_msg = frm_admin_js.conf_delete;
        // If deleting a section, add an extra message
        if ( this.parentNode.className === 'divider_section_only' ) {
            confirm_msg += '\n\n' + frm_admin_js.conf_delete_sec;
        }
        if ( confirm(confirm_msg) !== true ) {
            return false;
        }
		var field_id = jQuery(this).closest('li').data('fid');
		deleteField(field_id);

		if(jQuery(this).closest('li').hasClass('edit_field_type_divider')){
			jQuery(this).closest('li').find('li.frm_field_box').each(function(){
				//TODO: maybe delete only end section
				//if(n.hasClass('edit_field_type_end_divider')){
					deleteField(jQuery(this).data('fid'));
				//}
			});

		}
		toggleSectionHolder();
		return false;
	}

	function deleteField(field_id){
		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_delete_field', field_id:field_id, nonce:frmGlobal.nonce},
			success:function(msg){
				var $thisField = jQuery(document.getElementById('frm_field_id_'+field_id));
				$thisField.fadeOut('slow', function(){
					var $section = $thisField.closest('.start_divider');					
					$thisField.remove();
					if(jQuery('#new_fields li').length === 0){
						jQuery('.frm_no_fields').show();
					}else if($section.length){
						toggleOneSectionHolder($section);
					}
				});
			}
		});
	}
	
	function addFieldLogicRow(){
		/*jshint validthis:true */
		var id=jQuery(this).closest('li.form-field').data('fid');
		var form_id = this_form_id;
		var meta_name = 0;
		if(jQuery('#frm_logic_row_'+id+' .frm_logic_row').length>0){
			meta_name = 1 + parseInt(jQuery('#frm_logic_row_'+id+' .frm_logic_row:last').attr('id').replace('frm_logic_'+id+'_', ''));
		}
		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_add_logic_row', form_id:form_id, field_id:id, meta_name:meta_name, nonce:frmGlobal.nonce},
			success:function(html){
				jQuery(document.getElementById('logic_'+id)).fadeOut('slow', function(){
                    var logicRow = jQuery(document.getElementById('frm_logic_row_'+id));
					logicRow.append(html);
					logicRow.parent('.frm_logic_rows').fadeIn('slow');
				});
			}
		});
		return false;
	}

	function addWatchLookupRow(){
		/*jshint validthis:true */
		var id=jQuery(this).closest('li.form-field').data('fid');
		var form_id = this_form_id;
		var row_key = 0;
		var lookupBlockRows = document.getElementById( 'frm_watch_lookup_block_'+id  ).children;
		if ( lookupBlockRows.length > 0 ) {
			var lastRowId = lookupBlockRows[ lookupBlockRows.length - 1 ].id;
			row_key = 1 + parseInt( lastRowId.replace( 'frm_watch_lookup_' + id + '_', '' ) );
		}

		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_add_watch_lookup_row', form_id:form_id, field_id:id, row_key:row_key, nonce:frmGlobal.nonce},
			success:function(newRow){
				jQuery(document.getElementById('frm_add_watch_lookup_link_'+id)).fadeOut('slow', function(){
                    var watchRowBlock = jQuery(document.getElementById('frm_watch_lookup_block_'+id));
					watchRowBlock.append(newRow);
					watchRowBlock.fadeIn('slow');
				});
			}
		});
		return false;
	}

	function hideOrShowAutopopulateValue() {
		/*jshint validthis:true */
		var fieldId = this.id.replace( 'autopopulate_value_', '' );
		var sections = document.querySelectorAll( '.frm_autopopulate_value_section_' + fieldId );

		var l = sections.length;
		for ( var i = 0; i<l; i++ ) {
			if ( this.checked ) {
				sections[i].className = sections[i].className.replace( 'frm_hidden', '' );
			} else {
				sections[i].className = sections[i].className + ' frm_hidden';
			}
		}
	}

	function updateGetValueFieldSelection() {
		/*jshint validthis:true */
		var fieldID = this.id.replace( 'get_values_form_', '' );
		var fieldSelect = document.getElementById( 'get_values_field_' + fieldID );
		var fieldType = this.getAttribute('data-fieldtype');

		if ( this.value === '' ) {
			fieldSelect.options.length = 1;
		} else {
			var formID = this.value;
			jQuery.ajax({
				type:'POST',url:ajaxurl,
				data:{
					action:'frm_get_options_for_get_values_field',
					form_id:formID,
					field_type:fieldType,
					nonce:frmGlobal.nonce
				},
				success:function(fields){
					fieldSelect.innerHTML = fields;
				}
			});
		}
	}

	// Clear the Watch Fields option when Lookup field switches to "Text" option
	function maybeClearWatchFields() {
		/*jshint validthis:true */
		if ( this.value === 'text' ) {
			var fieldID = this.name.replace( 'field_options[data_type_', '' ).replace( ']', '' );

			var lookupBlock = document.getElementById( 'frm_watch_lookup_block_' + fieldID );
			if ( lookupBlock !== null ) {
				// Clear the Watch Fields option
				lookupBlock.innerHTML = '';

				// Hide the Watch Fields row
				lookupBlock.parentNode.parentNode.style.display = 'none';
			}
		}
	}

	function clickVis(e){
		/*jshint validthis:true */
		clickAction(this);
		if(!jQuery(e.target).is('.inplace_field, .frm_ipe_field_label, .frm_ipe_field_desc, .frm_ipe_field_conf_desc, .frm_ipe_field_option, .frm_ipe_field_option_key')){
			jQuery('.inplace_field').blur();
		}
	}

	function clickSectionVis(e){
		/*jshint validthis:true */
		if(typeof jQuery(e.target).closest('.widget-top').attr('class') !== 'undefined'){
			clickWidget(jQuery(e.target).closest('.widget-top'));
		}

        // Do not stop propagation if opening TB_iframe
		if ( e.target.className.indexOf('thickbox') === -1 ) {
			e.stopPropagation();
			var isButton = jQuery(e.target).closest('.frm-btn-group');
			if ( isButton !== null ) {
				// allow bootstrap dropdown to open
				jQuery(isButton).find('[data-toggle=dropdown]').dropdown('toggle');
			}
		}

		clickAction(this);
		if(!jQuery(e.target).is('.inplace_field, .frm_ipe_field_label, .frm_ipe_field_desc, .frm_ipe_field_conf_desc, .frm_ipe_field_option, .frm_ipe_field_option_key')){
			jQuery('.inplace_field').blur();
		}
	}
	
	function toggleRepeat(){
		/*jshint validthis:true */
		var field_id = jQuery(this).closest('li.frm_field_box').data('fid');
		var main_form_id = jQuery('input[name="id"]').val();
		var prev_form = jQuery('input[name="field_options[form_select_'+field_id+']"]').val();

		if(this.checked){
			jQuery('#frm_field_id_'+field_id+' .show_repeat_sec').fadeIn('slow');
			jQuery(this).closest('li.frm_field_box').addClass('repeat_section').removeClass('no_repeat_section');

			toggleFormid(field_id, prev_form, main_form_id, 1);
		}else{
			if(confirm(frm_admin_js.conf_no_repeat)){
				jQuery('#frm_field_id_'+field_id+' .show_repeat_sec').fadeOut('slow');
				jQuery(this).closest('li.frm_field_box').removeClass('repeat_section').addClass('no_repeat_section');
				toggleFormid(field_id, prev_form, main_form_id, 0);
			}else{
				this.checked = true;
			}
		}
	}

	function toggleRepeatButtons(){
		/*jshint validthis:true */
		var $thisField = jQuery(this).closest('.frm_field_box');
		$thisField.find('.repeat_icon_links').removeClass('repeat_format repeat_formatboth repeat_formattext').addClass('repeat_format'+this.value);
		if ( this.value === 'text' || this.value === 'both' ) {
			$thisField.find('.frm_repeat_text').show();
			$thisField.find('.repeat_icon_links a').addClass('frm_button');
		}else{
			$thisField.find('.frm_repeat_text').hide();
			$thisField.find('.repeat_icon_links a').removeClass('frm_button');
		}
	}

	function checkRepeatLimit() {
		/*jshint validthis:true */
		var val = this.value;
		if ( val < 2 || val > 200) {
			alert(frm_admin_js.repeat_limit_min);
			this.value = '';
		}
	}

	function updateRepeatText(obj, addRemove){
		var $thisField = jQuery(obj).closest('.frm_field_box');
		$thisField.find('.frm_'+ addRemove +'_form_row .frm_repeat_label').text(obj.value);
	}
	
	function toggleFormid(field_id, form_id, main_form_id, checked){
		// change form ids of all fields in section
		var children = fieldsInSection(field_id);
		var field_name = document.getElementById('field_label_' + field_id).innerHTML;
		jQuery.ajax({type:'POST',url:ajaxurl,
			data:{action:'frm_toggle_repeat', form_id:form_id, parent_form_id:main_form_id, checked:checked, field_id:field_id, field_name:field_name, children:children, nonce:frmGlobal.nonce},
			success:function(id){
				//return form id to hidden field
				jQuery('input[name="field_options[form_select_'+field_id+']"]').val(id);

				// Update data-formid on section field
				var fieldListElement = document.getElementById( 'frm_field_id_' + field_id );
				if ( id !== '' ) {
					fieldListElement.setAttribute('data-formid', id);
				} else {
					fieldListElement.setAttribute('data-formid', main_form_id);
				}
			}
		});
	}

	function fieldsInSection(id){
		var children = [];
		jQuery(document.getElementById('frm_field_id_'+id)).find('li.frm_field_box:not(.no_repeat_section .edit_field_type_end_divider)').each(function(){
			children.push(jQuery(this).data('fid'));
		});
		return children;
	}
	
	function toggleFormTax(){
		/*jshint validthis:true */
		var id = jQuery(this).closest('li.form-field').data('fid');
		var val = this.value;
		var $showFields = document.getElementById('frm_show_selected_fields_'+id);
		var $showForms = document.getElementById('frm_show_selected_forms_'+id);
		
		jQuery($showForms).find('select').val('');
		if(val === 'form'){
			$showForms.style.display = 'inline';
			empty($showFields);
		}else{
			$showFields.style.display = 'none';
			$showForms.style.display = 'none';
			getTaxOrFieldSelection(val,id);
		}

	}

    function triggerDefaults(){
        /*jshint validthis:true */
        var n = this.name;
        if( typeof n === 'undefined'){
            return false;
        }
        n = n.replace('[other]', '');
        var end = n.indexOf(']');
        n = n.substring(10, end);
        showDefaults(n, jQuery(this).val());
    }
	
	function blurField(e){
		if(e.which == 13){
			jQuery('.inplace_field').blur();
			return false;
		}
	}

	function setIPELabel(){
		/*jshint validthis:true */
		jQuery(this).editInPlace({
			value_required:'true',
			default_text:frm_admin_js.no_label,
			callback:function(x,text){
				jQuery(this).next('input').val(text);
				var new_text = text || frm_admin_js.desc;
				return new_text;
			}
		});
	}

	function setIPEDesc(){
		/*jshint validthis:true */
		jQuery(this).editInPlace({
			default_text:frm_admin_js.desc,
			field_type:'textarea',textarea_rows:2,
			callback:function(x,text){
				jQuery(this).next('input').val(text);
				var new_text = text || frm_admin_js.desc;
				return new_text;
			},
			postclose:function(){
				if(jQuery(this).html() === frm_admin_js.desc){
					jQuery(this).addClass('frm-show-click');
				}else{
					jQuery(this).removeClass('frm-show-click');
				}
			}
		});
	}

	function setIPEOpts(){
		/*jshint validthis:true */
		var id = jQuery(this).attr('id');
		var fieldId = jQuery(this).closest('.frm_field_box').data('fid');
		jQuery(this).editInPlace({
			default_text:frm_admin_js.blank,
			callback:function(d,text){
				var input = jQuery(this).next('input');
				input.val(text);
				var new_text = text || frm_admin_js.blank;
				checkUniqueOpt(id,text);
				maybeSetSavedVal(id, fieldId, text, input);
				return new_text;
			},
			postclose:function(){
				resetDropdownOpts(fieldId);
			}
		});
	}

	function resetDropdownOpts(id){
		var field = document.getElementById('frm_dropdown_'+id);
		if ( field !== null ) {
			fillDropdownOpts(field, id);
		}
	}

	function fillDropdownOpts(field, sourceID, includeBlank){
		if ( field !== null ) {
			removeDropdownOpts(field);
			var opts = jQuery('input[name^="field_options[options_'+sourceID+'"][name$="[value]"]');
			var l = opts.length;
			jQuery.each(opts, function() {
				var labelName = this.name.replace('[value]', '[label]');
				var value = this.value;
				if ( includeBlank === 'blank' && value !== '' ) {
				    var blankOpt = document.createElement('option');
				    blankOpt.value = '';
				    field.appendChild(blankOpt);
					includeBlank = false;
				}

			    var opt = document.createElement('option');
			    opt.value = value;
			    opt.innerHTML = jQuery('input[name="'+labelName+'"]').val();
			    field.appendChild(opt);
			});
		}
	}

	function removeDropdownOpts(field){
	    var i;
	    for(i = field.options.length - 1 ; i >= 0 ; i--){
	        field.remove(i);
	    }
	}

	function checkUniqueOpt(id,text){
		if(id.indexOf('field_key_') === 0){
			var a=id.split('-');
			jQuery.each(jQuery('label[id^="'+a[0]+'"]'), function(k,v){
				var c=false;
				if(!c && jQuery(v).attr('id') != id && jQuery(v).html() == text){
					c = true;
					alert('Saved values cannot be identical.');
				}
			});
		}
	}

	function maybeSetSavedVal(id, fieldId, text, input){
		var isDisplayVal = id.indexOf('field_key_') !== 0;
		if ( isDisplayVal ){
			var separateVals = document.getElementById('separate_value_'+fieldId).checked;
			if ( !separateVals ){
				var cont = input.next('.frm_option_key');
				cont.find('label').html(text);
				cont.find('input').val(text);
			}
		}
	}

	function setStarValues() {
		/*jshint validthis:true */
		var fieldID = this.id.replace('radio_maxnum_', '');
		var container = jQuery('#field_'+ fieldID +'_inner_container .frm-star-group');
		var fieldKey = document.getElementsByName('field_options[field_key_'+ fieldID +']')[0].value;
		container.html('');

		var min = 1;
		var max = this.value;
		if ( min > max ) {
			max = min;
		}

		for ( var i = min; i<=max; i++ ) {
			container.append('<input type="hidden" name="field_options[options_'+ fieldID +']['+ i +']" value="'+ i +'"><input type="radio" name="item_meta['+ fieldID +']" id="field_'+ fieldKey +'-'+ i +'" value="'+ i +'" /><label for="field_'+ fieldKey +'-'+ i +'" class="star-rating"></label>');
		}
	}

	function setScaleValues() {
		/*jshint validthis:true */
		var isMin = this.id.indexOf('minnum') !== -1;
		var fieldID = this.id.replace('scale_maxnum_', '').replace('scale_minnum_', '');
		var min = this.value;
		var max = this.value;
		if ( isMin ) {
			max = document.getElementById('scale_maxnum_'+ fieldID).value;
		} else {
			min = document.getElementById('scale_minnum_'+ fieldID).value;
		}

		updateScaleValues( min, max, fieldID );
	}

	function updateScaleValues( min, max, fieldID ) {
		var container = jQuery('#field_'+ fieldID +'_inner_container .frm_form_fields');
		container.html('');

		if ( min >= max ) {
			max = min + 1;
		}

		for ( var i = min; i<=max; i++ ) {
			container.append('<div class="frm_scale"><label><input type="hidden" name="field_options[options_'+ fieldID +']['+ i +']" value="'+ i +'"> <input type="radio" name="item_meta['+ fieldID +']" value="'+ i +'"> '+ i +' </label></div>');
		}
		container.append('<div class="clear"></div>');
	}

	function getFieldValues(){
		/*jshint validthis:true */
		var is_taxonomy,
			val = this.value;

		if ( val ) {
			var parentIDs = this.parentNode.id.replace('frm_logic_', '').split('_');
			var fieldID = parentIDs[0];
			var metaKey = parentIDs[1];
			var valueField = document.getElementById('frm_field_id_'+val);
			var valueFieldType = valueField.getAttribute('data-ftype');
			var fill = document.getElementById('frm_show_selected_values_'+fieldID+'_'+metaKey);
			var optionName = 'field_options[hide_opt_'+ fieldID +'][]';
			var optionID = 'frm_field_logic_opt_' + fieldID;
			var input = false;
			var showSelect = (valueFieldType == 'select' || valueFieldType == 'checkbox' || valueFieldType == 'radio' );
			var showText = ( valueFieldType == 'text' || valueFieldType == 'email' || valueFieldType == 'phone' || valueFieldType == 'url' || valueFieldType == 'number' );

			if ( showSelect ) {
				is_taxonomy = document.getElementById( 'frm_has_hidden_options_' + val );
				if ( is_taxonomy !== null ) {
					// get the category options with ajax
					showSelect = false;
				}
			}

			if ( showSelect || showText ) {
				fill.innerHTML = '';
				if ( showSelect ) {
					input = document.createElement('select');
				} else {
					input = document.createElement('input');
					input.type = 'text';
				}
				input.name = optionName;
				input.id = optionID +'_'+ metaKey;
				fill.appendChild(input);

				if ( showSelect ) {
					var fillField = document.getElementById(input.id);
					fillDropdownOpts(fillField, val, 'blank');
				}
			} else {
				var thisType = this.getAttribute('data-type');
				frmGetFieldValues(val, fieldID, metaKey, thisType);
			}
		}
	}

	function getFieldSelection(){
		/*jshint validthis:true */
		var form_id = this.value;
		if(form_id){
			var field_id = jQuery(this).closest('li.form-field').data('fid');
            getTaxOrFieldSelection(form_id, field_id);
		}
	}

    function getTaxOrFieldSelection(form_id, field_id){
		if(form_id){
            jQuery.ajax({
				type:'POST',url:ajaxurl,
                data:{action:'frm_get_field_selection',field_id:field_id,form_id:form_id,nonce:frmGlobal.nonce},
                success:function(msg){ jQuery("#frm_show_selected_fields_"+field_id).html(msg).show();} 
            });
		}
    }

	function serializeSort() {
		var array = [];
		jQuery('#new_fields').each(function(i){
			jQuery('li.frm_field_box', this).each(function(e) {
				array.push('frm_field_id['+ e +']='+ this.getAttribute('data-fid'));
			});
		});
		return array.join('&');
	}

	function updateFieldOrder(){
		var order = serializeSort();
		jQuery.ajax({
			type:"POST",url:ajaxurl,
			data:'action=frm_update_field_order&nonce='+frmGlobal.nonce+'&'+order
		});
	}
	
	function toggleSectionHolder(){
		jQuery('.start_divider').each(function(){
			toggleOneSectionHolder(jQuery(this));
		});
	}
	
	function toggleOneSectionHolder($section){
		if($section.length === 0){
			return;
		}
		if($section.children('li').length < 2){
			$section.parent('.frm_field_box').children('.frm_no_section_fields').show();
		}else{
			$section.parent('.frm_field_box').children('.frm_no_section_fields').hide();
		}
	}
	
	function slideDown(){
		/*jshint validthis:true */
		var id = jQuery(this).data('slidedown');
		var $thisId = jQuery(document.getElementById(id));
		if ($thisId.is(":hidden")) {
			$thisId.slideDown('fast');
			this.style.display = 'none';
		}
		return false;
	}

	function slideUp(){
		/*jshint validthis:true */
		var id = jQuery(this).data('slideup');
		var $thisId = jQuery(document.getElementById(id));
		$thisId.slideUp('fast');
		$thisId.siblings('a').show();
		return false;
	}

	function createFromTemplate() {
		var dropdown = document.getElementById('frm_create_template_dropdown');
		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{
				action:'frm_create_from_template', this_form:this_form_id,
				id:dropdown.options[dropdown.selectedIndex].value, nonce:frmGlobal.nonce
			},
			success:function(url){
				window.location = url;
			}
		});
	}

	function submitBuild(){
		/*jshint validthis:true */
		var $thisEle = jQuery(this);
		var p = $thisEle.html();

		preFormSave(this);

		var $form = jQuery(document.getElementById('frm_build_form'));
		var v = JSON.stringify($form.serializeArray());

		jQuery(document.getElementById('frm_compact_fields')).val(v);
		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_save_form','frm_compact_fields':v, nonce:frmGlobal.nonce},
			success:function(msg){
				afterFormSave( $thisEle, p );

				var $postStuff = document.getElementById('frm_form_editor_container');
				var $html = document.createElement('div');
				$html.setAttribute('id', 'message');
				$html.setAttribute('class', 'frm_message updated');
				$html.style.padding = '5px';
				$html.innerHTML = msg;
				$postStuff.insertBefore($html, $postStuff.firstChild);
			},
			error:function(html){
				jQuery(document.getElementById('frm_js_build_form')).submit();
			}
		});
	}

	function submitNoAjax(){
		/*jshint validthis:true */
		preFormSave(this);

		var form = jQuery(document.getElementById('frm_build_form'));
		jQuery(document.getElementById('frm_compact_fields')).val(JSON.stringify(form.serializeArray()));
		jQuery(document.getElementById('frm_js_build_form')).submit();
	}

	function preFormSave(b){
		removeWPUnload();
		if(jQuery('form.inplace_form').length){
			jQuery('.inplace_save, .postbox').click();
		}

		$button = jQuery(b);
		if($button.attr('id') === 'save-post'){
			jQuery('input[name="status"]').val('draft');
		}else{
			jQuery('input[name="status"]').val('published');
		}

		if ( $button.hasClass('frm_button_submit') ) {
			$button.addClass('frm_loading_form');
			$button.html(frm_admin_js.saving);
		} else {
			$button.val(frm_admin_js.saving);
		}

		$button.prevAll('.spinner').css('visibility', 'visible').fadeIn();
		$button.nextAll('.frm-loading-img').css('visibility', 'visible');
	}

	function afterFormSave( $button, buttonVal ){
		$button.removeClass('frm_loading_form');
		$button.html(frm_admin_js.saved);

		$button.prevAll('.spinner').css('visibility', 'hidden').fadeOut();
		$button.nextAll('.frm-loading-img').css('visibility', 'hidden');

		setTimeout(function(){
			jQuery('.frm_message').fadeOut('slow');
			$button.fadeOut('slow', function(){
				$button.html(buttonVal);
				$button.show();
			});
		}, 2000);
	}

	/* Form settings */
	function showSuccessOpt(){
		/*jshint validthis:true */
		var c = 'success';
		if(this.name === 'options[edit_action]'){
			c = 'edit';
		}
		var v = jQuery(this).val();
		jQuery('.'+c+'_action_box').hide();
		if(v === 'redirect'){
			jQuery('.'+c+'_action_redirect_box.'+c+'_action_box').fadeIn('slow');
		}else if(v === 'page'){
			jQuery('.'+c+'_action_page_box.'+c+'_action_box').fadeIn('slow');
		}else{
			jQuery('.'+c+'_action_message_box.'+c+'_action_box').fadeIn('slow');
		}
	}
	
	function addFormAction(){
		/*jshint validthis:true */
		var actionId = getNewActionId();
		var type = jQuery(this).data('actiontype');
		var formId = this_form_id;

        jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_add_form_action', type:type, list_id:actionId, form_id:formId, nonce:frmGlobal.nonce},
			success:function(html){
				jQuery('#frm_notification_settings .widget-inside').css('display','none');//Close any open actions first
				jQuery('#frm_notification_settings').append(html);
				jQuery('.frm_form_action_settings').fadeIn('slow');
				jQuery('#frm_form_action_' + actionId + ' .widget-inside').css('display','block');
				jQuery('#frm_form_action_' + actionId).addClass('open');
				jQuery('#action_post_title_' + actionId).focus();

				//check if icon should be active
				checkActiveAction(type);
				initiateMultiselect();
			}
		});
	}

	function triggerDefaults(){
		var n = this.name;
		if( typeof n === 'undefined'){
			return false;
		}

		var fieldContainer = jQuery(this).closest('.frm_field_box');

		maybeShowDefaultValIcons(fieldContainer);
	}

	/**
	 * Show or hide the default value icons of a field
	 *
	 * @since 2.04.02
	 *
	 * @param {boolean} showDefaultValIcons
	 * @param {object} $innerField
	 */
	function showOrHideDefaultValIcons(showDefaultValIcons, $innerField) {
		var $defaultValueIcons = $innerField.find('.frm_default_val_icons');

		if (showDefaultValIcons) {
			$defaultValueIcons.css('visibility', 'visible').fadeIn('slow');
		} else {
			$defaultValueIcons.css('visibility', 'visible').fadeOut('slow');
		}
	}

	/**
	 * Determine if a field has default content and display the default value icons if it does
	 *
	 * @since 2.04.02
	 *
	 * @param {number} fieldId
	 */
	function maybeShowDefaultValIcons( $fieldInner ) {
		var showDefaultValIcons = false;
		var isComboOrConfirmationField = $fieldInner.find('.frm_multi_fields_container, .frm_inner_conf_container').length > 0;
		var inputList = $fieldInner.find('input[name^="item_meta"], input[id^="conf_field"], select[name^="item_meta"], textarea[name^="item_meta"]');

		jQuery(inputList).each( function(index) {

			if (jQuery(this).val()) {
				showDefaultValIcons = true;
				return false;
			} else if (!isComboOrConfirmationField) {
				return false;
			}

		});

		showOrHideDefaultValIcons(showDefaultValIcons, $fieldInner);
	}

	function getNewActionId() {
		var len = 0;
		if ( jQuery('.frm_form_action_settings:last').length ) {
			//Get number of previous action
			len = jQuery('.frm_form_action_settings:last').attr('id').replace('frm_form_action_', '');
		}
		len = parseInt(len) + 1;
		if ( typeof document.getElementById( 'frm_form_action_'+ len ) !== 'undefined'  ) {
			len = len + 100;
		}
		return len;
	}

	function clickAction(obj){
		var shouldScroll, selected, preTop, curOffset,
			selectedOffset = 0,
			selectedHeight = 0,
			$thisobj = jQuery(obj);

		if(obj.className.indexOf('selected') !== -1){
			return;
		}
		if(obj.className.indexOf('edit_field_type_end_divider') !== -1 && $thisobj.closest('.edit_field_type_divider').hasClass('no_repeat_section')){
			return;
		}

		selected = jQuery('li.ui-state-default.selected');

		// get offsets before anything changes
		preTop = document.documentElement.scrollTop || document.body.scrollTop;
		curOffset = $thisobj.offset().top;

		if ( selected.length )	{
			shouldScroll = isElementInViewport(selected);
			selectedOffset = selected.offset().top;
			selectedHeight = selected.height();
		}

		if(obj.className.indexOf('edit_field_type_divider') !== -1){
			$thisobj.find('.frm_default_val_icons').hide().css('visibility', 'hidden');
		}else{
			maybeShowDefaultValIcons($thisobj);
		}

		selected.removeClass('selected');
		$thisobj.addClass('selected');
		var newOffset = $thisobj.offset().top;

		if ( selected.length && shouldScroll && curOffset > newOffset ) {
			var curTop = document.documentElement.scrollTop || document.body.scrollTop; // body for Safari;
			document.documentElement.scrollTop = document.body.scrollTop = curTop - ( curOffset - newOffset );
		}
	}

	function isElementInViewport(el) {
		var t, height;

		if ( el instanceof jQuery ) {
			el = el[0];
		}

		t = el.offsetTop;
		height = el.offsetHeight;

		while ( el.offsetParent ) {
			el = el.offsetParent;
			t += el.offsetTop;
		}

		return (
			t < ( window.pageYOffset + window.innerHeight ) &&
			( t + height ) > window.pageYOffset
		);
	}

	function showEmailRow(){
		/*jshint validthis:true */
		var actionKey = jQuery(this).closest('.frm_form_action_settings').data('actionkey');
		var rowType = this.getAttribute( 'data-emailrow' );

		jQuery('#frm_form_action_' + actionKey + ' .frm_' + rowType + '_row').fadeIn('slow');
		jQuery(this).fadeOut('slow');
	}

	function hideEmailRow(){
		/*jshint validthis:true */
		var action_box = jQuery(this).closest('.frm_form_action_settings');
		var rowType = this.getAttribute( 'data-emailrow' );

		var emailRowSelector = '.frm_'+ rowType +'_row';
		var emailButtonSelector = '.frm_'+ rowType +'_button';

		jQuery(action_box).find(emailButtonSelector).fadeIn('slow');
		jQuery(action_box).find(emailRowSelector).fadeOut('slow', function(){
			jQuery(action_box).find(emailRowSelector + ' input').val('');
		});
	}

	function checkActiveAction(type){
		var limit = parseInt(jQuery('.frm_'+type+'_action').data('limit'));
		var len = jQuery('.frm_single_'+type+'_settings').length;
		if(len >= limit){
			jQuery('.frm_'+type+'_action').removeClass('frm_active_action').addClass('frm_inactive_action');
		}else{
			jQuery('.frm_'+type+'_action').removeClass('frm_inactive_action').addClass('frm_active_action');
		}
	}
	
	function addFormLogicRow(){
		/*jshint validthis:true */
		var id=jQuery(this).data('emailkey');
		var type = jQuery(this).closest('.frm_form_action_settings').find('.frm_action_name').val();
		var meta_name = 0;
		var form_id = document.getElementById('form_id').value;
		if(jQuery('#frm_form_action_'+id+' .frm_logic_row').length){
			meta_name = 1 + parseInt(jQuery('#frm_form_action_'+id+' .frm_logic_row:last').attr('id').replace('frm_logic_'+id+'_', ''));
		}
		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_add_form_logic_row', email_id:id, form_id:form_id, meta_name:meta_name, type:type, nonce:frmGlobal.nonce},
			success:function(html){
				jQuery(document.getElementById('logic_link_'+id)).fadeOut('slow', function(){
					var $logicRow = jQuery(document.getElementById('frm_logic_row_'+id));
					$logicRow.append(html);
					$logicRow.parent('.frm_logic_rows').fadeIn('slow');
				});
			}
		});
		return false;
	}

	/**
	 * Adds submit button Conditional Logic row and reveals submit button Conditional Logic
	 *
	 * @returns {boolean}
	 */
	function addSubmitLogic() {
		/*jshint validthis:true */
		var form_id = this_form_id;
		var meta_name = 0;
		if ( jQuery( '#frm_submit_logic_row .frm_logic_row' ).length > 0 ) {
			var last = jQuery( '#frm_submit_logic_row .frm_logic_row:last' );
			var submitRowID = last.attr( 'id' );
			var idFromSubmitRow = submitRowID.replace( 'frm_logic_submit_', '' );

			meta_name = 1 + parseInt( last.attr( 'id' ).replace( 'frm_logic_submit_', '' ) );
		}
		jQuery.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'frm_add_submit_logic_row',
				form_id: form_id,
				meta_name: meta_name,
				nonce: frmGlobal.nonce
			},
			success: function ( html ) {
				jQuery( document.getElementById( 'logic_link_submit' ) ).fadeOut( 'slow', function () {
					var $logicRow = jQuery( document.getElementById( 'frm_submit_logic_row' ) );
					$logicRow.append( html );
					$logicRow.parent( '.frm_submit_logic_rows' ).fadeIn( 'slow' );
				} );
			}
		} );
		return false;
	}

	/**
	 *  When the user selects a field for a submit condition, update corresponding options field accordingly.
	 */
	function addSubmitLogicOpts() {
		var fieldOpt = jQuery( this );
		var field_id = fieldOpt.find( ':selected' ).val();

		if ( field_id ) {
			var row = fieldOpt.data( 'row' );
			frmGetFieldValues( field_id, 'submit', row, '', 'options[submit_conditions][hide_opt][]' );
		}
	}

	function formatEmailSetting(){
		/*jshint validthis:true */
		var val = jQuery(this).val();
		var email = val.match(/(\s[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/gi);
		/*if(email !== null && email.length) {
			//has email
			//TODO: add < > if they aren't there
		}*/
	}

	function showFormMessages() {
		var action = document.getElementById('success_action');
		var selectedAction = action.options[action.selectedIndex].value;
		if ( selectedAction === 'message' ) {
			return true;
		}

		var show = false;
		var editable = document.getElementById('editable');
		if ( editable !== null ) {
			show = editable.checked && jQuery(document.getElementById('edit_action')).val() === 'message';
			if ( ! show ) {
				show = document.getElementById('save_draft').checked;
			}
		}
		return show;
	}

	function checkDupPost(){
		/*jshint validthis:true */
		var postField = jQuery('select.frm_single_post_field');
		postField.css('border-color', '');
		var $t = this;
		var v = jQuery($t).val();
		if(v === '' || v === 'checkbox'){
			return false;
		}
		postField.each(function(){
			if(jQuery(this).val() === v && this.name !== $t.name){
				this.style.borderColor = 'red';
				jQuery($t).val('');
				alert('Oops. You have already used that field.');
				return false;
			}
		});
	}

	function togglePostContent(){
		/*jshint validthis:true */
		var v = jQuery(this).val();
		if('' === v){
			jQuery('.frm_post_content_opt, select.frm_dyncontent_opt').hide().val('');
			jQuery('.frm_dyncontent_opt').hide();
		}else if('post_content' === v){
			jQuery('.frm_post_content_opt').show();
			jQuery('.frm_dyncontent_opt').hide();
			jQuery('select.frm_dyncontent_opt').val('');
		}else{
			jQuery('.frm_post_content_opt').hide().val('');
			jQuery('select.frm_dyncontent_opt').show();
		}
	}

	function fillDyncontent(){
		/*jshint validthis:true */
		var v = jQuery(this).val();
		var $dyn = jQuery(document.getElementById('frm_dyncontent'));
		if('' === v || 'new' === v){
			$dyn.val('');
			jQuery('.frm_dyncontent_opt').show();
		}else{
			jQuery.ajax({
				type:'POST',url:ajaxurl,
				data:{action:'frm_display_get_content', id:v, nonce:frmGlobal.nonce},
				success:function(val){
					$dyn.val(val);
					jQuery('.frm_dyncontent_opt').show();
				}
			});
		}
	}

    function switchPostType(){
        /*jshint validthis:true */
        // update all rows of categories/taxonomies
        var cat_rows = document.getElementById('frm_posttax_rows').childNodes;
        var post_type = this.value;
        var cur_select;
        var new_select;

        // Get new category/taxonomy options
        jQuery.ajax({
            type:'POST',url:ajaxurl,
            data:{action:'frm_replace_posttax_options', post_type:post_type, nonce:frmGlobal.nonce},
            success:function(html){

                // Loop through each category row, and replace the first dropdown
                for (i = 0; i < cat_rows.length ; i++) {
                    // Check if current element is a div
                    if ( cat_rows[i].tagName != 'DIV' ) {
                        continue;
                    }

                    // Get current category select
                    cur_select = cat_rows[i].getElementsByTagName('select')[0];

                    // Set up new select
                    new_select = document.createElement("select");
                    new_select.innerHTML = html;
                    new_select.className = cur_select.className;
                    new_select.name = cur_select.name;

                    // Replace the old select with the new select
                    cat_rows[i].replaceChild(new_select, cur_select);
                }
            }
        });
    }
	
	function addPosttaxRow(){
		/*jshint validthis:true */
		addPostRow( 'tax', this );
	}
	
	function addPostmetaRow(){
		/*jshint validthis:true */
		addPostRow( 'meta', this );
	}

	function addPostRow( type, button ){
		var id = jQuery('input[name="id"]').val();
		var settings = jQuery(button).closest('.frm_form_action_settings');
		var key = settings.data('actionkey');
		var post_type = settings.find('.frm_post_type').val();

		var meta_name = 0;
		if(jQuery('.frm_post'+type+'_row').length){
			var name = jQuery('.frm_post'+type+'_row:last').attr('id').replace('frm_post'+type+'_', '');
			if(jQuery.isNumeric(name)){
				meta_name = 1 + parseInt(name);
			}else{
				meta_name = 1;
			}
		}
		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{
				action:'frm_add_post'+type+'_row', form_id:id,
				meta_name:meta_name, tax_key:meta_name,
				post_type:post_type, action_key:key, nonce:frmGlobal.nonce
			},
			success:function(html){
				jQuery(document.getElementById('frm_post'+type+'_rows')).append(html);
				jQuery('.frm_add_post'+type+'_row.button').hide();

				if ( type === 'meta' ) {
					document.getElementById('postcustomstuff').style.display = 'block';
					jQuery('.frm_toggle_cf_opts').not(':last').hide();
				}
			}
		});
	}

	function getMetaValue(id, meta_name){
		var new_meta = meta_name;
		if(jQuery(document.getElementById(id+meta_name)).length>0){
			new_meta = getMetaValue(id,meta_name+1);	
		}
		return new_meta;
	}

	function changePosttaxRow(){
		/*jshint validthis:true */
		if(!jQuery(this).closest('.frm_posttax_row').find('.frm_posttax_opt_list').length) {
			return;
		}

		jQuery(this).closest('.frm_posttax_row').find('.frm_posttax_opt_list').html('<div class="spinner frm_spinner" style="display:block"></div>');

		var post_type = jQuery(this).closest('.frm_form_action_settings').find('select[name$="[post_content][post_type]"]').val();
		var action_key = jQuery(this).closest('.frm_form_action_settings').data('actionkey');
		var tax_key = jQuery(this).closest('.frm_posttax_row').attr('id').replace('frm_posttax_', '');
		var meta_name = jQuery(this).val();
		var show_exclude = jQuery(document.getElementById(tax_key+'_show_exclude')).is(':checked') ? 1 : 0;
		var field_id = jQuery('select[name$="[post_category]['+tax_key+'][field_id]"]').val();
		var id = jQuery('input[name="id"]').val();

		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_add_posttax_row', form_id:id, post_type:post_type, tax_key:tax_key, action_key:action_key,
				meta_name:meta_name, field_id:field_id, show_exclude:show_exclude, nonce:frmGlobal.nonce
			},
			success:function(html){
				var $tax = jQuery(document.getElementById('frm_posttax_'+tax_key));
				$tax.replaceWith(html);
			}
		});
	}

	function toggleCfOpts(){
		/*jshint validthis:true */
		var row = jQuery(this).closest('.frm_postmeta_row');
		var cancel = row.find('.frm_cancelnew');
		var select = row.find('.frm_enternew');
		if(row.find('select.frm_cancelnew').is(':visible')){
			cancel.hide();
			select.show();
		}else{
			cancel.show();
			select.hide();
		}

		row.find('input.frm_enternew, select.frm_cancelnew').val('');
		return false;
	}

	function toggleFormOpts(){
        /*jshint validthis:true */
		var changedOpt = jQuery(this);
		var val = changedOpt.val();
		if ( changedOpt.attr('type') === 'checkbox' ) {
			if ( this.checked === false ) {
				val = '';
			}
		}

		var toggleClass = changedOpt.data('toggleclass');
		if ( val === '' ) {
			jQuery('.'+toggleClass).hide();
		} else {
			jQuery('.'+toggleClass).show();
			jQuery('.hide_'+toggleClass+'_'+val).hide();
		}
	}

	function submitSettings(){
		/*jshint validthis:true */
		preFormSave(this);
		jQuery('.frm_form_settings').submit();
	}
	
	/* View Functions */
	function showCount(){
		/*jshint validthis:true */
		var value = jQuery(this).val();

		var $cont = document.getElementById('date_select_container');
		var tab = document.getElementById('frm_listing_tab');
		var label = tab.dataset.label;
		if(value === 'calendar'){
			jQuery('.hide_dyncontent, .hide_single_content').show();
			jQuery('.limit_container').hide();
			$cont.style.display = 'block';
		}else if(value === 'dynamic'){
			jQuery('.hide_dyncontent, .limit_container, .hide_single_content').show();
		}else if(value === 'one'){
			label = tab.dataset.one;
			jQuery('.hide_dyncontent, .limit_container, .hide_single_content').hide();
		}else{
			jQuery('.hide_dyncontent').hide();
			jQuery('.limit_container, .hide_single_content').show();
		}

		if(value !== 'calendar'){
			$cont.style.display = 'none';
		}
		tab.innerHTML = label;
	}
	
	function displayFormSelected(){
		/*jshint validthis:true */
		var form_id = jQuery(this).val();
		this_form_id = form_id; // set the global form id
		if (form_id === ''){
			return;
		}

		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_get_cd_tags_box',form_id:form_id, nonce:frmGlobal.nonce},
			success:function(html){
				jQuery('#frm_adv_info .categorydiv').html(html);
			}
		});

		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_get_date_field_select',form_id:form_id, nonce:frmGlobal.nonce},
			success:function(html){
				jQuery(document.getElementById('date_select_container')).html(html);
			}
		});
	}

    function clickTabsAfterAjax(){
		/*jshint validthis:true */
        var t = jQuery(this).attr('href');
        jQuery(this).parent().addClass('tabs').siblings('li').removeClass('tabs');
        jQuery(t).show().siblings('.tabs-panel').hide();
        return false;
    }

	function clickContentTab(){
		/*jshint validthis:true */
		link = jQuery(this);
		var t = link.attr('href');
		if(typeof t === 'undefined'){
			return false;
		}

		var c = t.replace('#', '.');
		link.closest('.nav-tab-wrapper').children('a').removeClass('nav-tab-active');
		link.addClass('nav-tab-active');
		jQuery('.nav-menu-content').not(t).not(c).hide();
		jQuery(t+','+c).show();
		
		return false;
	}

	function addOrderRow(){
		var l = 0;
		if(jQuery('#frm_order_options .frm_logic_rows div:last').length>0){
			l = jQuery('#frm_order_options .frm_logic_rows div:last').attr('id').replace('frm_order_field_', '');
		}
		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_add_order_row',form_id:this_form_id,order_key:(parseInt(l)+1), nonce:frmGlobal.nonce},
			success:function(html){
				jQuery('#frm_order_options .frm_logic_rows').append(html).prev('.frm_add_order_row').hide();
			}
		});
	}
	
	function addWhereRow(){
		var l = 0;
		if(jQuery('#frm_where_options .frm_logic_rows div:last').length){
			l = jQuery('#frm_where_options .frm_logic_rows div:last').attr('id').replace('frm_where_field_', '');
		}
		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_add_where_row',form_id:this_form_id,where_key:(parseInt(l)+1), nonce:frmGlobal.nonce},
			success:function(html){
				jQuery('#frm_where_options .frm_logic_rows').append(html).show().prev('.frm_add_where_row').hide();
			}
		});
	}
	
	function insertWhereOptions(){
		/*jshint validthis:true */
		var value = this.value;
		var where_key = jQuery(this).closest('.frm_where_row').attr('id').replace('frm_where_field_', '');
		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_add_where_options',where_key:where_key,field_id:value, nonce:frmGlobal.nonce},
			success: function(html){jQuery(document.getElementById('where_field_options_'+where_key)).html(html);}
		}); 
	}

	function hideWhereOptions(){
		/*jshint validthis:true */
		var value = this.value;
		var where_key = jQuery(this).closest('.frm_where_row').attr('id').replace('frm_where_field_', '');
		if( value === 'group_by' || value === 'group_by_newest' ){
			document.getElementById('where_field_options_'+where_key).style.display = 'none';
		}else{
			document.getElementById('where_field_options_'+where_key).style.display = 'inline-block';
		}
	}

	function setDefaultPostStatus() {
		var urlQuery = window.location.search.substring(1);
		if ( urlQuery.indexOf('action=edit') === -1 ) {
			document.getElementById('post-visibility-display').innerHTML = frm_admin_js.private;
			document.getElementById('hidden-post-visibility').value = 'private';
			document.getElementById('visibility-radio-private').checked = true;
		}
	}

	/* Customization Panel */
	function insertCode(){
		/*jshint validthis:true */
		insertFieldCode(jQuery(this), jQuery(this).data('code'));
		return false;
	}

	function insertFieldCode(element,variable){
		var element_id = element;
		if(typeof(element) === 'object'){
			element_id = element.closest('div').attr('class').split(' ')[1];
			if(element.hasClass('frm_noallow')){
				return;
			}
		}

		var rich = true;
		if(element_id){ 
			rich = jQuery('#wp-'+element_id+'-wrap.wp-editor-wrap').length > 0;
		}

		if(element_id.substring(0,11) === 'frm_classes'){
			variable=variable+' ';
		}else{
			variable='['+variable+']';
		}
		if(rich){
			wpActiveEditor=element_id;
			send_to_editor(variable);
			return;
		}
		var content_box=jQuery(document.getElementById(element_id));
		if(!content_box) {
			return false;
		}

		if(variable === '[default-html]' || variable === '[default-plain]'){
			var p = 0;
			if(variable === '[default-plain]'){
				p = 1;
			}
			jQuery.ajax({
				type:"POST",url:ajaxurl,
				data:{action:'frm_get_default_html', form_id:jQuery('input[name="id"]').val(), plain_text:p, nonce:frmGlobal.nonce},
				success:function(msg){
					insertContent(content_box,msg);
				} 
			});
		}else{
			insertContent(content_box,variable);
		}
		return false;
	}
	
	function insertContent(content_box,variable){
		if(document.selection){
			content_box[0].focus();
			document.selection.createRange().text=variable;
		}else if(content_box[0].selectionStart){
			obj = content_box[0];
			var e = obj.selectionEnd;
			obj.value = obj.value.substr(0,obj.selectionStart)+variable+obj.value.substr(obj.selectionEnd,obj.value.length);
			var s=e+variable.length;obj.focus();
			obj.setSelectionRange(s,s);
		}else{
			content_box.val(variable+content_box.val());
		}
		content_box.keyup(); //trigger change
	}
	
	function toggleAllowedShortcodes(id,f){
		var c, clickedID;

		if(typeof(id) === 'undefined'){
			id = '';
		}
		c = id;
		
		if(id !== ''){
			var $ele = jQuery(document.getElementById(id));
			if($ele.attr('class') && id !== 'wpbody-content' && id !== 'content' && id !== 'dyncontent' && id !== 'success_msg'){
				var d = $ele.attr('class').split(' ')[0];
				if(d === 'frm_long_input' || typeof d === 'undefined'){
					d = '';
				}else{
					id = jQuery.trim(d);
				}
				c = c+' '+d;
				c = c.replace('widefat', '').replace('frm_with_left_label', '');
			}
		}

		jQuery('#frm-insert-fields-box,#frm-conditionals,#frm-adv-info-tab,#frm-html-tags,#frm-layout-classes,#frm-dynamic-values').removeClass().addClass('tabs-panel '+c);
		var a=[
			'content','wpbody-content','dyncontent','success_url',
			'success_msg','edit_msg','frm_dyncontent','frm_not_email_message',
			'frm_not_email_subject'
		];
		var b=[
			'before_content','after_content','frm_not_email_to',
			'after_html','before_html','submit_html','field_custom_html',
			'dyn_default_value', 'frm_classes'
		];

		if(jQuery.inArray(id, a) >= 0){
			jQuery('.frm_code_list a').removeClass('frm_noallow').addClass('frm_allow');
			jQuery('.frm_code_list a.hide_'+id).addClass('frm_noallow').removeClass('frm_allow');
		}else if(jQuery.inArray(id, b) >= 0){
			jQuery('.frm_code_list a:not(.show_'+id+')').addClass('frm_noallow').removeClass('frm_allow');
			jQuery('.frm_code_list a.show_'+id).removeClass('frm_noallow').addClass('frm_allow');
		}else{
			jQuery('.frm_code_list a').addClass('frm_noallow').removeClass('frm_allow');
		}

		//Automatically select a tab
		if ( id === 'dyn_default_value' ) {
			clickedID = 'frm_dynamic_values';
		} else if ( id === 'frm_classes' ) {
			clickedID = 'frm_layout_classes';
		} else if ( jQuery('.frm_form_builder').length ) {
			if ( f === 'focusin' || jQuery( document.getElementById( 'frm-dynamic-values' ) ).is(':visible') || jQuery( document.getElementById( 'frm-layout-classes' ) ).is( ':visible' ) ) {
				clickedID = 'frm_insert_fields';
			}
		}
		if ( typeof clickedID !== 'undefined' ) {
			jQuery( document.getElementById( clickedID + '_tab' ) ).click();
			jQuery( '#' + clickedID.replace( /_/g, '-' ) + ' .frm_show_inactive' ).addClass( 'frm_hidden' );
			jQuery( '#' + clickedID.replace( /_/g, '-' ) + ' .frm_show_active' ).removeClass( 'frm_hidden' );
		}
	}

	function toggleKeyID(switch_to, e){
		e.stopPropagation();
		jQuery('.frm_code_list .frmids, .frm_code_list .frmkeys').hide();
		jQuery('.frm_code_list .'+switch_to).show();
		jQuery('.frmids, .frmkeys').removeClass('current');
		jQuery('.'+switch_to).addClass('current');
	}
	
	/* Styling */
	//function to append a new theme stylesheet with the new style changes
	function updateUICSS(locStr){
		if(locStr == -1){
			jQuery('link.ui-theme').remove();
			return false;
		}
		var cssLink = jQuery('<link href="'+locStr+'" type="text/css" rel="Stylesheet" class="ui-theme" />');
		jQuery('head').append(cssLink);

		if ( jQuery( 'link.ui-theme' ).length > 1 ) {
			jQuery('link.ui-theme:first').remove();
		}
	}
	
	function setPosClass(){
		/*jshint validthis:true */
		var value = this.value;
		if(value === 'none'){
			value = 'top';
		} else if ( value === 'no_label' ) {
			value = 'none';
		}
		jQuery('.frm_pos_container').removeClass('frm_top_container frm_left_container frm_right_container frm_none_container frm_inside_container').addClass('frm_'+value+'_container');
	}

    function collapseAllSections(){
        jQuery('.control-section.accordion-section.open').removeClass('open');
    }

	function textSquishCheck(){
		var size = document.getElementById('frm_field_font_size').value.replace(/\D/g, '');
		var height = document.getElementById('frm_field_height').value.replace(/\D/g, '');
		var paddingEntered = document.getElementById('frm_field_pad').value.split(' ');
		var paddingCount = paddingEntered.length;

		// If too many or too few padding entries, leave now
		if ( paddingCount === 0 || paddingCount > 4 || height === '' ) {
			return;
		}

		// Get the top and bottom padding from entered values
		var paddingTop = paddingEntered[0].replace(/\D/g, '');
		var paddingBottom = paddingTop;
		if ( paddingCount >= 3 ) {
			paddingBottom = paddingEntered[2].replace(/\D/g, '');
		}

		// Check if there is enough space for text
		var textSpace = height - size - paddingTop - paddingBottom - 3;
		if ( textSpace < 0 ) {
			alert( frm_admin_js.css_invalid_size );
		}
	}
	
	/* Global settings page */
	function loadSettingsTab( anchor ) {
		var holder = anchor.replace('#','');
		var holderContainer = jQuery('.frm_'+ holder +'_ajax');
		if ( holderContainer.length ) {
			jQuery.ajax({
				type:'POST',url:ajaxurl,
				data:{
					'action':'frm_settings_tab',
					'tab':holder.replace('_settings',''),
					'nonce':frmGlobal.nonce
				},
				success:function(html){
					holderContainer.replaceWith(html);
				}
			});
		}
	}

	function uninstallNow(){ 
		if(confirm(frm_admin_js.confirm_uninstall)){
			jQuery('.frm_uninstall .spinner').show();
			jQuery.ajax({
				type:'POST',url:ajaxurl,data:"action=frm_uninstall&nonce="+frmGlobal.nonce,
                success:function(msg){
					jQuery('.frm_uninstall').fadeOut('slow');
					window.location=msg;
				}
			});
		}
		return false;
	}

	function authorize(){
		/*jshint validthis:true */
		var button = jQuery(this);
		var pluginSlug = button.data('plugin');
		var license = document.getElementById('edd_'+pluginSlug+'_license_key').value;
		var wpmu = document.getElementById('proplug-wpmu');
		if ( wpmu === null ) {
			wpmu = 0;
		} else {
			if ( wpmu.checked ) {
				wpmu = 1;
			} else {
				wpmu = 0;
			}
		}

		jQuery.ajax({
			type:'POST',url:ajaxurl,dataType:'json',
			data:{action:'frm_addon_activate',license:license,plugin:pluginSlug,wpmu:wpmu,nonce:frmGlobal.nonce},
			success:function(msg){
				var messageBox = jQuery('.frm_pro_license_msg');
				if ( msg.success === true ) {
					document.getElementById('frm_license_top').style.display = 'none';
					document.getElementById('frm_license_bottom').style.display = 'block';
					messageBox.removeClass('frm_error_style').addClass('frm_message');
				}else{
					messageBox.addClass('frm_error_style').removeClass('frm_message');
				}

				messageBox.html(msg.message);
				if ( msg.message !== '' ){
					setTimeout(function(){
						messageBox.html('');
						messageBox.removeClass('frm_error_style frm_message');
					},5000);
				}
			}
		});
	}

	function deauthorize(){
		/*jshint validthis:true */
		if(!confirm(frmGlobal.deauthorize)){
			return false;
		}
		var $link = jQuery(this);
		$link.next('.spinner').show();
		var pluginSlug = $link.data('plugin');
		var license = document.getElementById('edd_'+pluginSlug+'_license_key').value;
		jQuery.ajax({
			type:'POST',url:ajaxurl,
			data:{action:'frm_addon_deactivate',license:license,plugin:pluginSlug,nonce:frmGlobal.nonce},
			success:function(msg){
				jQuery('.spinner').fadeOut('slow');
				$link.fadeOut('slow');
				showAuthForm();
			}
		});
		return false;
	}

	function showAuthForm(){
		var form = document.getElementById('frm_license_top');
		var cred = jQuery('#frm_license_bottom');
		if(cred.is(':visible')){
			cred.hide();
			form.style.display = 'block';
		}else{
			cred.show();
			form.style.display = 'none';
		}
	}

	function saveAddonLicense() {
		/*jshint validthis:true */
		var button = jQuery(this);
		var buttonName = this.name;
		var pluginSlug = button.data('plugin');
		var action = buttonName.replace('edd_'+pluginSlug+'_license_', '');
		var license = document.getElementById('edd_'+pluginSlug+'_license_key').value;
		jQuery.ajax({
			type:'POST',url:ajaxurl,dataType:'json',
			data:{action:'frm_addon_'+action,license:license,plugin:pluginSlug,nonce:frmGlobal.nonce},
			success:function(msg){
				var thisRow = button.closest('.edd_frm_license_row');
				if ( action === 'deactivate' ) {
					license = '';
					document.getElementById('edd_'+pluginSlug+'_license_key').value = '';
				}
				thisRow.find('.edd_frm_license').html( license );
				if ( msg.success === true ) {
					thisRow.find('.frm_icon_font').removeClass('frm_hidden');
					thisRow.find('div.alignleft').toggleClass( 'frm_hidden', 1000 );
				}

				var messageBox = thisRow.find('.frm_license_msg');
				messageBox.html(msg.message);
				if ( msg.message !== '' ){
					setTimeout(function(){
						messageBox.html('');
					},15000);
				}
			}
		});
	}

	function fillLicenses(){
		var emptyFields = jQuery('.frm_addon_license_key:visible');
		if ( emptyFields.length < 1 ){
			return false;
		}

		jQuery.ajax({
			type:'POST',url:ajaxurl,dataType:'json',
			data:{action:'frm_fill_licenses', nonce:frmGlobal.nonce},
			success:function(json){
				var i;
				var licenses = json.licenses;
				var filledSomething = false;
				for ( i in licenses ) {
				    if (licenses.hasOwnProperty(i)) {
						var input = jQuery('#edd_'+ licenses[i].slug +'_license_key');
						if ( typeof input !== null && input.is(':visible') ) {
							input.val(licenses[i].key);
							jQuery('input[name="edd_'+ licenses[i].slug +'_license_activate"]').click();
							filledSomething = true;
						}
				    }
				}
				if ( ! filledSomething ) {
					jQuery('.edd_frm_fill_license').replaceWith(frm_admin_js.no_licenses);
				}
			}
		});
		return false;
	}

	/* Import/Export page */
	function validateExport(e){
        /*jshint validthis:true */
		e.preventDefault();
	
		var s = false;
		var $exportForms = jQuery('select[name="frm_export_forms[]"]');
		if (!$exportForms.val()){
			$exportForms.closest('.form-field').addClass('frm_blank_field');
			s = 'stop';
		}

		var $exportType = jQuery('input[name="type[]"]');
		if (!jQuery('input[name="type[]"]:checked').val() && $exportType.attr('type') === 'checkbox'){
			$exportType.closest('.form-field').addClass('frm_blank_field');
			s = 'stop';
		}

		if ( s === 'stop' ){
			return false;
		}

		e.stopPropagation();
		this.submit();
	}

	function removeExportError(){
		/*jshint validthis:true */
		var t = jQuery(this).closest('.frm_blank_field');
		if (typeof(t) === 'undefined'){
			return;
		}
		
		var $thisName = this.name;
		if($thisName === 'type[]' && jQuery('input[name="type[]"]:checked').val()){
			t.removeClass('frm_blank_field');
		}else if($thisName === 'frm_export_forms[]' && jQuery(this).val()){
			t.removeClass('frm_blank_field');
		}

	}

	function checkCSVExtension(){
		/*jshint validthis:true */
		var f = jQuery(this).val();
		var re = /\.csv$/i;
		if(f.match(re) !== null){
			jQuery('.show_csv').fadeIn();
		}else{
			jQuery('.show_csv').fadeOut();
		}
	}

	function checkExportTypes(){
		/*jshint validthis:true */
		var $dropdown = jQuery(this);
		var $selected = $dropdown.find(':selected');
		var s = $selected.data('support');

		var multiple = s.indexOf('|');
		jQuery('input[name="type[]"]').each(function(){
			this.checked = false;
			if(s.indexOf(this.value) >= 0){
				this.disabled = false;
				if ( multiple == -1 ) {
					this.checked = true;
				}
			}else{
				this.disabled = true;
			}
		});

		if($dropdown.val() === 'csv'){
			jQuery('.csv_opts').show();
		}else{
			jQuery('.csv_opts').hide();
		}

		var c = $selected.data('count');
		var exportField = jQuery('select[name="frm_export_forms[]"]');
		if(c === 'single'){
			exportField.prop('multiple', false).next('.howto').hide();
		}else{
			exportField.prop('multiple', true).next('.howto').show();
		}
	}

    function initiateMultiselect(){
        jQuery('.frm_multiselect').multiselect({
			templates: {ul:'<ul class="multiselect-container frm-dropdown-menu"></ul>'},
			buttonContainer: '<div class="btn-group frm-btn-group" />',
			nonSelectedText:frm_admin_js['default'],// TODO: should be noneSelectedText
			onDropdownShown: function( event ) {
				var action = jQuery( event.currentTarget.closest( '.frm_form_action_settings, #new_fields' ) );
				if ( action.length ) {
					jQuery( '#wpcontent' ).click(function () {
						if ( jQuery( '.multiselect-container.frm-dropdown-menu' ).is( ':visible' ) ) {
							jQuery( event.currentTarget ).removeClass('open');
						}
					});
				}
			}
        });
    }

	/* Helpers */
	function toggle( cname, id ) {
		if(id === '#'){
			var cont = document.getElementById(cname);
			var hidden = cont.style.display;
			if(hidden === 'none'){
				cont.style.display = 'block';
			}else{
				cont.style.display = 'none';
			}
		}else{
			var vis = cname.is(':visible');
			if(vis){
				cname.hide();
			}else{
				cname.show();
			}
		}
	}

	function removeWPUnload() {
		window.onbeforeunload = null;
		var w = jQuery( window );
		w.off( 'beforeunload.widgets' );
		w.off( 'beforeunload.edit-post' );
	}

	return{
		init: function(){
			// Bootstrap dropdown button
			jQuery('.wp-admin').click(function(e){
				var t = jQuery(e.target);
				var $openDrop = jQuery('.dropdown.open');
				if($openDrop.length && e.target.className.indexOf('dropdown') === -1 && !t.closest('.dropdown').length){
					$openDrop.removeClass('open');
				}
			});

			if ( typeof this_form_id === 'undefined' ) {
				this_form_id = jQuery(document.getElementById('form_id')).val();
			}

			if($newFields.length > 0){
				// only load this on the form builder page
				frmAdminBuild.buildInit();
			}else if(jQuery(document.getElementById('frm_notification_settings')).length > 0){
				// only load on form settings page
				frmAdminBuild.settingsInit();
			}else if(document.getElementById('frm_styling_form') !== null){
				// load styling settings js
				frmAdminBuild.styleInit();
			}else if(document.getElementById('frm_custom_css_box') !== null){
				// load styling settings js
				frmAdminBuild.customCSSInit();
			}else if(jQuery(document.getElementById('form_global_settings')).length > 0){
				// global settings page
				frmAdminBuild.globalSettingsInit();
			}else if(jQuery(document.getElementById('frm_export_xml')).length > 0){
				// import/export page
				frmAdminBuild.exportInit();
			}else{
				var $dynCont = jQuery(document.getElementById('frm_dyncontent'));
				if($dynCont.length > 0){
					// only load on views settings page
					frmAdminBuild.viewInit();
				}
			}
			
			var $advInfo = jQuery(document.getElementById('frm_adv_info'));
			if($advInfo.length > 0 || jQuery('.frm_field_list').length > 0){
				// only load on the form and view settings pages
				frmAdminBuild.panelInit();
			}

			loadTooltips();

			jQuery(document).on('click', 'a[data-frmverify]', confirmClick);

            jQuery(document.getElementById('wpbody')).on('click', '.frm_remove_tag, .frm_remove_form_action', removeThisTag);

			// used on build, form settings, and view settings
			var $shortCodeDiv = jQuery(document.getElementById('frm_shortcodediv'));
			if($shortCodeDiv.length > 0){
				jQuery('a.edit-frm_shortcode').click(function() {
					if ($shortCodeDiv.is(':hidden')) {
						$shortCodeDiv.slideDown('fast', function(){setMenuOffset();});
						this.style.display = 'none';
					}
					return false;
				});

				jQuery('.cancel-frm_shortcode', '#frm_shortcodediv').click(function() {
					$shortCodeDiv.slideUp('fast', function(){setMenuOffset();});
					$shortCodeDiv.siblings('a.edit-frm_shortcode').show();
					return false;
				});
			}

			// tabs
			jQuery('.frm-category-tabs a').click(function(){
				clickTab(this);
				return false;
			});
			jQuery('.starttab a').trigger('click');

			// submit the search for with dropdown
			jQuery('#frm-fid-search-menu a').click(function(){
				var val = this.id.replace('fid-', '');
				jQuery('select[name="fid"]').val(val);
				jQuery(document.getElementById('posts-filter')).submit();
				return false;
			});
			
			jQuery('.frm_select_box').click(function(){this.select();});
			jQuery('.frm_select_box').focus(function(){this.select();});
			
			jQuery(document.getElementById('frm_deauthorize_link')).click(deauthorize);
			jQuery('.frm_authorize_link').click(authorize);

			// prevent annoying confirmation message from WordPress
			jQuery('button, input[type=submit]').on('click', removeWPUnload);
		},
		
		buildInit: function(){
			if(jQuery('.frm_field_loading').length){
				var load_field_id = jQuery('.frm_field_loading').first().attr('id');
				loadFields(load_field_id);
			}

			setupSortable('ul.frm_sorting');

			// Show message if section has no fields inside
			var frm_sorting = jQuery('.start_divider .frm_sorting');
			for ( i = 0; i < frm_sorting.length ; i++) {
				if ( frm_sorting[i].children.length < 2 ) {
					jQuery(frm_sorting[i]).parent().children('.frm_no_section_fields').addClass('frm_block');
				}
			}

			jQuery('.field_type_list > li:not(.frm_noallow)').draggable({
				connectToSortable:'#new_fields',cursor:'move',
				helper:'clone',revert:'invalid',delay:10,
				cancel:'.frm-dropdown-menu'
			});
			jQuery('ul.field_type_list, .field_type_list li, ul.frm_code_list, .frm_code_list li, .frm_code_list li a, #frm_adv_info #category-tabs li, #frm_adv_info #category-tabs li a').disableSelection();
			
			var $form_name = jQuery('input[name="name"]');
			if($form_name.val() === ''){
				$form_name.focus();
			}

			jQuery(document.getElementById('frm_create_template_button')).click(createFromTemplate);
			jQuery('.frm_submit_ajax').click(submitBuild);
			jQuery('.frm_submit_no_ajax').click(submitNoAjax);
			
			jQuery('a.edit-form-status').click(slideDown);
			jQuery('.cancel-form-status').click(slideUp);
			jQuery('.save-form-status').click(function(){
				var newStatus = jQuery(document.getElementById('form_change_status')).val();
				jQuery('input[name="new_status"]').val(newStatus);
				jQuery(document.getElementById('form-status-display')).html(newStatus);
				jQuery('.cancel-form-status').click();
				return false;
			});
			
			jQuery('.frm_form_builder form:first').submit(function(){
				jQuery('.inplace_field').blur();
			});

			initiateMultiselect();
			preventBodyScroll();

			$newFields.on('keypress', '.frm_ipe_field_label, .frm_ipe_field_option, .frm_ipe_field_option_key', blurField);
			$newFields.on('mouseenter', '.frm_ipe_field_option, .frm_ipe_field_option_key', setIPEOpts);
			$newFields.on('mouseenter', '.frm_ipe_field_label', setIPELabel);
			$newFields.on('mouseenter', '.frm_ipe_field_desc, .frm_ipe_field_conf_desc', setIPEDesc);
			$newFields.on('click', '.frm_add_logic_row', addFieldLogicRow);
            $newFields.on('click', '.frm_remove_tag', removeThisTag);
			$newFields.on('click', '.frm_add_watch_lookup_row', addWatchLookupRow);
			$newFields.on('change', '.autopopulate_value', hideOrShowAutopopulateValue);
			$newFields.on('change', '.frm_get_values_form', updateGetValueFieldSelection);
			$newFields.on('change', '.frm_logic_field_opts', getFieldValues );
			$newFields.on('change', '.scale_maxnum, .scale_minnum', setScaleValues);
			$newFields.on('change', '.radio_maxnum', setStarValues);

			jQuery(document.getElementById('frm-insert-fields')).on('click', '.frm_add_field', addFieldClick);
			$newFields.on('click', '.frm_duplicate_icon', duplicateField);
			$newFields.on('click', '.use_calc', popCalcFields);
			$newFields.on('change', 'input[id^="frm_calc"]', checkCalculationCreatedByUser);
			$newFields.on('change', 'input.frm_format_opt', toggleInvalidMsg);
			$newFields.on('click', 'input.frm_req_field', markRequired);
			$newFields.on('click', 'a.frm_req_field', clickRequired);
			$newFields.on('click', '.frm_mark_unique', markUnique);
			$newFields.on('click', '.frm_reload_icon', { iconType: 'clear_on_focus' }, toggleDefaultValueIcon);
			$newFields.on('click', '.frm_error_icon', { iconType: 'default_blank' }, toggleDefaultValueIcon);

			$newFields.on('click', '.frm_repeat_field', toggleRepeat);
			$newFields.on('change', '.frm_repeat_format', toggleRepeatButtons);
			$newFields.on('change', '.frm_repeat_limit', checkRepeatLimit);
			$newFields.on('input', 'input[name^="field_options[add_label_"]', function(){
				updateRepeatText(this, 'add');
			});
			$newFields.on('input', 'input[name^="field_options[remove_label_"]', function(){
				updateRepeatText(this, 'remove');
			});
			$newFields.on('change', 'select[name^="field_options[data_type_"]', maybeClearWatchFields );

			$newFields.on('click', '.frm_toggle_sep_values', toggleSepValues);
			$newFields.on('click', '.frm_multiselect_opt', toggleMultiselect);
			$newFields.on('click', '.frm_delete_field', clickDeleteField);
			$newFields.on('click', '.frm_single_option .frm_delete_icon', deleteFieldOption);
            $newFields.on('click', '.frm_add_opt', addFieldOption);
			$newFields.on('change', '.frm_toggle_mult_sel', toggleMultSel);

			jQuery(document.getElementById('frm_form_editor_container')).on('click', '#new_fields > li.ui-state-default', clickVis);
			$newFields.on('click', '.start_divider li.ui-state-default', clickSectionVis);
			$newFields.on('change', '.frm_tax_form_select', toggleFormTax);
			jQuery('.frm_form_builder').on('keyup', 'input[name^="item_meta"], textarea[name^="item_meta"]', triggerDefaults);
			jQuery('.frm_form_builder').on('change', 'select[name^="item_meta"]', triggerDefaults);
			$newFields.on('change', 'select.conf_field', addConf);

			$newFields.on('change', '.frm_get_field_selection', getFieldSelection);
		},
		
		settingsInit: function(){
			var $formActions = jQuery(document.getElementById('frm_notification_settings'));
			//BCC, CC, and Reply To button functionality
			$formActions.on('click', '.frm_email_buttons', showEmailRow);
			$formActions.on('click', '.frm_remove_field', hideEmailRow);
			$formActions.on('change', '.frm_tax_selector', changePosttaxRow);
			$formActions.on('change', 'select.frm_single_post_field', checkDupPost);
			$formActions.on('change', 'select.frm_toggle_post_content', togglePostContent);
			$formActions.on('change', 'select.frm_dyncontent_opt', fillDyncontent);
            $formActions.on('change', '.frm_post_type', switchPostType);
			$formActions.on('click', '.frm_add_postmeta_row', addPostmetaRow);
			$formActions.on('click', '.frm_add_posttax_row', addPosttaxRow);
			$formActions.on('click', '.frm_toggle_cf_opts', toggleCfOpts);
			jQuery('select[data-toggleclass], input[data-toggleclass]').change(toggleFormOpts);
			jQuery('.frm_actions_list').on('click', '.frm_active_action', addFormAction);
			initiateMultiselect();

			//set actions icons to inactive
			jQuery('ul.frm_actions_list li').each(function(){
				checkActiveAction(jQuery(this).children('a').data('actiontype'));
			});
			
			jQuery('.frm_submit_settings_btn').click(submitSettings);
			
			jQuery('.frm_form_settings').on('click', '.frm_add_form_logic', addFormLogicRow);
			jQuery('.frm_form_settings').on('blur', '.frm_email_blur', formatEmailSetting);

			jQuery( '.frm_form_settings' ).on( 'click', '.frm_add_submit_logic', addSubmitLogic );
			jQuery( '.frm_form_settings' ).on( 'change', '.frm_submit_logic_field_opts', addSubmitLogicOpts );
			
			//Warning when user selects "Do not store entries ..."
			jQuery(document.getElementById('no_save')).change(function(){
				if( this.checked ) {
                    if ( confirm(frm_admin_js.no_save_warning) !== true ) {
                        // Uncheck box if user hits "Cancel"
                        jQuery(this).attr('checked', false);
                    }
				}
			});

			//Show/hide Messages header
			jQuery('#editable, #edit_action, #save_draft, #success_action').change(function(){
				if ( showFormMessages() ) {
					jQuery(document.getElementById('frm_messages_header')).fadeIn('slow');
				} else {
					jQuery(document.getElementById('frm_messages_header')).fadeOut('slow');
				}
			});
			jQuery("select[name='options[success_action]'], select[name='options[edit_action]']").change(showSuccessOpt);

			var $loggedIn = document.getElementById('logged_in');
			jQuery($loggedIn).change(function(){
				if(this.checked){
					frmFrontForm.visible('.hide_logged_in'); 
				}else{
					frmFrontForm.invisible('.hide_logged_in');
				}
			});
			
			var $cookieExp = jQuery(document.getElementById('frm_cookie_expiration'));
			jQuery(document.getElementById('frm_single_entry_type')).change(function(){
				if(this.value === 'cookie'){
					$cookieExp.fadeIn('slow');
				}else{
					$cookieExp.fadeOut('slow');
				}
			});
			
			var $singleEntry = document.getElementById('single_entry');
			jQuery($singleEntry).change(function(){
				if(this.checked){
					frmFrontForm.visible('.hide_single_entry'); 
				}else{
					frmFrontForm.invisible('.hide_single_entry');
				}
				
				if(this.checked && jQuery(document.getElementById('frm_single_entry_type')).val() === 'cookie'){
					$cookieExp.fadeIn('slow');
				}else{
					$cookieExp.fadeOut('slow');
				}
			});
			
			jQuery('.hide_editable, .hide_save_draft').hide();

			var $saveDraft = jQuery(document.getElementById('save_draft'));
			$saveDraft.change(function(){
				if(this.checked){
					jQuery('.hide_save_draft').fadeIn('slow');
				}else{
					jQuery('.hide_save_draft').fadeOut('slow');
				}
			});
			$saveDraft.change();

			//If Allow editing is checked/unchecked
			var $editable = document.getElementById('editable');
			if( $editable !== null && $editable.checked ){
				jQuery('.hide_editable').show();
			}
			jQuery($editable).change(function(){
				if(this.checked) {
					jQuery('.hide_editable').fadeIn('slow');
					if ( jQuery(document.getElementById('edit_action')).val() === 'message' ) {
						jQuery('.edit_action_message_box').fadeIn('slow');//Show On Update message box
					}
				} else {
					jQuery('.hide_editable').fadeOut('slow');
					jQuery('.edit_action_message_box').fadeOut('slow');//Hide On Update message box
				}
			});
		},
		
		panelInit: function(){
			setupMenuOffset();

			jQuery('.frm_code_list a').addClass('frm_noallow');
			
			jQuery('#postbox-container-1').on('click', '.frm_insert_code', insertCode);
			jQuery(document).on('change', '.frm_insert_val', function(){
				insertFieldCode(jQuery(this).data('target'), jQuery(this).val());
				jQuery(this).val('');	
			});

			jQuery(document).on('focusin click', 'form input, form textarea, #wpcontent', function(e){
				e.stopPropagation();
				if(jQuery(this).is(':not(:submit, input[type=button])')){
					var id = jQuery(this).attr('id');
					toggleAllowedShortcodes(id,e.type);
				}
			});

			jQuery('#postbox-container-1').on('mousedown', '#frm_adv_info a, .frm_field_list a', function(e){
				e.preventDefault();
			});

			jQuery('#frm_field_search').on('keyup', function (e) {

				var searchText = jQuery(this).val().toLowerCase();

				jQuery("ul.frm_customize_field_list li").each(function () {
					var $this = jQuery(this);
					$this.find('a').text().toLowerCase().indexOf(searchText) >= 0 ? $this.show() : $this.hide();
				});
			});

			var customPanel = jQuery('#frm_adv_info');
			customPanel.on('click', '.subsubsub a.frmids', function(e){toggleKeyID('frmids',e);});
			customPanel.on('click', '.subsubsub a.frmkeys', function(e){toggleKeyID('frmkeys',e);});

			if(typeof(tinymce) === 'object'){
				DOM=tinymce.DOM; 
				if(typeof(DOM.events) !== 'undefined' && typeof(DOM.events.add) !== 'undefined'){
					DOM.events.add( DOM.select('.wp-editor-wrap'), 'mouseover', function(e){
						if(jQuery('*:focus').length>0) {
							return;
						}
						if(this.id) {
							toggleAllowedShortcodes(this.id.slice(3, -5), 'focusin');
						}
					});
					DOM.events.add( DOM.select('.wp-editor-wrap'), 'mouseout', function(e){
						if(jQuery('*:focus').length>0) {
							return;
						}
						if(this.id) {
							toggleAllowedShortcodes(this.id.slice(3, -5), 'focusin');
						}
					});
				}else{
					jQuery('#frm_dyncontent').on('mouseover mouseout', '.wp-editor-wrap', function(e){
						if(jQuery('*:focus').length>0) {
							return;
						}
						if(this.id){
							toggleAllowedShortcodes(this.id.slice(3,-5),'focusin');
						}
					});
				}
			}

		},

		viewInit: function(){
			var $advInfo = jQuery(document.getElementById('frm_adv_info'));
			$advInfo.before('<div id="frm_position_ele"></div>');

			// add form nav
			var $navCont = document.getElementById('frm_nav_container');
			if ( $navCont !== null ) {
				var $titleDiv = document.getElementsByClassName('wp-header-end')[0];
				if ( $titleDiv === null || typeof $titleDiv  === 'undefined' ) {
					$titleDiv = document.getElementById('titlediv');
				} else {
					$titleDiv = $titleDiv.parentNode;
				}
				$titleDiv.insertBefore($navCont, $titleDiv.firstChild);
				$navCont.style.display = 'block';
			}

			// move content tabs
			jQuery('#frm_dyncontent .handlediv').before(jQuery('#frm_dyncontent .nav-menus-php'));

			// click content tabs
			jQuery('.nav-tab-wrapper a').click(clickContentTab);

            // click tabs after panel is replaced with ajax
            jQuery('#side-sortables').on('click', '.frm_doing_ajax.categorydiv .category-tabs a', clickTabsAfterAjax);

			jQuery('input[name="show_count"]').change(showCount);

			jQuery(document.getElementById('form_id')).change(displayFormSelected);

			var $addRemove = jQuery('.frm_repeat_rows');
			$addRemove.on('click', '.frm_add_order_row', addOrderRow);
			$addRemove.on('click', '.frm_add_where_row', addWhereRow);
			$addRemove.on('change', '.frm_insert_where_options', insertWhereOptions);
			$addRemove.on('change', '.frm_where_is_options', hideWhereOptions);

			setDefaultPostStatus();
		},
		
		styleInit: function(){
            collapseAllSections();

			document.getElementById('frm_field_height').addEventListener('change', textSquishCheck);
			document.getElementById('frm_field_font_size').addEventListener('change', textSquishCheck);
			document.getElementById('frm_field_pad').addEventListener('change', textSquishCheck);

			jQuery('input.hex').wpColorPicker({
				width:200,
				change: function( event, ui ) {
					var hexcolor = jQuery( this ).wpColorPicker( 'color' );
					jQuery( event.target ).val( hexcolor ).change();
				}
			});
			jQuery('.wp-color-result-text').text( function( i, oldText ) {
				return oldText === 'Select Color' ? 'Select' : oldText;
			});

            // update styling on change
            jQuery('#frm_styling_form .styling_settings').change(function(){
                var locStr = jQuery('input[name^="frm_style_setting[post_content]"], select[name^="frm_style_setting[post_content]"], textarea[name^="frm_style_setting[post_content]"], input[name="style_name"]').serialize();
                jQuery.ajax({
                    type:'POST',url:ajaxurl,
                    data:'action=frm_change_styling&nonce='+frmGlobal.nonce+'&'+locStr,
                    success:function(css){
                        document.getElementById('this_css').innerHTML = css;
                    }
                });
            });
			
			// menu tabs
			jQuery('#menu-settings-column').bind('click', function(e) {
				var selectAreaMatch, panelId, wrapper, items,
					target = jQuery(e.target);

				if ( e.target.className.indexOf('nav-tab-link') !== -1 ) {

					panelId = target.data( 'type' );

					wrapper = target.parents('.accordion-section-content').first();


					jQuery('.tabs-panel-active', wrapper).removeClass('tabs-panel-active').addClass('tabs-panel-inactive');
					jQuery('#' + panelId, wrapper).removeClass('tabs-panel-inactive').addClass('tabs-panel-active');

					jQuery('.tabs', wrapper).removeClass('tabs');
					target.parent().addClass('tabs');

					// select the search bar
					jQuery('.quick-search', wrapper).focus();

					e.preventDefault();
				}
			});

            jQuery('.multiselect-container.frm-dropdown-menu li a').click(function(){
                var radio = this.children[0].children[0];
                var btnGrp = jQuery(this).closest('.btn-group');
                var btnId = btnGrp.attr('id');
                document.getElementById(btnId.replace('_select', '')).value = radio.value;
                btnGrp.children('button').html(radio.nextElementSibling.innerHTML + ' <b class="caret"></b>');

                // set active class
                btnGrp.find('li.active').removeClass('active');
                jQuery(this).closest('li').addClass('active');
            });

			jQuery('.frm_reset_style').click(function(){
				if(!confirm(frm_admin_js.confirm)){
					return false;
				}
				jQuery.ajax({
					type:'POST',url:ajaxurl,
					data:{action:'frm_settings_reset', nonce:frmGlobal.nonce},
					success:function(errObj){
						errObj=errObj.replace(/^\s+|\s+$/g,'');
						if(errObj.indexOf('{') === 0){
							errObj=jQuery.parseJSON(errObj);
						}
						for (var key in errObj){
							jQuery('input[name$="['+key+']"], select[name$="['+key+']"]').val(errObj[key]);
						}
						jQuery('#frm_submit_style, #frm_auto_width').prop('checked', false);
						jQuery(document.getElementById('frm_fieldset')).change();
					}
				});
			});
			
			jQuery('.frm_pro_form #datepicker_sample').datepicker({changeMonth:true,changeYear:true});
			
			jQuery(document.getElementById('frm_position')).change(setPosClass);
			
			jQuery('select[name$="[theme_selector]"]').change(function(){
				var themeVal = jQuery(this).val();
				var themeName = themeVal;
				var css = themeVal;
				if ( themeVal !== -1 ) {
					if ( themeVal === 'ui-lightness' && frm_admin_js.pro_url !== '' ) {
						css = frm_admin_js.pro_url +'/css/ui-lightness/jquery-ui.css';
						jQuery('.frm_date_color').show();
					} else {
						css = frm_admin_js.jquery_ui_url +'/themes/'+themeVal+'/jquery-ui.css';
						jQuery('.frm_date_color').hide();
					}
				}

				updateUICSS(css);
				document.getElementById('frm_theme_css').value = themeVal;
				return false;
			}).change();
		},

        customCSSInit: function() {
			/* deprecated since WP 4.9 */
            var customCSS = document.getElementById('frm_custom_css_box');
            if ( customCSS !== null ) {
                var editor = CodeMirror.fromTextArea(customCSS, {
                    lineNumbers: true
                });
            }
        },

		globalSettingsInit: function(){
			var $globalForm = jQuery(document.getElementById('form_global_settings'));
			$globalForm.on('click', '.frm_show_auth_form', showAuthForm);
			jQuery(document.getElementById('frm_uninstall_now')).click(uninstallNow);
            initiateMultiselect();

			// activate addon licenses
			var licenseTab = document.getElementById('licenses_settings');
			jQuery(licenseTab).on('click', '.edd_frm_save_license', saveAddonLicense);
			jQuery(licenseTab).on('click', '.edd_frm_fill_license', fillLicenses);
		},

		exportInit: function(){
			jQuery(document.getElementById('frm_export_xml')).submit(validateExport);
			jQuery('#frm_export_xml input, #frm_export_xml select').change(removeExportError);
			jQuery('input[name="frm_import_file"]').change(checkCSVExtension);
			jQuery('select[name="format"]').change(checkExportTypes).change();
			initiateMultiselect();
		},
		
		updateOpts: function(field_id,opts){
			$fieldOpts = document.getElementById('frm_field_'+field_id+'_opts');
			empty($fieldOpts);
			addClass($fieldOpts, 'frm-loading-img');
			jQuery.ajax({
				type:"POST",url:ajaxurl,
				data:{action:'frm_import_options', field_id:field_id, opts:opts, nonce:frmGlobal.nonce},
				success:function(html){jQuery('#frm_field_'+field_id+'_opts').html(html).removeClass('frm-loading-img');
				if(jQuery('select[name="item_meta['+field_id+']"]').length>0){
					var o = opts.replace(/\s\s*$/,'').split("\n");
					var sel='';
					for (var i=0;i<o.length;i++){
						sel +='<option value="'+o[i]+'">'+o[i]+'</option>';
					}
					jQuery('select[name="item_meta['+field_id+']"]').html(sel);
				}
				}
			});	
		},

        /* remove conditional logic if the field doesn't exist */
        triggerRemoveLogic: function(fieldID, metaName){
            jQuery('#frm_logic_'+ fieldID +'_'+ metaName +' .frm_remove_tag').click();
        },

        downloadXML: function(controller, ids, isTemplate){
            var url = ajaxurl+'?action=frm_'+ controller +'_xml&ids='+ ids;
            if(isTemplate !== null){
                url = url +'&is_template='+ isTemplate;
            }
            location.href = url;
        }
	};
}
var frmAdminBuild = frmAdminBuildJS();

jQuery(document).ready(function($){
	frmAdminBuild.init();
});

function frm_remove_tag(html_tag){
	console.warn('DEPRECATED: function frm_remove_tag in v2.0'); 
	jQuery(html_tag).remove();
}

function frm_show_div(div,value,show_if,class_id){
	if(value == show_if){
		jQuery(class_id+div).fadeIn('slow').css('visibility', 'visible');
	}else{
		jQuery(class_id+div).fadeOut('slow');
	}
}

function frmCheckAll(checked,n){
	if(checked){
		jQuery("input[name^='"+n+"']").attr('checked','checked');
	}else{
		jQuery("input[name^='"+n+"']").removeAttr('checked');
	}
}

function frmCheckAllLevel(checked,n,level){
	var $kids = jQuery(".frm_catlevel_"+level).children(".frm_checkbox").children('label');
	if(checked){
		$kids.children("input[name^='"+n+"']").attr("checked","checked");
	}else{
		$kids.children("input[name^='"+n+"']").removeAttr("checked");
	}	
}

function frm_add_logic_row(id,form_id){
	console.warn('DEPRECATED: function frm_add_logic_row in v2.0'); 
jQuery.ajax({
    type:"POST",url:ajaxurl,
    data:{action:'frm_add_logic_row',form_id:form_id, field_id:id, meta_name:jQuery('#frm_logic_row_'+id+' > div').size(), nonce:frmGlobal.nonce},
    success:function(html){jQuery('#frm_logic_row_'+id).append(html);}
});
return false;
}

function frmGetFieldValues( field_id, cur, row_number, field_type, html_name ) {

	if ( field_id ) {
		jQuery.ajax( {
			type: 'POST', url: ajaxurl,
			data: 'action=frm_get_field_values&current_field=' + cur + '&field_id=' + field_id + '&name=' + html_name + '&t=' + field_type + '&form_action=' + jQuery( 'input[name="frm_action"]' ).val() + '&nonce=' + frmGlobal.nonce,
			success: function ( msg ) {
				document.getElementById( 'frm_show_selected_values_' + cur + '_' + row_number ).innerHTML = msg;
			}
		} );
	}
}

function frmImportCsv(formID){
	var urlVars = '';
	if(typeof __FRMURLVARS != 'undefined'){
		urlVars = __FRMURLVARS;
	}
	
    jQuery.ajax({
		type:"POST",url:ajaxurl,
		data:'action=frm_import_csv&nonce='+frmGlobal.nonce+'&frm_skip_cookie=1'+urlVars,
    success:function(count){
		var max = jQuery('.frm_admin_progress_bar').attr('aria-valuemax');
		var imported = max - count;
		var percent = (imported / max) * 100;
		jQuery('.frm_admin_progress_bar').css('width', percent +'%').attr('aria-valuenow', imported);
		
        if(parseInt(count) > 0){
			jQuery('.frm_csv_remaining').html(count);
			frmImportCsv(formID);
		}else{
			jQuery(document.getElementById('frm_import_message')).html(frm_admin_js.import_complete);
			setTimeout(function(){
				location.href = '?page=formidable-entries&frm_action=list&form='+formID+'&import-message=1';
			}, 2000);
        }
    }
    });
}
