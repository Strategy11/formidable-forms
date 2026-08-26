<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Shared helpers for the Formidable abilities controllers.
 *
 * Used by the Formidable abilities and by the ones Pro and Views register, so
 * every domain answers with the same shapes whichever plugin owns it.
 *
 * @since x.x
 */
class FrmAbilitiesHelper {

	/**
	 * Register one ability, unless the name is already taken.
	 *
	 * Every Formidable family ability goes through here. The Abilities API
	 * treats a duplicate name as a mistake, raising _doing_it_wrong and
	 * returning null, and on a site running a mix of plugin versions two of them
	 * really can reach for the same name. Skipping is the right answer: whoever
	 * registered first is serving the name, and a notice in the log helps nobody
	 * because neither plugin is at fault.
	 *
	 * @since x.x
	 *
	 * @param string $name Ability name, including the formidable-forms prefix.
	 * @param array  $args Arguments accepted by wp_register_ability().
	 *
	 * @return WP_Ability|null The registered ability, or null when it was skipped or rejected.
	 */
	public static function register( $name, $args ) {
		if ( function_exists( 'wp_has_ability' ) && wp_has_ability( $name ) ) {
			return null;
		}

		return wp_register_ability( $name, $args );
	}

	/**
	 * Get the meta every Formidable ability declares.
	 *
	 * The show_in_rest flag exposes it on wp-abilities/v1, and mcp.public is
	 * what the adapter's discovery and execution tools resolve against. The
	 * annotations tell a client what an ability does before it calls it, which
	 * is what lets an assistant avoid a destructive call it did not mean to
	 * make.
	 *
	 * @since x.x
	 *
	 * @param bool $readonly    Whether the ability only reads data.
	 * @param bool $destructive Whether the ability can remove data.
	 * @param bool $idempotent  Whether calling it twice has the same effect as calling it once.
	 *
	 * @return array
	 */
	public static function meta( $readonly, $destructive, $idempotent ) {
		return array(
			'show_in_rest' => true,
			'mcp'          => array(
				'public' => true,
			),
			'annotations'  => array(
				'readonly'    => $readonly,
				'destructive' => $destructive,
				'idempotent'  => $idempotent,
			),
		);
	}

	/**
	 * Resolve the user behind an MCP or Abilities API request.
	 *
	 * The abilities run inside a REST request that has already authenticated,
	 * but the current user is not always set on the global by the time an
	 * execute callback runs. Asking the determine_current_user filter again is
	 * what the permission callbacks are checked against, so it has to happen
	 * before any capability check.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function set_current_user() {
		if ( is_user_logged_in() ) {
			return;
		}

		$user_id = apply_filters( 'determine_current_user', false );

		if ( $user_id ) {
			wp_set_current_user( $user_id );
		}
	}

	/**
	 * Build the list of field types the create abilities accept.
	 *
	 * Derived from the field registry instead of a hard-coded list so every
	 * installed type (Formidable, Pro, and add-ons) is creatable. Types whose
	 * add-on is not installed resolve to the Default placeholder class and are
	 * excluded, since storing them would render a field with no input. The
	 * palette repeater key ('divider|repeat') and the auto-managed submit field
	 * are excluded, and the schema aliases the create abilities normalize are
	 * appended.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	public static function get_creatable_field_types() {
		$types = array();

		foreach ( array_keys( FrmField::all_field_selection() ) as $type ) {
			if ( ! is_string( $type ) || str_contains( $type, '|' ) || 'submit' === $type ) {
				continue;
			}

			$field_type = FrmFieldFactory::get_field_type( $type );

			if ( in_array( get_class( $field_type ), array( 'FrmFieldDefault', 'FrmProFieldDefault' ), true ) ) {
				continue;
			}

			$types[] = $type;
		}

		return array_merge( $types, array( 'dropdown', 'star_rating', 'section' ) );
	}

	/**
	 * Lowercase the order input so downstream queries always receive asc or desc.
	 *
	 * The input schemas accept both casings because clients (LLMs especially)
	 * send DESC as often as desc, but internally one consistent case is used.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array
	 */
	public static function normalize_order( $input ) {
		if ( isset( $input['order'] ) && is_string( $input['order'] ) ) {
			$input['order'] = strtolower( $input['order'] );
		}

		return $input;
	}

	/**
	 * Build the ORDER BY and LIMIT clauses for a paged list ability.
	 *
	 * Every number is cast before it reaches the clause and the column goes
	 * through FrmDb::esc_order, so nothing here carries a caller's string into
	 * SQL. The page size is capped: an ability is answering a language model,
	 * and an unbounded page is a way to exhaust memory by accident.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array The order clause and the limit clause, in that order.
	 */
	public static function prepare_order_and_limit( $input ) {
		$page_size = 50;

		if ( ! empty( $input['limit'] ) ) {
			$page_size = absint( $input['limit'] );
		} elseif ( ! empty( $input['page_size'] ) ) {
			$page_size = absint( $input['page_size'] );
		}

		$page_size = min( max( $page_size, 1 ), 200 );
		$page      = ! empty( $input['page'] ) ? max( 1, absint( $input['page'] ) ) : 1;
		$order_by  = ! empty( $input['order_by'] ) && is_string( $input['order_by'] ) ? $input['order_by'] : 'created_at';
		$order     = ! empty( $input['order'] ) && is_string( $input['order'] ) ? $input['order'] : 'DESC';
		$offset    = $page_size * ( $page - 1 );

		return array(
			FrmDb::esc_order( ' ORDER BY ' . $order_by . ' ' . $order ),
			' LIMIT ' . $offset . ',' . $page_size,
		);
	}

	/**
	 * Flatten a WP_Error carrying an array message into one readable string.
	 *
	 * The adapter's output schema requires error to be a string, but validation
	 * errors arrive as per field maps. An array message fails output validation,
	 * and the client is then told the output was malformed rather than which
	 * field was wrong.
	 *
	 * @since x.x
	 *
	 * @param WP_Error $error The error to flatten.
	 *
	 * @return WP_Error
	 */
	public static function flatten_error( $error ) {
		$messages = array();

		foreach ( $error->get_error_codes() as $code ) {
			foreach ( (array) $error->errors[ $code ] as $message ) {
				if ( ! is_array( $message ) ) {
					$messages[] = (string) $message;
					continue;
				}

				foreach ( $message as $key => $msg ) {
					$messages[] = is_string( $key ) ? $key . ': ' . $msg : (string) $msg;
				}
			}
		}

		return new WP_Error( $error->get_error_code(), implode( '; ', $messages ), $error->get_error_data() );
	}

	/**
	 * Build the error returned when an ability needs a plugin that is not active.
	 *
	 * @since x.x
	 *
	 * @param string $plugin_name Display name of the plugin the ability needs, such as Formidable Forms Pro.
	 *
	 * @return WP_Error
	 */
	public static function missing_plugin_error( $plugin_name ) {
		return new WP_Error(
			'frm_plugin_required',
			sprintf(
				/* translators: %s: the name of a Formidable plugin, such as Formidable Forms Pro */
				__( 'This action needs %s, which is not active on this site.', 'formidable' ),
				$plugin_name
			),
			array( 'status' => 403 )
		);
	}

	/**
	 * Look up a form by id or key, for the abilities that accept either.
	 *
	 * @since x.x
	 *
	 * @param int|string $id Form ID or form_key.
	 *
	 * @return stdClass|WP_Error The form, or an error when nothing matches.
	 */
	public static function get_form( $id ) {
		$form = FrmForm::getOne( $id );

		if ( ! $form ) {
			return new WP_Error(
				'frm_form_not_found',
				__( 'No form was found with that ID or key.', 'formidable' ),
				array( 'status' => 404 )
			);
		}

		return $form;
	}
}
