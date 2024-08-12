<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-style-component frm-background-image-component">
	<div class="frm-flex-justify">
		<input type="text" <?php echo esc_attr( $field_name ); ?> id="<?php echo esc_attr( $component['id'] ); ?>" class="hex" value="<?php echo esc_attr( $field_value ); ?>" size="4" <?php do_action( 'frm_style_settings_input_atts', $component['action_slug'] ); ?> />
		<?php
			do_action(
				'frm_style_settings_general_section_after_background',
				array(
					'frm_style'           => $component['frm_style'],
					'style'               => $component['style'],
					'image_id_input_name' => $component['image_id_input_name'],
				)
			);
			if ( ! FrmAppHelper::pro_is_installed() ) {
				?>
					<div class="frm_image_preview_wrapper" data-upgrade="<?php esc_attr_e( 'Background image styles', 'formidable' ); ?>" data-medium="background-image">
						<button type="button" class="frm_choose_image_box frm_button frm_no_style_button frm_noallow">
							<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_upload_icon' ); ?>
							<?php esc_attr_e( 'Upload background image', 'formidable' ); ?>
						</button>
					</div>
				<?php
			}
			?>
	</div>
</div>