<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$left_value  = $component['left_value'] ?? '';
$right_value = $component['right_value'] ?? '';
?>
<div class="<?php echo esc_attr( $component_class ); ?> frm-text-toggle-component frm-radio-component">
	<div class="frm-radio-container frm-flex-justify">
		<input
			type="radio"
			<?php echo esc_attr( $field_name ); ?>
			id="frm-text-toggle-left-<?php echo esc_attr( $field_name ); ?>"
			value="<?php echo esc_attr( $left_value ); ?>"
			<?php checked( empty( $field_value ) || $left_value === $field_value, true ); ?>
		/>
		<label class="frm-flex-center" for="frm-text-toggle-left-<?php echo esc_attr( $field_name ); ?>" tabindex="0">
			<span class="frm-toggle-label"><?php echo esc_html( $component['left_label'] ?? '' ); ?></span>
		</label>

		<input
			type="radio"
			<?php echo esc_attr( $field_name ); ?>
			id="frm-text-toggle-right-<?php echo esc_attr( $field_name ); ?>"
			value="<?php echo esc_attr( $right_value ); ?>"
			<?php checked( $right_value === $field_value, true ); ?>
		/>
		<label class="frm-flex-center" for="frm-text-toggle-right-<?php echo esc_attr( $field_name ); ?>" tabindex="0">
			<span class="frm-toggle-label"><?php echo esc_html( $component['right_label'] ?? '' ); ?></span>
		</label>

		<span class="frm-radio-active-tracker"></span>
	</div>
</div>
