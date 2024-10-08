<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm6 frm_form_field frm_first">
	<label for="honeypot"><?php esc_html_e( 'Use Honeypot to check entries for spam', 'formidable' ); ?>
		<?php FrmAppHelper::tooltip_icon( __( 'Include an invisible field in your form to trick bots. Setting to strict will catch more spam but issues with autocomplete may prevent real people from submitting on some browsers.', 'formidable' ), array( 'data-container' => 'body' ) ); ?>
	</label>
	<select id="honeypot" name="options[honeypot]">
		<option value="off" <?php selected( $values['honeypot'], 'off' ); ?>><?php esc_html_e( 'Off', 'formidable' ); ?></option>
		<option value="basic" <?php selected( $values['honeypot'], 'basic' ); ?>><?php esc_html_e( 'Basic', 'formidable' ); ?></option>
		<option value="strict" <?php selected( $values['honeypot'], 'strict' ); ?>><?php esc_html_e( 'Strict', 'formidable' ); ?></option>
	</select>
</p>
