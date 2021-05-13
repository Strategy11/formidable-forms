<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<tr>
	<td colspan="2">
		<input id="no_honeypot" type="checkbox" name="options[no_honeypot]" <?php checked( $values['no_honeypot'], 0 ); ?> value="1" />
		<label for="no_honeypot"><?php esc_html_e( 'Use Honeypot to check entries for spam', 'formidable' ); ?></label>
	</td>
</tr>
