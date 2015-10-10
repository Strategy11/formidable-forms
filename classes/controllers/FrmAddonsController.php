<?php

class FrmAddonsController {

	public static function show_addons() {
		$plugins = apply_filters( 'frm_installed_addons', array() );
		if ( empty( $plugins ) ) {
			_e( 'There are no plugins on your site that require a license', 'formidable' );
			return;
		}

		include( FrmAppHelper::plugin_path() . '/classes/views/addons/settings.php' );
	}
}
