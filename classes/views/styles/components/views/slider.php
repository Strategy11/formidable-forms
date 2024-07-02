<div class="frm-style-component frm-slider-component" data-max-value="100" >
	<div class="frm-flex-justify">
		<div class="frm-slider-container">
			<?php if ( ! empty( $component['icon'] ) ) : ?>
				<?php FrmAppHelper::icon_by_class( 'frm_icon_font ' . $component['icon'] ); ?>
			<?php endif; ?>
			<span class="frm-slider">
				<span class="frm-slider-active-track"><span class="frm-slider-bullet"></span></span>
			</span>
		</div>
		<div class="frm-slider-value">
			<input type="text" value="<?php echo esc_attr( $field_value ); ?>" />
			<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field_value ); ?>" id="<?php echo esc_attr( $component['id'] ); ?>" />
			<select>
				<option value="px">px</option>
				<option value="em">em</option>
				<option value="%">%</option>
			</select>
		</div>
	</div>
</div>