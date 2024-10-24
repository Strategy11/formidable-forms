<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-style-component frm-field-shape frm-radio-component">
	<div class="frm-radio-container frm-flex-box frm-flex-justify">
		<input id="frm-field-shape-regular" <?php checked( $field_value, 'regular' ); ?> type="radio" <?php echo esc_attr( $field_name ); ?> value="regular" />
		<label class="frm-flex-center" for="frm-field-shape-regular" tabindex="0">
			<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-square' ); ?>
		</label>

		<input data-frm-show-element="field-shape-corner-radius" id="frm-field-shape-rounded-corners" <?php checked( $field_value, 'rounded-corner' ); ?> type="radio" <?php echo esc_attr( $field_name ); ?> value="rounded-corner" />
		<label class="frm-flex-center" for="frm-field-shape-rounded-corners" tabindex="0">
			<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-rounded-square' ); ?>
		</label>

		<input id="frm-field-shape-circle" <?php checked( $field_value, 'circle' ); ?> type="radio" <?php echo esc_attr( $field_name ); ?> value="circle" />
		<label class="frm-flex-center" for="frm-field-shape-circle" tabindex="0">
			<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-circle' ); ?>
		</label>

		<input id="frm-field-shape-underline" <?php checked( $field_value, 'underline' ); ?> type="radio" <?php echo esc_attr( $field_name ); ?> value="underline" />
		<label class="frm-flex-center" for="frm-field-shape-underline" tabindex="0">
			<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-underline' ); ?>
		</label>

		<span class="frm-radio-active-tracker"></span>
	</div>
</div>

