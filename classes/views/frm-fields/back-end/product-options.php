<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm_form_field frm_product_type">
	<label><?php esc_html_e( 'Product Type', 'formidable' ); ?></label>
	<select name="field_options[data_type_<?php echo esc_attr( $field['id'] ); ?>]" class="frmjs_prod_data_type_opt">
		<?php
		foreach ( $data_types as $type => $label ) {
			$disabled = 'user_def' === $type ? 'disabled' : '';
			?>
			<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $field['data_type'], $type ); ?> <?php echo esc_attr( $disabled ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php } ?>
	</select>
</p>
