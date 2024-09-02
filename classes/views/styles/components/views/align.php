<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<span class="<?php echo esc_attr( $component_class ); ?>">
	<div class="frm-radio-container frm-flex-box frm-flex-justify">
		<?php if ( empty( $component['options'] ) || in_array( 'left', $component['options'], true ) ) : ?>
			<input id="frm-align-left-<?php echo esc_attr( $field_name ); ?>" <?php checked( $field_value, 'left' ); ?>  type="radio" <?php echo esc_attr( $field_name ); ?> value="left" />
			<label for="frm-align-left-<?php echo esc_attr( $field_name ); ?>" tabindex="0">
				<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-align-left' ); ?>
			</label>
		<?php endif; ?>

		<?php if ( empty( $component['options'] ) || in_array( 'center', $component['options'], true ) ) : ?>
			<input id="frm-align-center-<?php echo esc_attr( $field_name ); ?>" <?php checked( $field_value, 'center' ); ?> type="radio" <?php echo esc_attr( $field_name ); ?> value="center"/>
			<label for="frm-align-center-<?php echo esc_attr( $field_name ); ?>" tabindex="0">
				<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-align-center' ); ?>
			</label>
		<?php endif; ?>

		<?php if ( empty( $component['options'] ) || in_array( 'right', $component['options'], true ) ) : ?>
			<input id="frm-align-right-<?php echo esc_attr( $field_name ); ?>" <?php checked( $field_value, 'right' ); ?> type="radio" <?php echo esc_attr( $field_name ); ?> value="right" />
			<label for="frm-align-right-<?php echo esc_attr( $field_name ); ?>" tabindex="0">
				<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-align-right' ); ?>
			</label>
		<?php endif; ?>
		<span class="frm-radio-active-tracker"></span>
	</div>
</span>