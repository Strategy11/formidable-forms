<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

_deprecated_file( esc_html( basename( __FILE__ ) ), '4.0' );

do_action( 'frm_show_entry_sidebar', $entry );
FrmEntriesController::entry_sidebar( $entry );
