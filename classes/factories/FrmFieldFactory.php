<?php

/**
 * @since 2.03.05
 */
class FrmFieldFactory {

	/**
	 * Create an instance of an FrmFieldValueSelector object
	 *
	 * @since 2.03.05
	 *
	 * @param int $field_id
	 * @param array $args
	 *
	 * @return FrmFieldValueSelector
	 */
	public static function create_field_value_selector( $field_id, $args ) {
		$selector = null;

		if ( $field_id > 0 ) {
			$selector = apply_filters( 'frm_create_field_value_selector', $selector, $field_id, $args );
		}

		if ( ! is_object( $selector ) ) {
			$selector = new FrmFieldValueSelector( $field_id, $args );
		}

		return $selector;
	}

	/**
	 * @since 3.0
	 */
	public static function get_field_type( $field_type ) {
		switch ( $field_type ) {
			case 'text':
				$field = new FrmFieldText();
			break;
			case 'textarea':
				$field = new FrmFieldTextarea();
			break;
			case 'select':
				$field = new FrmFieldSelect();
			break;
			case 'radio':
				$field = new FrmFieldRadio();
			break;
			case 'checkbox':
				$field = new FrmFieldCheckbox();
			break;
			case 'number':
				$field = new FrmFieldNumber();
			break;
			case 'phone':
				$field = new FrmFieldPhone();
			break;
			case 'url':
			case 'website':
				$field = new FrmFieldUrl();
			break;
			case 'email':
				$field = new FrmFieldEmail();
			break;
			case 'user_id':
				$field = new FrmFieldUserID();
			break;
			case 'html':
				$field = new FrmFieldHTML();
			break;
			case 'hidden':
				$field = new FrmFieldHidden();
			break;
			case 'captcha':
				$field = new FrmFieldCaptcha();
			break;
			default:
				$field = apply_filters( 'frm_default_html_object_' . $field_type, null );
		}

		if ( ! is_object( $field ) ) {
			$field = new FrmFieldType( $field_type );
		}

		return $field;
	}

	/**
	 * @since 3.0
	 */
	public static function field_has_html( $type ) {
		$field = self::get_field_type( $type );
		$has_html = $field->has_html;

		// this hook is here for reverse compatibility since 3.0
		$has_html = apply_filters( 'frm_show_custom_html', $has_html, $type );

		return $has_html;
	}
}
