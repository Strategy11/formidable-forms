<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$attributes = array(
	'class' => 'frm_form_field frm6 frm6_followed frm-h-stack button frm-button-secondary frm-button-gradient frm-rounded-6 frm-max-w-fit frm-font-normal frm-py-2xs frm-px-xs frm-mt-xs frm-mb-12',
);

if ( isset( $should_hide_bulk_edit ) && $should_hide_bulk_edit ) {
	$attributes['class'] .= ' frm_hidden!';
}

$data = FrmAppHelper::get_upgrade_data_params(
	'ai',
	array(
		'requires' => 'Business',
		'upgrade'  => __( 'Generate options with AI', 'formidable' ),
		'medium'   => 'builder',
		'content'  => 'generate-options-with-ai',
	),
	true
);

if ( in_array( FrmAddonsController::license_type(), array( 'elite', 'business' ), true ) && 'active' === $data['plugin-status'] ) {
	// Backwards compatibility "@since x.x".
	if ( ! method_exists( 'FrmAIAppController', 'get_ai_generated_options_summary' ) ) {
		$data = array(
			'modal-title'   => __( 'Generate options with AI', 'formidable' ),
			'modal-content' => __( 'Update the Formidable AI add-on to the last version to use this feature.', 'formidable' ),
		);
	} else {
		$attributes['class']   .= ' frm-ai-generate-options-modal-trigger';
		$attributes['data-fid'] = $args['likert_id'] ?? $args['field']['id'];
	}
}

if ( empty( $attributes['data-fid'] ) ) {
	unset( $data['plugin-status'] );
	foreach ( $data as $key => $value ) {
		$attributes[ 'data-' . $key ] = $value;
	}
}
?>
<button <?php FrmAppHelper::array_to_html_params( $attributes, true ); ?>>
	<?php FrmAppHelper::icon_by_class( 'frmfont frm-ai-form-icon frm_svg15', array( 'aria-label' => __( 'Generate options with AI', 'formidable' ) ) ); ?>
	<span><?php esc_html_e( 'Generate with AI', 'formidable' ); ?></span>
</button>
