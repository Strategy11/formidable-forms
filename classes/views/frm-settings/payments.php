<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

echo '<div class="frm-long-icon-buttons">';
$first = true;
foreach ( $payment_sections as $key => $section ) {
	$name         = isset( $section['name'] ) ? $section['name'] : ucfirst( $key );
	$input_params = array(
		'id'           => "frm_toggle_{$key}_settings",
		'type'         => 'radio',
		'name'         => 'frm_payment_section',
		'value'        => $key,
		'data-frmshow' => "#frm_{$key}_settings_section",
	);
	if ( $first ) {
		$input_params['checked'] = 'checked';
	}
	$other_sections = array_diff( array_keys( $payment_sections ), array( $key ) );
	$input_params['data-frmhide'] = implode( ',', array_map( function( $section ) {
		return "#frm_{$section}_settings_section";
	}, $other_sections ) );
	?>
	<input <?php echo FrmAppHelper::array_to_html_params( $input_params, true ); ?> />
	<label for="frm_toggle_<?php echo esc_attr( $key ); ?>_settings" class="frm_payment_settings_tab">
		<?php echo esc_html( $name ); ?>
	</label>
	<?php
	$first = false;
}
echo '</div>';

$first = true;
foreach ( $payment_sections as $key => $section ) {
	?>
	<div id="frm_<?php echo esc_attr( $key ); ?>_settings_section" class="frm_payments_section <?php if ( ! $first ) { echo 'frm_hidden'; } ?>">
		<?php
		if ( isset( $section['class'] ) ) {
			call_user_func( array( $section['class'], $section['function'] ) );
		} else {
			call_user_func( ( isset( $section['function'] ) ? $section['function'] : $section ) );
		}
		?>
	</div>
	<?php
	$first = false;
}
