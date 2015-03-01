<?php
if(!defined('ABSPATH')) die('You are not allowed to call this page directly.');

class FrmAppHelper{
    public static $db_version = 18; //version of the database we are moving to
    public static $pro_db_version = 27;

    /*
    * @since 2.0
    */
    public static $plug_version = '2.0rc2';

    /*
    * @since 1.07.02
    *
    * @param none
    * @return float The version of this plugin
    */
    public static function plugin_version() {
        return self::$plug_version;
    }

    public static function plugin_folder() {
        return basename(self::plugin_path());
    }

    public static function plugin_path(){
        return dirname(dirname(dirname(__FILE__)));
    }

    public static function plugin_url($url=''){
        //prevously FRM_URL constant
        if ( empty($url) ) {
            $url = plugins_url('', self::plugin_folder() .'/formidable.php');
        }

        if ( is_ssl() && !preg_match('/^https:\/\/.*\..*$/', $url) ) {
            $url = str_replace('http://', 'https://', $url);
        }

        return $url;
    }

    /*
    * @return string Site URL
    */
    public static function site_url(){
        $url = self::plugin_url(site_url());
        return $url;
    }

    /*
    * Get the name of this site
    * Used for [sitename] shortcode
    *
    * @since 2.0
    * @return string
    */
    public static function site_name() {
        return get_option('blogname');
    }

    /*
    * Get the Formidable settings
    *
    * @since 2.0
    *
    * @param None
    * @return Object $frm_setings
    */
    public static function get_settings() {
        global $frm_settings;
        if ( empty($frm_settings) ) {
            $frm_settings = new FrmSettings();
        }
        return $frm_settings;
    }

    /*
    * Show a message in place of pro features
    *
    * @since 2.0
    */
    public static function update_message($features, $class = ''){
        if ( ! self::pro_is_installed() ) {
            include(self::plugin_path() .'/classes/views/shared/update_message.php');
        }
    }

    public static function pro_is_installed() {
        return apply_filters('frm_pro_installed', false);
    }

    /*
    * Check for certain page in Formidable settings
    *
    * @since 2.0
    *
    * @param $page string The name of the page to check
    * @return boolean
    */
    public static function is_admin_page($page = 'formidable') {
        global $pagenow;
        if ( $pagenow ) {
            return $pagenow == 'admin.php' && $_GET['page'] == $page;
        }

        return is_admin() && isset($_GET['page']) && $_GET['page'] == $page;
    }

    /*
    * Check for the form preview page
    *
    * @since 2.0
    *
    * @param None
    * @return boolean
    */
    public static function is_preview_page() {
        global $pagenow;
        return $pagenow && $pagenow == 'admin-ajax.php' && isset($_GET['action']) && $_GET['action'] == 'frm_forms_preview';
    }

    /*
    * Check for ajax except the form preview page
    *
    * @since 2.0
    *
    * @param None
    * @return boolean
    */
    public static function doing_ajax() {
        return defined('DOING_AJAX') && DOING_AJAX && ! self::is_preview_page();
    }

    /*
    * Check if on an admin page
    *
    * @since 2.0
    *
    * @param None
    * @return boolean
    */
    public static function is_admin() {
        return is_admin() && ( ! defined('DOING_AJAX') || ! DOING_AJAX );
    }

    /*
    * Check if value contains blank value or empty array
    *
    * @since 2.0
    * @param $value - value to check
    * @return true or false
    */
    public static function is_empty_value( $value, $empty = '' ) {
        return ( is_array( $value ) && empty( $value ) ) || $value == $empty;
    }

    /*
    * Get any value from the $_SERVER
    * Used by [ip] shortcode
    *
    * @since 2.0
    * @return string
    */
    public static function get_server_value($value) {
        return isset($_SERVER[$value]) ? $_SERVER[$value] : '';
    }

    /*
    * Check for the IP address in several places
    * @return string The IP address of the current user
    */
    public static function get_ip_address(){
        foreach ( array(
            'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'
        ) as $key ) {
            if ( ! array_key_exists($key, $_SERVER) ) {
                continue;
            }

            foreach ( explode(',', $_SERVER[$key]) as $ip ) {
                $ip = trim($ip); // just to be safe

                if ( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false ) {
                    return $ip;
                }
            }
        }
    }

    public static function get_param($param, $default='', $src='get'){
        if(strpos($param, '[')){
            $params = explode('[', $param);
            $param = $params[0];
        }

        if($src == 'get'){
            $value = (isset($_POST[$param]) ? stripslashes_deep($_POST[$param]) : (isset($_GET[$param]) ? stripslashes_deep($_GET[$param]) : $default));
            if ( ! isset($_POST[$param]) && isset($_GET[$param]) && ! is_array($value) ) {
                $value = stripslashes_deep(htmlspecialchars_decode(urldecode($_GET[$param])));
            }
        }else{
            $value = isset($_POST[$param]) ? stripslashes_deep(maybe_unserialize($_POST[$param])) : $default;
        }

        if ( isset($params) && is_array($value) && ! empty($value) ) {
            foreach($params as $k => $p){
                if ( ! $k || ! is_array($value) ) {
                    continue;
                }

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
    * @since 2.0
    */

    /**
     * @param string $action
     */
    public static function simple_get($action) {
        if ( $_GET && isset($_GET[$action]) ) {
            return $_GET[$action];
        } else {
            return '';
        }
    }

    /*
    * Used when switching the action for a bulk action
    * @since 2.0
    */
    public static function remove_get_action() {
        if ( ! isset($_GET) ) {
            return;
        }

        if ( isset($_GET['action']) ) {
            $_SERVER['REQUEST_URI'] = str_replace('&action='. $_GET['action'], '', $_SERVER['REQUEST_URI']);
        }

        if ( isset($_GET['action2']) ) {
            $_SERVER['REQUEST_URI'] = str_replace('&action='. $_GET['action2'], '', $_SERVER['REQUEST_URI']);
        }
    }

    /*
    * Check the WP query for a parameter
    *
    * @since 2.0
    * @return string|array
    */
    public static function get_query_var( $value, $param ) {
        if ( $value != '' ) {
            return $value;
        }

        global $wp_query;
        if ( isset($wp_query->query_vars[$param]) ) {
            $value = $wp_query->query_vars[$param];
        }

        return $value;
    }

    /**
     * @param string $type
     */
    public static function trigger_hook_load( $type, $object = null ) {
        // only load the form hooks once
        $hooks_loaded = apply_filters('frm_'. $type .'_hooks_loaded', false, $object);
        if ( ! $hooks_loaded ) {
            do_action('frm_load_'. $type .'_hooks');
        }
    }

    /*
    * Check cache before fetching values and saving to cache
    *
    * @since 2.0
    *
    * @param string $cache_key The unique name for this cache
    * @param string $group The name of the cache group
    * @param string $query If blank, don't run a db call
    * @param string $type The wpdb function to use with this query
    * @return mixed $results The cache or query results
    */
    public static function check_cache( $cache_key, $group = '', $query = '', $type = 'get_var', $time = 300 ) {
        $results = wp_cache_get($cache_key, $group);
        if ( ! self::is_empty_value( $results, false ) || empty($query) ) {
            return $results;
        }

        if ( 'get_posts' == $type ) {
            $results = get_posts($query);
        } else {
            global $wpdb;
            $results = $wpdb->{$type}($query);
        }

        wp_cache_set($cache_key, $results, $group, $time);

        return $results;
    }

    /*
    * Data that should be stored for a long time can be stored in a transient.
    * First check the cache, then check the transient
    * @since 2.0
    * @return mixed The cached value or false
    */
    public static function check_cache_and_transient($cache_key) {
        // check caching layer first
        $results = self::check_cache( $cache_key );
        if ( $results ) {
            return $results;
        }

        // then check the transient
        $results = get_transient($cache_key);
        if ( $results ) {
            wp_cache_set($cache_key, $results);
        }

        return $results;
    }

    /*
    * @since 2.0
    */

    /**
     * @param string $cache_key
     */
    public static function delete_cache_and_transient($cache_key) {
        delete_transient($cache_key);
        wp_cache_delete($cache_key);
    }

    /*
    * Delete all caching in a single group
    *
    * @since 2.0
    *
    * @param string $group The name of the cache group
    * @return boolean True or False
    */
    public static function cache_delete_group($group) {
    	global $wp_object_cache;

        if ( isset($wp_object_cache->cache[$group]) ) {
            foreach ( $wp_object_cache->cache[$group] as $k => $v ) {
                wp_cache_delete($k, $group);
            }
            return true;
        }

    	return false;
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

    /*
    * Used to filter shortcode in text widgets
    */
    public static function widget_text_filter_callback( $matches ) {
        return do_shortcode( $matches[0] );
    }

    public static function load_scripts($scripts){
        _deprecated_function( __FUNCTION__, '2.0', 'wp_enqueue_script' );
        foreach ( (array) $scripts as $s ) {
            wp_enqueue_script($s);
        }
    }

    public static function load_styles($styles){
        _deprecated_function( __FUNCTION__, '2.0', 'wp_enqueue_style' );
        foreach ( (array) $styles as $s ) {
            wp_enqueue_style($s);
        }
    }

    public static function get_pages(){
      return get_posts( array('post_type' => 'page', 'post_status' => array('publish', 'private'), 'numberposts' => 999, 'orderby' => 'title', 'order' => 'ASC'));
    }

    public static function wp_pages_dropdown($field_name, $page_id, $truncate=false){
        $pages = self::get_pages();
    ?>
        <select name="<?php echo esc_attr($field_name); ?>" id="<?php echo esc_attr($field_name); ?>" class="frm-pages-dropdown">
            <option value=""> </option>
            <?php foreach($pages as $page){ ?>
                <option value="<?php echo esc_attr($page->ID); ?>" <?php
                echo ( ( ( isset($_POST[$field_name]) && $_POST[$field_name] == $page->ID ) || ( ! isset($_POST[$field_name]) && $page_id == $page->ID ) ) ? ' selected="selected"' : '' );
                ?>><?php echo esc_html( $truncate ? self::truncate( $page->post_title, $truncate ) : $page->post_title ); ?> </option>
            <?php } ?>
        </select>
    <?php
    }

    public static function post_edit_link($post_id) {
        $post = get_post($post_id);
        if ( $post ) {
            return '<a href="'. esc_url(admin_url('post.php') .'?post='. $post_id .'&action=edit') .'">'. self::truncate($post->post_title, 50) .'</a>';
        }
        return '';
    }

    public static function wp_roles_dropdown($field_name, $capability, $multiple = 'single') {
        $capability = (array) self::get_param($field_name, $capability, 'post');

    ?>
        <select name="<?php echo esc_attr($field_name); ?>" id="<?php echo esc_attr($field_name); ?>" <?php
            echo ( 'multiple' == $multiple ) ? 'multiple="multiple"' : '';
            ?> class="frm_multiselect">
            <?php self::roles_options($capability); ?>
        </select>
    <?php
    }

    public static function roles_options($capability) {
        global $frm_vars;
        if ( isset($frm_vars['editable_roles']) ) {
            $editable_roles = $frm_vars['editable_roles'];
        } else {
            $editable_roles = get_editable_roles();
            $frm_vars['editable_roles'] = $editable_roles;
        }

        foreach ( $editable_roles as $role => $details ) {
            $name = translate_user_role($details['name'] ); ?>
        <option value="<?php echo esc_attr($role) ?>" <?php echo in_array($role, (array) $capability) ? ' selected="selected"' : ''; ?>><?php echo esc_attr($name) ?> </option>
<?php
            unset($role, $details);
        }
    }

    public static function frm_capabilities($type = 'auto') {
        $cap = array(
            'frm_view_forms'        => __('View Forms and Templates', 'formidable'),
            'frm_edit_forms'        => __('Add/Edit Forms and Templates', 'formidable'),
            'frm_delete_forms'      => __('Delete Forms and Templates', 'formidable'),
            'frm_change_settings'   => __('Access this Settings Page', 'formidable'),
            'frm_view_entries'      => __('View Entries from Admin Area', 'formidable'),
            'frm_delete_entries'    => __('Delete Entries from Admin Area', 'formidable'),
        );

        if ( ! self::pro_is_installed() && 'pro' != $type) {
            return $cap;
        }

        $cap['frm_create_entries'] = __('Add Entries from Admin Area', 'formidable');
        $cap['frm_edit_entries'] = __('Edit Entries from Admin Area', 'formidable');
        $cap['frm_view_reports'] = __('View Reports', 'formidable');
        $cap['frm_edit_displays'] = __('Add/Edit Views', 'formidable');

        return $cap;
    }

    public static function user_has_permission($needed_role){
        if($needed_role == '-1')
            return false;

        // $needed_role will be equal to blank if "Logged-in users" is selected
        if ( ( $needed_role == '' && is_user_logged_in() ) || current_user_can( $needed_role ) ) {
            return true;
        }

        $roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );
        foreach ($roles as $role){
        	if (current_user_can($role))
        		return true;
        	if ($role == $needed_role)
        		break;
        }
        return false;
    }

    /*
    * Make sure administrators can see Formidable menu
    *
    * @since 2.0
    */
    public static function maybe_add_permissions() {
        if ( ! current_user_can('administrator') || current_user_can('frm_view_forms') ) {
            return;
        }

        global $current_user;
        $frm_roles = self::frm_capabilities();
        foreach ( $frm_roles as $frm_role => $frm_role_description ) {
            $current_user->add_cap( $frm_role );
            unset($frm_role, $frm_role_description);
        }
        unset($frm_roles);
    }

    /*
    * Check if the user has permision for action.
    * Return permission message and stop the action if no permission
    * @since 2.0
    */

    /**
     * @param string $permission
     */
    public static function permission_check($permission, $show_message = 'show') {
        $permission_error = self::permission_nonce_error($permission);
        if ( $permission_error !== false ) {
            if ( 'hide' == $show_message ) {
                $permission_error = '';
            }
            wp_die($permission_error);
        }
    }

    /*
    * Check user permission and nonce
    * @since 2.0
    * @return false|string The permission message or false if allowed
    */
    public static function permission_nonce_error($permission, $nonce_name = '', $nonce = '') {
        $error = false;
        if ( ! empty($permission) && current_user_can($permission) ) {
            return $error;
        }

        if ( empty($nonce_name) ) {
            return $error;
        }

        if ( $_REQUEST && ( ! isset($_REQUEST[$nonce_name]) || ! wp_verify_nonce($_REQUEST[$nonce_name], $nonce) ) ) {
            $frm_settings = self::get_settings();
            $error = $frm_settings->admin_permission;
        }

        return $error;
    }

    public static function checked($values, $current){
        if(self::check_selected($values, $current))
            echo ' checked="checked"';
    }

    public static function check_selected($values, $current){
        //if(is_array($current))
        //    $current = (isset($current['value'])) ? $current['value'] : $current['label'];

        self::recursive_trim($values);
        $current = trim($current);

        /*if(is_array($values))
            $values = array_map('htmlentities', $values);
        else
             $values = htmlentities($values);

        $values = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $values);
        $current = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $current);
        */

        return ( is_array($values) && in_array($current, $values) ) || ( ! is_array($values) && $values == $current );
    }

    /**
    * Check if current field option is an "other" option
    *
    * @since 2.0
    *
    * @param $opt_key string
    * @return boolean true or false. Returns true if current field option is an "Other" option
    */
    public static function is_other_opt( $opt_key ) {
        return $opt_key && strpos( $opt_key, 'other' ) !== false;
    }

    /**
    * Get value that belongs in "Other" text box
    *
    * @since 2.0
    *
    * @param $opt_key string
    * @param $field array
    * @return $other_val string
    */
    public static function get_other_val( $opt_key, $field, $parent = false, $pointer = false ) {
        $other_val = '';

        //If option is an "other" option and there is a value set for this field, check if the value belongs in the current "Other" option text field
        if ( ! self::is_other_opt( $opt_key ) || ! isset( $field['value'] ) || ! $field['value'] ) {
            return $other_val;
        }

        // Check posted vals before checking saved values

        // For fields inside repeating sections - note, don't check if $pointer is true because it will often be zero
        if ( $parent && isset( $_POST['item_meta'][$parent][$pointer]['other'][$field['id']] ) ) {
            if ( FrmFieldsHelper::is_field_with_multiple_values( $field ) ) {
                $other_val = isset( $_POST['item_meta'][$parent][$pointer]['other'][$field['id']][$opt_key] ) ? $_POST['item_meta'][$parent][$pointer]['other'][$field['id']][$opt_key] : '';
            } else {
                $other_val = $_POST['item_meta'][$parent][$pointer]['other'][$field['id']];
            }
            return $other_val;

        // For normal fields
        } else if ( isset( $field['id'] ) && isset( $_POST['item_meta']['other'][$field['id']] ) ) {
            if ( FrmFieldsHelper::is_field_with_multiple_values( $field ) ) {
                $other_val = isset( $_POST['item_meta']['other'][$field['id']][$opt_key] ) ? $_POST['item_meta']['other'][$field['id']][$opt_key] : '';
            } else {
                $other_val = $_POST['item_meta']['other'][$field['id']];
            }
            return $other_val;
        }

        // For checkboxes
        if ( $field['type'] == 'checkbox' && is_array( $field['value'] ) ) {
             // Check if there is an "other" val in saved value and make sure the "other" val is not equal to the Other checkbox option
            if ( isset( $field['value'][$opt_key] ) && $field['options'][$opt_key] != $field['value'][$opt_key] ) {
                $other_val = $field['value'][$opt_key];
            }

        // For radio buttons and dropdowns
        } else {
        //Check if saved value equals any of the options. If not, set it as the other value.
            foreach ( $field['options'] as $opt_key => $opt_val ) {
                $temp_val = is_array( $opt_val ) ? $opt_val['value'] : $opt_val;
                // Multi-select dropdowns - key is not preserved
                if ( is_array( $field['value'] ) ) {
                    $o_key = array_search( $temp_val, $field['value'] );
                    if ( isset( $field['value'][$o_key] ) ) {
                        unset( $field['value'][$o_key], $o_key );
                    }

                // For radio and regular dropdowns
                } else if ( $temp_val == $field['value'] ) {
                    return '';
                } else {
                    $other_val = $field['value'];
                }
                unset($opt_key, $opt_val, $temp_val);
            }
            // For multi-select dropdowns only
            if ( is_array( $field['value'] ) && !empty( $field['value'] ) ) {
                $other_val = reset( $field['value'] );
            }
        }
        return $other_val;
    }

    /**
    * Check if there is a saved value for the "Other" text field. If so, set it as the $other_val.
    * Intended for front-end use
    *
    * @since 2.0
    *
    * @param $field array
    * @param $other_opt boolean
    * @param $checked string
    * @param $args array, should include opt_key and field name
    * @return $other_val string
    */
    public static function prepare_other_input( $field, &$other_opt, &$checked, $args = array() ){
        //Check if this is an "Other" option
        if ( !self::is_other_opt( $args['opt_key'] ) ) {
            return;
        }

        $other_opt = true;
        $other_args = array();
        $parent = $pointer = '';

        // Check for parent ID and pointer
        $temp_array = explode( '[', $args['field_name'] );
        // Count should only be greater than 3 if inside of a repeating section
        if ( count( $temp_array ) > 3 ) {
            $parent = str_replace( ']', '', $temp_array[1] );
            $pointer = str_replace( ']', '', $temp_array[2]);
        }
        unset( $temp_array );

        //Set up name for other field
        $other_args['name'] = str_replace( '[]', '', $args['field_name'] );
        $other_args['name'] = preg_replace('/\[' . $field['id'] . '\]$/', '', $other_args['name']);
        $other_args['name'] = $other_args['name'] . '[other]' . '[' . $field['id'] . ']';
        //Converts item_meta[field_id] => item_meta[other][field_id] and
        //item_meta[parent][0][field_id] => item_meta[parent][0][other][field_id]
        if ( FrmFieldsHelper::is_field_with_multiple_values( $field ) ) {
            $other_args['name'] .= '[' . $args['opt_key'] . ']';
        }

        // Get text for "other" text field
        $other_args['value'] = self::get_other_val( $args['opt_key'], $field, $parent, $pointer );

        if ( $other_args['value'] ) {
            $checked = 'checked="checked" ';
        }

        return $other_args;
    }

    public static function recursive_trim(&$value) {
        if ( is_array($value) ) {
            $value = array_map(array('FrmAppHelper', 'recursive_trim'), $value);
        } else {
            $value = trim($value);
        }

        return $value;
    }

    public static function esc_textarea( $text ) {
        $safe_text = str_replace('&quot;', '"', $text);
        $safe_text = htmlspecialchars( $safe_text, ENT_NOQUOTES );
    	return apply_filters( 'esc_textarea', $safe_text, $text );
    }

    /*
    * Add auto paragraphs to text areas
    * @since 2.0
    */
    public static function use_wpautop($content) {
        if ( apply_filters('frm_use_wpautop', true) ) {
            $content = wpautop(str_replace( '<br>', '<br />', $content));
        }
        return $content;
    }

    public static function replace_quotes($val){
        //Replace double quotes
        $val = str_replace( array( '&#8220;', '&#8221;', '&#8243;'), '"', $val);
        //Replace single quotes
        $val = str_replace( array( '&#8216;', '&#8217;', '&#8242;', '&prime;', '&rsquo;', '&lsquo;' ), "'", $val );
        return $val;
    }

    /*
    * @since 2.0
    * @return string The base Google APIS url for the current version of jQuery UI
    */
    public static function jquery_ui_base_url() {
        $url = 'http'. ( is_ssl() ? 's' : '' ) .'://ajax.googleapis.com/ajax/libs/jqueryui/'. self::script_version('jquery-ui-core');
        $url = apply_filters('frm_jquery_ui_base_url', $url);
        return $url;
    }

    /**
     * @param string $handle
     */
    public static function script_version($handle) {
        global $wp_scripts;
    	if ( ! $wp_scripts ) {
    	    return false;
    	}

        $ver = 0;

        if ( ! isset($wp_scripts->registered[$handle]) ) {
            return $ver;
        }

        $query = $wp_scripts->registered[$handle];
    	if ( is_object( $query ) ) {
    	    $ver = $query->ver;
    	}

    	return $ver;
    }

    public static function js_redirect($url){
        return '<script type="text/javascript">window.location="'. $url .'"</script>';
    }

    public static function get_user_id_param($user_id){
        if ( ! $user_id || empty($user_id) || is_numeric($user_id) ) {
            return $user_id;
        }

        if($user_id == 'current'){
            $user_ID = get_current_user_id();
            $user_id = $user_ID;
        }else{
            if ( is_email($user_id) ) {
                $user = get_user_by('email', $user_id);
            } else {
                $user = get_user_by('login', $user_id);
            }

            if ( $user ) {
                $user_id = $user->ID;
            }
            unset($user);
        }

        return $user_id;
    }

    public static function get_file_contents($filename, $atts = array()) {
        if ( ! is_file($filename) ) {
            return false;
        }

        extract($atts);
        ob_start();
        include($filename);
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    /**
     * @param string $table_name
     * @param string $column
     */
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

        if ( is_numeric($key) || in_array($key, array('id', 'key', 'created-at', 'detaillink', 'editlink', 'siteurl', 'evenodd')) ) {
            $key = $key .'a';
        }

        $query = "SELECT $column FROM $table_name WHERE $column = %s AND ID != %d LIMIT 1";
        $key_check = $wpdb->get_var($wpdb->prepare($query, $key, $id));

        if ( $key_check || is_numeric($key_check) ) {
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

    /*
    * Editing a Form or Entry
    * @return bool|array
    */

    /**
     * @param string $table
     */
    public static function setup_edit_vars($record, $table, $fields='', $default=false, $post_values=array()){
        if ( ! $record ) {
            return false;
        }

        global $frm_vars;

        if ( empty($post_values) ) {
            $post_values = stripslashes_deep($_POST);
        }

        $values = array('id' => $record->id, 'fields' => array());

        foreach ( array('name', 'description') as $var ) {
            $default_val = isset($record->{$var}) ? $record->{$var} : '';
            $values[$var] = self::get_param($var, $default_val);
            unset($var, $default_val);
        }

        $values['description'] = self::use_wpautop($values['description']);
        $frm_settings = self::get_settings();
        $is_form_builder = self::is_admin_page('formidable');

        foreach ( (array) $fields as $field ) {
            // Make sure to filter default values (for placeholder text), but not on the form builder page
            if ( ! $is_form_builder ) {
                $field->default_value = apply_filters('frm_get_default_value', $field->default_value, $field, true );
            }
            self::fill_field_defaults($field, $record, $values, compact('default', 'post_values', 'frm_settings'));
        }

        self::fill_form_opts($record, $table, $post_values, $values);

        if ( $table == 'entries' ) {
            $values = FrmEntriesHelper::setup_edit_vars( $values, $record );
        } else if ( $table == 'forms' ) {
            $values = FrmFormsHelper::setup_edit_vars( $values, $record, $post_values );
        }

        return $values;
    }

    private static function fill_field_defaults($field, $record, array &$values, $args) {
        $post_values = $args['post_values'];

        if ( $args['default'] ) {
            $meta_value = $field->default_value;
        } else {
            if ( $record->post_id && self::pro_is_installed() && isset($field->field_options['post_field']) && $field->field_options['post_field'] ) {
                if ( ! isset($field->field_options['custom_field']) ) {
                    $field->field_options['custom_field'] = '';
                }
                $meta_value = FrmProEntryMetaHelper::get_post_value($record->post_id, $field->field_options['post_field'], $field->field_options['custom_field'], array('truncate' => false, 'type' => $field->type, 'form_id' => $field->form_id, 'field' => $field));
            } else {
                $meta_value = self::get_meta_value($field->id, $record);
            }
        }

        $field_type = isset($post_values['field_options']['type_'.$field->id]) ? $post_values['field_options']['type_'.$field->id] : $field->type;
        $new_value = isset($post_values['item_meta'][$field->id]) ? maybe_unserialize($post_values['item_meta'][$field->id]) : $meta_value;

        $field_array = array(
            'id'            => $field->id,
            'value'         => $new_value,
            'default_value' => $field->default_value,
            'name'          => $field->name,
            'description'   => $field->description,
            'type'          => apply_filters('frm_field_type', $field_type, $field, $new_value),
            'options'       => $field->options,
            'required'      => $field->required,
            'field_key'     => $field->field_key,
            'field_order'   => $field->field_order,
            'form_id'       => $field->form_id
        );

        $args['field_type'] = $field_type;
        self::fill_field_opts($field, $field_array, $args);

        $field_array = apply_filters('frm_setup_edit_fields_vars', $field_array, $field, $values['id']);

        if ( ! isset($field_array['unique']) || ! $field_array['unique'] ) {
            $field_array['unique_msg'] = '';
        }

        foreach ( (array) $field->field_options as $k => $v ) {
            if ( ! isset($field_array[$k]) ) {
                $field_array[$k] = $v;
            }
            unset($k, $v);
        }

        $values['fields'][$field->id] = $field_array;
    }

    private static function fill_field_opts($field, array &$field_array, $args) {
        $post_values = $args['post_values'];
        $opt_defaults = FrmFieldsHelper::get_default_field_opts($field_array['type'], $field, true);

        foreach ( $opt_defaults as $opt => $default_opt ) {
            $field_array[$opt] = ( $post_values && isset($post_values['field_options'][$opt .'_'. $field->id]) ) ? maybe_unserialize($post_values['field_options'][$opt .'_'. $field->id]) : ( isset($field->field_options[$opt]) ? $field->field_options[$opt] : $default_opt );
            if ( $opt == 'blank' && $field_array[$opt] == '' ) {
                $field_array[$opt] = $args['frm_settings']->blank_msg;
            } else if ( $opt == 'invalid' && $field_array[$opt] == '' ) {
                if ( $args['field_type'] == 'captcha' ) {
                    $field_array[$opt] = $args['frm_settings']->re_msg;
                } else {
                    $field_array[$opt] = sprintf(__('%s is invalid', 'formidable'), $field_array['name']);
                }
            }
        }

        if ( $field_array['custom_html'] == '' ) {
            $field_array['custom_html'] = FrmFieldsHelper::get_default_html($args['field_type']);
        }

        $field_array['size'] = self::get_field_size($field_array);
    }

    private static function fill_form_opts($record, $table, $post_values, array &$values) {
        if ( $table == 'entries' ) {
            $form = $record->form_id;
            FrmFormsHelper::maybe_get_form( $form );
        } else {
            $form = $record;
        }

        if ( ! $form ) {
            return;
        }

        $values['form_name'] = isset($record->form_id) ? $form->name : '';
        if ( ! is_array($form->options) ) {
            return;
        }

        foreach ( $form->options as $opt => $value ) {
            $values[$opt] = isset($post_values[$opt]) ? maybe_unserialize($post_values[$opt]) : $value;
        }

        self::fill_form_defaults($post_values, $values);
    }

    /*
    * Set to POST value or default
    */
    private static function fill_form_defaults($post_values, array &$values) {
        $form_defaults = FrmFormsHelper::get_default_opts();

        foreach ( $form_defaults as $opt => $default ) {
            if ( ! isset($values[$opt]) || $values[$opt] == '' ) {
                if ( $opt == 'notification' ) {
                    $values[$opt] = ( $post_values && isset($post_values[$opt]) ) ? $post_values[$opt] : $default;

                    foreach ( $default as $o => $d ) {
                        if ( $o == 'email_to' ) {
                            $d = ''; //allow blank email address
                        }
                        $values[$opt][0][$o] = ( $post_values && isset($post_values[$opt][0][$o]) ) ? $post_values[$opt][0][$o] : $d;
                        unset($o, $d);
                    }
                } else {
                    $values[$opt] = ( $post_values && isset($post_values['options'][$opt]) ) ? $post_values['options'][$opt] : $default;
                }
            } else if ( $values[$opt] == 'notification' ) {
                foreach ( $values[$opt] as $k => $n ) {
                    foreach ( $default as $o => $d ) {
                        if ( ! isset($n[$o]) ) {
                            $values[$opt][$k][$o] = ( $post_values && isset($post_values[$opt][$k][$o])) ? $post_values[$opt][$k][$o] : $d;
                        }
                        unset($o, $d);
                    }
                    unset($k, $n);
                }
            }

            unset($opt, $defaut);
        }

        if ( ! isset($values['custom_style']) ) {
            $frm_settings = self::get_settings();
            $values['custom_style'] = ( $post_values && isset($post_values['options']['custom_style']) ) ? $_POST['options']['custom_style'] : ( $frm_settings->load_style != 'none' );
        }

        foreach ( array('before', 'after', 'submit') as $h ) {
            if ( ! isset($values[$h .'_html']) ) {
                $values[$h .'_html'] = (isset($post_values['options'][$h .'_html']) ? $post_values['options'][$h .'_html'] : FrmFormsHelper::get_default_html($h));
            }
            unset($h);
        }
    }

    /**
     * @return string
     */
    public static function get_meta_value($field_id, $entry) {
        if ( isset($entry->metas) ) {
            return isset($entry->metas[$field_id]) ? $entry->metas[$field_id] : false;
        } else {
            return FrmEntryMeta::get_entry_meta_by_field($entry->id, $field_id);
        }
    }

    /*
    * @since 2.0
    * @return string
    */
    public static function get_field_size($field) {
        if ( '' == $field['size'] ) {
            global $frm_vars;
            $field['size'] = isset($frm_vars['sidebar_width']) ? $frm_vars['sidebar_width'] : '';
        }
        return $field['size'];
    }

    public static function insert_opt_html($args) {
        $class = '';
        if ( in_array( $args['type'], array( 'email', 'user_id', 'hidden', 'select', 'radio', 'checkbox', 'phone', 'text' ) ) ) {
            $class .= 'show_frm_not_email_to';
        }
    ?>
<li>
    <a href="javascript:void(0)" class="frmids frm_insert_code alignright <?php echo esc_attr($class) ?>" data-code="<?php echo esc_attr($args['id']) ?>" >[<?php echo esc_attr( $args['id'] ) ?>]</a>
    <a href="javascript:void(0)" class="frmkeys frm_insert_code alignright <?php echo esc_attr($class) ?>" data-code="<?php echo esc_attr($args['key']) ?>" >[<?php echo esc_attr( self::truncate($args['key'], 10) ) ?>]</a>
    <a href="javascript:void(0)" class="frm_insert_code <?php echo esc_attr( $class ) ?>" data-code="<?php echo esc_attr($args['id']) ?>" ><?php echo esc_attr( self::truncate($args['name'], 60) ) ?></a>
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
            __('South Africa', 'formidable'), __('South Sudan', 'formidable'),
            __('Spain', 'formidable'), __('Sri Lanka', 'formidable'),
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

        $length = (int) $length;
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

    public static function get_formatted_time($date, $date_format = '', $time_format = '' ) {
        if ( empty($date) ) {
            return $date;
        }

        if ( empty($date_format) ) {
            $date_format = get_option('date_format');
        }

        if ( preg_match('/^\d{1-2}\/\d{1-2}\/\d{4}$/', $date) && self::pro_is_installed() ) {
            $frmpro_settings = new FrmProSettings();
            $date = FrmProAppHelper::convert_date($date, $frmpro_settings->date_format, 'Y-m-d');
        }

        $do_time = ( date('H:i:s', strtotime($date)) == '00:00:00' ) ? false : true;

        $date = get_date_from_gmt($date);

        $formatted = date_i18n($date_format, strtotime($date));

        if ( $do_time ) {

            if ( empty($time_format) ) {
                $time_format = get_option('time_format');
            }

            $trimmed_format = trim($time_format);
            if ( $time_format && ! empty($trimmed_format) ) {
                $formatted .= ' '. __('at', 'formidable') .' '. date_i18n($time_format, strtotime($date));
            }
        }

        return $formatted;
    }

    /*
    * @returns string The time ago in words
    */
    public static function human_time_diff( $from, $to = '' ) {
    	if ( empty($to) )
    		$to = time();

    	// Array of time period chunks
    	$chunks = array(
    		array( 60 * 60 * 24 * 365 , __( 'year', 'formidable' ), __( 'years', 'formidable' ) ),
    		array( 60 * 60 * 24 * 30 , __( 'month', 'formidable' ), __( 'months', 'formidable' ) ),
    		array( 60 * 60 * 24 * 7, __( 'week', 'formidable' ), __( 'weeks', 'formidable' ) ),
    		array( 60 * 60 * 24 , __( 'day', 'formidable' ), __( 'days', 'formidable' ) ),
    		array( 60 * 60 , __( 'hour', 'formidable' ), __( 'hours', 'formidable' ) ),
    		array( 60 , __( 'minute', 'formidable' ), __( 'minutes', 'formidable' ) ),
    		array( 1, __( 'second', 'formidable' ), __( 'seconds', 'formidable' ) )
    	);

    	// Difference in seconds
    	$diff = (int) ($to - $from);

    	// Something went wrong with date calculation and we ended up with a negative date.
    	if ( $diff < 1)
    		return '0 ' . __( 'seconds', 'formidable' );

    	/**
    	 * We only want to output one chunks of time here, eg:
    	 * x years
    	 * xx months
    	 * so there's only one bit of calculation below:
    	 */

        $count = 0;

    	//Step one: the first chunk
    	for ( $i = 0, $j = count($chunks); $i < $j; $i++) {
    		$seconds = $chunks[$i][0];

    		// Finding the biggest chunk (if the chunk fits, break)
    		if ( ( $count = floor($diff / $seconds) ) != 0 )
    			break;
    	}

    	// Set output var
    	$output = ( 1 == $count ) ? '1 '. $chunks[$i][1] : $count . ' ' . $chunks[$i][2];


    	if ( !(int)trim($output) )
    		$output = '0 ' . __( 'seconds', 'formidable' );

    	return $output;
    }

    /*
    * Added for < WP 4.0 compatability
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

    /**
     * @param string $order_query
     */
    public static function esc_order($order_query) {
        if ( empty($order_query) ) {
            return '';
        }

        // remove ORDER BY before santizing
        $order_query = strtolower($order_query);
        if ( strpos($order_query, 'order by') !== false ) {
            $order_query = str_replace('order by', '', $order_query);
        }

        $order_query = explode(' ', trim($order_query));

        $order_fields = array(
            'id', 'form_key', 'name', 'description',
            'parent_form_id', 'logged_in', 'is_template',
            'default_template', 'status', 'created_at',
        );

        $order = trim(trim(reset($order_query), ','));
        if ( ! in_array($order, $order_fields) ) {
            return '';
        }

        $order_by = '';
        if ( count($order_query) > 1 ) {
            $sort_options = array('asc', 'desc');
            $order_by = end($order_query);
            if ( ! in_array($order_by, $sort_options) ) {
                $order_by = 'asc';
            }
        }

        return ' ORDER BY '. $order . ' '. $order_by;
    }

    /**
     * @param string $limit
     */
    public static function esc_limit($limit) {
        if ( empty($limit) ) {
            return '';
        }

        $limit = trim(str_replace(' limit', '', strtolower($limit)));
        if ( is_numeric($limit) ) {
            return ' LIMIT '. $limit;
        }

        $limit = explode(',', trim($limit));
        foreach ( $limit as $k => $l ) {
            if ( is_numeric($l) ) {
                $limit[$k] = $l;
            }
        }

        $limit = implode(',', $limit);
        return ' LIMIT '. $limit;
    }

    public static function prepend_and_or_where( $starts_with = ' WHERE ', $where = '' ){
        if ( empty($where) ) {
            return '';
        }

        if(is_array($where)){
            global $wpdb;
            $where = self::get_where_clause_and_values( $where );
            $where = $wpdb->prepare($where['where'], $where['values']);
        }else{
            $where = $starts_with . $where;
        }

        return $where;
    }

    /*
    * Change array into format $wpdb->prepare can use
    */
    public static function get_where_clause_and_values( $args ) {
        $where = '';
        $values = array();
        if ( is_array($args) ) {
            foreach ( $args as $key => $value ) {
                $where .= empty($where) ? ' WHERE' : ' AND';
                $where .= ' '. $key .'=';
                $where .= is_numeric($value) ? ( strpos($value, '.') !== false ? '%f' : '%d' ) : '%s';

                $values[] = $value;
            }
        }

        return compact('where', 'values');
    }

    // Pagination Methods
    public static function getLastRecordNum($r_count,$current_p,$p_size){
      return (($r_count < ($current_p * $p_size))?$r_count:($current_p * $p_size));
    }

    /**
     * @param integer $current_p
     */
    public static function getFirstRecordNum($r_count,$current_p,$p_size){
      if($current_p == 1)
        return 1;
      else
        return (self::getLastRecordNum($r_count,($current_p - 1),$p_size) + 1);
    }

    /**
     * @param string $table_name
     */
    public static function &getRecordCount($where = '', $table_name) {
        $cache_key = 'count_'. $table_name .'_'. maybe_serialize($where);
        $query = 'SELECT COUNT(*) FROM ' . $table_name . self::prepend_and_or_where(' WHERE ', $where);

        $count = self::check_cache($cache_key, 'formidable', $query, 'get_var');

        return $count;
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
    	if ( isset($_SESSION) && isset($_SESSION['frm_http_referer']) && $_SESSION['frm_http_referer'] ) {
        	foreach ($_SESSION['frm_http_referer'] as $referer) {
        		$referrerinfo .= str_pad("Referer $i: ",20) . $referer. "\r\n";
        		$keywords_used = self::get_referer_query($referer);
        		if ( $keywords_used !== false ) {
        			$keywords[] = $keywords_used;
                }

        		$i++;
        	}

        	$referrerinfo .= "\r\n";
	    }else{
	        $referrerinfo = self::get_server_value('HTTP_REFERER');
	    }

    	$i = 1;
    	if ( isset($_SESSION) && isset($_SESSION['frm_http_pages']) && $_SESSION['frm_http_pages'] ) {
        	foreach ( $_SESSION['frm_http_pages'] as $page ) {
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
        foreach ( $json_vars as $jv ) {
            $jv_name = explode('[', $jv['name']);
            $last = count($jv_name) - 1;
            foreach ( $jv_name as $p => $n ) {
                $name = trim($n, ']');
                if ( ! isset($l1) ) {
                    $l1 = $name;
                }

                if ( ! isset($l2) ) {
                    $l2 = $name;
                }

                if ( ! isset($l3) ) {
                    $l3 = $name;
                }

                $this_val = ( $p == $last ) ? $jv['value'] : array();

                switch ( $p ) {
                    case 0:
                        $l1 = $name;
                        if ( $name == '' ) {
                            $vars[] = $this_val;
                        } else if ( ! isset($vars[$l1]) ) {
                            $vars[$l1] = $this_val;
                        }
                    break;

                    case 1:
                        $l2 = $name;
                        if ( $name == '' ) {
                            $vars[$l1][] = $this_val;
                        } else if ( ! isset($vars[$l1][$l2]) ) {
                            $vars[$l1][$l2] = $this_val;
                        }
                    break;

                    case 2:
                        $l3 = $name;
                        if ( $name == '' ) {
                            $vars[$l1][$l2][] = $this_val;
                        } else if ( ! isset($vars[$l1][$l2][$l3]) ) {
                            $vars[$l1][$l2][$l3] = $this_val;
                        }
                    break;

                    case 3:
                        $l4 = $name;
                        if ( $name == '' ) {
                            $vars[$l1][$l2][$l3][] = $this_val;
                        } else if ( ! isset($vars[$l1][$l2][$l3][$l4] ) ) {
                            $vars[$l1][$l2][$l3][$l4] = $this_val;
                        }
                    break;
                }

                unset($this_val, $n);
            }

            unset($last, $jv);
        }

        return $vars;
    }

    public static function maybe_add_tooltip($name, $class = 'closed', $form_name = '') {
        $tooltips = array(
            'action_title'  => __('Give this action a label for easy reference.', 'formidable'),
            'email_to'      => __('Add one or more recipient addresses separated by a ",".  FORMAT: Name <name@email.com> or name@email.com.  [admin_email] is the address set in WP General Settings.', 'formidable'),
            'cc'            => __('Add CC addresses separated by a ",".  FORMAT: Name <name@email.com> or name@email.com.', 'formidable'),
            'bcc'           => __('Add BCC addresses separated by a ",".  FORMAT: Name <name@email.com> or name@email.com.', 'formidable'),
            'reply_to'      => __('If you would like a different reply to address than the "from" address, add a single address here.  FORMAT: Name <name@email.com> or name@email.com.', 'formidable'),
            'from'          => __('Enter the name and/or email address of the sender. FORMAT: John Bates <john@example.com> or john@example.com.', 'formidable'),
            'email_subject' => esc_attr( sprintf( __('If you leave the subject blank, the default will be used: %1$s Form submitted on %2$s', 'formidable'), $form_name, self::site_name() ) ),
        );

        if ( ! isset( $tooltips[ $name ] ) ) {
            return;
        }

        if ( 'open' == $class ) {
            echo ' frm_help"';
        } else {
            echo ' class="frm_help"';
        }

        echo ' title="'. esc_attr($tooltips[$name]);

        if ( 'open' != $class ) {
            echo '"';
        }
    }

    /* Prepare and json_encode post content
    *
    * Since 2.0
    *
    * @param $post_content array
    * @return $post_content string ( json encoded array )
    */
    public static function prepare_and_encode( $post_content ) {

        //Loop through array to strip slashes and add only the needed ones
        foreach( $post_content as $key => $val ) {
            if ( isset( $post_content[ $key ] ) && ! is_array( $val ) ) {
                // Strip all slashes so everything is the same, no matter where the value is coming from
                $val = stripslashes( $val );

                // Add backslashes before double quotes and forward slashes only
                $post_content[$key] = addcslashes( $val, '"\\/' );
            }
            unset( $key, $val );
        }

        // json_encode the array
        $post_content = json_encode( $post_content );

	    // add extra slashes for \r\n since WP strips them
	    $post_content = str_replace( array('\\r', '\\n', '\\u'), array('\\\\r', '\\\\n', '\\\\u'), $post_content );

        // allow for &quot
	    $post_content = str_replace( '&quot;', '\\"', $post_content );

        return $post_content;
    }

    public static function maybe_json_decode($string){
        if ( is_array($string) ) {
            return $string;
        }

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

    public static function locales( $type = 'date' ) {
        $locales = array(
            'en' => __('English', 'formidable'),    '' => __('English/Western', 'formidable'),
            'af' => __('Afrikaans', 'formidable'),  'sq' => __('Albanian', 'formidable'),
            'ar' => __('Arabic', 'formidable'),     'hy' => __('Armenian', 'formidable'),
            'az' => __('Azerbaijani', 'formidable'), 'eu' => __('Basque', 'formidable'),
            'bs' => __('Bosnian', 'formidable'),    'bg' => __('Bulgarian', 'formidable'),
            'ca' => __('Catalan', 'formidable'),    'zh-HK' => __('Chinese Hong Kong', 'formidable'),
            'zh-CN' => __('Chinese Simplified', 'formidable'), 'zh-TW' => __('Chinese Traditional', 'formidable'),
            'hr' => __('Croatian', 'formidable'),   'cs' => __('Czech', 'formidable'),
            'da' => __('Danish', 'formidable'),     'nl' => __('Dutch', 'formidable'),
            'en-GB' => __('English/UK', 'formidable'), 'eo' => __('Esperanto', 'formidable'),
            'et' => __('Estonian', 'formidable'),   'fo' => __('Faroese', 'formidable'),
            'fa' => __('Farsi/Persian', 'formidable'), 'fil' => __('Filipino', 'formidable'),
            'fi' => __('Finnish', 'formidable'),    'fr' => __('French', 'formidable'),
            'fr-CA' => __('French/Canadian', 'formidable'), 'fr-CH' => __('French/Swiss', 'formidable'),
            'de' => __('German', 'formidable'),     'de-AT' => __('German/Austria', 'formidable'),
            'de-CH' => __('German/Switzerland', 'formidable'), 'el' => __('Greek', 'formidable'),
            'he' => __('Hebrew', 'formidable'),     'iw' => __('Hebrew', 'formidable'),
            'hi' => __('Hindi', 'formidable'),      'hu' => __('Hungarian', 'formidable'),
            'is' => __('Icelandic', 'formidable'),  'id' => __('Indonesian', 'formidable'),
            'it' => __('Italian', 'formidable'),    'ja' => __('Japanese', 'formidable'),
            'ko' => __('Korean', 'formidable'),     'lv' => __('Latvian', 'formidable'),
            'lt' => __('Lithuanian', 'formidable'), 'ms' => __('Malaysian', 'formidable'),
            'no' => __('Norwegian', 'formidable'),  'pl' => __('Polish', 'formidable'),
            'pt' => __('Portuguese', 'formidable'), 'pt-BR' => __('Portuguese/Brazilian', 'formidable'),
            'pt-PT' => __('Portuguese/Portugal', 'formidable'), 'ro' => __('Romanian', 'formidable'),
            'ru' => __('Russian', 'formidable'),    'sr' => __('Serbian', 'formidable'),
            'sr-SR' => __('Serbian', 'formidable'), 'sk' => __('Slovak', 'formidable'),
            'sl' => __('Slovenian', 'formidable'),  'es' => __('Spanish', 'formidable'),
            'es-419' => __('Spanish/Latin America', 'formidable'), 'sv' => __('Swedish', 'formidable'),
            'ta' => __('Tamil', 'formidable'),      'th' => __('Thai', 'formidable'),
            'tu' => __('Turkish', 'formidable'),    'tr' => __('Turkish', 'formidable'),
            'uk' => __('Ukranian', 'formidable'),   'vi' => __('Vietnamese', 'formidable'),
        );

        if ( $type == 'captcha' ) {
            // remove the languages unavailable for the captcha
            $unset = array(
                '', 'af', 'sq', 'hy', 'az', 'eu', 'bs',
                'zh-HK', 'eo', 'et', 'fo', 'fr-CH',
                'he', 'is', 'ms', 'sr-SR', 'ta', 'tu',
            );
        } else {
            // remove the languages unavailable for the datepicker
            $unset = array(
                'en', 'fil', 'fr-CA', 'de-AT', 'de-AT',
                'de-CH', 'iw', 'hi', 'id', 'pt', 'pt-PT',
                'es-419', 'tr',
            );
        }

        $locales = array_diff_key($locales, array_flip($unset));
        $locales = apply_filters('frm_locales', $locales);

        return $locales;
    }
}
