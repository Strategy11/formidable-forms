<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<select name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $html_id ); ?>" <?php do_action( 'frm_field_input_html', $field ); ?>>
<?php
$placeholder = FrmFieldsController::add_placeholder_to_select( $field );
$skipped     = false;

foreach ( $field['options'] as $opt_key => $opt ) {
	$field_val = FrmFieldsHelper::get_value_from_array( $opt, $opt_key, $field );
	$selected  = FrmAppHelper::check_selected( $field['value'], $field_val );
	$price     = FrmFieldProduct::get_price_from_array( $opt, $opt_key, $field );
	$opt       = FrmFieldsHelper::get_label_from_array( $opt, $opt_key, $field );

	if ( ! empty( $placeholder ) && $opt == '' && ! $skipped ) {
		$skipped = true;
		continue;
	}
	FrmHtmlHelper::echo_dropdown_option(
		$opt,
		$selected,
		array(
			'value'         => $field_val,
			'data-frmprice' => $price,
		)
	);
}
?>
</select>
