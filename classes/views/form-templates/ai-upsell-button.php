<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
/**
 * @since x.x
 */

$ai_install_span_attrs = array(
	'data-upgrade'  => __( 'Autofilled forms with AI', 'formidable' ),
	'data-content'  => 'ai-autofill',
	'data-medium'   => 'test-mode',
	'data-requires' => 'Business',
);

$oneclick_data = FrmAddonsController::install_link( 'ai' );
if ( isset( $oneclick_data['url'] ) ) {
	$ai_install_span_attrs['data-oneclick'] = json_encode( $oneclick_data );
}
?>
<div class="frm-flex-box frm-items-center frm_show_upgrade">
	<?php
	FrmFieldsHelper::render_ai_generate_options_button( array(), false );
	FrmAppHelper::show_pill_text( __( 'BETA', 'formidable' ) );
	?>
</div>

