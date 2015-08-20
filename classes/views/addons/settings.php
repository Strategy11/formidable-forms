<div class="wrap">
	<h2><?php _e( 'Plugin Licenses', 'formidable' ); ?></h2>

	<?php
		
	foreach ( $plugins as $slug => $plugin ) {
		$license = get_option( 'edd_'. $slug .'_license_key' );
		$status  = get_option( 'edd_'. $slug .'_license_active' );
		$activate = ( false !== $license && $status == 'valid' ) ? 'deactivate' : 'activate';
		$icon_class = ( $status == 'valid' ) ? 'frm_inactive_icon' : '';
		$icon_class = ( empty( $license ) ) ? 'frm_hidden' : $icon_class;
		?>

		<div class="edd_frm_license_row">
			<label class="frm_left_label" for="edd_<?php echo esc_attr( $slug ) ?>_license_key"><?php echo $plugin->plugin_name ?></label>
			<input id="edd_<?php echo esc_attr( $slug ) ?>_license_key" name="edd_<?php echo esc_attr( $slug ) ?>_license_key" type="text" class="regular-text" value="<?php echo esc_attr( $license ); ?>" />

			<span class="frm_icon_font frm_action_icon frm_error_icon edd_frm_status_icon <?php echo esc_attr( $icon_class ); ?>"></span>

			<input type="button" class="button-secondary edd_frm_save_license" data-plugin="<?php echo esc_attr( $slug ) ?>" name="edd_<?php echo esc_attr( $slug ) ?>_license_<?php echo esc_attr( $activate ) ?>" value="<?php echo esc_attr( $activate_labels[ $activate ] ) ?>"/>
		</div>
	<?php } ?>

</div>