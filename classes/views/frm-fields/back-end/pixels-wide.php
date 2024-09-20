<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$field_size_container_atts = array();
if ( $display_max ) {
	$max_characters_label         = __( 'Max Characters', 'formidable' );
	$can_fit_label_in_two_columns = FrmAppHelper::mb_function( array( 'mb_strlen', 'strlen' ), array( $max_characters_label ) ) < 20;

	if ( $can_fit_label_in_two_columns ) {
		$field_size_container_atts['class'] = 'frm6 frm_form_field';
	}
}
?>
<p <?php FrmAppHelper::array_to_html_params( $field_size_container_atts, true ); ?>>
	<label for="field_options_size_<?php echo esc_attr( $field['id'] ); ?>">
		<?php esc_html_e( 'Field Size', 'formidable' ); ?>
		<span class="frm-sub-label">
			<?php esc_html_e( '(%, px, em)', 'formidable' ); ?>
		</span>
	</label>

	<input type="text" name="field_options[size_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['size'] ); ?>" size="5" id="field_options_size_<?php echo esc_attr( $field['id'] ); ?>" aria-describedby="howto_size_<?php echo esc_attr( $field['id'] ); ?>" />
</p>

<?php
if ( $display_max ) {
	include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/max.php';
}
?>
