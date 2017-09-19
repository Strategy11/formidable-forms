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

	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-user-id.php';
	}

	protected function prepare_display_value( $value, $atts ) {
		$user_info = $this->prepare_user_info_attribute( $atts );
		return FrmFieldsHelper::get_user_display_name( $value, $user_info, $atts );
	}

	/**
	 * Generate the user info attribute for the get_display_name() function
	 *
	 * @since 3.0
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

	protected function prepare_import_value( $value, $atts ) {
		return FrmAppHelper::get_user_id_param( trim( $value ) );
	}
}
