<?php

/**
 * @since 3.0
 */
class FrmFieldUserID extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'user_id';

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $has_input = false;

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $has_html = false;

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $holds_email_values = true;

	/**
	 * @return string
	 */
	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-user-id.php';
	}

	public function prepare_field_html( $args ) {
		$args = $this->fill_display_field_values( $args );

		$user_ID      = get_current_user_id();
		$user_ID      = ( $user_ID ? $user_ID : '' );
		$posted_value = ( FrmAppHelper::is_admin() && $_POST && isset( $_POST['item_meta'][ $this->field['id'] ] ) ); // WPCS: CSRF ok.
		$updating     = ( isset( $args['action'] ) && $args['action'] == 'update' );
		$value        = ( is_numeric( $this->field['value'] ) || $posted_value || $updating ) ? $this->field['value'] : $user_ID;

		echo '<input type="hidden" name="' . esc_attr( $args['field_name'] ) . '" id="' . esc_attr( $args['html_id'] ) . '" value="' . esc_attr( $value ) . '" data-frmval="' . esc_attr( $value ) . '"/>' . "\n";
	}

	public function validate( $args ) {
		if ( '' == $args['value'] ) {
			return array();
		}

		// make sure we have a user ID
		if ( ! is_numeric( $args['value'] ) ) {
			$args['value'] = FrmAppHelper::get_user_id_param( $args['value'] );
			FrmEntriesHelper::set_posted_value( $this->field, $args['value'], $args );
		}

		//add user id to post variables to be saved with entry
		$_POST['frm_user_id'] = $args['value'];

		return array();
	}

	/**
	 * @param $value
	 * @param $atts array
	 *
	 * @return false|mixed|string
	 */
	protected function prepare_display_value( $value, $atts ) {
		$user_info = $this->prepare_user_info_attribute( $atts );

		return FrmFieldsHelper::get_user_display_name( $value, $user_info, $atts );
	}

	/**
	 * Generate the user info attribute for displaying
	 * a value from the user ID
	 * From the get_display_name() function
	 *
	 * @since 3.0
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	private function prepare_user_info_attribute( $atts ) {
		if ( isset( $atts['show'] ) ) {
			if ( $atts['show'] === 'id' ) {
				$user_info = 'ID';
			} else {
				$user_info = $atts['show'];
			}
		} else {
			$user_info = 'display_name';
		}

		return $user_info;
	}

	/**
	 * @param $value
	 * @param $atts
	 *
	 * @return int
	 */
	protected function prepare_import_value( $value, $atts ) {
		return FrmAppHelper::get_user_id_param( trim( $value ) );
	}

	/**
	 * @since 4.0.04
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'intval', $value );
	}
}
