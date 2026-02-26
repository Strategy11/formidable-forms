<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Show the product field as radio button or checkbox on the front-end.
 * Extra line breaks show as space on the front-end when
 * the form is double filtered and not minimized.
 */

$display_type = $field['data_type'];

if ( $display_type !== 'radio' && $display_type !== 'checkbox' ) {
	return;
}

if ( $display_type === 'checkbox' ) {
	$field_name .= '[]';
}

$option_index = 0;

foreach ( $field['options'] as $opt_key => $opt ) {
	if ( isset( $shortcode_atts ) && isset( $shortcode_atts['opt'] ) && $shortcode_atts['opt'] !== $opt_key ) {
		continue;
	}

	$field_val = FrmFieldsHelper::get_value_from_array( $opt, $opt_key, $field );
	$price     = FrmFieldProduct::get_price_from_array( $opt, $opt_key, $field );
	$opt       = FrmFieldsHelper::get_label_from_array( $opt, $opt_key, $field );
	?>
	<div class="<?php echo esc_attr( apply_filters( 'frm_' . $display_type . '_class', 'frm_' . $display_type, $field, $field_val ) ); ?>" id="<?php echo esc_attr( FrmFieldsHelper::get_checkbox_id( $field, $opt_key, $display_type ) ); ?>"><?php

	if ( ! isset( $shortcode_atts ) || ! isset( $shortcode_atts['label'] ) || $shortcode_atts['label'] ) {
		?><label for="<?php echo esc_attr( $html_id ); ?>-<?php echo esc_attr( $opt_key ); ?>"><?php
	}
	?>
	<input type="<?php echo esc_attr( $display_type ); ?>" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $html_id . '-' . $opt_key ); ?>" value="<?php echo esc_attr( $field_val ); ?>"<?php

	FrmAppHelper::checked( $field['value'], $field_val );

	if ( 0 === $option_index && FrmField::is_required( $field ) ) {
		echo ' aria-required="true" ';
	}

	?> data-frmprice="<?php echo esc_attr( $price ); ?>" <?php do_action( 'frm_field_input_html', $field ); ?> /><?php

	if ( ! isset( $shortcode_atts ) || ! isset( $shortcode_atts['label'] ) || $shortcode_atts['label'] ) {
		echo ' ';
		FrmAppHelper::kses_echo( $opt, 'all' );
		echo '</label>';
	}
	?></div>
<?php
	++$option_index;
}
