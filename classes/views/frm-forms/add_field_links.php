<div id="postbox-container-1" class="<?php echo FrmAppController::get_postbox_class(); ?>">
    <?php if(!isset($hide_preview) or !$hide_preview){ 
        if (!$values['is_template']){ ?>
    <p class="howto" style="margin-top:0;"><?php _e('Insert into a post, page, display or text widget', 'formidable') ?>:
    <input type="text" readonly="true" class="frm_select_box" value='[formidable id=<?php echo $id; ?>]' /></p>
    <?php } ?>
    
    <?php if(isset($values['form_key'])){ ?>
    <p class="frm_orange"><a href="<?php echo FrmFormsHelper::get_direct_link($values['form_key']); ?>" target="_blank"><?php _e('Preview Form', 'formidable') ?></a>
    <?php global $frm_settings; 
        if (!empty($frm_settings->preview_page_id)){ ?>
        <?php _e('or', 'formidable') ?> 
        <a href="<?php echo add_query_arg('form', $values['form_key'], get_permalink($frm_settings->preview_page_id)) ?>" target="_blank"><?php _e('Preview in Current Theme', 'formidable') ?></a>
    <?php } ?>
    </p>
    <?php } 
    } ?>
    <div id="frm_position_ele"></div>
    
    <div class="postbox frm_field_list">
    <div class="inside">
    <div id="taxonomy-linkcategory" class="categorydiv">
        <ul id="category-tabs" class="category-tabs frm-category-tabs">
    		<li class="tabs" ><a href="#frm-insert-fields" id="frm_insert_fields_tab"><?php _e( 'Fields', 'formidable' ); ?></a></li>
    		<li class="hide-if-no-js"><a href="#frm-layout-classes" id="frm_layout_classes_tab"><?php _e( 'Layout', 'formidable' ); ?></a></li>
    		<?php do_action('frm_extra_form_instruction_tabs'); ?>
    	</ul>

    	<div id="frm-insert-fields" class="tabs-panel" style="max-height:none;overflow:visible;">
			<ul class="field_type_list">
            <?php 
            $col_class = 'frm_col_one';
            foreach ($frm_field_selection as $field_key => $field_type){ ?>
                <li class="frmbutton button <?php echo $col_class ?> frm_t<?php echo $field_key ?>" id="<?php echo $field_key ?>"><a href="javascript:add_frm_field_link(<?php echo $id ?>,'<?php echo $field_key ?>');"><?php echo $field_type ?></a></li>
             <?php
             $col_class = (empty($col_class)) ? 'frm_col_one' : '';
             } ?>
             </ul>
             <div class="clear"></div>

             <h4 class="title" style="margin-bottom:0;margin-top:4px;"><?php _e('Pro Fields', 'formidable') ?></h4>
			 <ul<?php echo apply_filters('frm_drag_field_class', '') ?> style="margin-top:2px;">
             <?php $col_class = 'frm_col_one';
             foreach (FrmFieldsHelper::pro_field_selection() as $field_key => $field_type){ ?>
                 <li class="frmbutton button <?php echo $col_class ?> frm_t<?php echo $field_key ?>" id="<?php echo $field_key ?>"><?php echo apply_filters('frmpro_field_links', $field_type, $id, $field_key) ?></li>
            <?php 
            $col_class = (empty($col_class)) ? 'frm_col_one' : '';
            } ?>
             </ul>
             <div class="clear"></div>
        </div>
    	<?php do_action('frm_extra_form_instructions'); ?>
    	
    	<div id="frm-layout-classes" class="tabs-panel" style="display:none;max-height:none;">
			<p class="howto"><?php _e('Add classes in the "CSS layout classes" field option', 'formidable') ?></p>
    	    <ul class="frm_code_list">
    	    <?php $classes = array(
    	            'frm_first_half' => __('First 1/2', 'formidable'), 
    	            'frm_last_half' => __('Last 1/2', 'formidable'),
    	            'frm_first_third' => __('First 1/3', 'formidable'),
    	            'frm_last_third' => __('Last 1/3', 'formidable'),
    	            'frm_first_two_thirds' => __('First 2/3', 'formidable'),
    	            'frm_last_two_thirds' => __('Last 2/3', 'formidable'),
    	            'frm_third' => __('1/3', 'formidable'),
    	            'frm_fourth' => __('1/4', 'formidable'),
    	            'frm_first_fourth' => array('label' => __('First 1/4', 'formidable')),
    	            'frm_last_fourth' => __('Last 1/4', 'formidable'), 
    	            'frm_first_fifth' => __('First 1/5', 'formidable'),
    	            'frm_last_fifth' => __('Last 1/5', 'formidable'),
    	            'frm_fifth' => __('1/5', 'formidable'),
    	            'frm_inline' => array('label' => __('Inline', 'formidable'), 'title' => __('Align fields in a row without a specific width.', 'formidable')),
    	            'frm_first_inline' => array('label' => __('First Inline', 'formidable'), 'title' => __('Align fields at the beginning of a row without a specific width.', 'formidable')), 
    	            'frm_last_inline' => array('label' => __('Last Inline', 'formidable'), 'title' => __('Align fields at the end of a row without a specific width.', 'formidable')),
    	            
    	            'frm_full' => array('label' => __('100% width', 'formidable'), 'title' => __('Force the field to fill the full space with 100% width.', 'formidable')),
    	            'frm_grid_first' => __('First Grid Row', 'formidable'), 
    	            'frm_grid' => __('Even Grid Row', 'formidable'),
    	            'frm_grid_odd' => __('Odd Grid Row', 'formidable'),
    	            'frm_two_col' => array('label' => __('2 Col Options', 'formidable'), 'title' => __('Put your radio button or checkbox options into two columns.', 'formidable')),
    	            'frm_three_col' => array('label' => __('3 Col Options', 'formidable'), 'title' => __('Put your radio button or checkbox options into three columns.', 'formidable')),
    	            'frm_four_col' => array('label' => __('4 Col Options', 'formidable'), 'title' => __('Put your radio button or checkbox options into four columns.', 'formidable')),
    	            'frm_total' => array('label' => __('Total', 'formidable'), 'title' => __('Add this to a read-only field to display the text in bold without a border or background.', 'formidable')),
    	            'frm_scroll_box' => array('label' => __('Scroll Box', 'formidable'), 'title' => __('If you have many checkbox or radio button options, you may add this class to allow your user to easily scroll through the options.', 'formidable'))
    	        );
    	        $classes = apply_filters('frm_layout_classes', $classes);
    	        $col = 'one';
    	        foreach($classes as $c => $d){
    	            $title = (!empty($d) and is_array($d) and isset($d['title'])) ? $d['title'] : '';
    	        ?>
    	        <li class="frm_col_<?php echo $col ?>">
                    <a class="frmbutton frm_insert_code button show_frm_classes<?php if(!empty($title)) echo ' frm_help'; ?>" data-code="<?php echo esc_attr($c) ?>" href="javascript:void(0)" <?php if(!empty($title)){ ?>title="<?php echo esc_attr($title); ?>"<?php } ?>>
                        <?php 
                        if(empty($d))
                            echo $c;
                        else if(!is_array($d))
                            echo $d;
                        else if(isset($d['label']))
                            echo $d['label'];
                        ?>
                    </a>
                </li>
                <?php
                    $col = ($col == 'one') ? 'two' : 'one';
    	            unset($c);
    	            unset($d);
    	        }
    	    ?>
    	    </ul>
    	</div>
    	
    	<?php 
    	$action = isset($_REQUEST['frm_action']) ? 'frm_action' : 'action';
        $action = FrmAppHelper::get_param($action);
        $button = ($action == 'new' or $action == 'duplicate') ? __('Create', 'formidable') : __('Update', 'formidable');
        ?>
    	<form method="post" id="frm_js_build_form">
    	    <input type="hidden" id="frm_compact_fields" name="frm_compact_fields" value="" />
    	    <p><input type="button" onclick="frmSubmit<?php echo (isset($values['ajax_load']) and $values['ajax_load']) ? 'Build' : 'NoAjax'; ?>(this)" value="<?php echo esc_attr($button) ?>" class="frm_submit_form button-primary" id="frm_submit_side" />
    	        <span class="frm-loading-img"></span>
    	    </p>
    	</form>
    </div>
    </div>
    </div>
</div>