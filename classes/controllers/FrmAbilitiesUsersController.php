<?php
/**
 * Create subscriber accounts for entry-based member experiences.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAbilitiesUsersController {
	/**
	 * Register account creation without exposing passwords or privileged roles.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_abilities() {
		FrmSolutionAbilitiesHelper::register(
			'create-subscriber',
			__( 'Create a subscriber account without sending a notification', 'formidable' ),
			array(
				'properties' => array(
					'username'   => array(
						'type'      => 'string',
						'minLength' => 1,
						'maxLength' => 60,
					),
					'email'      => array(
						'type'   => 'string',
						'format' => 'email',
					),
					'first_name' => array(
						'type'    => 'string',
						'default' => '',
					),
					'last_name'  => array(
						'type'    => 'string',
						'default' => '',
					),
				),
				'required'   => array( 'username', 'email' ),
			),
			array( self::class, 'create' ),
			array( 'create_users', 'frm_edit_entries' )
		);
	}

	/**
	 * Use WordPress account validation and keep the generated password private.
	 *
	 * @since x.x
	 *
	 * @param array $input New subscriber identity.
	 *
	 * @return array|WP_Error
	 */
	public static function create( $input ) {
		$first = sanitize_text_field( $input['first_name'] );
		$last  = sanitize_text_field( $input['last_name'] );
		$id    = wp_insert_user(
			array(
				'user_login'   => sanitize_user( $input['username'], true ),
				'user_email'   => sanitize_email( $input['email'] ),
				'user_pass'    => wp_generate_password( 40, true, true ),
				'first_name'   => $first,
				'last_name'    => $last,
				'display_name' => trim( $first . ' ' . $last ),
				'role'         => 'subscriber',
			)
		);
		return is_wp_error( $id ) ? $id : array(
			'id'   => $id,
			'role' => 'subscriber',
		);
	}
}
