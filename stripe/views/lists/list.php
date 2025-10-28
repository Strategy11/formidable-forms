<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<div class="frm_wrap">
	<?php
	$should_show_add_new_button = class_exists( 'FrmTransAppController', false ) || class_exists( 'FrmPaymentsController', false );
	FrmAppHelper::get_admin_header(
		array(
			'label'   => __( 'Payments', 'formidable' ),
			'form'    => FrmAppHelper::simple_get( 'form', 'absint', 0 ),
			'publish' => ! $should_show_add_new_button ? true : array(
				'FrmAppHelper::add_new_item_link',
				array(
					'new_link' => admin_url( 'admin.php?page=formidable-payments&amp;action=new' ),
				),
			),
		)
	);
	?>

	<div class="wrap">

		<style>
			.frm-payments-tabs {
				margin-bottom: var(--gap-md);
			}

			.frm-payments-tab {
				font-weight: 600;
				font-size: 14px;
				color: var(--grey-900);
				display: inline-block;
				border-width: 1px 1px 0px 1px;
				border-style: solid;
				border-color: rgba(234, 236, 240, 1);
				padding: 8px 12px;
				border-radius: 6px 6px 0 0;
				background-color: rgba(249, 250, 251, 1);
			}

			.frm-payments-tab.frm-active {
				background-color: white;
			}

			.frm-payments-tab a {
				text-decoration: none;
				color: var(--grey-900);				
			}
		</style>
		<div class="frm-payments-tabs">
			<div class="frm-payments-tab frm-active">
				<?php echo esc_html__( 'Payments', 'formidable' ); ?>
			</div>
			<div class="frm-payments-tab">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-coupons' ) ); ?>"><?php echo esc_html__( 'Coupons', 'formidable' ); ?></a>
			</div>
		</div>

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
