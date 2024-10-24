<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<span class="<?php echo esc_attr( $component_class ); ?> frm-direction-component frm-radio-component">
	<div class="frm-radio-container frm-flex-box frm-flex-justify">
		<input id="frm-direction-left-<?php echo esc_attr( $field_name ); ?>" <?php checked( $field_value, 'ltr' ); ?>  type="radio" <?php echo esc_attr( $field_name ); ?> value="ltr" />
		<label class="frm-flex-center" for="frm-direction-left-<?php echo esc_attr( $field_name ); ?>" tabindex="0">
			<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-direction-right' ); ?>
		</label>

		<input id="frm-direction-right-<?php echo esc_attr( $field_name ); ?>" <?php checked( $field_value, 'rtl' ); ?> type="radio" <?php echo esc_attr( $field_name ); ?> value="rtl" />
		<label class="frm-flex-center" for="frm-direction-right-<?php echo esc_attr( $field_name ); ?>" tabindex="0">
			<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-direction-left' ); ?>
		</label>
		<span class="frm-radio-active-tracker"></span>
	</div>
</span>	