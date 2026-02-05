<?php
/**
 * Dropdown select view
 *
 * @since 4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @var array $html_attrs HTML attributes for the select element
 * @var array $args       Dropdown arguments including source, placeholder, selected, and truncate
 */
?>
<select <?php FrmAppHelper::array_to_html_params( $html_attrs, true ); ?>>
	<option value=""><?php echo esc_html( $args['placeholder'] ); ?></option>
	<?php
	foreach ( $args['source'] as $key => $source ) :
		/**
		 * @var array $value_label Contains value and label for the option
		 */
		$value_label = FrmAppHelper::get_dropdown_value_and_label_from_option( $source, $key, $args );

		if ( ! empty( $args['truncate'] ) ) {
			$value_label['label'] = FrmAppHelper::truncate( $value_label['label'], $args['truncate'] );
		}
		?>
		<option value="<?php echo esc_attr( $value_label['value'] ); ?>" <?php selected( $value_label['value'], $args['selected'] ); ?>><?php echo esc_html( $value_label['label'] ); ?></option><?php // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong ?>
	<?php endforeach; ?>
</select>
