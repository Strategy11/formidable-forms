<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$options = $component['options'] ?? array();
?>
<div class="frm-text-toggle-component frm-radio-component <?php echo esc_attr( $component_class ); ?>">
	<div class="frm-radio-container frm-flex-justify">
		<?php
		$is_default_checked = empty( $field_value );

		foreach ( $options as $index => $option ) {
			$input_id     = 'frm-text-toggle-' . $field_name . '-' . $index;
			$option_value = $option['value'] ?? '';
			$is_checked   = ( $is_default_checked && 0 === $index ) || $option_value === $field_value;
			?>
			<input
				type="radio"
				<?php echo esc_attr( $field_name ); ?>
				id="<?php echo esc_attr( $input_id ); ?>"
				value="<?php echo esc_attr( $option_value ); ?>"
				<?php checked( $is_checked, true ); ?>
			/>
			<label class="frm-flex-center" for="<?php echo esc_attr( $input_id ); ?>" tabindex="0">
				<span class="frm-toggle-label"><?php echo esc_html( $option['label'] ?? '' ); ?></span>
			</label>
		<?php } ?>

		<span class="frm-radio-active-tracker"></span>
	</div>
</div>
