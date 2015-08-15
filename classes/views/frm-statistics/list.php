<div id="form_reports_page" class="wrap">
    <div class="frmicon icon32"><br/></div>
    <h2>
		<?php _e( 'Reports', 'formidable' ) ?>
		<a href="#" class="add-new-h2 frm_invisible"><?php _e( 'Add New', 'formidable' ); ?></a>
	</h2>

<?php
if ( $form ) {
	FrmAppController::get_form_nav( $form, true );
}
require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' );

FrmAppHelper::update_message( __( 'view reports and statistics on your saved entries', 'formidable' ) );
?>

    <img class="frm_no_reports" src="http://fp.strategy11.com/wp-content/themes/formidablepro/images/reports1.png" alt="Reports"/>
</div>
