<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm6 frm_form_field frm_noallow frm_show_upgrade" data-upgrade="<?php esc_attr_e( 'Separate Values', 'formidable' ); ?>" data-message="<?php esc_attr_e( 'Add a separate value to use for calculations, email routing, saving to the database, and many other uses. The option values are saved while the option labels are shown in the form.', 'formidable' ); ?>" data-medium="builder" data-content="separate-values">
	<label>
		<input type="checkbox" value="1" disabled="disabled" />
		<?php esc_html_e( 'Use separate values', 'formidable' ); ?>
	</label>
</p>

<?php
FrmAppHelper::images_dropdown(
	array(
		'selected'     => '0',
		'options'      => array(
			'0'       => array(
				'text' => __( 'Simple radio button', 'formidable' ),
				'svg'  => 'frm_simple_radio',
			),
			'1'       => array(
				'text' => __( 'Use images for radio button', 'formidable' ),
				'svg'  => 'frm_image_as_option',
			),
			'buttons' => array(
				'text' => __( 'Display options as buttons', 'formidable' ),
				'svg'  => 'frm_button_as_option',
			),
		),
		'show_upgrade' => true,
	)
);
