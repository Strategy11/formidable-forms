<?php 
global $frm_vars, $frm_settings;
FrmFormsHelper::form_loaded($form);
if($values['custom_style']) $frm_vars['load_css'] = true;

if((!isset($frm_vars['css_loaded']) || !$frm_vars['css_loaded']) && $frm_vars['load_css']){
echo FrmAppController::footer_js('header');
$frm_vars['css_loaded'] = true;
}

echo FrmFormsHelper::replace_shortcodes($values['before_html'], $form, $title, $description); ?>
<div class="frm_form_fields <?php echo apply_filters('frm_form_fields_class', '', $values); ?>">
<fieldset>
<div>
<input type="hidden" name="frm_action" value="<?php echo esc_attr($form_action) ?>" />
<input type="hidden" name="form_id" value="<?php echo esc_attr($form->id) ?>" />
<input type="hidden" name="form_key" value="<?php echo esc_attr($form->form_key) ?>" />
<?php wp_nonce_field('frm_submit_entry_nonce', 'frm_submit_entry_'. $form->id); ?>

<?php if (isset($id)){ ?><input type="hidden" name="id" value="<?php echo esc_attr($id) ?>" /><?php } ?>
<?php if (isset($controller) && isset($plugin)){ ?>
<input type="hidden" name="controller" value="<?php echo esc_attr($controller); ?>" />
<input type="hidden" name="plugin" value="<?php echo esc_attr($plugin); ?>" />
<?php }

if($values['fields']){
foreach($values['fields'] as $field){
    $field_name = 'item_meta['. $field['id'] .']';
    if (apply_filters('frm_show_normal_field_type', true, $field['type']))
        echo FrmFieldsHelper::replace_shortcodes($field['custom_html'], $field, $errors, $form);
    else
        do_action('frm_show_other_field_type', $field, $form, array('action' => $form_action));
    
    do_action('frm_get_field_scripts', $field, $form);
}    
}

if ((is_admin() and !defined('DOING_AJAX')) and !$frm_settings->lock_keys){ ?>
<div class="frm_form_field form-field">
<label class="frm_primary_label"><?php _e('Entry Key', 'formidable') ?></label>   
<input type="text" name="item_key" value="<?php echo esc_attr($values['item_key']) ?>" />
</div>
<?php }else{ ?>
<input type="hidden" name="item_key" value="<?php echo esc_attr($values['item_key']) ?>" />
<?php }

do_action('frm_entry_form', $form, $form_action, $errors);

global $frm_vars;
if(isset($frm_vars['div']) and $frm_vars['div']){
    echo "</div>\n";
    $frm_vars['div'] = false;
} ?>
</div>
</fieldset>
</div>
<?php echo FrmFormsHelper::replace_shortcodes($values['after_html'], $form); 

global $wp_filter;
if(isset($wp_filter['frm_entries_footer_scripts']) and !empty($wp_filter['frm_entries_footer_scripts'])){ ?>
<script type="text/javascript">
<?php do_action('frm_entries_footer_scripts', $values['fields'], $form); ?>
</script><?php }

if ( !$form->is_template && $form->status == 'published' && (!is_admin() || defined('DOING_AJAX')) ) {
    unset($values['fields']);
    FrmFormsHelper::get_custom_submit($values['submit_html'], $form, $submit, $form_action, $values);
}
