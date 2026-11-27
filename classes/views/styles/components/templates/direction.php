<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$left_id  = ! empty( $component['id'] ) ? $component['id'] . '-left' : 'frm-direction-left-' . $field_name;
$right_id = ! empty( $component['id'] ) ? $component['id'] . '-right' : 'frm-direction-right-' . $field_name;
?>
<span class="<?php echo esc_attr( $component_class ); ?> frm-direction-component frm-radio-component">
	<div class="frm-radio-container frm-flex-box frm-flex-justify">
		<input aria-label="<?php esc_attr_e( 'Left to Right', 'formidable' ); ?>" id="<?php echo esc_attr( $left_id ); ?>" <?php checked( $field_value, 'ltr' ); ?>  type="radio" <?php echo esc_attr( $field_name ); ?> value="ltr" />
		<label aria-label="<?php esc_attr_e( 'Left to Right', 'formidable' ); ?>" class="frm-flex-center" for="<?php echo esc_attr( $left_id ); ?>" tabindex="0">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm-direction-right' ); ?>
		</label>

		<input aria-label="<?php esc_attr_e( 'Right to Left', 'formidable' ); ?>" id="<?php echo esc_attr( $right_id ); ?>" <?php checked( $field_value, 'rtl' ); ?> type="radio" <?php echo esc_attr( $field_name ); ?> value="rtl" />
		<label aria-label="<?php esc_attr_e( 'Right to Left', 'formidable' ); ?>" class="frm-flex-center" for="<?php echo esc_attr( $right_id ); ?>" tabindex="0">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm-direction-left' ); ?>
		</label>
		<span class="frm-radio-active-tracker"></span>
	</div>
</span>	