<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<tr>
	<td colspan="2">
		<input id="honeypot" type="checkbox" name="options[honeypot]" <?php checked( $values['honeypot'], 1 ); ?> value="1" />
		<label for="honeypot"><?php esc_html_e( 'Use Honeypot to check entries for spam', 'formidable' ); ?></label>
	</td>
</tr>
