<?php
/**
 * Show the radio field on the front-end.
 * Extra line breaks show as space on the front-end when
 * the form is double filtered and not minimized.
 *
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent
 */

if ( isset( $field['post_field'] ) && $field['post_field'] == 'post_category' ) {
	$type = $field['type'];
	do_action( 'frm_after_checkbox', compact( 'field', 'field_name', 'type' ) );
} elseif ( is_array( $field['options'] ) ) {

	$image_size = ! empty ( $field['image_size'] ) ? $field[ 'image_size' ] : FrmAppHelper::get_default_image_option_size();
	$image_option_class = ! empty( $field['image_options'] ) ? ' frm_image_option frm_image_' . $image_size : '';

	foreach ( $field['options'] as $opt_key => $opt ) {
		if ( isset( $shortcode_atts ) && isset( $shortcode_atts['opt'] ) && ( $shortcode_atts['opt'] !== $opt_key ) ) {
			continue;
		}

		$field_val = FrmFieldsHelper::get_value_from_array( $opt, $opt_key, $field );
		$image     = FrmFieldsHelper::get_image_from_array( $opt, $opt_key, $field );
		$image_url = FrmFieldsHelper::get_image_url( $image );
		$opt = FrmFieldsHelper::get_label_from_array( $opt, $opt_key, $field );
		$label = FrmFieldsHelper::create_single_option_label( $field, $opt, $image_url );
		?>
		<div class="<?php echo esc_attr( apply_filters( 'frm_radio_class', 'frm_radio', $field, $field_val ) ); ?> <?php echo ( $image_option_class ); ?>" id="<?php echo esc_attr( FrmFieldsHelper::get_checkbox_id( $field, $opt_key, 'radio' ) ); ?>"><?php

		if ( ! isset( $shortcode_atts ) || ! isset( $shortcode_atts['label'] ) || $shortcode_atts['label'] ) {
			?><label for="<?php echo esc_attr( $html_id ); ?>-<?php echo esc_attr( $opt_key ); ?>"><?php
		}
		$checked = FrmAppHelper::check_selected( $field['value'], $field_val ) ? 'checked="checked" ' : ' ';

		$other_opt = false;
		$other_args = FrmFieldsHelper::prepare_other_input( compact( 'field_name', 'opt_key', 'field' ), $other_opt, $checked );
		?>
		<input type="radio" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $html_id . '-' . $opt_key ); ?>" value="<?php echo esc_attr( $field_val ); ?>"
		<?php
		echo $checked . ' '; // WPCS: XSS ok.
		do_action( 'frm_field_input_html', $field );
		?>/><?php

		if ( ! isset( $shortcode_atts ) || ! isset( $shortcode_atts['label'] ) || $shortcode_atts['label'] ) {
			echo ' ' . FrmAppHelper::kses( $label, 'all' ) . '</label>'; // WPCS: XSS ok.
		}

		$other_args = array(
			'other_opt' => $other_opt,
			'read_only' => $read_only,
			'checked'   => $checked,
			'name'      => $other_args['name'],
			'value'     => $other_args['value'],
			'field'     => $field,
			'html_id'   => $html_id,
			'opt_key'   => $opt_key,
			'opt_label' => $opt,
		);
		FrmFieldsHelper::include_other_input( $other_args );

		unset( $other_opt, $other_args );
		?></div>
<?php
	}
}
