<?php
/**
 * AI generate options upsell button.
 *
 * @package Formidable
 *
 * @since x.x
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

FrmFieldsHelper::render_ai_generate_options_button(
	array(
		'show_pill'    => true,
		'button_text'  => __( 'Create with AI', 'formidable' ),
		'class'        => 'frm-form-templates-create-button frm-flex-box frm-items-center frm_show_upgrade',
		'upgrade_text' => __( 'Create with AI', 'formidable' ),
	)
);
