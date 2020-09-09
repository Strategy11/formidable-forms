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

<p class="frm6 frm_form_field frm_image_options_radio frm_noallow frm_show_upgrade" data-upgrade="<?php esc_attr_e( 'Image Options', 'formidable' ); ?>" data-message="<?php echo esc_attr( __( 'Show images instead of radio buttons or check boxes. This is ideal for polls, surveys, segmenting questionnaires and more.', 'formidable' ) . '<img src="' . FrmAppHelper::plugin_url() . '/images/image-options.png" />' ); ?>" data-medium="builder" data-content="image-options">
	<label>
		<input type="checkbox" value="1" disabled="disabled" />
		<?php esc_html_e( 'Use images for options', 'formidable' ); ?>
	</label>
</p>
