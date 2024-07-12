<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
if ( $component['has-multiple-values'] ) : ?>
	<div class="<?php echo esc_attr( $component_class ); ?>" <?php echo esc_attr( $component_attr )?> >
		<div class="frm-slider-component frm-has-multiple-values" data-type="vertical" data-max-value="<?php echo (int) $component['max_value']?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-margin-top-bottom' ) ?>
					<span class="frm-slider">
						<span class="frm-slider-active-track"><span class="frm-slider-bullet"></span></span>
					</span>
				</div>
				<div class="frm-slider-value">
					<input type="text" value="<?php echo (int) $component['vertical']['value']; ?>" />
					<select>
						<option <?php selected( $component['vertical']['unit'], 'px' ) ?> value="px">px</option>
						<option <?php selected( $component['vertical']['unit'], 'em' ) ?> value="em">em</option>
						<option <?php selected( $component['vertical']['unit'], '%' ) ?> value="%">%</option>
					</select>
				</div>
			</div>
		</div>
		<div class="frm-slider-component frm-has-multiple-values"  data-type="horizontal" data-max-value="<?php echo (int) $component['max_value']?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-margin-left-right' ) ?>
					<span class="frm-slider">
						<span class="frm-slider-active-track"><span class="frm-slider-bullet"></span></span>
					</span>
				</div>
				<div class="frm-slider-value">
					<input type="text" value="<?php echo (int) $component['horizontal']['value']; ?>" />
					<select>
						<option <?php selected( $component['horizontal']['unit'], 'px' ) ?> value="px">px</option>
						<option <?php selected( $component['horizontal']['unit'], 'em' ) ?> value="em">em</option>
						<option <?php selected( $component['horizontal']['unit'], '%' ) ?> value="%">%</option>
					</select>
				</div>
			</div>
		</div>
		<input type="hidden" <?php echo esc_attr( $field_name ); ?> value="<?php echo esc_attr( $field_value ); ?>" id="<?php echo esc_attr( $component['id'] ); ?>" />
	</div>
<?php else: ?>
	<div class="<?php echo esc_attr( $component_class ); ?> frm-slider-component" <?php echo esc_attr( $component_attr )?> data-max-value="<?php echo (int) $component['max_value']?>" >
		<div class="frm-flex-justify">
			<div class="frm-slider-container">
				<?php if ( ! empty( $component['icon'] ) ) : ?>
					<?php FrmAppHelper::icon_by_class( $component['icon'] ); ?>
				<?php endif; ?>
				<span class="frm-slider">
					<span class="frm-slider-active-track"><span class="frm-slider-bullet"></span></span>
				</span>
			</div>
			<div class="frm-slider-value">
				<input type="text" value="<?php echo (int) $field_value; ?>" />
				<input type="hidden" <?php echo esc_attr( $field_name ); ?> value="<?php echo esc_attr( $field_value ); ?>" id="<?php echo esc_attr( $component['id'] ); ?>" />
				<select>
					<option <?php selected( $component['unit_measurement'], 'px' ) ?> value="px">px</option>
					<option <?php selected( $component['unit_measurement'], 'em' ) ?> value="em">em</option>
					<option <?php selected( $component['unit_measurement'], '%' ) ?> value="%">%</option>
				</select>
			</div>
		</div>
	</div>
<?php endif; ?>