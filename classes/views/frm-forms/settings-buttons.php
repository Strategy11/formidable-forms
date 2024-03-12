<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="howto">
	<?php esc_html_e( 'Set your button text.', 'formidable' ); ?>
</p>

<input type="hidden" name="options[custom_style]" value="<?php echo esc_attr( $values['custom_style'] ); ?>" />

<table class="form-table">
	<tr>
		<td colspan="2">
			<?php esc_html_e( 'Page Turn Transitions setting is moved to the page break field settings in the form builder', 'formidable' ); ?>
		</td>
	</tr>
	<?php do_action( 'frm_add_form_style_tab_options', $values ); ?>
	<tr>
		<td colspan="2">
			<h3><?php esc_html_e( 'Buttons', 'formidable' ); ?></h3>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php esc_html_e( 'The submit button settings are moved to the Submit button in the form builder.', 'formidable' ); ?>
		</td>
	</tr>
	<?php do_action( 'frm_add_form_button_options', $values ); ?>
</table>
