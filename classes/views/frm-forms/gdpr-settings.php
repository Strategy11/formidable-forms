<?php
/**
 * GDPR settings for form
 *
 * @since x.x
 *
 * @package Formidable
 *
 * @var array $values Form values.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$frm_settings         = FrmAppHelper::get_settings();
$gdpr_enabled         = ! FrmFieldGdprHelper::hide_gdpr_field();
$global_settings_link = admin_url( 'admin.php?page=formidable-settings&t=general_settings' );
$has_gdpr_field       = (bool) FrmField::get_all_types_in_form( $values['id'], FrmFieldGdprHelper::FIELD_TYPE, 1 );

$gdpr_checkbox_params = array(
	'type'  => 'checkbox',
	'id'    => 'frm_include_gdpr',
	'name'  => 'frm_include_gdpr',
	'value' => '1',
);

if ( ! $gdpr_enabled ) {
	$gdpr_checkbox_params['disabled'] = 'disabled';
}

if ( $has_gdpr_field ) {
	$gdpr_checkbox_params['checked'] = 'checked';
}
?>
<div class="frm-gdpr-settings">
<p class="howto">
	<?php esc_html_e( 'Control how this form collects and keeps personal data.', 'formidable' ); ?>
</p>

<?php if ( ! $gdpr_enabled ) : ?>
	<div class="frm_warning_style frm_force_visible_warning">
		<span>
			<?php
			printf(
				/* translators: %1$s: Opening anchor tag, %2$s: Closing anchor tag */
				esc_html__( 'To use these settings, first enable GDPR in %1$sGlobal GDPR Settings%2$s.', 'formidable' ),
				'<a href="' . esc_url( $global_settings_link ) . '">',
				'</a>'
			);
			?>
		</span>
	</div>
<?php endif; ?>

<?php
if ( has_action( 'frm_add_form_gdpr_options' ) ) {
	/**
	 * Fires in the GDPR form settings so Pro can show the auto-delete entries setting.
	 *
	 * @since x.x
	 *
	 * @param array $values       Form values.
	 * @param bool  $gdpr_enabled Whether GDPR is enabled in global settings.
	 */
	do_action( 'frm_add_form_gdpr_options', $values, $gdpr_enabled );
} else {
	include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/gdpr-settings/auto-delete-upsell.php';
}
?>

<p class="frm8 frm_form_field">
	<?php if ( $gdpr_enabled ) : ?>
		<input type="hidden" name="frm_include_gdpr" value="0" />
	<?php endif; ?>
	<label for="frm_include_gdpr" class="frm_inline_block">
		<input <?php FrmAppHelper::array_to_html_params( $gdpr_checkbox_params, true ); ?> />
		<?php esc_html_e( 'Include a GDPR agreement field', 'formidable' ); ?>
	</label>
</p>

<?php if ( $gdpr_enabled ) : ?>
	<div id="frm_gdpr_add_warning" class="frm_warning_style" style="display: none;">
		<span><?php esc_html_e( 'A GDPR field will be added to this form when you save settings.', 'formidable' ); ?></span>
	</div>
	<div id="frm_gdpr_remove_warning" class="frm_warning_style" style="display: none;">
		<span><?php esc_html_e( 'The GDPR field will be removed from this form when you save settings.', 'formidable' ); ?></span>
	</div>
<?php endif; ?>

<h3><?php esc_html_e( 'Global GDPR Settings', 'formidable' ); ?></h3>

<p class="howto">
	<?php esc_html_e( 'Managed in Global Settings and applied to every form.', 'formidable' ); ?>
</p>

<table class="form-table frm-fields frm-global-gdpr-table">
	<tr>
		<td>
			<?php FrmHtmlHelper::show_readonly_setting_icon( $frm_settings->enable_gdpr ); ?>
			<?php esc_html_e( 'Enable GDPR related features and enhancements.', 'formidable' ); ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php FrmHtmlHelper::show_readonly_setting_icon( $frm_settings->no_gdpr_cookies ); ?>
			<?php esc_html_e( 'Disable user tracking cookies.', 'formidable' ); ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php FrmHtmlHelper::show_readonly_setting_icon( $frm_settings->no_ips ); ?>
			<?php esc_html_e( 'Do not store user IPs with form submissions.', 'formidable' ); ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php FrmHtmlHelper::show_readonly_setting_icon( $frm_settings->custom_header_ip ); ?>
			<?php esc_html_e( 'Use custom headers when retrieving IPs with form submissions.', 'formidable' ); ?>
		</td>
	</tr>
</table>

<p>
	<?php
	printf(
		/* translators: %1$s: Opening anchor tag, %2$s: Closing anchor tag */
		esc_html__( 'To change these values %1$svisit Global GDPR Settings%2$s', 'formidable' ),
		'<a href="' . esc_url( $global_settings_link ) . '" target="_blank">',
		'</a>'
	);
	?>
</p>
</div>
