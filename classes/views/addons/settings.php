<div class="wrap">
	<h4><?php _e( 'Plugin Licenses', 'formidable' ); ?></h4>

	<?php

	foreach ( $plugins as $slug => $plugin ) {
		$license = get_option( 'edd_'. $slug .'_license_key' );
		$status  = get_option( 'edd_'. $slug .'_license_active' );
		$activate = ( false !== $license && $status == 'valid' ) ? 'deactivate' : 'activate';
		$icon_class = ( empty( $license ) ) ? 'frm_hidden' : '';
		?>

		<div class="edd_frm_license_row">
			<label class="frm_left_label" for="edd_<?php echo esc_attr( $slug ) ?>_license_key"><?php echo wp_kses( sprintf( '%s license key', $plugin->plugin_name ), array() ); ?></label>
			<div class="edd_frm_authorized alignleft <?php echo esc_attr( $activate == 'activate' ) ? 'frm_hidden' : '' ?>">
				<span class="edd_frm_license"><?php echo esc_html( $license ); ?></span>
				<span class="frm_icon_font frm_action_icon frm_error_icon edd_frm_status_icon frm_inactive_icon"></span>
				<input type="button" class="button-secondary edd_frm_save_license" data-plugin="<?php echo esc_attr( $slug ) ?>" name="edd_<?php echo esc_attr( $slug ) ?>_license_deactivate" value="<?php esc_attr_e( 'Deactivate', 'formidable' ) ?>"/>
				<p class="frm_license_msg"></p>
			</div>
			<div class="edd_frm_unauthorized alignleft <?php echo esc_attr( $activate == 'deactivate' ) ? 'frm_hidden' : '' ?>">
				<input id="edd_<?php echo esc_attr( $slug ) ?>_license_key" name="edd_<?php echo esc_attr( $slug ) ?>_license_key" type="text" class="regular-text" value="<?php echo esc_attr( $license ); ?>" />
				<span class="frm_icon_font frm_action_icon frm_error_icon edd_frm_status_icon <?php echo esc_attr( $icon_class ); ?>"></span>
				<input type="button" class="button-secondary edd_frm_save_license" data-plugin="<?php echo esc_attr( $slug ) ?>" name="edd_<?php echo esc_attr( $slug ) ?>_license_activate" value="<?php esc_attr_e( 'Activate', 'formidable' ) ?>"/>
				<p class="frm_license_msg"></p>
			</div>

		</div>
	<?php } ?>

</div>
