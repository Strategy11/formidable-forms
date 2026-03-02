<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frmjs_prod_field_opt_cont frm_prod_field_opt_cont">
	<label>
		<?php esc_html_e( 'Product Field', 'formidable-pro' ); ?>
	</label>
	<span id="field_options[product_field_<?php echo esc_attr( $field['id'] ); ?>]" class="frmjs_prod_field_opt frm_grid_container" data-frmfname="field_options[product_field_<?php echo esc_attr( $field['id'] ); ?>][]" data-frmcurrent="<?php echo esc_attr( json_encode( $field['product_field'] ) ); ?>">
		<?php
			if ( ! empty( $field['product_field'] ) ) {
				foreach ( $field['product_field'] as $f ) {
				?>
					<input type="checkbox" name="field_options[product_field_<?php echo esc_attr( $field['id'] ); ?>][]" value="<?php echo esc_attr( $f ); ?>" checked="checked" />
				<?php
				}
			}
		?>
	</span>
</p>
