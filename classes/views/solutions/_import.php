<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_image_options frm_form_field" style="--image-size:<?php echo esc_attr( $width ); ?>px">
	<div class="frm_opt_container">
		<?php
		foreach ( $options as $info ) {
			if ( ! empty( $xml ) && isset( $info['url'] ) && $info['url'] === 'auto' ) {
				$info['url'] = $xml;
			}

			$disabled = isset( $imported[ $info['form'] ] ) ? ' disabled' : '';
			$url   = isset( $info['url'] ) ? $info['url'] : '';
			$value = $importing === 'form' ? $info['form'] : $info['key'];
			if ( ! isset( $info['img'] ) ) {
				?>
				<input type="hidden" name="<?php echo esc_attr( $importing ); ?>[<?php echo esc_attr( $info['form'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" <?php echo esc_attr( $disabled ); ?>/>
				<?php
				continue;
			}

			$hide_views = $importing === 'view' && ( ( $selected && $info['form'] !== $selected ) || isset( $imported[ $info['form'] ] ) );
			?>
			<div class="frm_radio radio-inline radio frm_image_option<?php echo esc_attr( $importing === 'view' ? ' show_sub_opt show_' . $info['form'] : '' ); ?>" style="<?php echo esc_attr( $hide_views ? 'display:none' : '' ); ?>">
				<?php if ( $importing === 'form' ) { ?>
					<input type="hidden" name="xml[<?php echo esc_attr( $info['form'] ); ?>]" value="<?php echo esc_attr( $url ); ?>" <?php echo esc_attr( $disabled ); ?>/>
				<?php } ?>
				<label>
					<input type="radio" name="<?php echo esc_attr( $importing . ( $importing === 'view' ? '[' . $info['form'] . ']' : '' ) ); ?>" value="<?php echo esc_attr( $value ); ?>"
					<?php
					echo esc_attr( $disabled );
					if ( ! $selected && empty( $disabled ) ) {
						echo ' checked="checked"';
						$selected = $info['form'];
					}
					?>
					<?php if ( $importing === 'form' ) { ?>
						onchange="frm_show_div('show_sub_opt',this.checked,false,'.');frm_show_div('show_<?php echo esc_attr( $info['form'] ); ?>',this.checked,true,'.')"
					<?php } ?>
					/>
					<div class="frm_image_option_container frm_label_with_image">
						<?php echo FrmAppHelper::kses( $info['img'], array( 'svg', 'rect', 'path' ) );  // WPCS: XSS ok. ?>
						<span class="frm_text_label_for_image">
							<?php
							if ( ! empty( $disabled ) ) {
								FrmAppHelper::icon_by_class(
									'frmfont frm_step_complete_icon',
									array(
										'aria-label' => __( 'Imported', 'formidable' ),
									)
								);
							}

							if ( $importing === 'form' && $disabled ) {
								echo FrmFormsHelper::edit_form_link( $imported[ $info['form'] ] ); // WPCS: XSS ok.
							} else {
								echo esc_html( $info['name'] );
							}
							?>
						</span>
					</div>
				</label>
			</div>
		<?php } ?>
	</div>
</div>
