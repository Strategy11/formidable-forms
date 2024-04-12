<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_license_box">
	<h3 class="frm-no-border frm-mt-0"><?php esc_html_e( 'License Key', 'formidable' ); ?></h3>
	<p class="howto">
		<?php esc_html_e( 'Your license key provides access to new features and updates.', 'formidable' ); ?>
	</p>

	<?php do_action( 'frm_before_settings' ); ?>
</div>

<h3><?php esc_html_e( 'Styling & Scripts', 'formidable' ); ?></h3>

<p class="frm_grid_container">
	<label class="frm4 frm_form_field" for="frm_load_style">
		<?php esc_html_e( 'Load form styling', 'formidable' ); ?>
	</label>
	<select id="frm_load_style" name="frm_load_style" class="frm8 frm_form_field">
		<option value="all" <?php selected( $frm_settings->load_style, 'all' ); ?>>
			<?php esc_html_e( 'on every page of my site', 'formidable' ); ?>
		</option>
		<option value="dynamic" <?php selected( $frm_settings->load_style, 'dynamic' ); ?>>
			<?php esc_html_e( 'only on applicable pages', 'formidable' ); ?>
		</option>
		<option value="none" <?php selected( $frm_settings->load_style, 'none' ); ?>>
			<?php esc_html_e( 'Don\'t use form styling on any page', 'formidable' ); ?>
		</option>
	</select>
</p>

<?php do_action( 'frm_style_general_settings', $frm_settings ); ?>

<h3><?php esc_html_e( 'Other', 'formidable' ); ?></h3>

<p class="frm_grid_container">
	<label class="frm4 frm_form_field" for="frm_default_email">
		<?php esc_html_e( 'Default Email Address', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The default email address to receive notifications for new form submissions.', 'formidable' ); ?>"></span>
	</label>
	<input class="frm_with_left_label frm8" type="text" name="frm_default_email" id="frm_default_email" value="<?php echo esc_attr( $frm_settings->default_email ); ?>" />
</p>

<?php
ob_start();

/**
 * Trigger an action so Pro can display additional General settings in the Other section.
 *
 * @param FrmSettings $frm_settings
 */
do_action( 'frm_settings_form', $frm_settings );

$more_html = ob_get_clean();
echo $more_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

FrmSettingsController::maybe_render_currency_selector( $frm_settings, $more_html );
unset( $more_html );
?>

<div class="clear"></div>

<?php if ( ! FrmAppHelper::pro_is_installed() ) { ?>
	<input type="hidden" name="frm_menu" id="frm_menu" value="<?php echo esc_attr( $frm_settings->menu ); ?>"/>
	<input type="hidden" name="frm_mu_menu" id="frm_mu_menu" value="<?php echo esc_attr( $frm_settings->mu_menu ); ?>"/>
<?php } ?>

<p>
	<label for="frm_no_ips">
		<input type="checkbox" name="frm_no_ips" id="frm_no_ips" value="1" <?php checked( $frm_settings->no_ips, 1 ); ?> data-frmhide=".frm_custom_header_ip_cont" />
		<?php esc_html_e( 'Do not store IPs with form submissions. Check this box for to assist with GDPR compliance.', 'formidable' ); ?>
	</label>
</p>

<?php
$custom_header_ip_wrapper_params = array( 'class' => 'frm_custom_header_ip_cont' );
if ( $frm_settings->no_ips ) {
	$custom_header_ip_wrapper_params['class'] .= ' frm_hidden';
}
?>
<p <?php FrmAppHelper::array_to_html_params( $custom_header_ip_wrapper_params, true ); ?>>
	<label for="frm_custom_header_ip">
		<input type="checkbox" name="frm_custom_header_ip" id="frm_custom_header_ip" value="1" <?php checked( $frm_settings->custom_header_ip, 1 ); ?> />
		<?php esc_html_e( 'Use custom headers when retrieving IPs with form submissions.', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Only turn this on if IP addresses are incorrect in entries. Some server setups may require spoofable headers to determine an accurate IP address.', 'formidable' ); ?>"></span>
	</label>
</p>

<p>
	<label for="frm_admin_bar">
		<input type="checkbox" name="frm_admin_bar" id="frm_admin_bar" value="1" <?php checked( $frm_settings->admin_bar, 1 ); ?> />
		<?php esc_html_e( 'Do not include Formidable in the admin bar.', 'formidable' ); ?>
	</label>
</p>
