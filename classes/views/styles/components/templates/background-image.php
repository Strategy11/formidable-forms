<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php echo esc_html( $component['title'] ); ?></label></div>
<div class="frm7 frm_form_field">
	<div class="frm-style-component frm-background-image-component">
		<span class="frm-flex-justify" tabindex="0">
			<input type="text" <?php echo esc_attr( $field_name ); ?> id="<?php echo esc_attr( $component['id'] ); ?>" class="hex" value="<?php echo esc_attr( $field_value ); ?>" size="4" <?php do_action( 'frm_style_settings_input_atts', $component['action_slug'] ); ?> />
			<?php
			/**
			 * Prompt Pro to load the upload button along with its functionalities.
			 * Before it was loaded via frm_style_settings_general_section_after_background action hook.
			 *
			 * @since 6.14
			 */
			do_action(
				'frm_style_settings_bg_image_component_upload_button',
				array(
					'frm_style'           => $component['frm_style'],
					'style'               => $component['style'],
					'image_id_input_name' => $component['image_id_input_name'],
				)
			);
			if ( ! FrmAppHelper::pro_is_installed() ) {
				?>
				<div class="frm_image_preview_wrapper" data-upgrade="<?php esc_attr_e( 'Background image styles', 'formidable' ); ?>" data-medium="background-image">
					<button type="button" class="frm_choose_image_box frm_button frm-flex-center frm_no_style_button frm_noallow" tabindex="0">
						<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_upload_icon' ); ?>
						<?php esc_attr_e( 'Upload background image', 'formidable' ); ?>
					</button>
				</div>
				<?php
			}
			?>
		</span>
	</div>
</div>
<?php
if ( ! empty( $component['include_additional_settings'] ) ) {
	/**
	 * Prompt Pro to load the additional background image options like "Image Opacity".
	 */
	do_action(
		'frm_style_settings_general_section_after_background',
		array(
			'frm_style' => $component['frm_style'],
			'style'     => $component['style'],
		)
	);
}