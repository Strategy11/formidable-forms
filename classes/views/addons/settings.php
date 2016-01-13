<div class="wrap">
	<h4><?php _e( 'Plugin Licenses', 'formidable' ); ?></h4>

	<?php

	$any_unauthorized = false;
	foreach ( $plugins as $slug => $plugin ) {
		if ( $slug == 'formidable_pro' ) {
			continue;
		}

		$license = get_option( 'edd_'. $slug .'_license_key' );
		$status  = get_option( 'edd_'. $slug .'_license_active' );
		$activate = ( false !== $license && $status == 'valid' ) ? 'deactivate' : 'activate';
		if ( $activate == 'activate' ) {
			$any_unauthorized = true;
		}
		$icon_class = ( empty( $license ) ) ? 'frm_hidden' : '';
		?>

		<div class="edd_frm_license_row">
			<label class="frm_left_label" for="edd_<?php echo esc_attr( $slug ) ?>_license_key"><?php echo wp_kses( sprintf( '%s license key', $plugin->plugin_name ), array() ); ?></label>
			<div class="edd_frm_authorized alignleft <?php echo esc_attr( $activate == 'activate' ) ? 'frm_hidden' : '' ?>">
				<span class="edd_frm_license"><?php esc_html_e( 'Good to go!', 'formidable' ); ?></span>
				<span class="frm_icon_font frm_action_icon frm_error_icon edd_frm_status_icon frm_inactive_icon"></span>
				<input type="button" class="button-secondary edd_frm_save_license" data-plugin="<?php echo esc_attr( $slug ) ?>" name="edd_<?php echo esc_attr( $slug ) ?>_license_deactivate" value="<?php esc_attr_e( 'Deactivate', 'formidable' ) ?>"/>
				<p class="frm_license_msg"></p>
			</div>
			<div class="edd_frm_unauthorized alignleft <?php echo esc_attr( $activate == 'deactivate' ) ? 'frm_hidden' : '' ?>">
				<input id="edd_<?php echo esc_attr( $slug ) ?>_license_key" name="edd_<?php echo esc_attr( $slug ) ?>_license_key" type="text" class="regular-text frm_addon_license_key" value="" />
				<span class="frm_icon_font frm_action_icon frm_error_icon edd_frm_status_icon <?php echo esc_attr( $icon_class ); ?>"></span>
				<input type="button" class="button-secondary edd_frm_save_license" data-plugin="<?php echo esc_attr( $slug ) ?>" name="edd_<?php echo esc_attr( $slug ) ?>_license_activate" value="<?php esc_attr_e( 'Activate', 'formidable' ) ?>"/>
				<p class="frm_license_msg"></p>
			</div>

		</div>
	<?php } ?>
	<?php if ( $any_unauthorized && FrmAppHelper::pro_is_installed() ) { ?>
		<div class="clear"></div>
		<p><a href="#" class="edd_frm_fill_license button-secondary"><?php _e( 'Autofill Licenses', 'formidable' ) ?></a></p>
	<?php } ?>
</div>
