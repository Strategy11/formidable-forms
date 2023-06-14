<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2><?php esc_html_e( 'Payments', 'formidable' ); ?></h2>

	<?php
	include FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php';
	$wp_list_table->views();
	?>

	<form id="posts-filter" method="get">
		<input type="hidden" name="page" value="formidable-payments" />
		<input type="hidden" name="frm_action" value="list" />
		<?php $wp_list_table->display(); ?>
	</form>

</div>
