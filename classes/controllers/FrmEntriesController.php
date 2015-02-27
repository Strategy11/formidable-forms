<?php
/**
 * @package Formidable
 */
 
if(!defined('ABSPATH')) die('You are not allowed to call this page directly.');

if(class_exists('FrmEntriesController'))
    return;

class FrmEntriesController{
    
    public static function load_hooks(){
        add_action('admin_menu', 'FrmEntriesController::menu', 11);
        add_action('wp', 'FrmEntriesController::process_entry', 10, 0);
        add_action('frm_wp', 'FrmEntriesController::process_entry', 10, 0);
        add_filter('frm_redirect_url', 'FrmEntriesController::delete_entry_before_redirect', 50, 3);
        add_action('frm_after_entry_processed', 'FrmEntriesController::delete_entry_after_save', 100);
        add_filter('frm_email_value', 'FrmEntriesController::filter_email_value', 10, 3);
    }
    
    public static function menu(){
        global $frm_vars;
        if(!$frm_vars['pro_is_installed']){
            add_submenu_page('formidable', 'Formidable |'. __('Entries', 'formidable'), '<span style="opacity:.5;filter:alpha(opacity=50);">'. __('Entries', 'formidable') .'</span>', 'administrator', 'formidable-entries', 'FrmEntriesController::list_entries');
        }
    }
    
    public static function list_entries(){
        global $frm_entry;
        $frm_form = new FrmForm();
        $form_select = $frm_form->getAll("is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name');
        $form_id = FrmAppHelper::get_param('form', false);
        if($form_id)
            $form = $frm_form->getOne($form_id);
        else
            $form = (isset($form_select[0])) ? $form_select[0] : 0;
        
        if($form)
            $entry_count = $frm_entry->getRecordCount($form->id);
            
        include(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/list.php');
    }
    
    public static function show_form($id='', $key='', $title=false, $description=false){
        _deprecated_function( __FUNCTION__, '1.07.05', 'FrmFormsController::show_form()' );
        return FrmFormsController::show_form($id, $key, $title, $description);
    }
    
    public static function get_form($filename, $form, $title, $description) {
        _deprecated_function( __FUNCTION__, '1.07.05', 'FrmFormsController::get_form()' );
        return FrmFormsController::get_form($form, $title, $description);
    }
    
    public static function process_entry($errors='', $ajax=false){
        if((is_admin() and !defined('DOING_AJAX')) or !isset($_POST) or !isset($_POST['form_id']) or !is_numeric($_POST['form_id']) or !isset($_POST['item_key']))
            return;

        global $frm_entry, $frm_vars;
        
        $frm_form = new FrmForm();
        $form = $frm_form->getOne($_POST['form_id']);
        if(!$form)
            return;
        
        $params = FrmEntriesController::get_params($form);
        
        if(!isset($frm_vars['form_params']))
            $frm_vars['form_params'] = array();
        $frm_vars['form_params'][$form->id] = $params;
        
        if(isset($frm_vars['created_entries'][$_POST['form_id']]))
            return;
           
        if($errors == '')
            $errors = $frm_entry->validate($_POST);
        $frm_vars['created_entries'][$_POST['form_id']] = array('errors' => $errors);
        
        if( empty($errors) ){
            $_POST['frm_skip_cookie'] = 1;
            if($params['action'] == 'create'){
                if (apply_filters('frm_continue_to_create', true, $_POST['form_id']) and !isset($frm_vars['created_entries'][$_POST['form_id']]['entry_id']))
                    $frm_vars['created_entries'][$_POST['form_id']]['entry_id'] = $frm_entry->create( $_POST );
            }
            
            do_action('frm_process_entry', $params, $errors, $form, array('ajax' => $ajax));
            unset($_POST['frm_skip_cookie']);
        }
    }
    
    public static function delete_entry_before_redirect($url, $form, $atts){
        self::_delete_entry($atts['id'], $form);
        return $url;
    }
    
    //Delete entry if not redirected
    public static function delete_entry_after_save($atts){
        self::_delete_entry($atts['entry_id'], $atts['form']);
    }
    
    private static function _delete_entry($entry_id, $form){
        if(!$form)
            return;
        
        $form->options = maybe_unserialize($form->options);
        if(isset($form->options['no_save']) and $form->options['no_save']){
            global $frm_entry;
            $frm_entry->destroy( $entry_id );
        }
    }
    
    public static function show_entry_shortcode($atts){
        $atts = shortcode_atts(array(
            'id' => false, 'entry' => false, 'fields' => false, 'plain_text' => false,
            'user_info' => false, 'include_blank' => false, 'default_email' => false,
            'form_id' => false, 'format' => 'text', 'direction' => 'ltr',
            'font_size' => '', 'text_color' => '',
            'border_width' => '', 'border_color' => '',
            'bg_color' => '', 'alt_bg_color' => '', 
        ), $atts);
        extract($atts);
        
        if ( $format != 'text' ) {
            //format options are text, array, or json
            $plain_text = true;
        }
        
        global $frm_entry;
        
        if ( !$entry || !is_object($entry) ) {
            if ( !$id && !$default_email ) {
                return '';
            }
            
            if($id)
                $entry = $frm_entry->getOne($id, true);
        }
        
        if ( $entry ) {
            $form_id = $entry->form_id;
            $id = $entry->id;
        }
        
        if ( !$fields || !is_array($fields) ) {
            global $frm_field;
            $fields = $frm_field->getAll(array('fi.form_id' => $form_id), 'field_order');
        }
        
        $content = ( $format != 'text' ) ? array() : '';
        $odd = true;
        
        if ( !$plain_text ) {
            global $frmpro_settings;
            
            $default_settings = array(
                'border_color' => 'dddddd',
                'bg_color' => 'f7f7f7',
                'text_color' => '444444',
                'font_size' => '12px',
                'border_width' => '1px',
                'alt_bg_color' => 'ffffff',
            );
            
            // merge defaults, global settings, and shortcode options
            foreach ( $default_settings as $key => $setting ) {
                if ( $atts[$key] != '' ) {
                    continue;
                }
                
                if ( $frmpro_settings ) {
                    if ( 'alt_bg_color' == $key ) {
                        $atts[$key] = $frmpro_settings->bg_color_active;
                    } else if ( 'border_width' == $key ) {
                        $atts[$key] = $frmpro_settings->field_border_width;
                    } else {
                        $atts[$key] = $frmpro_settings->{$key};
                    }
                } else {
                    $atts[$key] = $setting;
                }
                unset($key, $setting);
            }
            
            unset($default_settings);
            
            $content .= "<table cellspacing='0' style='font-size:{$atts['font_size']};line-height:135%; border-bottom:{$atts['border_width']} solid #{$atts['border_color']};'><tbody>\r\n";
            $bg_color = " style='background-color:#{$atts['bg_color']};'";
            $bg_color_alt = " style='background-color:#{$atts['alt_bg_color']};'";
            $row_style = "style='text-align:". ( $direction == 'rtl' ? 'right' : 'left' ) .";color:#{$atts['text_color']};padding:7px 9px;border-top:{$atts['border_width']} solid #{$atts['border_color']}'";
        }
        
        foreach ( $fields as $f ) {
            if ( in_array($f->type, array('divider', 'captcha', 'break', 'html')) )
                continue;
            
            if ( $entry && !isset($entry->metas[$f->id]) ) {
                if ( $entry->post_id  && ( $f->type == 'tag' || (isset($f->field_options['post_field']) && $f->field_options['post_field'])) ) {
                    $p_val = FrmProEntryMetaHelper::get_post_value($entry->post_id, $f->field_options['post_field'], $f->field_options['custom_field'], array(
                        'truncate' => (($f->field_options['post_field'] == 'post_category') ? true : false), 
                        'form_id' => $entry->form_id, 'field' => $f, 'type' => $f->type, 
                        'exclude_cat' => (isset($f->field_options['exclude_cat']) ? $f->field_options['exclude_cat'] : 0)
                    ));
                    if ( $p_val != '' ) {
                        $entry->metas[$f->id] = $p_val;
                    }
                }
                
                if ( !isset($entry->metas[$f->id]) && !$include_blank && !$default_email ) {
                    continue;
                }
                
                $entry->metas[$f->id] = $default_email ? '['. $f->id .']' : '';
            }
            
            $val = '';
            if ( $entry ) { 
                $prev_val = maybe_unserialize($entry->metas[$f->id]);
                $meta = array('item_id' => $id, 'field_id' => $f->id, 'meta_value' => $prev_val, 'field_type' => $f->type);

                $val = $default_email ? $prev_val : apply_filters('frm_email_value', $prev_val, (object) $meta, $entry);
            } else if ( $default_email ) {
                $val = '['. $f->id .']';
            }

            if ( $f->type == 'textarea' and !$plain_text ) {
                $val = str_replace(array("\r\n", "\r", "\n"), ' <br/>', $val);
            }
            
            //Remove signature from default-message shortcode
            if ( $f->type == 'signature' && !$default_email ) {
                continue;
            }
            
            if ( is_array($val) && $format == 'text' ) {
                $val = implode(', ', $val);
            }
             
            $fname = $default_email ? '['. $f->id .' show=field_label]' : $f->name;
            
            if ( $format != 'text' ){
                $content[$f->field_key] = $val;
            } else if ( $plain_text ) {
                if ( 'rtl' == $direction ) {
                    $content .= $val . ' :' . $fname . "\r\n\r\n";
                } else {
                    $content .= $fname . ': ' . $val . "\r\n\r\n";
                }
            } else {
             	if (!$default_email){
             	    $content .= '<tr'. ( $odd ? $bg_color : $bg_color_alt ) .'>';
             	    if ( 'rtl' == $direction ) {
             	        $content .= "<td $row_style>$val</td><th $row_style>" . $fname ."</th>";
             	    } else {
             		    $content .= "<th $row_style>" . $fname ."</th><td $row_style>$val</td>";
         		    }
         		    $content .= '</tr>'. "\r\n";
					$odd = ($odd) ? false : true;
             	}else{
             	    $content .= '[if '. $f->id .']<tr style="[frm-alt-color]">';
             	    if ( 'rtl' == $direction ) {
             	        $content .= "<td $row_style>$val</td><th $row_style>" . $fname ."</th>";
             	    } else {
					    $content .= "<th $row_style>" . $fname ."</th><td $row_style>$val</td>";
				    }
				    $content .= "</tr>\r\n[/if $f->id]";
				}
                
            }
            
            unset($fname, $f);
        }
        
        if ( $user_info ) {
            if ( isset($entry->description) ) {
                $data = maybe_unserialize($entry->description);
            } else if ( $default_email ) {
                $entry->ip = '[ip]';
                $data = array(
                    'browser' => '[browser]',
                    'referrer' => '[referrer]',
                );
            }
            if ( $format != 'text' ) {
                $content['ip'] = $entry->ip;
                $content['browser'] = $data['browser'];
                $content['referrer'] = $data['referrer'];
            } else if ( $plain_text ) {
                $content .= "\r\n\r\n" . __('User Information', 'formidable') ."\r\n";
                if ( 'rtl' == $direction ) {
                    $content .=  $entry->ip . ' :'. __('IP Address', 'formidable') ."\r\n";
                    $content .= $data['browser'] .' :'. __('User-Agent (Browser/OS)', 'formidable') ."\r\n";
                    $content .= $data['referrer'] .' :'. __('Referrer', 'formidable') ."\r\n";
                } else {
                    $content .= __('IP Address', 'formidable') . ': '. $entry->ip ."\r\n";
                    $content .= __('User-Agent (Browser/OS)', 'formidable') . ': '. $data['browser']."\r\n";
                    $content .= __('Referrer', 'formidable') . ': '. $data['referrer']."\r\n";
                }
            } else {
                $content .= '<tr'. ($odd ? $bg_color : $bg_color_alt) .'>';
                if ( 'rtl' == $direction ) {
                    $content .= "<td $row_style>". $entry->ip ."</td><th $row_style>". __('IP Address', 'formidable') . "</th>";
                } else {
                    $content .= "<th $row_style>". __('IP Address', 'formidable') . "</th><td $row_style>". $entry->ip ."</td>";
                }
                $content .= '</tr>'. "\r\n";
                $odd = ($odd) ? false : true;
                
                if ( isset($data['browser']) ) {
                    $content .= '<tr'. ($odd ? $bg_color : $bg_color_alt) .'>';
                    if ( 'rtl' == $direction ) {
                        $content .= "<td $row_style>". $data['browser']."</td><th $row_style>". __('User-Agent (Browser/OS)', 'formidable') . "</th>";
                    } else {
                        $content .= "<th $row_style>". __('User-Agent (Browser/OS)', 'formidable') . "</th><td $row_style>". $data['browser']."</td>";
                    }
                    $content .= '</tr>'. "\r\n";
                }
                $odd = ($odd) ? false : true;
                
                if ( isset($data['referrer']) ) {
                    $content .= '<tr'. ($odd ? $bg_color : $bg_color_alt) .'>';
                    if ( 'rtl' == $direction ) {
                        $content .= "<td $row_style>". str_replace("\r\n", '<br/>', $data['referrer']) ."</td><th $row_style>".__('Referrer', 'formidable') . "</th>";
                    } else {
                        $content .= "<th $row_style>".__('Referrer', 'formidable') . "</th><td $row_style>". str_replace("\r\n", '<br/>', $data['referrer']) ."</td>";
                    }
                    $content .= '</tr>'. "\r\n";
                }
            }
        }

        if ( ! $plain_text ) {
            $content .= '</tbody></table>';
        }
        
        if ( $format == 'json' ) {
            $content = json_encode($content);
        }
        
        return $content;
    }
    
    public static function &filter_email_value($value, $meta, $entry, $atts=array()){
        $frm_field = new FrmField();
        $field = $frm_field->getOne($meta->field_id);
        if(!$field)
            return $value; 
            
        $value = self::filter_display_value($value, $field, $atts);
        return $value;
    }
    
    public static function &filter_display_value($value, $field, $atts=array()){
        $field->field_options = maybe_unserialize($field->field_options);
        
        $saved_value = (isset($atts['saved_value']) and $atts['saved_value']) ? true : false;
        if(!in_array($field->type, array('radio', 'checkbox', 'radio', 'select')) or !isset($field->field_options['separate_value']) or !$field->field_options['separate_value'] or $saved_value)
            return $value;
            
        $field->options = maybe_unserialize($field->options);
        $f_values = array();
        $f_labels = array();
        foreach($field->options as $opt_key => $opt){
            if(!is_array($opt))
                continue;
            
            $f_labels[$opt_key] = isset($opt['label']) ? $opt['label'] : reset($opt);
            $f_values[$opt_key] = isset($opt['value']) ? $opt['value'] : $f_labels[$opt_key];
            if($f_labels[$opt_key] == $f_values[$opt_key]){
                unset($f_values[$opt_key]);
                unset($f_labels[$opt_key]);
            }
            unset($opt_key);
            unset($opt);
        }

        if(!empty($f_values)){
            foreach((array)$value as $v_key => $val){
                if(in_array($val, $f_values)){
                    $opt = array_search($val, $f_values);
                    if(is_array($value))
                        $value[$v_key] = $f_labels[$opt];
                    else
                        $value = $f_labels[$opt];
                }
                unset($v_key);
                unset($val);
            }
        }
        
        return $value;
    }
    
    public static function get_params($form=null){
        global $frm_vars;
        
        $frm_form = new FrmForm();
        if(!$form)
            $form = $frm_form->getAll(array(), 'name', 1);
        else if(!is_object($form))
            $form = $frm_form->getOne($form);
        
        if(isset($frm_vars['form_params']) && is_array($frm_vars['form_params']) && isset($frm_vars['form_params'][$form->id]))
            return $frm_vars['form_params'][$form->id];
           
        $action_var = isset($_REQUEST['frm_action']) ? 'frm_action' : 'action';
        $action = apply_filters('frm_show_new_entry_page', FrmAppHelper::get_param($action_var, 'new'), $form);
        
        $default_values = array(
            'id' => '', 'form_name' => '', 'paged' => 1, 'form' => $form->id, 'form_id' => $form->id, 
            'field_id' => '', 'search' => '', 'sort' => '', 'sdir' => '', 'action' => $action
        );
            
        $values['posted_form_id'] = FrmAppHelper::get_param('form_id');
        if (!is_numeric($values['posted_form_id']))
            $values['posted_form_id'] = FrmAppHelper::get_param('form');

        if ($form->id == $values['posted_form_id']){ //if there are two forms on the same page, make sure not to submit both
            foreach ($default_values as $var => $default){
                if($var == 'action')
                    $values[$var] = FrmAppHelper::get_param($action_var, $default);
                else
                    $values[$var] = FrmAppHelper::get_param($var, $default);
                unset($var);
                unset($default);
            }
        }else{
            foreach ($default_values as $var => $default){
                $values[$var] = $default;
                unset($var);
                unset($default);
            }
        }

        if(in_array($values['action'], array('create', 'update')) and (!isset($_POST) or (!isset($_POST['action']) and !isset($_POST['frm_action']))))
            $values['action'] = 'new';

        return $values;
    }
    
}
