<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmDeprecated
 *
 * @since 3.04.03
 * @codeCoverageIgnore
 */
class FrmDeprecated {

	/**
	 * @deprecated 2.3
	 */
	public static function deprecated( $function, $version ) {
		_deprecated_function( $function, $version );
	}

	/**
	 * @deprecated 4.0
	 */
	public static function new_form( $values = array() ) {
		_deprecated_function( __FUNCTION__, '4.0', 'FrmFormsController::edit' );

		FrmAppHelper::permission_check( 'frm_edit_forms' );

		$action = isset( $_REQUEST['frm_action'] ) ? 'frm_action' : 'action';
		$action = empty( $values ) ? FrmAppHelper::get_param( $action, '', 'get', 'sanitize_title' ) : $values[ $action ];

		if ( $action === 'create' ) {
			FrmFormsController::update( $values );
			return;
		}

		$values = FrmFormsHelper::setup_new_vars( $values );
		$id   = FrmForm::create( $values );
		$values['id'] = $id;

		FrmFormsController::edit( $values );
	}

	/**
	 * @deprecated 4.0
	 */
	public static function update_order() {
		_deprecated_function( __FUNCTION__, '4.0' );
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$fields = FrmAppHelper::get_post_param( 'frm_field_id' );
		foreach ( (array) $fields as $position => $item ) {
			FrmField::update( absint( $item ), array( 'field_order' => absint( $position ) ) );
		}
		wp_die();
	}

	/**
	 * @deprecated 4.0
	 * @param array $values - The form array
	 */
	public static function builder_submit_button( $values ) {
		_deprecated_function( __FUNCTION__, '4.0' );
		$page_action = FrmAppHelper::get_param( 'frm_action' );
		$label = ( $page_action == 'edit' || $page_action == 'update' ) ? __( 'Update', 'formidable' ) : __( 'Create', 'formidable' );

		?>
		<div class="postbox">
			<p class="inside">
				<button class="frm_submit_<?php echo esc_attr( ( isset( $values['ajax_load'] ) && $values['ajax_load'] ) ? '' : 'no_' ); ?>ajax button-primary frm_button_submit" type="button">
					<?php echo esc_html( $label ); ?>
				</button>
			</p>
		</div>
		<?php
	}

	/**
	 * @deprecated 3.04.03
	 */
	public static function get_licenses() {
		_deprecated_function( __FUNCTION__, '3.04.03' );

		$allow_autofill = self::allow_autofill();
		$required_role = $allow_autofill ? 'setup_network' : 'frm_change_settings';
		FrmAppHelper::permission_check( $required_role );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		if ( is_multisite() && get_site_option( 'frmpro-wpmu-sitewide' ) ) {
			$license = get_site_option( 'frmpro-credentials' );
		} else {
			$license = get_option( 'frmpro-credentials' );
		}

		if ( $license && is_array( $license ) && isset( $license['license'] ) ) {
			$url = 'https://formidableforms.com/frm-edd-api/licenses?l=' . urlencode( base64_encode( $license['license'] ) );
			$licenses = self::send_api_request(
				$url,
				array(
					'name'    => 'frm_api_licence',
					'expires' => 60 * 60 * 5,
				)
			);
			echo json_encode( $licenses );
		}

		wp_die();
	}


	/**
	 * Don't allow subsite addon licenses to be fetched
	 * unless the current user has super admin permissions
	 *
	 * @since 2.03.10
	 * @deprecated 3.04.03
	 */
	private static function allow_autofill() {
		$allow_autofill = FrmAppHelper::pro_is_installed();
		if ( $allow_autofill && is_multisite() ) {
			$sitewide_activated = get_site_option( 'frmpro-wpmu-sitewide' );
			if ( $sitewide_activated ) {
				$allow_autofill = current_user_can( 'setup_network' );
			}
		}
		return $allow_autofill;
	}

	/**
	 * @deprecated 3.04.03
	 */
	private static function send_api_request( $url, $transient = array() ) {
		$data = get_transient( $transient['name'] );
		if ( $data !== false ) {
			return $data;
		}

		$arg_array = array(
			'body'      => array(
				'url'   => home_url(),
			),
			'timeout'   => 15,
			'user-agent' => 'Formidable/' . FrmAppHelper::$plug_version . '; ' . home_url(),
		);

		$response = wp_remote_post( $url, $arg_array );
		$body = wp_remote_retrieve_body( $response );
		$data = false;
		if ( ! is_wp_error( $response ) && ! is_wp_error( $body ) ) {
			$data = json_decode( $body, true );
			set_transient( $transient['name'], $data, $transient['expires'] );
		}

		return $data;
	}

	/**
	 * @since 3.04.03
	 * @deprecated 3.06
	 * @codeCoverageIgnore
	 */
	public static function get_pro_updater() {
		_deprecated_function( __FUNCTION__, '3.06', 'FrmFormApi::get_pro_updater' );
		$api = new FrmFormApi();
		return $api->get_pro_updater();
	}

	/**
	 * @since 4.06.02
	 * @deprecated 4.09.01
	 * @codeCoverageIgnore
	 */
	public static function ajax_multiple_addons() {
		_deprecated_function( __FUNCTION__, '4.09.01', 'FrmProAddonsController::' . __METHOD__ );
		echo json_encode( __( 'Your plugin has been not been installed. Please update Formidable Pro to get downloads.', 'formidable' ) );
		wp_die();
	}

	/**
	 * @since 3.04.03
	 * @deprecated 3.06
	 * @codeCoverageIgnore
	 * @return array
	 */
	public static function error_for_license( $license ) {
		_deprecated_function( __FUNCTION__, '3.06', 'FrmFormApi::error_for_license' );
		$api = new FrmFormApi( $license );
		return $api->error_for_license();
	}

	/**
	 * @since 3.04.03
	 * @deprecated 3.06
	 */
	public static function reset_cached_addons( $license = '' ) {
		_deprecated_function( __FUNCTION__, '3.06', 'FrmFormApi::reset_cached' );
		$api = new FrmFormApi( $license );
		$api->reset_cached();
	}

	/**
	 * @since 3.04.03
	 * @deprecated 3.06
	 * @return string
	 */
	public static function get_cache_key( $license ) {
		_deprecated_function( __FUNCTION__, '3.06', 'FrmFormApi::get_cache_key' );
		$api = new FrmFormApi( $license );
		return $api->get_cache_key();
	}

	/**
	 * @since 3.04.03
	 * @deprecated 3.06
	 * @codeCoverageIgnore
	 * @return array
	 */
	public static function get_addon_info( $license = '' ) {
		_deprecated_function( __FUNCTION__, '3.06', 'FrmFormApi::get_api_info' );
		$api = new FrmFormApi( $license );
		return $api->get_api_info();
	}

	/**
	 * Add a filter to shorten the EDD filename for Formidable plugin, and add-on, updates
	 *
	 * @since 2.03.08
	 * @deprecated 3.04.03
	 *
	 * @param boolean $return
	 * @param string $package
	 *
	 * @return boolean
	 */
	public static function add_shorten_edd_filename_filter( $return, $package ) {
		_deprecated_function( __FUNCTION__, '3.04.03' );

		if ( strpos( $package, '/edd-sl/package_download/' ) !== false && strpos( $package, 'formidableforms.com' ) !== false ) {
			add_filter( 'wp_unique_filename', 'FrmDeprecated::shorten_edd_filename', 10, 2 );
		}

		return $return;
	}

	/**
	 * Shorten the EDD filename for automatic updates
	 * Decreases size of file path so file path limit is not hit on Windows servers
	 *
	 * @since 2.03.08
	 * @deprecated 3.04.03
	 *
	 * @param string $filename
	 * @param string $ext
	 *
	 * @return string
	 */
	public static function shorten_edd_filename( $filename, $ext ) {
		_deprecated_function( __FUNCTION__, '3.04.03' );

		$filename = substr( $filename, 0, 50 ) . $ext;
		remove_filter( 'wp_unique_filename', 'FrmDeprecated::shorten_edd_filename', 10 );

		return $filename;
	}

	/**
	 * Filter shortcodes in text widgets
	 *
	 * @deprecated 2.5.4
	 */
	public static function widget_text_filter( $content ) {
		_deprecated_function( __FUNCTION__, '2.5.4' );
		$regex = '/\[\s*(formidable|display-frm-data|frm-stats|frm-graph|frm-entry-links|formresults|frm-search)\s+.*\]/';
		return preg_replace_callback( $regex, 'FrmAppHelper::widget_text_filter_callback', $content );
	}

	/**
	 * Used to filter shortcode in text widgets
	 *
	 * @deprecated 2.5.4
	 */
	public static function widget_text_filter_callback( $matches ) {
		_deprecated_function( __FUNCTION__, '2.5.4' );
		return do_shortcode( $matches[0] );
	}

	/**
	 * Deprecated in favor of wpmu_upgrade_site
	 *
	 * @deprecated 2.3
	 */
	public static function front_head() {
		_deprecated_function( __FUNCTION__, '2.3' );
		if ( is_multisite() && FrmAppController::needs_update() ) {
			FrmAppController::install();
		}
	}

	/**
	 * @deprecated 3.0.04
	 */
	public static function activation_install() {
		_deprecated_function( __FUNCTION__, '3.0.04', 'FrmAppController::install' );
		FrmDb::delete_cache_and_transient( 'frm_plugin_version' );
		FrmFormActionsController::actions_init();
		FrmAppController::install();
	}

	/**
	 * Routes for wordpress pages -- we're just replacing content
	 *
	 * @deprecated 3.0
	 */
	public static function page_route( $content ) {
		_deprecated_function( __FUNCTION__, '3.0' );
		global $post;

		if ( $post && isset( $_GET['form'] ) ) {
			$content = FrmFormsController::page_preview();
		}

		return $content;
	}

	/**
	 * @deprecated 1.07.05
	 */
	public static function get_form_shortcode( $atts ) {
		_deprecated_function( __FUNCTION__, '1.07.05', 'FrmFormsController::get_form_shortcode()' );
		return FrmFormsController::get_form_shortcode( $atts );
	}

	/**
	 * @deprecated 1.07.05
	 */
	public static function show_form( $id = '', $key = '', $title = false, $description = false ) {
		_deprecated_function( __FUNCTION__, '1.07.05', 'FrmFormsController::show_form()' );
		return FrmFormsController::show_form( $id, $key, $title, $description );
	}

	/**
	 * @deprecated 1.07.05
	 */
	public static function get_form( $filename, $form, $title, $description ) {
		_deprecated_function( __FUNCTION__, '1.07.05', 'FrmFormsController::get_form()' );
		return FrmFormsController::get_form( $form, $title, $description );
	}

	/**
	 * @deprecated 3.0
	 */
	public static function edit_name( $field = 'name', $id = '' ) {
		_deprecated_function( __FUNCTION__, '3.0' );

		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		if ( empty( $field ) ) {
			$field = 'name';
		}

		if ( empty( $id ) ) {
			$id = FrmAppHelper::get_post_param( 'element_id', '', 'sanitize_title' );
			$id = str_replace( 'field_label_', '', $id );
		}

		$value = FrmAppHelper::get_post_param( 'update_value', '', 'wp_kses_post' );
		$value = trim( $value );
		if ( trim( strip_tags( $value ) ) === '' ) {
			// set blank value if there is no content
			$value = '';
		}

		FrmField::update( $id, array( $field => $value ) );

		do_action( 'frm_after_update_field_' . $field, compact( 'id', 'value' ) );

		echo stripslashes( wp_kses_post( $value ) );
		wp_die();
	}

	/**
	 * Load a single field in the form builder along with all needed variables
	 *
	 * @deprecated 3.0
	 *
	 * @param int $field_id
	 * @param array $values
	 * @param int $form_id
	 *
	 * @return array
	 */
	public static function include_single_field( $field_id, $values, $form_id = 0 ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldsController::load_single_field' );

		$field = FrmFieldsHelper::setup_edit_vars( FrmField::getOne( $field_id ) );
		FrmFieldsController::load_single_field( $field, $values, $form_id );

		return $field;
	}

	/**
	 * @deprecated 3.0
	 */
	public static function bulk_create_template( $ids ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmForm::duplicate( $id, true, true )' );
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		foreach ( $ids as $id ) {
			FrmForm::duplicate( $id, true, true );
		}

		return __( 'Form template was Successfully Created', 'formidable' );
	}

	/**
	 * @deprecated 2.03
	 */
	public static function register_pro_scripts() {
		_deprecated_function( __FUNCTION__, '2.03', 'FrmProEntriesController::register_scripts' );
		if ( FrmAppHelper::pro_is_installed() ) {
			FrmProEntriesController::register_scripts();
		}
	}

	/**
	 * @deprecated 3.0
	 */
	public static function edit_key() {
		_deprecated_function( __FUNCTION__, '3.0' );
		$values = self::edit_in_place_value( 'form_key' );
		echo wp_kses( stripslashes( FrmForm::get_key_by_id( $values['form_id'] ) ), array() );
		wp_die();
	}

	/**
	 * @deprecated 3.0
	 */
	public static function edit_description() {
		_deprecated_function( __FUNCTION__, '3.0' );
		$values = self::edit_in_place_value( 'description' );
		echo wp_kses_post( FrmAppHelper::use_wpautop( stripslashes( $values['description'] ) ) );
		wp_die();
	}

	/**
	 * @deprecated 3.0
	 */
	private static function edit_in_place_value( $field ) {
		_deprecated_function( __FUNCTION__, '3.0' );
		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check( 'frm_edit_forms', 'hide' );

		$form_id = FrmAppHelper::get_post_param( 'form_id', '', 'absint' );
		$value = FrmAppHelper::get_post_param( 'update_value', '', 'wp_filter_post_kses' );

		$values = array( $field => trim( $value ) );
		FrmForm::update( $form_id, $values );
		$values['form_id'] = $form_id;

		return $values;
	}

	/**
	 * @deprecated 1.07.05
	 */
	public static function add_default_templates( $path, $default = true, $template = true ) {
		_deprecated_function( __FUNCTION__, '1.07.05', 'FrmXMLController::add_default_templates()' );

		$path = untrailingslashit( trim( $path ) );
		$templates = glob( $path . '/*.php' );

		for ( $i = count( $templates ) - 1; $i >= 0; $i-- ) {
			$filename = str_replace( '.php', '', str_replace( $path . '/', '', $templates[ $i ] ) );
			$template_query = array( 'form_key' => $filename );
			if ( $template ) {
				$template_query['is_template'] = 1;
			}
			if ( $default ) {
				$template_query['default_template'] = 1;
			}
			$form = FrmForm::getAll( $template_query, '', 1 );

			$values = FrmFormsHelper::setup_new_vars();
			$values['form_key'] = $filename;
			$values['is_template'] = $template;
			$values['status'] = 'published';

			include( $templates[ $i ] );

			//get updated form
			if ( isset( $form ) && ! empty( $form ) ) {
				$old_id = $form->id;
				$form = FrmForm::getOne( $form->id );
			} else {
				$old_id = false;
				$form = FrmForm::getAll( $template_query, '', 1 );
			}

			if ( $form ) {
				do_action( 'frm_after_duplicate_form', $form->id, (array) $form, array( 'old_id' => $old_id ) );
			}
		}
	}

	/**
	 * @deprecated 3.01
	 */
	public static function sanitize_array( &$values ) {
		_deprecated_function( __FUNCTION__, '3.01', 'FrmAppHelper::sanitize_value' );
		FrmAppHelper::sanitize_value( 'wp_kses_post', $values );
	}

	/**
	 * Prepare and save settings in styles and actions
	 *
	 * @param array $settings
	 * @param string $group
	 *
	 * @since 2.0.6
	 * @deprecated 2.05.06
	 */
	public static function save_settings( $settings, $group ) {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmDb::' . __FUNCTION__ );
		return FrmDb::save_settings( $settings, $group );
	}

	/**
	 * Since actions are JSON encoded, we don't want any filters messing with it.
	 * Remove the filters and then add them back in case any posts or views are
	 * also being imported.
	 *
	 * Used when saving form actions and styles
	 *
	 * @since 2.0.4
	 * @deprecated 2.05.06
	 */
	public static function save_json_post( $settings ) {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmDb::' . __FUNCTION__ );
		return FrmDb::save_json_post( $settings );
	}

	/**
	 * Check cache before fetching values and saving to cache
	 *
	 * @since 2.0
	 * @deprecated 2.05.06
	 *
	 * @param string $cache_key The unique name for this cache
	 * @param string $group The name of the cache group
	 * @param string $query If blank, don't run a db call
	 * @param string $type The wpdb function to use with this query
	 * @return mixed $results The cache or query results
	 */
	public static function check_cache( $cache_key, $group = '', $query = '', $type = 'get_var', $time = 300 ) {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmDb::' . __FUNCTION__ );
		return FrmDb::check_cache( $cache_key, $group, $query, $type, $time );
	}

	/**
	 * @deprecated 2.05.06
	 */
	public static function set_cache( $cache_key, $results, $group = '', $time = 300 ) {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmDb::' . __FUNCTION__ );
		FrmDb::set_cache( $cache_key, $results, $group, $time );
	}

	/**
	 * Keep track of the keys cached in each group so they can be deleted
	 * in Redis and Memcache
	 * @deprecated 2.05.06
	 */
	public static function add_key_to_group_cache( $key, $group ) {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmDb::' . __FUNCTION__ );
		FrmDb::add_key_to_group_cache( $key, $group );
	}

	/**
	 * @deprecated 2.05.06
	 */
	public static function get_group_cached_keys( $group ) {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmDb::' . __FUNCTION__ );
		return FrmDb::get_group_cached_keys( $group );
	}

	/**
	 * @since 2.0
	 * @deprecated 2.05.06
	 * @param string $cache_key
	 */
	public static function delete_cache_and_transient( $cache_key, $group = 'default' ) {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmDb::' . __FUNCTION__ );
		FrmDb::delete_cache_and_transient( $cache_key, $group );
	}

	/**
	 * @since 2.0
	 * @deprecated 2.05.06
	 *
	 * @param string $group The name of the cache group
	 */
	public static function cache_delete_group( $group ) {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmDb::' . __FUNCTION__ );
		FrmDb::cache_delete_group( $group );
	}

	/**
	 * Added for < WP 4.0 compatability
	 *
	 * @since 1.07.10
	 * @deprecated 2.05.06
	 *
	 * @param string $term The value to escape
	 * @return string The escaped value
	 */
	public static function esc_like( $term ) {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmDb::' . __FUNCTION__ );
		return FrmDb::esc_like( $term );
	}

	/**
	 * @param string $order_query
	 * @deprecated 2.05.06
	 */
	public static function esc_order( $order_query ) {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmDb::' . __FUNCTION__ );
		return FrmDb::esc_order( $order_query );
	}

	/**
	 * Make sure this is ordering by either ASC or DESC
	 * @deprecated 2.05.06
	 */
	public static function esc_order_by( &$order_by ) {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmDb::' . __FUNCTION__ );
		FrmDb::esc_order_by( $order_by );
	}

	/**
	 * @param string $limit
	 * @deprecated 2.05.06
	 */
	public static function esc_limit( $limit ) {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmDb::' . __FUNCTION__ );
		return FrmDb::esc_limit( $limit );
	}

	/**
	 * Get an array of values ready to go through $wpdb->prepare
	 * @since 2.0
	 * @deprecated 2.05.06
	 */
	public static function prepare_array_values( $array, $type = '%s' ) {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmDb::' . __FUNCTION__ );
		return FrmDb::prepare_array_values( $array, $type );
	}

	/**
	 * @deprecated 2.05.06
	 */
	public static function prepend_and_or_where( $starts_with = ' WHERE ', $where = '' ) {
		_deprecated_function( __FUNCTION__, '2.05.06', 'FrmDb::' . __FUNCTION__ );
		return FrmDb::prepend_and_or_where( $starts_with, $where );
	}

	/**
	 * @deprecated 2.05.06
	 */
	public static function upgrade() {
		$db = new FrmDbDeprecated();
		$db->upgrade();
	}

	/**
	 * @deprecated 2.05.06
	 */
	public static function collation() {
		$db = new FrmDbDeprecated();
		return $db->collation();
	}

	/**
	 * @deprecated 2.05.06
	 */
	public static function uninstall() {
		$db = new FrmDbDeprecated();
		$db->uninstall();
	}

	/**
	 * @deprecated 3.0
	 *
	 * @param string $html
	 * @param array $field
	 * @param array $errors
	 * @param object $form
	 * @param array $args
	 *
	 * @return string
	 */
	public static function replace_shortcodes( $html, $field, $errors = array(), $form = false, $args = array() ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType::prepare_field_html' );
		$field_obj = FrmFieldFactory::get_field_type( $field['type'], $field );
		return $field_obj->prepare_field_html( compact( 'errors', 'form' ) );
	}

	/**
	 * @deprecated 3.0
	 */
	public static function get_default_field_opts( $type, $field = null, $limit = false ) {
		if ( $limit ) {
			_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldHelper::get_default_field_options' );
			$field_options = FrmFieldsHelper::get_default_field_options( $type );
		} else {
			_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldHelper::get_default_field' );
			$field_options = FrmFieldsHelper::get_default_field( $type );
		}

		return $field_options;
	}

	/**
	 * @deprecated 2.02.07
	 */
	public static function dropdown_categories( $args ) {
		_deprecated_function( __FUNCTION__, '2.02.07', 'FrmProPost::get_category_dropdown' );

		if ( FrmAppHelper::pro_is_installed() ) {
			$args['location'] = 'front';
			$dropdown = FrmProPost::get_category_dropdown( $args['field'], $args );
		} else {
			$dropdown = '';
		}

		return $dropdown;
	}

	/**
	 * @deprecated 3.0
	 */
	public static function remove_inline_conditions( $no_vars, $code, $replace_with, &$html ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmShortcodeHelper::remove_inline_conditions' );
		FrmShortcodeHelper::remove_inline_conditions( $no_vars, $code, $replace_with, $html );
	}

	/**
	 * @deprecated 3.0
	 */
	public static function get_shortcode_tag( $shortcodes, $short_key, $args ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmShortcodeHelper::get_shortcode_tag' );
        return FrmShortcodeHelper::get_shortcode_tag( $shortcodes, $short_key, $args );
    }

	/**
	 * @deprecated 3.01
	 */
	public static function get_sigle_label_postitions() {
		_deprecated_function( __FUNCTION__, '3.01', 'FrmStylesHelper::get_single_label_positions' );
		return FrmStylesHelper::get_single_label_positions();
	}

	/**
	 * @deprecated 3.02.03
	 */
    public static function jquery_themes() {
		_deprecated_function( __FUNCTION__, '3.02.03', 'FrmProStylesController::jquery_themes' );

        $themes = array(
            'ui-lightness'  => 'UI Lightness',
            'ui-darkness'   => 'UI Darkness',
            'smoothness'    => 'Smoothness',
            'start'         => 'Start',
            'redmond'       => 'Redmond',
            'sunny'         => 'Sunny',
            'overcast'      => 'Overcast',
            'le-frog'       => 'Le Frog',
            'flick'         => 'Flick',
			'pepper-grinder' => 'Pepper Grinder',
            'eggplant'      => 'Eggplant',
            'dark-hive'     => 'Dark Hive',
            'cupertino'     => 'Cupertino',
            'south-street'  => 'South Street',
            'blitzer'       => 'Blitzer',
            'humanity'      => 'Humanity',
            'hot-sneaks'    => 'Hot Sneaks',
            'excite-bike'   => 'Excite Bike',
            'vader'         => 'Vader',
            'dot-luv'       => 'Dot Luv',
            'mint-choc'     => 'Mint Choc',
            'black-tie'     => 'Black Tie',
            'trontastic'    => 'Trontastic',
            'swanky-purse'  => 'Swanky Purse',
        );

		$themes = apply_filters( 'frm_jquery_themes', $themes );
        return $themes;
    }

	/**
	 * @deprecated 3.02.03
	 */
    public static function enqueue_jquery_css() {
		_deprecated_function( __FUNCTION__, '3.02.03', 'FrmProStylesController::enqueue_jquery_css' );

		$form = self::get_form_for_page();
		$theme_css = FrmStylesController::get_style_val( 'theme_css', $form );
        if ( $theme_css != -1 ) {
			wp_enqueue_style( 'jquery-theme', self::jquery_css_url( $theme_css ), array(), FrmAppHelper::plugin_version() );
        }
    }

	/**
	 * @deprecated 3.02.03
	 */
	public static function jquery_css_url( $theme_css ) {
		_deprecated_function( __FUNCTION__, '3.02.03', 'FrmProStylesController::jquery_css_url' );

		if ( ! is_callable( 'FrmProStylesController::jquery_css_url' ) ) {
			return;
		}

		return FrmProStylesController::jquery_css_url( $theme_css );
    }

	/**
	 * @deprecated 3.02.03
	 */
	public static function get_form_for_page() {
		_deprecated_function( __FUNCTION__, '3.02.03' );

		global $frm_vars;
		$form_id = 'default';
		if ( ! empty( $frm_vars['forms_loaded'] ) ) {
			foreach ( $frm_vars['forms_loaded'] as $form ) {
				if ( is_object( $form ) ) {
					$form_id = $form->id;
					break;
				}
			}
		}
		return $form_id;
	}

	/**
	 * @deprecated 3.0
	 */
	public static function validate_url_field( &$errors, $field, $value, $args ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType::validate' );

		if ( $value == '' || ! in_array( $field->type, array( 'website', 'url' ) ) ) {
			return;
		}

		FrmEntryValidate::validate_field_types( $errors, $field, $value, $args );
	}

	/**
	 * @deprecated 3.0
	 */
	public static function validate_email_field( &$errors, $field, $value, $args ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType::validate' );

		if ( $field->type != 'email' ) {
			return;
		}

		FrmEntryValidate::validate_field_types( $errors, $field, $value, $args );
	}

	/**
	 * @deprecated 3.0
	 */
	public static function validate_number_field( &$errors, $field, $value, $args ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType::validate' );

		//validate the number format
		if ( $field->type != 'number' ) {
			return;
		}

		FrmEntryValidate::validate_field_types( $errors, $field, $value, $args );
	}

	/**
	 * @deprecated 3.0
	 */
	public static function validate_recaptcha( &$errors, $field, $args ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldType::validate' );

		if ( $field->type != 'captcha' ) {
			return;
		}

		FrmEntryValidate::validate_field_types( $errors, $field, '', $args );
	}
}
