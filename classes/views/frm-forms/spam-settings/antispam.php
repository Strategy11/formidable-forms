<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
$tooltip = __( 'Generate unique tokens for validating form submissions.', 'formidable' );
if ( FrmAppHelper::pro_is_installed() ) {
	$tooltip .= ' ' . __( 'Uploaded files will also be validated for spam.', 'formidable' );
}
?>
<p class="frm6 frm_form_field frm_first">
	<label for="antispam">
		<input id="antispam" type="checkbox" name="options[antispam]" <?php checked( $values['antispam'], 1 ); ?> value="1" />
		<?php esc_html_e( 'Check entries for spam using JavaScript', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo esc_attr( $tooltip ); ?>" data-container="body"></span>
	</label>
</p>
