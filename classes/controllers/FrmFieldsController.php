<?php

class FrmFieldsController{

    public static function load_field(){
        $fields = $_POST['field'];
        if ( empty($fields) ) {
            die();
        }

        $_GET['page'] = 'formidable';
        $fields = stripslashes_deep($fields);

        $ajax = true;
        $values = array();
        $path = FrmAppHelper::plugin_path();
        $field_html = array();

        foreach ( $fields as $field ) {
            $field = htmlspecialchars_decode(nl2br($field));
            $field = json_decode($field, true);
            if ( ! isset($field['id']) ) {
                // this field may have already been loaded
                continue;
            }

            $field_id = $field['id'];

            if ( ! isset( $field['value'] ) ) {
                $field['value'] = '';
            }

            $field_name = 'item_meta['. $field_id .']';
            $html_id = FrmFieldsHelper::get_html_id($field);

            ob_start();
            include($path .'/classes/views/frm-forms/add_field.php');
            $field_html[$field_id] = ob_get_contents();
            ob_end_clean();
        }

        unset($path);

        echo json_encode($field_html);

        die();
    }

    public static function create(){
        $field_type = $_POST['field'];
        $form_id = $_POST['form_id'];

        $field = self::include_new_field($field_type, $form_id);

        // this hook will allow for multiple fields to be added at once
        do_action('frm_after_field_created', $field, $form_id);

        wp_die();
    }

    public static function include_new_field($field_type, $form_id) {
        $values = array();
        if ( FrmAppHelper::pro_is_installed() ) {
            $values['post_type'] = FrmProFormsHelper::post_type($form_id);
        }

        $field_values = apply_filters('frm_before_field_created', FrmFieldsHelper::setup_new_vars($field_type, $form_id));
        $field_id = FrmField::create( $field_values );

        if ( ! $field_id ) {
            return false;
        }

        $field = self::include_single_field($field_id, $values, $form_id);

        return $field;
    }

    public static function update_form_id() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

        $field_id = (int) $_POST['field'];
        $form_id = (int) $_POST['form_id'];
        if ( ! $field_id || ! $form_id ) {
            return;
        }

        FrmField::update( $field_id, compact('form_id') );

        die();
    }

    public static function edit_name($field = 'name', $id = '') {
        if ( empty($field) ) {
            $field = 'name';
        }

        if ( empty($id) ) {
            $id = str_replace('field_label_', '', $_POST['element_id']);
        }

        $value = trim($_POST['update_value']);
        if ( trim(strip_tags($value)) == '' ) {
            // set blank value if there is no content
            $value = '';
        }

        FrmField::update($id, array($field => $value));
        echo stripslashes($value);
        wp_die();
    }

    public static function update_ajax_option(){
        $field = FrmField::getOne($_POST['field']);
        foreach ( array('clear_on_focus', 'separate_value', 'default_blank') as $val ) {
            if ( isset($_POST[$val]) ) {
                $new_val = $_POST[$val];
                if ( $val == 'separate_value' ) {
                    $new_val = (isset($field->field_options[$val]) && $field->field_options[$val]) ? 0 : 1;
                }

                $field->field_options[$val] = $new_val;
                unset($new_val);
            }
            unset($val);
        }

        FrmField::update( $_POST['field'], array(
            'field_options' => $field->field_options,
            'form_id' => $field->form_id
        ) );
        die();
    }

    public static function duplicate(){
        global $wpdb;

        $copy_field = FrmField::getOne($_POST['field_id']);
        if ( ! $copy_field ) {
            die();
        }

        $form_id = (int) $_POST['form_id'];

        do_action('frm_duplicate_field', $copy_field, $form_id);
        do_action('frm_duplicate_field_'. $copy_field->type, $copy_field, $form_id);

        $values = array();
        FrmFieldsHelper::fill_field( $values, $copy_field, $form_id );

        $field_count = $wpdb->get_var( $wpdb->prepare('SELECT COUNT(*) FROM '. $wpdb->prefix .'frm_fields fi LEFT JOIN '. $wpdb->prefix .'frm_forms fr ON (fi.form_id = fr.id) WHERE fr.id = %d OR fr.parent_form_id = %d', $form_id, $form_id) );

        $values['field_order'] = $field_count + 1;

        if ( ! $field_id = FrmField::create($values) ) {
            die();
        }

        self::include_single_field($field_id, $values);

        die();
    }

    /*
    * Load a single field in the form builder along with all needed variables
    */
    public static function include_single_field( $field_id, $values, $form_id = 0 ) {
        $field = FrmFieldsHelper::setup_edit_vars(FrmField::getOne($field_id));
        $field_name = 'item_meta['. $field_id .']';
        $html_id = FrmFieldsHelper::get_html_id($field);
        $id = $form_id ? $form_id : $field['form_id'];
        if ( $field['type'] == 'html' ) {
            $field['stop_filter'] = true;
        }

        require(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/add_field.php');

        return $field;
    }

    public static function destroy(){
        FrmField::destroy($_POST['field_id']);
        die();
    }

    /* Field Options */

    //Add Single Option or Other Option
    public static function add_option(){
        $id = $_POST['field_id'];
        $opt_type = $_POST['opt_type'];

        //Get the field
        $field = FrmField::getOne($id);
        $options = maybe_unserialize($field->options);
        if ( !empty($options) ) {
            $keys = array_keys( $options );
            $last = str_replace( 'other_', '', end( $keys ) );
        } else {
            $last = 0;
        }
        $opt_key = $last + 1;

        if ( 'other' == $opt_type ) {
            $opt = __('Other', 'formidable');
            $other_val = '';
            $opt_key = 'other_' . $opt_key;

            //Update value of "other" in DB
            $field_options = maybe_unserialize( $field->field_options );
            $field_options['other'] = 1;
            FrmField::update( $id, array( 'field_options' => maybe_serialize( $field_options ) ) );
        } else {
            $first_opt = reset($options);
            $next_opt = count($options);
            if ( $first_opt != '' ) {
                $next_opt++;
            }
            $opt = __('Option', 'formidable') .' '. $next_opt;
            unset($next_opt);
        }
        $field_val = $opt;
        $options[$opt_key] = $opt;

        //Update options in DB
        FrmField::update($id, array('options' => maybe_serialize($options)));

        $field_data = $field;
        $field = array(
            'type'  => $field_data->type,
            'id'    => $id,
            'separate_value' => isset($field_data->field_options['separate_value']) ? $field_data->field_options['separate_value'] : 0,
            'form_id' => $field_data->form_id,
            'field_key' => $field_data->field_key,
        );

        $field_name = "item_meta[$id]";
        $html_id = FrmFieldsHelper::get_html_id($field);
        $checked = '';

        if ( 'other' == $opt_type ) {
            require(FrmAppHelper::plugin_path() .'/pro/classes/views/frmpro-fields/other-option.php');
        } else {
            require(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/single-option.php');
        }
        die();
    }

    public static function edit_option() {
        $ids = explode('-', $_POST['element_id']);
        $id = $_POST['field_id'];
        $update_value = trim($_POST['update_value']);
        if ( strpos($_POST['element_id'], 'key_') ) {
            $new_value = $update_value;
        } else {
            $new_label = $update_value;
        }

        $field = FrmField::getOne($id);
        $separate_values = ( isset($field->field_options['separate_value']) && $field->field_options['separate_value'] );
        $options = maybe_unserialize($field->options);

        $this_opt_id = end($ids);
        $this_opt = (array) $options[$this_opt_id];
        $other_opt = ( $this_opt_id && strpos( $this_opt_id, 'other') !== false ? true : false );

        $label = isset($this_opt['label']) ? $this_opt['label'] : reset($this_opt);
        $value =  isset($this_opt['value']) ? $this_opt['value'] : '';

        if ( ! isset( $new_label ) ) {
            $new_label = $label;
        }

        if ( isset($new_value) || isset($value) ) {
            $update_value = isset($new_value) ? $new_value : $value;
        }

        if ( $update_value != $new_label && $other_opt == false && $separate_values ) {
            $options[$this_opt_id] = array('value' => $update_value, 'label' => $new_label);
        } else {
            $options[$this_opt_id] = trim($_POST['update_value']);
        }

        FrmField::update($field->id, array('options' => $options));
        echo (trim($_POST['update_value']) == '') ? __('(Blank)', 'formidable') : stripslashes($_POST['update_value']);
        die();
    }

    public static function delete_option(){
        $field = FrmField::getOne($_POST['field_id']);
        $opt_key = $_POST['opt_key'];
        $options = maybe_unserialize($field->options);
        unset($options[$opt_key]);
        $response = array( 'other' => true );

        //If the deleted option is an "other" option
        if ( FrmAppHelper::is_other_opt( $opt_key ) ) {
            //Assume all other options are gone, unless proven otherwise
            $other = false;

            //Check if all other options are really gone
            foreach ( $options as $o_key => $o_val ) {
                //If there is still an other option in the field, set other to true
                if ( FrmAppHelper::is_other_opt( $o_key ) ) {
                    $other = true;
                    break;
                }
                unset( $o_key, $o_val );
            }

            //If all other options are gone
            if ( false === $other ) {
                $field_options = maybe_unserialize( $field->field_options );
                $field_options['other'] = 0;
                FrmField::update( $_POST['field_id'], array( 'field_options' => maybe_serialize( $field_options ) ) );
                $response = array('other' => false );
            }
        }
        echo json_encode( $response );

        FrmField::update($_POST['field_id'], array('options' => maybe_serialize($options)));

        die();
    }

    public static function import_choices(){
        if ( !current_user_can('frm_edit_forms') ) {
            return;
        }

        $field_id = $_REQUEST['field_id'];

        global $current_screen, $hook_suffix;

        // Catch plugins that include admin-header.php before admin.php completes.
        if ( empty( $current_screen ) && function_exists('set_current_screen') ) {
            $hook_suffix = '';
        	set_current_screen();
        }

        if ( function_exists('register_admin_color_schemes') ) {
            register_admin_color_schemes();
        }

        $hook_suffix = $admin_body_class = '';

        if ( get_user_setting('mfold') == 'f' ) {
        	$admin_body_class .= ' folded';
        }

        if ( function_exists('is_admin_bar_showing') && is_admin_bar_showing() ) {
        	$admin_body_class .= ' admin-bar';
        }

        if ( is_rtl() ) {
        	$admin_body_class .= ' rtl';
        }

        $admin_body_class .= ' admin-color-' . sanitize_html_class( get_user_option( 'admin_color' ), 'fresh' );
        $prepop = array();
        self::get_bulk_prefilled_opts($prepop);

        $field = FrmField::getOne($field_id);

        include(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/import_choices.php');
        die();
    }

    private static function get_bulk_prefilled_opts(array &$prepop) {
        $prepop[__('Countries', 'formidable')] = FrmAppHelper::get_countries();

        $states = FrmAppHelper::get_us_states();
        $state_abv = array_keys($states);
        sort($state_abv);
        $prepop[__('U.S. State Abbreviations', 'formidable')] = $state_abv;

        $states = array_values($states);
        sort($states);
        $prepop[__('U.S. States', 'formidable')] = $states;
        unset($state_abv, $states);

        $prepop[__('Age', 'formidable')] = array(
            __('Under 18', 'formidable'), __('18-24', 'formidable'), __('25-34', 'formidable'),
            __('35-44', 'formidable'), __('45-54', 'formidable'), __('55-64', 'formidable'),
            __('65 or Above', 'formidable'), __('Prefer Not to Answer', 'formidable')
        );

        $prepop[__('Satisfaction', 'formidable')] = array(
            __('Very Satisfied', 'formidable'), __('Satisfied', 'formidable'), __('Neutral', 'formidable'),
            __('Unsatisfied', 'formidable'), __('Very Unsatisfied', 'formidable'), __('N/A', 'formidable')
        );

        $prepop[__('Importance', 'formidable')] = array(
            __('Very Important', 'formidable'), __('Important', 'formidable'), __('Neutral', 'formidable'),
            __('Somewhat Important', 'formidable'), __('Not at all Important', 'formidable'), __('N/A', 'formidable')
        );

        $prepop[__('Agreement', 'formidable')] = array(
            __('Strongly Agree', 'formidable'), __('Agree', 'formidable'), __('Neutral', 'formidable'),
            __('Disagree', 'formidable'), __('Strongly Disagree', 'formidable'), __('N/A', 'formidable')
        );

        $prepop = apply_filters('frm_bulk_field_choices', $prepop);
    }

    public static function import_options(){
        if ( ! is_admin() || ! current_user_can('frm_edit_forms') ) {
            return;
        }

        $field_id = $_POST['field_id'];
        $field = FrmField::getOne($field_id);

        if ( ! in_array($field->type, array('radio', 'checkbox', 'select')) ) {
            return;
        }

        $field = FrmFieldsHelper::setup_edit_vars($field);
        $opts = stripslashes_deep($_POST['opts']);
        $opts = explode("\n", rtrim($opts, "\n"));
        if ( $field['separate_value'] ) {
            foreach ( $opts as $opt_key => $opt ) {
                if ( strpos($opt, '|') !== false ) {
                    $vals = explode('|', $opt);
                    if ( $vals[0] != $vals[1] ) {
                        $opts[$opt_key] = array('label' => trim($vals[0]), 'value' => trim($vals[1]));
                    }
                    unset($vals);
                }
                unset($opt_key, $opt);
            }
        }

        //Keep other options after bulk update
        if ( isset( $field['field_options']['other'] ) && $field['field_options']['other'] == true ) {
            $other_array = array();
            foreach ( $field['options'] as $opt_key => $opt ) {
                if ( $opt_key && strpos( $opt_key, 'other' ) !== false ) {
                    $other_array[$opt_key] = $opt;
                }
                unset($opt_key, $opt);
            }
            if ( ! empty($other_array) ) {
                $opts = array_merge( $opts, $other_array);
            }
        }

        FrmField::update( $field_id, array( 'options' => maybe_serialize( $opts ) ) );

        $field['options'] = $opts;
        $field_name = $field['name'];

        if ( $field['type'] == 'radio' || $field['type'] == 'checkbox' ) {
            require(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/radio.php');
        } else {
            FrmFieldsHelper::show_single_option($field);
        }

        die();
    }

    public static function update_order(){
        if ( isset($_POST) && isset($_POST['frm_field_id']) ) {
            foreach ($_POST['frm_field_id'] as $position => $item)
                FrmField::update($item, array('field_order' => $position));
        }
        die();
    }

    public static function change_type($type){
        $type_switch = array(
            'scale'     => 'radio',
            '10radio'   => 'radio',
            'rte'       => 'textarea',
            'website'   => 'url',
        );
        if ( isset($type_switch[$type]) ) {
            $type = $type_switch[$type];
        }

        $frm_field_selection = FrmFieldsHelper::field_selection();
        $types = array_keys($frm_field_selection);
        if ( ! in_array($type, $types) && $type != 'captcha' ) {
            $type = 'text';
        }

        return $type;
    }

    public static function display_field_options($display){
        switch($display['type']){
            case 'captcha':
                $display['required'] = false;
                $display['invalid'] = true;
                $display['default_blank'] = false;
            break;
            case 'radio':
                $display['default_blank'] = false;
            break;
            case 'text':
            case 'textarea':
                $display['size'] = true;
                $display['clear_on_focus'] = true;
            break;
            case 'select':
                $display['size'] = true;
            break;
            case 'url':
            case 'website':
            case 'email':
                $display['size'] = true;
                $display['clear_on_focus'] = true;
                $display['invalid'] = true;
        }

        return $display;
    }

    public static function input_html($field, $echo=true){
        $class = array(); //$field['type'];
        self::add_input_classes($field, $class);

        $add_html = array();
        self::add_html_size($field, $add_html);
        self::add_html_length($field, $add_html);
        self::add_html_placeholder($field, $add_html, $class);

        $class = apply_filters('frm_field_classes', implode(' ', $class), $field);

        if ( ! empty($class) ) {
            $add_html['class'] = 'class="'. trim($class) .'"';
        }

        self::add_shortcodes_to_html($field, $add_html);

        $add_html = implode(' ', $add_html);

        if ( $echo ) {
            echo $add_html;
        }

        return $add_html;
    }

    private static function add_input_classes($field, array &$class) {
        global $frm_vars;
        if ( is_admin() && ! FrmAppHelper::is_preview_page() && ! in_array($field['type'], array('scale', 'radio', 'checkbox', 'data')) ) {
            $class[] = 'dyn_default_value';
        }

        if ( isset($field['size']) && $field['size'] > 0 ) {
            $class[] = 'auto_width';
        }

        if ( isset($field['input_class']) && ! empty($field['input_class']) ) {
            $class[] = $field['input_class'];
        }
    }

    private static function add_html_size($field, array &$add_html) {
        if ( ! isset($field['size']) || $field['size'] <= 0 || in_array($field['type'], array('select', 'data', 'time', 'hidden')) ) {
            return;
        }

        if ( FrmAppHelper::is_admin_page('formidable') ) {
            return;
        }

        if ( is_numeric($field['size']) ) {
            $field['size'] .= 'px';
        }

        $important = apply_filters('frm_use_important_width', 1, $field);
        $add_html['style'] = 'style="width:'. $field['size'] . ( $important ? ' !important' : '' ) .'"';

        self::add_html_cols($field, $add_html);
    }

    private static function add_html_cols($field, array &$add_html) {
        if ( ! in_array($field['type'], array('textarea', 'rte')) ) {
            return;
        }

        // convert to cols for textareas
        $calc = array(
            ''      => 7.08,
            'px'    => 7.08,
            'rem'   => 0.444,
            'em'    => 0.544,
        );

        // include "col" for valid html
        $unit = trim(preg_replace('/[0-9]+/', '', $field['size']));

        if ( ! isset($calc[$unit]) ) {
            return;
        }

        $size = (float) str_replace($unit, '', $field['size']) / $calc[$unit];

        $add_html['cols'] = 'cols="'. (int) $size .'"';
    }

    private static function add_html_length($field, array &$add_html) {
        // check for max setting and if this field accepts maxlength
        if ( ! isset($field['max']) || empty($field['max']) || in_array( $field['type'], array('textarea', 'rte', 'hidden') ) ) {
            return;
        }

        if ( FrmAppHelper::is_admin_page('formidable') ) {
            // don't load on form builder page
            return;
        }

        $add_html['maxlength'] = 'maxlength="'. $field['max'] .'"';
    }

    private static function add_html_placeholder($field, array &$add_html, array &$class) {
        // check for a default value and placeholder setting
        if ( ! isset($field['clear_on_focus']) || ! $field['clear_on_focus'] || empty($field['default_value']) ) {
            return;
        }

        // don't apply this to the form builder page
        if ( FrmAppHelper::is_admin_page('formidable') ) {
            return;
        }

        $frm_settings = FrmAppHelper::get_settings();

        if ( $frm_settings->use_html && ! in_array($field['type'], array('select', 'radio', 'checkbox', 'hidden')) ) {
            // use HMTL5 placeholder with js fallback
            $add_html['placeholder'] = 'placeholder="'. esc_attr($field['default_value']) .'"';
            wp_enqueue_script('jquery-placeholder');
        } else if ( ! $frm_settings->use_html ) {
            $val = str_replace(array("\r\n", "\n"), '\r', addslashes(str_replace('&#039;', "'", esc_attr($field['default_value']))));
            $add_html['data-frmval'] = 'data-frmval="'. esc_attr($val) .'"';
            $class[] = 'frm_toggle_default';

            if ( $field['value'] == $field['default_value'] ) {
                $class[] = 'frm_default';
            }
        }
    }

    private static function add_shortcodes_to_html($field, array &$add_html) {
        if ( ! isset($field['shortcodes']) || empty($field['shortcodes']) ) {
            return;
        }

        foreach ( $field['shortcodes'] as $k => $v ) {
            if ( 'opt' === $k ) {
                continue;
            }

            if ( is_numeric($k) && strpos($v, '=') ) {
                $add_html[] = $v;
            } else if ( ! empty($k) && isset($add_html[$k]) ) {
                $add_html[$k] = str_replace($k .'="', $k .'="'. $v, $add_html[$k]);
            } else {
                $add_html[$k] = $k .'="'. $v .'"';
            }

            unset($k, $v);
        }
    }

    public static function check_value($opt, $opt_key, $field){
        if(is_array($opt)){
            if ( isset($field['separate_value']) && $field['separate_value'] ) {
                $opt = isset($opt['value']) ? $opt['value'] : (isset($opt['label']) ? $opt['label'] : reset($opt));
            } else {
                $opt = (isset($opt['label']) ? $opt['label'] : reset($opt));
            }
        }
        return $opt;
    }

    public static function check_label($opt){
        if ( is_array($opt) ) {
            $opt = (isset($opt['label']) ? $opt['label'] : reset($opt));
        }

        return $opt;
    }

    public static function add_conditional_update_msg() {
        echo '<tr><td colspan="2">';
        FrmAppHelper::update_message('calculate and conditionally hide and show fields');
        echo '</td></tr>';
    }
}
