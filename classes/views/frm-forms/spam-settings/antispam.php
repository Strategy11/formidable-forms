<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<tr>
	<td colspan="2">
		<input id="antispam" type="checkbox" name="options[antispam]" <?php checked( $values['antispam'], 1 ); ?> value="1" />
		<label for="antispam"><?php esc_html_e( 'Check entries for spam using JavaScript', 'formidable' ); ?></label>
	</td>
</tr>
