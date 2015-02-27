<div id="form_settings_page" class="wrap">
    <div class="frmicon icon32"><br/></div>
    <h2><?php _e('Settings', 'formidable') ?>
        <a href="#" class="add-new-h2" style="visibility:hidden;"></a>
    </h2>
    <?php FrmAppController::get_form_nav($id, true);
	require(FrmAppHelper::plugin_path() .'/classes/views/shared/errors.php');
	    
    if(version_compare( $GLOBALS['wp_version'], '3.3.3', '<')){ ?>
    <div id="poststuff" class="metabox-holder has-right-sidebar">
    <?php   
        require(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/sidebar-settings.php'); 
    }else{ ?>
    <div id="poststuff">
    <?php } ?>
    
        <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">
                 
<form method="post" class="frm_form_settings">     
    <p style="clear:left; margin-top:0;">        
        <input type="submit" value="<?php _e('Update', 'formidable') ?>" class="button-primary" />
        <?php _e('or', 'formidable') ?>
        <a class="button-secondary cancel" href="<?php echo esc_url(admin_url('admin.php?page=formidable') . '&frm_action=edit&id='. $id) ?>"><?php _e('Cancel', 'formidable') ?></a>
        <?php do_action('frm_settings_buttons', $values); ?>
    </p>
    
    <div class="clear"></div> 

    <input type="hidden" name="id" value="<?php echo $id; ?>" />
    <input type="hidden" name="frm_action" value="update_settings" />
    <div id="poststuff" class="metabox-holder">
    <div id="post-body">
        <div class="meta-box-sortables">
        <div class="categorydiv postbox">
        <h3 class="hndle"><span><?php echo FrmAppHelper::truncate($values['name'], 40) .' '. __('Settings', 'formidable') ?></span></h3>
        <div class="inside frm-help-tabs">
        <div id="contextual-help-back"></div>
        <div id="contextual-help-columns">
        <div class="contextual-help-tabs">
        <ul class="frm-category-tabs frm-form-setting-tabs">
            <?php $a = isset($_GET['t']) ? $_GET['t'] : 'advanced_settings'; ?>
        	<li <?php echo ($a == 'advanced_settings') ? 'class="tabs active"' : '' ?>><a href="#advanced_settings"><?php _e('General', 'formidable') ?></a></li>
        	<li <?php echo ($a == 'notification_settings') ? 'class="tabs active"' : '' ?>><a href="#notification_settings"><?php _e('Emails', 'formidable') ?></a></li>
            <li <?php echo ($a == 'html_settings') ? 'class="tabs active"' : '' ?>><a href="#html_settings"><?php _e('Customize HTML', 'formidable') ?></a></li>
            <li <?php echo ($a == 'post_settings') ? 'class="tabs active"' : '' ?>><a href="#post_settings"><?php _e('Create Posts', 'formidable') ?></a></li>
            <?php foreach($sections as $sec_name => $section){ ?>
                <li <?php echo ($a == $sec_name .'_settings') ? 'class="tabs active"' : '' ?>><a href="#<?php echo $sec_name ?>_settings"><?php echo ucfirst($sec_name) ?></a></li>
            <?php } ?>
        </ul>
        </div>
        <div style="display:<?php echo ($a == 'advanced_settings') ? 'block' : 'none'; ?>;" class="advanced_settings tabs-panel">
        	<table class="form-table">                
                <tr><td colspan="2"><label for="custom_style"><input type="checkbox" name="options[custom_style]" id="custom_style" <?php echo ($values['custom_style']) ? ' checked="checked"' : ''; ?> value="1" />
                    <?php _e('Use Formidable styling for this form', 'formidable') ?></label></td>
                </tr>

                <tr><td colspan="2"><label class="frm_left_label"><?php _e('Submit Button Text', 'formidable') ?></label>
                    <input type="text" name="options[submit_value]" value="<?php echo esc_attr($values['submit_value']); ?>" /></td>
                </tr>
                
                <tr><td colspan="2"><label><?php _e('Action After Form Submission', 'formidable') ?></label>
                    <?php if(!$frm_vars['pro_is_installed']){ ?>
                    <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php _e('You must upgrade to Formidable Pro to get access to the second two options.', 'formidable') ?>" ></span>
                    <?php } ?><br/>

                        <label for="success_action_message"><input type="radio" name="options[success_action]" id="success_action_message" value="message" <?php checked($values['success_action'], 'message') ?> /> <?php _e('Display a Message', 'formidable') ?></label>
                        <label for="success_action_page" <?php echo $pro_feature ?>><input type="radio" name="options[success_action]" id="success_action_page" value="page" <?php checked($values['success_action'], 'page') ?> <?php if(!$frm_vars['pro_is_installed']) echo 'disabled="disabled" '; ?>/> <?php _e('Display content from another page', 'formidable') ?></label>
                        <label for="success_action_redirect" <?php echo $pro_feature ?>><input type="radio" name="options[success_action]" id="success_action_redirect" value="redirect" <?php checked($values['success_action'], 'redirect') ?> <?php if(!$frm_vars['pro_is_installed']) echo 'disabled="disabled" '; ?>/> <?php _e('Redirect to URL', 'formidable') ?></label>
                        
                        <p class="frm_indent_opt success_action_redirect_box success_action_box" <?php echo ($values['success_action'] == 'redirect') ? '' : 'style="display:none;"'; ?>>
                            <input type="text" name="options[success_url]" id="success_url" value="<?php if(isset($values['success_url'])) echo esc_attr($values['success_url']); ?>" style="width:98%" placeholder="http://example.com" />
                        </p>
                        
                        <div class="frm_indent_opt success_action_message_box success_action_box" <?php echo ($values['success_action'] == 'message') ? '' : 'style="display:none;"'; ?>>
                            <p><textarea id="success_msg" name="options[success_msg]" cols="50" rows="2" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea($values['success_msg']); ?></textarea></p>
                            <p class="frm_show_form_opt">
                            <label for="show_form"><input type="checkbox" name="options[show_form]" id="show_form" value="1" <?php checked($values['show_form'], 1) ?> /> <?php _e('Show the form with the confirmation message', 'formidable')?></label>
                            </p>
                        </div>
                        
                        <?php if($frm_vars['pro_is_installed']){ ?>
                        <p class="frm_indent_opt success_action_page_box success_action_box" <?php echo ($values['success_action'] == 'page') ? '' : 'style="display:none;"'; ?>>
                            <label><?php _e('Use Content from Page', 'formidable') ?></label>
                            <?php FrmAppHelper::wp_pages_dropdown( 'options[success_page_id]', $values['success_page_id'] ) ?>
                        </p>
                        <?php } ?>
                    </td>
                </tr>
                
                <tr><td colspan="2"><label for="ajax_load"><input type="checkbox" name="options[ajax_load]" id="ajax_load" value="1"<?php echo ($values['ajax_load']) ? ' checked="checked"' : ''; ?> /> <?php _e('Load and save form builder page with AJAX', 'formidable') ?></label> <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php _e('Recommended for long forms.', 'formidable') ?>" ></span></td></tr>


                <?php do_action('frm_additional_form_options', $values); ?> 
                
                <tr><td colspan="2"><label for="no_save"><input type="checkbox" name="options[no_save]" id="no_save" value="1" <?php checked($values['no_save'], 1); ?> /> <?php _e('Do not store any entries submitted from this form.', 'formidable') ?> <span class="howto"><?php _e('Warning: There is no way to retrieve unsaved entries.', 'formidable') ?></span></label></td></tr>
                
                <?php if (function_exists( 'akismet_http_post' )){ ?>
                <tr><td colspan="2"><?php _e('Use Akismet to check entries for spam for', 'formidable') ?>
                        <select name="options[akismet]">
                            <option value=""><?php _e('no one', 'formidable') ?></option>
                            <option value="1" <?php selected($values['akismet'], 1)?>><?php _e('everyone', 'formidable') ?></option>
                            <option value="logged" <?php selected($values['akismet'], 'logged')?>><?php _e('visitors who are not logged in', 'formidable') ?></option>
                        </select>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>

        <?php
            $first_email = true;
            foreach($values['notification'] as $email_key => $notification){
                include(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/notification.php');
                unset($email_key);
                unset($notification);
                $first_email = false;
            } 
        if($frm_vars['pro_is_installed']){ ?>
        <div id="frm_email_add_button" class="notification_settings hide_with_tabs submit" style="display:<?php echo ($a == 'notification_settings') ? 'block' : 'none'; ?>;">
            <a href="javascript:frmAddEmailList(<?php echo $values['id'] ?>)" class="button-secondary">+ <?php _e('Add Notification', 'formidable') ?></a></td>
        </div>
        <?php } ?>
        
        <div id="html_settings" class="tabs-panel" style="display:<?php echo ($a == 'html_settings') ? 'block' : 'none'; ?>;">
            
            <div id="post-body-content" class="frm_top_container" style="margin-right:260px;">
                <p><label class="frm_primary_label"><?php _e('Before Fields', 'formidable') ?></label>
                <textarea name="options[before_html]" rows="4" id="before_html" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea($values['before_html']) ?></textarea></p>

                <div id="add_html_fields">
                    <?php 
                    if (isset($values['fields'])){
                        foreach($values['fields'] as $field){
                            if (apply_filters('frm_show_custom_html', true, $field['type'])){ ?>
                                <p><label class="frm_primary_label"><?php echo $field['name'] ?></label>
                                <textarea name="field_options[custom_html_<?php echo $field['id'] ?>]" rows="7" id="custom_html_<?php echo $field['id'] ?>" class="field_custom_html frm_long_input"><?php echo FrmAppHelper::esc_textarea($field['custom_html']) ?></textarea></p>
                            <?php }
                            unset($field);
                        }
                    } ?>
                </div>

                <p><label class="frm_primary_label"><?php _e('After Fields', 'formidable') ?></label>
                <textarea name="options[after_html]" rows="3" id="after_html" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea($values['after_html']) ?></textarea></p> 
                
                <p><label class="frm_primary_label"><?php _e('Submit Button', 'formidable') ?></label>
                <textarea name="options[submit_html]" rows="3" id="submit_html" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea($values['submit_html']) ?></textarea></p>
            </div>
        </div>
        <div id="post_settings" class="tabs-panel" style="display:<?php echo ($a == 'post_settings') ? 'block' : 'none'; ?>;">
            <?php if($frm_vars['pro_is_installed'])
                FrmProFormsController::post_options($values);
            else
                FrmAppController::update_message('create and edit posts, pages, and custom post types through your forms');
            ?>
        </div>
        
        <?php foreach($sections as $sec_name => $section){ ?>
            <div id="<?php echo $sec_name ?>_settings" class="tabs-panel" style="display:<?php echo ($a == $sec_name .'_settings') ? 'block' : 'none'; ?>;"><?php
            if(isset($section['class'])){
                call_user_func(array($section['class'], $section['function']), $values); 
            }else{
                call_user_func((isset($section['function']) ? $section['function'] : $section), $values); 
            } ?>
            </div>
        <?php } ?>
    
        <?php do_action('frm_add_form_option_section', $values); ?>
        <div class="clear"></div>
        </div>
        </div>
        </div>
        </div>
</div>

</div>

    <p>        
        <input type="submit" value="<?php _e('Update', 'formidable') ?>" class="button-primary" />
        <?php _e('or', 'formidable') ?>
        <a class="button-secondary cancel" href="<?php echo admin_url('admin.php?page=formidable') ?>&amp;frm_action=edit&amp;id=<?php echo $id ?>"><?php _e('Cancel', 'formidable') ?></a>
    </p>
    </form>


    </div>
    <?php
        if(version_compare( $GLOBALS['wp_version'], '3.3.2', '>'))
            require(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/sidebar-settings.php'); 
    ?>
    </div>
</div>
</div>
