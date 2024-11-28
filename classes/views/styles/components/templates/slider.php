<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
if ( $component['has-multiple-values'] ) : ?>
	<div class="<?php echo esc_attr( $component_class ); ?>" <?php echo esc_attr( $component_attr ); ?> >
		<div class="frm-slider-component frm-has-multiple-values frm-group-sliders" data-display-sliders="top,bottom" data-type="vertical" data-max-value="<?php echo (int) $component['max_value']; ?>">
			<div class="frm-flex-justify">
				<div class="frm-slider-container">
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-margin-top-bottom' ); ?>
					<span class="frm-slider" tabindex="0">
						<span class="frm-slider-active-track">
							<span class="frm-slider-bullet">
								<span class="frm-slider-value-label"><?php echo (int) $component['vertical']['value']; ?></span>
							</span>
						</span>
					</span>
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php echo esc_attr__( 'Vertical value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['vertical']['value'] ); ?>" />
					<select aria-label="<?php echo esc_attr__( 'Value unit', 'formidable' ); ?>">
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
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-margin-top' ); ?>
					<span class="frm-slider" tabindex="0">
						<span class="frm-slider-active-track">
							<span class="frm-slider-bullet">
								<span class="frm-slider-value-label"><?php echo (int) $component['top']['value']; ?></span>
							</span>
						</span>
					</span>
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php echo esc_attr__( 'Top value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['top']['value'] ); ?>" />
					<select aria-label="<?php echo esc_attr__( 'Value unit', 'formidable' ); ?>">
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
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-margin-bottom' ); ?>
					<span class="frm-slider" tabindex="0">
						<span class="frm-slider-active-track">
							<span class="frm-slider-bullet">
								<span class="frm-slider-value-label"><?php echo (int) $component['bottom']['value']; ?></span>
							</span>
						</span>
					</span>
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php echo esc_attr__( 'Bottom value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['bottom']['value'] ); ?>" />
					<select aria-label="<?php echo esc_attr__( 'Value unit', 'formidable' ); ?>">
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
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-margin-left-right' ); ?>
					<span class="frm-slider" tabindex="0">
						<span class="frm-slider-active-track">
							<span class="frm-slider-bullet">
								<span class="frm-slider-value-label"><?php echo (int) $component['horizontal']['value']; ?></span>
							</span>
						</span>
					</span>
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php echo esc_attr__( 'Horizontal value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['horizontal']['value'] ); ?>" />
					<select aria-label="<?php echo esc_attr__( 'Value unit', 'formidable' ); ?>">
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
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-margin-left' ); ?>
					<span class="frm-slider" tabindex="0">
						<span class="frm-slider-active-track">
							<span class="frm-slider-bullet">
								<span class="frm-slider-value-label"><?php echo (int) $component['left']['value']; ?></span>
							</span>
						</span>
					</span>
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php echo esc_attr__( 'Left value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['left']['value'] ); ?>" />
					<select aria-label="<?php echo esc_attr__( 'Value unit', 'formidable' ); ?>">
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
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-margin-right' ); ?>
					<span class="frm-slider" tabindex="0">
						<span class="frm-slider-active-track">
							<span class="frm-slider-bullet">
								<span class="frm-slider-value-label"><?php echo (int) $component['right']['value']; ?></span>
							</span>
						</span>
					</span>
				</div>
				<div class="frm-slider-value">
					<input aria-label="<?php echo esc_attr__( 'Right value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['right']['value'] ); ?>" />
					<select aria-label="<?php echo esc_attr__( 'Value unit', 'formidable' ); ?>">
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
						<span class="frm-slider" tabindex="0">
							<span class="frm-slider-active-track">
								<span class="frm-slider-bullet">
									<span class="frm-slider-value-label"><?php echo (int) $field_value; ?></span>
								</span>
							</span>
						</span>
					</div>
					<div class="frm-slider-value">
						<input aria-label="<?php echo esc_attr__( 'Field value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['value_label'] ); ?>" />
						<input type="hidden" <?php echo esc_attr( $field_name ); ?> value="<?php echo esc_attr( $field_value ); ?>" id="<?php echo esc_attr( $component['id'] ); ?>" />
						<select aria-label="<?php echo esc_attr__( 'Value unit', 'formidable' ); ?>">
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
							<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-margin-top-bottom' ); ?>
							<span class="frm-slider" tabindex="0">
								<span class="frm-slider-active-track">
									<span class="frm-slider-bullet">
										<span class="frm-slider-value-label"><?php echo (int) $field_value; ?></span>
									</span>
								</span>
							</span>
						</div>
						<div class="frm-slider-value">
							<input aria-label="<?php echo esc_attr__( 'Field value', 'formidable' ); ?>" type="text" value="<?php echo esc_attr( $component['value_label'] ); ?>" />
							<select aria-label="<?php echo esc_attr__( 'Value unit', 'formidable' ); ?>">
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
										<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm-margin-' . $field['type'] ); ?>
									<?php endif; ?>
									<span class="frm-slider" tabindex="0">
										<span class="frm-slider-active-track">
											<span class="frm-slider-bullet">
												<span class="frm-slider-value-label"><?php echo (int) $field['value']; ?></span>
											</span>
										</span>
									</span>
								</div>
								<div class="frm-slider-value">
									<input aria-label="<?php echo esc_attr__( 'Field value', 'formidable' ); ?>" type="text" value="<?php echo empty( $component['unit_measurement'] ) ? esc_attr( $field['value'] ) : (int) $field['value']; ?>" />
									<input type="hidden" name="<?php echo esc_attr( $field['name'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" />
									<select aria-label="<?php echo esc_attr__( 'Value unit', 'formidable' ); ?>">
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