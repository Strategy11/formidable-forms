<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$options = $component['options'] ?? array();
?>
<div class="frm-text-toggle-component frm-radio-component <?php echo esc_attr( $component_class ); ?>">
	<div class="frm-radio-container frm-flex-justify">
		<?php
		$is_default_checked = isset( $component['default_value'] ) ? empty( $field_value ) && '' !== $component['default_value'] : empty( $field_value );

		foreach ( $options as $index => $option ) {
			$input_attrs = array(
				'id'    => 'frm-text-toggle-' . $field_name . '-' . $index,
				'value' => $option['value'] ?? '',
			);

			if ( empty( $component['input_attrs_str'] ) ) {
				$input_attrs['type'] = 'radio';
			}
			?>
			<input
				<?php
				echo $component['input_attrs_str'] ?? $field_name; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				FrmAppHelper::array_to_html_params( $input_attrs, true );
				checked( ( $is_default_checked && 0 === $index ) || $option['value'] == $field_value, true );
				?>
			/>
			<label class="frm-force-flex-center <?php echo esc_attr( $option['classes'] ?? '' ); ?>" for="<?php echo esc_attr( $input_attrs['id'] ); ?>" tabindex="0" data-value="<?php echo esc_attr( $input_attrs['value'] ); ?>" <?php echo $option['custom_attrs'] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<span class="frm-toggle-label"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
			</label>
			<?php
		}//end foreach
		?>

		<span class="frm-radio-active-tracker"></span>
	</div>
</div>
