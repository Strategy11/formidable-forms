<?php
/**
 * Dropdown select view
 *
 * @since x.x
 *
 * @var array $html_attrs HTML attributes for the select element
 * @var array $args       Dropdown arguments including source, placeholder, selected, and truncate
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<select <?php FrmAppHelper::array_to_html_params( $html_attrs, true ); ?>>
	<option value=""><?php echo esc_html( $args['placeholder'] ); ?></option>
	<?php
	foreach ( $args['source'] as $key => $source ) :
		$value_label = FrmAppHelper::get_dropdown_value_and_label_from_option( $source, $key, $args );

		if ( ! empty( $args['truncate'] ) ) {
			$value_label['label'] = FrmAppHelper::truncate( $value_label['label'], $args['truncate'] );
		}
		?>
		<option value="<?php echo esc_attr( $value_label['value'] ); ?>" <?php selected( $value_label['value'], $args['selected'] ); ?>><?php echo esc_html( $value_label['label'] ); ?></option><?php // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong ?>
	<?php endforeach; ?>
</select>
