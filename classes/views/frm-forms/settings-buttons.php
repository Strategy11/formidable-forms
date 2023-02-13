<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="howto">
	<?php esc_html_e( 'Select a style for this form and set your button text.', 'formidable' ); ?>
</p>

<input type="hidden" name="options[custom_style]" value="<?php echo esc_attr( $values['custom_style'] ); ?>" />

<table class="form-table">
	<?php do_action( 'frm_add_form_style_tab_options', $values ); ?>
	<tr>
		<td colspan="2">
			<h3><?php esc_html_e( 'Buttons', 'formidable' ); ?></h3>
		</td>
	</tr>
	<tr>
		<td><label for="frm_submit_button_text"><?php esc_html_e( 'Submit Button Text', 'formidable' ); ?></label></td>
		<td>
			<input id="frm_submit_button_text" type="text" name="options[submit_value]" value="<?php echo esc_attr( $values['submit_value'] ); ?>" />
		</td>
	</tr>
	<?php do_action( 'frm_add_form_button_options', $values ); ?>
</table>
