<?php
/**
 * Slider style component template.
 *
 * @since 6.14
 *
 * @package Formidable
 *
 * @var string $component_attr  HTML attribute string for the outer wrapper element.
 * @var string $component_class CSS class string for the outer wrapper element.
 * @var array  $component       Slider configuration data set by FrmSliderStyleComponent.
 * @var string $field_name      HTML name attribute string (e.g. 'name="frm_style_setting[post_content][font_size]"').
 * @var string $field_value     Raw field value including unit (e.g. '13px' or '10px 20px 10px 20px').
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! empty( $component['has-multiple-values'] ) ) : ?>
	<div class="<?php echo esc_attr( $component_class ); ?>" <?php echo esc_attr( $component_attr ); ?> >
		<div class="frm-slider-component frm-has-multiple-values frm-group-sliders<?php echo esc_attr( $this->disabled_class( $component['vertical']['unit'] ) ); ?>" data-display-sliders="top,bottom" data-type="vertical" data-max-value="<?php echo (int) $component['max_value']; ?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-top-bottom' ); ?>
					<?php $this->print_range_input( __( 'Vertical value', 'formidable' ), $component['vertical']['value'], $component['vertical']['unit'] ); ?>
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php esc_attr_e( 'Vertical value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['vertical']['value'] ); ?>" <?php disabled( ! $this->is_measured_unit( $component['vertical']['unit'] ) ); ?> />
					<select aria-label="<?php esc_attr_e( 'Value unit', 'formidable' ); ?>">
						<?php foreach ( $component['units'] as $unit ) : ?>
							<option <?php selected( $component['vertical']['unit'], $unit ); ?> value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="frm-slider-component frm-has-multiple-values frm_hidden<?php echo esc_attr( $this->disabled_class( $component['top']['unit'] ) ); ?>" data-type="top" data-max-value="<?php echo (int) $component['max_value']; ?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-top' ); ?>
					<?php $this->print_range_input( __( 'Top value', 'formidable' ), $component['top']['value'], $component['top']['unit'] ); ?>
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php esc_attr_e( 'Top value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['top']['value'] ); ?>" <?php disabled( ! $this->is_measured_unit( $component['top']['unit'] ) ); ?> />
					<select aria-label="<?php esc_attr_e( 'Value unit', 'formidable' ); ?>">
						<?php foreach ( $component['units'] as $unit ) : ?>
							<option <?php selected( $component['top']['unit'], $unit ); ?> value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="frm-slider-component frm-has-multiple-values frm_hidden<?php echo esc_attr( $this->disabled_class( $component['bottom']['unit'] ) ); ?>" data-type="bottom" data-max-value="<?php echo (int) $component['max_value']; ?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-bottom' ); ?>
					<?php $this->print_range_input( __( 'Bottom value', 'formidable' ), $component['bottom']['value'], $component['bottom']['unit'] ); ?>
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php esc_attr_e( 'Bottom value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['bottom']['value'] ); ?>" <?php disabled( ! $this->is_measured_unit( $component['bottom']['unit'] ) ); ?> />
					<select aria-label="<?php esc_attr_e( 'Value unit', 'formidable' ); ?>">
						<?php foreach ( $component['units'] as $unit ) : ?>
							<option <?php selected( $component['bottom']['unit'], $unit ); ?> value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="frm-slider-component frm-has-multiple-values frm-group-sliders<?php echo esc_attr( $this->disabled_class( $component['horizontal']['unit'] ) ); ?>" data-display-sliders="left,right" data-type="horizontal" data-max-value="<?php echo (int) $component['max_value']; ?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-left-right' ); ?>
					<?php $this->print_range_input( __( 'Horizontal value', 'formidable' ), $component['horizontal']['value'], $component['horizontal']['unit'] ); ?>
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php esc_attr_e( 'Horizontal value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['horizontal']['value'] ); ?>" <?php disabled( ! $this->is_measured_unit( $component['horizontal']['unit'] ) ); ?> />
					<select aria-label="<?php esc_attr_e( 'Value unit', 'formidable' ); ?>">
						<?php foreach ( $component['units'] as $unit ) : ?>
							<option <?php selected( $component['horizontal']['unit'], $unit ); ?> value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="frm-slider-component frm-has-multiple-values frm_hidden<?php echo esc_attr( $this->disabled_class( $component['left']['unit'] ) ); ?>" data-type="left" data-max-value="<?php echo (int) $component['max_value']; ?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-left' ); ?>
					<?php $this->print_range_input( __( 'Left value', 'formidable' ), $component['left']['value'], $component['left']['unit'] ); ?>
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php esc_attr_e( 'Left value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['left']['value'] ); ?>" <?php disabled( ! $this->is_measured_unit( $component['left']['unit'] ) ); ?> />
					<select aria-label="<?php esc_attr_e( 'Value unit', 'formidable' ); ?>">
						<?php foreach ( $component['units'] as $unit ) : ?>
							<option <?php selected( $component['left']['unit'], $unit ); ?> value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="frm-slider-component frm-has-multiple-values frm_hidden<?php echo esc_attr( $this->disabled_class( $component['right']['unit'] ) ); ?>" data-type="right" data-max-value="<?php echo (int) $component['max_value']; ?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-right' ); ?>
					<?php $this->print_range_input( __( 'Right value', 'formidable' ), $component['right']['value'], $component['right']['unit'] ); ?>
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php esc_attr_e( 'Right value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['right']['value'] ); ?>" <?php disabled( ! $this->is_measured_unit( $component['right']['unit'] ) ); ?> />
					<select aria-label="<?php esc_attr_e( 'Value unit', 'formidable' ); ?>">
						<?php foreach ( $component['units'] as $unit ) : ?>
							<option <?php selected( $component['right']['unit'], $unit ); ?> value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<input type="hidden" <?php echo esc_attr( $field_name ); ?> value="<?php echo esc_attr( $field_value ); ?>" id="<?php echo esc_attr( $component['id'] ); ?>" />
	</div>
<?php else : ?>
	<div>
		<?php if ( empty( $component['independent_fields'] ) ) : ?>
			<div class="frm-slider-component <?php echo esc_attr( $component_class . $this->disabled_class( $component['unit_measurement'] ) ); ?>" <?php echo esc_attr( $component_attr ); ?> data-display-sliders="top,bottom" data-type="vertical" data-max-value="<?php echo (int) $component['max_value']; ?>">
				<div class="frm-flex-justify">
					<div class="frm-slider-container">
						<?php if ( ! empty( $component['icon'] ) ) : ?>
							<?php FrmAppHelper::icon_by_class( $component['icon'] ); ?>
						<?php endif; ?>
						<?php $this->print_range_input( __( 'Field value', 'formidable' ), $component['value_label'], $component['unit_measurement'] ); ?>
					</div>
					<div class="frm-slider-value">
						<input aria-label="<?php esc_attr_e( 'Field value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['value_label'] ); ?>" <?php disabled( ! $this->is_measured_unit( $component['unit_measurement'] ) ); ?> />
						<input type="hidden" <?php echo esc_attr( $field_name ); ?> value="<?php echo esc_attr( $field_value ); ?>" id="<?php echo esc_attr( $component['id'] ); ?>" />
						<select aria-label="<?php esc_attr_e( 'Value unit', 'formidable' ); ?>">
							<?php foreach ( $component['units'] as $unit ) : ?>
								<option <?php selected( $component['unit_measurement'], $unit ); ?> value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
		<?php else : ?>
			<div class="<?php echo esc_attr( $component_class ); ?>" <?php echo esc_attr( $component_attr ); ?> >
				<div class="frm-slider-component frm-group-sliders frm-has-independent-fields<?php echo esc_attr( $this->disabled_class( $component['unit_measurement'] ) ); ?>" data-display-sliders="top,bottom" data-max-value="<?php echo (int) $component['max_value']; ?>">
					<div class="frm-flex-justify">
						<div class="frm-slider-container">
							<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-top-bottom' ); ?>
							<?php $this->print_range_input( __( 'Field value', 'formidable' ), $component['value_label'], $component['unit_measurement'] ); ?>
						</div>
						<div class="frm-slider-value">
							<input aria-label="<?php esc_attr_e( 'Field value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['value_label'] ); ?>" <?php disabled( ! $this->is_measured_unit( $component['unit_measurement'] ) ); ?> />
							<select aria-label="<?php esc_attr_e( 'Value unit', 'formidable' ); ?>">
								<?php foreach ( $component['units'] as $unit ) : ?>
									<option <?php selected( $component['unit_measurement'], $unit ); ?> value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<?php
					foreach ( $component['independent_fields'] as $field ) :
						?>
						<div class="frm-slider-component frm-independent-slider-field frm_hidden<?php echo esc_attr( $this->disabled_class( $component['unit_measurement'] ) ); ?>" data-type="<?php echo esc_attr( $field['type'] ); ?>" data-max-value="<?php echo (int) $component['max_value']; ?>">
							<div class="frm-flex-justify">
								<div class="frm-slider-container">
									<?php if ( ! empty( $component['icon'] ) ) : ?>
										<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-' . $field['type'] ); ?>
									<?php endif; ?>
									<?php $this->print_range_input( $this->get_label_for_type( $field['type'] ), $field['value'], $component['unit_measurement'] ); ?>
								</div>
								<div class="frm-slider-value">
									<input aria-label="<?php echo esc_attr( $this->get_label_for_type( $field['type'] ) ); ?>" type="text" value="<?php echo $this->is_measured_unit( $component['unit_measurement'] ) ? (int) $field['value'] : ''; ?>" <?php disabled( ! $this->is_measured_unit( $component['unit_measurement'] ) ); ?> />
									<input type="hidden" name="<?php echo esc_attr( $field['name'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" />
									<select aria-label="<?php esc_attr_e( 'Value unit', 'formidable' ); ?>">
										<?php foreach ( $component['units'] as $unit ) : ?>
											<option <?php selected( $component['unit_measurement'], $unit ); ?> value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
