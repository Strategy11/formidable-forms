<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-style-component frm-field-shape frm-radio-component">
	<div class="frm-radio-container frm-flex-box frm-flex-justify">
		<input id="frm-field-shape-regular" <?php checked( $field_value, 'regular' ); ?> type="radio" <?php echo esc_attr( $field_name ); ?> value="regular" />
		<label class="frm-flex-center" for="frm-field-shape-regular">
			<span tabindex="0" role="radio" aria-checked="<?php echo esc_attr( $field_value === 'regular' ? 'true' : 'false' ); ?>" aria-label="<?php esc_attr_e( 'Regular', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm-square' ); ?>
			</span>
		</label>

		<input data-frm-show-element="field-shape-corner-radius" id="frm-field-shape-rounded-corners" <?php checked( $field_value, 'rounded-corner' ); ?> type="radio" <?php echo esc_attr( $field_name ); ?> value="rounded-corner" />
		<label class="frm-flex-center" for="frm-field-shape-rounded-corners">
			<span tabindex="0" role="radio" aria-checked="<?php echo esc_attr( $field_value === 'rounded-corner' ? 'true' : 'false' ); ?>" aria-label="<?php esc_attr_e( 'Rounded corners', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm-rounded-square' ); ?>
			</span>
		</label>

		<input id="frm-field-shape-circle" <?php checked( $field_value, 'circle' ); ?> type="radio" <?php echo esc_attr( $field_name ); ?> value="circle" />
		<label class="frm-flex-center" for="frm-field-shape-circle">
			<span tabindex="0" role="radio" aria-checked="<?php echo esc_attr( $field_value === 'circle' ? 'true' : 'false' ); ?>" aria-label="<?php esc_attr_e( 'Circle', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm-circle' ); ?>
			</span>
		</label>

		<input id="frm-field-shape-underline" <?php checked( $field_value, 'underline' ); ?> type="radio" <?php echo esc_attr( $field_name ); ?> value="underline" />
		<label class="frm-flex-center" for="frm-field-shape-underline">
			<span tabindex="0" role="radio" aria-checked="<?php echo esc_attr( $field_value === 'underline' ? 'true' : 'false' ); ?>" aria-label="<?php esc_attr_e( 'Underline', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm-underline' ); ?>
			</span>
		</label>

		<span class="frm-radio-active-tracker"></span>
	</div>
</div>
