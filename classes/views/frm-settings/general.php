<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
$is_gdpr_enabled = FrmAppHelper::is_gdpr_enabled();

?>
<div class="frm_license_box">
	<h3 class="frm-no-border frm-mt-0"><?php esc_html_e( 'License Key', 'formidable' ); ?></h3>
	<p class="howto">
		<?php esc_html_e( 'Your license key provides access to new features and updates.', 'formidable' ); ?>
	</p>

	<?php do_action( 'frm_before_settings' ); ?>
</div>

<h3><?php esc_html_e( 'Defaults', 'formidable' ); ?></h3>

<p class="frm_grid_container">
	<label class="frm4 frm_form_field" for="frm_default_email">
		<?php esc_html_e( 'Default Email Address', 'formidable' ); ?>
		<?php FrmAppHelper::tooltip_icon( __( 'The default email address to receive notifications for new form submissions.', 'formidable' ) ); ?>
	</label>
	<input class="frm_with_left_label frm8" type="text" name="frm_default_email" id="frm_default_email" value="<?php echo esc_attr( $frm_settings->default_email ); ?>" />
</p>

<p class="frm_grid_container">
	<label class="frm4 frm_form_field" for="frm_from_email">
		<?php esc_html_e( 'Default From Address', 'formidable' ); ?>
		<?php FrmAppHelper::tooltip_icon( __( 'The "From" address for emails sent from this site.', 'formidable' ) ); ?>
	</label>
	<input class="frm_with_left_label frm8" type="text" name="frm_from_email" id="frm_from_email" value="<?php echo esc_attr( $frm_settings->from_email ); ?>" />
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

<h3><?php esc_html_e( 'GDPR', 'formidable' ); ?></h3>
<?php
$gdpr_options_wrapper_params = array( 'class' => 'frm_gdpr_options' );
if ( ! $is_gdpr_enabled ) {
	$gdpr_options_wrapper_params['class'] .= ' frm_hidden';
}
$custom_header_ip_wrapper_params = array( 'class' => 'frm_custom_header_ip_cont frm_gdpr_options' );
if ( $frm_settings->no_ips || ! $is_gdpr_enabled ) {
	$custom_header_ip_wrapper_params['class'] .= ' frm_hidden';
}
?>
<p>
	<label>
		<input type="checkbox" name="frm_enable_gdpr" id="frm_enable_gdpr" value="1" <?php checked( $is_gdpr_enabled, 1 ); ?> data-frmshow=".frm_gdpr_options" data-frmuncheck="#frm_no_gdpr_cookies, #frm_no_ips, #frm_custom_header_ip" />
		<?php esc_html_e( 'Enable GDPR related features and enhancements.', 'formidable' ); ?>
	</label>
</p>

<p <?php FrmAppHelper::array_to_html_params( $gdpr_options_wrapper_params, true ); ?>>
	<label>
		<input type="checkbox" name="frm_no_gdpr_cookies" id="frm_no_gdpr_cookies" value="1" <?php checked( $frm_settings->no_gdpr_cookies, 1 ); ?> />
		<?php esc_html_e( 'Disable user tracking cookies. This will disable the option to limit form entries to one per user by cookie.', 'formidable' ); ?>
	</label>
</p>

<p <?php FrmAppHelper::array_to_html_params( $gdpr_options_wrapper_params, true ); ?>>
	<label>
		<input type="checkbox" name="frm_no_ips" id="frm_no_ips" value="1" <?php checked( $frm_settings->no_ips, 1 ); ?> data-frmhide=".frm_custom_header_ip_cont" />
		<?php esc_html_e( 'Do not store user IPs with form submissions.', 'formidable' ); ?>
	</label>
</p>
<p <?php FrmAppHelper::array_to_html_params( $custom_header_ip_wrapper_params, true ); ?>>
	<label>
		<input type="checkbox" name="frm_custom_header_ip" id="frm_custom_header_ip" value="1" <?php checked( $frm_settings->custom_header_ip, 1 ); ?> />
		<?php esc_html_e( 'Use custom headers when retrieving IPs with form submissions.', 'formidable' ); ?>
		<?php FrmAppHelper::tooltip_icon( __( 'Only turn this on if IP addresses are incorrect in entries. Some server setups may require spoofable headers to determine an accurate IP address.', 'formidable' ) ); ?>
	</label>
</p>
<p class="frm-text-xs frm-mb-0">
	<?php
	// translators: %s: Knowledge base URL
	printf( esc_html__( 'Learn more about our GDPR settings', 'formidable' ) . ' <a href="%s" target="_blank">%s</a>', 'https://formidableforms.com/knowledgebase/gdpr-settings/', esc_html__( 'here', 'formidable' ) );
	?>
</p>
<h3 class="frm-mt-xs"><?php esc_html_e( 'Other', 'formidable' ); ?></h3>

<?php
/**
 * Trigger an action so Pro can display additional General settings in the Other section.
 *
 * @since 6.18
 *
 * @param FrmSettings $frm_settings
 */
do_action( 'frm_other_settings_form', $frm_settings );
