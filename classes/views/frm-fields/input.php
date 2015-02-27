<?php if ($field['type'] == 'text'){ ?>
<input type="text" id="field_<?php echo $field['field_key'] ?>" name="<?php echo $field_name ?>" value="<?php echo esc_attr($field['value']) ?>" <?php do_action('frm_field_input_html', $field) ?>/>
<?php }else if ($field['type'] == 'textarea'){ ?>
<textarea name="<?php echo $field_name ?>" id="field_<?php echo $field['field_key'] ?>"<?php if($field['size']) echo ' cols="'. $field['size'].'"'; if($field['max']) echo ' rows="'. $field['max'] .'"'; ?> <?php do_action('frm_field_input_html', $field) ?>><?php echo FrmAppHelper::esc_textarea($field['value']) ?></textarea>
<?php 

}else if ($field['type'] == 'radio'){
    if ( isset($field['post_field']) && $field['post_field'] == 'post_category' ) {
        do_action('frm_after_checkbox', array('field' => $field, 'field_name' => $field_name, 'type' => $field['type']));
    } else if ( is_array($field['options']) ) {
        foreach ( $field['options'] as $opt_key => $opt ) {
            if ( isset($atts) && isset($atts['opt']) && ($atts['opt'] != $opt_key) ) {
                continue;
            }
            
            $field_val = apply_filters('frm_field_value_saved', $opt, $opt_key, $field);
            $opt = apply_filters('frm_field_label_seen', $opt, $opt_key, $field);
            ?>
<div class="<?php echo apply_filters('frm_radio_class', 'frm_radio', $field, $field_val)?>"><?php

            if ( !isset($atts) || !isset($atts['label']) || $atts['label'] ) {
?><label for="field_<?php echo $field['id'] ?>-<?php echo $opt_key ?>"><?php
            }
                
?><input type="radio" name="<?php echo $field_name ?>" id="field_<?php echo $field['id'] ?>-<?php echo $opt_key ?>" value="<?php echo esc_attr($field_val) ?>" <?php echo (FrmAppHelper::check_selected($field['value'], $field_val)) ? 'checked="checked"' : ''; ?> <?php do_action('frm_field_input_html', $field) ?>/><?php

            if ( !isset($atts) || !isset($atts['label']) || $atts['label'] ) {
                echo ' '. $opt .'</label>';
            } 
?></div>
<?php
        }
    }

}else if ($field['type'] == 'select'){ 
    if(isset($field['post_field']) and $field['post_field'] == 'post_category'){
        echo FrmFieldsHelper::dropdown_categories(array('name' => $field_name, 'field' => $field) );
    }else{ 
        if ( isset($field['read_only']) && $field['read_only'] && (!isset($frm_vars['readonly']) || $frm_vars['readonly'] != 'disabled') && (!is_admin() || defined('DOING_AJAX')) ) { ?>
<input type="hidden" value="<?php echo esc_attr($field['value']) ?>" name="<?php echo $field_name ?>" id="field_<?php echo $field['field_key'] ?>" />
<select disabled="disabled" <?php do_action('frm_field_input_html', $field) ?>>
<?php   }else{ ?>        
<select name="<?php echo $field_name ?>" id="field_<?php echo $field['field_key'] ?>" <?php do_action('frm_field_input_html', $field) ?>>
<?php   } 
    foreach ($field['options'] as $opt_key => $opt){ 
        $field_val = apply_filters('frm_field_value_saved', $opt, $opt_key, $field);
        $opt = apply_filters('frm_field_label_seen', $opt, $opt_key, $field); ?>
<option value="<?php echo esc_attr($field_val) ?>" <?php
if (FrmAppHelper::check_selected($field['value'], $field_val)) echo ' selected="selected"'; ?>><?php echo ($opt == '') ? ' ' : $opt; ?></option>
    <?php } ?>
</select>
<?php }

}else if ($field['type'] == 'checkbox'){
    $checked_values = $field['value'];
    
    if(isset($field['post_field']) and $field['post_field'] == 'post_category'){
        do_action('frm_after_checkbox', array('field' => $field, 'field_name' => $field_name, 'type' => $field['type']));
    } else if ( $field['options'] ) {
        foreach ( $field['options'] as $opt_key => $opt ) {
            if ( isset($atts) && isset($atts['opt']) && ($atts['opt'] != $opt_key) ) {
                continue;
            }
            
            $field_val = apply_filters('frm_field_value_saved', $opt, $opt_key, $field);
            $opt = apply_filters('frm_field_label_seen', $opt, $opt_key, $field);
            $checked = FrmAppHelper::check_selected($checked_values, $field_val) ? ' checked="checked"' : '';
            
            ?>
<div class="<?php echo apply_filters('frm_checkbox_class', 'frm_checkbox', $field, $field_val) ?>" id="frm_checkbox_<?php echo $field['id']?>-<?php echo $opt_key ?>"><?php

            if ( !isset($atts) || !isset($atts['label']) || $atts['label'] ) {
                ?><label for="field_<?php echo $field['id'] ?>-<?php echo $opt_key ?>"><?php
            }
            
            ?><input type="checkbox" name="<?php echo $field_name ?>[]" id="field_<?php echo $field['id'] ?>-<?php echo $opt_key ?>" value="<?php echo esc_attr($field_val) ?>" <?php echo $checked ?> <?php do_action('frm_field_input_html', $field) ?> /><?php
            
            if ( !isset($atts) || !isset($atts['label']) || $atts['label'] ) {
                echo ' '. $opt .'</label>';
            }
            
            ?></div>
<?php
        }
    }

} else if ( $field['type'] == 'captcha' && (!is_admin() || defined('DOING_AJAX')) ) {
    global $frm_settings;
    $error_msg = null;
    
    if ( !empty($errors) ) {
        foreach ( $errors as $error_key => $error ) {
            if ( strpos($error_key, 'captcha-') === 0 ) {
                $error_msg = preg_replace('/^captcha-/', '', $error_key);
            }
            unset($error);
        }
    }
    
    if ( !empty($frm_settings->pubkey) ) {
        FrmFieldsHelper::display_recaptcha($field, $error_msg);
    }
} else {
    do_action('frm_form_fields', $field, $field_name);
}