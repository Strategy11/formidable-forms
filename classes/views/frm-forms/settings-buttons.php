<p class="howto">
	<?php esc_html_e( 'Select a style for this form and set your button text.', 'formidable' ); ?>
</p>
<table class="form-table">
	<tr>
		<td class="frm_left_label">
			<label for="custom_style">
				<?php esc_html_e( 'Style Template', 'formidable' ); ?>
			</label>
		</td>
		<td>
		<?php if ( $no_global_style ) { ?>
				<p class="howto">
					<?php esc_html_e( 'The form styling has been disabled in the Global settings.', 'formidable' ); ?>
				</p>
				<input type="hidden" name="options[custom_style]" value="<?php echo esc_attr( $values['custom_style'] ); ?>" />
		<?php } else { ?>
			<select name="options[custom_style]" id="custom_style">
			<option value="1" <?php selected( $values['custom_style'], 1 ); ?>>
				<?php esc_html_e( 'Always use default', 'formidable' ); ?>
			</option>
			<?php foreach ( $styles as $s ) { ?>
				<option value="<?php echo esc_attr( $s->ID ); ?>" <?php selected( $s->ID, $values['custom_style'] ); ?>>
					<?php echo esc_html( $s->post_title . ( empty( $s->menu_order ) ? '' : ' (' . __( 'default', 'formidable' ) . ')' ) ); ?>
				</option>
				<?php } ?>
				<option value="0"
				<?php
				selected( $values['custom_style'], 0 );
				selected( $values['custom_style'], '' );
				?>>
				<?php esc_html_e( 'Do not use Formidable styling', 'formidable' ); ?>
			</option>
			</select>
		<?php } ?>
		</td>
	</tr>
	<tr>
		<td><label><?php esc_html_e( 'Submit Button Text', 'formidable' ); ?></label></td>
		<td>
			<input type="text" name="options[submit_value]" value="<?php echo esc_attr( $values['submit_value'] ); ?>" />
		</td>
	</tr>
	<?php do_action( 'frm_add_form_button_options', $values ); ?>
</table>
