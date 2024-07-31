<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p>
	<label>
		<input type="checkbox" name="frm_tracking" id="frm_tracking" value="1" <?php checked( $frm_settings->tracking, 1 ); ?> />
		<?php esc_html_e( 'Allow Formidable Forms to track plugin usage to help us ensure compatibility and simplify our settings.', 'formidable' ); ?>
	</label>
</p>

<p>
	<label>
		<input
			type="checkbox"
			name="frm_summary_emails"
			id="frm_summary_emails"
			value="1"
			data-toggleclass="frm_summary_emails_recipients_wrapper"
			<?php checked( $frm_settings->summary_emails, 1 ); ?>
		/>
		<?php esc_html_e( 'Send monthly and yearly summary emails with form and payment stats.', 'formidable' ); ?>
	</label>
</p>

<p class="frm_summary_emails_recipients_wrapper frm_indent_opt <?php echo ! $frm_settings->summary_emails ? 'frm_hidden' : ''; ?>">
	<label for="frm_summary_emails_recipients"><?php esc_html_e( 'Summary email recipients', 'formidable' ); ?></label>
	<input type="text" name="frm_summary_emails_recipients" id="frm_summary_emails_recipients" value="<?php echo esc_attr( $frm_settings->summary_emails_recipients ); ?>" />
	<?php if ( FrmAppHelper::is_formidable_branding() && in_array( FrmAddonsController::license_type(), array( 'elite', 'business' ), true ) ) { ?>
		<span>
			<?php
			printf(
				/* translators: %1$s the opening link tag, %2$s the closing link tag */
				esc_html__( 'Summary emails can be disabled across multiple sites from %1$sFormidableForms.com%2$s.', 'formidable' ),
				'<a href="' . esc_url( FrmAppHelper::admin_upgrade_link( 'misc-settings', 'account/profile/' ) ) . '" target="_blank" rel="noopener">',
				'</a>'
			);
			?>
		</span>
	<?php } ?>
</p>

<!-- Deprecated settings can only be switched away from the default -->
<?php if ( empty( $frm_settings->use_html ) ) { ?>
<p>
	<label for="frm_use_html">
		<input type="checkbox" id="frm_use_html" name="frm_use_html" value="1" <?php checked( $frm_settings->use_html, 1 ); ?> />
		<?php esc_html_e( 'Use HTML5 in forms', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'We recommend using HTML 5 for your forms. It adds some nifty options like placeholders, patterns, and autocomplete.', 'formidable' ); ?>"></span>
	</label>
</p>
<?php } else { ?>
	<input type="hidden" name="frm_use_html" value="1" />
<?php } ?>

<p class="frm_uninstall">
	<label>
		<input type="checkbox" id="frm-uninstall-box" value="1" onchange="frm_show_div('frm_uninstall_now',this.checked,true,'#')" />
		<?php esc_html_e( 'Uninstall Formidable Forms and permanently delete all data.', 'formidable' ); ?>
	</label>
	<a href="javascript:void(0)" id="frm_uninstall_now" class="frm_hidden" data-frmverify="<?php esc_attr_e( 'Are you sure you want to delete all forms, form data, and all other Formidable data. There is no Undo.', 'formidable' ); ?>" data-frmverify-btn="frm-button-red" data-frmuninstall="1">
		<?php esc_html_e( 'Uninstall Now', 'formidable' ); ?>
	</a>
	<span class="frm-wait frm_spinner"></span>
</p>
