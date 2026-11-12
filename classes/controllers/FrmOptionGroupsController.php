<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Option groups let dropdown options be organized under labeled headings.
 *
 * The group name is stored on each option as a 'group' key next to 'label' and 'value',
 * so anything that reads options without knowing about groups keeps working unchanged.
 *
 * @since x.x
 */
class FrmOptionGroupsController {

	/**
	 * A line in the Bulk Edit Options text that starts with this marker begins a new group.
	 * The marker on its own ends the current group.
	 *
	 * @var string
	 */
	const BULK_EDIT_MARKER = '##';

	/**
	 * The label of the optgroup that is currently open while a dropdown is printed.
	 *
	 * @var string
	 */
	private static $open_group = '';

	/**
	 * @since x.x
	 *
	 * @param array|object $field The field settings.
	 *
	 * @return bool
	 */
	public static function field_supports_groups( $field ) {
		$type = is_array( $field ) ? ( $field['type'] ?? '' ) : $field->type;

		if ( 'select' !== $type ) {
			return false;
		}

		$post_field = is_array( $field ) ? ( $field['post_field'] ?? '' ) : FrmField::get_option( $field, 'post_field' );
		return 'post_category' !== $post_field;
	}

	/**
	 * @since x.x
	 *
	 * @param array      $field   The field settings.
	 * @param int|string $opt_key The option key.
	 *
	 * @return string
	 */
	public static function get_option_group( $field, $opt_key ) {
		$option = $field['options'][ $opt_key ] ?? '';

		if ( ! is_array( $option ) || ! isset( $option['group'] ) || ! is_string( $option['group'] ) ) {
			return '';
		}

		return trim( $option['group'] );
	}

	/**
	 * Opens or closes an optgroup when the group changes between two options.
	 *
	 * @since x.x
	 *
	 * @param array      $field   The field settings.
	 * @param int|string $opt_key The option key.
	 *
	 * @return void
	 */
	public static function before_dropdown_option( $field, $opt_key ) {
		if ( ! self::field_supports_groups( $field ) ) {
			return;
		}

		$group = self::get_option_group( $field, $opt_key );

		if ( $group === self::$open_group ) {
			return;
		}

		self::close_open_group();

		if ( '' === $group ) {
			return;
		}

		echo '<optgroup label="' . esc_attr( $group ) . '">';
		self::$open_group = $group;
	}

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	public static function after_dropdown_options() {
		self::close_open_group();
	}

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	private static function close_open_group() {
		if ( '' === self::$open_group ) {
			return;
		}

		echo '</optgroup>';
		self::$open_group = '';
	}

	/**
	 * Adds the hidden group input to each option row in the builder.
	 *
	 * @since x.x
	 *
	 * @param array $args Includes the field array and the option key.
	 *
	 * @return void
	 */
	public static function admin_single_opt( $args ) {
		$field = $args['field'];

		if ( ! self::field_supports_groups( $field ) || FrmFieldsHelper::is_other_opt( $args['opt_key'] ) ) {
			return;
		}

		$input_atts = array(
			'type'  => 'hidden',
			'name'  => 'field_options[options_' . $field['id'] . '][' . $args['opt_key'] . '][group]',
			'value' => self::get_option_group( $field, $args['opt_key'] ),
			'class' => 'frm-option-group-input',
		);
		?>
		<input <?php FrmAppHelper::array_to_html_params( $input_atts, true ); ?> />
		<?php
	}

	/**
	 * Turns "## Group name" lines from the Bulk Edit Options text into groups
	 * for the options that follow them.
	 *
	 * @since x.x
	 *
	 * @param array $opts  One item per line, either a string or an array with 'label' and 'value'.
	 * @param array $field The field the options belong to.
	 *
	 * @return array
	 */
	public static function bulk_edit_options( $opts, $field ) {
		if ( ! is_array( $opts ) || ! self::field_supports_groups( $field ) ) {
			return $opts;
		}

		$grouped_opts = array();
		$group        = '';

		foreach ( $opts as $opt ) {
			$label = is_array( $opt ) ? (string) ( $opt['label'] ?? '' ) : (string) $opt;

			if ( str_starts_with( $label, self::BULK_EDIT_MARKER ) ) {
				$group = trim( substr( $label, strlen( self::BULK_EDIT_MARKER ) ) );
				continue;
			}

			if ( '' === $group ) {
				$grouped_opts[] = $opt;
				continue;
			}

			if ( ! is_array( $opt ) ) {
				$opt = array(
					'label' => $opt,
					'value' => $opt,
				);
			}

			$opt['group']   = $group;
			$grouped_opts[] = $opt;
		}//end foreach

		return $grouped_opts;
	}
}
