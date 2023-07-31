<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Determine the support link based on lite vs pro.
$support_link = FrmAppHelper::pro_is_installed() ? 'https://wordpress.org/support/plugin/formidable/' : 'https://formidableforms.com/new-topic/';

// Determine the upgrade link based on lite vs elite.
$upgrade_link = FrmAppHelper::pro_is_installed() ? 'https://formidableforms.com/lite-upgrade/' : 'https://formidableforms.com/account/downloads/';
?>

<div class="frm-admin-footer-links">
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
		<a href="<?php echo esc_url( $support_link ); ?>"><?php esc_html_e( 'Support', 'formidable' ); ?></a> /
		<a href="https://formidableforms.com/knowledgebase/"><?php esc_html_e( 'Docs', 'formidable' ); ?></a> /
		<a href="<?php echo esc_url( $upgrade_link ); ?>"><?php esc_html_e( 'Upgrade', 'formidable' ); ?></a>
	</div><!-- .frm-admin-footer-links-nav -->

	<div class="frm-admin-footer-links-socials">
		<!-- Facebook link -->
		<a href="https://www.facebook.com/formidableforms/"><span class="dashicons dashicons-facebook"></span></a>
		<!-- Instagram link -->
		<a href="https://www.instagram.com/formidableforms/"><span class="dashicons dashicons-instagram"></span></a>
		<!-- Twitter link -->
		<a href="https://twitter.com/formidableforms/"><span class="dashicons dashicons-twitter"></span></a>
		<!-- Youtube link -->
		<a href="https://www.youtube.com/c/FormidableFormsPlugin/"><span class="dashicons dashicons-youtube"></span></a>
	</div><!-- .frm-admin-footer-links-socials -->
</div><!-- .frm-admin-footer-links -->
