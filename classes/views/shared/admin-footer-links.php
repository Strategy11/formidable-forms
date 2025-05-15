<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Determine the support link based on lite vs pro.
$support_link = ! FrmAppHelper::pro_is_installed() ? 'https://wordpress.org/support/plugin/formidable/' : 'https://formidableforms.com/new-topic/';

$upgrade_link = FrmSalesApi::get_best_sale_value( 'footer_cta_link' );
$utm          = array(
	'medium' => 'admin-footer-link',
);
if ( $upgrade_link ) {
	$upgrade_link = FrmAppHelper::maybe_add_missing_utm( $upgrade_link, $utm );
} else {
	if ( FrmAppHelper::pro_is_installed() ) {
		$upgrade_link = 'https://formidableforms.com/account/downloads/';
	} else {
		$upgrade_link = FrmAppHelper::maybe_add_missing_utm( 'https://formidableforms.com/lite-upgrade/', $utm );
	}
}
?>

<div class="frm-admin-footer-links frm_hidden">
	<span class="frm-admin-footer-links-text">
		<?php
		printf(
			/* translators: %1$s: Heart icon */
			esc_html__( 'Made with %1$s by the Formidable Team', 'formidable' ),
			'<span class="dashicons dashicons-heart"></span>'
		);
		?>
	</span><!-- .frm-admin-footer-links-text -->

	<div class="frm-admin-footer-links-nav">
		<a href="<?php echo esc_url( $support_link ); ?>" target="_blank"><?php esc_html_e( 'Support', 'formidable' ); ?></a>
		<span>/</span>
		<a href="https://formidableforms.com/knowledgebase/" target="_blank"><?php esc_html_e( 'Docs', 'formidable' ); ?></a>
		<?php if ( 'elite' !== FrmAddonsController::license_type() ) : ?>
			<?php
			$cta_text = FrmSalesApi::get_best_sale_value( 'footer_cta_text' );
			if ( ! $cta_text ) {
				$cta_text = __( 'Upgrade', 'formidable' );
			}
			?>
			<span>/</span>
			<a href="<?php echo esc_url( $upgrade_link ); ?>" target="_blank"><?php echo esc_html( $cta_text ); ?></a>
		<?php endif; ?>
	</div><!-- .frm-admin-footer-links-nav -->

	<div class="frm-admin-footer-links-socials">
		<!-- Facebook link -->
		<a href="https://www.facebook.com/formidableforms/" target="_blank"><span class="dashicons dashicons-facebook"></span></a>
		<!-- Instagram link -->
		<a href="https://www.instagram.com/formidableforms/" target="_blank"><span class="dashicons dashicons-instagram"></span></a>
		<!-- Twitter link -->
		<a href="https://twitter.com/formidableforms/" target="_blank"><span class="dashicons dashicons-twitter"></span></a>
		<!-- Youtube link -->
		<a href="https://www.youtube.com/c/FormidableFormsPlugin/" target="_blank"><span class="dashicons dashicons-youtube"></span></a>
	</div><!-- .frm-admin-footer-links-socials -->
</div><!-- .frm-admin-footer-links -->
