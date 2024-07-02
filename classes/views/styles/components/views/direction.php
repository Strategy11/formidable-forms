<span class="frm-style-component frm-direction-component frm-radio-component">
	<div class="frm-radio-container frm-flex-box frm-flex-justify">
		<input id="frm-direction-left-<?php echo esc_attr( $field_name ); ?>" <?php checked( $field_value, 'left' ); ?>  type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="ltr" />
		<label for="frm-direction-left-<?php echo esc_attr( $field_name ); ?>">
			<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-direction-left' ); ?>
		</label>

		<input id="frm-direction-right-<?php echo esc_attr( $field_name ); ?>" <?php checked( $field_value, 'right' ); ?> type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="rtl" />
		<label for="frm-direction-right-<?php echo esc_attr( $field_name ); ?>">
			<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-direction-right' ); ?>
		</label>
		<span class="frm-radio-active-tracker"></span>
	</div>
</span>	