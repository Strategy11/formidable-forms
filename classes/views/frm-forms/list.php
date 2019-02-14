<div class="frm_wrap">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label' => $params['template'] ? __( 'Templates', 'formidable' ) : __( 'Forms', 'formidable' ),
			'new_link' => ( ! $params['template'] && current_user_can( 'frm_edit_forms' ) ) ? '?page=formidable&frm_action=add_new' : '',
		)
	);
	?>
	<div class="wrap">
<?php
require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' );
$wp_list_table->views();
?>

<form id="posts-filter" method="get">
	<input type="hidden" name="page" value="<?php echo esc_attr( FrmAppHelper::simple_get( 'page', 'sanitize_title' ) ); ?>" />
	<input type="hidden" name="frm_action" value="list" />
<?php

$wp_list_table->search_box( __( 'Search', 'formidable' ), 'entry' );
$wp_list_table->display();

?>
</form>

</div>
</div>
