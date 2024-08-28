<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$max_setting_container_atts = array();
if ( ! empty( $can_fit_label_in_two_columns ) ) {
	$max_setting_container_atts['class'] = 'frm6 frm_form_field';
}
?>
<p <?php FrmAppHelper::array_to_html_params( $max_setting_container_atts, true ); ?>>
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
