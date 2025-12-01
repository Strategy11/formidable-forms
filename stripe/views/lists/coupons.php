<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<div class="frm_wrap">
	<?php
	/**
	 * @since x.x
	 *
	 * @param bool $publish
	 */
	$publish = apply_filters( 'frm_coupons_list_button', true );

	FrmAppHelper::get_admin_header(
		array(
			'label'   => __( 'Payments', 'formidable' ),
			'form'    => FrmAppHelper::simple_get( 'form', 'absint', 0 ),
			'publish' => $publish,
		)
	);
	?>

	<div class="wrap">
		<?php
		/**
		 * Allow the coupons add-on to hook in and display the coupons list.
		 * If the return value of the hook doesn't change, the coupons upsell
		 * will be displayed instead.
		 *
		 * @since x.x
		 *
		 * @param bool $coupons_list_displayed
		 */
		$coupons_list_displayed = apply_filters( 'frm_coupons_list_displayed', false );
		if ( ! $coupons_list_displayed ) {
			include FrmAppHelper::plugin_path() . '/stripe/views/lists/coupons-upsell.php';
		}
		?>
	</div>
</div>
