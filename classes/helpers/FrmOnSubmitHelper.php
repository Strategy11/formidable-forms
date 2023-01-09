<?php
/**
 * On Submit action helper
 *
 * @package Formidable
 * @since 5.x.x
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmOnSubmitHelper {

	/**
	 * Cache the on submit actions.
	 *
	 * @var array
	 */
	private static $actions = array();

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
		<div class="frm_form_field frm_has_shortcodes frm_has_textarea">
			<label for="<?php echo esc_attr( $id_attr ); ?>">
				<?php esc_html_e( 'Message on submit', 'formidable' ); ?>
			</label>
			<textarea
				name="<?php echo esc_attr( $args['action_control']->get_field_name( 'success_msg' ) ); ?>"
				id="<?php echo esc_attr( $id_attr ); ?>"
				rows="4"
			><?php echo FrmAppHelper::esc_textarea( $args['form_action']->post_content['success_msg'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></textarea>
		</div>

		<?php
		$id_attr   = $args['action_control']->get_field_id( 'show_form' );
		$name_attr = $args['action_control']->get_field_name( 'show_form' );
		?>
		<div class="frm_form_field">
			<?php
			if ( method_exists( 'FrmProHtmlHelper', 'toggle' ) ) {
				FrmProHtmlHelper::toggle(
					$id_attr,
					$name_attr,
					array(
						'div_class' => 'with_frm_style frm_toggle',
						'checked'   => ! empty( $args['form_action']->post_content['show_form'] ),
						'echo'      => true,
					)
				);
			} else {
				printf(
					'<input type="checkbox" value="1" id="%1$s" name="%2$s" %3$s />',
					esc_attr( $id_attr ),
					esc_attr( $name_attr ),
					checked( ! empty( $args['form_action']->post_content['show_form'] ) )
				);
			}
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
			<label for="<?php echo esc_attr( $id_attr ); ?>"><?php esc_html_e( 'Enter URL', 'formidable' ); ?></label>
			<input
				type="text"
				id="<?php echo esc_attr( $id_attr ); ?>"
				name="<?php echo esc_attr( $args['action_control']->get_field_name( 'success_url' ) ); ?>"
				value="<?php echo esc_attr( $args['form_action']->post_content['success_url'] ); ?>"
			/>
		</div>

		<?php $id_attr = $args['action_control']->get_field_id( 'redirect_msg' ); ?>
		<div class="frm_form_field">
			<label for="<?php echo esc_attr( $id_attr ); ?>"><?php esc_html_e( 'Redirect message (used when multiple On Submit actions run)', 'formidable' ); ?></label>
			<textarea
				name="<?php echo esc_attr( $args['action_control']->get_field_name( 'redirect_msg' ) ); ?>"
				id="<?php echo esc_attr( $id_attr ); ?>"
				rows="4"
			><?php echo FrmAppHelper::esc_textarea( $args['form_action']->post_content['redirect_msg'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></textarea>
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
			<label for="<?php echo esc_attr( $name_attr ); ?>"><?php esc_html_e( 'Select a page', 'formidable' ); ?></label>
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
	 *
	 * @param int $form_id Form ID.
	 * @return array
	 */
	public static function get_actions( $form_id ) {
		if ( isset( self::$actions[ $form_id ] ) ) {
			return self::$actions[ $form_id ];
		}

		self::$actions[ $form_id ] = FrmFormAction::get_action_for_form( $form_id, FrmOnSubmitAction::$slug );
		return self::$actions[ $form_id ];
	}

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
}
