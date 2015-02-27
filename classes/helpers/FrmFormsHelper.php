<?php
if(!defined('ABSPATH')) die('You are not allowed to call this page directly.');

if(class_exists('FrmFormsHelper'))
    return;

class FrmFormsHelper{
    public static function get_direct_link($key, $form = false ) {
        $target_url = esc_url(admin_url('admin-ajax.php') . '?action=frm_forms_preview&form='. $key);
        $target_url = apply_filters('frm_direct_link', $target_url, $key, $form);

        return $target_url;
    }
    
    public static function get_template_dropdown($templates) {
        if ( ! current_user_can('frm_edit_forms') ) {
            return;
        }
        ?>
        <select id="select_form" name="select_form" onChange="frmAddNewForm(this.value,'duplicate')">
            <option value="">&mdash; <?php _e('Create Form from Template', 'formidable') ?> &mdash;</option>
            <?php foreach ($templates as $temp){ ?>
                <option value="<?php echo $temp->id ?>"><?php echo FrmAppHelper::truncate($temp->name, 40) ?></option>
            <?php }?>
        </select> 
    <?php    
    }
    
    public static function forms_dropdown( $field_name, $field_value='', $blank=true, $field_id=false, $onchange=false ){
        if (!$field_id)
            $field_id = $field_name;
        
        $where = apply_filters('frm_forms_dropdown', "is_template=0 AND (status is NULL OR status = '' OR status = 'published')", $field_name);
        $frm_form = new FrmForm();
        $forms = $frm_form->getAll($where, ' ORDER BY name');
        ?>
        <select name="<?php echo $field_name; ?>" id="<?php echo $field_id ?>" <?php if ($onchange) echo 'onchange="'. $onchange .'"'; ?>>
            <?php if ($blank){ ?>
            <option value=""><?php echo ($blank == 1) ? '' : '- '. $blank .' -'; ?></option>
            <?php } ?>
            <?php foreach($forms as $form){ ?>
                <option value="<?php echo $form->id; ?>" <?php selected($field_value, $form->id); ?>><?php echo FrmAppHelper::truncate($form->name, 33); ?></option>
            <?php } ?>
        </select>
        <?php
    }
	
    public static function form_switcher(){
        $where = apply_filters('frm_forms_dropdown', "is_template=0 AND (status is NULL OR status = '' OR status = 'published')", '');
        
        $frm_form = new FrmForm();
        $forms = $frm_form->getAll($where, ' ORDER BY name');
        unset($frm_form);
        
        $args = array('id' => 0, 'form' => 0);
        if(isset($_GET['id']) and !isset($_GET['form']))
            unset($args['form']);
        else if(isset($_GET['form']) and !isset($_GET['id']))
            unset($args['id']);
        
        if(isset($_GET['page']) and $_GET['page'] == 'formidable-entries' and isset($_GET['frm_action']) and in_array($_GET['frm_action'], array('edit', 'show', 'destroy_all'))){
            $args['frm_action'] = 'list';
            $args['form'] = 0;
        }else if(isset($_GET['page']) and $_GET['page'] == 'formidable' and isset($_GET['frm_action']) and $_GET['frm_action'] == 'new'){
            $args['frm_action'] = 'edit';
        }else if(isset($_GET['post'])){
            $args['form'] = 0;
            $base = admin_url('edit.php?post_type=frm_display');
        }

        ?>
		<li class="dropdown last" id="frm_bs_dropdown">
			<a href="#" id="frm-navbarDrop" class="frm-dropdown-toggle" data-toggle="dropdown"><?php _e('Switch Form', 'formidable') ?> <b class="caret"></b></a>
		    <ul class="frm-dropdown-menu" role="menu" aria-labelledby="frm-navbarDrop">
			<?php foreach($forms as $form){
			    if(isset($args['id']))
			        $args['id'] = $form->id;
			    if(isset($args['form']))
			        $args['form'] = $form->id;
                ?>
				<li><a href="<?php echo isset($base) ? add_query_arg($args, $base) : add_query_arg($args); ?>" tabindex="-1"><?php echo empty($form->name) ? __('(no title)') : FrmAppHelper::truncate($form->name, 33); ?></a></li>
			<?php
			        unset($form);
			    } ?>
			</ul>
		</li>
        <?php
    }
    
    public static function get_sortable_classes($col, $sort_col, $sort_dir){
        echo ($sort_col == $col) ? 'sorted' : 'sortable'; 
        echo ($sort_col == $col and $sort_dir == 'desc') ? ' asc' : ' desc';
    }
    
    public static function setup_new_vars($values=array()){
        global $wpdb, $frmdb, $frm_settings;
        
        if(!empty($values)){
            $post_values = $values;
        }else{
            $values = array();
            $post_values = isset($_POST) ? $_POST : array();
        }
        
        foreach (array('name' => '', 'description' => '') as $var => $default){
            if(!isset($values[$var]))
                $values[$var] = FrmAppHelper::get_param($var, $default);
        }
        
        if(apply_filters('frm_use_wpautop', true))
            $values['description'] = wpautop(str_replace( '<br>', '<br />', $values['description']));
        
        foreach (array('form_id' => '', 'logged_in' => '', 'editable' => '', 'default_template' => 0, 'is_template' => 0) as $var => $default){
            if(!isset($values[$var]))
                $values[$var] = FrmAppHelper::get_param($var, $default);
        }
            
        if(!isset($values['form_key']))
            $values['form_key'] = ($post_values and isset($post_values['form_key'])) ? $post_values['form_key'] : FrmAppHelper::get_unique_key('', $wpdb->prefix .'frm_forms', 'form_key');
        
        $values = self::fill_default_opts($values, false, $post_values);
            
        $values['custom_style'] = ($post_values and isset($post_values['options']['custom_style'])) ? $post_values['options']['custom_style'] : ($frm_settings->load_style != 'none');
        $values['before_html'] = FrmFormsHelper::get_default_html('before');
        $values['after_html'] = FrmFormsHelper::get_default_html('after');
        $values['submit_html'] = FrmFormsHelper::get_default_html('submit');
        
        return apply_filters('frm_setup_new_form_vars', $values);
    }
    
    public static function setup_edit_vars($values, $record, $post_values=array()){
        if(empty($post_values))
            $post_values = stripslashes_deep($_POST);

        $values['form_key'] = isset($post_values['form_key']) ? $post_values['form_key'] : $record->form_key;
        $values['default_template'] = isset($post_values['default_template']) ? $post_values['default_template'] : $record->default_template;
        $values['is_template'] = isset($post_values['is_template']) ? $post_values['is_template'] : $record->is_template;
        
        $values = self::fill_default_opts($values, $record, $post_values);

        return apply_filters('frm_setup_edit_form_vars', $values);
    }
    
    public static function fill_default_opts($values, $record, $post_values) {
        
        $defaults = FrmFormsHelper::get_default_opts();
        foreach ($defaults as $var => $default){
            if ( is_array($default) ) {
                if(!isset($values[$var]))
                    $values[$var] = ($record && isset($record->options[$var])) ? $record->options[$var] : array();
                
                foreach($default as $k => $v){
                    $values[$var][$k] = ($post_values && isset($post_values[$var][$k])) ? $post_values[$var][$k] : (($record && isset($record->options[$var]) && isset($record->options[$var][$k])) ? $record->options[$var][$k] : $v);
                    
                    if ( is_array($v) ) {
                        foreach ( $v as $k1 => $v1 ) {
                            $values[$var][$k][$k1] = ($post_values && isset($post_values[$var][$k][$k1])) ? $post_values[$var][$k][$k1] : (($record && isset($record->options[$var]) && isset($record->options[$var][$k]) && isset($record->options[$var][$k][$k1])) ? $record->options[$var][$k][$k1] : $v1);
                            unset($k1);
                            unset($v1);
                        }
                    }
                    
                    unset($k);
                    unset($v);
                }
                
            }else{
                $values[$var] = ($post_values && isset($post_values['options'][$var])) ? $post_values['options'][$var] : (($record && isset($record->options[$var])) ? $record->options[$var] : $default);
            }
            
            unset($var);
            unset($default);
        }
        
        return $values;
    }
    
    public static function get_default_opts(){
        global $frm_settings;
        
        return array(
            'notification' => array(
                array(
                    'email_to' => $frm_settings->email_to, 'reply_to' => '', 'reply_to_name' => '',
                    'cust_reply_to' => '', 'cust_reply_to_name' => '',
                    'email_subject' => '', 'email_message' => '[default-message]', 
                    'inc_user_info' => 0, 'plain_text' => 0,
                )
            ),
            'submit_value' => $frm_settings->submit_value, 'success_action' => 'message',
            'success_msg' => $frm_settings->success_msg, 'show_form' => 0, 'akismet' => '',
            'no_save' => 0, 'ajax_load' => 0
        );
    }
    
    public static function get_default_html($loc){
        if($loc == 'submit'){
            $sending = __('Sending', 'formidable');
            $draft_link = self::get_draft_link();
            $img = '[frmurl]/images/ajax_loader.gif';
            $default_html = <<<SUBMIT_HTML
<div class="frm_submit">
[if back_button]<input type="button" value="[back_label]" name="frm_prev_page" formnovalidate="formnovalidate" class="frm_prev_page" [back_hook] />[/if back_button]
<input type="submit" value="[button_label]" [button_action] />
<img class="frm_ajax_loading" src="$img" alt="$sending" style="visibility:hidden;" />
$draft_link
</div>
SUBMIT_HTML;
        }else if ($loc == 'before'){
            $default_html = <<<BEFORE_HTML
[if form_name]<h3>[form_name]</h3>[/if form_name]
[if form_description]<div class="frm_description">[form_description]</div>[/if form_description]
BEFORE_HTML;
        }else{
            $default_html = '';
        }
        
        return $default_html;
    }
    
    public static function get_draft_link(){
        $link = '[if save_draft]<a class="frm_save_draft" [draft_hook]>[draft_label]</a>[/if save_draft]';
        return $link;
    }
    
    public static function get_custom_submit($html, $form, $submit, $form_action, $values){
        $button = FrmFormsHelper::replace_shortcodes($html, $form, $submit, $form_action, $values);
        if(strpos($button, '[button_action]')){
            $button_parts = explode('[button_action]', $button);
            echo $button_parts[0];
            //echo ' id="frm_submit_"';
            $classes = apply_filters('frm_submit_button_class', array(), $form);
            if(!empty($classes))
                echo ' class="'. implode(' ', $classes) .'"';
            
            do_action('frm_submit_button_action', $form, $form_action);
            echo $button_parts[1];
        }
    }
    
    public static function replace_shortcodes($html, $form, $title=false, $description=false, $values=array()){
        foreach (array('form_name' => $title, 'form_description' => $description, 'entry_key' => true) as $code => $show){
            if ($code == 'form_name'){
                $replace_with = $form->name;
            }else if ($code == 'form_description'){
                if(apply_filters('frm_use_wpautop', true))
                    $replace_with = wpautop(str_replace( '<br>', '<br />', $form->description));
                else
                    $replace_with = $form->description;
            }else if($code == 'entry_key' and isset($_GET) and isset($_GET['entry'])){
                $replace_with = $_GET['entry'];
            }
                
            if ( FrmAppHelper::is_true($show) && $replace_with != '' ) {
                $html = str_replace('[if '.$code.']', '', $html); 
        	    $html = str_replace('[/if '.$code.']', '', $html);
            }else{
                $html = preg_replace('/(\[if\s+'.$code.'\])(.*?)(\[\/if\s+'.$code.'\])/mis', '', $html);
            }
            $html = str_replace('['.$code.']', $replace_with, $html);   
        }
        
        //replace [form_key]
        $html = str_replace('[form_key]', $form->form_key, $html);
        
        //replace [frmurl]
        $html = str_replace('[frmurl]', FrmAppHelper::plugin_url(), $html);
        
        if(strpos($html, '[button_label]')){
            $replace_with = apply_filters('frm_submit_button', $title, $form);
            $html = str_replace('[button_label]', $replace_with, $html);
        }
        
        $html = apply_filters('frm_form_replace_shortcodes', $html, $form, $values);
        
        if(strpos($html, '[if back_button]'))
            $html = preg_replace('/(\[if\s+back_button\])(.*?)(\[\/if\s+back_button\])/mis', '', $html);
            
        if(strpos($html, '[if save_draft]'))
            $html = preg_replace('/(\[if\s+save_draft\])(.*?)(\[\/if\s+save_draft\])/mis', '', $html);
        
        return $html;
    }
    
    public static function form_loaded($form) {
        global $frm_vars;
        $small_form = new stdClass();
        foreach ( array('id', 'form_key', 'name' ) as $var ) {
            $small_form->{$var} = $form->{$var};
            unset($var);
        }
        
        $frm_vars['forms_loaded'][] = $small_form;
    }

}
