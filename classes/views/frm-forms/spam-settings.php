<?php
/**
 * Spam settings for form
 *
 * @since 6.33
 *
 * @package Formidable
 *
 * @var array $values Form values.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$frm_settings  = FrmAppHelper::get_settings();
$view_dir_path = FrmAppHelper::plugin_path() . '/classes/views/frm-forms/spam-settings/';

?>
<div class="frm-spam-settings">
<p class="howto">
	<?php esc_html_e( 'Prevent spam submissions with anti-spam tools.', 'formidable' ); ?>
</p>

<?php
if ( function_exists( 'akismet_http_post' ) ) {
	include $view_dir_path . 'akismet.php';
}

include $view_dir_path . 'stopforumspam.php';
include $view_dir_path . 'antispam.php';
?>

<?php
// Check if captcha is configured
$captcha_settings   = FrmCaptchaFactory::get_settings_object();
$captcha_configured = $captcha_settings->has_pubkey();

// Check if form has a captcha field
$form_fields       = FrmField::get_all_for_form( $values['id'], '', 'exclude' );
$has_captcha_field = false;
$captcha_field_id  = 0;

foreach ( $form_fields as $field ) {
	if ( 'captcha' === $field->type ) {
		$has_captcha_field = true;
		$captcha_field_id  = $field->id;
		break;
	}
}
?>

<p class="frm8 frm_form_field">
	<input type="hidden" name="frm_include_captcha" value="0" />
	<label for="frm_include_captcha" class="frm_inline_block">
		<input type="checkbox" id="frm_include_captcha" name="frm_include_captcha" value="1" <?php disabled( $captcha_configured, false ); ?> <?php checked( $has_captcha_field, true ); ?> />
		<?php esc_html_e( 'Include Captcha in this form', 'formidable' ); ?>
	</label>
</p>

<?php if ( ! $captcha_configured ) : ?>
	<div class="frm_warning_style frm_force_visible_warning">
		<span>
			<?php
			printf(
				/* translators: %1$s: Opening anchor tag, %2$s: Closing anchor tag */
				esc_html__( 'To enable Captcha, first set up a Captcha service in %1$sGlobal Spam Settings%2$s.', 'formidable' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=formidable-settings&t=captcha_settings' ) ) . '">',
				'</a>'
			);
			?>
		</span>
	</div>
<?php else : ?>
	<div id="frm_captcha_add_warning" class="frm_warning_style" style="display: none;">
		<span><?php esc_html_e( 'A Captcha field will be added to this form when you save settings.', 'formidable' ); ?></span>
	</div>
	<div id="frm_captcha_remove_warning" class="frm_warning_style" style="display: none;">
		<span><?php esc_html_e( 'The Captcha field will be removed from this form when you save settings.', 'formidable' ); ?></span>
	</div>
<?php endif; ?>

<h3><?php esc_html_e( 'Global Spam Settings', 'formidable' ); ?></h3>

<p class="howto">
	<?php esc_html_e( 'Managed in Global Settings and applied to every form.', 'formidable' ); ?>
</p>

<table class="form-table frm-fields frm-global-spam-table">
	<tr>
		<td>
			<?php FrmHtmlHelper::show_readonly_setting_icon( $frm_settings->honeypot ); ?>
			<?php esc_html_e( 'Use honeypot to check entries for spam', 'formidable' ); ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php FrmHtmlHelper::show_readonly_setting_icon( $frm_settings->wp_spam_check ); ?>
			<?php esc_html_e( 'Use WordPress spam comments to check entries for spam', 'formidable' ); ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php FrmHtmlHelper::show_readonly_setting_icon( $frm_settings->denylist_check ); ?>
			<?php esc_html_e( 'Check denylist data to validate for spam', 'formidable' ); ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php
			/**
			 * Filters the spam retention period shown in the form settings summary.
			 *
			 * @since x.x
			 *
			 * @param int $retention_days Days before automatic deletion. 0 disables cleanup.
			 */
			$retention_days = absint( apply_filters( 'frm_spam_retention_summary', 0 ) );
			FrmHtmlHelper::show_readonly_setting_icon( FrmAppHelper::pro_is_installed() && $retention_days > 0 );
			esc_html_e( 'Delete spam entries automatically', 'formidable' );

			if ( FrmAppHelper::pro_is_installed() && $retention_days > 0 ) {
				/* translators: %d: Number of days before spam entries are deleted. */
				printf( ' ' . esc_html( _n( '(after %d day)', '(after %d days)', $retention_days, 'formidable' ) ), absint( $retention_days ) );
			}

			if ( ! FrmAppHelper::pro_is_installed() ) {
				?>
				<a href="#" class="frm_show_upgrade" data-upgrade="<?php esc_attr_e( 'Automatic spam cleanup', 'formidable' ); ?>" data-medium="spam-cleanup">
					<?php esc_html_e( 'Upgrade to Pro', 'formidable' ); ?>
				</a>
				<?php
			}
			?>
		</td>
	</tr>
</table>

<p>
	<?php
	printf(
		/* translators: %1$s: Opening anchor tag, %2$s: Closing anchor tag */
		esc_html__( 'To change these values %1$svisit Global Spam Settings%2$s', 'formidable' ),
		'<a href="' . esc_url( admin_url( 'admin.php?page=formidable-settings&t=captcha_settings' ) ) . '" target="_blank">',
		'</a>'
	);
	?>
</p>
</div>
