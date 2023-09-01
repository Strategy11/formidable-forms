<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<div class="frm_wrap">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label' => __( 'Payments', 'formidable' ),
			'form'  => FrmAppHelper::simple_get( 'form', 'absint', 0 ),
		)
	);
	?>

	<div class="wrap">

		<?php
		$wp_list_table->views();
		?>

		<form id="posts-filter" method="get">
			<input type="hidden" name="page" value="formidable-payments" />
			<input type="hidden" name="frm_action" value="list" />
			<?php $wp_list_table->display(); ?>
		</form>

	</div>
</div>
