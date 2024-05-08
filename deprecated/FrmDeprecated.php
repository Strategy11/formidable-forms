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
	 * @deprecated 3.0
	 *
	 * @param string       $html
	 * @param array        $field
	 * @param array        $errors
	 * @param false|object $form
	 * @param array        $args
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
	public static function remove_inline_conditions( $no_vars, $code, $replace_with, &$html ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmShortcodeHelper::remove_inline_conditions' );
		FrmShortcodeHelper::remove_inline_conditions( $no_vars, $code, $replace_with, $html );
	}

	/**
	 * @deprecated 2.02.07 This is still referenced in the Highrise add-on. It is not safe to remove.
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
}
