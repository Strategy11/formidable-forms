<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_wrap">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label'       => __( 'Forms', 'formidable' ),

			'import_link' => true,
			'publish'     => array(
				'FrmAppHelper::add_new_item_link',
				array(
					'create_form' => current_user_can( 'frm_edit_forms' ),
					'new_link'    => admin_url( 'admin.php?page=' . FrmFormTemplatesController::PAGE_SLUG . '&return_page=forms' ),
				),
			),
		)
	);
	?>
	<div class="wrap">
<?php
require FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php';
$wp_list_table->views();
$form_type = FrmAppHelper::simple_get( 'form_type', 'sanitize_title', 'published' );
?>

<form id="posts-filter" method="get">
	<input type="hidden" name="page" value="<?php echo esc_attr( FrmAppHelper::simple_get( 'page', 'sanitize_title' ) ); ?>" />
	<input type="hidden" name="frm_action" value="list" />
	<input type="hidden" name="form_type"  value="<?php echo esc_attr( $form_type ); ?>" />
<?php

$wp_list_table->search_box( __( 'Search', 'formidable' ), 'entry' );
$wp_list_table->display();

if ( $wp_list_table->total_items === 1 && empty( $_REQUEST['s'] ) && $wp_list_table->status === '' ) {
	$is_default = false;
	foreach ( $wp_list_table->items as $item ) {
		$is_default = $item->form_key === 'contact-form';
	}
	// Show no form created info if only the default form exists.
	if ( $is_default ) {
		$title = __( 'You have not created any forms yet', 'formidable' );
		$info  = __( 'Start collecting leads and data today.', 'formidable' );
		include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/_no_forms.php';
	}
}

?>
</form>

<?php do_action( 'frm_page_footer', array( 'table' => $wp_list_table ) ); ?>
</div>
</div>
