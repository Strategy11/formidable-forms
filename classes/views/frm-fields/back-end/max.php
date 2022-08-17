<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm6 frm_form_field">
	<label for="field_options_max_<?php echo esc_attr( $field['id'] ); ?>">
		<?php
		if ( 'textarea' === $field['type'] || 'rte' === $field['type'] ) {
			esc_html_e( 'Rows', 'formidable' );
		} else {
			esc_html_e( 'Max Characters', 'formidable' );
		}
		?>
	</label>
	<input type="text" class="frm_max_length_opt" name="field_options[max_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['max'] ); ?>" id="field_options_max_<?php echo esc_attr( $field['id'] ); ?>" size="5" data-fid="<?php echo intval( $field['id'] ); ?>" />
</p>
