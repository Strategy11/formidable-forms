<?php

if ( $params['action'] == 'create' && $params['posted_form_id'] == $form->id && $_POST ) {
    
    if ( !empty($errors) ) {
        $values = $fields ? FrmEntriesHelper::setup_new_vars($fields, $form) : array();
        require(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/new.php');
        return;
    }
    
    do_action('frm_validate_form_creation', $params, $fields, $form, $title, $description);
    if ( !apply_filters('frm_continue_to_create', true, $form->id) ) {
        return;
    }
        
    $values = FrmEntriesHelper::setup_new_vars($fields, $form, true);
    $created = (isset($frm_vars['created_entries']) && isset($frm_vars['created_entries'][$form->id])) ? $frm_vars['created_entries'][$form->id]['entry_id'] : 0;
    $conf_method = apply_filters('frm_success_filter', 'message', $form, $form->options, 'create');
            
    if ( !$created || !is_numeric($created) || $conf_method == 'message' ) {
        $saved_message = apply_filters('frm_content', $saved_message, $form, $created);
        $message = ($created && is_numeric($created)) ? '<div class="frm_message" id="message">'. wpautop(do_shortcode($saved_message)) .'</div>' : '<div class="frm_error_style">'. $frm_settings->failed_msg .'</div>';
                
        if ( !isset($form->options['show_form']) || $form->options['show_form'] ) {
            require(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/new.php');
        } else { 
            global $frm_vars;
            FrmFormsHelper::form_loaded($form);
            if ( $values['custom_style'] ) {
                $frm_vars['load_css'] = true;
            }
                
            if ( (!isset($frm_vars['css_loaded']) || !$frm_vars['css_loaded']) && $frm_vars['load_css'] ) {
                echo FrmAppController::footer_js('header');
                $frm_vars['css_loaded'] = true;
            }
            ?>
<div class="frm_forms<?php echo ($values['custom_style']) ? ' with_frm_style' : ''; ?>" id="frm_form_<?php echo $form->id ?>_container"><?php require(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/errors.php') ?></div>
<?php
        }
    } else {
        do_action('frm_success_action', $conf_method, $form, $form->options, $created);
    }
        
    do_action('frm_after_entry_processed', array( 'entry_id' => $created, 'form' => $form));
}else{
    do_action('frm_display_form_action', $params, $fields, $form, $title, $description);
    if (apply_filters('frm_continue_to_new', true, $form->id, $params['action'])){
        $values = FrmEntriesHelper::setup_new_vars($fields, $form);
        require(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/new.php');
    }
}
