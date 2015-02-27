<?php
if(!defined('ABSPATH')) die('You are not allowed to call this page directly.');

if(class_exists('FrmAppHelper'))
    return;

class FrmAppHelper{
    public static $db_version = 11; //version of the database we are moving to (skip 12)
    public static $pro_db_version = 25;
    
    /*
    * @since 1.07.02
    *
    * @param none
    * @return float The version of this plugin
    */
    public static function plugin_version(){
        $plugin_data = get_file_data( WP_PLUGIN_DIR .'/formidable/formidable.php', array('Version' => 'Version'), 'plugin' );
        return $plugin_data['Version'];
    }
    
    public static function plugin_path(){
        return dirname(dirname(dirname(__FILE__)));
    }
    
    public static function plugin_url($url=''){
        //prevously FRM_URL constant
        if(empty($url))
            $url = plugins_url('', 'formidable/formidable.php');
        if(is_ssl() and !preg_match('/^https:\/\/.*\..*$/', $url))
            $url = str_replace('http://', 'https://', $url);
        
        return $url;
    }
    
    public static function site_url(){
        $url = self::plugin_url(site_url());
        return $url;
    }
    
    public static function get_param($param, $default='', $src='get'){
        if(strpos($param, '[')){
            $params = explode('[', $param);
            $param = $params[0];    
        }

        if($src == 'get'){
            $value = (isset($_POST[$param]) ? stripslashes_deep($_POST[$param]) : (isset($_GET[$param]) ? stripslashes_deep($_GET[$param]) : $default));
            if(!isset($_POST[$param]) and isset($_GET[$param]) and !is_array($value))
                $value = stripslashes_deep(htmlspecialchars_decode(urldecode($_GET[$param])));
        }else{
            $value = isset($_POST[$param]) ? stripslashes_deep(maybe_unserialize($_POST[$param])) : $default;
        }
        
        if(isset($params) and is_array($value) and !empty($value)){
            foreach($params as $k => $p){
                if(!$k or !is_array($value))
                    continue;
                    
                $p = trim($p, ']');
                $value = (isset($value[$p])) ? $value[$p] : $default;
            }
        }

        return $value;
    }
    
    public static function get_post_param($param, $default=''){
        return isset($_POST[$param]) ? stripslashes_deep(maybe_unserialize($_POST[$param])) : $default;
    }
    
    /*
    * Check a value from a shortcode to see if true or false.
    * True when value is 1, true, 'true', 'yes'
    *
    * @since 1.07.10
    *
    * @param string $value The value to compare
    * @return boolean True or False
    */
    public static function is_true($value) {
        return ( true === $value || 1 == $value || 'true' == $value || 'yes' == $value );
    }
    
    public static function load_scripts($scripts){
        foreach ( (array) $scripts as $s ) {
            wp_enqueue_script($s);
        }
    }
    
    public static function load_styles($styles){
        foreach ( (array) $styles as $s ) {
            wp_enqueue_style($s);
        }
    }
    
    public static function get_pages(){
      return get_posts( array('post_type' => 'page', 'post_status' => array('publish', 'private'), 'numberposts' => 999, 'orderby' => 'title', 'order' => 'ASC'));
    }
  
    public static function wp_pages_dropdown($field_name, $page_id, $truncate=false){
        $pages = FrmAppHelper::get_pages();
    ?>
        <select name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" class="frm-pages-dropdown">
            <option value=""></option>
            <?php foreach($pages as $page){ ?>
                <option value="<?php echo $page->ID; ?>" <?php echo (((isset($_POST[$field_name]) and $_POST[$field_name] == $page->ID) or (!isset($_POST[$field_name]) and $page_id == $page->ID))?' selected="selected"':''); ?>><?php echo ($truncate)? FrmAppHelper::truncate($page->post_title, $truncate) : $page->post_title; ?> </option>
            <?php } ?>
        </select>
    <?php
    }
    
    public static function wp_roles_dropdown($field_name, $capability){
        global $frm_vars;
        $field_value = FrmAppHelper::get_param($field_name);
        if(!isset($frm_vars['editable_roles']) or !$frm_vars['editable_roles'])
    	    $frm_vars['editable_roles'] = get_editable_roles();

    ?>
        <select name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" class="frm-pages-dropdown">
            <?php foreach($frm_vars['editable_roles'] as $role => $details){ 
                $name = translate_user_role($details['name'] ); ?>
                <option value="<?php echo esc_attr($role) ?>" <?php echo (((isset($_POST[$field_name]) and $_POST[$field_name] == $role) or (!isset($_POST[$field_name]) and $capability == $role))?' selected="selected"':''); ?>><?php echo $name ?> </option>
            <?php 
                    unset($role);
                    unset($details);
                } ?>
        </select>
    <?php
    }
    
    public static function frm_capabilities($type = 'auto'){
        global $frm_vars;
        $cap = array(
            'frm_view_forms'        => __('View Forms and Templates', 'formidable'),
            'frm_edit_forms'        => __('Add/Edit Forms and Templates', 'formidable'),
            'frm_delete_forms'      => __('Delete Forms and Templates', 'formidable'),
            'frm_change_settings'   => __('Access this Settings Page', 'formidable')
        );
        if ( $frm_vars['pro_is_installed'] || 'pro' == $type ) {
            $cap['frm_view_entries'] = __('View Entries from Admin Area', 'formidable');
            $cap['frm_create_entries'] = __('Add Entries from Admin Area', 'formidable');
            $cap['frm_edit_entries'] = __('Edit Entries from Admin Area', 'formidable');
            $cap['frm_delete_entries'] = __('Delete Entries from Admin Area', 'formidable');
            $cap['frm_view_reports'] = __('View Reports', 'formidable');
            $cap['frm_edit_displays'] = __('Add/Edit Views', 'formidable');
        }
        return $cap;
    }
    
    public static function user_has_permission($needed_role){
        if($needed_role == '-1')
            return false;
            
        if($needed_role == '' or current_user_can($needed_role))
            return true;
           
        $roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );
        foreach ($roles as $role){
        	if (current_user_can($role))
        		return true;
        	if ($role == $needed_role)
        		break;
        }
        return false;
    }
    
    public static function checked($values, $current){
        if(FrmAppHelper::check_selected($values, $current))
            echo ' checked="checked"';
    }
    
    public static function check_selected($values, $current){
        //if(is_array($current))
        //    $current = (isset($current['value'])) ? $current['value'] : $current['label'];
        
        if(is_array($values))
            $values = array_map(array('FrmAppHelper', 'recursive_trim'), $values);
        else
            $values = trim($values);
        $current = trim($current);
            
        /*if(is_array($values))
            $values = array_map('htmlentities', $values);
        else
             $values = htmlentities($values);
        
        $values = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $values);
        $current = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $current);
        */

        if((is_array($values) && in_array($current, $values)) or (!is_array($values) and $values == $current))
            return true;
        else
            return false;
    }
    
    public static function recursive_trim(&$value) {
        if (is_array($value))
            $value = array_map(array('FrmAppHelper', 'recursive_trim'), $value);
        else
            $value = trim($value);
            
        return $value;
    }
    
    public static function esc_textarea( $text ) {
        $safe_text = str_replace('&quot;', '"', $text);
        $safe_text = htmlspecialchars( $safe_text, ENT_NOQUOTES );
    	return apply_filters( 'esc_textarea', $safe_text, $text );
    }
    
    public static function replace_quotes($val){
        $val = str_replace(array('&#8220;', '&#8221;', '&#8216;', '&#8217;'), array('"', '"', "'", "'"), $val);
        return $val;
    }
    
    public static function script_version($handle, $list='scripts'){
        global $wp_scripts;
    	if(!$wp_scripts)
    	    return false;
        
        $ver = 0;
        
        if ( isset($wp_scripts->registered[$handle]) )
            $query = $wp_scripts->registered[$handle];
            
    	if ( is_object( $query ) )
    	    $ver = $query->ver;

    	return $ver;
    }
    
    public static function js_redirect($url){
        return '<script type="text/javascript">window.location="'. $url .'"</script>';
    }
    
    public static function get_file_contents($filename){
        if (is_file($filename)){
            ob_start();
            include $filename;
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        }
        return false;
    }
    
    public static function get_unique_key($name='', $table_name, $column, $id = 0, $num_chars = 6){
        global $wpdb;

        $key = '';
        
        if (!empty($name)){
            $key = sanitize_key($name);
        }
        
        if(empty($key)){
            $max_slug_value = pow(36, $num_chars);
            $min_slug_value = 37; // we want to have at least 2 characters in the slug
            $key = base_convert( rand($min_slug_value, $max_slug_value), 10, 36 );
        }

        if (is_numeric($key) or in_array($key, array('id', 'key', 'created-at', 'detaillink', 'editlink', 'siteurl', 'evenodd')))
            $key = $key .'a';
            
        $query = "SELECT $column FROM $table_name WHERE $column = %s AND ID != %d LIMIT 1";
        $key_check = $wpdb->get_var($wpdb->prepare($query, $key, $id));
        
        if ($key_check or is_numeric($key_check)){
            $suffix = 2;
			do {
				$alt_post_name = substr($key, 0, 200-(strlen($suffix)+1)). "$suffix";
				$key_check = $wpdb->get_var($wpdb->prepare($query, $alt_post_name, $id));
				$suffix++;
			} while ($key_check || is_numeric($key_check));
			$key = $alt_post_name;
        }
        return $key;
    }

    //Editing a Form or Entry
    public static function setup_edit_vars($record, $table, $fields='', $default=false, $post_values=array()){
        if(!$record) return false;
        global $frm_entry_meta, $frm_settings, $frm_vars;
        
        if(empty($post_values))
            $post_values = stripslashes_deep($_POST);
        
        $values = array('id' => $record->id, 'fields' => array());

        foreach ( array('name', 'description') as $var ) {
            $default_val = isset($record->{$var}) ? $record->{$var} : '';
            $values[$var] = FrmAppHelper::get_param($var, $default_val);
            unset($var, $default_val);
        }
              
        if ( apply_filters('frm_use_wpautop', true) ) {
            $values['description'] = wpautop(str_replace( '<br>', '<br />', $values['description']));
        }
        
        foreach ( (array) $fields as $field ) {

                if ($default){
                    $meta_value = $field->default_value;
                }else{
                    if($record->post_id and class_exists('FrmProEntryMetaHelper') and isset($field->field_options['post_field']) and $field->field_options['post_field']){
                        if(!isset($field->field_options['custom_field']))
                            $field->field_options['custom_field'] = '';
                        $meta_value = FrmProEntryMetaHelper::get_post_value($record->post_id, $field->field_options['post_field'], $field->field_options['custom_field'], array('truncate' => false, 'type' => $field->type, 'form_id' => $field->form_id, 'field' => $field));
                    }else if(isset($record->metas)){
                        $meta_value = isset($record->metas[$field->id]) ? $record->metas[$field->id] : false;
                    }else{
                        $meta_value = $frm_entry_meta->get_entry_meta_by_field($record->id, $field->id);
                    }
                }
                
                $field_type = isset($post_values['field_options']['type_'.$field->id]) ? $post_values['field_options']['type_'.$field->id] : $field->type;
                $new_value = isset($post_values['item_meta'][$field->id]) ? maybe_unserialize($post_values['item_meta'][$field->id]) : $meta_value;

                $field_array = array(
                    'id' => $field->id,
                    'value' => $new_value,
                    'default_value' => $field->default_value,
                    'name' => $field->name,
                    'description' => $field->description,
                    'type' => apply_filters('frm_field_type', $field_type, $field, $new_value),
                    'options' => $field->options,
                    'required' => $field->required,
                    'field_key' => $field->field_key,
                    'field_order' => $field->field_order,
                    'form_id' => $field->form_id
                );
                
                /*if(in_array($field_array['type'], array('checkbox', 'radio', 'select')) and !empty($field_array['options'])){
                    foreach((array)$field_array['options'] as $opt_key => $opt){
                        if(!is_array($opt))
                            $field_array['options'][$opt_key] = array('label' => $opt);
                        unset($opt);
                        unset($opt_key);
                    }
                }*/
                
                $opt_defaults = FrmFieldsHelper::get_default_field_opts($field_array['type'], $field, true);
                
                foreach ($opt_defaults as $opt => $default_opt){
                    $field_array[$opt] = ($post_values && isset($post_values['field_options'][$opt.'_'.$field->id]) ) ? maybe_unserialize($post_values['field_options'][$opt.'_'.$field->id]) : (isset($field->field_options[$opt]) ? $field->field_options[$opt] : $default_opt);
                    if($opt == 'blank' and $field_array[$opt] == ''){
                        $field_array[$opt] = $frm_settings->blank_msg;
                    }else if($opt == 'invalid' and $field_array[$opt] == ''){
                        if($field_type == 'captcha')
                            $field_array[$opt] = $frm_settings->re_msg;
                        else
                            $field_array[$opt] = sprintf(__('%s is invalid', 'formidable'), $field_array['name']);
                    }
                }
                
                unset($opt_defaults);
                    
                if ($field_array['custom_html'] == '')
                    $field_array['custom_html'] = FrmFieldsHelper::get_default_html($field_type);
                
                if ($field_array['size'] == '')
                    $field_array['size'] = isset($frm_vars['sidebar_width']) ? $frm_vars['sidebar_width'] : '';
                
                $field_array = apply_filters('frm_setup_edit_fields_vars', $field_array, $field, $values['id']);
                
                if(!isset($field_array['unique']) or !$field_array['unique'])
                    $field_array['unique_msg'] = '';
                
                foreach((array)$field->field_options as $k => $v){
                    if(!isset($field_array[$k]))
                        $field_array[$k] = $v;
                    unset($k);
                    unset($v);
                }
                
                $values['fields'][$field->id] = $field_array;
                
                unset($field);   
        }
      
        $frm_form = new FrmForm();
        $form = $frm_form->getOne( $table == 'entries' ? $record->form_id : $record->id );
        unset($frm_form);

        if ($form){
            $values['form_name'] = (isset($record->form_id)) ? $form->name : '';
            if (is_array($form->options)){
                foreach ($form->options as $opt => $value){
                    if(in_array($opt, array('email_to', 'reply_to', 'reply_to_name')))
                        $values['notification'][0][$opt] = isset($post_values["notification[0][$opt]"]) ? maybe_unserialize($post_values["notification[0][$opt]"]) : $value;
                    
                    $values[$opt] = isset($post_values[$opt]) ? maybe_unserialize($post_values[$opt]) : $value;
                }
            }
        }
        
        $form_defaults = FrmFormsHelper::get_default_opts();
        
        //set to posted value or default
        foreach ($form_defaults as $opt => $default){
            if (!isset($values[$opt]) or $values[$opt] == ''){
                if($opt == 'notification'){
                    $values[$opt] = ($post_values and isset($post_values[$opt])) ? $post_values[$opt] : $default;
                    
                    foreach($default as $o => $d){
                        if($o == 'email_to')
                            $d = ''; //allow blank email address
                        $values[$opt][0][$o] = ($post_values and isset($post_values[$opt][0][$o])) ? $post_values[$opt][0][$o] : $d;
                        unset($o);
                        unset($d);
                    }
                }else{
                    $values[$opt] = ($post_values and isset($post_values['options'][$opt])) ? $post_values['options'][$opt] : $default;
                }    
            }else if($values[$opt] == 'notification'){
                foreach($values[$opt] as $k => $n){
                    foreach($default as $o => $d){
                        if(!isset($n[$o]))
                            $values[$opt][$k][$o] = ($post_values and isset($post_values[$opt][$k][$o])) ? $post_values[$opt][$k][$o] : $d;
                        unset($o);
                        unset($d);
                    }
                    unset($k);
                    unset($n);
                }
            }
            
            unset($opt);
            unset($defaut);
        }
         
        if (!isset($values['custom_style']))
            $values['custom_style'] = ($post_values and isset($post_values['options']['custom_style'])) ? $_POST['options']['custom_style'] : ($frm_settings->load_style != 'none');

        foreach(array('before', 'after', 'submit') as $h){
            if (!isset($values[$h.'_html']))
                $values[$h .'_html'] = (isset($post_values['options'][$h .'_html']) ? $post_values['options'][$h .'_html'] : FrmFormsHelper::get_default_html($h));
            unset($h);
        }
        
        if ($table == 'entries')
            $values = FrmEntriesHelper::setup_edit_vars( $values, $record );
        else if ($table == 'forms')
            $values = FrmFormsHelper::setup_edit_vars( $values, $record, $post_values );

        return $values;
    }
    
    public static function insert_opt_html($args){ 
        extract($args);
        
        $class = '';
        
        if ( in_array( $type, array( 'email', 'user_id', 'hidden', 'select', 'radio', 'checkbox', 'phone', 'text' ) ) ) {
            $class .= 'show_frm_not_email_to';
        }
    ?>
<li>
    <a class="frmids frm_insert_code alignright <?php echo $class ?>" data-code="<?php echo esc_attr($id) ?>" href="javascript:void(0)">[<?php echo $id ?>]</a>
    <a class="frmkeys frm_insert_code alignright <?php echo $class ?>" data-code="<?php echo esc_attr($key) ?>" href="javascript:void(0)">[<?php echo FrmAppHelper::truncate($key, 10) ?>]</a>
    <a class="frm_insert_code <?php echo $class ?>" data-code="<?php echo esc_attr($id) ?>" href="javascript:void(0)"><?php echo FrmAppHelper::truncate($name, 60) ?></a>
</li>
    <?php
    }
    
    public static function get_us_states(){
        return apply_filters('frm_us_states', array(
            'AL' => 'Alabama', 'AK' => 'Alaska', 'AR' => 'Arkansas', 'AZ' => 'Arizona', 
            'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
            'DC' => 'District of Columbia', 
            'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 
            'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 
            'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine','MD' => 'Maryland', 
            'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 
            'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 
            'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York', 
            'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 
            'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 
            'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 
            'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 
            'WI' => 'Wisconsin', 'WY' => 'Wyoming'
        ));
    }
    
    public static function get_countries(){
        return apply_filters('frm_countries', array(
            __('Afghanistan', 'formidable'), __('Albania', 'formidable'), __('Algeria', 'formidable'), 
            __('American Samoa', 'formidable'), __('Andorra', 'formidable'), __('Angola', 'formidable'),
            __('Anguilla', 'formidable'), __('Antarctica', 'formidable'), __('Antigua and Barbuda', 'formidable'), 
            __('Argentina', 'formidable'), __('Armenia', 'formidable'), __('Aruba', 'formidable'),
            __('Australia', 'formidable'), __('Austria', 'formidable'), __('Azerbaijan', 'formidable'),
            __('Bahamas', 'formidable'), __('Bahrain', 'formidable'), __('Bangladesh', 'formidable'), 
            __('Barbados', 'formidable'), __('Belarus', 'formidable'), __('Belgium', 'formidable'),
            __('Belize', 'formidable'), __('Benin', 'formidable'), __('Bermuda', 'formidable'), 
            __('Bhutan', 'formidable'), __('Bolivia', 'formidable'), __('Bosnia and Herzegovina', 'formidable'),
            __('Botswana', 'formidable'), __('Brazil', 'formidable'), __('Brunei', 'formidable'), 
            __('Bulgaria', 'formidable'), __('Burkina Faso', 'formidable'), __('Burundi', 'formidable'),
            __('Cambodia', 'formidable'), __('Cameroon', 'formidable'), __('Canada', 'formidable'), 
            __('Cape Verde', 'formidable'), __('Cayman Islands', 'formidable'), __('Central African Republic', 'formidable'), 
            __('Chad', 'formidable'), __('Chile', 'formidable'), __('China', 'formidable'),
            __('Colombia', 'formidable'), __('Comoros', 'formidable'), __('Congo', 'formidable'),
            __('Costa Rica', 'formidable'), __('C&ocirc;te d\'Ivoire', 'formidable'), __('Croatia', 'formidable'),
            __('Cuba', 'formidable'), __('Cyprus', 'formidable'), __('Czech Republic', 'formidable'), 
            __('Denmark', 'formidable'), __('Djibouti', 'formidable'), __('Dominica', 'formidable'),
            __('Dominican Republic', 'formidable'), __('East Timor', 'formidable'), __('Ecuador', 'formidable'), 
            __('Egypt', 'formidable'), __('El Salvador', 'formidable'), __('Equatorial Guinea', 'formidable'),
            __('Eritrea', 'formidable'), __('Estonia', 'formidable'), __('Ethiopia', 'formidable'), 
            __('Fiji', 'formidable'), __('Finland', 'formidable'), __('France', 'formidable'), 
            __('French Guiana', 'formidable'), __('French Polynesia', 'formidable'), __('Gabon', 'formidable'), 
            __('Gambia', 'formidable'), __('Georgia', 'formidable'), __('Germany', 'formidable'),
            __('Ghana', 'formidable'), __('Gibraltar', 'formidable'), __('Greece', 'formidable'), 
            __('Greenland', 'formidable'), __('Grenada', 'formidable'), __('Guam', 'formidable'),
            __('Guatemala', 'formidable'), __('Guinea', 'formidable'), __('Guinea-Bissau', 'formidable'), 
            __('Guyana', 'formidable'), __('Haiti', 'formidable'), __('Honduras', 'formidable'), 
            __('Hong Kong', 'formidable'), __('Hungary', 'formidable'), __('Iceland', 'formidable'), 
            __('India', 'formidable'), __('Indonesia', 'formidable'), __('Iran', 'formidable'), 
            __('Iraq', 'formidable'), __('Ireland', 'formidable'), __('Israel', 'formidable'), 
            __('Italy', 'formidable'), __('Jamaica', 'formidable'), __('Japan', 'formidable'), 
            __('Jordan', 'formidable'), __('Kazakhstan', 'formidable'), __('Kenya', 'formidable'), 
            __('Kiribati', 'formidable'), __('North Korea', 'formidable'), __('South Korea', 'formidable'), 
            __('Kuwait', 'formidable'), __('Kyrgyzstan', 'formidable'), __('Laos', 'formidable'), 
            __('Latvia', 'formidable'), __('Lebanon', 'formidable'), __('Lesotho', 'formidable'), 
            __('Liberia', 'formidable'), __('Libya', 'formidable'), __('Liechtenstein', 'formidable'), 
            __('Lithuania', 'formidable'), __('Luxembourg', 'formidable'), __('Macedonia', 'formidable'), 
            __('Madagascar', 'formidable'), __('Malawi', 'formidable'), __('Malaysia', 'formidable'), 
            __('Maldives', 'formidable'), __('Mali', 'formidable'), __('Malta', 'formidable'), 
            __('Marshall Islands', 'formidable'), __('Mauritania', 'formidable'), __('Mauritius', 'formidable'), 
            __('Mexico', 'formidable'), __('Micronesia', 'formidable'), __('Moldova', 'formidable'), 
            __('Monaco', 'formidable'), __('Mongolia', 'formidable'), __('Montenegro', 'formidable'), 
            __('Montserrat', 'formidable'), __('Morocco', 'formidable'), __('Mozambique', 'formidable'), 
            __('Myanmar', 'formidable'), __('Namibia', 'formidable'), __('Nauru', 'formidable'), 
            __('Nepal', 'formidable'), __('Netherlands', 'formidable'), __('New Zealand', 'formidable'),
            __('Nicaragua', 'formidable'), __('Niger', 'formidable'), __('Nigeria', 'formidable'), 
            __('Norway', 'formidable'), __('Northern Mariana Islands', 'formidable'), __('Oman', 'formidable'), 
            __('Pakistan', 'formidable'), __('Palau', 'formidable'), __('Palestine', 'formidable'), 
            __('Panama', 'formidable'), __('Papua New Guinea', 'formidable'), __('Paraguay', 'formidable'), 
            __('Peru', 'formidable'), __('Philippines', 'formidable'), __('Poland', 'formidable'), 
            __('Portugal', 'formidable'), __('Puerto Rico', 'formidable'), __('Qatar', 'formidable'), 
            __('Romania', 'formidable'), __('Russia', 'formidable'), __('Rwanda', 'formidable'), 
            __('Saint Kitts and Nevis', 'formidable'), __('Saint Lucia', 'formidable'), 
            __('Saint Vincent and the Grenadines', 'formidable'), __('Samoa', 'formidable'), 
            __('San Marino', 'formidable'), __('Sao Tome and Principe', 'formidable'), __('Saudi Arabia', 'formidable'),
            __('Senegal', 'formidable'), __('Serbia and Montenegro', 'formidable'), __('Seychelles', 'formidable'), 
            __('Sierra Leone', 'formidable'), __('Singapore', 'formidable'), __('Slovakia', 'formidable'), 
            __('Slovenia', 'formidable'), __('Solomon Islands', 'formidable'), __('Somalia', 'formidable'), 
            __('South Africa', 'formidable'), __('Spain', 'formidable'), __('Sri Lanka', 'formidable'), 
            __('Sudan', 'formidable'), __('Suriname', 'formidable'), __('Swaziland', 'formidable'), 
            __('Sweden', 'formidable'), __('Switzerland', 'formidable'), __('Syria', 'formidable'), 
            __('Taiwan', 'formidable'), __('Tajikistan', 'formidable'), __('Tanzania', 'formidable'), 
            __('Thailand', 'formidable'), __('Togo', 'formidable'), __('Tonga', 'formidable'), 
            __('Trinidad and Tobago', 'formidable'), __('Tunisia', 'formidable'), __('Turkey', 'formidable'), 
            __('Turkmenistan', 'formidable'), __('Tuvalu', 'formidable'), __('Uganda', 'formidable'), 
            __('Ukraine', 'formidable'), __('United Arab Emirates', 'formidable'), __('United Kingdom', 'formidable'),
            __('United States', 'formidable'), __('Uruguay', 'formidable'), __('Uzbekistan', 'formidable'), 
            __('Vanuatu', 'formidable'), __('Vatican City', 'formidable'), __('Venezuela', 'formidable'), 
            __('Vietnam', 'formidable'), __('Virgin Islands, British', 'formidable'), 
            __('Virgin Islands, U.S.', 'formidable'), __('Yemen', 'formidable'), __('Zambia', 'formidable'), 
            __('Zimbabwe', 'formidable')
        ));
    }
    
    public static function truncate($str, $length, $minword = 3, $continue = '...'){
        if(is_array($str))
            return;
        
        $length = (int)$length;
        $str = strip_tags($str);
        $original_len = (function_exists('mb_strlen')) ? mb_strlen($str) : strlen($str);
        
        if($length == 0){
            return '';
        }else if($length <= 10){
            $sub = (function_exists('mb_substr')) ? mb_substr($str, 0, $length) : substr($str, 0, $length);
            return $sub . (($length < $original_len) ? $continue : '');
        }
            
        $sub = '';
        $len = 0;

        $words = (function_exists('mb_split')) ? mb_split(' ', $str) : explode(' ', $str);
            
        foreach ($words as $word){
            $part = (($sub != '') ? ' ' : '') . $word;
            $total_len = (function_exists('mb_strlen')) ? mb_strlen($sub . $part) : strlen($sub. $part);
            if ( $total_len > $length && str_word_count($sub) ) {
                break;
            }
            
            $sub .= $part;
            $len += (function_exists('mb_strlen')) ? mb_strlen($part) : strlen($part);
            
            if ( str_word_count($sub) > $minword && $total_len >= $length ) {
                break;
            }
            
            unset($total_len, $word);
        }
        
        return $sub . (($len < $original_len) ? $continue : '');
    }
    
    /*
    * Added for < 4.0 compatability
    *
    * @since 1.07.10
    *
    * @param $term The value to escape
    * @return string The escaped value
    */
    public static function esc_like($term) {
        global $wpdb;
        if ( method_exists($wpdb, 'esc_like') ) { // WP 4.0
            $term = $wpdb->esc_like( $term );
        } else {
            $term = like_escape( $term );
        }
        
        return $term;
    }
    
    public static function prepend_and_or_where( $starts_with = ' WHERE ', $where = '' ){
        if ( empty($where) ) {
            return '';
        }
        
        if(is_array($where)){
            global $frmdb, $wpdb;
            extract($frmdb->get_where_clause_and_values( $where ));
            $where = $wpdb->prepare($where, $values);
        }else{
            $where = $starts_with . $where;
        }
        
        return $where;
    }
    
    // Pagination Methods
    public static function getLastRecordNum($r_count,$current_p,$p_size){
      return (($r_count < ($current_p * $p_size))?$r_count:($current_p * $p_size));
    }

    public static function getFirstRecordNum($r_count,$current_p,$p_size){
      if($current_p == 1)
        return 1;
      else
        return (self::getLastRecordNum($r_count,($current_p - 1),$p_size) + 1);
    }
    
    public static function getRecordCount($where="", $table_name){
        global $wpdb;
        $query = 'SELECT COUNT(*) FROM ' . $table_name . FrmAppHelper::prepend_and_or_where(' WHERE ', $where);
        return $wpdb->get_var($query);
    }
    
    public static function get_referer_query($query) {
    	if (strpos($query, "google.")) {
    	    //$pattern = '/^.*\/search.*[\?&]q=(.*)$/';
            $pattern = '/^.*[\?&]q=(.*)$/';
    	} else if (strpos($query, "bing.com")) {
    		$pattern = '/^.*q=(.*)$/';
    	} else if (strpos($query, "yahoo.")) {
    		$pattern = '/^.*[\?&]p=(.*)$/';
    	} else if (strpos($query, "ask.")) {
    		$pattern = '/^.*[\?&]q=(.*)$/';
    	} else {
    		return false;
    	}
    	preg_match($pattern, $query, $matches);
    	$querystr = substr($matches[1], 0, strpos($matches[1], '&'));
    	return urldecode($querystr);
    }
    
    public static function get_referer_info(){
        $referrerinfo = '';
    	$keywords = array();
    	$i = 1;
    	if(isset($_SESSION) and isset($_SESSION['frm_http_referer']) and $_SESSION['frm_http_referer']){
        	foreach ($_SESSION['frm_http_referer'] as $referer) {
        		$referrerinfo .= str_pad("Referer $i: ",20) . $referer. "\r\n";
        		$keywords_used = FrmAppHelper::get_referer_query($referer);
        		if ($keywords_used)
        			$keywords[] = $keywords_used;

        		$i++;
        	}
        	
        	$referrerinfo .= "\r\n";
	    }else{
	        $referrerinfo = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	    }

    	$i = 1;
    	if(isset($_SESSION) and isset($_SESSION['frm_http_pages']) and $_SESSION['frm_http_pages']){
        	foreach ($_SESSION['frm_http_pages'] as $page) {
        		$referrerinfo .= str_pad("Page visited $i: ",20) . $page. "\r\n";
        		$i++;
        	}
        	
        	$referrerinfo .= "\r\n";
	    }

    	$i = 1;
    	foreach ($keywords as $keyword) {
    		$referrerinfo .= str_pad("Keyword $i: ",20) . $keyword. "\r\n";
    		$i++;
    	}
    	$referrerinfo .= "\r\n";
    	
    	return $referrerinfo;
    }
    
    public static function json_to_array($json_vars){
        $vars = array();
        foreach($json_vars as $jv){
            $jv_name = explode('[', $jv['name']);
            $last = count($jv_name)-1;
            foreach($jv_name as $p => $n){
                $name = trim($n, ']');
                $this_val = ($p == $last) ? $jv['value'] : array();
                   
                if(!$p){
                    $l1 = $name;
                    if($name == '')
                        $vars[] = $this_val;
                    else if(!isset($vars[$l1]))
                        $vars[$l1] = $this_val;
                }
                
                if($p == 1){
                    $l2 = $name;
                    if($name == '')
                        $vars[$l1][] = $this_val;
                    else if(!isset($vars[$l1][$l2]))
                        $vars[$l1][$l2] = $this_val;
                }
                
                if($p == 2){
                    $l3 = $name;
                    if($name == '')
                        $vars[$l1][$l2][] = $this_val;
                    else if(!isset($vars[$l1][$l2][$l3]))
                        $vars[$l1][$l2][$l3] = $this_val;
                }
                
                unset($this_val);
                unset($n);
            }
            
            unset($last);
            unset($jv);
        }
        
        return $vars;
    }
    
    public static function maybe_json_decode($string){
        $new_string = json_decode($string, true);
        if ( function_exists('json_last_error') ) { // php 5.3+
            if ( json_last_error() == JSON_ERROR_NONE ) {
                $string = $new_string;
            }
        } else if ( isset($new_string) ) { // php < 5.3 fallback
            $string = $new_string;
        }
        return $string;
    }
    
    public static function check_mem_use($function='', $start_mem=0) {
        $mem = memory_get_usage(true) - $start_mem;
        
        //error_log($mem .' '. $function);
        return $start_mem + $mem;
    }
    
    /*
    * @since 1.07.10
    *
    * @param string $post_type The name of the post type that may need to be highlighted
    * @return echo The javascript to open and highlight the Formidable menu
    */
    public static function maybe_highlight_menu($post_type) {
        global $post, $pagenow;

        if ( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] != $post_type ) {
            return;
        }
        
        if ( is_object($post) && $post->post_type != $post_type ) {
            return;
        }
        
        echo <<<HTML
<script type="text/javascript">
jQuery(document).ready(function(){
jQuery('#toplevel_page_formidable').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu wp-menu-open');
jQuery('#toplevel_page_formidable a.wp-has-submenu').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu wp-menu-open');
});
</script>
HTML;
    }
    
    /*
    * @since 1.07.10
    *
    * @param float $min_version The version the add-on requires
    * @return echo The message on the plugins listing page
    */
    public static function min_version_notice($min_version) {
        $frm_version = self::plugin_version();
        
        // check if Formidable meets minimum requirements
        if ( version_compare($frm_version, $min_version, '>=') ) {
            return;
        }
        
        $wp_list_table = _get_list_table('WP_Plugins_List_Table');
        echo '<tr class="plugin-update-tr active"><th colspan="' . $wp_list_table->get_column_count() . '" class="check-column plugin-update colspanchange"><div class="update-message">'.
        __('You are running an outdated version of Formidable. This plugin may not work correctly if you do not update Formidable.', 'formidable') .
        '</div></td></tr>';
    }
}
