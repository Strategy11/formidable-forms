<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm6 frm_form_field frm_first">
	<label for="frm_akismet"><?php esc_html_e( 'Use Akismet to check entries for spam for', 'formidable' ); ?></label>
	<select id="frm_akismet" name="options[akismet]">
		<option value="">
			<?php esc_html_e( 'no one', 'formidable' ); ?>
		</option>
		<option value="1" <?php selected( $values['akismet'], 1 ); ?>>
			<?php esc_html_e( 'everyone', 'formidable' ); ?>
		</option>
		<option value="logged" <?php selected( $values['akismet'], 'logged' ); ?>>
			<?php esc_html_e( 'visitors who are not logged in', 'formidable' ); ?>
		</option>
	</select>
</p>
