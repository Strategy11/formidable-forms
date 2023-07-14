<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p>
	<label for="frm_tracking">
		<input type="checkbox" name="frm_tracking" id="frm_tracking" value="1" <?php checked( $frm_settings->tracking, 1 ); ?> />
		<?php esc_html_e( 'Allow Formidable Forms to track plugin usage to help us ensure compatibility and simplify our settings.', 'formidable' ); ?>
	</label>
</p>

<!-- Deprecated settings can only be switched away from the default -->
<input type="hidden" id="frm_use_html" name="frm_use_html" value="1" />

<?php if ( empty( $frm_settings->use_html ) ) { ?>
<p>
	<label for="frm_use_html">
		<input type="checkbox" id="frm_use_html" name="frm_use_html" value="1" <?php checked( $frm_settings->use_html, 1 ); ?> />
		<?php esc_html_e( 'Use HTML5 in forms', 'formidable' ); ?>
	</label>
	<span class="frm_help frm_icon_font frm_tooltip_icon"
	title="<?php esc_attr_e( 'We recommend using HTML 5 for your forms. It adds some nifty options like placeholders, patterns, and autocomplete.', 'formidable' ); ?>"></span>
</p>
<?php } ?>

<p class="frm_uninstall">
	<label for="frm-uninstall-box">
		<input type="checkbox" id="frm-uninstall-box" value="1" onchange="frm_show_div('frm_uninstall_now',this.checked,true,'#')" />
		<?php esc_html_e( 'Uninstall Formidable Forms and permanently delete all data.', 'formidable' ); ?>
	</label>
	<a href="javascript:void(0)" id="frm_uninstall_now" class="frm_hidden" data-frmverify="<?php esc_attr_e( 'Are you sure you want to delete all forms, form data, and all other Formidable data. There is no Undo.', 'formidable' ); ?>" data-frmverify-btn="frm-button-red" data-frmuninstall="1">
		<?php esc_html_e( 'Uninstall Now', 'formidable' ); ?>
	</a>
	<span class="frm-wait frm_spinner"></span>
</p>
