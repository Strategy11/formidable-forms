<?php
/**
 * Access free templates banner view.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-access-free-templates-banner frm-flex frm-gap-sm frm-items-center" data-direction="<?php echo esc_attr( $args['direction'] ?? 'horizontal' ); ?>">
	<div class="frm-banner-image-wrapper">
		<img width="100" src="<?php echo esc_url( FrmAppHelper::plugin_url() ); ?>/images/access-free-templates-banner.png" alt="<?php esc_attr_e( 'Access free templates', 'formidable' ); ?>" />
	</div>

	<div class="frm-banner-content frm-flex-col frm-gap-xs frm-items-start">
		<h3 class="frm-text-sm frm-font-semibold">
			<?php
			printf(
				// translators: %s: HTML line break
				esc_html__( 'Get Instant Access to %s 30+ Free Form Templates', 'formidable' ),
				'<br>'
			);
			?>
		</h3>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-form-templates&access-free-templates=true' ) ); ?>" class="button button-primary frm-button-primary">
			<?php esc_html_e( 'Get Templates', 'formidable' ); ?>
		</a>
	</div>
</div>
