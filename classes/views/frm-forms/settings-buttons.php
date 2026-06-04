<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<table class="form-table">
	<?php
	do_action_deprecated( 'frm_add_form_style_tab_options', array( $values ), '6.16.3' );
	?>
	<tr>
		<td colspan="2">
			<h3><?php esc_html_e( 'Buttons', 'formidable' ); ?></h3>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div class="frm_note_style" style="margin-top: 0;">
				<?php esc_html_e( 'Submit button settings were moved to the Submit button in the form builder.', 'formidable' ); ?>
			</div>
		</td>
	</tr>
	<?php
	if ( $should_deprecate_hook ) {
		do_action_deprecated( 'frm_add_form_button_options', array( $values ), '6.16.3' );
	} else {
		do_action( 'frm_add_form_button_options', $values );
	}
	?>
</table>
