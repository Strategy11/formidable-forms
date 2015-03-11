<div id="form_views_page" class="wrap">
    <div class="frmicon icon32"><br/></div>
    <h2>
        <?php _e( 'Views', 'formidable' ); ?>
        <a href="#" class="add-new-h2 frm_invisible"><?php _e( 'Add New', 'formidable' ); ?></a>
    </h2>

<?php
	if ( $form ) {
		FrmAppController::get_form_nav( $form );
	}
	require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' );
	FrmAppHelper::update_message( __( 'display collected data in lists, calendars, and other formats', 'formidable' ) );
?>

    <img class="frm_no_views" src="http://fp.strategy11.com/images/custom-display-settings.png" alt="Display"/>

</div>