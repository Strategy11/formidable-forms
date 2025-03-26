<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm6 frm_form_field frm_first">
	<label for="stopforumspam">
		<input id="stopforumspam" type="checkbox" name="options[stopforumspam]" <?php checked( $values['stopforumspam'], 1 ); ?> value="1" />
		<?php esc_html_e( 'Use stopforumspam API to check entries for spam', 'formidable' ); ?>
		<?php FrmAppHelper::tooltip_icon( __( 'Send the IP address, email, and name to the stopforumspam API to check.', 'formidable' ), array( 'data-container' => 'body' ) ); ?>
	</label>
</p>
