<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Roving tabindex: only one label is a Tab stop, and it can't be a hidden one. Fall back to the first visible gateway when none is selected yet.
$visible_gateways = array();

foreach ( $gateways as $gateway_name => $gateway ) {
	if ( $gateway['recurring'] || 'recurring' !== $form_action->post_content['type'] ) {
		$visible_gateways[ $gateway_name ] = $gateway;
	}
}
$selected_gateways = array_intersect( array_keys( $visible_gateways ), (array) $form_action->post_content['gateway'] );
$tab_stop          = $selected_gateways ? reset( $selected_gateways ) : (string) key( $visible_gateways );
?>
<div class="frm-long-icon-buttons" role="tablist">
<?php
foreach ( $gateways as $gateway_name => $gateway ) {
	$is_active       = in_array( $gateway_name, (array) $form_action->post_content['gateway'], true );
	$name            = $gateway['label'] ?? ucfirst( $gateway_name );
	$gateway_classes = $gateway['recurring'] ? '' : 'frm_gateway_no_recur';

	if ( $form_action->post_content['type'] === 'recurring' && ! $gateway['recurring'] ) {
		$gateway_classes .= ' frm_hidden';
	}

	// Include the action number so a form with more than one payment action doesn't repeat these ids.
	// A duplicate id would point every label at the first action's radio buttons.
	$toggle_id = $action_control->get_field_id( 'frm_toggle_' . $gateway_name . '_settings' );

	$input_params = array(
		'id'    => $toggle_id,
		'type'  => 'radio',
		'name'  => $action_control->get_field_name( 'gateway' ),
		'value' => $gateway_name,
	);

	if ( $is_active ) {
		$input_params['checked'] = 'checked';
	}

	$label_params = array(
		'for'           => $toggle_id,
		'class'         => trim( 'frm_payment_settings_tab frm_gateway_opt ' . $gateway_classes ),
		'tabindex'      => $gateway_name === $tab_stop ? '0' : '-1',
		'role'          => 'tab',
		'aria-selected' => $is_active ? 'true' : 'false',
	);
	?>
	<input <?php FrmAppHelper::array_to_html_params( $input_params, true ); ?> />
	<label <?php FrmAppHelper::array_to_html_params( $label_params, true ); ?>>
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_' . $gateway_name . '_full_icon' ); ?>
		<span class="screen-reader-text"><?php echo esc_html( $name ); ?></span>
	</label>
<?php
}//end foreach
?>
</div>
