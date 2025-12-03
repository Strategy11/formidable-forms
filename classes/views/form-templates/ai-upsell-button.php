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
<div class="frm-activate-addon frm-flex-box frm-form-templates-create-button frm-items-center frm_show_upgrade">
	<?php FrmAppHelper::icon_by_class( 'frmfont frm-ai-form-icon', array( 'aria-label' => _x( 'Create', 'form templates: create an AI generated form', 'formidable' ) ) ); ?>
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
	<?php FrmAppHelper::show_pill_text( __( 'BETA', 'formidable' ) ); ?>
</div>