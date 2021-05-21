<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<tr>
	<td colspan="2"><?php esc_html_e( 'Use Akismet to check entries for spam for', 'formidable' ); ?>
		<select name="options[akismet]">
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
	</td>
</tr>
