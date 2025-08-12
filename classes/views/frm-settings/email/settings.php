<?php
/**
 * View for upsell settings in Email global settings
 *
 * @since x.x
 * @package FormidableForms
 */

FrmTipsHelper::show_tip(
	array(
		'tip'   => __( 'Make every email match your brand — beautifully and effortlessly.', 'formidable' ),
		'call'  => __( 'Upgrade to PRO', 'formidable' ),
		'link'  => array(
			'url' => 'https://formidableforms.com/knowledgebase/email-styles/',
		),
	)
);
?>

<table class="form-table">
	<tbody>
		<tr>
			<th class="frm_left_label">
				<p><strong><?php esc_html_e( 'Header Image', 'formidable' ); ?></strong></p>
				<p class="description">
					<?php esc_html_e( 'Upload or choose a logo to be displayed at the top of email notifications.', 'formidable' ); ?>
				</p>
			</th>

			<td>
				<img src="placeholderr image" />
				<button type="button"><?php esc_html_e( 'Remove Image', 'formidable' ); ?></button>

				<div class="frm_grid_container">
					<div class="frm6">
						<label><?php esc_html_e( 'Size', 'formidable' ); ?></label>
						<select>
							<option><?php esc_html_e( 'Small', 'formidable' ); ?></option>
							<option><?php esc_html_e( 'Medium', 'formidable' ); ?></option>
							<option><?php esc_html_e( 'Large', 'formidable' ); ?></option>
						</select>
					</div>

					<div class="frm6">
						<label><?php esc_html_e( 'Image Alignment', 'formidable' ); ?></label>
						<select>
							<option><?php esc_html_e( 'Centered', 'formidable' ); ?></option>
							<option><?php esc_html_e( 'Left', 'formidable' ); ?></option>
							<option><?php esc_html_e( 'Right', 'formidable' ); ?></option>
						</select>
					</div>

					<div class="frm6">
						<label><?php esc_html_e( 'Image Location', 'formidable' ); ?></label>
						<select>
							<option><?php esc_html_e( 'Outside container', 'formidable' ); ?></option>
							<option><?php esc_html_e( 'Inside container', 'formidable' ); ?></option>
						</select>
					</div>
				</div>
			</td>
		</tr>

		<tr>
			<th>
				<p><strong><?php esc_html_e( 'Color Scheme', 'formidable' ); ?></strong></p>
				<p class="description"><?php esc_html_e( 'Change how your email looks by changing these values.', 'formidable' ); ?></p>
			</th>

			<td>
				<div class="frm_grid_container">
					<div class="frm6">
						<label><?php esc_html_e( 'Email Background', 'formidable' ); ?></label>

					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<hr class="frm-mt-md frm-mb-md" />
