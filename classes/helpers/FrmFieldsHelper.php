<?php

if(!defined('ABSPATH')) die('You are not allowed to call this page directly.');

if(class_exists('FrmFieldsHelper'))
    return;

class FrmFieldsHelper{
    
    public static function field_selection(){
        $fields = apply_filters('frm_available_fields', array(
            'text' => __('Single Line Text', 'formidable'),
            'textarea' => __('Paragraph Text', 'formidable'),
            'checkbox' => __('Checkboxes', 'formidable'),
            'radio' => __('Radio Buttons', 'formidable'),
            'select' => __('Dropdown', 'formidable'),
            'captcha' => __('reCAPTCHA', 'formidable')
        ));
        
        return $fields;
    }
    
    public static function pro_field_selection(){
        return apply_filters('frm_pro_available_fields', array(
            'email' => __('Email Address', 'formidable'),
            'url' => __('Website/URL', 'formidable'),
            'divider' => __('Section Heading', 'formidable'),
            'break' => __('Page Break', 'formidable'),
            'file' => __('File Upload', 'formidable'),
            'rte' => __('Rich Text', 'formidable'), 
            'number' => __('Number', 'formidable'), 
            'phone' => __('Phone Number', 'formidable'), 
            'date' => __('Date', 'formidable'), 
            'time' => __('Time', 'formidable'),
            'image' => __('Image URL', 'formidable'), 
            'scale' => __('Scale', 'formidable'),
            //'grid' => __('Grid', 'formidable'),
            'data' => __('Data from Entries', 'formidable'),
            //'form' => __('SubForm', 'formidable'),
            'hidden' => __('Hidden Field', 'formidable'), 
            'user_id' => __('User ID (hidden)', 'formidable'),
            'password'  => __('Password', 'formidable'),
            'html' => __('HTML', 'formidable'),
            'tag' => __('Tags', 'formidable')
            //'address' => 'Address' //Address line 1, Address line 2, City, State/Providence, Postal Code, Select Country 
            //'city_selector' => 'US State/County/City selector', 
            //'full_name' => 'First and Last Name', 
            //'quiz' => 'Question and Answer' // for captcha alternative
        ));
    }
    
    public static function setup_new_vars($type='', $form_id=''){
        global $frm_settings;
        
        $defaults = FrmFieldsHelper::get_default_field_opts($type, $form_id);
        $defaults['field_options']['custom_html'] = FrmFieldsHelper::get_default_html($type);

        $values = array();
        
        foreach ($defaults as $var => $default){
            if($var == 'field_options'){
                $values['field_options'] = array();
                foreach ($default as $opt_var => $opt_default){
                    $values['field_options'][$opt_var] = $opt_default;
                    unset($opt_var);
                    unset($opt_default);
                }
            }else{
                $values[$var] = $default;
            }
            unset($var);
            unset($default);
        }
            
        if ($type == 'radio' || ($type == 'checkbox'))
            $values['options'] = serialize(array(__('Option 1', 'formidable'), __('Option 2', 'formidable')));
        else if ( $type == 'select')
            $values['options'] = serialize(array('', __('Option 1', 'formidable')));
        else if ($type == 'textarea')
            $values['field_options']['max'] = '5';
        else if ($type == 'captcha')
            $values['invalid'] = $frm_settings->re_msg;
            
        $fields = self::field_selection();
        $fields = array_merge($fields, self::pro_field_selection());

        if(isset($fields[$type]))
            $values['name'] = $fields[$type];

        unset($fields);
        
        return $values;
    }
    
    public static function setup_edit_vars($record, $doing_ajax=false){
        global $frm_entry_meta;
        
        $values = array('id' => $record->id, 'form_id' => $record->form_id);
        $defaults = array('name' => $record->name, 'description' => $record->description);
        $default_opts = array(
            'field_key' => $record->field_key, 'type' => $record->type, 
            'default_value'=> $record->default_value, 'field_order' => $record->field_order, 
            'required' => $record->required
        );
        
        if($doing_ajax){
            $values = $values + $defaults + $default_opts;
            $values['form_name'] = '';
        }else{
            foreach ($defaults as $var => $default){
                $values[$var] = htmlspecialchars(FrmAppHelper::get_param($var, $default));
                unset($var);
                unset($default);
            }
            
            foreach (array('field_key' => $record->field_key, 'type' => $record->type, 'default_value'=> $record->default_value, 'field_order' => $record->field_order, 'required' => $record->required) as $var => $default){
                $values[$var] = FrmAppHelper::get_param($var, $default);
                unset($var);
                unset($default);
            }
            
            $frm_form = new FrmForm();
            $values['form_name'] = ($record->form_id) ? $frm_form->getName( $record->form_id ) : '';
            unset($frm_form);
        }
        
        unset($defaults);
        unset($default_opts);
             
        $values['options'] = $record->options;
        $values['field_options'] = $record->field_options;
        
        $defaults = self::get_default_field_opts($values['type'], $record, true);
        
        if($values['type'] == 'captcha'){
            global $frm_settings;
            $defaults['invalid'] = $frm_settings->re_msg;
        }
            
        foreach($defaults as $opt => $default){
            $values[$opt] = (isset($record->field_options[$opt])) ? $record->field_options[$opt] : $default;
            unset($opt);
            unset($default);
        }

        $values['custom_html'] = (isset($record->field_options['custom_html'])) ? $record->field_options['custom_html'] : self::get_default_html($record->type);
        
        return apply_filters('frm_setup_edit_field_vars', $values, array('doing_ajax' => $doing_ajax));
    }
    
    public static function get_default_field_opts($type, $field, $limit=false){
        $field_options = array(
            'size' => '', 'max' => '', 'label' => '', 'blank' => '', 
            'required_indicator' => '*', 'invalid' => '', 'separate_value' => 0,
            'clear_on_focus' => 0, 'default_blank' => 0, 'classes' => '',
            'custom_html' => ''
        );
        
        if($limit)
            return $field_options;
        
        global $wpdb, $frm_settings;
        
        $form_id = (is_numeric($field)) ? $field : $field->form_id;
        
        $key = is_numeric($field) ? FrmAppHelper::get_unique_key('', $wpdb->prefix .'frm_fields', 'field_key') : $field->field_key;
        $field_count = FrmAppHelper::getRecordCount(array('form_id' => $form_id), $wpdb->prefix .'frm_fields');
        
        return array(
            'name' => __('Untitled', 'formidable'), 'description' => '', 
            'field_key' => $key, 'type' => $type, 'options'=>'', 'default_value'=>'', 
            'field_order' => $field_count+1, 'required' => false, 
            'blank' => $frm_settings->blank_msg, 'unique_msg' => $frm_settings->unique_msg,
            'invalid' => __('This field is invalid', 'formidable'), 'form_id' => $form_id,
            'field_options' => $field_options
        );
    }
    
    public static function get_form_fields($form_id, $error=false){
        global $frm_field;
        $fields = $frm_field->getAll(array('fi.form_id' => $form_id), 'field_order');
        $fields = apply_filters('frm_get_paged_fields', $fields, $form_id, $error); 
        return $fields;
    }
    
    public static function get_default_html($type='text'){
        if (apply_filters('frm_normal_field_type_html', true, $type)){
            $input = (in_array($type, array('radio', 'checkbox', 'data'))) ? '<div class="frm_opt_container">[input]</div>' : '[input]';
            $for = '';
            if(!in_array($type, array('radio', 'checkbox', 'data', 'scale')))
                $for = 'for="field_[key]"';
            
            $default_html = <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="frm_form_field form-field [required_class][error_class]">
    <label $for class="frm_primary_label">[field_name]
        <span class="frm_required">[required_label]</span>
    </label>
    $input
    [if description]<div class="frm_description">[description]</div>[/if description]
    [if error]<div class="frm_error">[error]</div>[/if error]
</div>
DEFAULT_HTML;
        }else
            $default_html = apply_filters('frm_other_custom_html', '', $type);

        return apply_filters('frm_custom_html', $default_html, $type);
    }
    
    public static function replace_shortcodes($html, $field, $errors=array(), $form=false){
        $html = apply_filters('frm_before_replace_shortcodes', $html, $field, $errors, $form);
        
        $field_name = 'item_meta['. $field['id'] .']';
        if(isset($field['multiple']) and $field['multiple'] and ($field['type'] == 'select' or ($field['type'] == 'data' and isset($field['data_type']) and $field['data_type'] == 'select')))
            $field_name .= '[]';
        
        //replace [id]
        $html = str_replace('[id]', $field['id'], $html);
        
        //replace [key]        
        $html = str_replace('[key]', $field['field_key'], $html);
        
        //replace [description] and [required_label] and [error]
        $required = ($field['required'] == '0') ? '' : $field['required_indicator'];
        if(!is_array($errors))
            $errors = array();
        $error = isset($errors['field'. $field['id']]) ? $errors['field'. $field['id']] : false; 
        foreach (array('description' => $field['description'], 'required_label' => $required, 'error' => $error) as $code => $value){
            if (!$value or $value == '')
                $html = preg_replace('/(\[if\s+'.$code.'\])(.*?)(\[\/if\s+'.$code.'\])/mis', '', $html);
            else{
                $html = str_replace('[if '.$code.']', '', $html); 
        	    $html = str_replace('[/if '.$code.']', '', $html);
            }

            $html = str_replace('['.$code.']', $value, $html);
        }        
        
        //replace [required_class]
        $required_class = ($field['required'] == '0') ? '' : ' frm_required_field';            
        $html = str_replace('[required_class]', $required_class, $html);  
        
        //replace [label_position]
        $field['label'] = apply_filters('frm_html_label_position', $field['label'], $field);
        $field['label'] = ($field['label'] and $field['label'] != '') ? $field['label'] : 'top';
        $html = str_replace('[label_position]', (($field['type'] == 'divider' or $field['type'] == 'break') ? $field['label'] : ' frm_primary_label'), $html);
        
        //replace [field_name]
        $html = str_replace('[field_name]', $field['name'], $html);
            
        //replace [error_class] 
        $error_class = isset($errors['field'. $field['id']]) ? ' frm_blank_field' : '';
        $error_class .= ' frm_'. $field['label'] .'_container' ;
        //insert custom CSS classes
        if(!empty($field['classes'])){
            if(!strpos($html, 'frm_form_field '))
                $error_class .= ' frm_form_field';
            $error_class .= ' '. $field['classes'];
        }
        $html = str_replace('[error_class]', $error_class, $html);
        
        //replace [entry_key]
        $entry_key = (isset($_GET) and isset($_GET['entry'])) ? $_GET['entry'] : '';
        $html = str_replace('[entry_key]', $entry_key, $html);
        
        //replace [input]
        preg_match_all("/\[(input|deletelink)\b(.*?)(?:(\/))?\]/s", $html, $shortcodes, PREG_PATTERN_ORDER);
        global $frm_vars;

        foreach ($shortcodes[0] as $short_key => $tag){
            $atts = shortcode_parse_atts( $shortcodes[2][$short_key] );

            if(!empty($shortcodes[2][$short_key])){
                $tag = str_replace('[', '',$shortcodes[0][$short_key]);
                $tag = str_replace(']', '', $tag);
                $tags = explode(' ', $tag);
                if(is_array($tags))
                    $tag = $tags[0];
            }else
                $tag = $shortcodes[1][$short_key];
               
            $replace_with = ''; 
            
            if($tag == 'input'){
                if(isset($atts['opt'])) $atts['opt']--;
                $field['input_class'] = isset($atts['class']) ? $atts['class'] : '';
                if(isset($atts['class']))
                    unset($atts['class']);
                $field['shortcodes'] = $atts;
                ob_start();
                include(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/input.php');
                $replace_with = ob_get_contents();
                ob_end_clean();
            }else if($tag == 'deletelink' and class_exists('FrmProEntriesController'))
                $replace_with = FrmProEntriesController::entry_delete_link($atts);
            
            $html = str_replace($shortcodes[0][$short_key], $replace_with, $html);
        }
        
        if($form){
            $form = (array)$form;
            
            //replace [form_key]
            $html = str_replace('[form_key]', $form['form_key'], $html);
            
            //replace [form_name]
            $html = str_replace('[form_name]', $form['name'], $html);
        }
        $html .= "\n";
        
        $html = apply_filters('frm_replace_shortcodes', $html, $field, array('errors' => $errors, 'form' => $form));
        
        // remove [collapse_this] when running the free version
        if (preg_match('/\[(collapse_this)\]/s', $html))
            $html = str_replace('[collapse_this]', '', $html);
        
        return $html;
    }
    
    public static function display_recaptcha($field, $error=null){
    	global $frm_settings, $frm_vars;
    	
    	if(!function_exists('recaptcha_get_html'))
            require(FrmAppHelper::plugin_path().'/classes/recaptchalib.php');
        
        $lang = apply_filters('frm_recaptcha_lang', $frm_settings->re_lang, $field);
        
        if(defined('DOING_AJAX') and (!isset($frm_vars['preview']) or !$frm_vars['preview'])){
            if(!isset($frm_vars['recaptcha_loaded']) or !$frm_vars['recaptcha_loaded'])
                $frm_vars['recaptcha_loaded'] = '';
            
            $frm_vars['recaptcha_loaded'] .= "Recaptcha.create('". $frm_settings->pubkey ."','field_". $field['field_key'] ."',{theme:'". $frm_settings->re_theme ."',lang:'". $lang ."'". apply_filters('frm_recaptcha_custom', '', $field) ."});";
?>
<div id="field_<?php echo $field['field_key'] ?>"></div>
<?php   }else{ ?>
<script type="text/javascript">var RecaptchaOptions={theme:'<?php echo $frm_settings->re_theme ?>',lang:'<?php echo $lang ?>'<?php echo apply_filters('frm_recaptcha_custom', '', $field) ?>};</script>
<?php       echo recaptcha_get_html($frm_settings->pubkey .'&hl='. $lang, $error, is_ssl());
        }
    }
    
    public static function dropdown_categories($args){
        global $frm_vars;
        
        $defaults = array('field' => false, 'name' => false, 'show_option_all' => ' ');
        extract(wp_parse_args($args, $defaults));
        
        if(!$field) return;
        if(!$name) $name = "item_meta[$field[id]]";
        $id = 'field_'. $field['field_key'];
        $class = $field['type'];

        $exclude = (is_array($field['exclude_cat'])) ? implode(',', $field['exclude_cat']) : $field['exclude_cat'];
        $exclude = apply_filters('frm_exclude_cats', $exclude, $field);
        
        if(is_array($field['value'])){
            if(!empty($exclude))
                $field['value'] = array_diff($field['value'], explode(',', $exclude));
            $selected = reset($field['value']);
        }else{
            $selected = $field['value'];
        }
        
        $args = array(
            'show_option_all' => $show_option_all, 'hierarchical' => 1, 'name' => $name,
            'id' => $id, 'exclude' => $exclude, 'class' => $class, 'selected' => $selected, 
            'hide_empty' => false, 'echo' => 0, 'orderby' => 'name',
        );
        
        $args = apply_filters('frm_dropdown_cat', $args, $field);
        
        if ( class_exists('FrmProFormsHelper') ) {
            $post_type = FrmProFormsHelper::post_type($field['form_id']);
            $args['taxonomy'] = FrmProAppHelper::get_custom_taxonomy($post_type, $field);
            if ( ! $args['taxonomy'] ) {
                return;
            }
            
            if ( is_taxonomy_hierarchical($args['taxonomy']) ) {
                $args['exclude_tree'] = $exclude;
            }
        }
        
        $dropdown = wp_dropdown_categories($args);

        $add_html = FrmFieldsController::input_html($field, false);
        
        if($frm_vars['pro_is_installed'])
            $add_html .= FrmProFieldsController::input_html($field, false);
        
        $dropdown = str_replace("<select name='$name' id='$id' class='$class'", "<select name='$name' id='$id' ". $add_html, $dropdown);
        
        if(is_array($field['value'])){
            $skip = true;
            foreach($field['value'] as $v){
                if($skip){
                    $skip = false;
                    continue;
                }
                $dropdown = str_replace(' value="'. $v. '"', ' value="'. $v .'" selected="selected"', $dropdown);
                unset($v);
            }
        }
        
        return $dropdown;
    }
    
    public static function get_term_link($tax_id) {
        $tax = get_taxonomy($tax_id);
        if ( !$tax ) {
            return;
        }
        
        $link = sprintf(
            __('Please add options from the WordPress "%1$s" page', 'formidable'),
            '<a href="'. esc_url(admin_url('edit-tags.php?taxonomy='. $tax->name)) .'" target="_blank">'. ( empty($tax->labels->name) ? __('Categories') : $tax->labels->name ) .'</a>'
        );
        unset($tax);
        
        return $link;
    }
    
    public static function get_field_types($type){
        $frm_field_selection = FrmFieldsHelper::field_selection();    
        $field_types = array();
        $single_input = array(
            'text', 'textarea', 'rte', 'number', 'email', 'url', 
            'image', 'file', 'date', 'phone', 'hidden', 'time', 
            'user_id', 'tag', 'password'
        );
        $multiple_input = array('radio', 'checkbox', 'select', 'scale');
        $other_type = array('divider', 'html', 'break');
        $frm_pro_field_selection = FrmFieldsHelper::pro_field_selection();
        
        if (in_array($type, $single_input)){
            foreach($single_input as $input){
                if (isset($frm_pro_field_selection[$input]))
                    $field_types[$input] = $frm_pro_field_selection[$input];
                else
                    $field_types[$input] = $frm_field_selection[$input];
            }
        }else if (in_array($type, $multiple_input)){
            foreach($multiple_input as $input){
                if (isset($frm_pro_field_selection[$input]))
                    $field_types[$input] = $frm_pro_field_selection[$input];
                else
                    $field_types[$input] = $frm_field_selection[$input];
            }
        }else if (in_array($type, $other_type)){
            foreach($other_type as $input){
                if (isset($frm_pro_field_selection[$input]))
                    $field_types[$input] = $frm_pro_field_selection[$input];
                else
                    $field_types[$input] = $frm_field_selection[$input];
            }
        }
        
        return $field_types;
    }
    
    public static function show_onfocus_js($field_id, $clear_on_focus){ ?>
    <a class="frm_bstooltip <?php echo ($clear_on_focus) ? '' : 'frm_inactive_icon '; ?>frm_default_val_icons frm_action_icon frm_reload_icon frm_icon_font" id="clear_field_<?php echo $field_id; ?>" title="<?php echo esc_attr($clear_on_focus ? __('Clear default value when typing', 'formidable') : __('Do not clear default value when typing', 'formidable')); ?>"></a>
    <?php
    }
    
    public static function show_default_blank_js($field_id, $default_blank){ ?>
    <a class="frm_bstooltip <?php echo ($default_blank) ? '' :'frm_inactive_icon '; ?>frm_default_val_icons frm_action_icon frm_error_icon frm_icon_font" id="default_blank_<?php echo $field_id; ?>" title="<?php echo $default_blank ? __('Default value will NOT pass form validation', 'formidable') : __('Default value will pass form validation', 'formidable'); ?>"></a>
    <?php
    }
    
    public static function switch_field_ids($val){
        global $frm_duplicate_ids;
        $replace = array();
        $replace_with = array();
        foreach((array)$frm_duplicate_ids as $old => $new){
            $replace[] = '[if '. $old .']';
            $replace_with[] = '[if '. $new .']';
            $replace[] = '[if '. $old .' ';
            $replace_with[] = '[if '. $new .' ';
            $replace[] = '[/if '. $old .']';
            $replace_with[] = '[/if '. $new .']';
            $replace[] = '['. $old .']';
            $replace_with[] = '['. $new .']';
            $replace[] = '['. $old .' ';
            $replace_with[] = '['. $new .' ';
            unset($old);
            unset($new);
        }
        if(is_array($val)){
            foreach($val as $k => $v){
                $val[$k] = str_replace($replace, $replace_with, $v);
                unset($k);
                unset($v);
            }
        }else{
            $val = str_replace($replace, $replace_with, $val);
        }
        
        return $val;
    }
}
