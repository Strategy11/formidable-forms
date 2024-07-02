<span class="frm-style-component frm-align-component frm-radio-component">
	<div class="frm-radio-container frm-flex-box frm-flex-justify">
		<input id="frm-align-left-<?php echo esc_attr( $field_name ); ?>" <?php checked( $field_value, 'left' ); ?>  type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="left" />
		<label for="frm-align-left-<?php echo esc_attr( $field_name ); ?>">
			<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-align-left' ); ?>
		</label>

		<input id="frm-align-center-<?php echo esc_attr( $field_name ); ?>" <?php checked( $field_value, 'center' ); ?> type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="center" />
		<label for="frm-align-center-<?php echo esc_attr( $field_name ); ?>">
			<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-align-center' ); ?>
		</label>

		<input id="frm-align-right-<?php echo esc_attr( $field_name ); ?>" <?php checked( $field_value, 'right' ); ?> type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="right" />
		<label for="frm-align-right-<?php echo esc_attr( $field_name ); ?>">
			<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-align-right' ); ?>
		</label>
		<span class="frm-radio-active-tracker"></span>
	</div>
</span>