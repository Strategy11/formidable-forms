<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Determine the support link based on lite vs pro.
$support_link = ! FrmAppHelper::pro_is_installed() ? 'https://wordpress.org/support/plugin/formidable/' : 'https://formidableforms.com/new-topic/';

// Determine the upgrade link based on lite vs pro.
$upgrade_link = ! FrmAppHelper::pro_is_installed() ? 'https://formidableforms.com/lite-upgrade/' : 'https://formidableforms.com/account/downloads/';
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
			<span>/</span>
			<a href="<?php echo esc_url( $upgrade_link ); ?>" target="_blank"><?php esc_html_e( 'Upgrade', 'formidable' ); ?></a>
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
