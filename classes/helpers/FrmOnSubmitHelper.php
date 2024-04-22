<?php
/**
 * On Submit action helper
 *
 * @package Formidable
 * @since 6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmOnSubmitHelper {

	/**
	 * Shows message settings.
	 *
	 * @param array $args {
	 *     The args.
	 *
	 *     @type object        $form_action    Form action post data.
	 *     @type FrmFormAction $action_control Form action object.
	 *     @type object        $form           Form data.
	 *     @type string        $action_key     Action key.
	 *     @type array         $values         Contains `fields` (form fields) and `id` (form ID).
	 * }
	 */
	public static function show_message_settings( $args ) {
		$id_attr = $args['action_control']->get_field_id( 'success_msg' );
		?>
		<div class="frm_form_field frm_has_shortcodes">
			<label for="<?php echo esc_attr( $id_attr ); ?>" class="screen-reader-text">
				<?php esc_html_e( 'Message on submit', 'formidable' ); ?>
			</label>

			<?php
			wp_editor(
				$args['form_action']->post_content['success_msg'],
				$id_attr,
				array(
					'textarea_name' => $args['action_control']->get_field_name( 'success_msg' ),
					'textarea_rows' => 4,
					'editor_class'  => 'frm_not_email_message',
				)
			);
			?>
		</div>

		<?php
		$id_attr   = $args['action_control']->get_field_id( 'show_form' );
		$name_attr = $args['action_control']->get_field_name( 'show_form' );
		?>
		<div class="frm_form_field">
			<?php
			FrmHtmlHelper::toggle(
				$id_attr,
				$name_attr,
				array(
					'div_class' => 'with_frm_style frm_toggle',
					'checked'   => ! empty( $args['form_action']->post_content['show_form'] ),
					'echo'      => true,
				)
			);
			?>
			<label for="<?php echo esc_attr( $id_attr ); ?>">
				<?php esc_html_e( 'Show the form with the confirmation message', 'formidable' ); ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Shows redirect settings.
	 *
	 * @param array $args {
	 *     The args.
	 *
	 *     @type object        $form_action    Form action post data.
	 *     @type FrmFormAction $action_control Form action object.
	 *     @type object        $form           Form data.
	 *     @type string        $action_key     Action key.
	 *     @type array         $values         Contains `fields` (form fields) and `id` (form ID).
	 * }
	 */
	public static function show_redirect_settings( $args ) {
		$id_attr = $args['action_control']->get_field_id( 'success_url' );
		?>
		<div class="frm_form_field frm_has_shortcodes">
			<label for="<?php echo esc_attr( $id_attr ); ?>"><?php esc_html_e( 'Redirect URL', 'formidable' ); ?></label>
			<input
				type="text"
				id="<?php echo esc_attr( $id_attr ); ?>"
				name="<?php echo esc_attr( $args['action_control']->get_field_name( 'success_url' ) ); ?>"
				value="<?php echo esc_attr( $args['form_action']->post_content['success_url'] ); ?>"
			/>
		</div>

		<?php
		$id_attr   = $args['action_control']->get_field_id( 'open_in_new_tab' );
		$name_attr = $args['action_control']->get_field_name( 'open_in_new_tab' );
		?>
		<div class="frm_form_field">
			<?php
			FrmHtmlHelper::toggle(
				$id_attr,
				$name_attr,
				array(
					'div_class' => 'with_frm_style frm_toggle',
					'checked'   => ! empty( $args['form_action']->post_content['open_in_new_tab'] ),
					'echo'      => true,
				)
			);
			?>
			<label for="<?php echo esc_attr( $id_attr ); ?>" <?php FrmAppHelper::maybe_add_tooltip( 'new_tab' ); ?>>
				<?php esc_html_e( 'Open in new tab', 'formidable' ); ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Shows page settings.
	 *
	 * @param array $args {
	 *     The args.
	 *
	 *     @type object        $form_action    Form action post data.
	 *     @type FrmFormAction $action_control Form action object.
	 *     @type object        $form           Form data.
	 *     @type string        $action_key     Action key.
	 *     @type array         $values         Contains `fields` (form fields) and `id` (form ID).
	 * }
	 */
	public static function show_page_settings( $args ) {
		$name_attr = $args['action_control']->get_field_name( 'success_page_id' );
		?>
		<div class="frm_form_field">
			<label for="<?php echo esc_attr( $name_attr ); ?>" class="screen-reader-text">
				<?php esc_html_e( 'Select a page', 'formidable' ); ?>
			</label>
			<?php
			FrmAppHelper::maybe_autocomplete_pages_options(
				array(
					'field_name'  => $name_attr,
					'page_id'     => $args['form_action']->post_content['success_page_id'],
					'placeholder' => __( 'Select a Page', 'formidable' ),
				)
			);
			?>
		</div>
		<?php
	}

	/**
	 * Gets all active On Submit form actions for form.
	 * This checks and gets data from WordPress cache group `frm_actions` first. It prevents the cache issue if form
	 * action is created during run time. If you use another cache method, please remember to delete the cache in
	 * {@see FrmFormAction::save_settings()}.
	 *
	 * @param int $form_id Form ID.
	 * @return array
	 */
	public static function get_actions( $form_id ) {
		$cache_key = 'frm_on_submit_actions_' . $form_id;
		$actions   = wp_cache_get( $cache_key, 'frm_actions' );
		if ( false !== $actions ) {
			return $actions;
		}

		$actions = FrmFormAction::get_action_for_form( $form_id, FrmOnSubmitAction::$slug, array( 'post_status' => 'publish' ) );
		wp_cache_set( $cache_key, 'frm_actions' );
		return $actions;
	}

	/**
	 * Gets On Submit action type (message, redirect or page).
	 *
	 * @param object $action Form action object.
	 * @return string
	 */
	public static function get_action_type( $action ) {
		if ( isset( $action->post_content['success_action'] ) ) {
			return $action->post_content['success_action'];
		}
		return self::get_default_action_type();
	}

	public static function get_default_action_type() {
		return 'message';
	}

	public static function get_default_msg() {
		$msg = FrmAppHelper::get_settings()->success_msg;
		return $msg ? $msg : __( 'Your responses were successfully submitted. Thank you!', 'formidable' );
	}

	public static function get_default_redirect_msg() {
		return __( 'Please wait while you are redirected.', 'formidable' );
	}

	/**
	 * Gets the default open in new tab message.
	 *
	 * @since 6.3.1
	 *
	 * @return string
	 */
	public static function get_default_new_tab_msg() {
		return FrmAppHelper::get_settings()->new_tab_msg;
	}

	/**
	 * Adds the first On Submit action data to the form options to be saved.
	 *
	 * @param int $form_id Form ID.
	 */
	public static function save_on_submit_settings( $form_id ) {
		$actions             = self::get_actions( $form_id );
		$first_create_action = null;
		$first_edit_action   = null;
		foreach ( $actions as $action ) {
			if ( ! $first_create_action && in_array( 'create', $action->post_content['event'], true ) ) {
				$first_create_action = $action;
			}
			if ( ! $first_edit_action && in_array( 'update', $action->post_content['event'], true ) ) {
				$first_edit_action = $action;
			}
		}

		$form_options = array();
		self::populate_on_submit_data( $form_options, $first_create_action );
		if ( method_exists( 'FrmProFormActionsController', 'change_on_submit_action_ops' ) && FrmAppHelper::pro_is_connected() ) {
			$form_editable = FrmDb::get_var( 'frm_forms', array( 'id' => $form_id ), 'editable' );
			if ( $form_editable ) {
				self::populate_on_submit_data( $form_options, $first_edit_action, 'update' );
			}
		}

		if ( ! empty( $form_options ) ) {
			$_POST['options'] += $form_options;
		}
	}

	/**
	 * Populates the On Submit data to form options.
	 *
	 * @param array  $form_options Form options.
	 * @param object $action       Optional. The On Submit action object.
	 * @param string $event        Form event. Default is `create`.
	 */
	public static function populate_on_submit_data( &$form_options, $action = null, $event = 'create' ) {
		$opt = 'update' === $event ? 'edit_' : 'success_';
		if ( ! $action || ! is_object( $action ) ) {
			$form_options[ $opt . 'action' ] = self::get_default_action_type();
			$form_options[ $opt . 'msg' ]    = self::get_default_msg();

			return;
		}

		$form_options[ $opt . 'action' ] = isset( $action->post_content['success_action'] ) ? $action->post_content['success_action'] : 'message';

		switch ( $form_options[ $opt . 'action' ] ) {
			case 'redirect':
				$form_options[ $opt . 'url' ]    = isset( $action->post_content['success_url'] ) ? $action->post_content['success_url'] : '';
				$form_options['open_in_new_tab'] = ! empty( $action->post_content['open_in_new_tab'] );
				break;

			case 'page':
				$form_options[ $opt . 'page_id' ] = isset( $action->post_content['success_page_id'] ) ? $action->post_content['success_page_id'] : '';
				break;

			default:
				$form_options[ $opt . 'msg' ] = ! empty( $action->post_content['success_msg'] ) ? $action->post_content['success_msg'] : self::get_default_msg();
				$form_options['show_form']    = ! empty( $action->post_content['show_form'] );
		}
	}

	/**
	 * Maybe migrate submit settings from the form options to On Submit action.
	 * This is added after On Submit action is released. This might migrate the frontend editing submit settings too.
	 *
	 * @since 6.1.1
	 *
	 * @param int $form_id Form ID.
	 */
	public static function maybe_migrate_submit_settings_to_action( $form_id ) {
		$form = FrmDb::get_row( 'frm_forms', array( 'id' => $form_id ), 'options,editable' );
		if ( ! $form ) {
			return;
		}

		FrmAppHelper::unserialize_or_decode( $form->options );

		if ( self::form_has_migrated( $form ) ) {
			return;
		}

		$form->id    = $form_id;
		$action_type = FrmOnSubmitAction::$slug;

		// Check if form already has form actions to avoid creating duplicates.
		$has_actions = FrmFormAction::form_has_action_type( $form_id, $action_type );
		if ( ! empty( $has_actions ) ) {
			// Don't migrate again.
			self::save_migrated_success_actions( $form );
			return;
		}

		// Create On Submit action.
		$base_action = array();

		$base_action['post_type']    = FrmFormActionsController::$action_post_type;
		$base_action['post_excerpt'] = $action_type;
		$base_action['post_title']   = FrmOnSubmitAction::get_name();
		$base_action['menu_order']   = $form_id;
		$base_action['post_status']  = 'publish';
		$base_action['post_content'] = array(
			'event' => array( 'create' ),
		);

		$action_data = self::get_on_submit_action_data_from_form_options( $form->options );

		// If frontend editing is enabled, migrate its settings too.
		if ( method_exists( 'FrmProFormActionsController', 'change_on_submit_action_ops' ) && FrmAppHelper::pro_is_connected() && $form->editable ) {
			$edit_data = self::get_on_submit_action_data_from_form_options( $form->options, 'update' );

			if ( $action_data === $edit_data ) {
				// Just create one action for both create and update if they are the same.
				$base_action['post_content']['event'][] = 'update';
			} else {
				// Create a separate action for update.
				$edit_action                          = $base_action;
				$edit_action['post_content']         += $edit_data;
				$edit_action['post_content']['event'] = array( 'update' );

				$edit_action['post_content'] = FrmAppHelper::prepare_and_encode( $edit_action['post_content'] );
				FrmDb::save_json_post( $edit_action );
			}
		}

		$action                  = $base_action;
		$action['post_content'] += $action_data;

		$action['post_content'] = FrmAppHelper::prepare_and_encode( $action['post_content'] );
		FrmDb::save_json_post( $action );

		self::save_migrated_success_actions( $form );
	}

	/**
	 * Gets On Submit action data from form options to be used for the migration.
	 *
	 * @since 6.1.1
	 *
	 * @param array  $form_options Form options.
	 * @param string $event        Action event. Accepts `create` or `update`. Default is `create`.
	 * @return array
	 */
	private static function get_on_submit_action_data_from_form_options( $form_options, $event = 'create' ) {
		$opt  = 'update' === $event ? 'edit_' : 'success_';
		$data = array(
			'success_action' => isset( $form_options[ $opt . 'action' ] ) ? $form_options[ $opt . 'action' ] : self::get_default_action_type(),
		);

		switch ( $data['success_action'] ) {
			case 'redirect':
				$data['success_url'] = isset( $form_options[ $opt . 'url' ] ) ? $form_options[ $opt . 'url' ] : '';
				break;

			case 'page':
				$data['success_page_id'] = isset( $form_options[ $opt . 'page_id' ] ) ? $form_options[ $opt . 'page_id' ] : '';
				break;

			default:
				$data['success_msg'] = isset( $form_options[ $opt . 'msg' ] ) ? $form_options[ $opt . 'msg' ] : self::get_default_msg();
				$data['show_form']   = ! empty( $form_options['show_form'] );
		}

		return $data;
	}

	/**
	 * Checks if On Submit settings are migrated.
	 *
	 * @since 6.1.1
	 *
	 * @param object $form Form object.
	 * @return bool
	 */
	public static function form_has_migrated( $form ) {
		return ! empty( $form->options['on_submit_migrated'] );
	}

	/**
	 * @since 6.1.1
	 *
	 * @param object $form Limited form object.
	 * @return void
	 */
	private static function save_migrated_success_actions( $form ) {
		// Options may be an empty string.
		if ( ! is_array( $form->options ) ) {
			$form->options = array();
		}
		$form->options['on_submit_migrated'] = 1;
		FrmForm::update( $form->id, array( 'options' => $form->options ) );
	}

	/**
	 * Gets fallback action, used when no actions match.
	 *
	 * @since 6.1.1
	 *
	 * @param array|string $event Uses 'create' or 'update'.
	 *
	 * @return object
	 */
	public static function get_fallback_action( $event = 'create' ) {
		$action = new stdClass();

		$action->post_content = array(
			'event'          => (array) $event,
			'success_action' => 'message',
			'success_msg'    => self::get_default_msg(),
		);

		return $action;
	}

	/**
	 * Gets fallback action after opening the redirect URL in a new tab.
	 *
	 * @since 6.3.1
	 *
	 * @param array|string $event Uses 'create' or 'update'.
	 * @return object
	 */
	public static function get_fallback_action_after_open_in_new_tab( $event ) {
		$action = self::get_fallback_action( $event );

		$action->post_content['success_msg'] = self::get_default_new_tab_msg();

		return $action;
	}

	/**
	 * Check if the current event has been paased. If not, use create actions.
	 *
	 * @since 6.1.1
	 *
	 * @return string
	 */
	public static function current_event( $atts ) {
		return ! empty( $atts['action'] ) ? $atts['action'] : 'create';
	}
}
