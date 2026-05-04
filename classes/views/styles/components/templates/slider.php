<?php
/**
 * Slider style component template.
 *
 * @since 6.14
 *
 * @package Formidable
 *
 * @var string      $component_attr  HTML attribute string for the outer wrapper element.
 * @var string      $component_class CSS class string for the outer wrapper element.
 * @var array       $component       {
 *     Slider configuration data set by FrmSliderStyleComponent.
 *
 *     @type bool         $has-multiple-values Whether the slider controls multiple values (top/bottom, left/right).
 *     @type int          $max_value           Maximum slider value. Default 100.
 *     @type string       $unit_measurement    Active unit: 'px', 'em', '%', or ''.
 *     @type string[]     $units               Available unit options.
 *     @type float|string $value_label         Numeric display value, stripped of unit.
 *     @type string       $icon                Optional icon CSS class string.
 *     @type string       $id                  HTML id for the hidden input.
 *     @type array[]|null $independent_fields  Optional. Each item has keys: name, value, id, type.
 *     @type array        $vertical            When $has-multiple-values: keys value and unit.
 *     @type array        $horizontal          When $has-multiple-values: keys value and unit.
 *     @type array        $top                 When $has-multiple-values: keys value and unit.
 *     @type array        $bottom              When $has-multiple-values: keys value and unit.
 *     @type array        $left                When $has-multiple-values: keys value and unit.
 *     @type array        $right               When $has-multiple-values: keys value and unit.
 * }
 * @var string      $field_name  HTML name attribute string (e.g. 'name="frm_style_setting[post_content][font_size]"').
 * @var string      $field_value Raw field value including unit (e.g. '13px' or '10px 20px 10px 20px').
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( $component['has-multiple-values'] ) : ?>
	<div class="<?php echo esc_attr( $component_class ); ?>" <?php echo esc_attr( $component_attr ); ?> >
		<div class="frm-slider-component frm-has-multiple-values frm-group-sliders" data-display-sliders="top,bottom" data-type="vertical" data-max-value="<?php echo (int) $component['max_value']; ?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-top-bottom' ); ?>
					<input type="range" class="frm-slider" min="0" max="<?php echo (int) $component['max_value']; ?>" value="<?php echo esc_attr( $component['vertical']['value'] ); ?>" />
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php esc_attr_e( 'Vertical value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['vertical']['value'] ); ?>" />
					<select aria-label="<?php esc_attr_e( 'Value unit', 'formidable' ); ?>">
						<?php foreach ( $component['units'] as $unit ) : ?>
							<option <?php selected( $component['vertical']['unit'], $unit ); ?> value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="frm-slider-component frm-has-multiple-values frm_hidden" data-type="top" data-max-value="<?php echo (int) $component['max_value']; ?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-top' ); ?>
					<input type="range" class="frm-slider" min="0" max="<?php echo (int) $component['max_value']; ?>" value="<?php echo esc_attr( $component['top']['value'] ); ?>" />
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php esc_attr_e( 'Top value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['top']['value'] ); ?>" />
					<select aria-label="<?php esc_attr_e( 'Value unit', 'formidable' ); ?>">
						<?php foreach ( $component['units'] as $unit ) : ?>
							<option <?php selected( $component['top']['unit'], $unit ); ?> value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="frm-slider-component frm-has-multiple-values frm_hidden" data-type="bottom" data-max-value="<?php echo (int) $component['max_value']; ?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-bottom' ); ?>
					<input type="range" class="frm-slider" min="0" max="<?php echo (int) $component['max_value']; ?>" value="<?php echo esc_attr( $component['bottom']['value'] ); ?>" />
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php esc_attr_e( 'Bottom value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['bottom']['value'] ); ?>" />
					<select aria-label="<?php esc_attr_e( 'Value unit', 'formidable' ); ?>">
						<?php foreach ( $component['units'] as $unit ) : ?>
							<option <?php selected( $component['bottom']['unit'], $unit ); ?> value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="frm-slider-component frm-has-multiple-values frm-group-sliders" data-display-sliders="left,right" data-type="horizontal" data-max-value="<?php echo (int) $component['max_value']; ?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-left-right' ); ?>
					<input type="range" class="frm-slider" min="0" max="<?php echo (int) $component['max_value']; ?>" value="<?php echo esc_attr( $component['horizontal']['value'] ); ?>" />
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php esc_attr_e( 'Horizontal value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['horizontal']['value'] ); ?>" />
					<select aria-label="<?php esc_attr_e( 'Value unit', 'formidable' ); ?>">
						<?php foreach ( $component['units'] as $unit ) : ?>
							<option <?php selected( $component['horizontal']['unit'], $unit ); ?> value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="frm-slider-component frm-has-multiple-values frm_hidden" data-type="left" data-max-value="<?php echo (int) $component['max_value']; ?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-left' ); ?>
					<input type="range" class="frm-slider" min="0" max="<?php echo (int) $component['max_value']; ?>" value="<?php echo esc_attr( $component['left']['value'] ); ?>" />
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php esc_attr_e( 'Left value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['left']['value'] ); ?>" />
					<select aria-label="<?php esc_attr_e( 'Value unit', 'formidable' ); ?>">
						<?php foreach ( $component['units'] as $unit ) : ?>
							<option <?php selected( $component['left']['unit'], $unit ); ?> value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="frm-slider-component frm-has-multiple-values frm_hidden" data-type="right" data-max-value="<?php echo (int) $component['max_value']; ?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-right' ); ?>
					<input type="range" class="frm-slider" min="0" max="<?php echo (int) $component['max_value']; ?>" value="<?php echo esc_attr( $component['right']['value'] ); ?>" />
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php esc_attr_e( 'Right value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['right']['value'] ); ?>" />
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
			<div class="frm-slider-component <?php echo esc_attr( $component_class ); ?>" <?php echo esc_attr( $component_attr ); ?> data-display-sliders="top,bottom" data-type="vertical" data-max-value="<?php echo (int) $component['max_value']; ?>">
				<div class="frm-flex-justify">
					<div class="frm-slider-container">
						<?php if ( ! empty( $component['icon'] ) ) : ?>
							<?php FrmAppHelper::icon_by_class( $component['icon'] ); ?>
						<?php endif; ?>
						<input type="range" class="frm-slider" min="0" max="<?php echo (int) $component['max_value']; ?>" value="<?php echo esc_attr( $component['value_label'] ); ?>" />
					</div>
					<div class="frm-slider-value">
						<input aria-label="<?php esc_attr_e( 'Field value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['value_label'] ); ?>" />
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
				<div class="frm-slider-component frm-group-sliders frm-has-independent-fields" data-display-sliders="top,bottom" data-max-value="<?php echo (int) $component['max_value']; ?>">
					<div class="frm-flex-justify">
						<div class="frm-slider-container">
							<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-top-bottom' ); ?>
							<input type="range" class="frm-slider" min="0" max="<?php echo (int) $component['max_value']; ?>" value="<?php echo esc_attr( $component['value_label'] ); ?>" />
						</div>
						<div class="frm-slider-value">
							<input aria-label="<?php esc_attr_e( 'Field value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['value_label'] ); ?>" />
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
						<div class="frm-slider-component frm-independent-slider-field frm_hidden" data-type="<?php echo esc_attr( $field['type'] ); ?>" data-max-value="<?php echo (int) $component['max_value']; ?>">
							<div class="frm-flex-justify">
								<div class="frm-slider-container">
									<?php if ( ! empty( $component['icon'] ) ) : ?>
										<?php FrmAppHelper::icon_by_class( 'frmfont frm-margin-' . $field['type'] ); ?>
									<?php endif; ?>
									<input type="range" class="frm-slider" min="0" max="<?php echo (int) $component['max_value']; ?>" value="<?php echo ! empty( $component['unit_measurement'] ) ? (int) $field['value'] : esc_attr( $field['value'] ); ?>" />
								</div>
								<div class="frm-slider-value">
									<input aria-label="<?php esc_attr_e( 'Field value', 'formidable' ); ?>" type="text" value="<?php echo ! empty( $component['unit_measurement'] ) ? (int) $field['value'] : esc_attr( $field['value'] ); ?>" />
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
