<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

?>
<p>
	<?php esc_html_e( 'Configure and manage your payment gateways in one place to control transactions, settings, and more.', 'formidable' ); ?>
</p>
<?php

echo '<div class="frm-long-icon-buttons" role="tablist">';

$tab = FrmAppHelper::simple_get( 't', 'sanitize_title', 'general_settings' );
if ( $tab && in_array( $tab, array( 'stripe_settings', 'square_settings' ), true ) ) {
	$tab = str_replace( '_settings', '', $tab );
} else {
	$tab = 'stripe';
}

foreach ( $payment_sections as $key => $section ) {
	$is_active    = $tab === $key;
	$name         = isset( $section['name'] ) ? $section['name'] : ucfirst( $key );
	$input_params = array(
		'id'           => "frm_toggle_{$key}_settings",
		'type'         => 'radio',
		'name'         => 'frm_payment_section',
		'value'        => $key,
		'data-frmshow' => "#frm_{$key}_settings_section",
	);
	if ( $is_active ) {
		$input_params['checked'] = 'checked';
	}
	$other_sections = array_diff( array_keys( $payment_sections ), array( $key ) );
	$input_params['data-frmhide'] = implode( ',', array_map( function( $section ) {
		return "#frm_{$section}_settings_section";
	}, $other_sections ) );
	?>
	<input <?php echo FrmAppHelper::array_to_html_params( $input_params, true ); ?> />
	<label for="frm_toggle_<?php echo esc_attr( $key ); ?>_settings" class="frm_payment_settings_tab" tabindex="0" role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
		<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_' . $key . '_full_icon' ); ?>
		<span class="screen-reader-text"><?php echo esc_attr( $name ); ?></span>
	</label>
	<?php
}
echo '</div>';

foreach ( $payment_sections as $key => $section ) {
	$is_active       = $tab === $key;
	$name            = $section['name'] ?? ucfirst( $key );
	$include_h3      = 'authorize_net' !== $key; // Exclude Authorize.Net as the h3 tag is added explicitly.
	$section_classes = 'frm_payments_section';

	if ( ! $is_active ) {
		$section_classes .= ' frm_hidden';
	}
	?>
	<div id="frm_<?php echo esc_attr( $key ); ?>_settings_section" class="<?php echo esc_attr( $section_classes ); ?>" role="tabpanel">
		<?php if ( $include_h3 ) { ?>
			<h3 style="margin-bottom: 0;"><?php echo esc_html( $name ) . ' ' . esc_html__( 'Settings', 'formidable' ); ?></h3>
		<?php } ?>
		<?php
		if ( isset( $section['class'] ) ) {
			call_user_func( array( $section['class'], $section['function'] ) );
		} else {
			call_user_func( ( isset( $section['function'] ) ? $section['function'] : $section ) );
		}
		?>
	</div>
	<?php
}
