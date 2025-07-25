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
	$other_section_selectors      = array_map(
		function ( $section ) {
			return "#frm_{$section}_settings_section";
		},
		array_diff( array_keys( $payment_sections ), array( $key ) )
	);
	$input_params['data-frmhide'] = implode( ',', $other_section_selectors );

	$label_params = array(
		'for'           => "frm_toggle_{$key}_settings",
		'class'         => 'frm_payment_settings_tab',
		'tabindex'      => '0',
		'role'          => 'tab',
		'aria-selected' => $is_active ? 'true' : 'false',
	);
	?>
	<input <?php FrmAppHelper::array_to_html_params( $input_params, true ); ?> />
	<label <?php FrmAppHelper::array_to_html_params( $label_params, true ); ?>>
		<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_' . $key . '_full_icon' ); ?>
		<span class="screen-reader-text"><?php echo esc_html( $name ); ?></span>
	</label>
	<?php
}//end foreach
echo '</div>';

foreach ( $payment_sections as $key => $section ) {
	$is_active       = $tab === $key;
	$name            = $section['name'] ?? ucfirst( $key );
	$section_classes = 'frm_payments_section';

	// Exclude Authorize.Net as the h3 tag is added explicitly.
	$include_h3 = 'authorize_net' !== $key;

	if ( ! $is_active ) {
		$section_classes .= ' frm_hidden';
	}
	?>
	<div id="frm_<?php echo esc_attr( $key ); ?>_settings_section" class="<?php echo esc_attr( $section_classes ); ?>" role="tabpanel">
		<?php if ( $include_h3 ) { ?>
			<h3 style="margin-bottom: 0;">
				<?php
				// translators: %s is the payment gateway name
				printf( '%s Settings', esc_html( $name ) );
				?>
			</h3>
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
}//end foreach
