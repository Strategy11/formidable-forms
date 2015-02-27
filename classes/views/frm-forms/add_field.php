<?php
if(isset($values) and isset($values['ajax_load']) and $values['ajax_load'] and isset($count) and $count > 10){ ?>
<li id="frm_field_id_<?php echo $field['id']; ?>" class="form-field frm_field_box frm_field_loading edit_form_item frm_top_container" data-triggered="0">
<img src="<?php echo FrmAppHelper::plugin_url() ?>/images/ajax_loader.gif" alt="<?php _e('Loading', 'formidable') ?>" />
<span class="frm_hidden_fdata frm_hidden"><?php echo htmlspecialchars(json_encode($field)) ?></span>
</li>
<?php
   return;
}

global $frm_settings;
if(!isset($frm_all_field_selection)){
    if(isset($frm_field_selection) and isset($frm_pro_field_selection)){
        $frm_all_field_selection = array_merge($frm_field_selection, $frm_pro_field_selection);
    }else{
        $frm_pro_field_selection = FrmFieldsHelper::pro_field_selection();
        $frm_all_field_selection = array_merge(FrmFieldsHelper::field_selection(), $frm_pro_field_selection);
    }
}

if(!isset($frm_vars))
    global $frm_vars;
    
$disabled_fields = ($frm_vars['pro_is_installed']) ? array() : $frm_pro_field_selection;

 
$display = apply_filters('frm_display_field_options', array(
    'type' => $field['type'], 'field_data' => $field,
    'required' => true, 'unique' => false, 'read_only' => false,
    'description' => true, 'options' => true, 'label_position' => true, 
    'invalid' => false, 'size' => false, 'clear_on_focus' => false, 
    'default_blank' => true, 'css' => true
)); ?>
<?php if(!isset($ajax)){ ?>
<li id="frm_field_id_<?php echo $field['id']; ?>" class="form-field edit_form_item frm_field_box ui-state-default edit_field_type_<?php echo $display['type'] ?> frm_top_container">
<?php } ?>
    <a href="javascript:void(0);" class="frm_bstooltip alignright frm-show-hover frm-move frm-hover-icon frm_icon_font frm_move_field" title="<?php esc_attr_e('Move Field', 'formidable') ?>"> </a>
    <a href="javascript:frm_delete_field(<?php echo $field['id']; ?>)" class="frm_bstooltip alignright frm-show-hover frm-hover-icon frm_icon_font frm_delete_icon" id="frm_delete_field<?php echo $field['id']; ?>" title="<?php esc_attr_e('Delete Field', 'formidable') ?>"> </a>
    <a href="javascript:frm_duplicate_field(<?php echo $field['id']; ?>)" class="frm_bstooltip alignright frm-show-hover frm-hover-icon frm_icon_font frm_duplicate_form" title="<?php esc_attr_e('Duplicate Field', 'formidable') ?>"> </a>
    <input type="hidden" name="frm_fields_submitted[]" value="<?php echo esc_attr($field['id']) ?>" />
    <?php do_action('frm_extra_field_actions', $field['id']); ?>
    <?php if ($display['required']){ ?>
    <span id="require_field_<?php echo $field['id']; ?>">
        <a class="frm_req_field frm_action_icon frm_required_icon frm_icon_font alignleft frm_required<?php echo (int)$field['required'] ?>" id="req_field_<?php echo $field['id']; ?>" title="Click to Mark as <?php echo ($field['required'] == '0') ? '' : 'not '; ?>Required"></a>
    </span>
    <?php } ?>
    <label class="frm_ipe_field_label frm_primary_label <?php echo ($field['type'] == 'break') ? 'button': ''; ?>" id="field_label_<?php echo $field['id']; ?>"><?php echo force_balance_tags($field['name']) ?></label>

<div class="frm_form_fields" data-ftype="<?php echo $display['type'] ?>"> 
<?php if ($display['type'] == 'text'){ ?>
    <input type="text" name="<?php echo $field_name ?>" id="field_<?php echo $field['field_key'] ?>" value="<?php echo esc_attr($field['default_value']); ?>" <?php echo (isset($field['size']) && $field['size']) ? 'style="width:auto" size="'. $field['size'] .'"' : ''; ?> class="dyn_default_value" /> 
<?php }else if ($field['type'] == 'textarea'){ ?>
    <textarea name="<?php echo $field_name ?>"<?php if ($field['size']) echo ' style="width:auto" cols="'. $field['size'] .'"' ?> rows="<?php echo $field['max']; ?>" id="field_<?php echo $field['field_key'] ?>" class="dyn_default_value"><?php echo FrmAppHelper::esc_textarea(force_balance_tags($field['default_value'])); ?></textarea> 
  
<?php 

}else if ($field['type'] == 'radio' or $field['type'] == 'checkbox'){
    $field['default_value'] = maybe_unserialize($field['default_value']); 
    if(isset($field['post_field']) and $field['post_field'] == 'post_category'){
        do_action('frm_after_checkbox', array('field' => $field, 'field_name' => $field_name, 'type' => $field['type']));
    }else{ ?>
        <div id="frm_field_<?php echo $field['id'] ?>_opts" class="clear<?php echo (count($field['options']) > 10) ? ' frm_field_opts_list' : ''; ?>">
        <?php do_action('frm_add_multiple_opts_labels', $field); ?>
        <?php include(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/radio.php'); ?>
        </div>
    <?php
    }
    ?>

    <div class="frm-show-click" style="margin-top:5px;">
<?php

    if ( isset($field['post_field']) && $field['post_field'] == 'post_category' ) {
        echo '<p class="howto">'. FrmFieldsHelper::get_term_link($field['taxonomy']) .'</p>';
    } else if ( !isset($field['post_field']) || $field['post_field'] != 'post_status' ) {
?>
    <div id="frm_add_field_<?php echo $field['id']; ?>">
        <a href="javascript:frm_add_field_option(<?php echo $field['id']; ?>)" class="frm_orange frm_add_opt">+ <?php _e('Add an Option', 'formidable') ?></a>
        
        <?php _e('or', 'formidable'); ?>
        <a title="<?php echo FrmAppHelper::truncate(esc_attr(strip_tags(str_replace('"', '&quot;', $field['name']))), 20) . ' '. __('Field Choices', 'formidable'); ?>" href="<?php echo esc_url(admin_url('admin-ajax.php') .'?action=frm_import_choices&field_id='. $field['id'] .'&TB_iframe=1') ?>" class="thickbox frm_orange"><?php _e('Bulk Edit Field Choices', 'formidable') ?></a>
    </div>
<?php
    }
?>
    </div>
<?php
}else if ($field['type'] == 'select'){ 
    if(isset($field['post_field']) and $field['post_field'] == 'post_category'){
        echo FrmFieldsHelper::dropdown_categories(array('name' => $field_name, 'field' => $field) );
    }else{ ?>
    <select name="<?php echo $field_name; echo (isset($field['multiple']) and $field['multiple']) ? '[]' : ''; ?>" <?php 
        echo (isset($field['size']) && $field['size']) ? 'style="width:auto"' : '';
        echo (isset($field['multiple']) and $field['multiple']) ? ' multiple="multiple"' : ''; ?> >
        <?php foreach ($field['options'] as $opt_key => $opt){ 
            $field_val = apply_filters('frm_field_value_saved', $opt, $opt_key, $field);
            $opt = apply_filters('frm_field_label_seen', $opt, $opt_key, $field);
            $selected = ($field['default_value'] == $field_val)?(' selected="selected"'):(''); ?>
            <option value="<?php echo $field_val ?>"<?php echo $selected ?>><?php echo $opt ?></option>
        <?php } ?>
    </select>
<?php }
    if ($display['default_blank']){ ?>
        <span id="frm_clear_on_focus_<?php echo $field['id'] ?>" class="frm-show-click">
        <?php FrmFieldsHelper::show_default_blank_js($field['id'], $field['default_blank']); ?>
        </span>
    <?php } ?>
    <div class="clear"></div>
    <div class="frm-show-click" style="margin-top:5px;">
    <?php 
    
    if ( isset($field['post_field']) && $field['post_field'] == 'post_category' ) {
        echo '<p class="howto">'. FrmFieldsHelper::get_term_link($field['taxonomy']) .'</p>';
    } else if ( !isset($field['post_field']) || $field['post_field'] != 'post_status' ) { ?>
        <div id="frm_field_<?php echo $field['id'] ?>_opts"<?php echo (count($field['options']) > 10) ? ' class="frm_field_opts_list"' : ''; ?>>
        <?php do_action('frm_add_multiple_opts_labels', $field); ?>
        <?php 
        
        foreach ( $field['options'] as $opt_key => $opt ) {
            $field_val = apply_filters('frm_field_value_saved', $opt, $opt_key, $field);
            $opt = apply_filters('frm_field_label_seen', $opt, $opt_key, $field);
            require(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/single-option.php');
        }
        ?>
        </div>
        <div id="frm_add_field_<?php echo $field['id']; ?>">
            <a href="javascript:frm_add_field_option(<?php echo $field['id']; ?>)" class="frm_orange frm_add_opt">+ <?php _e('Add an Option', 'formidable') ?></a>
            
            <?php if (!isset($field['post_field']) or $field['post_field'] != 'post_category'){ ?>
            <?php _e('or', 'formidable'); ?>
            <a title="<?php echo FrmAppHelper::truncate(esc_attr(strip_tags(str_replace('"', '&quot;', $field['name']))), 20) . ' '. __('Field Choices', 'formidable'); ?>" href="<?php echo esc_url(admin_url('admin-ajax.php') .'?action=frm_import_choices&field_id='. $field['id'] .'&TB_iframe=1') ?>" class="thickbox frm_orange"><?php _e('Bulk Edit Field Choices', 'formidable') ?></a>
            <?php } ?>
        </div>
<?php 
    } ?>
    </div>
<?php
}else if ($field['type'] == 'captcha'){ 
?>
    <img src="<?php echo FrmAppHelper::plugin_url() ?>/images/<?php echo $frm_settings->re_theme ?>-captcha.png" alt="captcha" />
    <p class="howto" style="margin-top:0;"><?php printf(__('Hint: Change colors in the %1$sFormidable settings', 'formidable'), '<a href="?page=formidable-settings">') ?></a></p>
    <div class="clear"></div>
    <?php if (empty($frm_settings->pubkey)){ ?>
    <div class="howto" style="font-weight:bold;color:red;"><?php printf(__('Your captcha will not appear on your form until you %1$sset up%2$s the Public and Private Keys', 'formidable'), '<a href="?page=formidable-settings">', '</a>') ?></div>
    <?php } ?>
    <input type="hidden" name="<?php echo $field_name ?>" value="1"/>
<?php 
}else{
    do_action('frm_display_added_fields',$field);
} 

if ($display['clear_on_focus']){ ?>
    <span id="frm_clear_on_focus_<?php echo $field['id'] ?>" class="frm-show-click">
<?php
if ($display['default_blank'])
    FrmFieldsHelper::show_default_blank_js($field['id'], $field['default_blank']);

    FrmFieldsHelper::show_onfocus_js($field['id'], $field['clear_on_focus']);    
?>
    </span>
<?php        
    
    do_action('frm_extra_field_display_options', $field);
} 
?>
<div class="clear"></div>
</div>
<?php
if ($display['description']){ ?> 
    <div class="frm_ipe_field_desc description frm-show-click" id="field_description_<?php echo $field['id']; ?>"><?php echo ($field['description'] == '') ? __('(Click here to add a description or instructions)', 'formidable') : force_balance_tags($field['description']); ?></div> 
<?php
}

if ($display['options']){ ?>
    <div class="widget">
        <div class="widget-top">
    	    <div class="widget-title-action"><a class="widget-action"></a></div>
    		<div class="widget-title"><h4><?php _e('Field Options', 'formidable') ?> (ID <?php echo $field['id'] ?>)</h4></div>
        </div>
    	<div class="widget-inside">
            <table class="form-table" style="clear:none;">
                <?php $field_types = FrmFieldsHelper::get_field_types($field['type']); ?>
                <tr><td width="150px"><label><?php _e('Field Type', 'formidable') ?></label></td>
                    <td>
                        <div class="hide-if-no-js edit-slug-box frm_help" title="<?php _e('The field key can be used as an alternative to the field ID in many cases.', 'formidable') ?>">
                            <?php _e('Field Key:', 'formidable') ?>
                            <div class="<?php echo ($frm_settings->lock_keys) ? 'frm_field_key' : 'frm_ipe_field_key" title="'. __('Click to edit.', 'formidable'); ?>" ><?php echo $field['field_key']; ?></div>
                            <?php if(!$frm_settings->lock_keys){ ?>
                            <input type="hidden" name="field_options[field_key_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['field_key']); ?>" />
                            <?php } ?>
                        </div>
                        
                <?php if (!empty($field_types)){ ?>
                    <select name="field_options[type_<?php echo $field['id'] ?>]">
                    <?php foreach ($field_types as $fkey => $ftype){ ?>
                        <option value="<?php echo $fkey ?>" <?php echo ($fkey == $field['type']) ? ' selected="selected"' : ''; ?> <?php echo array_key_exists($fkey, $disabled_fields ) ? 'disabled="disabled"' : '';  ?>><?php echo $ftype ?></option>
                    <?php
                            unset($fkey);
                            unset($ftype);
                        } ?>
                    </select>
                <?php }else if(isset($frm_all_field_selection[$field['type']])){ ?>
                    <select disabled="disabled">
                        <option value=""><?php echo $frm_all_field_selection[$field['type']] ?></option>
                    </select>
                <?php } ?>
                <?php if ($display['required']){ ?>
                    <label for="frm_req_field_<?php echo $field['id'] ?>" class="frm_inline_label"><input type="checkbox" id="frm_req_field_<?php echo $field['id'] ?>" class="frm_req_field" name="field_options[required_<?php echo $field['id'] ?>]" value="1" <?php echo ($field['required']) ? 'checked="checked"': ''; ?> /> <?php _e('Required', 'formidable') ?></label>
                <?php } ?>
                <?php if($display['unique']){ 
                    if(!isset($field['unique']))
                        $field['unique'] = false;
                ?>
                <label for="frm_uniq_field_<?php echo $field['id'] ?>" class="frm_inline_label frm_help" title="<?php _e('Unique: Do not allow the same response multiple times. For example, if one user enters \'Joe\' then no one else will be allowed to enter the same name.', 'formidable') ?>"><input type="checkbox" name="field_options[unique_<?php echo $field['id'] ?>]" id="frm_uniq_field_<?php echo $field['id'] ?>" value="1" <?php echo $field['unique'] ? ' checked="checked"' : ''; ?> onclick="frmMarkUnique(<?php echo $field['id'] ?>,<?php echo ($field['unique']) ? 1 : 0; ?>)"/> <?php _e('Unique', 'formidable') ?></label>
                <?php } ?>
                <?php if($display['read_only']){ 
                    if(!isset($field['read_only']))
                        $field['read_only'] = false;
                ?>
                <label for="frm_read_only_field_<?php echo $field['id'] ?>" class="frm_inline_label frm_help" title="<?php _e('Read Only: Show this field but do not allow the field value to be edited from the front-end.', 'formidable') ?>" ><input type="checkbox" id="frm_read_only_field_<?php echo $field['id'] ?>" name="field_options[read_only_<?php echo $field['id'] ?>]" value="1" <?php echo $field['read_only'] ? ' checked="checked"' : ''; ?>/> <?php _e('Read Only', 'formidable') ?></label>
                <?php } ?>
                
                <?php if ($display['required']){ ?>
                <div class="frm_required_details<?php echo $field['id'] . ( $field['required'] ? '' : ' frm_hidden'); ?>">
                    <span class="howto"><?php _e('Indicate required field with', 'formidable') ?></span>
                    <input type="text" name="field_options[required_indicator_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['required_indicator']); ?>" />
                </div>
                <?php } ?>
                    </td>
                </tr>
                
                <?php if ($display['css']){ ?>
                <tr><td><label><?php _e('CSS layout classes', 'formidable') ?></label>
                    <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php _e('Add a CSS class to the field container. Use our predefined classes to align multiple fields in single row.', 'formidable') ?>" ></span>
                    </td>
                    <td><input type="text" name="field_options[classes_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['classes']) ?>" id="frm_classes_<?php echo $field['id'] ?>" class="frm_classes frm_long_input" />
                    </td>  
                </tr>
                <?php } ?>
                <?php if ($display['label_position']){ ?>
                    <tr><td width="150px"><label><?php _e('Label Position', 'formidable') ?></label></td>
                        <td><select name="field_options[label_<?php echo $field['id'] ?>]">
                            <option value=""<?php selected($field['label'], ''); ?>><?php _e('Default', 'formidable') ?></option>
                            <option value="top"<?php selected($field['label'], 'top'); ?>><?php _e('Top', 'formidable') ?></option>
                            <option value="left"<?php selected($field['label'], 'left'); ?>><?php _e('Left', 'formidable') ?></option>
                            <option value="right"<?php selected($field['label'], 'right'); ?>><?php _e('Right', 'formidable') ?></option>
                            <option value="inline"<?php selected($field['label'], 'inline'); ?>><?php _e('Inline (left without a set width)', 'formidable') ?></option>
                            <option value="none"<?php selected($field['label'], 'none'); ?>><?php _e('None', 'formidable') ?></option>
                            <option value="hidden"<?php selected($field['label'], 'hidden'); ?>><?php _e('Hidden (but leave the space)', 'formidable') ?></option>
                        </select>
                        </td>  
                    </tr>
                <?php } ?>
                <?php if ($display['size']){ ?>
                    <tr><td width="150px"><label><?php _e('Field Size', 'formidable') ?></label></td>
                        <td>
                        <?php if(in_array($field['type'], array('select', 'time', 'data'))){ ?>
                            <?php if(!isset($values['custom_style']) or $values['custom_style']){ ?>
                                <label for="size_<?php echo $field['id'] ?>"><input type="checkbox" name="field_options[size_<?php echo $field['id'] ?>]" id="size_<?php echo $field['id'] ?>" value="1" <?php echo (isset($field['size']) and $field['size'])? 'checked="checked"':''; ?> /> <?php _e('automatic width', 'formidable') ?>
                            <?php }
                            }else{ ?>
                                <input type="text" name="field_options[size_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['size']); ?>" size="5" /> <span class="howto"><?php echo ($field['type'] == 'textarea' || $field['type'] == 'rte')? __('columns wide', 'formidable') : __('characters wide', 'formidable') ?></span>

                                <input type="text" name="field_options[max_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['max']); ?>" size="5" /> <span class="howto"><?php echo ($field['type'] == 'textarea' || $field['type'] == 'rte')? __('rows high', 'formidable') : __('characters maximum', 'formidable') ?></span>
                        <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                <?php do_action('frm_field_options_form', $field, $display, $values); ?>
                
                <?php if ($display['required'] or $display['invalid'] or $display['unique']){ ?>
                    <tr class="frm_validation_msg <?php echo ($display['invalid'] || $field['required'] || (isset($field['unique']) && $field['unique'])) ? '' : 'frm_hidden'; ?>"><td><?php _e('Validation', 'formidable') ?></td>
                    <td class="frm_validation_box">
                        <?php if ($display['required']){ ?>
                        <p class="frm_required_details<?php echo $field['id'] . ($field['required'] ? '' : ' frm_hidden'); ?>"><label><?php _e('Required', 'formidable') ?></label>
                            <input type="text" name="field_options[blank_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['blank']); ?>" />
                        </p>
                        <?php } ?>
                        <?php if ($display['invalid']){ ?>
                            <p><label><?php _e('Invalid Format', 'formidable') ?></label>
                                <input type="text" name="field_options[invalid_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['invalid']); ?>" />
                            </p>
                        <?php } ?>
                        <?php if($display['unique']){ ?>
                        <p class="frm_unique_details<?php echo $field['id'] . ($field['unique'] ? '' : ' frm_hidden'); ?>">
                            <label><?php _e('Unique', 'formidable') ?></label>
                            <input type="text" name="field_options[unique_msg_<?php echo $field['id'] ?>]" value="<?php echo esc_attr($field['unique_msg']); ?>" />
                        </p>
                        <?php } ?>
                        <?php if ($display['default_blank']){ //TODO ?>
                        <?php } ?>
                    </td>
                    </tr>
                <?php } ?>
                
            </table>
        </div>
    </div>
<?php } ?>
<?php if(!isset($ajax)){ ?>       
</li>
<?php } ?>
<?php unset($display); ?>