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
	'class'         => "frm-form-templates-create-button",
);

$oneclick_data = FrmAddonsController::install_link( 'ai' );
if ( isset( $oneclick_data['url'] ) ) {
	$ai_install_span_attrs['data-oneclick'] = json_encode( $oneclick_data );
}
?>
<span <?php FrmAppHelper::array_to_html_params( $ai_install_span_attrs, true ); ?>>
<?php
FrmAddonsController::conditional_action_button(
	'ai',
	array(
		'medium' => 'ai-autofill',
	)
);
?>
</span>