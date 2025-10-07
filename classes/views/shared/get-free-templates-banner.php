<?php
/**
 * Get free templates banner view.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-get-free-templates-banner frm-flex frm-gap-sm frm-items-center" data-direction="<?php echo esc_attr( $args['direction'] ?? 'horizontal' ); ?>">
	<div class="frm-banner-image-wrapper">
		<img width="100" src="<?php echo esc_url( FrmAppHelper::plugin_url() ); ?>/images/get-free-templates-banner.png" alt="<?php esc_attr_e( 'Get free templates', 'formidable' ); ?>" />
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
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-form-templates&free-templates=1' ) ); ?>" class="button button-primary frm-button-primary">
			<?php echo esc_html_x( 'Get Templates', 'get free templates banner button', 'formidable' ); ?>
		</a>
	</div>
</div>
