<?php
/**
 * Explicit, permission-checked MCP operations owned by solution add-ons.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSolutionAbilitiesHelper {
	/**
	 * Register a fixed server-side operation using its native validation.
	 *
	 * @since x.x
	 *
	 * @param string   $name Ability slug without namespace.
	 * @param string   $label Translated operation label.
	 * @param array    $schema Explicit input schema.
	 * @param callable $callback Native operation accepting the input array.
	 * @param array    $caps Required user capabilities.
	 * @param array    $annotations Readonly, destructive and idempotent flags.
	 *
	 * @return void
	 */
	public static function register( $name, $label, $schema, $callback, $caps, $annotations = array() ) {
		if ( ! FrmAbilitiesController::is_active() ) {
			return;
		}

		if ( empty( $schema['required'] ) ) {
			$schema['default'] = array();
		}

		$flags    = array_replace( array( false, false, false ), $annotations );
		$defaults = array();

		foreach ( $schema['properties'] ?? array() as $key => $property ) {
			if ( array_key_exists( 'default', $property ) ) {
				$defaults[ $key ] = $property['default'];
			}
		}

		FrmAbilitiesHelper::register(
			'formidable-forms/' . $name,
			array(
				'label'               => $label,
				'description'         => $label,
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array_merge(
					array(
						'type'                 => 'object',
						'additionalProperties' => false,
					),
					$schema
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array( 'result' => array( 'type' => array( 'object', 'array', 'integer', 'number', 'string', 'boolean', 'null' ) ) ),
				),
				'permission_callback' => function ( $input ) use ( $caps ) {
					unset( $input );
					FrmAbilitiesHelper::set_current_user();

					foreach ( $caps as $cap ) {
						$allowed = str_starts_with( $cap, 'frm_' ) ? FrmAppHelper::current_user_can( $cap ) : current_user_can( $cap );

						if ( ! $allowed ) {
							return false;
						}
					}

					return true;
				},
				'execute_callback'    => function ( $input ) use ( $callback, $defaults ) {
					FrmAbilitiesHelper::set_current_user();
					$result = call_user_func( $callback, array_merge( $defaults, $input ) );

					if ( is_wp_error( $result ) ) {
						return $result;
					}

					if ( false === $result ) {
						return new WP_Error( 'frm_solution_operation', __( 'The operation is not available for this record or its current state.', 'formidable' ) );
					}

					return array( 'result' => $result );
				},
				'meta'                => FrmAbilitiesHelper::meta( $flags[0], $flags[1], $flags[2] ),
			)
		);
	}
}
