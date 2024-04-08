<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<tr>
	<td>
		<label for="frm_option_transition" class="frm_show_upgrade frm_noallow" data-medium="transitions" data-upgrade="Form transitions">
			<?php esc_html_e( 'Page Turn Transitions', 'formidable' ); ?>
		</label>
	</td>
	<td>
		<select id="frm_option_transition" >
			<option disabled>
				<?php esc_html_e( 'Slide horizontally', 'formidable' ); ?>
			</option>
			<option disabled>
				<?php esc_html_e( 'Slide vertically', 'formidable' ); ?>
			</option>
		</select>
	</td>
</tr>
