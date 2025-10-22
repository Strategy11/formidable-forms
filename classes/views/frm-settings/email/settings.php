<?php
/**
 * View for upsell settings in Email global settings
 *
 * @since 6.25
 * @package FormidableForms
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

FrmTipsHelper::show_tip(
	array(
		'tip'  => __( 'Make every email match your brand — beautifully and effortlessly.', 'formidable' ),
		'call' => __( 'Upgrade to PRO', 'formidable' ),
		'link' => array(
			'medium'  => 'tip',
			'content' => 'email-settings',
		),
	)
);

$upgrade_attrs = array(
	'data-upgrade' => __( 'Email Styles Settings', 'formidable' ),
	'class'        => 'frm_show_upgrade',
	'style'        => 'position:absolute;top:0;right:0;bottom:0;left:0;',
);
?>

<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">
				<strong><?php esc_html_e( 'Header Image', 'formidable' ); ?></strong>
				<p class="description frm-mt-xs">
					<?php esc_html_e( 'Upload or choose a logo to be displayed at the top of email notifications.', 'formidable' ); ?>
				</p>
			</th>

			<td>
				<div style="position:relative;display:flex;align-items:center;">
					<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/email-styles/placeholder.png' ); ?>" alt="" style="width:120px;height:auto;margin-right:16px;" />
					<button type="button" class="frm-button-secondary" disabled><?php esc_html_e( 'Upload Image', 'formidable' ); ?></button>
					<div <?php FrmAppHelper::array_to_html_params( $upgrade_attrs, true ); ?>></div>
				</div>

				<div class="frm_grid_container frm-mt-xs" style="position:relative;">
					<div class="frm6">
						<p>
							<label for="frm-email-image-size"><?php esc_html_e( 'Size', 'formidable' ); ?></label>
							<select id="frm-email-image-size" disabled>
								<option><?php esc_html_e( 'Small', 'formidable' ); ?></option>
							</select>
						</p>
					</div>

					<div class="frm6">
						<p>
							<label for="frm-email-image-align"><?php esc_html_e( 'Image Alignment', 'formidable' ); ?></label>
							<select id="frm-email-image-align" disabled>
								<option><?php esc_html_e( 'Centered', 'formidable' ); ?></option>
							</select>
						</p>
					</div>

					<div class="frm6 frm-mt-sm">
						<p>
							<label for="frm-email-image-location"><?php esc_html_e( 'Image Location', 'formidable' ); ?></label>
							<select id="frm-email-image-location" disabled>
								<option><?php esc_html_e( 'Outside', 'formidable' ); ?></option>
							</select>
						</p>
					</div>

					<div <?php FrmAppHelper::array_to_html_params( $upgrade_attrs, true ); ?>></div>
				</div>
			</td>
		</tr>

		<tr>
			<th scope="row" class="frm-pt-xl">
				<strong><?php esc_html_e( 'Color Scheme', 'formidable' ); ?></strong>
				<p class="description frm-mt-xs"><?php esc_html_e( 'Change how your email looks by changing these values.', 'formidable' ); ?></p>
			</th>

			<td class="frm-pt-xl">
				<div class="frm_grid_container" style="position:relative;">
					<div class="frm6">
						<label style="margin-bottom:4px;"><?php esc_html_e( 'Email Background', 'formidable' ); ?></label>
						<?php FrmSettingsController::fake_color_picker( '#eaecf0' ); ?>
					</div>
					<div class="frm6">
						<label style="margin-bottom:4px;"><?php esc_html_e( 'Container Background', 'formidable' ); ?></label>
						<?php FrmSettingsController::fake_color_picker( '#ffffff' ); ?>
					</div>
					<div class="frm6 frm-mt-sm">
						<label style="margin-bottom:4px;"><?php esc_html_e( 'Text', 'formidable' ); ?></label>
						<?php FrmSettingsController::fake_color_picker( '#3d3d3d' ); ?>
					</div>
					<div class="frm6 frm-mt-sm">
						<label style="margin-bottom:4px;"><?php esc_html_e( 'Link', 'formidable' ); ?></label>
						<?php FrmSettingsController::fake_color_picker( '#4199fd' ); ?>
					</div>
					<div class="frm6 frm-mt-sm">
						<label style="margin-bottom:4px;"><?php esc_html_e( 'Field Divider', 'formidable' ); ?></label>
						<?php FrmSettingsController::fake_color_picker( '#dddddd' ); ?>
					</div>

					<div <?php FrmAppHelper::array_to_html_params( $upgrade_attrs, true ); ?>></div>
				</div>
			</td>
		</tr>

		<tr>
			<th scope="row" class="frm-pt-xl">
				<strong><?php esc_html_e( 'Typography', 'formidable' ); ?></strong>
				<p class="description frm-mt-xs"><?php esc_html_e( 'Choose the style that’s applied to all text in email notifications.', 'formidable' ); ?></p>
			</th>

			<td class="frm-pt-xl">
				<div class="frm_grid_container" style="position:relative;">
					<div class="frm6">
						<select disabled>
							<option><?php esc_html_e( 'Sans serif', 'formidable' ); ?></option>
						</select>
					</div>

					<div <?php FrmAppHelper::array_to_html_params( $upgrade_attrs, true ); ?>></div>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<hr class="frm-mt-md frm-mb-md" />
