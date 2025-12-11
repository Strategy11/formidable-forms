<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
/**
 * @since x.x
 */
$atts = array(
	'show_pill'    => true,
	'button_text'  => __( 'Create with AI', 'formidable' ),
	'class'        => 'frm-form-templates-create-button frm-flex-box frm-items-center frm_show_upgrade',
	'upgrade_text' => __( 'Create with AI', 'formidable' ),
);
FrmFieldsHelper::render_ai_generate_options_button( $atts );
