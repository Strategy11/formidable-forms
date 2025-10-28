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
			'label'   => __( 'Coupons', 'formidable' ),
			'form'    => FrmAppHelper::simple_get( 'form', 'absint', 0 ),
			'publish' => $publish,
		)
	);
	?>

	<div class="wrap">
		<?php
		/**
		 * @since x.x
		 *
		 * @param bool $coupons_list_displayed
		 */
		$coupons_list_displayed = apply_filters( 'frm_coupons_list_displayed', false );
		if ( ! $coupons_list_displayed ) {
			// TODO: Show upsell here.
		}
		?>
	</div>
</div>
