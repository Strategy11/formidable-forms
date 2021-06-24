<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<tr>
	<td colspan="2">
		<label for="honeypot"><?php esc_html_e( 'Use Honeypot to check entries for spam', 'formidable' ); ?></label>
		<select id="honeypot" name="options[honeypot]">
			<option value="off" <?php selected( $values['honeypot'], 'off' ); ?>><?php esc_html_e( 'Off', 'formidable' ); ?></option>
			<option value="basic" <?php selected( $values['honeypot'], 'basic' ); ?>><?php esc_html_e( 'Basic', 'formidable' ); ?></option>
			<option value="strict" <?php selected( $values['honeypot'], 'strict' ); ?>><?php esc_html_e( 'Strict', 'formidable' ); ?></option>
		</select>
	</td>
</tr>
