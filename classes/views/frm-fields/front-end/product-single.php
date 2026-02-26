<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

foreach ( $field['options'] as $opt_key => $opt ) {
	$field_val = FrmFieldsHelper::get_value_from_array( $opt, $opt_key, $field );
	$price     = FrmFieldProduct::get_price_from_array( $opt, $opt_key, $field );
	$opt       = FrmFieldsHelper::get_label_from_array( $opt, $opt_key, $field );
	?>
	<p class="frm_single_product_label">
		<?php // TODO: should show currency
		echo esc_html( $opt ); ?>: <?php echo esc_html( $price ); ?>
	</p>

	<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $html_id ); ?>" value="<?php echo esc_attr( $field_val ); ?>" data-frmprice="<?php echo esc_attr( $price ); ?>" <?php do_action( 'frm_field_input_html', $field ); ?> />
	<?php
	break; // We want just the first
}
