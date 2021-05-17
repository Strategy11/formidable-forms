<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

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
	 *
	 * @param object|array $field
	 */
	public static function get_field_factory( $field ) {
		if ( is_object( $field ) ) {
			$field_info = self::get_field_object( $field );
		} elseif ( isset( $field['id'] ) && $field['id'] ) {
			$field_info = self::get_field_object( $field['id'] );
		} else {
			$field_info = self::get_field_type( $field['type'], $field );
		}

		return $field_info;
	}

	public static function get_field_object( $field ) {
		if ( ! is_object( $field ) ) {
			$field = FrmField::getOne( $field );
		}

		return self::get_field_type( $field->type, $field );
	}

	/**
	 * @since 3.0
	 *
	 * @param string $field_type
	 * @param int|array|object $field
	 *
	 * @return stdClass
	 */
	public static function get_field_type( $field_type, $field = 0 ) {
		$class = self::get_field_type_class( $field_type );
		if ( empty( $class ) ) {
			$field = new FrmFieldDefault( $field, $field_type );
		} else {
			$field = new $class( $field, $field_type );
		}

		return $field;
	}

	/**
	 * @since 3.0
	 *
	 * @param string $field_type
	 *
	 * @return string
	 */
	private static function get_field_type_class( $field_type ) {
		$type_classes = array(
			'text'     => 'FrmFieldText',
			'textarea' => 'FrmFieldTextarea',
			'select'   => 'FrmFieldSelect',
			'radio'    => 'FrmFieldRadio',
			'checkbox' => 'FrmFieldCheckbox',
			'number'   => 'FrmFieldNumber',
			'phone'    => 'FrmFieldPhone',
			'url'      => 'FrmFieldUrl',
			'website'  => 'FrmFieldUrl',
			'email'    => 'FrmFieldEmail',
			'user_id'  => 'FrmFieldUserID',
			'html'     => 'FrmFieldHTML',
			'hidden'   => 'FrmFieldHidden',
			'captcha'  => 'FrmFieldCaptcha',
			'name'     => 'FrmFieldName',
		);

		$class = isset( $type_classes[ $field_type ] ) ? $type_classes[ $field_type ] : '';

		return apply_filters( 'frm_get_field_type_class', $class, $field_type );
	}

	/**
	 * @since 3.0
	 */
	public static function field_has_html( $type ) {
		$has_html = self::field_has_property( $type, 'has_html' );

		// this hook is here for reverse compatibility since 3.0
		return apply_filters( 'frm_show_custom_html', $has_html, $type );
	}

	/**
	 * @since 3.0
	 */
	public static function field_has_property( $type, $property ) {
		$field = self::get_field_type( $type );

		return $field->{$property};
	}
}
